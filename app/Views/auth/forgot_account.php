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

    /* Option Selection Cards */
    .recovery-options-grid {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .recovery-option-card {
        background: rgba(255, 255, 255, 0.78);
        border: 1.5px solid rgba(255, 255, 255, 0.95);
        border-radius: 18px;
        padding: 1.25rem 1.15rem;
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        text-align: left;
        display: flex;
        flex-direction: column;
        box-shadow: 0 4px 14px rgba(15, 40, 80, 0.05);
        position: relative;
    }

    .recovery-option-card:hover {
        background: #ffffff;
        border-color: #1d72f2;
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(29, 114, 242, 0.15);
    }

    .recovery-option-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.45rem;
    }

    .recovery-option-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #eff6ff;
        color: #1d72f2;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .recovery-option-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0b326d;
        margin: 0;
    }

    .recovery-option-desc {
        font-size: 0.86rem;
        color: #516a87;
        line-height: 1.45;
        margin: 0 0 1rem 0;
    }

    .recovery-option-btn {
        width: 100%;
        height: 44px;
        background: linear-gradient(90deg, #1f77fa 0%, #0856e2 100%);
        border: none;
        border-radius: 9999px;
        color: #ffffff;
        font-family: inherit;
        font-size: 0.92rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(24, 110, 235, 0.28);
        transition: all 0.2s ease;
    }

    .recovery-option-btn:hover {
        background: linear-gradient(90deg, #2581ff 0%, #0a5ef3 100%);
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(24, 110, 235, 0.38);
        color: #ffffff;
    }

    /* Back to Login Link */
    .recovery-back-row {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 1.25rem;
        padding-top: 1.15rem;
        border-top: 1px solid rgba(100, 130, 165, 0.2);
    }

    .recovery-back-link {
        color: #1d72f2;
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: color 0.18s ease;
    }

    .recovery-back-link:hover {
        color: #0b4ec2;
        text-decoration: underline;
    }
</style>

<!-- Inside Glassmorphism Card Header -->
<div class="glass-card-header">
    <?php if (has_company_logo()): ?>
        <img src="<?= e(company_logo_url()) ?>" alt="<?= e(company_display_name()) ?>" class="glass-card-emblem" style="object-fit: contain;">
    <?php else: ?>
        <div class="glass-card-icon-badge" style="width: 44px; height: 44px; font-size: 1.25rem; border-radius: 12px; background: linear-gradient(135deg, #0b326d 0%, #1d72f2 100%); color: #ffffff; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.65rem; box-shadow: 0 4px 12px rgba(11, 50, 109, 0.2);">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
    <?php endif; ?>
    <h2 class="glass-card-title">Recover Your Account</h2>
    <p class="glass-card-sub">Don't worry! Follow a few simple steps to recover your account.</p>
</div>

<!-- Option Selection Grid -->
<div class="recovery-options-grid">
    <!-- Option A: Forgot Username -->
    <div class="recovery-option-card">
        <div class="recovery-option-header">
            <div class="recovery-option-icon">
                <i class="fa-solid fa-user-tag"></i>
            </div>
            <h3 class="recovery-option-title">Forgot Username</h3>
        </div>
        <p class="recovery-option-desc">Forgot your username? Recover it using your registered email address.</p>
        <a href="<?= url('/forgot-username') ?>" class="recovery-option-btn" data-no-ajax="true" data-native="true" rel="external">
            <i class="fa-solid fa-id-card"></i>
            <span>Recover Username</span>
        </a>
    </div>

    <!-- Option B: Forgot Password -->
    <div class="recovery-option-card">
        <div class="recovery-option-header">
            <div class="recovery-option-icon">
                <i class="fa-solid fa-key"></i>
            </div>
            <h3 class="recovery-option-title">Forgot Password</h3>
        </div>
        <p class="recovery-option-desc">Forgot your password? Verify your email and create a new password.</p>
        <a href="<?= url('/forgot-password') ?>" class="recovery-option-btn" data-no-ajax="true" data-native="true" rel="external">
            <i class="fa-solid fa-lock-rotate"></i>
            <span>Reset Password</span>
        </a>
    </div>
</div>

<!-- Back to Login Navigation -->
<div class="recovery-back-row">
    <a href="<?= url('/login') ?>" class="recovery-back-link" data-no-ajax="true" data-native="true" rel="external">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Back to Login</span>
    </a>
</div>

