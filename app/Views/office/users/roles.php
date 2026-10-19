<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--vg-navy-900);">Roles &amp; Permissions Matrix</h1>
        <p style="color: var(--vg-text-secondary); font-size: 0.9rem;">Architectural access control policy enforced across office command and driver mobile systems.</p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="<?= url('/office/users') ?>" class="btn btn-outline btn-sm">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            <span>User Directory</span>
        </a>
        <a href="<?= url('/office/users/create') ?>" class="btn btn-primary btn-sm">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            <span>Add User</span>
        </a>
    </div>
</div>

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
