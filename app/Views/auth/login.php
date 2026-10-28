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
        font-size: 1.85rem !important;
        font-weight: 800 !important;
        color: #0b326d !important;
        letter-spacing: -0.02em !important;
        line-height: 1.2 !important;
        margin-bottom: 0.3rem !important;
    }

    .glass-card-sub {
        font-size: 0.95rem !important;
        color: #516a87 !important;
        font-weight: 500 !important;
    }

    /* Form Fields with Frosted Pill Inputs */
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
        padding: 0 1.25rem 0 3.1rem !important;
        font-family: inherit !important;
        font-size: 0.95rem !important;
        font-weight: 500 !important;
        color: #0d2c54 !important;
        box-shadow: 0 2px 8px rgba(15, 40, 80, 0.04) !important;
        transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1) !important;
        outline: none !important;
    }

    .glass-input-pill::placeholder {
        color: #7b91ab;
        font-weight: 400;
    }

    .glass-input-pill:hover {
        background: rgba(255, 255, 255, 0.88) !important;
        border-color: rgba(255, 255, 255, 1) !important;
    }

    .glass-input-pill:focus {
        background: #ffffff !important;
        border-color: #1d72f2 !important;
        box-shadow: 0 0 0 3.5px rgba(29, 114, 242, 0.18), 0 4px 14px rgba(29, 114, 242, 0.12) !important;
    }

    .glass-input-wrapper.has-toggle .glass-input-pill {
        padding-right: 3.25rem !important;
    }

    /* Masked Password Field to prevent Chrome & Browser Credential Leak Checkup on All Devices */
    .masked-password-field {
        -webkit-text-security: disc !important;
        text-security: disc !important;
        letter-spacing: 0.1em;
    }

    .masked-password-field.revealed {
        -webkit-text-security: none !important;
        text-security: none !important;
        letter-spacing: normal;
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

    /* Remember Me & Forgot Password Row */
    .glass-options-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 0.5rem 0 1.45rem;
        font-size: 0.88rem;
    }

    .glass-checkbox-label {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        cursor: pointer;
        user-select: none;
        color: #3e5674;
        font-weight: 500;
    }

    .glass-checkbox-label input[type="checkbox"] {
        appearance: none;
        -webkit-appearance: none;
        width: 18px;
        height: 18px;
        border: 1.8px solid #8ba1ba;
        border-radius: 5px;
        background: rgba(255, 255, 255, 0.8);
        cursor: pointer;
        display: grid;
        place-content: center;
        transition: all 0.18s ease;
    }

    .glass-checkbox-label input[type="checkbox"]:checked {
        background: #1d72f2;
        border-color: #1d72f2;
    }

    .glass-checkbox-label input[type="checkbox"]:checked::before {
        content: "\f00c";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        font-size: 10px;
        color: #ffffff;
    }

    .glass-forgot-link {
        color: #1d72f2 !important;
        font-weight: 600 !important;
        text-decoration: none !important;
        transition: color 0.18s ease, text-decoration 0.18s ease;
    }

    .glass-forgot-link:hover {
        color: #0b4ec2 !important;
        text-decoration: underline !important;
    }

    /* Submit Login Button */
    .glass-btn-login {
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
        position: relative;
        overflow: hidden;
    }

    .glass-btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(24, 110, 235, 0.48) !important;
        background: linear-gradient(90deg, #2581ff 0%, #0a5ef3 100%) !important;
    }

    .glass-btn-login:active {
        transform: translateY(0);
        box-shadow: 0 6px 16px rgba(24, 110, 235, 0.35) !important;
    }

    /* Professional Security Seal */
    .glass-security-footer {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 1.65rem;
        padding-top: 1.25rem;
        border-top: 1px solid rgba(100, 130, 165, 0.2);
        color: #557292;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.01em;
        text-align: center;
    }

    .glass-security-footer i {
        color: #1d72f2;
        font-size: 0.88rem;
    }

    /* Glass Flash Alerts */
    .glass-alert {
        padding: 0.75rem 1rem;
        border-radius: 16px;
        margin-bottom: 1.25rem;
        font-size: 0.88rem;
        display: flex;
        align-items: center;
        gap: 0.65rem;
        backdrop-filter: blur(12px);
        transition: opacity 0.4s ease, transform 0.4s ease, max-height 0.4s ease, margin 0.4s ease, padding 0.4s ease;
        max-height: 120px;
        overflow: hidden;
    }

    .glass-alert.fade-out {
        opacity: 0;
        transform: translateY(-8px);
        max-height: 0;
        margin-top: 0;
        margin-bottom: 0;
        padding-top: 0;
        padding-bottom: 0;
        border: none;
    }

    .glass-alert-danger {
        background: rgba(220, 53, 69, 0.12);
        border: 1px solid rgba(220, 53, 69, 0.3);
        color: #a71d2a;
    }

    .glass-alert-success {
        background: rgba(40, 167, 69, 0.12);
        border: 1px solid rgba(40, 167, 69, 0.3);
        color: #155724;
    }

    .glass-alert-info {
        background: rgba(13, 110, 253, 0.12);
        border: 1px solid rgba(13, 110, 253, 0.3);
        color: #0b4ec2;
    }

    .glass-alert-close {
        margin-left: auto;
        background: none;
        border: none;
        color: inherit;
        opacity: 0.55;
        cursor: pointer;
        font-size: 1.15rem;
        line-height: 1;
        padding: 0 0.25rem;
        transition: opacity 0.2s ease;
    }

    .glass-alert-close:hover {
        opacity: 1;
    }

    /* Developer Quick Fill Tool (Discrete Collapsible Chip) */
    .dev-quickfill-box {
        margin-top: 1.25rem;
        padding-top: 0.75rem;
    }

    .dev-quickfill-toggle {
        background: none;
        border: none;
        color: #6d84a0;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        width: 100%;
        padding: 0.3rem;
    }

    .dev-quickfill-toggle:hover {
        color: #1d72f2;
    }

    .dev-quickfill-grid {
        display: none;
        grid-template-columns: 1fr 1fr;
        gap: 0.4rem;
        margin-top: 0.65rem;
    }

    .dev-quickfill-grid.is-open {
        display: grid;
    }

    .dev-cred-btn {
        background: rgba(255, 255, 255, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.9);
        border-radius: 8px;
        padding: 0.4rem 0.5rem;
        font-size: 0.76rem;
        font-weight: 600;
        color: #1b3858;
        cursor: pointer;
        text-align: left;
        transition: all 0.18s ease;
    }

    .dev-cred-btn:hover {
        background: #ffffff;
        color: #1d72f2;
        border-color: #1d72f2;
    }
