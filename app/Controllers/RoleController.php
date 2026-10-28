<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use App\Core\ValidationException;
use App\Models\Role;
use App\Models\AuditLog;
use App\Services\PermissionRegistry;
use PDO;
use Throwable;

class RoleController extends Controller
{
    private Role $roleModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->roleModel = new Role();
        $this->auditLog = new AuditLog();
    }

    /**
     * Display Role List with top metric cards, filters, and paginated table
     */
    public function index(Request $request): Response
    {
        $search = trim((string)$request->input('search', ''));
        $status = trim((string)$request->input('status', ''));
        $page = max(1, (int)$request->input('page', 1));
        $perPage = in_array((int)$request->input('per_page'), [10, 25, 50], true) ? (int)$request->input('per_page') : 10;

        $totalRecords = $this->roleModel->countFiltered($search, $status);
        $roles = $this->roleModel->getPaginated($search, $status, $page, $perPage);
        $metrics = $this->roleModel->getMetrics();
        $totalPages = max(1, (int)ceil($totalRecords / $perPage));

        return $this->render('office/roles/index', [
            'roles'        => $roles,
            'metrics'      => $metrics,
            'search'       => $search,
            'status'       => $status,
            'currentPage'  => $page,
            'totalPages'   => $totalPages,
            'totalRecords' => $totalRecords,
            'perPage'      => $perPage,
            'title'        => 'Role List & Operational Access',
        ], 'layouts/office');
    }

    /**
     * Store new operational role in database
     */
    public function store(Request $request): Response
    {
        try {
            $input = $request->all();

            $data = $this->validate($input, [
                'name' => 'required|min:2|max:80',
            ]);

            $name = trim($data['name']);

            if ($this->roleModel->isRoleNameTaken($name)) {
                throw new ValidationException("A role with the name '{$name}' already exists.", [
                    'name' => ['Role name already exists. Please choose a distinct title.']
                ]);
            }

            $slug = $this->roleModel->generateSlug($name);
            $description = !empty($input['description']) ? trim((string)$input['description']) : null;
            $now = date('Y-m-d H:i:s');

            $roleId = $this->roleModel->create([
                'name'        => $name,
                'slug'        => $slug,
                'description' => $description,
                'is_system'   => 0,
                'status'      => 'active',
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            // Record audit log
            $this->auditLog->record(
                'role_created',
                'roles',
                $roleId,
                null,
                [
                    'name' => $name,
                    'slug' => $slug,
                ],
                Session::getUserId()
            );

            if ($request->isJson() || $request->wantsJson()) {
                return $this->json([
                    'success' => true,
                    'message' => "Role '{$name}' successfully created.",
                    'role_id' => $roleId,
                    'slug'    => $slug,
                ]);
            }

            Session::setFlash('success', "Role '{$name}' (Key: {$slug}) successfully created.");
            return $this->redirect(url('/office/roles'));
        } catch (ValidationException $e) {
            if ($request->isJson() || $request->wantsJson()) {
                return $this->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->getErrors()], 422);
            }
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/office/roles'));
        } catch (Throwable $e) {
            if ($request->isJson() || $request->wantsJson()) {
                return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            Session::setFlash('danger', 'Failed to create role: ' . $e->getMessage());
            return $this->redirect(url('/office/roles'));
        }
    }

    /**
     * Update existing role
     */
    public function update(Request $request, array $params = []): Response
    {
        $roleId = (int)($params['id'] ?? $request->input('id', 0));
        $role = $this->roleModel->find($roleId);

        if (!$role) {
            if ($request->isJson() || $request->wantsJson()) {
                return $this->json(['success' => false, 'message' => 'Role not found.'], 404);
            }
            Session::setFlash('danger', 'Role not found.');
            return $this->redirect(url('/office/roles'));
        }

        try {
            $input = $request->all();
            $data = $this->validate($input, [
                'name' => 'required|min:2|max:80',
            ]);

            $name = trim($data['name']);
            if ($this->roleModel->isRoleNameTaken($name, $roleId)) {
                throw new ValidationException("Role name '{$name}' is already taken by another role.", [
                    'name' => ['Role name already in use.']
                ]);
            }

            $updateData = [
                'description' => trim((string)($input['description'] ?? '')),
                'updated_at'  => date('Y-m-d H:i:s'),
            ];

            // Only allow changing name if not a system role
            if (!(bool)$role['is_system']) {
                $updateData['name'] = $name;
            }

            $this->roleModel->update($roleId, $updateData);

            $this->auditLog->record(
                'role_updated',
                'roles',
                $roleId,
                $role,
                $updateData,
                Session::getUserId()
            );

            if ($request->isJson() || $request->wantsJson()) {
                return $this->json(['success' => true, 'message' => 'Role updated successfully.']);
            }

            Session::setFlash('success', 'Role updated successfully.');
            return $this->redirect(url('/office/roles'));
        } catch (ValidationException $e) {
            if ($request->isJson() || $request->wantsJson()) {
                return $this->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->getErrors()], 422);
            }
            Session::setFlash('danger', $e->getMessage());
            return $this->redirect(url('/office/roles'));
        } catch (Throwable $e) {
            if ($request->isJson() || $request->wantsJson()) {
                return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            Session::setFlash('danger', 'Failed to update role: ' . $e->getMessage());
            return $this->redirect(url('/office/roles'));
        }
    }

    /**
     * Delete custom role
     */
    public function destroy(Request $request, array $params = []): Response
    {
        $roleId = (int)($params['id'] ?? $request->input('id', 0));
        $role = $this->roleModel->getRoleWithStats($roleId);

        if (!$role) {
            if ($request->isJson() || $request->wantsJson()) {
                return $this->json(['success' => false, 'message' => 'Role not found.'], 404);
            }
            Session::setFlash('danger', 'Role not found.');
            return $this->redirect(url('/office/roles'));
        }

        if ((bool)$role['is_system'] || in_array(strtolower($role['slug']), ['admin', 'owner', 'superadmin'], true)) {
            $msg = 'Core system roles cannot be deleted!';
            if ($request->isJson() || $request->wantsJson()) {
                return $this->json(['success' => false, 'message' => $msg], 403);
            }
            Session::setFlash('danger', $msg);
            return $this->redirect(url('/office/roles'));
        }

        if ((int)$role['user_count'] > 0) {
            $msg = "Cannot delete role! There are {$role['user_count']} users currently assigned to this role.";
            if ($request->isJson() || $request->wantsJson()) {
                return $this->json(['success' => false, 'message' => $msg], 400);
            }
            Session::setFlash('warning', $msg);
            return $this->redirect(url('/office/roles'));
        }

        $this->roleModel->delete($roleId);

        $this->auditLog->record(
            'role_deleted',
            'roles',
            $roleId,
            $role,
            null,
            Session::getUserId()
        );

        if ($request->isJson() || $request->wantsJson()) {
            return $this->json(['success' => true, 'message' => 'Role deleted successfully.']);
        }

        Session::setFlash('success', 'Role deleted successfully.');
        return $this->redirect(url('/office/roles'));
    }

    /**
     * Role & Permission Overview Directory
     */
    public function permissionIndex(Request $request): Response
    {
        $search = trim((string)$request->input('search', ''));
        $type = trim((string)$request->input('type', ''));
        $page = max(1, (int)$request->input('page', 1));
        $perPage = in_array((int)$request->input('per_page'), [10, 25, 50], true) ? (int)$request->input('per_page') : 10;

        $total = $this->roleModel->countFiltered($search, null, $type);
        $roles = $this->roleModel->getPaginated($search, null, $page, $perPage, $type);
        $metrics = $this->roleModel->getMetrics();

        return $this->render('office/role_permissions/index', [
            'roles'        => $roles,
            'total'        => $total,
            'limit'        => $perPage,
            'page'         => $page,
            'search'       => $search,
            'type'         => $type,
            'metrics'      => $metrics,
            'title'        => 'Role & Permission Directory',
        ], 'layouts/office');
    }

    /**
     * Permission Assignment Matrix Editor
     */
    public function permissionCreate(Request $request, array $params = []): Response
    {
        $pdo = Database::getConnection();
        $scopeMode = (string)$request->input('scope', 'role');
        $roleId = (int)($params['id'] ?? $request->input('role_id', 0));
        $userId = (int)$request->input('user_id', 0);

        if ($userId > 0 && !$request->input('scope')) {
            $scopeMode = 'user';
        }

        $roles = $this->roleModel->getAssignableRoles();
        if ($roleId === 0 && !empty($roles) && $scopeMode === 'role') {
            $roleId = (int)$roles[0]['id'];
        }

        $role = $roleId > 0 ? $this->roleModel->getRoleWithStats($roleId) : null;

        // Fetch users for user scope
        $users = $pdo->query("SELECT u.id, u.username, u.email, u.name, u.role, r.name AS role_name 
                              FROM `users` u 
                              LEFT JOIN `roles` r ON u.role = r.slug 
                              WHERE u.status = 'active' 
                              ORDER BY u.name ASC, u.username ASC")->fetchAll(PDO::FETCH_ASSOC);

        $selectedUser = null;
        if ($userId > 0) {
            foreach ($users as $u) {
                if ((int)$u['id'] === $userId) {
                    $selectedUser = $u;
                    break;
                }
            }
        }

        $catalogue = PermissionRegistry::getCatalogueWithPermissionIds($pdo);

        $isOwnerRole = false;
        $selectedPermissionIds = [];

        if ($scopeMode === 'user' && $selectedUser) {
            $isOwnerRole = strtolower((string)($selectedUser['role'] ?? '')) === 'admin' || strtolower((string)$selectedUser['username']) === 'owner';
            if ($isOwnerRole) {
                $allPerms = $pdo->query("SELECT id FROM permissions")->fetchAll(PDO::FETCH_COLUMN);
                $selectedPermissionIds = array_map('intval', $allPerms);
            } else {
                // Find role ID from user's role slug
                $userRoleSlug = $selectedUser['role'] ?? '';
                $userRoleStmt = $pdo->prepare("SELECT id FROM `roles` WHERE `slug` = ? LIMIT 1");
                $userRoleStmt->execute([$userRoleSlug]);
                $userRoleId = (int)$userRoleStmt->fetchColumn();

                $inheritedPermIds = [];
                if ($userRoleId > 0) {
                    $inheritedPermIds = $this->roleModel->getPermissionIdsForRole($userRoleId);
                }

                $userOverrides = $pdo->prepare("SELECT permission_id, is_granted FROM `user_permissions` WHERE `user_id` = ? AND (expires_at IS NULL OR expires_at > NOW())");
                $userOverrides->execute([$selectedUser['id']]);
                $overrides = $userOverrides->fetchAll(PDO::FETCH_ASSOC);

                $granted = [];
                $revoked = [];
                foreach ($overrides as $ov) {
                    if ((int)$ov['is_granted'] === 1) {
                        $granted[] = (int)$ov['permission_id'];
                    } else {
                        $revoked[] = (int)$ov['permission_id'];
                    }
                }

                $selectedPermissionIds = array_values(array_diff(array_unique(array_merge($inheritedPermIds, $granted)), $revoked));
            }
        } elseif ($role) {
            $isOwnerRole = strtolower((string)$role['slug']) === 'admin';
            if ($isOwnerRole) {
                $allPerms = $pdo->query("SELECT id FROM permissions")->fetchAll(PDO::FETCH_COLUMN);
                $selectedPermissionIds = array_map('intval', $allPerms);
            } else {
                $selectedPermissionIds = $this->roleModel->getPermissionIdsForRole($roleId);
            }
        }

        $selectedPermissionNames = [];
        if (!empty($selectedPermissionIds)) {
            $placeholders = implode(',', array_fill(0, count($selectedPermissionIds), '?'));
            $permNameStmt = $pdo->prepare("SELECT name FROM `permissions` WHERE id IN ({$placeholders})");
            $permNameStmt->execute($selectedPermissionIds);
            $selectedPermissionNames = $permNameStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        }

        return $this->render('office/role_permissions/assign', [
            'scopeMode'               => $scopeMode,
            'roles'                   => $roles,
            'role'                    => $role,
            'users'                   => $users,
            'user'                    => $selectedUser,
            'isOwnerRole'             => $isOwnerRole,
            'catalogue'               => $catalogue,
            'selectedPermissionIds'   => $selectedPermissionIds,
            'selectedPermissionNames' => $selectedPermissionNames,
            'title'                   => 'Role Permission Assignment Matrix',
        ], 'layouts/office');
    }

    /**
     * AJAX endpoint to fetch permissions and catalogue for a role
     */
    public function permissionOptions(Request $request, array $params = []): Response
    {
        $roleId = (int)($params['id'] ?? $request->input('id', 0));
        $role = $this->roleModel->getRoleWithStats($roleId);

        if (!$role) {
            return $this->json(['message' => 'Role not found.'], 404);
        }

        $pdo = Database::getConnection();
        $isOwner = strtolower((string)$role['slug']) === 'admin';

        if ($isOwner) {
            $allPerms = $pdo->query("SELECT id FROM permissions")->fetchAll(PDO::FETCH_COLUMN);
            $selected = array_map('intval', $allPerms);
        } else {
            $selected = $this->roleModel->getPermissionIdsForRole($roleId);
        }

        return $this->json([
            'role' => [
                'id'               => (int)$role['id'],
                'name'             => (string)$role['name'],
                'slug'             => (string)$role['slug'],
                'description'      => (string)($role['description'] ?? ''),
                'is_system'        => (int)($role['is_system'] ?? 0),
                'user_count'       => (int)($role['user_count'] ?? 0),
                'permission_count' => count($selected),
                'is_owner'         => $isOwner,
            ],
            'is_owner'   => $isOwner,
            'catalogue'  => PermissionRegistry::getCatalogueWithPermissionIds($pdo),
            'selected'   => $selected,
        ]);
    }

    /**
     * AJAX endpoint to fetch permissions for a user
     */
    public function userPermissionOptions(Request $request, array $params = []): Response
    {
        $userId = (int)($params['id'] ?? $request->input('id', 0));
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("SELECT u.*, r.name AS role_name FROM `users` u LEFT JOIN `roles` r ON u.role = r.slug WHERE u.id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return $this->json(['message' => 'User not found.'], 404);
        }

        $isOwner = strtolower((string)($user['role'] ?? '')) === 'admin' || strtolower((string)$user['username']) === 'owner';

        if ($isOwner) {
            $allPerms = $pdo->query("SELECT id FROM permissions")->fetchAll(PDO::FETCH_COLUMN);
            $selected = array_map('intval', $allPerms);
            $rolePermIds = $selected;
        } else {
            $userRoleStmt = $pdo->prepare("SELECT id FROM `roles` WHERE `slug` = ? LIMIT 1");
            $userRoleStmt->execute([$user['role'] ?? '']);
            $userRoleId = (int)$userRoleStmt->fetchColumn();

            $rolePermIds = $userRoleId > 0 ? $this->roleModel->getPermissionIdsForRole($userRoleId) : [];

            $userOverridesStmt = $pdo->prepare("SELECT permission_id, is_granted FROM `user_permissions` WHERE `user_id` = ? AND (expires_at IS NULL OR expires_at > NOW())");
            $userOverridesStmt->execute([$userId]);
            $overrides = $userOverridesStmt->fetchAll(PDO::FETCH_ASSOC);

            $granted = [];
            $revoked = [];
            foreach ($overrides as $ov) {
                if ((int)$ov['is_granted'] === 1) {
                    $granted[] = (int)$ov['permission_id'];
                } else {
                    $revoked[] = (int)$ov['permission_id'];
                }
            }

            $selected = array_values(array_diff(array_unique(array_merge($rolePermIds, $granted)), $revoked));
        }

        return $this->json([
            'user' => [
                'id'               => (int)$user['id'],
                'username'         => (string)$user['username'],
                'name'             => (string)($user['name'] ?: $user['username']),
                'role'             => (string)($user['role'] ?? 'customer'),
                'role_name'        => (string)($user['role_name'] ?? 'Customer'),
                'email'            => (string)$user['email'],
                'is_owner'         => $isOwner,
                'permission_count' => count($selected),
            ],
            'is_owner'         => $isOwner,
            'catalogue'        => PermissionRegistry::getCatalogueWithPermissionIds($pdo),
            'selected'         => $selected,
            'role_permissions' => $rolePermIds,
        ]);
    }

    /**
     * Store permission mappings for role or user
     */
    public function permissionStore(Request $request): Response
    {
        $data = $request->all();
        $isAjax = $request->isJson() || $request->wantsJson() || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
        $scopeMode = $data['scope_mode'] ?? 'role';

        $pdo = Database::getConnection();

        if ($scopeMode === 'user') {
            $userId = (int)($data['user_id'] ?? 0);
            $userStmt = $pdo->prepare("SELECT * FROM `users` WHERE id = ? LIMIT 1");
            $userStmt->execute([$userId]);
            $user = $userStmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                if ($isAjax) {
                    return $this->json(['status' => 'error', 'message' => 'Please select a valid user.'], 400);
                }
                Session::setFlash('danger', 'Please select a valid user.');
                return $this->redirect(url('/office/role-permission/assign?scope=user'));
            }

            $isOwner = strtolower((string)$user['role']) === 'admin' || strtolower((string)$user['username']) === 'owner';
            if ($isOwner) {
                if ($isAjax) {
                    return $this->json(['status' => 'success', 'message' => 'Administrator has full master access by default.']);
                }
                Session::setFlash('info', 'Administrator has full master access by default.');
                return $this->redirect(url('/office/role-permission'));
            }

            $rawPermissions = is_array($data['permissions'] ?? null) ? $data['permissions'] : [];
            $permissionIds = PermissionRegistry::resolvePermissionIds($pdo, $rawPermissions);

            $pdo->beginTransaction();
            try {
                $del = $pdo->prepare("DELETE FROM `user_permissions` WHERE user_id = ?");
                $del->execute([$userId]);

                $ins = $pdo->prepare("INSERT INTO `user_permissions` (`user_id`, `permission_id`, `is_granted`, `created_at`) VALUES (?, ?, 1, CURRENT_TIMESTAMP)");
                foreach ($permissionIds as $pId) {
                    $ins->execute([$userId, $pId]);
                }
                $pdo->commit();
            } catch (Throwable $e) {
                $pdo->rollBack();
                if ($isAjax) {
                    return $this->json(['status' => 'error', 'message' => 'Failed to save user permissions: ' . $e->getMessage()], 500);
                }
                Session::setFlash('danger', 'Failed to save user permissions: ' . $e->getMessage());
                return $this->redirect(url('/office/role-permission'));
            }

            $this->auditLog->record(
                'user_permissions_updated',
                'users',
                $userId,
                null,
                ['count' => count($permissionIds)],
                Session::getUserId()
            );

            if ($isAjax) {
                return $this->json([
                    'status'  => 'success',
                    'message' => "Permissions for user '{$user['username']}' saved successfully (" . count($permissionIds) . " granted).",
                    'count'   => count($permissionIds),
                ]);
            }

            Session::setFlash('success', "Permissions for user '{$user['username']}' updated successfully.");
            return $this->redirect(url('/office/role-permission'));
        }

        // Role scope
        $roleId = (int)($data['role_id'] ?? 0);
        $role = $this->roleModel->getRoleWithStats($roleId);

        if (!$role) {
            if ($isAjax) {
                return $this->json(['status' => 'error', 'message' => 'Please select a valid role.'], 400);
            }
            Session::setFlash('danger', 'Please select a valid role.');
            return $this->redirect(url('/office/role-permission/assign'));
        }

        $isOwnerRole = strtolower((string)$role['slug']) === 'admin';
        if ($isOwnerRole) {
            $allPerms = $pdo->query("SELECT id FROM permissions")->fetchAll(PDO::FETCH_COLUMN);
            $this->roleModel->syncPermissions($roleId, array_map('intval', $allPerms));

            if ($isAjax) {
                return $this->json([
                    'status'  => 'success',
                    'message' => 'Administrator has master access to all features by default (permissions locked).',
                    'count'   => count($allPerms),
                ]);
            }

            Session::setFlash('info', 'Administrator permissions are locked to 100% full master access.');
            return $this->redirect(url('/office/role-permission'));
        }

        $rawPermissions = is_array($data['permissions'] ?? null) ? $data['permissions'] : [];
        $permissionIds = PermissionRegistry::resolvePermissionIds($pdo, $rawPermissions);
        $this->roleModel->syncPermissions($roleId, $permissionIds);

        $this->auditLog->record(
            'role_permissions_updated',
            'roles',
            $roleId,
            null,
            ['count' => count($permissionIds)],
            Session::getUserId()
        );

        if ($isAjax) {
            return $this->json([
                'status'  => 'success',
                'message' => "Permissions for role '{$role['name']}' saved successfully (" . count($permissionIds) . " granted).",
                'count'   => count($permissionIds),
            ]);
        }

        Session::setFlash('success', "Permissions for role '{$role['name']}' updated successfully.");
        return $this->redirect(url('/office/role-permission'));
    }
}
