<?php
/**
 * Shared Mobile Top Navbar Component
 * Matches mobile_dashbord.jpeg in Forest Teal theme across all pages on responsive devices (<= 768px)
 */
$authUser = auth_user() ?: ($user ?? []);
$authUserName = $authUser['name'] ?? $authUser['username'] ?? ($user['name'] ?? 'Admin');
$authUserRole = ucfirst(auth_role() ?: ($userRole ?? 'Super Admin'));
$authUserEmail = $authUser['email'] ?? ($user['email'] ?? 'admin@manaswi.com');
$currLocale = \App\Core\Language::getLocale();
$dashLangs = [
    'en' => ['native' => 'English', 'name' => 'English', 'flag' => '🇬🇧'],
    'hi' => ['native' => 'हिन्दी', 'name' => 'Hindi', 'flag' => '🇮🇳'],
    'mr' => ['native' => 'मराठी', 'name' => 'Marathi', 'flag' => '🚩'],
];
$homeUrl = (auth_role() === 'driver') ? url('/driver/dashboard') : url('/office/dashboard');
$profileUrl = (auth_role() === 'driver') ? url('/driver/profile') : url('/office/users');
$notifSettingsUrl = (auth_role() === 'driver') ? url('/driver/notification-settings') : url('/office/notification-settings');
$curReturnUrl = $_SERVER['REQUEST_URI'] ?? $homeUrl;
?>
<!-- Mobile Top Navbar (Matches Dashboard Home Page on Responsive Devices) -->
<header class="mv-dash-topbar" id="mvDashTopbar">
    <a href="<?= $homeUrl ?>" class="mv-dash-logo" title="<?= e(company_display_name()) ?> - <?= e(__('sidebar.dashboard', 'Home')) ?>" data-no-ajax="true">
        <?php if (has_company_logo()): ?>
            <img src="<?= e(company_logo_url()) ?>" class="mv-logo-bus-img" alt="Logo" style="object-fit: contain; border-radius: 8px;">
        <?php else: ?>
            <div class="mv-logo-icon-badge" style="width: 36px; height: 36px; background: rgba(255, 255, 255, 0.15); color: #FFFFFF; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                <i class="fa-solid fa-building"></i>
            </div>
        <?php endif; ?>
        <div class="mv-logo-text">
            <span class="mv-brand-title"><?= e(company_display_name()) ?></span>
            <?php if (has_company_name()): ?>
                <?php if (!empty(company_tagline())): ?>
                    <span class="mv-brand-tagline"><?= e(company_tagline()) ?></span>
                <?php else: ?>
                    <span class="mv-brand-sub"><?= e(__('app.tours_travels', 'Tours & Travels')) ?></span>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </a>
    <div class="mv-dash-top-actions">
        <!-- Professional Language Dropdown -->
        <div class="mv-dash-dropdown mv-dash-lang-dropdown" id="mvLangDropdown">
            <button type="button" class="mv-dash-lang-btn" onclick="toggleMvLang(event)" aria-label="<?= e(__('lang.select_language', 'Change Language')) ?>" title="<?= e(__('lang.select_language', 'Change Language')) ?>">
                <i class="fa-solid fa-globe"></i>
                <span class="mv-dash-lang-code"><?= strtoupper($currLocale) ?></span>
                <i class="fa-solid fa-chevron-down mv-dropdown-chevron"></i>
            </button>
            <div class="mv-dash-menu mv-dash-lang-menu" id="mvLangMenu">
                <div class="mv-dash-menu-header">
                    <i class="fa-solid fa-language" style="color: #176B5B;"></i>
                    <span><?= e(__('lang.select_language', 'Select Language')) ?></span>
                </div>
                <?php foreach ($dashLangs as $code => $lInfo): ?>
                    <form action="<?= url('/language/set') ?>" method="POST" class="mv-lang-item-form" data-no-ajax="true">
                        <?= csrf_field() ?>
                        <input type="hidden" name="locale" value="<?= $code ?>">
                        <input type="hidden" name="return_url" value="<?= e($curReturnUrl) ?>">
                        <button type="submit" class="mv-lang-opt <?= $currLocale === $code ? 'is-active' : '' ?>">
                            <span class="mv-lang-flag"><?= $lInfo['flag'] ?></span>
                            <div class="mv-lang-names">
                                <span class="mv-lang-native"><?= $lInfo['native'] ?></span>
                                <span class="mv-lang-sub"><?= $lInfo['name'] ?></span>
                            </div>
                            <?php if ($currLocale === $code): ?>
                                <i class="fa-solid fa-circle-check mv-lang-check"></i>
                            <?php endif; ?>
                        </button>
                    </form>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Professional Profile Avatar Dropdown -->
        <div class="mv-dash-dropdown mv-dash-profile-dropdown" id="mvProfileDropdown">
            <button type="button" class="mv-dash-avatar-btn" onclick="toggleMvProfile(event)" aria-label="<?= e(__('topbar.view_profile', 'User Profile')) ?>" title="<?= e($authUserName) ?>">
                <img src="<?= asset('images/admin_avatar.jpg') ?>" class="mv-dash-avatar-img" alt="<?= e($authUserName) ?>" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?= urlencode($authUserName) ?>&background=176B5B&color=fff';">
                <i class="fa-solid fa-chevron-down mv-dropdown-chevron"></i>
            </button>
            <div class="mv-dash-menu mv-dash-profile-menu" id="mvProfileMenu">
                <div class="mv-profile-card-head">
                    <img src="<?= asset('images/admin_avatar.jpg') ?>" class="mv-profile-head-avatar" alt="Avatar" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?= urlencode($authUserName) ?>&background=176B5B&color=fff';">
                    <div class="mv-profile-head-meta">
                        <strong class="mv-profile-name"><?= e($authUserName) ?></strong>
                        <span class="mv-profile-role-pill"><?= e($authUserRole) ?></span>
                    </div>
                </div>
                <div class="mv-menu-divider"></div>
                <a href="<?= $profileUrl ?>" class="mv-menu-item" data-no-ajax="true">
                    <div class="mv-menu-item-icon" style="background: rgba(23, 107, 91, 0.1); color: #176B5B;">
                        <i class="fa-solid fa-user-circle"></i>
                    </div>
                    <span class="mv-menu-item-label"><?= e(__('topbar.view_profile', 'View Profile')) ?></span>
                    <i class="fa-solid fa-chevron-right mv-menu-item-arrow"></i>
                </a>
                <button type="button" class="mv-menu-item tours-install-action tours-install-btn" onclick="toggleMvProfile(event); triggerToursInstall(event);" style="background:none; border:none; width:100%; text-align:left; cursor:pointer;">
                    <div class="mv-menu-item-icon" style="background: rgba(23, 107, 91, 0.1); color: #176B5B;">
                        <i class="fa-solid fa-cloud-arrow-down"></i>
                    </div>
                    <span class="mv-menu-item-label install-btn-label">Install Tours</span>
                    <i class="fa-solid fa-download mv-menu-item-arrow"></i>
                </button>
                <a href="<?= $notifSettingsUrl ?>" class="mv-menu-item" data-no-ajax="true">
                    <div class="mv-menu-item-icon" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6;">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <span class="mv-menu-item-label">Notification Settings</span>
                    <i class="fa-solid fa-chevron-right mv-menu-item-arrow"></i>
                </a>
                <div class="mv-menu-divider"></div>
                <form action="<?= url('/logout') ?>" method="POST" data-no-ajax="true" style="margin:0;" onsubmit="var b = this.querySelector('button'); if(b){ b.disabled = true; b.style.opacity = '0.7'; var l = b.querySelector('.mv-menu-item-label'); if(l) l.textContent = '<?= e(__('topbar.sign_out_desc', 'Signing out...')) ?>'; }">
                    <?= csrf_field() ?>
                    <button type="submit" class="mv-menu-item mv-logout-btn">
                        <div class="mv-menu-item-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        </div>
                        <span class="mv-menu-item-label" style="color: #ef4444; font-weight: 700;"><?= e(__('sidebar.logout', 'Logout')) ?></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