</style>

<!-- Inside Glassmorphism Card Header -->
<div class="glass-card-header">
    <img src="<?= asset('images/manaswi-icon.png') ?>" alt="Manaswi Emblem" class="glass-card-emblem">
    <h2 class="glass-card-title"><?= e(__('auth.welcome_back', 'Welcome Back')) ?></h2>
    <p class="glass-card-sub"><?= e(__('auth.signin_prompt', 'Sign in to continue your journey')) ?></p>
</div>

<!-- Active Flash Notifications -->
<?php $activeFlashes = !empty($flashes) ? $flashes : \App\Core\Session::getAllFlashes(); ?>
<?php if (!empty($activeFlashes)): ?>
    <?php foreach ($activeFlashes as $type => $message): ?>
        <?php
            $alertClass = 'glass-alert-success';
            $alertIcon = 'fa-circle-check';
            if ($type === 'danger' || $type === 'error') {
                $alertClass = 'glass-alert-danger';
                $alertIcon = 'fa-circle-exclamation';
            } elseif ($type === 'info') {
                $alertClass = 'glass-alert-info';
                $alertIcon = 'fa-circle-info';
            }
        ?>
        <div class="glass-alert <?= $alertClass ?> flash-auto-dismiss" role="alert">
            <i class="fa-solid <?= $alertIcon ?>"></i>
            <span><?= e($message) ?></span>
            <button type="button" class="glass-alert-close" onclick="dismissAlert(this.closest('.glass-alert'))" aria-label="Dismiss">&times;</button>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Primary Authentication Form -->
