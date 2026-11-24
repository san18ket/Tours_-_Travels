<?php
$currentUser = auth_user() ?? \App\Core\Session::getUser() ?? [];
$currentRole = auth_role() ?? ($userRole ?? \App\Core\Session::getUserRole());
$path = $requestedPath ?? ($_SERVER['REQUEST_URI'] ?? '');
?>

<div style="font-size: 3.5rem; margin-bottom: 0.75rem;">🛡️</div>
<h2 style="color: #EF4444; margin-bottom: 0.5rem; font-size: 1.6rem; font-weight: 800; letter-spacing: -0.02em;"><?= e(__('error.forbidden_title', 'Access Forbidden (403)')) ?></h2>

<p style="color: #CBD5E1; font-size: 0.95rem; margin-bottom: 1.25rem; line-height: 1.5;">
    <?= e($message ?? __('error.forbidden_desc', 'You do not have administrative permission to view this resource.')) ?>
</p>

<?php if (!empty($currentRole)): ?>
    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; text-align: left; font-size: 0.86rem;">
        <div style="color: #94A3B8; margin-bottom: 4px; font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;">
            <?= e(__('error.current_session', 'Current Active Session')) ?>
        </div>
        <div style="color: #FFF; font-weight: 700; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px;">
            <span><?= e($currentUser['name'] ?? 'User') ?> (<?= e($currentUser['username'] ?? 'account') ?>)</span>
            <span style="background: rgba(239, 68, 68, 0.2); border: 1px solid #EF4444; color: #FCA5A5; padding: 2px 8px; border-radius: 6px; font-size: 0.75rem; text-transform: uppercase; font-weight: 800;">
                <?= e(__('common.role', 'Role')) ?>: <?= e(ucfirst($currentRole)) ?>
            </span>
        </div>
        <div style="color: #94A3B8; font-size: 0.78rem; margin-top: 6px;">
            <?php if ($currentRole === 'driver'): ?>
                <?= __('error.driver_needs_admin', ['portal' => '<a href="' . url('/driver/dashboard') . '" style="color: #34D399; text-decoration: underline;">' . e(__('driver.portal_title', 'Driver Portal')) . '</a>']) ?>
            <?php else: ?>
                This resource requires elevated administrative role: <strong><?= e(implode(', ', $requiredRoles ?? ['admin'])) ?></strong>.
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
    <?php if ($currentRole === 'driver'): ?>
        <a href="<?= url('/driver/dashboard') ?>" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px; padding: 0.65rem 1.25rem; border-radius: 10px; font-weight: 700; background: #10B981; border: none; color: #FFF; text-decoration: none;">
            <span><?= e(__('driver.console_title', 'Driver Console')) ?></span>
        </a>
    <?php endif; ?>

    <form action="<?= url('/logout') ?>" method="POST" style="margin: 0; display: inline;">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 6px; padding: 0.65rem 1.25rem; border-radius: 10px; font-weight: 700; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.25); color: #FFF; cursor: pointer;">
            <span><?= e(__('error.switch_account', 'Switch to Admin Account')) ?></span>
        </button>
    </form>

    <a href="<?= url('/') ?>" class="btn" style="display: inline-flex; align-items: center; gap: 6px; padding: 0.65rem 1.25rem; border-radius: 10px; font-weight: 700; background: transparent; color: #94A3B8; text-decoration: none;">
        <span><?= e(__('error.return_home', 'Return Home')) ?></span>
    </a>
</div>
