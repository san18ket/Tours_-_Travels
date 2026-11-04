<?php
/**
 * Tours & Travels — Professional Account Recovery 4-Digit Email OTP Modal
 * Matches visual reference: Compact centered white modal, rounded corners, top blue email icon,
 * exactly 4 separate numeric digit boxes with blue focus outline, resend link with 60s cooldown,
 * and full-width blue "Verify email" button.
 *
 * @var string $purpose 'recovery_username'|'recovery_password'
 */
$purpose = $purpose ?? 'recovery_password';
$sendEndpoint = ($purpose === 'recovery_username') ? url('/forgot-username/send-otp') : url('/forgot-password/send-otp');
$verifyEndpoint = ($purpose === 'recovery_username') ? url('/forgot-username/verify-otp') : url('/forgot-password/verify-otp');
?>

<!-- ========================================================================= -->
<!-- 4-DIGIT OTP MODAL STYLES (Matches Visual Reference Image)                 -->
<!-- ========================================================================= -->
<style>
/* Modal Backdrop Overlay */
.vg-otp-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.52);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
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

/* Compact Centered White Card with Rounded Corners */
.vg-otp-card {
    background: #FFFFFF;
    border-radius: 20px;
    max-width: 420px;
    width: 100%;
    padding: 34px 28px 28px;
    box-shadow: 0 24px 50px -12px rgba(15, 23, 42, 0.28), 0 0 0 1px rgba(0, 0, 0, 0.05);
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

/* Modal Close Icon (Top-Right) */
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
    box-shadow: 0 3px 10px rgba(37, 99, 235, 0.14);
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

/* In-Modal Alert Boxes */
.vg-otp-alert {
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 13px;
    font-weight: 500;
    line-height: 1.4;
    margin: 10px 0 6px 0;
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

/* Exactly FOUR Separate OTP Input Boxes */
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
    box-shadow: 0 0 0 3.5px rgba(37, 99, 235, 0.18);
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
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.24);
    outline: none;
}