<form action="<?= url('/login') ?>" method="POST" id="loginForm" autocomplete="off">
    <?= csrf_field() ?>

    <!-- Field 1: Email or Mobile Number -->
    <div class="glass-field-group">
        <div class="glass-input-wrapper">
            <span class="glass-input-icon-left">
                <i class="fa-regular fa-user"></i>
            </span>
            <input type="text" 
                   id="login" 
                   name="login" 
                   class="glass-input-pill" 
                   required 
                   autofocus 
                   placeholder="<?= e(__('auth.username_or_email', 'Email or Mobile Number')) ?>" 
                   value="<?= e($oldLogin ?? $oldEmail ?? '') ?>"
                   aria-label="Email or Mobile Number"
                   autocomplete="username">
        </div>
    </div>

    <!-- Field 2: Password with Reveal Toggle (Secured against browser breach checkup modals) -->
    <div class="glass-field-group">
        <div class="glass-input-wrapper has-toggle">
            <span class="glass-input-icon-left">
                <i class="fa-solid fa-lock"></i>
            </span>
            <input type="text" 
                   id="password" 
                   name="password" 
                   class="glass-input-pill masked-password-field" 
                   required 
                   placeholder="<?= e(__('auth.password', 'Password')) ?>" 
                   aria-label="Password"
                   autocomplete="off"
                   autocorrect="off"
                   autocapitalize="off"
                   spellcheck="false"
                   data-lpignore="true"
                   data-form-type="other">
            <button type="button" 
                    id="togglePasswordBtn" 
                    class="glass-eye-toggle" 
                    onclick="togglePasswordVisibility()" 
                    title="Toggle password visibility"
                    aria-label="Toggle password visibility">
                <i id="eyeIcon" class="fa-regular fa-eye-slash"></i>
            </button>
        </div>
    </div>

    <!-- Remember Me & Forgot Password -->
    <div class="glass-options-row">
        <label class="glass-checkbox-label" for="remember">
            <input type="checkbox" name="remember" id="remember" value="1" checked>
            <span><?= e(__('auth.remember_me', 'Remember Me')) ?></span>
        </label>
        <a href="<?= url('/forgot-password') ?>" class="glass-forgot-link">
            <?= e(__('auth.forgot_password', 'Forgot Password?')) ?>
        </a>
    </div>

    <!-- Submit Button: Arrow + Login -->
    <button type="submit" id="signInBtn" class="glass-btn-login">
        <i class="fa-solid fa-arrow-right"></i>
        <span><?= e(__('auth.login_btn', 'Login')) ?></span>
    </button>
</form>

<!-- Professional Security Seal -->
<div class="glass-security-footer">
    <i class="fa-solid fa-shield-halved"></i>
    <span>Enterprise Operations Gateway &bull; 256-Bit SSL Encrypted</span>
</div>

<?php if (config('app.env') === 'development' || config('app.debug') === true): ?>
<!-- Development Quick Fill Credentials Toggle -->
<div class="dev-quickfill-box">
    <button type="button" class="dev-quickfill-toggle" onclick="toggleDevCreds()">
        <i class="fa-solid fa-flask"></i> Quick Dev Credentials <i class="fa-solid fa-chevron-down" id="devChevron"></i>
    </button>
    <div class="dev-quickfill-grid" id="devCredsGrid">
        <button type="button" class="dev-cred-btn" onclick="fillCreds('owner', 'owner123', true)" title="Instant Login as System Owner">
            👑 System Owner <span style="font-size:0.7rem; color:#10B981; font-weight:700;">(Auto-Login)</span>
        </button>
        <button type="button" class="dev-cred-btn" onclick="fillCreds('admin', 'Admin@12345', true)" title="Instant Login as Administrator">
            💼 Administrator <span style="font-size:0.7rem; color:#10B981; font-weight:700;">(Auto-Login)</span>
        </button>
        <button type="button" class="dev-cred-btn" onclick="fillCreds('dispatcher', 'Dispatcher@12345', true)" title="Instant Login as Dispatcher">
            📡 Dispatcher <span style="font-size:0.7rem; color:#10B981; font-weight:700;">(Auto-Login)</span>
        </button>
        <button type="button" class="dev-cred-btn" onclick="fillCreds('rajesh.driver', 'Driver@12345')">
            🚗 Chauffeur
        </button>
    </div>
</div>
<?php endif; ?>

