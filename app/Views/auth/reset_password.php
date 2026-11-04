<style>
    /* Card Header */
    .glass-card-header {
        margin-bottom: 1.5rem;
    }

    .glass-card-emblem {
        width: 44px;
        height: 44px;
        object-fit: contain;
        margin-bottom: 0.65rem;
        filter: drop-shadow(0 3px 8px rgba(15, 45, 90, 0.15));
    }

    .glass-card-title {
        font-size: 1.75rem !important;
        font-weight: 800 !important;
        color: #0b326d !important;
        letter-spacing: -0.02em !important;
        line-height: 1.2 !important;
        margin-bottom: 0.35rem !important;
    }

    .glass-card-sub {
        font-size: 0.92rem !important;
        color: #516a87 !important;
        font-weight: 500 !important;
        line-height: 1.45 !important;
    }

    /* Form Fields */
    .glass-field-group {
        margin-bottom: 1.15rem;
        position: relative;
    }

    .glass-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
    }

    .glass-input-icon-left {
        position: absolute;
        left: 1.25rem;
        color: #5c7492;
        font-size: 1.05rem;
        pointer-events: none;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .glass-input-pill {
        width: 100% !important;
        height: 52px !important;
        background: rgba(255, 255, 255, 0.78) !important;
        border: 1.5px solid rgba(255, 255, 255, 0.95) !important;
        border-radius: 9999px !important;
        padding: 0 3.25rem 0 3.1rem !important;
        font-family: inherit !important;
        font-size: 0.95rem !important;
        font-weight: 500 !important;
        color: #0d2c54 !important;
        box-shadow: 0 2px 8px rgba(15, 40, 80, 0.04) !important;
        transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1) !important;
        outline: none !important;
        box-sizing: border-box;
    }

    .glass-input-pill::placeholder {
        color: #7b91ab;
        font-weight: 400;
    }

    .glass-input-pill:focus {
        background: #ffffff !important;
        border-color: #1d72f2 !important;
        box-shadow: 0 0 0 3.5px rgba(29, 114, 242, 0.18), 0 4px 14px rgba(29, 114, 242, 0.12) !important;
    }

    .glass-eye-toggle {
        position: absolute;
        right: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: #6d84a0;
        padding: 0.5rem;
        border-radius: 50%;
        cursor: pointer;
        font-size: 1.05rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s ease, background 0.2s ease;
        z-index: 2;
    }

    .glass-eye-toggle:hover {
        color: #1d72f2;
        background: rgba(29, 114, 242, 0.08);
    }

    /* Password Strength Guidance & Meter */
    .pwd-strength-container {
        margin: -0.35rem 0 1.15rem 0.25rem;
        text-align: left;
    }

    .pwd-meter-bar-track {
        height: 4px;
        background: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 0.45rem;
    }

    .pwd-meter-bar-fill {
        height: 100%;
        width: 0%;
        background: #ef4444;
        transition: width 0.25s ease, background-color 0.25s ease;
    }

    .pwd-criteria-list {
        display: flex;
        flex-direction: column;
        gap: 3px;
        font-size: 0.78rem;
        color: #64748b;
    }

    .pwd-criterion {
        display: flex;
        align-items: center;
        gap: 6px;
        transition: color 0.18s ease;
    }

    .pwd-criterion.is-valid {
        color: #059669;
        font-weight: 600;
    }

    .pwd-criterion.is-valid i {
        color: #10b981;
    }

    .pwd-match-hint {
        font-size: 0.78rem;
        margin: -0.45rem 0 1rem 0.5rem;
        text-align: left;
        display: none;
    }

    /* Primary Submit Button */
    .glass-btn-primary {
        width: 100% !important;
        height: 52px !important;
        background: linear-gradient(90deg, #1f77fa 0%, #0856e2 100%) !important;
        border: none !important;
        border-radius: 9999px !important;
        color: #ffffff !important;
        font-family: inherit !important;
        font-size: 1.05rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.01em !important;
        cursor: pointer !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 0.65rem !important;
        box-shadow: 0 10px 24px rgba(24, 110, 235, 0.38) !important;
        transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1) !important;
        text-decoration: none;
    }

    .glass-btn-primary:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(24, 110, 235, 0.48) !important;
        background: linear-gradient(90deg, #2581ff 0%, #0a5ef3 100%) !important;
    }

    .glass-btn-primary:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none !important;
    }

    /* Navigation Links */
    .recovery-nav-row {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 1.5rem;
        padding-top: 1.15rem;
        border-top: 1px solid rgba(100, 130, 165, 0.2);
        font-size: 0.88rem;
    }

    .recovery-nav-link {
        color: #1d72f2;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: color 0.18s ease;
    }

    .recovery-nav-link:hover {
        color: #0b4ec2;
        text-decoration: underline;
    }

    /* Inline Alert Banners */
    .glass-alert {
        padding: 0.85rem 1.1rem;
        border-radius: 14px;
        margin-bottom: 1.25rem;
        font-size: 0.88rem;
        font-weight: 600;
        display: none;
        align-items: center;
        gap: 0.75rem;
        backdrop-filter: blur(10px);
    }

    .glass-alert-danger {
        background: rgba(254, 242, 242, 0.95);
        border: 1.5px solid rgba(239, 68, 68, 0.35);
        color: #b91c1c;
    }
