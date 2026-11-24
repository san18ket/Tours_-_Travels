<?php
/**
 * Professional Custom PWA Installation Modal & Mandatory Update Gate
 * Matches Tours & Travels application theme and native PWA installation standards.
 */

$pwaAppName = pwa_app_name();
$pwaLogoUrl = pwa_icon_preview_url();
$pwaHasLogo = true;
$pwaAppVersion = app_version();
?>
<!-- 1. PWA Installation Modal -->
<div class="pwa-install-backdrop" 
     id="toursPwaInstallModal" 
     role="dialog" 
     aria-modal="true" 
     aria-labelledby="pwaModalTitle" 
     data-app-name="<?= e($pwaAppName) ?>" 
     data-logo-url="<?= e($pwaLogoUrl) ?>" 
     data-has-custom-logo="<?= $pwaHasLogo ? '1' : '0' ?>" 
     data-app-version="<?= e($pwaAppVersion) ?>" 
     style="display: none;">
    <div class="pwa-install-card" id="pwaModalCard">
        <button type="button" class="pwa-card-close" onclick="closeToursInstallModal()" aria-label="<?= e(__('btn.close', 'Close')) ?>">&times;</button>
        <div id="pwaModalDynamicContent">
            <!-- Dynamically populated and switched by tours-pwa.js state machine -->
        </div>
    </div>
</div>

