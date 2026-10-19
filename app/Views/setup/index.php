<div class="card-header" style="background: #152943; color: #FFF; border-bottom: 1px solid #2E5077;">
    <h3 style="color: #FFF; margin: 0; font-size: 1.15rem;">Automated Database Initialization & Pre-Flight Checks</h3>
</div>
<div class="card-body" style="padding: 1.75rem;">
    <!-- Connectivity Diagnostic -->
    <div style="margin-bottom: 1.5rem; padding: 1.25rem; border-radius: 10px; background: <?= $canConnect ? '#ECFDF5' : '#FEF2F2' ?>; border: 1px solid <?= $canConnect ? '#A7F3D0' : '#FECACA' ?>;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <strong style="color: <?= $canConnect ? '#065F46' : '#991B1B' ?>; font-size: 1rem;">
                    <?= $canConnect ? '✓ Database Server Connected' : '✗ Database Server Unreachable' ?>
                </strong>
                <p style="margin: 0.25rem 0 0; font-size: 0.88rem; color: #4B5563;">
                    Host: <code><?= e($config['host']) ?>:<?= e($config['port']) ?></code> | Target DB: <code><?= e($dbName) ?></code>
                    <?= $dbExists ? '<span class="badge badge-success" style="margin-left:8px;">Database Exists</span>' : '<span class="badge badge-warning" style="margin-left:8px;">Database Will Be Created</span>' ?>
                </p>
            </div>
            <span class="badge <?= $canConnect ? 'badge-success' : 'badge-danger' ?>">
                <?= $canConnect ? 'Ready' : 'Action Required' ?>
            </span>
        </div>

        <?php if ($connectionError): ?>
            <div style="margin-top: 0.75rem; color: #B91C1C; font-size: 0.85rem;">
                <?= e($connectionError) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Versioned Migrations Discovery -->
    <h4 style="color: var(--navy-900); font-size: 1rem; margin-bottom: 0.75rem;">
        Approved Versioned Migrations (<?= count($pendingMigrations) ?> Detected)
    </h4>
    <div style="background: #F8FAFC; border: 1px solid var(--border-light); border-radius: 8px; padding: 0.75rem; margin-bottom: 1.5rem; max-height: 180px; overflow-y: auto; font-family: monospace; font-size: 0.82rem;">
        <?php if (empty($pendingMigrations)): ?>
            <div style="color: #10B981; padding: 0.25rem;">✓ All migrations are already up-to-date.</div>
        <?php else: ?>
            <?php foreach ($pendingMigrations as $mig): ?>
                <div style="padding: 0.25rem 0.5rem; border-bottom: 1px solid #E2E8F0; color: #334155;">
                    <span style="color: #008DDA;">▶</span> <?= e($mig) ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($canConnect): ?>
        <form action="<?= url('/setup/run') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="form-group" style="background: #F1F5F9; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; font-weight: 600; color: var(--navy-900); margin-bottom: 0.5rem;">
                    <input type="checkbox" name="create_db" value="1" <?= !$dbExists ? 'checked' : '' ?> style="width: 18px; height: 18px;">
                    <span>Create database <code><?= e($dbName) ?></code> if not existing (with charset utf8mb4)</span>
                </label>

                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; font-weight: 600; color: var(--navy-900); margin: 0;">
                    <input type="checkbox" name="seed_data" value="1" checked style="width: 18px; height: 18px;">
                    <span>Seed production-ready initial users, fleet, sample tours, and test bookings</span>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-size: 1rem;">
                    ⚡ Run Safe Migrations & Complete Setup
                </button>
            </div>
        </form>
    <?php else: ?>
        <div style="text-align: right;">
            <button onclick="window.location.reload()" class="btn btn-navy">Retry Pre-Flight Check</button>
        </div>
    <?php endif; ?>
</div>
