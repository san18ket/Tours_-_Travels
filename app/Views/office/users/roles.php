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
    'image'       => 'header-roles.jpg',
    'badge'       => 'Server Enforced Policy',
]) ?>

<div class="vg-card">
    <div class="card-header">
        <span class="card-title">Enforced Authorization Matrix by Functional Module</span>
        <span class="badge badge-navy">Strict Server-Side Enforced</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="min-width: 180px;">Functional Module</th>
                        <th style="min-width: 200px;">Administrator</th>
                        <th style="min-width: 200px;">Dispatcher</th>
                        <th style="min-width: 200px;">Chauffeur / Driver</th>
                        <th style="min-width: 180px;">Customer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($matrix as $moduleName => $roles): ?>
                        <tr>
                            <td>
                                <strong style="color: var(--vg-navy-900); font-size: 0.95rem;"><?= e($moduleName) ?></strong>
                            </td>
                            <?php foreach (['admin', 'dispatcher', 'driver', 'customer'] as $roleKey): ?>
                                <?php $perm = $roles[$roleKey] ?? null; ?>
                                <td>
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