<!-- 2. Mandatory PWA Update Gate Modal (Centralized, Unclosable, Strictly No Cancel) -->
<div class="pwa-update-backdrop"
     id="toursPwaUpdateModal"
     role="alertdialog"
     aria-modal="true"
     aria-labelledby="pwaUpdateTitle"
     aria-describedby="pwaUpdateDesc"
     data-app-name="<?= e($pwaAppName) ?>"
     data-logo-url="<?= e($pwaLogoUrl) ?>"
     data-app-version="<?= e($pwaAppVersion) ?>"
     style="display: none;">
    <div class="pwa-update-card" id="pwaUpdateCard">
        <!-- STATE 1: UPDATE AVAILABLE / REQUIRED (DEFAULT) -->
        <div class="pwa-update-panel" id="pwaUpdateStateAvailable">
            <div class="pwa-update-pill-wrap">
                <span class="pwa-mandatory-pill">
                    <span class="pwa-pulse-beacon"></span>
                    <i class="fa-solid fa-sparkles"></i>
                    <span><?= e(__('pwa.new_update_available', 'New Update Available')) ?></span>
                </span>
            </div>

            <div class="pwa-update-hero">
                <div class="pwa-update-icon-stage">
                    <div class="pwa-radar-ring ring-1"></div>
                    <div class="pwa-radar-ring ring-2"></div>
                    <div class="pwa-update-icon-wrap">
                        <img src="<?= e($pwaLogoUrl) ?>" alt="<?= e($pwaAppName) ?>" class="pwa-update-hero-icon" id="pwaUpdateHeroIcon">
                    </div>
                    <div class="pwa-update-corner-badge" title="<?= e(__('pwa.update_available', 'Update Available')) ?>">
                        <i class="fa-solid fa-cloud-arrow-down"></i>
                    </div>
                </div>

                <h2 class="pwa-update-title" id="pwaUpdateTitle"><?= e(__('pwa.new_update_available', 'New Update Available')) ?></h2>
                <p class="pwa-update-desc" id="pwaUpdateDesc">
                    <?= __('pwa.update_desc_template', ['app' => '<strong id="pwaUpdateAppName">' . e($pwaAppName) . '</strong>', 'version' => '<span id="pwaUpdateNewVer">v' . e($pwaAppVersion) . '</span>']) ?>
                </p>
                <p class="pwa-update-subtext">
                    <?= e(__('pwa.update_subtext', 'Please update now to continue using the application.')) ?>
                </p>
            </div>

            <!-- Version Comparison Capsule -->
            <div class="pwa-version-comparison-card">
                <div class="pwa-ver-box">
                    <span class="ver-label"><i class="fa-regular fa-clock"></i> <?= e(__('pwa.installed_version', 'Installed Version')) ?></span>
                    <span class="ver-val font-mono" id="pwaUpdateCurrentVer">v1.0.0</span>
                    <span class="ver-status-badge ver-badge-old"><?= e(__('pwa.installed', 'Installed')) ?></span>
                </div>
                <div class="pwa-ver-arrow-stage">
                    <i class="fa-solid fa-arrow-right-long"></i>
                </div>
                <div class="pwa-ver-box ver-box-new">
                    <span class="ver-label"><i class="fa-solid fa-sparkles"></i> <?= e(__('pwa.new_version', 'New Version')) ?></span>
                    <span class="ver-val font-mono" id="pwaUpdateRequiredVer">v<?= e($pwaAppVersion) ?></span>
                    <span class="ver-status-badge ver-badge-new"><?= e(__('pwa.required', 'Required')) ?></span>
                </div>
            </div>

            <!-- Restart Notice -->
            <div class="pwa-update-restart-banner">
                <i class="fa-solid fa-arrows-rotate"></i>
                <span><?= e(__('pwa.restart_notice', 'The application will restart automatically after the update is completed.')) ?></span>
            </div>

            <!-- Update Now Primary Action (Strictly Zero Cancel Button) -->
            <div class="pwa-update-actions">
                <button type="button" class="btn-pwa-update-now" id="btnPwaUpdateNow" onclick="performPwaUpdate()">
                    <i class="fa-solid fa-cloud-arrow-down"></i>
                    <span id="pwaUpdateBtnText"><?= e(__('pwa.update_now', 'Update Now')) ?></span>
                </button>
                <p class="pwa-update-helper-text">
                    <?= e(__('pwa.update_required_helper', 'This update is required to continue using the application.')) ?>
                </p>
            </div>
        </div>

        <!-- STATE 2: UPDATING IN PROGRESS -->
        <div class="pwa-update-panel" id="pwaUpdateStateUpdating" style="display: none;">
            <div class="pwa-update-hero">
                <div class="pwa-update-icon-stage">
                    <div class="pwa-radar-ring ring-1 is-spinning"></div>
                    <div class="pwa-update-icon-wrap">
                        <img src="<?= e($pwaLogoUrl) ?>" alt="<?= e($pwaAppName) ?>" class="pwa-update-hero-icon">
                    </div>
                </div>

                <h2 class="pwa-update-title"><?= __('pwa.updating_app', ['app' => '<span id="pwaUpdatingAppName">' . e($pwaAppName) . '</span>']) ?></h2>
                <p class="pwa-update-desc">
                    <?= e(__('pwa.wait_updating', 'Please wait while the latest version is installed.')) ?>
                </p>
            </div>

            <!-- Professional Indeterminate Progress Bar (No Fake Percentages) -->
            <div class="pwa-progress-track-wrap">
                <div class="pwa-progress-track" role="progressbar" aria-label="<?= e(__('pwa.installing_update', 'Installing application update')) ?>">
                    <div class="pwa-progress-bar-indeterminate"></div>
                </div>
                <div class="pwa-updating-status">
                    <span class="pwa-mini-spinner"></span>
                    <span id="pwaUpdatingStatusText"><?= e(__('pwa.activating_services', 'Activating latest application services...')) ?></span>
                </div>
            </div>

            <div class="pwa-update-actions">
                <button type="button" class="btn-pwa-update-now" disabled>
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <span><?= e(__('pwa.updating', 'Updating...')) ?></span>
                </button>
            </div>
        </div>

        <!-- STATE 3: UPDATE FAILED / RECOVERABLE -->
        <div class="pwa-update-panel" id="pwaUpdateStateFailed" style="display: none;">
            <div class="pwa-failed-icon-wrap">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>

            <h2 class="pwa-update-title"><?= e(__('pwa.update_failed_title', 'Update Could Not Be Completed')) ?></h2>
            <p class="pwa-update-desc">
                <?= e(__('pwa.update_failed_desc', 'The latest version could not be installed. Please check your internet connection and try again.')) ?>
            </p>

            <div class="pwa-update-actions">
                <button type="button" class="btn-pwa-update-now btn-pwa-try-again" id="btnPwaTryAgain" onclick="performPwaUpdate()">
                    <i class="fa-solid fa-rotate-right"></i>
                    <span><?= e(__('btn.try_again', 'Try Again')) ?></span>
                </button>
                <p class="pwa-update-helper-text">
                    <?= e(__('pwa.update_required_notice', 'An update is required before normal application access can continue.')) ?>
                </p>
            </div>
        </div>
    </div>
</div>