</style>

<!-- Inside Glassmorphism Card -->
<div id="resetPasswordCard">
    <!-- Form State -->
    <div id="resetFormSection">
        <div class="glass-card-header">
            <?php if (has_company_logo()): ?>
                <img src="<?= e(company_logo_url()) ?>" alt="<?= e(company_display_name()) ?>" class="glass-card-emblem" style="object-fit: contain;">
            <?php else: ?>
                <div class="glass-card-icon-badge" style="width: 44px; height: 44px; font-size: 1.25rem; border-radius: 12px; background: linear-gradient(135deg, #0b326d 0%, #1d72f2 100%); color: #ffffff; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.65rem; box-shadow: 0 4px 12px rgba(11, 50, 109, 0.2);">
                    <i class="fa-solid fa-lock"></i>
                </div>
            <?php endif; ?>
            <h2 class="glass-card-title">Create New Password</h2>
            <p class="glass-card-sub">Choose a strong password to protect your account.</p>
        </div>

        <!-- Inline Error Alert -->
        <?php
        $allFlashes = \App\Core\Session::getAllFlashes();
        $flashErr = $allFlashes['danger'] ?? $allFlashes['error'] ?? null;
        ?>
        <div id="resetPageAlert" class="glass-alert <?= !empty($flashErr) ? 'glass-alert-danger' : '' ?>" style="<?= !empty($flashErr) ? 'display:flex;' : 'display:none;' ?>" role="alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span id="resetPageAlertText"><?= e($flashErr ?? '') ?></span>
        </div>

        <!-- Password Reset Form -->
        <form id="resetPasswordForm" method="POST" action="javascript:void(0);" onsubmit="handleResetPasswordSubmit(event); return false;" autocomplete="off" data-no-ajax="true" data-native="true">
            <?= csrf_field() ?>
            <input type="hidden" name="token" id="resetAuthToken" value="<?= e($token ?? '') ?>">

            <!-- Field 1: New Password -->
            <div class="glass-field-group">
                <div class="glass-input-wrapper">
                    <span class="glass-input-icon-left">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" 
                           id="newPassword" 
                           name="password" 
                           class="glass-input-pill" 
                           required 
                           placeholder="New Password" 
                           aria-label="New Password"
                           autocomplete="new-password">
                    <button type="button" 
                            class="glass-eye-toggle" 
                            onclick="togglePasswordInput('newPassword', 'eyeNew')" 
                            title="Toggle password visibility"
                            aria-label="Toggle password visibility">
                        <i id="eyeNew" class="fa-regular fa-eye-slash"></i>
                    </button>
                </div>
            </div>

            <!-- Password Strength Guidance -->
            <div class="pwd-strength-container">
                <div class="pwd-meter-bar-track">
                    <div id="pwdMeterFill" class="pwd-meter-bar-fill"></div>
                </div>
                <div class="pwd-criteria-list">
                    <div class="pwd-criterion" id="critLength">
                        <i class="fa-regular fa-circle-check"></i> <span>At least 8 characters long</span>
                    </div>
                    <div class="pwd-criterion" id="critLetter">
                        <i class="fa-regular fa-circle-check"></i> <span>Contains at least one letter</span>
                    </div>
                    <div class="pwd-criterion" id="critNumber">
                        <i class="fa-regular fa-circle-check"></i> <span>Contains at least one number</span>
                    </div>
                </div>
            </div>

            <!-- Field 2: Confirm New Password -->
            <div class="glass-field-group">
                <div class="glass-input-wrapper">
                    <span class="glass-input-icon-left">
                        <i class="fa-solid fa-shield-check"></i>
                    </span>
                    <input type="password" 
                           id="confirmPassword" 
                           name="password_confirmation" 
                           class="glass-input-pill" 
                           required 
                           placeholder="Confirm New Password" 
                           aria-label="Confirm New Password"
                           autocomplete="new-password">
                    <button type="button" 
                            class="glass-eye-toggle" 
                            onclick="togglePasswordInput('confirmPassword', 'eyeConfirm')" 
                            title="Toggle password visibility"
                            aria-label="Toggle password visibility">
                        <i id="eyeConfirm" class="fa-regular fa-eye-slash"></i>
                    </button>
                </div>
            </div>
            <div id="pwdMatchHint" class="pwd-match-hint"></div>

            <!-- Save New Password Button -->
            <button type="submit" id="btnSaveNewPassword" class="glass-btn-primary">
                <i class="fa-solid fa-check"></i>
                <span>Save New Password</span>
            </button>
        </form>

        <div class="recovery-nav-row">
            <a href="<?= url('/login') ?>" class="recovery-nav-link" data-no-ajax="true" data-native="true" rel="external">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Login</span>
            </a>
        </div>
    </div>

    <!-- Success State: Step 5 Output -->
    <div id="resetSuccessSection" style="display: none;">
        <div class="glass-card-header">
            <div class="glass-card-icon-badge" style="width: 56px; height: 56px; font-size: 1.6rem; border-radius: 50%; background: #ecfdf5; color: #059669; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.75rem; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.22);">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h2 class="glass-card-title">Password Reset Successfully</h2>
            <p class="glass-card-sub" style="font-size: 0.95rem; line-height: 1.5; margin-top: 0.5rem;">
                Your password has been updated. You can now log in using your new password.
            </p>
        </div>

        <a href="<?= url('/login') ?>" class="glass-btn-primary" style="margin-top: 1.5rem;" data-no-ajax="true" data-native="true" rel="external">
            <i class="fa-solid fa-right-to-bracket"></i>
            <span>Back to Login</span>
        </a>
    </div>
