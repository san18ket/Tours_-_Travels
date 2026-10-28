<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\ValidationException;
use App\Models\User;
use App\Models\AuditLog;
use Throwable;

class UserController extends Controller
{
    private User $userModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->userModel = new User();
        $this->auditLog = new AuditLog();
    }

    /**
     * Display list of users with search, role/status filtering, and pagination
     */
    public function index(Request $request): Response
    {
        $search = trim($request->get('search', ''));
        $role = trim($request->get('role', ''));
        $status = trim($request->get('status', ''));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = in_array((int)$request->get('per_page'), [5, 10, 15, 20, 25, 50, 100], true) ? (int)$request->get('per_page') : 10;

        $totalUsers = $this->userModel->countFiltered($search, $role, $status);
        $users = $this->userModel->getPaginated($search, $role, $status, $page, $perPage);
        $totalPages = max(1, (int)ceil($totalUsers / $perPage));

        // Aggregate statistics for KPI pills
        $activeAdmins = $this->userModel->countActiveAdmins();
        $totalAll = $this->userModel->count();
        $totalDispatchers = $this->userModel->count("`role` = 'dispatcher' AND `status` = 'active'");
        $totalDrivers = $this->userModel->count("`role` = 'driver' AND `status` = 'active'");

        return $this->render('office/users/index', [
            'users'            => $users,
            'totalUsers'       => $totalUsers,
            'currentPage'      => $page,
            'totalPages'       => $totalPages,
            'perPage'          => $perPage,
            'search'           => $search,
            'selectedRole'     => $role,
            'selectedStatus'   => $status,
            'activeAdmins'     => $activeAdmins,
            'totalAll'         => $totalAll,
            'totalDispatchers' => $totalDispatchers,
            'totalDrivers'     => $totalDrivers,
            'currentUserId'    => (int)Session::getUserId(),
            'title'            => 'User Accounts & Access Control',
        ], 'layouts/office');
    }

    /**
     * Display form to add new user
     */
    public function create(Request $request): Response
    {
        $employeeModel = new \App\Models\Employee();
        $employees = $employeeModel->where("`status` = 'active'", [], 'first_name ASC, last_name ASC');

        $roleModel = new \App\Models\Role();
        $roles = $roleModel->where("`status` = 'active'", [], 'is_system DESC, name ASC');

        return $this->render('office/users/create', [
            'title'     => 'Create User Account',
            'employees' => $employees,
            'roles'     => $roles,
            'errors'    => Session::getFlash('form_errors') ?? [],
            'old'       => Session::getFlash('form_old') ?? [],
        ], 'layouts/office');
    }

    /**
     * Store new user account
     */
    public function store(Request $request): Response
    {
        try {
            $input = $request->all();

            $employeeId = !empty($input['employee_id']) ? (int)$input['employee_id'] : null;
            if ($employeeId) {
                $employeeModel = new \App\Models\Employee();
                $emp = $employeeModel->find($employeeId);
                if ($emp) {
                    if (empty($input['name'])) {
                        $input['name'] = trim($emp['first_name'] . ' ' . $emp['last_name']);
                    }
                    if (empty($input['email']) && !empty($emp['email'])) {
                        $input['email'] = $emp['email'];
                    }
                    if (empty($input['phone']) && !empty($emp['phone'])) {
                        $input['phone'] = $emp['phone'];
                    }
                }
            }

            if (empty($input['role'])) {
                $input['role'] = 'driver';
            }

            $roleModel = new \App\Models\Role();
            $validRoles = array_column($roleModel->all(), 'slug');
            if (empty($validRoles)) {
                $validRoles = ['admin', 'dispatcher', 'driver', 'customer'];
            }
            if (!in_array('driver', $validRoles, true)) {
                $validRoles[] = 'driver';
            }

            $data = $this->validate($input, [
                'name'     => 'required|min:2|max:100',
                'username' => 'required|min:3|max:50',
                'email'    => 'required|email|max:150',
                'role'     => 'required|in:' . implode(',', $validRoles),
                'status'   => 'required|in:active,inactive',
                'password' => 'required|min:6',
            ]);

            $username = strtolower(trim($data['username']));
            if (!preg_match('/^[a-zA-Z0-9_.]+$/', $username)) {
                throw new ValidationException('Username can only contain alphanumeric characters, underscores, and periods.', ['username' => ['Username can only contain letters, numbers, underscores, and periods.']]);
            }

            // Confirm password check
            $confirmPassword = $input['password_confirmation'] ?? '';
            if ($data['password'] !== $confirmPassword) {
                throw new ValidationException('Password confirmation does not match.', ['password_confirmation' => ['Password confirmation does not match.']]);
            }

            // Check duplicate username
            if ($this->userModel->findByUsername($username)) {
                throw new ValidationException("The username '{$username}' is already taken.", ['username' => ['This username is already taken.']]);
            }

            // Check duplicate email
            if ($this->userModel->findByEmail($data['email'])) {
                throw new ValidationException("The email '{$data['email']}' is already registered.", ['email' => ['This email address is already registered.']]);
            }

            $selectedLanguage = in_array($input['language'] ?? '', ['en', 'hi', 'mr'], true)
                ? $input['language']
                : \App\Core\Language::getLocale();

            $newUserId = $this->userModel->create([
                'name'       => $data['name'],
                'username'   => $username,
                'email'      => strtolower(trim($data['email'])),
                'phone'      => trim($input['phone'] ?? ''),
                'password'   => $passwordHash,
                'role'       => $data['role'],
                'status'     => $data['status'],
                'language'   => $selectedLanguage,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            // Link employee record to newly created user account
            if ($employeeId) {
                $employeeModel = new \App\Models\Employee();
                $employeeModel->update($employeeId, [
                    'user_id'    => $newUserId,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            // Audit log without password hashes
            $this->auditLog->record(
                'user_created',
                'users',
                $newUserId,
                null,
                [
                    'name'     => $data['name'],
                    'username' => $username,
                    'email'    => strtolower(trim($data['email'])),
                    'role'     => $data['role'],
                    'status'   => $data['status'],
                ],
                Session::getUserId()
            );

            return $this->redirect(url('/office/users'), 'success', "User account '{$username}' successfully created.");
        } catch (ValidationException $e) {
            Session::setFlash('danger', $e->getMessage());
            Session::setFlash('form_errors', $e->getErrors());
            Session::setFlash('form_old', $request->all());
            return $this->redirect(url('/office/users/create'));
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            Session::setFlash('form_old', $request->all());
            return $this->redirect(url('/office/users/create'));
        }
    }

    /**
     * Display edit form for existing user
     */
    public function edit(Request $request, array $params = []): Response
    {
        $id = (int)($params['id'] ?? 0);
        $user = $this->userModel->find($id);

        if (!$user) {
            return $this->redirect(url('/office/users'), 'danger', 'Requested user account was not found.');
        }

        return $this->render('office/users/edit', [
            'user'          => $user,
            'isSelf'        => ($id === (int)Session::getUserId()),
            'isLastAdmin'   => $this->userModel->isLastActiveAdmin($id),
            'errors'        => Session::getFlash('form_errors') ?? [],
            'title'         => "Edit User: {$user['name']} (@{$user['username']})",
        ], 'layouts/office');
    }

    /**
     * Update user details
     */
    public function update(Request $request, array $params = []): Response
    {
        $id = (int)($params['id'] ?? 0);
        $user = $this->userModel->find($id);

        if (!$user) {
            return $this->redirect(url('/office/users'), 'danger', 'Requested user was not found.');
        }

        try {
            $input = $request->all();

            $data = $this->validate($input, [
                'name'     => 'required|min:2|max:100',
                'username' => 'required|min:3|max:50',
                'email'    => 'required|email|max:150',
                'role'     => 'required|in:admin,dispatcher,driver,customer',
                'status'   => 'required|in:active,inactive',
            ]);

            $newUsername = strtolower(trim($data['username']));
            if (!preg_match('/^[a-zA-Z0-9_.]+$/', $newUsername)) {
                throw new ValidationException('Username can only contain alphanumeric characters, underscores, and periods.', ['username' => ['Invalid username format.']]);
            }

            // Check duplicate username if changed
            if ($newUsername !== strtolower($user['username'])) {
                $existing = $this->userModel->findByUsername($newUsername);
                if ($existing && (int)$existing['id'] !== $id) {
                    throw new ValidationException("The username '{$newUsername}' is already taken.", ['username' => ['This username is already taken.']]);
                }
            }

            // Check duplicate email if changed
            $newEmail = strtolower(trim($data['email']));
            if ($newEmail !== strtolower($user['email'])) {
                $existing = $this->userModel->findByEmail($newEmail);
                if ($existing && (int)$existing['id'] !== $id) {
                    throw new ValidationException("The email '{$newEmail}' is already registered.", ['email' => ['This email is already registered.']]);
                }
            }

            // Security: Prevent deactivation of own logged-in account
            $currentUserId = (int)Session::getUserId();
            if ($id === $currentUserId && $data['status'] !== 'active') {
                throw new \RuntimeException("You cannot deactivate your own logged-in account.");
            }

            // Security: Prevent demotion or deactivation of the last active administrator
            if ($this->userModel->isLastActiveAdmin($id)) {
                if ($data['status'] !== 'active') {
                    throw new \RuntimeException("Cannot deactivate the only remaining active administrator.");
                }
                if ($data['role'] !== 'admin') {
                    throw new \RuntimeException("Cannot demote the only remaining active administrator.");
                }
            }

            $selectedLanguage = in_array($input['language'] ?? '', ['en', 'hi', 'mr'], true)
                ? $input['language']
                : ($user['language'] ?? 'en');

            $updateData = [
                'name'       => $data['name'],
                'username'   => $newUsername,
                'email'      => $newEmail,
                'phone'      => trim($input['phone'] ?? ''),
                'role'       => $data['role'],
                'status'     => $data['status'],
                'language'   => $selectedLanguage,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $this->userModel->update($id, $updateData);

            // Audit log without passwords
            $this->auditLog->record(
                'user_updated',
                'users',
                $id,
                [
                    'name'     => $user['name'],
                    'username' => $user['username'],
                    'email'    => $user['email'],
                    'role'     => $user['role'],
                    'status'   => $user['status'],
                ],
                $updateData,
                $currentUserId
            );

            // Update session if editing own profile
            if ($id === $currentUserId) {
                $currentUser = Session::getUser();
                $currentUser['name'] = $data['name'];
                $currentUser['username'] = $newUsername;
                $currentUser['email'] = $newEmail;
                Session::set('user', $currentUser);
            }

            return $this->redirect(url('/office/users'), 'success', "User '{$newUsername}' updated successfully.");
        } catch (ValidationException $e) {
            Session::setFlash('danger', $e->getMessage());
            Session::setFlash('form_errors', $e->getErrors());
            return $this->redirect(url("/office/users/{$id}/edit"));
        } catch (Throwable $e) {
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url("/office/users/{$id}/edit"));
        }
    }

    /**
     * Soft toggle user status (active <-> inactive)
     */
    public function toggleStatus(Request $request, array $params = []): Response
    {
        $id = (int)($params['id'] ?? 0);
        $user = $this->userModel->find($id);

        if (!$user) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Requested user not found.'], 404);
            }
            return $this->redirect(url('/office/users'), 'danger', 'Requested user not found.');
        }

        $currentUserId = (int)Session::getUserId();

        // Prevent self-deactivation
        if ($id === $currentUserId) {
            $msg = 'You cannot deactivate your own logged-in account.';
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $msg], 422);
            }
            return $this->redirect(url('/office/users'), 'danger', $msg);
        }

        // Prevent deactivating the last active admin
        if ($user['status'] === 'active' && $this->userModel->isLastActiveAdmin($id)) {
            $msg = 'Cannot deactivate the only remaining active administrator.';
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $msg], 422);
            }
            return $this->redirect(url('/office/users'), 'danger', $msg);
        }

        try {
            $newStatus = $this->userModel->toggleStatus($id);

            $this->auditLog->record(
                'user_status_toggled',
                'users',
                $id,
                ['old_status' => $user['status']],
                ['new_status' => $newStatus],
                $currentUserId
            );

            $actionWord = ($newStatus === 'active') ? 'activated' : 'deactivated';
            $msg = "User '{$user['username']}' has been {$actionWord}.";

            if ($request->isAjax()) {
                $isActive = ($newStatus === 'active');
                return $this->json([
                    'success'   => true,
                    'status'    => $newStatus,
                    'is_active' => $isActive,
                    'label'     => ucfirst($newStatus),
                    'message'   => $msg,
                    'metrics'   => [
                        'total'       => $this->userModel->count(),
                        'admins'      => $this->userModel->countActiveAdmins(),
                        'dispatchers' => $this->userModel->count("`role` = 'dispatcher' AND `status` = 'active'"),
                        'drivers'     => $this->userModel->count("`role` = 'driver' AND `status` = 'active'"),
                    ],
                ]);
            }

            return $this->redirect(url('/office/users'), 'success', $msg);
        } catch (Throwable $e) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            return $this->redirect(url('/office/users'), 'danger', $e->getMessage());
        }
    }

    /**
     * Reset password for user
     */
    public function resetPassword(Request $request, array $params = []): Response
    {
        $id = (int)($params['id'] ?? 0);
        $user = $this->userModel->find($id);

        if (!$user) {
            return $this->redirect(url('/office/users'), 'danger', 'Requested user not found.');
        }

        try {
            $input = $request->all();
            $data = $this->validate($input, [
                'password' => 'required|min:6',
            ]);

            $confirm = $input['password_confirmation'] ?? '';
            if ($data['password'] !== $confirm) {
                throw new ValidationException('Password confirmation does not match.');
            }

            $newHash = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $this->userModel->updatePassword($id, $newHash);

            // Audit logging without exposing password or hash
            $this->auditLog->record(
                'user_password_reset',
                'users',
                $id,
                null,
                ['target_username' => $user['username']],
                Session::getUserId()
            );

            return $this->redirect(url('/office/users'), 'success', "Password for user '{$user['username']}' has been successfully reset.");
        } catch (ValidationException $e) {
            return $this->redirect(url('/office/users'), 'danger', $e->getMessage());
        } catch (Throwable $e) {
            return $this->redirect(url('/office/users'), 'danger', $e->getMessage());
        }
    }

    /**
     * Display Roles & Permissions access matrix
     */
    public function rolesMatrix(Request $request): Response
    {
        $matrix = [
            'Dashboard' => [
                'admin'      => ['label' => 'Full Access', 'badge' => 'badge-success', 'desc' => 'All operational KPIs, financials, audits, and real-time alerts'],
                'dispatcher' => ['label' => 'Operations Access', 'badge' => 'badge-success', 'desc' => 'Active bookings, fleet readiness, and dispatch metrics'],
                'driver'     => ['label' => 'Restricted', 'badge' => 'badge-neutral', 'desc' => 'Redirects to mobile duty portal (/driver/duty)'],
                'customer'   => ['label' => 'No Access', 'badge' => 'badge-danger', 'desc' => 'Blocked with 403 Forbidden'],
            ],
            'Customers' => [
                'admin'      => ['label' => 'Full CRUD', 'badge' => 'badge-success', 'desc' => 'Create profiles, edit contact records, review booking histories'],
                'dispatcher' => ['label' => 'Read & Create', 'badge' => 'badge-info', 'desc' => 'Search directory, register new guests, view itineraries'],
                'driver'     => ['label' => 'No Access', 'badge' => 'badge-neutral', 'desc' => 'Passenger contacts provided strictly via assigned trip manifest'],
                'customer'   => ['label' => 'No Access', 'badge' => 'badge-danger', 'desc' => 'Direct staff directory blocked'],
            ],
            'Bookings' => [
                'admin'      => ['label' => 'Full Management', 'badge' => 'badge-success', 'desc' => 'Create, modify pricing/discounts, reassign, status overrides'],
                'dispatcher' => ['label' => 'Manage & Assign', 'badge' => 'badge-info', 'desc' => 'Create reservations, calculate GST fares, initiate dispatch'],
                'driver'     => ['label' => 'Assigned Duty Only', 'badge' => 'badge-warning', 'desc' => 'Read-only manifest of assigned trip duties via PWA'],
                'customer'   => ['label' => 'Own Bookings', 'badge' => 'badge-info', 'desc' => 'View personal travel itinerary history'],
            ],
            'Employees' => [
                'admin'      => ['label' => 'Full HR Control', 'badge' => 'badge-success', 'desc' => 'Add staff, edit designations, configure chauffeur links, terminate'],
                'dispatcher' => ['label' => 'View Only', 'badge' => 'badge-neutral', 'desc' => 'Roster lookup for operations assignment coordination'],
                'driver'     => ['label' => 'No Access', 'badge' => 'badge-neutral', 'desc' => 'Blocked from office employee records'],
                'customer'   => ['label' => 'No Access', 'badge' => 'badge-danger', 'desc' => 'Blocked with 403 Forbidden'],
            ],
            'Vehicles' => [
                'admin'      => ['label' => 'Full Fleet CRUD', 'badge' => 'badge-success', 'desc' => 'Register vehicles, update specs, manage compliance & retirement'],
                'dispatcher' => ['label' => 'Status & Readiness', 'badge' => 'badge-info', 'desc' => 'Flag for scheduled maintenance, verify odometer and readiness'],
                'driver'     => ['label' => 'Assigned Vehicle', 'badge' => 'badge-warning', 'desc' => 'Pre-trip odometer verification and inspection logging'],
                'customer'   => ['label' => 'No Access', 'badge' => 'badge-danger', 'desc' => 'Blocked from internal vehicle roster'],
            ],
            'Access Control' => [
                'admin'      => ['label' => 'Full Authority', 'badge' => 'badge-success', 'desc' => 'Create staff users, toggle active status, reset credentials'],
                'dispatcher' => ['label' => 'No Access', 'badge' => 'badge-danger', 'desc' => 'Blocked with 403 Forbidden'],
                'driver'     => ['label' => 'No Access', 'badge' => 'badge-danger', 'desc' => 'Blocked with 403 Forbidden'],
                'customer'   => ['label' => 'No Access', 'badge' => 'badge-danger', 'desc' => 'Blocked with 403 Forbidden'],
            ],
            'Trip Operations' => [
                'admin'      => ['label' => 'Full Authority', 'badge' => 'badge-success', 'desc' => 'Assign dispatches, override status transitions, audit completions'],
                'dispatcher' => ['label' => 'Dispatch & History', 'badge' => 'badge-info', 'desc' => 'Deploy available fleet & chauffeurs, monitor active routes'],
                'driver'     => ['label' => 'Trip Execution', 'badge' => 'badge-warning', 'desc' => 'Accept assignments, log odometers, commence & complete trips'],
                'customer'   => ['label' => 'No Access', 'badge' => 'badge-danger', 'desc' => 'Direct dispatch operations blocked'],
            ],
            'Live Tracking' => [
                'admin'      => ['label' => 'Live Radar & Logs', 'badge' => 'badge-success', 'desc' => 'Real-time vehicle map, telemetry stream, stale indicators'],
                'dispatcher' => ['label' => 'Live Radar Monitor', 'badge' => 'badge-info', 'desc' => 'Active route tracking, speed & heading telemetry'],
                'driver'     => ['label' => 'Telemetry Sender', 'badge' => 'badge-warning', 'desc' => 'Transmits high-accuracy GPS coordinates during active trip only'],
                'customer'   => ['label' => 'No Access', 'badge' => 'badge-danger', 'desc' => 'Raw internal telemetry blocked for privacy compliance'],
            ],
        ];

        return $this->render('office/users/roles', [
            'matrix' => $matrix,
            'title'  => 'Roles & Permissions Access Matrix',
        ], 'layouts/office');
    }
}

