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
        $totalAll = $this->employeeModel->count();
        $totalActive = $this->employeeModel->count("`status` = 'active'");
        $totalOnLeave = $this->employeeModel->count("`status` = 'on_leave'");
        $totalTerminated = $this->employeeModel->count("`status` = 'terminated'");
        $totalChauffeurs = $this->employeeModel->count("`department` = 'fleet_chauffeur' AND `status` = 'active'");
        $totalOperations = $this->employeeModel->count("`department` IN ('operations', 'dispatch') AND `status` = 'active'");

        return $this->render('office/employees/index', [
            'employees'          => $employees,
            'totalEmployees'     => $totalEmployees,
            'totalAll'           => $totalAll,
            'totalActive'        => $totalActive,
            'totalOnLeave'       => $totalOnLeave,
            'totalTerminated'    => $totalTerminated,
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

            $roleSlug = !empty($input['role']) ? strtolower(trim($input['role'])) : 'driver';
            $status = !empty($input['status']) && in_array($input['status'], ['active', 'on_leave', 'terminated'], true) ? $input['status'] : 'active';

            $rules = [
                'first_name'      => 'required|min:2|max:80',
                'last_name'       => 'required|min:1|max:80',
                'phone'           => 'required|min:8|max:30',
                'employment_type' => 'required|in:full_time,part_time,contract,probation',
                'joining_date'    => 'required|date',
            ];

            // If creating a new system account (strictly check toggle)
            $createAccount = !empty($input['create_account']) && in_array((string)$input['create_account'], ['1', 'true', 'on'], true);
            if ($createAccount) {
                $rules['username'] = 'required|min:3|max:50';
                $rules['password'] = 'required|min:6';
            }

            $data = $this->validate($input, $rules);

            // Automatically resolve Department and Designation based on Role
            if ($roleSlug === 'driver' || $roleSlug === 'chauffeur') {
                $department = 'fleet_chauffeur';
                $designation = 'Chauffeur / Driver';
            } elseif ($roleSlug === 'dispatcher') {
                $department = 'dispatch';
                $designation = 'Fleet Dispatcher';
            } elseif ($roleSlug === 'manager') {
                $department = 'management';
                $designation = 'Operations Manager';
            } elseif ($roleSlug === 'admin') {
                $department = 'administration';
                $designation = 'System Administrator';
            } else {
                $department = 'operations';
                $designation = ucwords(str_replace(['_', '-'], ' ', $roleSlug));
            }

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

            // Normalization of address matching customers/create architecture
            $addressLine = !empty($input['address_line']) ? trim($input['address_line']) : '';
            $city = !empty($input['city']) ? trim($input['city']) : '';
            $state = !empty($input['state']) ? trim($input['state']) : '';
            $country = !empty($input['country']) ? trim($input['country']) : 'India';
            $postalCode = !empty($input['postal_code']) ? trim($input['postal_code']) : '';
            $fullAddress = trim(implode(', ', array_filter([$addressLine, $city, $state, $postalCode, $country]))) ?: (!empty($input['address']) ? trim($input['address']) : null);

            // Handle optional profile photo, license document, and ID document uploads
            $uploadedAvatarPath = $this->saveUploadedFileOrBase64(
                $request,
                $input,
                'profile_photo',
                'profile_photo_base64',
                'employees/avatars',
                [
                    'image/jpeg'  => 'jpg',
                    'image/pjpeg' => 'jpg',
                    'image/png'   => 'png',
                    'image/x-png' => 'png',
                    'image/webp'  => 'webp',
                ]
            );

            $uploadedLicensePath = $this->saveUploadedFileOrBase64(
                $request,
                $input,
                'license_document_file',
                'license_document_file_base64',
                'employees/licenses',
                [
                    'image/jpeg'       => 'jpg',
                    'image/pjpeg'      => 'jpg',
                    'image/png'        => 'png',
                    'image/x-png'      => 'png',
                    'image/webp'       => 'webp',
                    'application/pdf'  => 'pdf',
                ]
            );

            $uploadedDocPath = $this->saveUploadedFileOrBase64(
                $request,
                $input,
                'id_document_file',
                'id_document_file_base64',
                'employees/documents',
                [
                    'image/jpeg'       => 'jpg',
                    'image/pjpeg'      => 'jpg',
                    'image/png'        => 'png',
                    'image/x-png'      => 'png',
                    'image/webp'       => 'webp',
                    'application/pdf'  => 'pdf',
                ]
            );

            $employeeCode = $this->employeeModel->generateEmployeeCode();

            $isDriverRole = in_array($roleSlug, ['driver', 'chauffeur', 'fleet_chauffeur'], true) || !empty($input['license_number']);

            // If chauffeur/driver role and user account wasn't created yet, auto-create one for driver portal linking
            if ($isDriverRole && !$userId) {
                $driverUsername = 'driver_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $data['first_name'])) . '_' . rand(100, 999);
                $driverEmail = !empty($input['email']) ? strtolower(trim($input['email'])) : ($driverUsername . '@voyagego.internal');
                $newUserId = $this->userModel->create([
                    'name'       => trim($data['first_name'] . ' ' . $data['last_name']),
                    'username'   => $driverUsername,
                    'email'      => $driverEmail,
                    'phone'      => trim($data['phone']),
                    'password'   => password_hash('Driver@' . rand(1000, 9999), PASSWORD_BCRYPT, ['cost' => 12]),
                    'role'       => 'driver',
                    'status'     => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $userId = $newUserId;
            }

            $employeeId = $this->employeeModel->create([
                'employee_code'          => $employeeCode,
                'user_id'                => $userId,
                'first_name'             => trim($data['first_name']),
                'last_name'              => trim($data['last_name']),
                'phone'                  => trim($data['phone']),
                'alternate_phone'        => !empty($input['alternate_phone']) ? trim($input['alternate_phone']) : null,
                'email'                  => !empty($input['email']) ? strtolower(trim($input['email'])) : null,
                'profile_photo'          => $uploadedAvatarPath,
                'date_of_birth'          => !empty($input['date_of_birth']) ? date('Y-m-d', strtotime($input['date_of_birth'])) : null,
                'gender'                 => !empty($input['gender']) ? trim($input['gender']) : null,
                'nationality'            => !empty($input['nationality']) ? trim($input['nationality']) : 'Indian',
                'department'             => $department,
                'designation'            => $designation,
                'employment_type'        => $data['employment_type'],
                'joining_date'           => $data['joining_date'],
                'status'                 => $status,
                'emergency_contact'      => !empty($input['emergency_contact']) ? trim($input['emergency_contact']) : trim(($input['emergency_contact_name'] ?? '') . ' ' . ($input['emergency_contact_phone'] ?? '')),
                'emergency_contact_name' => !empty($input['emergency_contact_name']) ? trim($input['emergency_contact_name']) : null,
                'emergency_contact_phone'=> !empty($input['emergency_contact_phone']) ? trim($input['emergency_contact_phone']) : null,
                'address'                => $fullAddress,
                'address_line'           => $addressLine ?: null,
                'city'                   => $city ?: null,
                'state'                  => $state ?: null,
                'country'                => $country ?: 'India',
                'postal_code'            => $postalCode ?: null,
                'id_type'                => !empty($input['id_type']) ? trim($input['id_type']) : null,
                'id_number'              => !empty($input['id_number']) ? trim($input['id_number']) : null,
                'id_expiry'              => !empty($input['id_expiry']) ? date('Y-m-d', strtotime($input['id_expiry'])) : null,
                'id_document_file'       => $uploadedDocPath,
                'license_document_file'  => $uploadedLicensePath,
                'notes'                  => !empty($input['notes']) ? trim($input['notes']) : null,
                'created_at'             => date('Y-m-d H:i:s'),
                'updated_at'             => date('Y-m-d H:i:s'),
            ]);

            // If chauffeur/driver role or license number provided, ensure driver_profile exists
            if ($isDriverRole && $userId) {
                $licenseNum = !empty($input['license_number']) ? strtoupper(trim($input['license_number'])) : ('DL-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)));
                $licenseExp = !empty($input['license_expiry']) ? date('Y-m-d', strtotime($input['license_expiry'])) : date('Y-m-d', strtotime('+3 years'));
                $expYears   = isset($input['experience_years']) ? (int)$input['experience_years'] : 3;

                $existingProfile = $this->driverModel->findBy('user_id', $userId);
                if (!$existingProfile) {
                    $this->driverModel->create([
                        'user_id'               => $userId,
                        'employee_id'           => $employeeId,
                        'license_number'        => $licenseNum,
                        'license_expiry'        => $licenseExp,
                        'experience_years'      => $expYears,
                        'license_document_file' => $uploadedLicensePath,
                        'duty_status'           => 'off_duty',
                        'rating'                => 5.00,
                    ]);
                } else {
                    $this->driverModel->update($existingProfile['id'], [
                        'employee_id'           => $employeeId,
                        'license_number'        => $licenseNum,
                        'license_expiry'        => $licenseExp,
                        'experience_years'      => $expYears,
                        'license_document_file' => $uploadedLicensePath,
                    ]);
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

        $dispatches = [];
        if (!empty($employee['driver_profile_id'])) {
            try {
                $dispatchModel = new \App\Models\Dispatch();
                $dispatches = $dispatchModel->getDriverHistory((int)$employee['driver_profile_id']);
            } catch (\Throwable $e) {
                $dispatches = [];
            }
        }

        return $this->render('office/employees/show', [
            'employee'   => $employee,
            'dispatches' => $dispatches,
            'title'      => "Employee Profile - {$employee['first_name']} {$employee['last_name']}",
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

    /**
     * AJAX Toggle employee employment / leave status
     */
    public function toggleStatus(Request $request, array $params): Response
    {
        $id = (int)($params['id'] ?? 0);
        $employee = $this->employeeModel->find($id);

        if (!$employee) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Employee record not found.'], 404);
            }
            Session::setFlash('danger', 'Employee record not found.');
            return $this->redirect(url('/office/employees'));
        }

        try {
            $inputStatus = (string)($request->input('status') ?? '');
            if (in_array($inputStatus, ['active', 'on_leave', 'terminated'], true)) {
                $newStatus = $inputStatus;
            } else {
                $newStatus = ($employee['status'] === 'active') ? 'on_leave' : 'active';
            }
            $this->employeeModel->update($id, ['status' => $newStatus]);

            // Sync driver profile duty status if connected
            $driverModel = new \App\Models\DriverProfile();
            $driverProfile = $driverModel->findBy('employee_id', $id);
            if (!$driverProfile && !empty($employee['user_id'])) {
                $driverProfile = $driverModel->findBy('user_id', (int)$employee['user_id']);
            }
            if ($driverProfile) {
                $dutyStatus = ($newStatus === 'active') ? 'on_duty' : 'off_duty';
                $driverModel->update((int)$driverProfile['id'], ['duty_status' => $dutyStatus]);
            }

            // Sync linked user account status if connected
            if (!empty($employee['user_id'])) {
                $userStatus = ($newStatus === 'active') ? 'active' : 'inactive';
                $this->userModel->update((int)$employee['user_id'], ['status' => $userStatus]);
            }

            $this->auditLog->record(
                'employee_status_toggled',
                'employees',
                $id,
                ['status' => $employee['status']],
                ['status' => $newStatus],
                Session::getUserId()
            );

            $actionWord = ($newStatus === 'active') ? 'activated' : 'marked as on leave';
            $msg = "Employee {$employee['first_name']} {$employee['last_name']} has been {$actionWord} successfully.";

            if ($request->isAjax()) {
                $isActive = ($newStatus === 'active');
                return $this->json([
                    'success'   => true,
                    'status'    => $newStatus,
                    'is_active' => $isActive,
                    'label'     => $isActive ? 'Active' : 'On Leave',
                    'message'   => $msg,
                    'metrics'   => [
                        'total'      => $this->employeeModel->count(),
                        'active'     => $this->employeeModel->count("`status` = 'active'"),
                        'on_leave'   => $this->employeeModel->count("`status` = 'on_leave'"),
                        'terminated' => $this->employeeModel->count("`status` = 'terminated'"),
                        'chauffeurs' => $this->employeeModel->count("`department` = 'fleet_chauffeur' AND `status` = 'active'"),
                        'operations' => $this->employeeModel->count("`department` IN ('operations', 'dispatch') AND `status` = 'active'"),
                    ],
                ]);
            }

            Session::setFlash('success', $msg);
            $referer = $_SERVER['HTTP_REFERER'] ?? url('/office/employees');
            return $this->redirect($referer);
        } catch (\Throwable $e) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/office/employees'));
        }
    }

    /**
     * Delete employee profile and associated files & linked accounts
     */
    public function delete(Request $request, array|int|string $params): Response
    {
        $id = is_array($params) ? (int)($params['id'] ?? 0) : (int)$params;
        $employee = $this->employeeModel->find($id);

        if (!$employee) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Employee record not found.'], 404);
            }
            Session::setFlash('danger', 'Employee record not found.');
            return $this->redirect(url('/office/employees'));
        }

        try {
            $pdo = \App\Core\Database::getConnection();

            // 1. Delete associated physical files
            $this->deleteEmployeeAssociatedFiles($employee);

            // 2. Clean or dissociate linked driver profile
            $driverModel = new \App\Models\DriverProfile();
            $driver = $driverModel->findBy('employee_id', $id);
            if (!$driver && !empty($employee['user_id'])) {
                $driver = $driverModel->findBy('user_id', (int)$employee['user_id']);
            }
            if ($driver) {
                if (!empty($driver['license_document_file'])) {
                    $this->deletePhysicalFile($driver['license_document_file']);
                }
                $pdo->prepare("DELETE FROM `driver_mobile_devices` WHERE `driver_id` = ?")->execute([$driver['id']]);
                $pdo->prepare("DELETE FROM `gps_logs` WHERE `driver_id` = ?")->execute([$driver['id']]);
                $pdo->prepare("DELETE FROM `tracking_sessions` WHERE `driver_id` = ?")->execute([$driver['id']]);
                $pdo->prepare("UPDATE `dispatches` SET `driver_id` = NULL WHERE `driver_id` = ?")->execute([$driver['id']]);
                $driverModel->delete((int)$driver['id']);
            }

            // 3. Clean linked user account if not an admin
            if (!empty($employee['user_id'])) {
                $userId = (int)$employee['user_id'];
                $user = $this->userModel->find($userId);
                if ($user && ($user['role'] ?? '') !== 'admin') {
                    $pdo->prepare("UPDATE `employees` SET `user_id` = NULL WHERE `user_id` = ?")->execute([$userId]);
                    $pdo->prepare("UPDATE `dispatches` SET `assigned_by` = NULL WHERE `assigned_by` = ?")->execute([$userId]);
                    $pdo->prepare("UPDATE `trip_expenses` SET `logged_by` = NULL WHERE `logged_by` = ?")->execute([$userId]);
                    $pdo->prepare("UPDATE `trip_status_history` SET `changed_by_user_id` = NULL WHERE `changed_by_user_id` = ?")->execute([$userId]);
                    $pdo->prepare("UPDATE `vehicle_maintenance` SET `created_by` = NULL WHERE `created_by` = ?")->execute([$userId]);
                    $this->userModel->delete($userId);
                }
            }

            // 4. Delete the primary employee record
            $this->employeeModel->delete($id);

            // 5. Audit Log
            $this->auditLog->record(
                'employee_deleted',
                'employees',
                $id,
                $employee,
                null,
                Session::getUserId()
            );

            $fullName = trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''));
            $msg = "Employee {$fullName} has been permanently deleted.";

            if ($request->isAjax()) {
                return $this->json([
                    'success' => true,
                    'message' => $msg,
                    'metrics' => [
                        'total'      => $this->employeeModel->count(),
                        'active'     => $this->employeeModel->count("`status` = 'active'"),
                        'on_leave'   => $this->employeeModel->count("`status` = 'on_leave'"),
                        'terminated' => $this->employeeModel->count("`status` = 'terminated'"),
                        'chauffeurs' => $this->employeeModel->count("`department` = 'fleet_chauffeur' AND `status` = 'active'"),
                        'operations' => $this->employeeModel->count("`department` IN ('operations', 'dispatch') AND `status` = 'active'"),
                    ]
                ]);
            }

            Session::setFlash('success', $msg);
            return $this->redirect(url('/office/employees'));
        } catch (\Throwable $e) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Error deleting employee: ' . $e->getMessage()], 500);
            }
            Session::setFlash('danger', 'Error deleting employee: ' . $e->getMessage());
            return $this->redirect(url('/office/employees'));
        }
    }

    /**
     * Delete physical files associated with employee
     */
    private function deleteEmployeeAssociatedFiles(array $employee): void
    {
        $fileKeys = ['profile_photo', 'id_document_file', 'license_document_file'];
        foreach ($fileKeys as $key) {
            if (!empty($employee[$key]) && is_string($employee[$key])) {
                $this->deletePhysicalFile($employee[$key]);
            }
        }
    }

    /**
     * Delete physical file from all storage and public mirrors
     */
    private function deletePhysicalFile(string $pathVal): void
    {
        $projectRoot = dirname(__DIR__, 2);
        $cleaned = str_replace('\\', '/', $pathVal);
        $cleaned = preg_replace('#^(\.\./|/)+#', '', $cleaned);
        $cleaned = preg_replace('#^(assets/uploads/|storage/private_uploads/|storage/|public/storage/|public/assets/uploads/)#', '', $cleaned);

        $candidateLocations = [
            $projectRoot . '/storage/private_uploads/' . $cleaned,
            $projectRoot . '/public/assets/uploads/' . $cleaned,
            $projectRoot . '/public/storage/' . $cleaned,
            $projectRoot . '/storage/uploads/' . $cleaned,
            $projectRoot . '/public/' . ltrim($cleaned, '/'),
        ];

        if (file_exists($pathVal) && is_file($pathVal)) {
            $candidateLocations[] = $pathVal;
        }

        foreach ($candidateLocations as $filePath) {
            if (!empty($filePath) && file_exists($filePath) && is_file($filePath)) {
                try {
                    @unlink($filePath);
                } catch (\Throwable $e) {
                    // Ignore file deletion error
                }
            }
        }
    }

    /**
     * Process file upload supporting both multipart $_FILES and Base64 payloads with public mirroring
     */
    private function saveUploadedFileOrBase64(Request $request, array $input, string $fileKey, string $b64Key, string $subDir, array $allowedMimes): ?string
    {
        $savedRelPath = null;
        $projectRoot = dirname(__DIR__, 2);

        // 1. Genuine multipart file upload
        if ($request->hasFile($fileKey)) {
            try {
                $savedRelPath = $request->storeUploadedFile($fileKey, $subDir);
            } catch (\Throwable $e) {
                // Ignore upload failure
            }
        }

        // 2. Base64 fallback if file was serialized by client into hidden input
        $b64Val = $input[$b64Key] ?? null;
        if (empty($savedRelPath) && !empty($b64Val) && str_starts_with($b64Val, 'data:')) {
            try {
                $commaPos = strpos($b64Val, ',');
                if ($commaPos !== false) {
                    $header = substr($b64Val, 0, $commaPos);
                    $dataPart = substr($b64Val, $commaPos + 1);

                    if (preg_match('/^data:([a-zA-Z0-9\/\-\+\.]+);base64/i', $header, $m)) {
                        $mime = strtolower(trim($m[1]));
                        if ($mime === 'image/jpg') $mime = 'image/jpeg';
                        if ($mime === 'application/x-pdf') $mime = 'application/pdf';

                        if (isset($allowedMimes[$mime])) {
                            $ext = $allowedMimes[$mime];
                            $binary = base64_decode($dataPart);
                            if ($binary !== false && strlen($binary) > 0 && strlen($binary) <= 10485760) {
                                $prefix = str_contains($subDir, 'avatar') ? 'avatar_' : (str_contains($subDir, 'license') ? 'lic_' : 'doc_');
                                $filename = $prefix . bin2hex(random_bytes(16)) . '.' . $ext;
                                $savedRelPath = trim($subDir, '/') . '/' . $filename;

                                $targetDir = $projectRoot . '/public/storage/' . trim($subDir, '/');
                                if (!is_dir($targetDir)) @mkdir($targetDir, 0777, true);
                                file_put_contents($targetDir . '/' . $filename, $binary);
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Ignore fallback failure
            }
        }

        return $savedRelPath;
    }
}