</div>

<script>
function togglePasswordInput(inputId, eyeIconId) {
    var inp = document.getElementById(inputId);
    var eye = document.getElementById(eyeIconId);
    if (!inp) return;

    if (inp.type === 'password') {
        inp.type = 'text';
        if (eye) {
            eye.classList.remove('fa-eye-slash');
            eye.classList.add('fa-eye');
        }
    } else {
        inp.type = 'password';
        if (eye) {
            eye.classList.remove('fa-eye');
            eye.classList.add('fa-eye-slash');
        }
    }
}

function showResetAlert(msg) {
    var box = document.getElementById('resetPageAlert');
    var text = document.getElementById('resetPageAlertText');
    if (!box || !text) return;
    text.textContent = msg;
    box.style.display = 'flex';
}

function hideResetAlert() {
    var box = document.getElementById('resetPageAlert');
    if (box) box.style.display = 'none';
}

// Password strength & criteria validator
function updatePasswordStrength() {
    var pwd = (document.getElementById('newPassword').value || '');
    var fill = document.getElementById('pwdMeterFill');
    var critLength = document.getElementById('critLength');
    var critLetter = document.getElementById('critLetter');
    var critNumber = document.getElementById('critNumber');

    var hasLen = pwd.length >= 8;
    var hasLet = /[A-Za-z]/.test(pwd);
    var hasNum = /\d/.test(pwd);

    if (critLength) critLength.className = hasLen ? 'pwd-criterion is-valid' : 'pwd-criterion';
    if (critLetter) critLetter.className = hasLet ? 'pwd-criterion is-valid' : 'pwd-criterion';
    if (critNumber) critNumber.className = hasNum ? 'pwd-criterion is-valid' : 'pwd-criterion';

    var score = 0;
    if (hasLen) score++;
    if (hasLet) score++;
    if (hasNum) score++;
    if (pwd.length >= 10 && /[!@#$%^&*(),.?":{}|<>]/.test(pwd)) score++;

    if (fill) {
        if (pwd.length === 0) {
            fill.style.width = '0%';
        } else if (score <= 1) {
            fill.style.width = '25%';
            fill.style.backgroundColor = '#ef4444'; // Red
        } else if (score === 2) {
            fill.style.width = '50%';
            fill.style.backgroundColor = '#f59e0b'; // Amber
        } else if (score === 3) {
            fill.style.width = '75%';
            fill.style.backgroundColor = '#3b82f6'; // Blue
        } else {
            fill.style.width = '100%';
            fill.style.backgroundColor = '#10b981'; // Green
        }
    }

    checkMatch();
}

function checkMatch() {
    var pwd = (document.getElementById('newPassword').value || '');
    var conf = (document.getElementById('confirmPassword').value || '');
    var hint = document.getElementById('pwdMatchHint');
    if (!hint) return;

    if (!conf) {
        hint.style.display = 'none';
        return;
    }

    hint.style.display = 'block';
    if (pwd === conf) {
        hint.innerHTML = '<span style="color:#059669; font-weight:600;"><i class="fa-solid fa-check"></i> Passwords match</span>';
    } else {
        hint.innerHTML = '<span style="color:#dc2626; font-weight:600;"><i class="fa-solid fa-xmark"></i> Passwords do not match</span>';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var newPwd = document.getElementById('newPassword');
    var confPwd = document.getElementById('confirmPassword');
    if (newPwd) {
        newPwd.addEventListener('input', updatePasswordStrength);
    }
    if (confPwd) {
        confPwd.addEventListener('input', checkMatch);
    }
});

function handleResetPasswordSubmit(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    hideResetAlert();

    var token = (document.getElementById('resetAuthToken').value || '').trim();
    var password = (document.getElementById('newPassword').value || '');
    var confirmPassword = (document.getElementById('confirmPassword').value || '');
    var btn = document.getElementById('btnSaveNewPassword');

    if (!token) {
        showResetAlert('Missing or expired recovery authorization token. Please start over.');
        return false;
    }

    if (!password) {
        showResetAlert('Please enter a new password.');
        document.getElementById('newPassword').focus();
        return false;
    }

    if (password.length < 8) {
        showResetAlert('Password must be at least 8 characters long.');
        document.getElementById('newPassword').focus();
        return false;
    }

    if (!/[A-Za-z]/.test(password) || !/\d/.test(password)) {
        showResetAlert('Password must contain at least one letter and one number.');
        document.getElementById('newPassword').focus();
        return false;
    }

    if (password !== confirmPassword) {
        showResetAlert('The entered passwords do not match. Please verify both fields.');
        document.getElementById('confirmPassword').focus();
        return false;
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Updating Password...</span>';
    }

    var csrfToken = '';
    var meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) csrfToken = meta.getAttribute('content');
    if (!csrfToken) {
        var inp = document.querySelector('input[name="_csrf_token"]');
        if (inp) csrfToken = inp.value;
    }

    var formData = new FormData();
    formData.append('token', token);
    formData.append('password', password);
    formData.append('password_confirmation', confirmPassword);
    formData.append('_csrf_token', csrfToken);

    fetch('<?= url('/forgot-password/reset') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(function(res) {
        return res.json().then(data => ({ status: res.status, ok: res.ok, data: data }));
    })
    .then(function(res) {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> <span>Save New Password</span>';
        }

        if (res.ok && res.data.success) {
            // Hide Form Section, Show Step 5 Success Section
            var formSec = document.getElementById('resetFormSection');
            var succSec = document.getElementById('resetSuccessSection');
            if (formSec) formSec.style.display = 'none';
            if (succSec) succSec.style.display = 'block';
        } else {
            var errMsg = (res.data && res.data.message) ? res.data.message : 'Unable to update password. Please check requirements and retry.';
            showResetAlert(errMsg);
        }
    })
    .catch(function(err) {
        console.error('Password reset submit error:', err);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> <span>Save New Password</span>';
        }
        showResetAlert('A network error occurred. Please try again.');
    });

    return false;
}
</script>

