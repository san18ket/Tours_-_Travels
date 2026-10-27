<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\ValidationException;
use App\Models\Employee;
use App\Models\User;
use App\Models\DriverProfile;
use App\Models\AuditLog;
use Throwable;

class EmployeeController extends Controller
{
    private Employee $employeeModel;
    private User $userModel;
    private DriverProfile $driverModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->employeeModel = new Employee();
        $this->userModel = new User();
        $this->driverModel = new DriverProfile();
        $this->auditLog = new AuditLog();
    }

    /**
     * Display Employee Directory with search, department filtering, and pagination
     */
    public function index(Request $request): Response
    {
        $search = trim((string)$request->get('search', ''));
        $department = trim((string)$request->get('department', ''));
        $status = trim((string)$request->get('status', ''));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = in_array((int)$request->get('per_page'), [10, 25, 50, 100], true) ? (int)$request->get('per_page') : 10;

        $totalEmployees = $this->employeeModel->countFiltered($search, $department, $status);
        $employees = $this->employeeModel->getPaginated($search, $department, $status, $page, $perPage);
        $totalPages = max(1, (int)ceil($totalEmployees / $perPage));

        // Aggregate counts for summary pills
        $totalActive = $this->employeeModel->count("`status` = 'active'");
        $totalChauffeurs = $this->employeeModel->count("`department` = 'fleet_chauffeur' AND `status` = 'active'");
        $totalOperations = $this->employeeModel->count("`department` IN ('operations', 'dispatch') AND `status` = 'active'");

        return $this->render('office/employees/index', [
            'employees'          => $employees,
            'totalEmployees'     => $totalEmployees,
            'totalActive'        => $totalActive,
            'totalChauffeurs'    => $totalChauffeurs,
            'totalOperations'    => $totalOperations,
            'currentPage'        => $page,
            'totalPages'         => $totalPages,
            'search'             => $search,
            'selectedDepartment' => $department,
            'selectedStatus'     => $status,
            'perPage'            => $perPage,
            'title'              => 'Employee Roster & HR Directory',
        ], 'layouts/office');
    }

    /**
     * Display Add Employee Form
     */
    public function create(Request $request): Response
    {
        $generatedCode = $this->employeeModel->generateEmployeeCode();

        // Fetch actual roles from roles table (only real database roles, no dummy data)
        $roleModel = new \App\Models\Role();
        $roles = $roleModel->where("`status` = 'active'", [], 'is_system DESC, name ASC');

        // Unlinked staff users who can optionally be attached to an employee record
        $unlinkedUsers = $this->userModel->where("`status` = 'active'", [], 'name ASC');

        return $this->render('office/employees/create', [
            'employeeCode'  => $generatedCode,
            'roles'         => $roles,
            'unlinkedUsers' => $unlinkedUsers,
            'errors'        => Session::getFlash('form_errors') ?? [],
            'old'           => Session::getFlash('form_old') ?? [],
            'title'         => 'Register New Employee',
        ], 'layouts/office');
    }

    /**
     * Store new employee record
     */
    public function store(Request $request): Response
    {
        try {
            $input = $request->all();

            if (empty($input['role'])) {
                $input['role'] = 'driver';
            }

            $rules = [
                'first_name'      => 'required|min:2|max:80',
                'last_name'       => 'required|min:1|max:80',
                'phone'           => 'required|min:8|max:30',
                'role'            => 'required',
                'employment_type' => 'required|in:full_time,part_time,contract,probation',
                'joining_date'    => 'required|date',
                'status'          => 'required|in:active,on_leave,terminated',
            ];

            // If creating a new system account (strictly check toggle)
            $createAccount = !empty($input['create_account']) && in_array((string)$input['create_account'], ['1', 'true', 'on'], true);
            if ($createAccount) {
                $rules['username'] = 'required|min:3|max:50';
                $rules['password'] = 'required|min:6';
            }

            $data = $this->validate($input, $rules);

            $roleSlug = strtolower(trim($data['role']));

            // Resolve Role Name
            $roleModel = new \App\Models\Role();
            $roleObj = $roleModel->findBy('slug', $roleSlug);
            $roleName = $roleObj['name'] ?? ucwords(str_replace(['_', '-'], ' ', $roleSlug));

            $userId = !empty($input['user_id']) ? (int)$input['user_id'] : null;

            // Check & create user account if requested
            if ($createAccount) {
                $username = strtolower(trim($input['username'] ?? ''));
                if (!preg_match('/^[a-zA-Z0-9_.]+$/', $username)) {
                    throw new ValidationException('Username can only contain letters, numbers, underscores, and periods.', ['username' => ['Username can only contain letters, numbers, underscores, and periods.']]);
                }

                $existingUser = $this->userModel->findBy('username', $username);
                if ($existingUser) {
                    throw new ValidationException("Username '{$username}' is already in use by another account.", ['username' => ['This username is already taken.']]);
                }

                $userEmail = !empty($input['email']) ? strtolower(trim($input['email'])) : ($username . '@voyagego.internal');
                $existingEmail = $this->userModel->findBy('email', $userEmail);
                if ($existingEmail) {
                    throw new ValidationException("Email address '{$userEmail}' is already registered with another account.", ['email' => ['This email address is already in use.']]);
                }

                if (!empty($input['password_confirmation']) && $input['password'] !== $input['password_confirmation']) {
                    throw new ValidationException('Passwords do not match.', ['password_confirmation' => ['Password confirmation does not match.']]);
                }

                $newUserId = $this->userModel->create([
                    'name'       => trim($data['first_name'] . ' ' . $data['last_name']),
                    'username'   => $username,
                    'email'      => $userEmail,
                    'phone'      => trim($data['phone']),
                    'password'   => password_hash($input['password'], PASSWORD_BCRYPT, ['cost' => 12]),
                    'role'       => $roleSlug,
                    'status'     => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $userId = $newUserId;
            }

            $employeeCode = $this->employeeModel->generateEmployeeCode();

            $employeeId = $this->employeeModel->create([
                'employee_code'     => $employeeCode,
                'user_id'           => $userId,
                'first_name'        => trim($data['first_name']),
                'last_name'         => trim($data['last_name']),
                'phone'             => trim($data['phone']),
                'alternate_phone'   => !empty($input['alternate_phone']) ? trim($input['alternate_phone']) : null,
                'email'             => !empty($input['email']) ? strtolower(trim($input['email'])) : null,
                'department'        => $roleSlug,
                'designation'       => $roleName,
                'employment_type'   => $data['employment_type'],
                'joining_date'      => $data['joining_date'],
                'status'            => $data['status'],
                'emergency_contact' => !empty($input['emergency_contact']) ? trim($input['emergency_contact']) : null,
                'address'           => !empty($input['address']) ? trim($input['address']) : null,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);

            // If chauffeur/driver role or license number provided, ensure driver_profile exists
            $isDriverRole = in_array($roleSlug, ['driver', 'chauffeur', 'fleet_chauffeur'], true) || !empty($input['license_number']);
            if ($isDriverRole && !empty($input['license_number']) && $userId) {
                $existingProfile = $this->driverModel->findBy('user_id', $userId);
                if (!$existingProfile) {
                    $this->driverModel->create([
                        'user_id'          => $userId,
                        'employee_id'      => $employeeId,
                        'license_number'   => strtoupper(trim($input['license_number'])),
                        'license_expiry'   => !empty($input['license_expiry']) ? $input['license_expiry'] : date('Y-m-d', strtotime('+3 years')),
                        'experience_years' => (int)($input['experience_years'] ?? 3),
                        'duty_status'      => 'off_duty',
                        'rating'           => 5.00,
                    ]);
                } else {
                    $this->driverModel->update($existingProfile['id'], ['employee_id' => $employeeId]);
                }
            }

            $this->auditLog->record(
                'employee_created',
                'employees',
                $employeeId,
                null,
                [
                    'code' => $employeeCode,
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'role' => $roleSlug,
                ],
                Session::getUserId()
            );

            return $this->redirect(url('/office/employees'), 'success', "Employee [{$employeeCode}] {$data['first_name']} {$data['last_name']} successfully registered.");

        } catch (ValidationException $e) {
            Session::setFlash('form_errors', $e->getErrors());
            Session::setFlash('form_old', $request->all());
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/office/employees/create'));
        } catch (Throwable $e) {
            Session::setFlash('form_old', $request->all());
            Session::setFlash('danger', 'Error registering employee: ' . $e->getMessage());
            return $this->redirect(url('/office/employees/create'));
        }
    }

    /**
     * Show detailed employee profile
     */
    public function show(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $employee = $this->employeeModel->findWithDetails($id);
        if (!$employee) {
            return $this->redirect(url('/office/employees'), 'danger', 'Employee not found.');
        }

        return $this->render('office/employees/show', [
            'employee' => $employee,
            'title'    => "Employee Profile - {$employee['first_name']} {$employee['last_name']}",
        ], 'layouts/office');
    }

    /**
     * Display edit form for employee
     */
    public function edit(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $employee = $this->employeeModel->findWithDetails($id);
        if (!$employee) {
            return $this->redirect(url('/office/employees'), 'danger', 'Employee not found.');
        }

        $unlinkedUsers = $this->userModel->where("`role` IN ('admin', 'dispatcher', 'driver') AND `status` = 'active'", [], 'name ASC');

        return $this->render('office/employees/edit', [
            'employee'      => $employee,
            'unlinkedUsers' => $unlinkedUsers,
            'errors'        => Session::getFlash('form_errors') ?? [],
            'title'         => "Edit Employee - {$employee['employee_code']}",
        ], 'layouts/office');
    }

    /**
     * Update existing employee record
     */
    public function update(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $employee = $this->employeeModel->find($id);
        if (!$employee) {
            return $this->redirect(url('/office/employees'), 'danger', 'Employee not found.');
        }

        try {
            $input = $request->all();

            $data = $this->validate($input, [
                'first_name'      => 'required|min:2|max:80',
                'last_name'       => 'required|min:1|max:80',
                'phone'           => 'required|min:8|max:30',
                'department'      => 'required|in:operations,dispatch,fleet_chauffeur,management,administration,maintenance',
                'designation'     => 'required|min:2|max:100',
                'employment_type' => 'required|in:full_time,part_time,contract,probation',
                'joining_date'    => 'required|date',
                'status'          => 'required|in:active,on_leave,terminated',
            ]);

            $userId = !empty($input['user_id']) ? (int)$input['user_id'] : null;

            $this->employeeModel->update($id, [
                'user_id'           => $userId,
                'first_name'        => trim($data['first_name']),
                'last_name'         => trim($data['last_name']),
                'phone'             => trim($data['phone']),
                'alternate_phone'   => !empty($input['alternate_phone']) ? trim($input['alternate_phone']) : null,
                'email'             => !empty($input['email']) ? strtolower(trim($input['email'])) : null,
                'department'        => $data['department'],
                'designation'       => trim($data['designation']),
                'employment_type'   => $data['employment_type'],
                'joining_date'      => $data['joining_date'],
                'status'            => $data['status'],
                'emergency_contact' => !empty($input['emergency_contact']) ? trim($input['emergency_contact']) : null,
                'address'           => !empty($input['address']) ? trim($input['address']) : null,
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);

            $this->auditLog->record(
                'employee_updated',
                'employees',
                $id,
                $employee,
                $data,
                Session::getUserId()
            );

            return $this->redirect(url("/office/employees/{$id}"), 'success', "Employee record [{$employee['employee_code']}] successfully updated.");

        } catch (ValidationException $e) {
            Session::setFlash('form_errors', $e->getErrors());
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url("/office/employees/{$id}/edit"));
        } catch (Throwable $e) {
            Session::setFlash('danger', 'Error updating employee: ' . $e->getMessage());
            return $this->redirect(url("/office/employees/{$id}/edit"));
        }
    }
}
