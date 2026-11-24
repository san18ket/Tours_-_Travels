<?php
/**
 * Tours & Travels — Unified Email OTP Verification Component & Reference Modal
 * Matches visual reference: Compact centered modal, rounded corners, top blue email icon,
 * 4 digit input boxes with blue focus outline, resend link with 60s cooldown, full-width blue verify button.
 *
 * @var string $flow 'customer'|'employee'
 */
$flow = $flow ?? 'customer';
?>

<!-- ========================================================================= -->
<!-- EMAIL OTP VERIFICATION STYLES                                             -->
<!-- ========================================================================= -->
<style>
/* Inline Email Field + Send OTP Button Row */
.vg-email-otp-group {
    position: relative;
    width: 100%;
}

.vg-email-otp-row {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
}

.vg-email-otp-row .modern-input-wrapper {
    flex: 1 1 auto;
    min-width: 0;
    margin-bottom: 0 !important;
}

.vg-btn-send-otp {
    flex: 0 0 auto;
    white-space: nowrap;
    height: 44px;
    padding: 0 18px;
    border-radius: 9999px;
    background: linear-gradient(135deg, #176B5B 0%, #125748 100%);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #FFFFFF;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 4px 14px rgba(23, 107, 91, 0.25);
    user-select: none;
}

.vg-btn-send-otp:hover:not(:disabled):not(.is-verified) {
    background: linear-gradient(135deg, #1A7E6C 0%, #146151 100%);
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(23, 107, 91, 0.38);
}

.vg-btn-send-otp:active:not(:disabled):not(.is-verified) {
    transform: translateY(0);
}

.vg-btn-send-otp.is-verified {
    background: linear-gradient(135deg, #10B981 0%, #059669 100%) !important;
    border: 1px solid rgba(255, 255, 255, 0.25) !important;
    color: #FFFFFF !important;
    cursor: default;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3) !important;
    pointer-events: none;
}

.vg-btn-send-otp:disabled {
    opacity: 0.65;
    cursor: not-allowed;
    transform: none !important;
    box-shadow: none !important;
}

/* Status Row & Pill Badges */
.vg-email-status-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 6px;
    min-height: 22px;
}

.vg-email-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5.5px;
    padding: 3px 10px;
    border-radius: 9999px;
    font-size: 11.5px;
    font-weight: 600;
    line-height: 1.3;
    transition: all 0.2s ease;
}

.vg-status-not-verified {
    background: #F1F5F9;
    color: #64748B;
    border: 1px solid #E2E8F0;
}

.vg-status-otp-sent {
    background: #E8F4F0;
    color: #176B5B;
    border: 1px solid #B2D8CF;
}

.vg-status-verified {
    background: #ECFDF5;
    color: #047857;
    border: 1px solid #A7F3D0;
}

.vg-email-inline-msg {
    font-size: 12px;
    font-weight: 500;
    color: #DC2626;
    margin-top: 4px;
    display: none;
}

/* ========================================================================= */
/* OTP REFERENCE MODAL STYLING (Matches Uploaded Reference Image)            */
/* ========================================================================= */
.vg-otp-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.5);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    z-index: 10050;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
    box-sizing: border-box;
}

.vg-otp-backdrop.is-open {
    display: flex !important;
}