<script>
function togglePasswordVisibility() {
    var pwdInput = document.getElementById('password');
    var eyeIcon = document.getElementById('eyeIcon');
    if (!pwdInput) return;
    
    var isRevealed = pwdInput.classList.contains('revealed') || pwdInput.type === 'text';
    if (pwdInput.classList.contains('masked-password-field')) {
        if (pwdInput.classList.contains('revealed')) {
            pwdInput.classList.remove('revealed');
            if (eyeIcon) {
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            }
        } else {
            pwdInput.classList.add('revealed');
            if (eyeIcon) {
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    } else {
        if (pwdInput.type === 'password') {
            pwdInput.type = 'text';
            if (eyeIcon) {
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        } else {
            pwdInput.type = 'password';
            if (eyeIcon) {
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            }
        }
    }
}

function toggleDevCreds() {
    var grid = document.getElementById('devCredsGrid');
    var chevron = document.getElementById('devChevron');
    if (!grid) return;
    grid.classList.toggle('is-open');
    if (chevron) {
        chevron.style.transform = grid.classList.contains('is-open') ? 'rotate(180deg)' : 'rotate(0deg)';
    }
}

function fillCreds(login, password, andSubmit) {
    var l = document.getElementById('login');
    var p = document.getElementById('password');
    if (l) l.value = login;
    if (p) p.value = password;
    if (andSubmit) {
        setTimeout(function() {
            var form = document.getElementById('loginForm');
            if (form) {
                var submitEvt = new Event('submit', { cancelable: true });
                form.dispatchEvent(submitEvt);
            }
        }, 60);
    }
}

function dismissAlert(alertEl) {
    if (!alertEl) return;
    alertEl.classList.add('fade-out');
    setTimeout(function() {
        if (alertEl.parentNode) alertEl.parentNode.removeChild(alertEl);
    }, 400);
}

function showLoginAlert(msg, type) {
    var existing = document.querySelector('.glass-alert');
    if (existing) existing.remove();

    var alertDiv = document.createElement('div');
    alertDiv.className = 'glass-alert ' + (type === 'danger' ? 'glass-alert-danger' : 'glass-alert-info') + ' flash-auto-dismiss';
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> <span>' + msg + '</span>' +
                         '<button type="button" class="glass-alert-close" onclick="dismissAlert(this.closest(\'.glass-alert\'))" aria-label="Dismiss">&times;</button>';

    var formEl = document.getElementById('loginForm');
    if (formEl && formEl.parentNode) {
        formEl.parentNode.insertBefore(alertDiv, formEl);
    }

    setTimeout(function() {
        dismissAlert(alertDiv);
    }, 4500);
}

document.addEventListener('DOMContentLoaded', function () {
    // Auto-dismiss existing flash alerts
    var alerts = document.querySelectorAll('.flash-auto-dismiss');
    alerts.forEach(function (alert) {
        var timer = null;
        var remaining = 3500;
        var start = Date.now();

        function startTimer() {
            start = Date.now();
            timer = setTimeout(function () {
                dismissAlert(alert);
            }, remaining);
        }

        alert.addEventListener('mouseenter', function () {
            if (timer) {
                clearTimeout(timer);
                timer = null;
                remaining = Math.max(500, remaining - (Date.now() - start));
            }
        });

        alert.addEventListener('mouseleave', function () {
            if (!timer) startTimer();
        });

        startTimer();
    });

    // Lightning-fast AJAX Submit Handler for Login
    var form = document.getElementById('loginForm');
    var btn = document.getElementById('signInBtn');

    if (form && btn) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (btn.dataset.submitting === 'true') {
                return false;
            }

            var loginInput = document.getElementById('login');
            var passInput = document.getElementById('password');
            var loginVal = (loginInput ? loginInput.value : '').trim();
            var passVal = (passInput ? passInput.value : '');

            if (!loginVal || !passVal) {
                showLoginAlert('Please enter your username/email and password.', 'danger');
                if (!loginVal && loginInput) loginInput.focus();
                else if (passInput) passInput.focus();
                return false;
            }

            btn.dataset.submitting = 'true';
            btn.style.pointerEvents = 'none';
            btn.style.opacity = '0.88';
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Authenticating...</span>';

            var formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(function(res) {
                if (res.redirected) {
                    window.location.href = res.url;
                    return null;
                }
                return res.json().catch(function() {
                    if (res.ok) {
                        window.location.href = '<?= url('/office/dashboard') ?>';
                        return null;
                    }
                    throw new Error('Authentication failed (HTTP ' + res.status + ')');
                });
            })
            .then(function(data) {
                if (!data) return;

                if (data.success) {
                    btn.style.background = 'linear-gradient(135deg, #10B981 0%, #059669 100%)';
                    btn.innerHTML = '<i class="fa-solid fa-circle-check"></i> <span>Success! Opening Admin Panel...</span>';
                    var target = data.redirect || '<?= url('/office/dashboard') ?>';
                    window.location.href = target;
                } else {
                    btn.dataset.submitting = 'false';
                    btn.style.pointerEvents = '';
                    btn.style.opacity = '1';
                    btn.innerHTML = '<i class="fa-solid fa-arrow-right"></i> <span><?= e(__('auth.login_btn', 'Login')) ?></span>';
                    showLoginAlert(data.message || 'Invalid credentials provided. Please check username or password.', 'danger');
                }
            })
            .catch(function(err) {
                console.error('AJAX Login error:', err);
                // Fallback to standard form submission
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Signing in...</span>';
                form.submit();
            });
        });
    }
});
</script>
