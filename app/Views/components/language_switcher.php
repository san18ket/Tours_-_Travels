<?php
/**
 * VoyageGo Language Selector Component
 * Modern, accessible AJAX dropdown for English, Hindi, and Marathi
 */

use App\Core\Language;

$currentLocale = Language::getLocale();
$supportedLangs = Language::getSupported();
$currentLang = Language::getCurrentLanguage();
$returnUrl = $_SERVER['REQUEST_URI'] ?? url('/');
?>
<?php if (!defined('VG_LANG_CSS_LOADED')): define('VG_LANG_CSS_LOADED', true); ?>
<style id="vg-lang-dropdown-styles">
.vg-lang-dropdown {
    position: relative;
    display: inline-block;
    z-index: 50;
    vertical-align: middle;
}
.vg-lang-dropdown.is-open {
    z-index: 10001;
}
.vg-lang-btn {
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
    padding: 0.35rem 0.85rem 0.35rem 0.75rem !important;
    background: rgba(255, 255, 255, 0.90) !important;
    border: 1px solid rgba(22, 68, 59, 0.14) !important;
    border-radius: var(--vg-radius-full, 9999px) !important;
    color: #0f172a !important;
    font-family: 'Inter', 'Noto Sans Devanagari', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
    font-size: 0.84rem !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1) !important;
    box-shadow: 0 2px 8px rgba(22, 68, 59, 0.06) !important;
    line-height: 1.35 !important;
    min-height: 40px !important;
    outline: none !important;
    user-select: none !important;
    box-sizing: border-box !important;
    white-space: nowrap !important;
    flex-shrink: 0 !important;
    position: relative !important;
}
.vg-lang-btn:hover,
.vg-lang-dropdown.is-open .vg-lang-btn {
    background: #FFFFFF !important;
    border-color: rgba(23, 107, 91, 0.35) !important;
    color: #176B5B !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 6px 18px rgba(22, 68, 59, 0.12) !important;
}
.vg-lang-btn:focus-visible {
    border-color: #176B5B !important;
    box-shadow: 0 0 0 3px rgba(23, 107, 91, 0.20) !important;
}
.vg-lang-btn.is-loading {
    cursor: wait !important;
    opacity: 0.9 !important;
    pointer-events: none !important;
}
.vg-lang-icon-globe {
    width: 18px !important;
    height: 18px !important;
    color: #176B5B !important;
    flex-shrink: 0 !important;
    transition: transform 0.24s cubic-bezier(0.4, 0, 0.2, 1) !important;
}
.vg-lang-btn:hover .vg-lang-icon-globe {
    transform: rotate(20deg) scale(1.08) !important;
}
.vg-lang-spinner {
    display: none !important;
    width: 16px !important;
    height: 16px !important;
    border: 2px solid rgba(23, 107, 91, 0.2) !important;
    border-top-color: #176B5B !important;
    border-radius: 50% !important;
    animation: vgLangSpin 0.65s linear infinite !important;
    flex-shrink: 0 !important;
}
.vg-lang-btn.is-loading .vg-lang-spinner {
    display: inline-block !important;
}
.vg-lang-btn.is-loading .vg-lang-icon-globe {
    display: none !important;
}
@keyframes vgLangSpin {
    to { transform: rotate(360deg); }
}
.vg-lang-current-label {
    display: inline-flex !important;
    align-items: center !important;
    font-family: 'Inter', 'Noto Sans Devanagari', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
    font-size: 0.84rem !important;
    font-weight: 700 !important;
    letter-spacing: -0.01em !important;
    line-height: 1.3 !important;
}
.vg-lang-chevron {
    width: 13px !important;
    height: 13px !important;
    color: #64748B !important;
    transition: transform 0.22s ease !important;
    flex-shrink: 0 !important;
}
.vg-lang-dropdown.is-open .vg-lang-chevron {
    transform: rotate(180deg) !important;
    color: #176B5B !important;
}
.vg-lang-menu {
    position: absolute !important;
    top: calc(100% + 8px) !important;
    right: 0 !important;
    min-width: 240px !important;
    background: rgba(255, 255, 255, 0.98) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border: 1px solid rgba(22, 68, 59, 0.12) !important;
    border-radius: 16px !important;
    box-shadow: 0 16px 36px -6px rgba(15, 23, 42, 0.16), 0 0 0 1px rgba(0, 0, 0, 0.04) !important;
    padding: 0.55rem !important;
    z-index: 10003 !important;
    opacity: 0 !important;
    visibility: hidden !important;
    transform: translateY(-6px) scale(0.97) !important;
    transition: opacity 0.18s cubic-bezier(0.4, 0, 0.2, 1), transform 0.18s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.18s cubic-bezier(0.4, 0, 0.2, 1) !important;
    box-sizing: border-box !important;
    pointer-events: none !important;
}
.vg-lang-dropdown.is-open .vg-lang-menu {
    opacity: 1 !important;
    visibility: visible !important;
    transform: translateY(0) scale(1) !important;
    pointer-events: auto !important;
}
.vg-lang-menu-header {
    display: flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
    padding: 0.45rem 0.65rem 0.5rem !important;
    font-size: 0.72rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.06em !important;
    color: #176B5B !important;
    border-bottom: 1px solid rgba(226, 232, 240, 0.8) !important;
    margin-bottom: 0.35rem !important;
    font-family: 'Inter', 'Noto Sans Devanagari', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
}
.vg-lang-form {
    display: block !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    background: transparent !important;
}
.vg-lang-item {
    width: 100% !important;
    box-sizing: border-box !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    padding: 0.6rem 0.75rem !important;
    border-radius: 10px !important;
    background: transparent !important;
    border: none !important;
    color: #0f172a !important;
    font-size: 0.86rem !important;
    font-weight: 600 !important;
    text-align: left !important;
    cursor: pointer !important;
    text-decoration: none !important;
    transition: all 0.16s ease !important;
    outline: none !important;
    font-family: 'Inter', 'Noto Sans Devanagari', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
    line-height: 1.35 !important;
}
.vg-lang-item:hover,
.vg-lang-item:focus-visible {
    background: rgba(23, 107, 91, 0.07) !important;
    color: #176B5B !important;
    transform: translateX(2px) !important;
}
.vg-lang-item.is-selected {
    background: rgba(23, 107, 91, 0.10) !important;
    color: #176B5B !important;
    font-weight: 700 !important;
}
.vg-lang-item-left {
    display: flex !important;
    align-items: center !important;
    gap: 0.65rem !important;
}
.vg-lang-badge-flag {
    width: 28px !important;
    height: 24px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 6px !important;
    background: #F1F5F9 !important;
    color: #1E3E62 !important;
    font-size: 0.72rem !important;
    font-weight: 800 !important;
    border: 1px solid #E2E8F0 !important;
    letter-spacing: 0.02em !important;
    flex-shrink: 0 !important;
}
.vg-lang-item.is-selected .vg-lang-badge-flag {
    background: #176B5B !important;
    color: #FFFFFF !important;
    border-color: #135548 !important;
}
.vg-lang-item-name {
    display: flex !important;
    flex-direction: column !important;
}
.vg-lang-native {
    font-size: 0.86rem !important;
    font-weight: 600 !important;
    line-height: 1.3 !important;
    font-family: 'Inter', 'Noto Sans Devanagari', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
}
.vg-lang-latin {
    font-size: 0.72rem !important;
    color: #64748B !important;
    line-height: 1.15 !important;
}
.vg-lang-check {
    width: 17px !important;
    height: 17px !important;
    color: #176B5B !important;
    flex-shrink: 0 !important;
}
.vg-lang-btn * {
    pointer-events: none !important;
}

