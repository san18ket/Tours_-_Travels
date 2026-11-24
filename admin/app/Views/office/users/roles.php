<?php
$rolesActions = '<div style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">'
    . '<a href="' . url('/office/users') . '" class="btn btn-outline btn-sm">User Directory</a>'
    . '<a href="' . url('/office/users/create') . '" class="btn btn-primary btn-sm">+ Add User</a>'
    . '</div>';
?>
<?= render_page_header([
    'title'       => 'Roles & Permissions Matrix',
    'subtitle'    => 'Architectural access control policy enforced across office command and driver mobile systems.',
    'breadcrumbs' => [
        ['label' => 'User Management', 'url' => url('/office/users')],
        ['label' => 'Permissions Matrix', 'url' => ''],
    ],
    'actions'     => $rolesActions,
    'badge'       => 'Server Enforced Policy',
]) ?>

<div class="vg-card">
    <div class="card-header">
        <span class="card-title">Enforced Authorization Matrix by Functional Module</span>
        <span class="badge badge-navy">Strict Server-Side Enforced</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="modern-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="col-priority-1"><?= e(__('table.functional_module', 'Functional Module')) ?></th>
                        <th class="col-priority-1"><?= e(__('role.admin', 'Administrator')) ?></th>
                        <th class="col-priority-2"><?= e(__('role.dispatcher', 'Dispatcher')) ?></th>
                        <th class="col-priority-2"><?= e(__('role.driver', 'Driver')) ?></th>
                        <th class="col-priority-3 col-optional"><?= e(__('table.customer', 'Customer')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rolePriorities = [
                        'admin'      => 'col-priority-1',
                        'dispatcher' => 'col-priority-2',
                        'driver'     => 'col-priority-2',
                        'customer'   => 'col-priority-3 col-optional',
                    ];
                    foreach ($matrix as $moduleName => $roles): ?>
                        <tr>
                            <td class="col-priority-1">
                                <strong style="color: var(--vg-navy-900); font-size: 0.95rem;"><?= e($moduleName) ?></strong>
                            </td>
                            <?php foreach (['admin', 'dispatcher', 'driver', 'customer'] as $roleKey): ?>
                                <?php 
                                $perm = $roles[$roleKey] ?? null; 
                                $pClass = $rolePriorities[$roleKey] ?? '';
                                ?>
                                <td class="<?= $pClass ?>">
                                    <?php if ($perm): ?>
                                        <div style="margin-bottom: 0.35rem;">
                                            <span class="badge <?= e($perm['badge']) ?>"><?= e($perm['label']) ?></span>
                                        </div>
                                        <div style="font-size: 0.78rem; color: var(--vg-text-muted); line-height: 1.35;">
                                            <?= e($perm['desc']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge badge-neutral">—</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
