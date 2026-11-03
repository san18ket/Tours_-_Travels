<?php
/**
 * Professional Custom PWA Installation Modal
 * Matches Tours & Travels application theme and native PWA installation standards.
 */

$pwaAppName = has_company_name() ? company_name() : 'Tours & Travels';
$pwaLogoUrl = has_company_logo() ? company_logo_url() : asset('icons/icon-192.png');
$pwaHasLogo = has_company_logo();
?>
<div class="pwa-install-backdrop" 
     id="toursPwaInstallModal" 
     role="dialog" 
     aria-modal="true" 
     aria-labelledby="pwaModalTitle" 
     data-app-name="<?= e($pwaAppName) ?>" 
     data-logo-url="<?= e($pwaLogoUrl) ?>" 
     data-has-custom-logo="<?= $pwaHasLogo ? '1' : '0' ?>" 
     style="display: none;">
    <div class="pwa-install-card" id="pwaModalCard">
        <button type="button" class="pwa-card-close" onclick="closeToursInstallModal()" aria-label="Close modal">&times;</button>
        <div id="pwaModalDynamicContent">
            <!-- Dynamically populated and switched by tours-pwa.js state machine -->
        </div>
    </div>
</div>