/* Smooth live DOM cross-fade during AJAX translation swap */
.vg-lang-fading {
    opacity: 0.45 !important;
    transition: opacity 0.16s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

/* Floating Toast Notification */
.vg-lang-toast-container {
    position: fixed !important;
    bottom: 24px !important;
    right: 24px !important;
    z-index: 999999 !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 10px !important;
    pointer-events: none !important;
}
.vg-lang-toast {
    pointer-events: auto !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.75rem !important;
    padding: 0.72rem 1.15rem !important;
    background: #0B192C !important;
    color: #FFFFFF !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    border-radius: 12px !important;
    box-shadow: 0 16px 36px rgba(11, 25, 44, 0.3), 0 4px 10px rgba(0, 0, 0, 0.12) !important;
    font-family: 'Inter', 'Noto Sans Devanagari', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
    font-size: 0.88rem !important;
    font-weight: 500 !important;
    line-height: 1.4 !important;
    transform: translateY(16px) scale(0.96) !important;
    opacity: 0 !important;
    transition: transform 0.26s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.22s ease !important;
    max-width: 400px !important;
    box-sizing: border-box !important;
}
.vg-lang-toast.show {
    transform: translateY(0) scale(1) !important;
    opacity: 1 !important;
}
.vg-lang-toast-icon {
    width: 22px !important;
    height: 22px !important;
    border-radius: 50% !important;
    background: #10B981 !important;
    color: #FFFFFF !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex-shrink: 0 !important;
}
.vg-lang-toast-icon svg {
    width: 13px !important;
    height: 13px !important;
    stroke-width: 3 !important;
}
.vg-lang-toast-message {
    flex-grow: 1 !important;
}
.vg-lang-toast-close {
    background: transparent !important;
    border: none !important;
    color: #94A3B8 !important;
    cursor: pointer !important;
    padding: 0.2rem !important;
    margin-left: 0.25rem !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 4px !important;
    transition: color 0.15s ease !important;
    line-height: 1 !important;
    font-size: 1.15rem !important;
}
.vg-lang-toast-close:hover {
    color: #FFFFFF !important;
}

@media (max-width: 600px) {
    .vg-lang-btn {
        padding: 0.35rem 0.6rem !important;
        gap: 0.35rem !important;
        min-height: 34px !important;
        font-size: 0.8rem !important;
    }
    .vg-lang-icon-globe {
        width: 15px !important;
        height: 15px !important;
    }
    .vg-lang-chevron {
        width: 11px !important;
        height: 11px !important;
    }
    .vg-lang-menu {
        right: 0 !important;
        left: auto !important;
        min-width: 190px !important;
    }
    .vg-lang-toast-container {
        left: 16px !important;
        right: 16px !important;
        bottom: 16px !important;
    }
    .vg-lang-toast {
        width: 100% !important;
        max-width: 100% !important;
    }
}
</style>
<?php endif; ?>
<div class="vg-lang-dropdown" id="vgLanguageDropdown" data-current-locale="<?= e($currentLocale) ?>">
    <button type="button" 
            class="vg-lang-btn" 
            id="vgLangTriggerBtn" 
            onclick="vgToggleLanguageDropdown(this, event)"
            aria-haspopup="true" 
            aria-expanded="false" 
            aria-controls="vgLangMenu" 
            aria-label="<?= e(__('lang.select_language')) ?> (<?= e($currentLang['native']) ?>)"
            title="<?= e(__('lang.select_language')) ?>">
        <!-- Globe Icon -->
        <svg class="vg-lang-icon-globe" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="2" y1="12" x2="22" y2="12"></line>
            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
        </svg>

        <!-- Loading Micro-Spinner -->
        <span class="vg-lang-spinner" aria-hidden="true"></span>

        <span class="vg-lang-current-label">
            <span class="vg-lang-current-native"><?= e($currentLang['native']) ?></span>
        </span>

        <!-- Chevron -->
        <svg class="vg-lang-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
    </button>

    <div class="vg-lang-menu" id="vgLangMenu" role="menu" aria-labelledby="vgLangTriggerBtn">
        <div class="vg-lang-menu-header">
            <?= e(__('lang.select_language')) ?>
        </div>

        <?php foreach ($supportedLangs as $code => $lang): ?>
            <?php $isSelected = ($code === $currentLocale); ?>
            <form action="<?= url('/language/set') ?>" 
                  method="POST" 
                  class="vg-lang-form" 
                  style="margin: 0;"
                  onsubmit="vgHandleLanguageSubmit(this, event)">
                <?= csrf_field() ?>
                <input type="hidden" name="locale" value="<?= e($code) ?>">
                <input type="hidden" name="return_url" value="<?= e($returnUrl) ?>">
                
                <button type="submit" 
                        class="vg-lang-item <?= $isSelected ? 'is-selected' : '' ?>" 
                        role="menuitem" 
                        data-locale="<?= e($code) ?>"
                        onclick="vgHandleLanguageSelect('<?= e($code) ?>', this, event)"
                        <?= $isSelected ? 'aria-current="true"' : '' ?>
                        title="Switch to <?= e($lang['name']) ?> (<?= e($lang['native']) ?>)">
                    <div class="vg-lang-item-left">
                        <span class="vg-lang-badge-flag"><?= e($lang['short']) ?></span>
                        <div class="vg-lang-item-name">
                            <span class="vg-lang-native"><?= e($lang['native']) ?></span>
                            <?php if ($lang['native'] !== $lang['name']): ?>
                                <span class="vg-lang-latin"><?= e($lang['name']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($isSelected): ?>
                        <svg class="vg-lang-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-label="Selected">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    <?php endif; ?>
                </button>
            </form>
        <?php endforeach; ?>
    </div>
</div>

<script>
(function() {
    'use strict';

    // Dropdown toggle handler
    window.vgToggleLanguageDropdown = function(btn, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        var dropdown = btn ? btn.closest('.vg-lang-dropdown') : null;
        if (!dropdown) return;
        var wasOpen = dropdown.classList.contains('is-open');

        // Close any other open dropdowns
        document.querySelectorAll('.vg-lang-dropdown.is-open').forEach(function(d) {
            d.classList.remove('is-open');
            var b = d.querySelector('.vg-lang-btn');
            if (b) b.setAttribute('aria-expanded', 'false');
        });

        // Close user profile dropdown if open
        var userProfile = document.getElementById('topbarUserProfile');
        if (userProfile) {
            userProfile.classList.remove('is-open');
            userProfile.setAttribute('aria-expanded', 'false');
        }

        if (!wasOpen) {
            dropdown.classList.add('is-open');
            btn.setAttribute('aria-expanded', 'true');
        }
    };

    // Item click handler
    window.vgHandleLanguageSelect = function(targetLocale, itemBtn, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        var form = itemBtn ? itemBtn.closest('form') : null;
        if (!form) return;
        window.vgSwitchLanguage(form, targetLocale, itemBtn);
    };

    // Form submit handler
    window.vgHandleLanguageSubmit = function(form, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        var localeInput = form ? form.querySelector('input[name="locale"]') : null;
        var targetLocale = localeInput ? localeInput.value : '';
        var itemBtn = form ? form.querySelector('button[type="submit"]') : null;
        window.vgSwitchLanguage(form, targetLocale, itemBtn);
    };

    // Core AJAX language switch orchestrator
    window.vgSwitchLanguage = function(form, targetLocale, itemBtn) {
        var dropdown = form ? form.closest('.vg-lang-dropdown') : document.querySelector('.vg-lang-dropdown');
        var triggerBtn = dropdown ? dropdown.querySelector('.vg-lang-btn') : null;
        var currentLocale = dropdown ? dropdown.getAttribute('data-current-locale') : '';

        // If already selected, just close menu
        if (currentLocale && targetLocale && currentLocale === targetLocale) {
            if (dropdown) dropdown.classList.remove('is-open');
            if (triggerBtn) triggerBtn.setAttribute('aria-expanded', 'false');
            return;
        }

        // Prevent duplicate concurrent requests
        if (triggerBtn && triggerBtn.classList.contains('is-loading')) {
            return;
        }

        // Close dropdown and show loading spinner on trigger button
        if (dropdown) dropdown.classList.remove('is-open');
        if (triggerBtn) {
            triggerBtn.classList.add('is-loading');
            triggerBtn.setAttribute('aria-busy', 'true');
            triggerBtn.setAttribute('aria-expanded', 'false');
        }

        // Build FormData
        var formData = new FormData(form);
        if (targetLocale) {
            formData.set('locale', targetLocale);
        }
        var actionUrl = (form && form.action) ? form.action : '/language/set';

        // 1. AJAX POST to /language/set
        fetch(actionUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function(res) {
            if (!res.ok) {
                throw new Error('HTTP ' + res.status + ' received from server');
            }
            return res.json();
        })
        .then(function(data) {
            if (!data || !data.success) {
                throw new Error((data && data.error) ? data.error : 'Language switch failed');
            }

            // Reliable full reload/redirect to ensure all charts, views, sidebars, and tables load the chosen language
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.reload();
            }
        })
        .catch(function(err) {
            console.warn('VoyageGo AJAX Language Switch fallback:', err);
            // Fallback: If network error or unsupported, perform standard form submit
            if (form) {
                form.submit();
            }
        })
        .finally(function() {
            if (triggerBtn) {
                triggerBtn.classList.remove('is-loading');
                triggerBtn.removeAttribute('aria-busy');
            }
        });
    };

    // Live DOM Updater: Preserves form inputs, category states, and scroll positions
    function vgApplyPageTranslations(htmlText, data) {
        var parser = new DOMParser();
        var newDoc = parser.parseFromString(htmlText, 'text/html');

        // 1. Preserve user-entered form inputs
        var savedInputs = {};
        var activeEl = document.activeElement;
        var activeElKey = (activeEl && (activeEl.id || activeEl.name)) ? (activeEl.id || activeEl.name) : null;

        document.querySelectorAll('input, textarea, select').forEach(function(el) {
            if (el.name === '_token' || el.type === 'password' || el.type === 'submit' || el.type === 'button') return;
            var key = el.id || el.name;
            if (!key) return;
            if (el.type === 'radio') {
                if (el.checked) savedInputs[key] = { type: 'radio', value: el.value };
            } else if (el.type === 'checkbox') {
                savedInputs[key] = { type: 'checkbox', checked: el.checked };
            } else {
                savedInputs[key] = { type: el.type, value: el.value };
            }
        });

        // 2. Preserve sidebar expanded state
        var expandedCategories = [];
        document.querySelectorAll('.sidebar-category-items.expanded').forEach(function(el) {
            if (el.id) expandedCategories.push(el.id);
        });

        // 3. Preserve scroll position
        var pageScrollY = window.scrollY;
        var currMain = document.querySelector('.main-content');
        var mainScrollTop = currMain ? currMain.scrollTop : 0;

        // 4. Subtle visual cross-fade transition
        var fadeTargets = document.querySelectorAll('#sidebar, .topbar, .main-content, .bottom-nav, .card');
        fadeTargets.forEach(function(el) { el.classList.add('vg-lang-fading'); });

        // Update html lang and document title
        if (newDoc.documentElement && newDoc.documentElement.lang) {
            document.documentElement.lang = newDoc.documentElement.lang;
        } else if (data.locale) {
            document.documentElement.lang = data.locale;
        }
        if (newDoc.title) {
            document.title = newDoc.title;
        }

        // Swap Sidebar
        var newSidebar = newDoc.querySelector('#sidebar');
        var currSidebar = document.querySelector('#sidebar');
        if (newSidebar && currSidebar) {
            currSidebar.innerHTML = newSidebar.innerHTML;
        }

        // Swap Topbar / Header
        var newTopbar = newDoc.querySelector('.topbar');
        var currTopbar = document.querySelector('.topbar');
        if (newTopbar && currTopbar) {
            currTopbar.innerHTML = newTopbar.innerHTML;
        }

        // Swap Main Content or Auth Card
        var newMain = newDoc.querySelector('.main-content');
        if (newMain && currMain) {
            currMain.innerHTML = newMain.innerHTML;
        } else {
            var newCard = newDoc.querySelector('.card');
            var currCard = document.querySelector('.card');
            if (newCard && currCard) {
                currCard.innerHTML = newCard.innerHTML;
            }
        }

        // Swap Bottom Nav (mobile)
        var newBottom = newDoc.querySelector('.bottom-nav');
        var currBottom = document.querySelector('.bottom-nav');
        if (newBottom && currBottom) {
            currBottom.innerHTML = newBottom.innerHTML;
        }

        // Update all Language Dropdowns on page
        document.querySelectorAll('.vg-lang-dropdown').forEach(function(dd) {
            dd.setAttribute('data-current-locale', data.locale);
            var lbl = dd.querySelector('.vg-lang-current-native');
            if (lbl && data.native) lbl.textContent = data.native;
            var trigger = dd.querySelector('.vg-lang-btn');
            if (trigger && data.native) {
                trigger.setAttribute('aria-label', (newDoc.querySelector('.vg-lang-btn')?.getAttribute('aria-label')) || ('Select Language (' + data.native + ')'));
            }

            dd.querySelectorAll('.vg-lang-item').forEach(function(item) {
                var isNowSelected = item.getAttribute('data-locale') === data.locale;
                if (isNowSelected) {
                    item.classList.add('is-selected');
                    item.setAttribute('aria-current', 'true');
                    if (!item.querySelector('.vg-lang-check')) {
                        var checkSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                        checkSvg.setAttribute('class', 'vg-lang-check');
                        checkSvg.setAttribute('viewBox', '0 0 24 24');
                        checkSvg.setAttribute('fill', 'none');
                        checkSvg.setAttribute('stroke', 'currentColor');
                        checkSvg.setAttribute('stroke-width', '2.5');
                        checkSvg.setAttribute('stroke-linecap', 'round');
                        checkSvg.setAttribute('stroke-linejoin', 'round');
                        checkSvg.setAttribute('aria-label', 'Selected');
                        checkSvg.innerHTML = '<polyline points="20 6 9 17 4 12"></polyline>';
                        item.appendChild(checkSvg);
                    }
                } else {
                    item.classList.remove('is-selected');
                    item.removeAttribute('aria-current');
                    var oldCheck = item.querySelector('.vg-lang-check');
                    if (oldCheck) oldCheck.remove();
                }
            });
        });

        // Synchronize window.VoyageTranslations if present in newDoc head
        newDoc.querySelectorAll('script').forEach(function(s) {
            if (s.textContent.indexOf('window.VoyageTranslations') !== -1) {
                try {
                    var match = s.textContent.match(/window\.VoyageTranslations\s*=\s*(\{[\s\S]*?\});/);
                    if (match && match[1]) {
                        window.VoyageTranslations = JSON.parse(match[1]);
                    }
                } catch (e) {}
            }
        });

        // Restore user-entered input values (except preferred_language so it reflects newly active system language)
        Object.keys(savedInputs).forEach(function(key) {
            if (key === 'preferred_language') return;
            var item = savedInputs[key];
            var el = document.getElementById(key) || document.querySelector('[name="' + key + '"]');
            if (!el) return;
            if (item.type === 'radio') {
                var radioEl = document.querySelector('input[type="radio"][name="' + key + '"][value="' + item.value + '"]');
                if (radioEl) radioEl.checked = true;
            } else if (item.type === 'checkbox') {
                el.checked = item.checked;
            } else {
                el.value = item.value;
            }
        });

        // Restore active focus if applicable
        if (activeElKey) {
            var restoreFocus = document.getElementById(activeElKey) || document.querySelector('[name="' + activeElKey + '"]');
            if (restoreFocus && typeof restoreFocus.focus === 'function') {
                try { restoreFocus.focus(); } catch (e) {}
            }
        }

        // Restore expanded sidebar categories
        expandedCategories.forEach(function(catId) {
            var catItems = document.getElementById(catId);
            if (catItems) {
                catItems.classList.add('expanded');
                var headerBtn = document.getElementById(catId.replace('-items', '-header'));
                if (headerBtn) {
                    headerBtn.classList.add('open');
                    headerBtn.setAttribute('aria-expanded', 'true');
                }
            }
        });

        // Restore scroll positions
        window.scrollTo(0, pageScrollY);
        if (currMain) currMain.scrollTop = mainScrollTop;

        // End fade transition smoothly
        setTimeout(function() {
            fadeTargets.forEach(function(el) { el.classList.remove('vg-lang-fading'); });
        }, 40);

        // Notify client app of language change event
        try {
            document.dispatchEvent(new CustomEvent('voyagego:languageChanged', { detail: data }));
        } catch (e) {}
    }

    // Professional Toast Alert System
    function vgShowLanguageToast(message) {
        var container = document.getElementById('vgLangToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'vgLangToastContainer';
            container.className = 'vg-lang-toast-container';
            document.body.appendChild(container);
        }

        // Clear existing toasts in container
        container.querySelectorAll('.vg-lang-toast').forEach(function(t) { t.remove(); });

        var toast = document.createElement('div');
        toast.className = 'vg-lang-toast';
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');

        var icon = document.createElement('span');
        icon.className = 'vg-lang-toast-icon';
        icon.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';

        var msgSpan = document.createElement('span');
        msgSpan.className = 'vg-lang-toast-message';
        msgSpan.textContent = message;

        var closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'vg-lang-toast-close';
        closeBtn.setAttribute('aria-label', 'Close');
        closeBtn.innerHTML = '&times;';
        closeBtn.onclick = function() {
            toast.classList.remove('show');
            setTimeout(function() { toast.remove(); }, 250);
        };

        toast.appendChild(icon);
        toast.appendChild(msgSpan);
        toast.appendChild(closeBtn);
        container.appendChild(toast);

        // Slide in
        requestAnimationFrame(function() {
            toast.classList.add('show');
        });

        // Auto dismiss after 3.8 seconds
        setTimeout(function() {
            if (toast.parentElement) {
                toast.classList.remove('show');
                setTimeout(function() { toast.remove(); }, 250);
            }
        }, 3800);
    }

    // Global document click-outside & keyboard handlers
    if (!window.__vgLangDropdownDocBound) {
        window.__vgLangDropdownDocBound = true;
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.vg-lang-dropdown')) {
                document.querySelectorAll('.vg-lang-dropdown.is-open').forEach(function(d) {
                    d.classList.remove('is-open');
                    var b = d.querySelector('.vg-lang-btn');
                    if (b) b.setAttribute('aria-expanded', 'false');
                });
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.vg-lang-dropdown.is-open').forEach(function(d) {
                    d.classList.remove('is-open');
                    var b = d.querySelector('.vg-lang-btn');
                    if (b) {
                        b.setAttribute('aria-expanded', 'false');
                        b.focus();
                    }
                });
            }
        });
    }
})();
</script>