.vg-otp-verify-submit:hover:not(:disabled) {
    background: #1D4ED8;
    box-shadow: 0 6px 18px rgba(37, 99, 235, 0.34);
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
    .vg-otp-card {
        padding: 26px 18px 22px;
        border-radius: 18px;
    }

    .vg-otp-icon-wrap {
        width: 46px;
        height: 46px;
        font-size: 20px;
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

<!-- ========================================================================= -->
<!-- MODAL OVERLAY & CARD                                                      -->
<!-- ========================================================================= -->
<div id="vgRecoveryOtpModalBackdrop" class="vg-otp-backdrop" role="dialog" aria-modal="true" aria-labelledby="vgRecoveryOtpModalTitle">
    <div class="vg-otp-card" id="vgRecoveryOtpModalCard" onclick="event.stopPropagation()">
        <!-- Close Button (Top-Right 'x') -->
        <button type="button" class="vg-otp-close-btn" onclick="vgRecoveryOtp.closeModal()" aria-label="Close modal">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <!-- Small Blue Email Icon at Top -->
        <div class="vg-otp-icon-wrap">
            <i class="fa-solid fa-envelope"></i>
        </div>

        <!-- Heading & Supporting Text -->
        <h3 class="vg-otp-title" id="vgRecoveryOtpModalTitle">Check your email</h3>
        <p class="vg-otp-subtitle">Enter the verification code sent to</p>
        <div class="vg-otp-email-display" id="vgRecoveryOtpTargetEmailDisplay">recipient@example.com</div>

        <!-- In-Modal Alert Banners -->
        <div id="vgRecoveryOtpModalError" class="vg-otp-alert vg-otp-alert-error" role="alert">
            <i class="fa-solid fa-circle-exclamation" style="margin-right: 5px;"></i>
            <span id="vgRecoveryOtpModalErrorText"></span>
        </div>
        <div id="vgRecoveryOtpModalSuccess" class="vg-otp-alert vg-otp-alert-success" role="status">
            <i class="fa-solid fa-circle-check" style="margin-right: 5px;"></i>
            <span id="vgRecoveryOtpModalSuccessText"></span>
        </div>

        <!-- Exactly FOUR Separate OTP Digit Boxes -->
        <div class="vg-otp-inputs-row" id="vgRecoveryOtpInputsRow">
            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="vg-otp-box" data-idx="0" autocomplete="one-time-code" aria-label="First digit">
            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="vg-otp-box" data-idx="1" autocomplete="off" aria-label="Second digit">
            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="vg-otp-box" data-idx="2" autocomplete="off" aria-label="Third digit">
            <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="vg-otp-box" data-idx="3" autocomplete="off" aria-label="Fourth digit">
        </div>

        <!-- Resend Link with 60-Second Cooldown -->
        <div class="vg-otp-resend-container">
            <span>Didn't get a code?</span>
            <button type="button" id="vgRecoveryOtpResendBtn" class="vg-otp-resend-btn" onclick="vgRecoveryOtp.resend()" disabled>
                <span id="vgRecoveryOtpResendBtnText">Resend in 60s</span>
            </button>
        </div>

        <!-- Full-Width Blue Verify Email Button -->
        <button type="button" id="vgRecoveryOtpVerifySubmitBtn" class="vg-otp-verify-submit" onclick="vgRecoveryOtp.verify()" disabled>
            <span id="vgRecoveryOtpVerifyBtnLabel">Verify email</span>
        </button>
    </div>
</div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT CONTROLLER FOR RECOVERY OTP FLOW                               -->
<!-- ========================================================================= -->
<script>
window.vgRecoveryOtp = (function() {
    'use strict';

    const PURPOSE = '<?= e($purpose) ?>';
    const SEND_URL = '<?= e($sendEndpoint) ?>';
    const VERIFY_URL = '<?= e($verifyEndpoint) ?>';

    let currentPendingEmail = '';
    let cooldownTimer = null;
    let cooldownRemaining = 0;
    let isSubmitting = false;

    // Cache elements
    function getBackdrop() {
        return document.getElementById('vgRecoveryOtpModalBackdrop');
    }
    function getDigitBoxes() {
        return Array.from(document.querySelectorAll('#vgRecoveryOtpInputsRow .vg-otp-box'));
    }
    function getVerifyBtn() {
        return document.getElementById('vgRecoveryOtpVerifySubmitBtn');
    }
    function getResendBtn() {
        return document.getElementById('vgRecoveryOtpResendBtn');
    }

    // CSRF token retrieval helper
    function getCsrf() {
        if (typeof window.getCsrfToken === 'function') {
            return window.getCsrfToken();
        }
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.getAttribute('content');
        const inp = document.querySelector('input[name="_csrf_token"], input[name="_token"]');
        return inp ? inp.value : '';
    }

    // Modal Alert helpers (no browser alerts)
    function setModalError(msg) {
        const errEl = document.getElementById('vgRecoveryOtpModalError');
        const txtEl = document.getElementById('vgRecoveryOtpModalErrorText');
        const succEl = document.getElementById('vgRecoveryOtpModalSuccess');
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
        const succEl = document.getElementById('vgRecoveryOtpModalSuccess');
        const txtEl = document.getElementById('vgRecoveryOtpModalSuccessText');
        const errEl = document.getElementById('vgRecoveryOtpModalError');
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
        const resendText = document.getElementById('vgRecoveryOtpResendBtnText');
        if (!resendBtn || !resendText) return;

        if (cooldownRemaining > 0) {
            resendBtn.disabled = true;
            resendText.textContent = 'Resend in ' + cooldownRemaining + 's';
        } else {
            resendBtn.disabled = false;
            resendText.textContent = 'Resend';
        }
    }

    function openModal(email, initialCooldown = 60) {
        currentPendingEmail = email;
        const displayEl = document.getElementById('vgRecoveryOtpTargetEmailDisplay');
        if (displayEl) displayEl.textContent = email;

        setModalError('');
        setModalSuccess('');
        clearDigitBoxes();
        startCooldown(initialCooldown);

        const backdrop = getBackdrop();
        if (backdrop) {
            // Relocate modal backdrop directly under body to break out of .glass-login-card's backdrop-filter stacking context
            if (backdrop.parentNode !== document.body) {
                document.body.appendChild(backdrop);
            }
            backdrop.classList.add('is-open');
        }

        setTimeout(function() {
            const boxes = getDigitBoxes();
            if (boxes.length > 0) {
                boxes[0].focus();
            }
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

        const isComplete = (code.length === 4 && /^\d{4}$/.test(code));
        if (verifyBtn && !isSubmitting) {
            verifyBtn.disabled = !isComplete;
        }

        return isComplete;
    }

    // Resend OTP inside modal
    function resend() {
        if (cooldownRemaining > 0 || isSubmitting) return;
        if (!currentPendingEmail) return;

        const resendBtn = getResendBtn();
        const resendText = document.getElementById('vgRecoveryOtpResendBtnText');
        if (resendBtn) resendBtn.disabled = true;
        if (resendText) resendText.textContent = 'Sending OTP...';

        setModalError('');
        setModalSuccess('');

        const formData = new FormData();
        formData.append('email', currentPendingEmail);
        formData.append('_csrf_token', getCsrf());

        fetch(SEND_URL, {
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

    // Verify OTP inside modal
    function verify() {
        if (isSubmitting) return;

        const boxes = getDigitBoxes();
        const otpCode = boxes.map(b => b.value).join('');

        if (otpCode.length !== 4 || !/^\d{4}$/.test(otpCode)) {
            setModalError('Please enter all 4 digits of your verification code.');
            return;
        }

        isSubmitting = true;
        const verifyBtn = getVerifyBtn();
        const verifyLabel = document.getElementById('vgRecoveryOtpVerifyBtnLabel');
        if (verifyBtn) verifyBtn.disabled = true;
        if (verifyLabel) verifyLabel.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Verifying...';

        setModalError('');
        setModalSuccess('');

        const formData = new FormData();
        formData.append('email', currentPendingEmail);
        formData.append('otp', otpCode);
        formData.append('_csrf_token', getCsrf());

        fetch(VERIFY_URL, {
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
            isSubmitting = false;
            if (verifyLabel) verifyLabel.textContent = 'Verify email';

            if (res.ok && res.data.success && res.data.token) {
                // Modal verification succeeded!
                closeModal();
                if (typeof window.onRecoveryOtpVerified === 'function') {
                    window.onRecoveryOtpVerified(res.data.token, currentPendingEmail);
                }
            } else {
                boxes.forEach(b => b.classList.add('has-error'));
                const errMsg = (res.data && res.data.message) ? res.data.message : 'Verification failed. Please check the code.';
                setModalError(errMsg);
                if (verifyBtn) verifyBtn.disabled = false;
                setTimeout(function() {
                    if (boxes.length > 0) {
                        boxes[0].focus();
                        boxes[0].select();
                    }
                }, 100);
            }
        })
        .catch(function(err) {
            isSubmitting = false;
            console.error('Verify OTP Error:', err);
            if (verifyLabel) verifyLabel.textContent = 'Verify email';
            if (verifyBtn) verifyBtn.disabled = false;
            setModalError('Connection error during verification. Please try again.');
        });
    }

    // Initialize 4-digit input behaviors
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

                // Auto-verify on 4th digit entered
                if (checkDigitCompleteness()) {
                    setTimeout(function() {
                        verify();
                    }, 140);
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
                    }, 140);
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

    function init() {
        initDigitInputs();

        const backdrop = getBackdrop();
        if (backdrop && backdrop.parentNode !== document.body) {
            document.body.appendChild(backdrop);
        }

        if (backdrop) {
            backdrop.addEventListener('click', function(e) {
                if (e.target === backdrop) {
                    closeModal();
                }
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && backdrop && backdrop.classList.contains('is-open')) {
                closeModal();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    return {
        openModal: openModal,
        closeModal: closeModal,
        verify: verify,
        resend: resend
    };
})();
</script>

