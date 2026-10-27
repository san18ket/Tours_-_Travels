<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

class PermissionRegistry
{
    /**
     * Complete catalog of all VoyageGo system categories, pages, and their header & row actions.
     * Matches exact architecture of the enterprise school-management-system reference.
     */
    public static function getCatalogue(): array
    {
        return [
            'dashboard_telemetry' => [
                'category_name' => 'Executive Dashboard & Live Telemetry',
                'category_icon' => 'fa-chart-line',
                'pages' => [
                    [
                        'id' => 'page_dashboard',
                        'title' => 'Executive Command Center',
                        'route' => '/office/dashboard',
                        'module' => 'dashboard',
                        'permission' => 'dashboard.view',
                        'description' => 'Real-time booking revenue KPIs, fleet utilization, and operational alerts',
                        'header_buttons' => [
                            ['name' => 'dashboard.refresh', 'label' => 'Refresh Telemetry', 'icon' => 'fa-rotate', 'action' => 'view', 'description' => 'Poll live updates across all modules']
                        ],
                        'action_buttons' => [
                            ['name' => 'dashboard.export', 'label' => 'Export KPI Manifest', 'icon' => 'fa-file-excel', 'action' => 'view', 'description' => 'Download executive metrics report'],
                            ['name' => 'dashboard.view_revenue', 'label' => 'View Revenue Breakdown', 'icon' => 'fa-receipt', 'action' => 'view', 'description' => 'Inspect GST invoices and net revenue totals']
                        ]
                    ],
                    [
                        'id' => 'page_live_tracking',
                        'title' => 'Real-Time Fleet Live Radar',
                        'route' => '/office/tracking/live',
                        'module' => 'live_tracking',
                        'permission' => 'live_tracking.view',
                        'description' => 'Interactive live map radar tracking active vehicles, speed, and GPS coordinates',
                        'header_buttons' => [
                            ['name' => 'live_tracking.locate_all', 'label' => 'Ping All Vehicles', 'icon' => 'fa-satellite-dish', 'action' => 'view', 'description' => 'Poll instant coordinates for all deployed vehicles']
                        ],
                        'action_buttons' => [
                            ['name' => 'live_tracking.inspect_unit', 'label' => 'Inspect Vehicle Telemetry', 'icon' => 'fa-crosshairs', 'action' => 'view', 'description' => 'Focus camera on specific vehicle telemetry'],
                            ['name' => 'live_tracking.telemetry_api', 'label' => 'Access Telemetry API', 'icon' => 'fa-network-wired', 'action' => 'view', 'description' => 'Authorize telemetry data feed stream']
                        ]
                    ]
                ]
            ],

            'guest_management' => [
                'category_name' => 'Customer & Guest Management',
                'category_icon' => 'fa-users',
                'pages' => [
                    [
                        'id' => 'page_customers',
                        'title' => 'Customer Directory',
                        'route' => '/office/customers',
                        'module' => 'customers',
                        'permission' => 'customers.view',
                        'description' => 'Master guest client list, contact profiles, and booking history ledgers',
                        'header_buttons' => [
                            ['name' => 'customers.create', 'label' => 'Add Customer Button', 'icon' => 'fa-user-plus', 'action' => 'add', 'description' => 'Register new customer profile']
                        ],
                        'action_buttons' => [
                            ['name' => 'customers.show', 'label' => 'View Customer Profile', 'icon' => 'fa-eye', 'action' => 'view', 'description' => 'Inspect client history and contact details'],
                            ['name' => 'customers.edit', 'label' => 'Edit Customer Profile', 'icon' => 'fa-pen-to-square', 'action' => 'edit', 'description' => 'Modify customer name, mobile, and preferences'],
                            ['name' => 'customers.delete', 'label' => 'Delete Customer', 'icon' => 'fa-trash-can', 'action' => 'delete', 'description' => 'Decommission customer profile']
                        ]
                    ],
                    [
                        'id' => 'page_customers_create',
                        'title' => 'Register New Customer',
                        'route' => '/office/customers/create',
                        'module' => 'customers',
                        'permission' => 'customers.create_page',
                        'description' => 'Customer registration wizard with duplicate detection and address capture',
                        'header_buttons' => [
                            ['name' => 'customers.store', 'label' => 'Save Customer Record', 'icon' => 'fa-floppy-disk', 'action' => 'add', 'description' => 'Commit new customer details into system']
                        ],
                        'action_buttons' => []
                    ]
                ]
            ],

            'booking_manifests' => [
                'category_name' => 'Bookings & Billing Manifests',
                'category_icon' => 'fa-calendar-check',
                'pages' => [
                    [
                        'id' => 'page_bookings',
                        'title' => 'Reservations Directory',
                        'route' => '/office/bookings',
                        'module' => 'bookings',
                        'permission' => 'bookings.view',
                        'description' => 'All luxury tour bookings, manifest schedules, billing status, and invoices',
                        'header_buttons' => [
                            ['name' => 'bookings.create', 'label' => 'New Reservation Button', 'icon' => 'fa-calendar-plus', 'action' => 'add', 'description' => 'Initiate new luxury tour reservation']
                        ],
                        'action_buttons' => [
                            ['name' => 'bookings.show', 'label' => 'View Reservation Details', 'icon' => 'fa-eye', 'action' => 'view', 'description' => 'Inspect full itinerary and billing details'],
                            ['name' => 'bookings.edit', 'label' => 'Modify Reservation', 'icon' => 'fa-pen-to-square', 'action' => 'edit', 'description' => 'Update reservation dates, passenger counts, or pricing'],
                            ['name' => 'bookings.cancel', 'label' => 'Cancel Reservation', 'icon' => 'fa-ban', 'action' => 'delete', 'description' => 'Cancel booking and release reserved units'],
                            ['name' => 'bookings.invoice', 'label' => 'Generate GST Invoice', 'icon' => 'fa-file-invoice-dollar', 'action' => 'view', 'description' => 'Download and print statutory GST tax invoice']
                        ]
                    ],
                    [
                        'id' => 'page_bookings_create',
                        'title' => 'Create Reservation',
                        'route' => '/office/bookings/create',
                        'module' => 'bookings',
                        'permission' => 'bookings.create_page',
                        'description' => 'Comprehensive multi-section reservation booking form with tax & discount calculation',
                        'header_buttons' => [
                            ['name' => 'bookings.store', 'label' => 'Submit Reservation', 'icon' => 'fa-check', 'action' => 'add', 'description' => 'Save reservation and generate tracking manifest']
                        ],
                        'action_buttons' => []
                    ]
                ]
            ],

            'fleet_assets' => [
                'category_name' => 'Fleet & Vehicle Assets',
                'category_icon' => 'fa-car-side',
                'pages' => [
                    [
                        'id' => 'page_vehicles',
                        'title' => 'Fleet Directory',
                        'route' => '/office/vehicles',
                        'module' => 'vehicles',
                        'permission' => 'vehicles.view',
                        'description' => 'Full motorpool inventory, license plates, seating capacities, and operational statuses',
                        'header_buttons' => [
                            ['name' => 'vehicles.create', 'label' => 'Register Vehicle Button', 'icon' => 'fa-circle-plus', 'action' => 'add', 'description' => 'Add new luxury transport unit']
                        ],
                        'action_buttons' => [
                            ['name' => 'vehicles.show', 'label' => 'Inspect Vehicle Details', 'icon' => 'fa-eye', 'action' => 'view', 'description' => 'View vehicle history, maintenance, and assigned chauffeur'],
                            ['name' => 'vehicles.edit', 'label' => 'Edit Vehicle Specs', 'icon' => 'fa-pen-to-square', 'action' => 'edit', 'description' => 'Update vehicle model, registration, or specs'],
                            ['name' => 'vehicles.toggle_status', 'label' => 'Toggle Maintenance Status', 'icon' => 'fa-toggle-on', 'action' => 'edit', 'description' => 'Toggle vehicle between Available and In Maintenance'],
                            ['name' => 'vehicles.delete', 'label' => 'Decommission Vehicle', 'icon' => 'fa-trash-can', 'action' => 'delete', 'description' => 'Permanently retire vehicle from fleet']
                        ]
                    ],
                    [
                        'id' => 'page_vehicles_create',
                        'title' => 'Register Vehicle',
                        'route' => '/office/vehicles/create',
                        'module' => 'vehicles',
                        'permission' => 'vehicles.create_page',
                        'description' => 'Motorpool registration form for adding new cars, sedans, SUVs, and coaches',
                        'header_buttons' => [
                            ['name' => 'vehicles.store', 'label' => 'Save Vehicle Record', 'icon' => 'fa-floppy-disk', 'action' => 'add', 'description' => 'Commit new vehicle specs into inventory']
                        ],
                        'action_buttons' => []
                    ]
                ]
            ],

            'staff_operations' => [
                'category_name' => 'Staff & Chauffeur Operations',
                'category_icon' => 'fa-id-badge',
                'pages' => [
                    [
                        'id' => 'page_employees',
                        'title' => 'Employee Directory',
                        'route' => '/office/employees',
                        'module' => 'employees',
                        'permission' => 'employees.view',
                        'description' => 'Official employee register, designations, chauffeurs, and emergency contacts',
                        'header_buttons' => [
                            ['name' => 'employees.create', 'label' => 'Onboard Employee Button', 'icon' => 'fa-user-plus', 'action' => 'add', 'description' => 'Add new employee or chauffeur profile']
                        ],
                        'action_buttons' => [
                            ['name' => 'employees.show', 'label' => 'View Personnel Profile', 'icon' => 'fa-eye', 'action' => 'view', 'description' => 'View complete employee dossier'],
                            ['name' => 'employees.edit', 'label' => 'Edit Employee Record', 'icon' => 'fa-pen-to-square', 'action' => 'edit', 'description' => 'Update employee designation, contact, or salary'],
                            ['name' => 'employees.toggle_status', 'label' => 'Toggle Employment Status', 'icon' => 'fa-toggle-on', 'action' => 'edit', 'description' => 'Toggle employee between Active and On Leave'],
                            ['name' => 'employees.delete', 'label' => 'Deactivate Employee', 'icon' => 'fa-trash-can', 'action' => 'delete', 'description' => 'Archive personnel record from active directory']
                        ]
                    ],
                    [
                        'id' => 'page_employees_create',
                        'title' => 'Onboard Employee',
                        'route' => '/office/employees/create',
                        'module' => 'employees',
                        'permission' => 'employees.create_page',
                        'description' => 'Personnel onboarding wizard with identity document validation and emergency contacts',
                        'header_buttons' => [
                            ['name' => 'employees.store', 'label' => 'Save Employee Profile', 'icon' => 'fa-floppy-disk', 'action' => 'add', 'description' => 'Save employee details into personnel register']
                        ],
                        'action_buttons' => []
                    ]
                ]
            ],

            'trip_dispatch' => [
                'category_name' => 'Trip Operations & Dispatch Control',
                'category_icon' => 'fa-route',
                'pages' => [
                    [
                        'id' => 'page_trip_assign',
                        'title' => 'Assign Trip Duty',
                        'route' => '/office/trips/assign',
                        'module' => 'trip_operations',
                        'permission' => 'trip_operations.assign_page',
                        'description' => 'Assign chauffeur and vehicle to pending reservation bookings with capacity validation',
                        'header_buttons' => [
                            ['name' => 'trip_operations.store', 'label' => 'Dispatch Trip Button', 'icon' => 'fa-paper-plane', 'action' => 'add', 'description' => 'Confirm and dispatch duty assignment']
                        ],
                        'action_buttons' => []
                    ],
                    [
                        'id' => 'page_trip_history',
                        'title' => 'Trip Transit History',
                        'route' => '/office/trips/history',
                        'module' => 'trip_operations',
                        'permission' => 'trip_operations.view',
                        'description' => 'Complete chronological status audit trail and dispatch log history',
                        'header_buttons' => [
                            ['name' => 'trip_operations.export', 'label' => 'Export Dispatch Logs', 'icon' => 'fa-download', 'action' => 'view', 'description' => 'Download transit history manifest']
                        ],
                        'action_buttons' => [
                            ['name' => 'trip_operations.show', 'label' => 'View Status Trail', 'icon' => 'fa-timeline', 'action' => 'view', 'description' => 'Inspect timestamped dispatch milestones'],
                            ['name' => 'trip_operations.update_status', 'label' => 'Update Trip Status', 'icon' => 'fa-check-double', 'action' => 'edit', 'description' => 'Advance trip milestone (Driver Accepted, En Route, Completed)'],
                            ['name' => 'trip_operations.cancel', 'label' => 'Abort / Cancel Trip', 'icon' => 'fa-triangle-exclamation', 'action' => 'delete', 'description' => 'Cancel active dispatch and release assigned assets']
                        ]
                    ]
                ]
            ],

            'access_control' => [
                'category_name' => 'Users & Permissions (Access Control)',
                'category_icon' => 'fa-users-gear',
                'pages' => [
                    [
                        'id' => 'page_users',
                        'title' => 'Users Directory',
                        'route' => '/office/users',
                        'module' => 'users',
                        'permission' => 'users.view',
                        'description' => 'Manage system login accounts, active sessions, and credential security',
                        'header_buttons' => [
                            ['name' => 'users.create', 'label' => 'Create Login Account Button', 'icon' => 'fa-user-plus', 'action' => 'add', 'description' => 'Provision new staff login account']
                        ],
                        'action_buttons' => [
                            ['name' => 'users.show', 'label' => 'View User Account', 'icon' => 'fa-eye', 'action' => 'view', 'description' => 'Inspect login history and access level'],
                            ['name' => 'users.edit', 'label' => 'Edit User Account', 'icon' => 'fa-user-pen', 'action' => 'edit', 'description' => 'Update user username, role, or linked employee'],
                            ['name' => 'users.toggle_status', 'label' => 'Toggle Account Status', 'icon' => 'fa-toggle-on', 'action' => 'edit', 'description' => 'Activate or suspend login access'],
                            ['name' => 'users.reset_pwd', 'label' => 'Reset Password', 'icon' => 'fa-key', 'action' => 'edit', 'description' => 'Trigger immediate password reset modal'],
                            ['name' => 'users.delete', 'label' => 'Delete User Account', 'icon' => 'fa-trash-can', 'action' => 'delete', 'description' => 'Permanently delete user account']
                        ]
                    ],
                    [
                        'id' => 'page_users_create',
                        'title' => 'Create Login Account',
                        'route' => '/office/users/create',
                        'module' => 'users',
                        'permission' => 'users.create_page',
                        'description' => 'Multi-step login account creator with employee auto-fill and role assignment',
                        'header_buttons' => [
                            ['name' => 'users.store', 'label' => 'Provision Account', 'icon' => 'fa-check', 'action' => 'add', 'description' => 'Save credentials and activate user account']
                        ],
                        'action_buttons' => []
                    ],
                    [
                        'id' => 'page_roles',
                        'title' => 'Roles & Permissions',
                        'route' => '/office/roles',
                        'module' => 'roles',
                        'permission' => 'roles.view',
                        'description' => 'Roles directory, security posture cards, and role definition management',
                        'header_buttons' => [
                            ['name' => 'roles.create', 'label' => 'Add Role Modal Button', 'icon' => 'fa-shield-plus', 'action' => 'add', 'description' => 'Open modal to create a new custom role']
                        ],
                        'action_buttons' => [
                            ['name' => 'roles.edit', 'label' => 'Edit Role', 'icon' => 'fa-pen-to-square', 'action' => 'edit', 'description' => 'Update role title and scope description'],
                            ['name' => 'roles.manage_permissions', 'label' => 'Assign Permissions', 'icon' => 'fa-sliders', 'action' => 'edit', 'description' => 'Launch permission matrix editor for role'],
                            ['name' => 'roles.delete', 'label' => 'Delete Role', 'icon' => 'fa-trash-can', 'action' => 'delete', 'description' => 'Remove custom role (system roles protected)']
                        ]
                    ],
                    [
                        'id' => 'page_role_permissions',
                        'title' => 'Role Permission Matrix',
                        'route' => '/office/role-permission',
                        'module' => 'roles',
                        'permission' => 'roles.manage_permissions',
                        'description' => 'Granular permission matrix editor with category accordion, Select All, and instant save',
                        'header_buttons' => [
                            ['name' => 'role_permissions.assign', 'label' => 'Assign Permissions Button', 'icon' => 'fa-user-shield', 'action' => 'edit', 'description' => 'Open permission checklist matrix']
                        ],
                        'action_buttons' => [
                            ['name' => 'role_permissions.store', 'label' => 'Save Permission Mappings', 'icon' => 'fa-floppy-disk', 'action' => 'edit', 'description' => 'Commit role and user permission assignments']
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Flatten catalogue into a clean list of permissions for database seeding.
     */
    public static function getFlatPermissions(): array
    {
        $permissions = [];
        $registeredNames = [];

        // Master full access
        $permissions[] = [
            'name' => 'all_access',
            'module' => 'all',
            'action' => 'view',
            'description' => 'Full administrative command center authority'
        ];
        $registeredNames['all_access'] = true;

        foreach (self::getCatalogue() as $cat) {
            foreach ($cat['pages'] as $page) {
                // Page view permission
                if (!isset($registeredNames[$page['permission']])) {
                    $permissions[] = [
                        'name' => $page['permission'],
                        'module' => $page['module'],
                        'action' => 'view',
                        'description' => 'Access ' . $page['title'] . ' Page'
                    ];
                    $registeredNames[$page['permission']] = true;
                }

                // Header buttons
                foreach ($page['header_buttons'] as $btn) {
                    if (!isset($registeredNames[$btn['name']])) {
                        $permissions[] = [
                            'name' => $btn['name'],
                            'module' => $page['module'],
                            'action' => $btn['action'] ?? 'add',
                            'description' => $btn['description'] ?? ($btn['label'] . ' on ' . $page['title'])
                        ];
                        $registeredNames[$btn['name']] = true;
                    }
                }

                // Row action buttons
                foreach ($page['action_buttons'] as $btn) {
                    if (!isset($registeredNames[$btn['name']])) {
                        $permissions[] = [
                            'name' => $btn['name'],
                            'module' => $page['module'],
                            'action' => $btn['action'] ?? 'edit',
                            'description' => $btn['description'] ?? ($btn['label'] . ' on ' . $page['title'])
                        ];
                        $registeredNames[$btn['name']] = true;
                    }
                }
            }
        }

        return $permissions;
    }

    /**
     * Idempotently seeds all catalogue permissions into the `permissions` table.
     */
    public static function ensureSeeded(PDO $pdo): void
    {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO `permissions` (`name`, `module`, `action`, `description`, `created_at`) 
                VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP) 
                ON DUPLICATE KEY UPDATE `description` = VALUES(`description`), `module` = VALUES(`module`), `action` = VALUES(`action`)
            ");

            foreach (self::getFlatPermissions() as $p) {
                $stmt->execute([$p['name'], $p['module'], $p['action'], $p['description']]);
            }
        } catch (\Throwable $e) {
            error_log("PermissionRegistry::ensureSeeded notice: " . $e->getMessage());
        }
    }

    /**
     * Map permission name to its database ID.
     */
    public static function getPermissionNameToIdMap(PDO $pdo): array
    {
        try {
            $rows = $pdo->query("SELECT id, name FROM `permissions`")->fetchAll(PDO::FETCH_ASSOC);
            $map = [];
            foreach ($rows as $r) {
                $map[$r['name']] = (int)$r['id'];
            }
            return $map;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Get the full catalogue enriched with database permission IDs.
     */
    public static function getCatalogueWithPermissionIds(PDO $pdo): array
    {
        $idMap = self::getPermissionNameToIdMap($pdo);
        $catalogue = self::getCatalogue();

        foreach ($catalogue as &$category) {
            foreach ($category['pages'] as &$page) {
                $page['permission_id'] = $idMap[$page['permission']] ?? null;

                foreach ($page['header_buttons'] as &$btn) {
                    $btn['permission_id'] = $idMap[$btn['name']] ?? null;
                }
                unset($btn);

                foreach ($page['action_buttons'] as &$btn) {
                    $btn['permission_id'] = $idMap[$btn['name']] ?? null;
                }
                unset($btn);
            }
            unset($page);
        }
        unset($category);

        return $catalogue;
    }

    /**
     * Resolves an array of permission IDs and/or permission name keys into valid database IDs.
     * Only inserts specific requested permissions on-demand if they are being assigned.
     */
    public static function resolvePermissionIds(PDO $pdo, array $inputs): array
    {
        if (empty($inputs)) {
            return [];
        }

        $idMap = self::getPermissionNameToIdMap($pdo);
        $resolvedIds = [];

        // Preload flat permission definitions
        $flatPerms = [];
        foreach (self::getFlatPermissions() as $p) {
            $flatPerms[$p['name']] = $p;
        }

        $insertStmt = null;

        foreach ($inputs as $item) {
            if (is_numeric($item) && (int)$item > 0) {
                $id = (int)$item;
                if (in_array($id, $idMap, true)) {
                    $resolvedIds[] = $id;
                    continue;
                }
            }

            $name = trim((string)$item);
            if (empty($name)) {
                continue;
            }

            if (isset($idMap[$name])) {
                $resolvedIds[] = $idMap[$name];
            } elseif (isset($flatPerms[$name])) {
                // Insert only this explicitly assigned permission
                $def = $flatPerms[$name];
                if ($insertStmt === null) {
                    $insertStmt = $pdo->prepare("INSERT INTO `permissions` (`name`, `module`, `action`, `description`, `created_at`) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)");
                }
                try {
                    $insertStmt->execute([$def['name'], $def['module'], $def['action'], $def['description']]);
                    $newId = (int)$pdo->lastInsertId();
                    $idMap[$name] = $newId;
                    $resolvedIds[] = $newId;
                } catch (\Throwable $e) {
                    $check = $pdo->prepare("SELECT id FROM `permissions` WHERE name = ?");
                    $check->execute([$name]);
                    $existId = (int)$check->fetchColumn();
                    if ($existId > 0) {
                        $idMap[$name] = $existId;
                        $resolvedIds[] = $existId;
                    }
                }
            }
        }

        return array_values(array_unique(array_filter($resolvedIds, static fn(int $id) => $id > 0)));
    }
}
