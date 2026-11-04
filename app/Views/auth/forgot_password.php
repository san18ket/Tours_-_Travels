<style>
    /* Card Header */
    .glass-card-header {
        margin-bottom: 1.65rem;
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
        margin-bottom: 1.25rem;
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
        padding: 0 1.25rem 0 3.1rem !important;
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

    .glass-input-pill.has-error {
        border-color: #ef4444 !important;
        background: rgba(254, 242, 242, 0.95) !important;
        box-shadow: 0 0 0 3.5px rgba(239, 68, 68, 0.2) !important;
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
        justify-content: space-between;
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

    .glass-alert-info {
        background: rgba(239, 246, 255, 0.95);
        border: 1.5px solid rgba(37, 99, 235, 0.35);
        color: #1d4ed8;
    }
</style>

<!-- Inside Glassmorphism Card -->
<div class="glass-card-header">
    <?php if (has_company_logo()): ?>
        <img src="<?= e(company_logo_url()) ?>" alt="<?= e(company_display_name()) ?>" class="glass-card-emblem" style="object-fit: contain;">
    <?php else: ?>
        <div class="glass-card-icon-badge" style="width: 44px; height: 44px; font-size: 1.25rem; border-radius: 12px; background: linear-gradient(135deg, #0b326d 0%, #1d72f2 100%); color: #ffffff; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.65rem; box-shadow: 0 4px 12px rgba(11, 50, 109, 0.2);">
            <i class="fa-solid fa-key"></i>
        </div>
    <?php endif; ?>
    <h2 class="glass-card-title">Reset Password</h2>
    <p class="glass-card-sub">Enter your registered email address to receive a secure 4-digit verification code.</p>
</div>

<!-- Flash Alerts if any -->
<?php
$allFlashes = \App\Core\Session::getAllFlashes();
$flashErr = $allFlashes['danger'] ?? $allFlashes['error'] ?? null;
?>
<div id="passwordPageAlert" class="glass-alert <?= !empty($flashErr) ? 'glass-alert-danger' : '' ?>" style="<?= !empty($flashErr) ? 'display:flex;' : 'display:none;' ?>" role="alert">
    <i id="passwordPageAlertIcon" class="fa-solid fa-circle-exclamation"></i>
    <span id="passwordPageAlertText"><?= e($flashErr ?? '') ?></span>
</div>

<!-- Form: Enter Registered Email -->
<form id="forgotPasswordForm" method="POST" action="javascript:void(0);" onsubmit="handleSendPasswordOtp(event); return false;" autocomplete="off" data-no-ajax="true" data-native="true">
    <?= csrf_field() ?>

    <div class="glass-field-group">
        <div class="glass-input-wrapper">
            <span class="glass-input-icon-left">
                <i class="fa-regular fa-envelope"></i>
            </span>
            <input type="email" 
                   id="recoveryEmail" 
                   name="email" 
                   class="glass-input-pill" 
                   required 
                   autofocus 
                   placeholder="Registered Email Address" 
                   aria-label="Registered Email Address"
                   autocomplete="email">
        </div>
    </div>

    <button type="submit" id="btnSendPasswordOtp" class="glass-btn-primary">
        <i class="fa-solid fa-paper-plane"></i>
        <span>Send OTP</span>
    </button>
</form>

<!-- Navigation links -->
<div class="recovery-nav-row">
    <a href="<?= url('/forgot-account') ?>" class="recovery-nav-link" data-no-ajax="true" data-native="true" rel="external">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Recovery Options</span>
    </a>
    <a href="<?= url('/login') ?>" class="recovery-nav-link" data-no-ajax="true" data-native="true" rel="external">
        <i class="fa-solid fa-right-to-bracket"></i>
        <span>Back to Login</span>
    </a>
</div>

<!-- Reusable 4-Digit OTP Modal -->
<?php
$purpose = 'recovery_password';
require __DIR__ . '/partials/recovery_otp_modal.php';
?>

<script>
function escapeHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function showAlert(msg, isError = true, title = null) {
    var box = document.getElementById('passwordPageAlert');
    var icon = document.getElementById('passwordPageAlertIcon');
    var text = document.getElementById('passwordPageAlertText');
    if (!box || !text) return;

    box.className = isError ? 'glass-alert glass-alert-danger' : 'glass-alert glass-alert-info';
    if (icon) {
        icon.className = isError ? 'fa-solid fa-circle-exclamation' : 'fa-solid fa-circle-info';
    }
    if (title && isError) {
        text.innerHTML = '<strong style="display:block; margin-bottom:2px;">' + escapeHtml(title) + '</strong>' + escapeHtml(msg);
    } else {
        text.textContent = msg;
    }
    box.style.display = 'flex';
}

function hideAlert() {
    var box = document.getElementById('passwordPageAlert');
    if (box) box.style.display = 'none';
}

function handleSendPasswordOtp(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    hideAlert();

    var emailInput = document.getElementById('recoveryEmail');
    var email = (emailInput ? emailInput.value : '').trim().toLowerCase();
    var btn = document.getElementById('btnSendPasswordOtp');

    if (emailInput) emailInput.classList.remove('has-error');

    if (!email) {
        showAlert('Please enter your registered email address.');
        if (emailInput) {
            emailInput.classList.add('has-error');
            emailInput.focus();
        }
        return false;
    }

    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showAlert('Please enter a valid email address format.');
        if (emailInput) {
            emailInput.classList.add('has-error');
            emailInput.focus();
        }
        return false;
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Sending OTP...</span>';
    }

    var csrfToken = '';
    var meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) csrfToken = meta.getAttribute('content');
    if (!csrfToken) {
        var inp = document.querySelector('input[name="_csrf_token"]');
        if (inp) csrfToken = inp.value;
    }

    var formData = new FormData();
    formData.append('email', email);
    formData.append('_csrf_token', csrfToken);

    fetch('<?= url('/forgot-password/send-otp') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(function(res) {
        return res.json().then(data => ({ status: res.status, ok: res.ok, data: data }));
    })
    .then(function(res) {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> <span>Send OTP</span>';
        }

        if (res.ok && res.data.success) {
            // Email is registered and eligible in database, OTP dispatched -> open 4-digit modal
            window.vgRecoveryOtp.openModal(email, res.data.cooldown || 60);
        } else {
            // Not registered, inactive, suspended, duplicate or delivery error -> show custom error message and highlight input
            var errMsg = (res.data && res.data.message) ? res.data.message : "We couldn't find an account associated with this email address. Please check your email and try again.";
            var errTitle = (res.data && res.data.error) ? res.data.error : null;
            showAlert(errMsg, true, errTitle);
            if (emailInput) {
                emailInput.classList.add('has-error');
                emailInput.focus();
            }
        }
    })
    .catch(function(err) {
        console.error('Send OTP error:', err);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> <span>Send OTP</span>';
        }
        showAlert('Network connection error. Please try again.', true);
    });

    return false;
}

document.addEventListener('DOMContentLoaded', function() {
    var emailInput = document.getElementById('recoveryEmail');
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            this.classList.remove('has-error');
            hideAlert();
        });
    }
});

// Callback invoked by modal upon successful server-side OTP verification
window.onRecoveryOtpVerified = function(recoveryToken, verifiedEmail) {
    // Navigate immediately to the reset password form with the short-lived recovery token
    window.location.href = '<?= url('/forgot-password/reset?token=') ?>' + encodeURIComponent(recoveryToken);
};
</script>