.vg-otp-card {
    background: #FFFFFF;
    border-radius: 20px;
    max-width: 420px;
    width: 100%;
    padding: 32px 28px 28px;
    box-shadow: 0 20px 45px -12px rgba(15, 23, 42, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.04);
    position: relative;
    text-align: center;
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    box-sizing: border-box;
    animation: vgOtpSlideIn 0.24s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes vgOtpSlideIn {
    from {
        opacity: 0;
        transform: translateY(18px) scale(0.96);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Modal Close Icon */
.vg-otp-close-btn {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #F1F5F9;
    border: none;
    color: #64748B;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 15px;
    transition: all 0.15s ease;
    line-height: 1;
}

.vg-otp-close-btn:hover {
    background: #E2E8F0;
    color: #0F172A;
}

/* Small Blue Email Icon at Top */
.vg-otp-icon-wrap {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: #EFF6FF;
    color: #2563EB;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    margin-bottom: 18px;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.12);
}

/* Heading & Supporting Text */
.vg-otp-title {
    font-size: 22px;
    font-weight: 700;
    color: #0F172A;
    margin: 0 0 8px 0;
    letter-spacing: -0.3px;
}

.vg-otp-subtitle {
    font-size: 14px;
    color: #64748B;
    margin: 0 0 4px 0;
    line-height: 1.45;
}

.vg-otp-email-display {
    font-size: 14.5px;
    font-weight: 600;
    color: #0F172A;
    word-break: break-all;
    display: inline-block;
    margin-bottom: 8px;
}

/* Error & Success Alert Boxes inside Modal */
.vg-otp-alert {
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 13px;
    font-weight: 500;
    line-height: 1.4;
    margin: 12px 0 6px 0;
    display: none;
    text-align: left;
}

.vg-otp-alert-error {
    background: #FEF2F2;
    border: 1px solid #FEE2E2;
    color: #B91C1C;
}

.vg-otp-alert-success {
    background: #ECFDF5;
    border: 1px solid #D1FAE5;
    color: #047857;
}

/* Four OTP Digit Boxes */
.vg-otp-inputs-row {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 12px;
    margin: 22px 0 18px;
}

.vg-otp-box {
    width: 56px;
    height: 60px;
    font-size: 26px;
    font-weight: 700;
    text-align: center;
    border: 1.5px solid #E2E8F0;
    border-radius: 12px;
    background: #F8FAFC;
    color: #0F172A;
    outline: none;
    transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
    caret-color: #2563EB;
    font-family: inherit;
    box-sizing: border-box;
}

.vg-otp-box:focus {
    border-color: #2563EB;
    background: #FFFFFF;
    box-shadow: 0 0 0 3.5px rgba(37, 99, 235, 0.15);
}

.vg-otp-box.is-filled {
    background: #FFFFFF;
    border-color: #94A3B8;
}

.vg-otp-box.has-error {
    border-color: #EF4444;
    background: #FFF5F5;
}

/* Resend Row */
.vg-otp-resend-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    font-size: 13.5px;
    color: #64748B;
    margin: 4px 0 22px;
}

.vg-otp-resend-btn {
    background: none;
    border: none;
    padding: 0;
    font-size: 13.5px;
    font-weight: 600;
    color: #2563EB;
    cursor: pointer;
    outline: none;
    transition: color 0.15s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.vg-otp-resend-btn:hover:not(:disabled) {
    color: #1D4ED8;
    text-decoration: underline;
}

.vg-otp-resend-btn:disabled {
    color: #94A3B8;
    cursor: not-allowed;
    text-decoration: none !important;
}

/* Full-Width Blue Verify Button */
.vg-otp-verify-submit {
    width: 100%;
    height: 48px;
    background: #2563EB;
    color: #FFFFFF;
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.22);
    outline: none;
}

.vg-otp-verify-submit:hover:not(:disabled) {
    background: #1D4ED8;
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.3);
    transform: translateY(-1px);
}

.vg-otp-verify-submit:active:not(:disabled) {
    transform: translateY(0);
}

.vg-otp-verify-submit:disabled {
    opacity: 0.55;
    cursor: not-allowed;
    box-shadow: none;
    transform: none !important;
}

/* Responsive Mobile Adjustments */
@media (max-width: 480px) {
    .vg-email-otp-row {
        gap: 6px;
    }

    .vg-btn-send-otp {
        padding: 0 12px;
        font-size: 12.5px;
        height: 42px;
    }

    .vg-otp-card {
        padding: 24px 18px 22px;
        border-radius: 18px;
    }

    .vg-otp-icon-wrap {
        width: 46px;
        height: 46px;
        font-size: 19px;
        margin-bottom: 14px;
    }

    .vg-otp-title {
        font-size: 20px;
    }

    .vg-otp-subtitle, .vg-otp-email-display {
        font-size: 13px;
    }

    .vg-otp-inputs-row {
        gap: 8px;
        margin: 18px 0 16px;
    }

    .vg-otp-box {
        width: 48px;
        height: 52px;
        font-size: 22px;
        border-radius: 10px;
    }

    .vg-otp-verify-submit {
        height: 44px;
        font-size: 14px;
    }
}

@media (max-width: 360px) {
    .vg-otp-box {
        width: 42px;
        height: 48px;
        font-size: 20px;
    }
    .vg-otp-inputs-row {
        gap: 6px;
    }
}
</style>

<!-- Hidden Verification Token Input -->
<input type="hidden" name="email_verification_token" id="emailVerificationToken" value="">

<!-- ========================================================================= -->
<!-- MODAL OVERLAY & CARD (Matches Reference Image)                            -->
<!-- ========================================================================= -->
<div id="vgOtpModalBackdrop" class="vg-otp-backdrop" role="dialog" aria-modal="true" aria-labelledby="vgOtpModalTitle">
    <div class="vg-otp-card" id="vgOtpModalCard" onclick="event.stopPropagation()">
        <!-- Close Button (Top-Right 'x') -->
        <button type="button" class="vg-otp-close-btn" onclick="vgEmailOtp.closeModal()" aria-label="Close modal">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <!-- Small Blue Email Icon at Top -->
        <div class="vg-otp-icon-wrap">
            <i class="fa-solid fa-envelope"></i>
        </div>

        <!-- Heading & Supporting Text -->
        <h3 class="vg-otp-title" id="vgOtpModalTitle">Check your email</h3>
        <p class="vg-otp-subtitle">Enter the verification code sent to</p>
        <div class="vg-otp-email-display" id="vgOtpTargetEmailDisplay">recipient@example.com</div>

        <!-- In-Modal Alert Banners -->
        <div id="vgOtpModalError" class="vg-otp-alert vg-otp-alert-error" role="alert">
            <i class="fa-solid fa-circle-exclamation" style="margin-right: 5px;"></i>
            <span id="vgOtpModalErrorText"></span>
        </div>
        <div id="vgOtpModalSuccess" class="vg-otp-alert vg-otp-alert-success" role="status">
            <i class="fa-solid fa-circle-check" style="margin-right: 5px;"></i>
            <span id="vgOtpModalSuccessText"></span>
        </div>

        <!-- FOUR Separate OTP Digit Boxes -->
        <div class="vg-otp-inputs-row" id="vgOtpInputsRow">
            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="vg-otp-box" data-idx="0" autocomplete="one-time-code" aria-label="First digit">
            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="vg-otp-box" data-idx="1" autocomplete="off" aria-label="Second digit">
            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="vg-otp-box" data-idx="2" autocomplete="off" aria-label="Third digit">
            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="vg-otp-box" data-idx="3" autocomplete="off" aria-label="Fourth digit">
        </div>

        <!-- Resend Link with 60-Second Cooldown -->
        <div class="vg-otp-resend-container">
            <span>Didn't get a code?</span>
            <button type="button" id="vgOtpResendBtn" class="vg-otp-resend-btn" onclick="vgEmailOtp.resend()" disabled>
                <span id="vgOtpResendBtnText">Resend in 60s</span>
            </button>
        </div>

        <!-- Full-Width Blue Verify Email Button -->
        <button type="button" id="vgOtpVerifySubmitBtn" class="vg-otp-verify-submit" onclick="vgEmailOtp.verify()" disabled>
            <span id="vgOtpVerifyBtnLabel">Verify email</span>
        </button>
    </div>
</div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT CONTROLLER FOR OTP FLOW                                        -->
<!-- ========================================================================= -->
<script>
window.vgEmailOtp = (function() {
    'use strict';

    const FLOW = '<?= e($flow) ?>';
    let isVerified = false;
    let verifiedEmail = '';
    let currentPendingEmail = '';
    let cooldownTimer = null;
    let cooldownRemaining = 0;

    // Cache elements
    function getEmailInput() {
        return document.getElementById('email');
    }
    function getSendBtn() {
        return document.getElementById('btnSendOtp');
    }
    function getStatusBadge() {
        return document.getElementById('emailStatusBadge');
    }
    function getInlineError() {
        return document.getElementById('emailOtpInlineError');
    }
    function getBackdrop() {
        return document.getElementById('vgOtpModalBackdrop');
    }
    function getDigitBoxes() {
        return Array.from(document.querySelectorAll('.vg-otp-box'));
    }
    function getVerifyBtn() {
        return document.getElementById('vgOtpVerifySubmitBtn');
    }
    function getResendBtn() {
        return document.getElementById('vgOtpResendBtn');
    }

    // Helper: CSRF retrieval
    function getCsrf() {
        if (typeof window.getCsrfToken === 'function') {
            return window.getCsrfToken();
        }
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.getAttribute('content');
        const inp = document.querySelector('input[name="_csrf_token"], input[name="_token"]');
        return inp ? inp.value : '';
    }

    // Toast helper without alert boxes
    function notify(msg, type = 'info') {
        if (typeof window.vgNotify === 'function') {
            window.vgNotify(msg, type);
        } else if (typeof window.vgToast === 'function') {
            window.vgToast(msg, type);
        } else {
            console.log('[' + type.toUpperCase() + '] ' + msg);
        }
    }

    // Modal Alert helpers
    function setModalError(msg) {
        const errEl = document.getElementById('vgOtpModalError');
        const txtEl = document.getElementById('vgOtpModalErrorText');
        const succEl = document.getElementById('vgOtpModalSuccess');
        if (succEl) succEl.style.display = 'none';

        if (errEl && txtEl) {
            if (msg) {
                txtEl.textContent = msg;
                errEl.style.display = 'block';
            } else {
                errEl.style.display = 'none';
            }
        }
    }

    function setModalSuccess(msg) {
        const succEl = document.getElementById('vgOtpModalSuccess');
        const txtEl = document.getElementById('vgOtpModalSuccessText');
        const errEl = document.getElementById('vgOtpModalError');
        if (errEl) errEl.style.display = 'none';

        if (succEl && txtEl) {
            if (msg) {
                txtEl.textContent = msg;
                succEl.style.display = 'block';
            } else {
                succEl.style.display = 'none';
            }
        }
    }

    function setInlineError(msg) {
        const errEl = getInlineError();
        if (errEl) {
            if (msg) {
                errEl.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + escapeHtml(msg);
                errEl.style.display = 'block';
            } else {
                errEl.style.display = 'none';
                errEl.innerHTML = '';
            }
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Update Status Pill UI
    function setStatus(state, customLabel = null) {
        const badge = getStatusBadge();
        const sendBtn = getSendBtn();
        if (!badge) return;

        badge.className = 'vg-email-status-pill';

        if (state === 'verified') {
            badge.classList.add('vg-status-verified');
            badge.innerHTML = '<i class="fa-solid fa-shield-check"></i> <span>' + (customLabel || 'Email verified') + '</span>';
            if (sendBtn) {
                sendBtn.classList.add('is-verified');
                sendBtn.innerHTML = '<i class="fa-solid fa-check"></i> <span>Verified</span>';
                sendBtn.disabled = true;
            }
        } else if (state === 'sent') {
            badge.classList.add('vg-status-otp-sent');
            badge.innerHTML = '<i class="fa-solid fa-clock"></i> <span>' + (customLabel || 'OTP sent (expires in 5m)') + '</span>';
            if (sendBtn) {
                sendBtn.classList.remove('is-verified');
                sendBtn.innerHTML = '<i class="fa-solid fa-key"></i> <span>Enter OTP</span>';
                sendBtn.disabled = false;
            }
        } else {
            badge.classList.add('vg-status-not-verified');
            badge.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> <span>' + (customLabel || 'Not verified') + '</span>';
            if (sendBtn) {
                sendBtn.classList.remove('is-verified');
                sendBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> <span>Send OTP</span>';
                sendBtn.disabled = false;
            }
        }
    }

    // Cooldown Timer
    function startCooldown(seconds) {
        clearInterval(cooldownTimer);
        cooldownRemaining = parseInt(seconds, 10) || 60;
        updateCooldownUI();

        cooldownTimer = setInterval(function() {
            cooldownRemaining--;
            if (cooldownRemaining <= 0) {
                clearInterval(cooldownTimer);
                cooldownRemaining = 0;
            }
            updateCooldownUI();
        }, 1000);
    }

    function updateCooldownUI() {
        const resendBtn = getResendBtn();
        const resendText = document.getElementById('vgOtpResendBtnText');
        if (!resendBtn || !resendText) return;

        if (cooldownRemaining > 0) {
            resendBtn.disabled = true;
            resendText.textContent = 'Resend in ' + cooldownRemaining + 's';
        } else {
            resendBtn.disabled = false;
            resendText.textContent = 'Resend';
        }
    }

    // Open & Close Modal
    function openModal(email) {
        currentPendingEmail = email;
        const displayEl = document.getElementById('vgOtpTargetEmailDisplay');
        if (displayEl) displayEl.textContent = email;

        setModalError('');
        setModalSuccess('');
        clearDigitBoxes();

        const backdrop = getBackdrop();
        if (backdrop) backdrop.classList.add('is-open');

        // Focus first box with slight delay for animation
        setTimeout(function() {
            const boxes = getDigitBoxes();
            if (boxes.length > 0) boxes[0].focus();
        }, 120);
    }

    function closeModal() {
        const backdrop = getBackdrop();
        if (backdrop) backdrop.classList.remove('is-open');
    }

    function clearDigitBoxes() {
        const boxes = getDigitBoxes();
        boxes.forEach(function(box) {
            box.value = '';
            box.classList.remove('is-filled', 'has-error');
        });
        checkDigitCompleteness();
    }

    function checkDigitCompleteness() {
        const boxes = getDigitBoxes();
        const code = boxes.map(b => b.value).join('');
        const verifyBtn = getVerifyBtn();

        boxes.forEach(b => {
            if (b.value) b.classList.add('is-filled');
            else b.classList.remove('is-filled');
        });

        if (verifyBtn) {
            verifyBtn.disabled = (code.length !== 4 || !/^\d{4}$/.test(code));
        }

        return code.length === 4 && /^\d{4}$/.test(code);
    }

    // Send OTP handler
    function sendOtp() {
        const emailInput = getEmailInput();
        if (!emailInput) return;

        const email = emailInput.value.trim().toLowerCase();
        setInlineError('');

        // Client-side email validation
        if (!email) {
            setInlineError('Email address is required to send verification code.');
            notify('Please enter an email address first.', 'error');
            emailInput.focus();
            return;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            setInlineError('Please enter a valid email address format.');
            notify('Invalid email address format.', 'error');
            emailInput.focus();
            return;
        }

        // If already verified for this exact email, reopen or inform
        if (isVerified && verifiedEmail === email) {
            notify('This email address is already verified.', 'success');
            return;
        }

        // If OTP was already sent and is active, open modal directly
        if (currentPendingEmail === email && cooldownRemaining > 0) {
            openModal(email);
            return;
        }

        const sendBtn = getSendBtn();
        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Sending OTP...</span>';
        }

        // Gather recipient name if available on page
        let recipientName = '';
        const nameField = document.getElementById('name') || document.getElementById('first_name');
        if (nameField) recipientName = nameField.value.trim();

        const formData = new FormData();
        formData.append('email', email);
        formData.append('flow', FLOW);
        formData.append('name', recipientName);
        formData.append('_csrf_token', getCsrf());

        fetch('<?= url('/office/email-otp/send') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrf()
            }
        })
        .then(function(res) {
            return res.json().then(data => ({ status: res.status, ok: res.ok, data: data }));
        })
        .then(function(res) {
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> <span>Send OTP</span>';
            }

            if (res.ok && res.data.success) {
                setStatus('sent');
                notify(res.data.message || 'Verification code sent!', 'success');
                startCooldown(res.data.cooldown || 60);
                openModal(email);
            } else {
                const errMsg = (res.data && res.data.message) ? res.data.message : 'Failed to send verification code.';
                setInlineError(errMsg);
                notify(errMsg, 'error');
                if (res.data && res.data.cooldown > 0) {
                    startCooldown(res.data.cooldown);
                }
            }
        })
        .catch(function(err) {
            console.error('Send OTP Error:', err);
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> <span>Send OTP</span>';
            }
            const netErr = 'Connection error. Please check your network and try again.';
            setInlineError(netErr);
            notify(netErr, 'error');
        });
    }

    // Resend OTP handler inside modal
    function resend() {
        if (cooldownRemaining > 0) return;
        if (!currentPendingEmail) return;

        const resendBtn = getResendBtn();
        const resendText = document.getElementById('vgOtpResendBtnText');
        if (resendBtn) resendBtn.disabled = true;
        if (resendText) resendText.textContent = 'Sending OTP...';

        setModalError('');
        setModalSuccess('');

        let recipientName = '';
        const nameField = document.getElementById('name') || document.getElementById('first_name');
        if (nameField) recipientName = nameField.value.trim();

        const formData = new FormData();
        formData.append('email', currentPendingEmail);
        formData.append('flow', FLOW);
        formData.append('name', recipientName);
        formData.append('_csrf_token', getCsrf());

        fetch('<?= url('/office/email-otp/resend') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrf()
            }
        })
        .then(function(res) {
            return res.json().then(data => ({ status: res.status, ok: res.ok, data: data }));
        })
        .then(function(res) {
            if (res.ok && res.data.success) {
                clearDigitBoxes();
                setModalSuccess('A fresh 4-digit verification code has been dispatched.');
                notify('New verification code sent to ' + currentPendingEmail, 'success');
                startCooldown(res.data.cooldown || 60);
                const boxes = getDigitBoxes();
                if (boxes.length > 0) boxes[0].focus();
            } else {
                const errMsg = (res.data && res.data.message) ? res.data.message : 'Unable to resend verification code.';
                setModalError(errMsg);
                if (res.data && res.data.cooldown > 0) {
                    startCooldown(res.data.cooldown);
                } else {
                    if (resendBtn) resendBtn.disabled = false;
                    if (resendText) resendText.textContent = 'Resend';
                }
            }
        })
        .catch(function(err) {
            console.error('Resend OTP Error:', err);
            setModalError('Network error while resending code. Please retry.');
            if (resendBtn) resendBtn.disabled = false;
            if (resendText) resendText.textContent = 'Resend';
        });
    }

    // Verify OTP handler inside modal
    function verify() {
        const boxes = getDigitBoxes();
        const otpCode = boxes.map(b => b.value).join('');

        if (otpCode.length !== 4 || !/^\d{4}$/.test(otpCode)) {
            setModalError('Please enter all 4 digits of your verification code.');
            return;
        }

        const verifyBtn = getVerifyBtn();
        const verifyLabel = document.getElementById('vgOtpVerifyBtnLabel');
        if (verifyBtn) verifyBtn.disabled = true;
        if (verifyLabel) verifyLabel.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Verifying...';

        setModalError('');
        setModalSuccess('');

        const formData = new FormData();
        formData.append('email', currentPendingEmail);
        formData.append('flow', FLOW);
        formData.append('otp', otpCode);
        formData.append('_csrf_token', getCsrf());

        fetch('<?= url('/office/email-otp/verify') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrf()
            }
        })
        .then(function(res) {
            return res.json().then(data => ({ status: res.status, ok: res.ok, data: data }));
        })
        .then(function(res) {
            if (verifyLabel) verifyLabel.textContent = 'Verify email';

            if (res.ok && res.data.success) {
                // Success: update state
                isVerified = true;
                verifiedEmail = currentPendingEmail;

                // Save token in hidden input
                const tokenInput = document.getElementById('emailVerificationToken');
                if (tokenInput && res.data.token) {
                    tokenInput.value = res.data.token;
                }

                // Close modal and update UI
                closeModal();
                setStatus('verified', 'Email verified');
                setInlineError('');
                notify('Email verified successfully! You may now proceed.', 'success');

                // Enable submit buttons if disabled
                const custSubmit = document.getElementById('btnSubmitCustomer');
                if (custSubmit) custSubmit.disabled = false;
                const empSubmit = document.getElementById('btnSubmitEmployee');
                if (empSubmit) empSubmit.disabled = false;
            } else {
                boxes.forEach(b => b.classList.add('has-error'));
                const errMsg = (res.data && res.data.message) ? res.data.message : 'Verification failed. Please check the code.';
                setModalError(errMsg);
                if (verifyBtn) verifyBtn.disabled = false;
                // Re-focus first empty or first digit
                setTimeout(function() {
                    if (boxes.length > 0) {
                        boxes[0].focus();
                        boxes[0].select();
                    }
                }, 100);
            }
        })
        .catch(function(err) {
            console.error('Verify OTP Error:', err);
            if (verifyLabel) verifyLabel.textContent = 'Verify email';
            if (verifyBtn) verifyBtn.disabled = false;
            setModalError('Connection error during verification. Please try again.');
        });
    }

    // Invalidate verification on email change
    function handleEmailInputChanged() {
        const emailInput = getEmailInput();
        if (!emailInput) return;

        const currentVal = emailInput.value.trim().toLowerCase();

        // If changed away from verified email, reset verification
        if (isVerified && currentVal !== verifiedEmail) {
            isVerified = false;
            verifiedEmail = '';
            const tokenInput = document.getElementById('emailVerificationToken');
            if (tokenInput) tokenInput.value = '';

            setStatus('unverified');
            setInlineError('Email changed. Please verify your new email address.');

            // Invalidate on server asynchronously
            const formData = new FormData();
            formData.append('email', verifiedEmail || currentVal);
            formData.append('flow', FLOW);
            formData.append('_csrf_token', getCsrf());

            fetch('<?= url('/office/email-otp/invalidate') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrf()
                }
            }).catch(function() {});
        } else if (!isVerified) {
            if (currentVal !== currentPendingEmail) {
                setStatus('unverified');
                setInlineError('');
            }
        }
    }

    // Digits input event binding
    function initDigitInputs() {
        const boxes = getDigitBoxes();

        boxes.forEach(function(box, idx) {
            // Numeric only constraint
            box.addEventListener('input', function(e) {
                box.classList.remove('has-error');
                const clean = box.value.replace(/\D/g, '');
                box.value = clean ? clean.charAt(0) : '';

                if (box.value && idx < 3) {
                    boxes[idx + 1].focus();
                }

                checkDigitCompleteness();

                // Auto-verify when 4th digit entered
                if (checkDigitCompleteness()) {
                    setTimeout(function() {
                        verify();
                    }, 150);
                }
            });

            // Backspace navigation & Arrow keys
            box.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace') {
                    box.classList.remove('has-error');
                    if (!box.value && idx > 0) {
                        e.preventDefault();
                        boxes[idx - 1].value = '';
                        boxes[idx - 1].focus();
                    } else if (box.value) {
                        box.value = '';
                    }
                    checkDigitCompleteness();
                } else if (e.key === 'ArrowLeft' && idx > 0) {
                    e.preventDefault();
                    boxes[idx - 1].focus();
                } else if (e.key === 'ArrowRight' && idx < 3) {
                    e.preventDefault();
                    boxes[idx + 1].focus();
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (checkDigitCompleteness()) {
                        verify();
                    }
                }
            });

            // Paste 4 digits support
            box.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text');
                const digits = pasted.replace(/\D/g, '').slice(0, 4);

                if (!digits) return;

                for (let i = 0; i < 4; i++) {
                    if (i < digits.length) {
                        boxes[i].value = digits.charAt(i);
                        boxes[i].classList.add('is-filled');
                    } else {
                        boxes[i].value = '';
                        boxes[i].classList.remove('is-filled');
                    }
                }

                if (digits.length >= 4) {
                    boxes[3].focus();
                    checkDigitCompleteness();
                    setTimeout(function() {
                        verify();
                    }, 150);
                } else {
                    boxes[digits.length].focus();
                    checkDigitCompleteness();
                }
            });

            box.addEventListener('focus', function() {
                box.select();
            });
        });
    }

    // Initialize component
    function init() {
        initDigitInputs();

        const emailInput = getEmailInput();
        if (emailInput) {
            emailInput.addEventListener('input', handleEmailInputChanged);
            emailInput.addEventListener('change', handleEmailInputChanged);

            // Also support pressing Enter in email input to send OTP
            emailInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !isVerified) {
                    e.preventDefault();
                    sendOtp();
                }
            });
        }

        // Close on backdrop click
        const backdrop = getBackdrop();
        if (backdrop) {
            backdrop.addEventListener('click', function(e) {
                if (e.target === backdrop) {
                    closeModal();
                }
            });
        }

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && backdrop && backdrop.classList.contains('is-open')) {
                closeModal();
            }
        });

        // Set initial status
        setStatus('unverified');
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    return {
        send: sendOtp,
        resend: resend,
        verify: verify,
        openModal: openModal,
        closeModal: closeModal,
        isVerified: function() { return isVerified; },
        getVerifiedEmail: function() { return verifiedEmail; },
        showError: function(msg) {
            setInlineError(msg);
            notify(msg, 'error');
        }
    };
})();
</script>

