<?php
/**
 * Email Settings - Office-Friendly Configuration View
 * Clear separation of Sender Information, Connection Settings, and Deliverability.
 */
?>
<div class="email-settings-page" style="max-width: 1350px; margin: 0 auto; padding: 1.25rem 0.5rem 2rem;">

    <!-- 1. Page Header -->
    <?= render_page_header([
        'title'       => 'Email Settings',
        'description' => 'Manage sender identity, email connection settings, and domain deliverability.',
        'badge'       => 'Configuration',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => url('/office/dashboard')],
            ['label' => 'Email Center', 'url' => url('/office/emails')],
            ['label' => 'Email Settings', 'url' => ''],
        ],
        'actions'     => '<div style="display:flex; gap:0.65rem; align-items:center; flex-wrap:wrap;">'
            . '<button type="button" onclick="checkEmailConnection(this)" class="btn btn-outline btn-sm">'
            . '<i class="fa-solid fa-rotate" style="margin-right:5px;"></i> <span id="btnCheckConnectionText">Check Connection</span></button>'
            . '<a href="' . url('/office/emails') . '" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-left" style="margin-right:5px;"></i> Back to Overview</a>'
            . '</div>',
    ]) ?>

    <!-- 2. Navigation Tabs -->
    <?php require __DIR__ . '/_tabs.php'; ?>

    <!-- 3. Flash Notifications -->
    <?php if ($msg = \App\Core\Session::getFlash('success')): ?>
        <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 0.9rem 1.25rem; margin-bottom: 1.5rem; color: #065F46; display: flex; align-items: center; gap: 0.75rem; font-weight: 600; font-size: 0.92rem;">
            <i class="fa-solid fa-circle-check" style="color: #10B981; font-size: 1.15rem;"></i>
            <span><?= e($msg) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($msg = \App\Core\Session::getFlash('danger')): ?>
        <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 0.9rem 1.25rem; margin-bottom: 1.5rem; color: #991B1B; display: flex; align-items: center; gap: 0.75rem; font-weight: 600; font-size: 0.92rem;">
            <i class="fa-solid fa-triangle-exclamation" style="color: #EF4444; font-size: 1.15rem;"></i>
            <span><?= e($msg) ?></span>
        </div>
    <?php endif; ?>

    <!-- Connection Status Inline Result Alert -->
    <div id="connectionStatusAlert" style="display: none; margin-bottom: 1.5rem; padding: 0.9rem 1.25rem; border-radius: 12px; font-size: 0.9rem; font-weight: 500;"></div>

    <!-- 4. Two-Column Settings Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">

        <!-- Section 1: Sender Information -->
        <div class="vg-card" style="margin-bottom: 0;">
            <div class="card-header" style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(23, 107, 91, 0.1); color: #176B5B; display: flex; align-items: center; justify-content: center; font-size: 0.95rem;">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <span class="card-title" style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #0F172A;">Sender Information</span>
                </div>
            </div>

            <div class="card-body">
                <p style="color: #64748B; font-size: 0.88rem; line-height: 1.5; margin: 0 0 1.25rem;">
                    These details appear in the &quot;From&quot; and &quot;Reply-To&quot; fields of emails received by your customers and staff.
                </p>

                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div style="background: #F8FAFC; padding: 0.85rem 1rem; border-radius: 10px; border: 1px solid var(--vg-pearl-200, #E2E8F0);">
                        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #64748B;">Sender Name (Display Name)</div>
                        <div style="font-size: 0.95rem; font-weight: 700; color: #0F172A; margin-top: 0.25rem;">
                            <?= e($smtpConfig['from_name'] ?: 'Not configured') ?>
                        </div>
                    </div>

                    <div style="background: #F8FAFC; padding: 0.85rem 1rem; border-radius: 10px; border: 1px solid var(--vg-pearl-200, #E2E8F0);">
                        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #64748B;">Sender Email Address</div>
                        <div style="font-size: 0.95rem; font-weight: 600; color: #176B5B; margin-top: 0.25rem; word-break: break-all;">
                            <?= e($smtpConfig['from_address'] ?: 'Not configured') ?>
                        </div>
                    </div>

                    <div style="background: #F8FAFC; padding: 0.85rem 1rem; border-radius: 10px; border: 1px solid var(--vg-pearl-200, #E2E8F0);">
                        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #64748B;">Reply-To Address</div>
                        <div style="font-size: 0.95rem; font-weight: 600; color: #334155; margin-top: 0.25rem; word-break: break-all;">
                            <?= e($smtpConfig['reply_to_address'] ?: $smtpConfig['from_address'] ?: 'Same as sender') ?>
                        </div>
                    </div>

                    <div style="background: #F8FAFC; padding: 0.85rem 1rem; border-radius: 10px; border: 1px solid var(--vg-pearl-200, #E2E8F0);">
                        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #64748B;">Admin Alert Copy Address</div>
                        <div style="font-size: 0.95rem; font-weight: 600; color: #334155; margin-top: 0.25rem; word-break: break-all;">
                            <?= e($smtpConfig['admin_address'] ?: 'None (optional)') ?>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 1.25rem; padding: 0.75rem 1rem; background: #F1F5F9; border-radius: 8px; font-size: 0.82rem; color: #64748B;">
                    <i class="fa-solid fa-circle-info" style="color: #176B5B; margin-right: 4px;"></i>
                    Sender information is synchronized with your Company Profile settings.
                </div>
            </div>
        </div>

        <!-- Section 2: Email Connection -->
        <div class="vg-card" style="margin-bottom: 0;">
            <div class="card-header" style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(56, 189, 248, 0.12); color: #0284C7; display: flex; align-items: center; justify-content: center; font-size: 0.95rem;">
                        <i class="fa-solid fa-server"></i>
                    </div>
                    <span class="card-title" style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #0F172A;">Email Connection</span>
                </div>
                <div>
                    <?php if (!empty($smtpConfig['is_configured'])): ?>
                        <span class="badge badge-success" style="font-size: 0.8rem; padding: 0.35rem 0.75rem;">
                            <span class="badge-dot"></span> Ready
                        </span>
                    <?php else: ?>
                        <span class="badge badge-warning" style="font-size: 0.8rem; padding: 0.35rem 0.75rem;">
                            <span class="badge-dot"></span> Incomplete
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-body">
                <p style="color: #64748B; font-size: 0.88rem; line-height: 1.5; margin: 0 0 1.25rem;">
                    The system connects to your email service using these connection parameters. Server passwords remain securely masked at all times.
                </p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div style="background: #F8FAFC; padding: 0.85rem 1rem; border-radius: 10px; border: 1px solid var(--vg-pearl-200, #E2E8F0);">
                        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #64748B;">Mail Server</div>
                        <div style="font-size: 0.95rem; font-weight: 700; color: #0F172A; margin-top: 0.25rem; word-break: break-all;">
                            <?= e($smtpConfig['host'] ?? '127.0.0.1') ?>
                        </div>
                    </div>

                    <div style="background: #F8FAFC; padding: 0.85rem 1rem; border-radius: 10px; border: 1px solid var(--vg-pearl-200, #E2E8F0);">
                        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #64748B;">Port &amp; Security</div>
                        <div style="font-size: 0.95rem; font-weight: 700; color: #0F172A; margin-top: 0.25rem;">
                            <?= (int)($smtpConfig['port'] ?? 587) ?> &bull; <?= strtoupper(e($smtpConfig['encryption'] ?? 'TLS')) ?>
                        </div>
                    </div>

                    <div style="background: #F8FAFC; padding: 0.85rem 1rem; border-radius: 10px; border: 1px solid var(--vg-pearl-200, #E2E8F0);">
                        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #64748B;">Account Username</div>
                        <div style="font-size: 0.92rem; font-weight: 600; color: #334155; margin-top: 0.25rem; word-break: break-all;">
                            <?= e($smtpConfig['username'] ?: 'None') ?>
                        </div>
                    </div>

                    <div style="background: #F8FAFC; padding: 0.85rem 1rem; border-radius: 10px; border: 1px solid var(--vg-pearl-200, #E2E8F0);">
                        <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #64748B;">Password</div>
                        <div style="font-size: 0.92rem; font-weight: 600; color: #94A3B8; font-family: monospace; letter-spacing: 2px; margin-top: 0.25rem;">
                            <?= e($smtpConfig['password_masked'] ?? '••••••••') ?>
                        </div>
                    </div>
                </div>

                <!-- Test Connection Button -->
                <div style="display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap;">
                    <button type="button" class="btn btn-outline btn-sm" onclick="checkEmailConnection(this)">
                        <i class="fa-solid fa-rotate" style="margin-right: 5px;"></i>
                        <span>Check Connection</span>
                    </button>
                    <span style="font-size: 0.8rem; color: #64748B;">
                        Credentials are securely configured in your server environment (<code>.env</code>).
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- Section 3: Send Diagnostic Test Email -->
    <div class="vg-card" style="margin-bottom: 2rem;">
        <div class="card-header">
            <span class="card-title" style="font-size: 1.05rem; font-weight: 700; color: #0F172A; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-paper-plane" style="color: #176B5B;"></i>
                <span>Send Test Email</span>
            </span>
        </div>
        <div class="card-body">
            <p style="color: #64748B; font-size: 0.88rem; line-height: 1.5; margin: 0 0 1.25rem;">
                Send an actual test message to any email address to confirm that emails arrive properly.
            </p>

            <form action="<?= url('/office/emails/send-test') ?>" method="POST" id="settingsTestForm" onsubmit="handleSettingsSendTest(event, this)" style="max-width: 580px;">
                <?= csrf_field() ?>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="settingsRecipientEmail" class="form-label" style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">Recipient Email Address</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="email" 
                               id="settingsRecipientEmail"
                               name="recipient_email" 
                               required 
                               placeholder="<?= e(__('placeholder.e_g_name_example_com', 'e.g. name@example.com')) ?>" 
                               class="form-control"
                               style="flex: 1; border-radius: 8px; padding: 0.6rem 0.85rem; border: 1px solid var(--vg-pearl-200, #CBD5E1);">
                        <button type="submit" id="btnSettingsSendTest" class="btn btn-primary" style="white-space: nowrap; font-weight: 700; border-radius: 8px;">
                            <i class="fa-solid fa-paper-plane" style="margin-right: 5px;"></i>
                            <span>Send Test</span>
                        </button>
                    </div>
                </div>
                <div id="settingsTestResult" style="display: none; padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.88rem; margin-top: 0.75rem;"></div>
            </form>
        </div>
    </div>

    <!-- Section 4: Domain & Deliverability Advice (Office-friendly) -->
    <div class="vg-card">
        <div class="card-header">
            <span class="card-title" style="font-size: 1.05rem; font-weight: 700; color: #0F172A; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-shield-halved" style="color: #0284C7;"></i>
                <span>Email Deliverability &amp; Inbox Placement</span>
            </span>
        </div>
        <div class="card-body">
            <p style="color: #64748B; font-size: 0.88rem; line-height: 1.5; margin: 0 0 1.25rem;">
                To ensure your customer confirmation emails and invoices reach the main inbox rather than the spam folder, make sure these standard domain records are added to your domain registrar (such as GoDaddy, Cloudflare, or Namecheap).
            </p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                
                <!-- SPF Record -->
                <div style="background: #F8FAFC; border: 1px solid var(--vg-pearl-200, #E2E8F0); border-radius: 10px; padding: 1.15rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                        <strong style="color: #0F172A; font-size: 0.95rem;">1. SPF (Sender Policy)</strong>
                        <span class="badge badge-info" style="font-size: 0.72rem;"><?= e($guidance['spf']['type'] ?? 'TXT') ?></span>
                    </div>
                    <div style="color: #64748B; font-size: 0.8rem; margin-bottom: 0.75rem;">
                        Authorizes your mail server to send emails on behalf of your domain name.
                    </div>
                    <div style="background: #FFFFFF; border: 1px solid var(--vg-pearl-200, #CBD5E1); border-radius: 6px; padding: 0.55rem 0.75rem; font-family: monospace; font-size: 0.8rem; color: #176B5B; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; word-break: break-all;">
                        <span><?= e($guidance['spf']['value'] ?? 'v=spf1 include:_spf.google.com ~all') ?></span>
                        <button type="button" onclick="copySnippet('<?= e($guidance['spf']['value'] ?? 'v=spf1 include:_spf.google.com ~all') ?>', this)" style="background: transparent; border: none; color: #64748B; cursor: pointer; padding: 0.2rem;" title="<?= e(__('tooltip.copy_to_clipboard', 'Copy to clipboard')) ?>">
                            <i class="fa-solid fa-copy"></i>
                        </button>
                    </div>
                </div>

                <!-- DKIM Record -->
                <div style="background: #F8FAFC; border: 1px solid var(--vg-pearl-200, #E2E8F0); border-radius: 10px; padding: 1.15rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                        <strong style="color: #0F172A; font-size: 0.95rem;">2. DKIM (Email Signature)</strong>
                        <span class="badge badge-info" style="font-size: 0.72rem;"><?= e($guidance['dkim']['type'] ?? 'TXT') ?></span>
                    </div>
                    <div style="color: #64748B; font-size: 0.8rem; margin-bottom: 0.75rem;">
                        Digitally signs outgoing emails so recipient servers can verify they have not been tampered with.
                    </div>
                    <div style="background: #FFFFFF; border: 1px solid var(--vg-pearl-200, #CBD5E1); border-radius: 6px; padding: 0.55rem 0.75rem; font-family: monospace; font-size: 0.8rem; color: #176B5B; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; word-break: break-all;">
                        <span>Selector: <?= e($guidance['dkim']['selector'] ?? 'google') ?></span>
                        <button type="button" onclick="copySnippet('<?= e($guidance['dkim']['selector'] ?? 'google') ?>', this)" style="background: transparent; border: none; color: #64748B; cursor: pointer; padding: 0.2rem;" title="<?= e(__('tooltip.copy_selector', 'Copy selector')) ?>">
                            <i class="fa-solid fa-copy"></i>
                        </button>
                    </div>
                </div>

                <!-- DMARC Record -->
                <div style="background: #F8FAFC; border: 1px solid var(--vg-pearl-200, #E2E8F0); border-radius: 10px; padding: 1.15rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                        <strong style="color: #0F172A; font-size: 0.95rem;">3. DMARC Policy</strong>
                        <span class="badge badge-info" style="font-size: 0.72rem;"><?= e($guidance['dmarc']['type'] ?? 'TXT') ?></span>
                    </div>
                    <div style="color: #64748B; font-size: 0.8rem; margin-bottom: 0.75rem;">
                        Protects your brand by preventing unauthorized spammers from spoofing your email address.
                    </div>
                    <div style="background: #FFFFFF; border: 1px solid var(--vg-pearl-200, #CBD5E1); border-radius: 6px; padding: 0.55rem 0.75rem; font-family: monospace; font-size: 0.8rem; color: #176B5B; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; word-break: break-all;">
                        <span><?= e($guidance['dmarc']['value'] ?? 'v=DMARC1; p=none;') ?></span>
                        <button type="button" onclick="copySnippet('<?= e($guidance['dmarc']['value'] ?? 'v=DMARC1; p=none;') ?>', this)" style="background: transparent; border: none; color: #64748B; cursor: pointer; padding: 0.2rem;" title="<?= e(__('tooltip.copy_to_clipboard', 'Copy to clipboard')) ?>">
                            <i class="fa-solid fa-copy"></i>
                        </button>
                    </div>
                </div>

            </div>

            <!-- Deliverability Checklist -->
            <?php if (!empty($guidance['recommendations'])): ?>
                <div style="background: #F1F5F9; border-radius: 10px; padding: 1rem 1.25rem;">
                    <div style="color: #0F172A; font-weight: 700; font-size: 0.88rem; margin-bottom: 0.5rem;">
                        <i class="fa-solid fa-circle-check" style="color: #10B981; margin-right: 0.35rem;"></i>
                        Best Practices for Clean Email Delivery
                    </div>
                    <ul style="margin: 0; padding-left: 1.25rem; color: #475569; font-size: 0.85rem; line-height: 1.6;">
                        <?php foreach ($guidance['recommendations'] as $rec): ?>
                            <li><?= e($rec) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
function copySnippet(text, btn) {
    if (!navigator.clipboard) {
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Snippet copied: ' + text, 'info');
        }
        return;
    }
    navigator.clipboard.writeText(text).then(() => {
        const icon = btn.querySelector('i');
        icon.className = 'fa-solid fa-check';
        icon.style.color = '#10B981';
        if (typeof window.vgNotify === 'function') {
            window.vgNotify('Copied to clipboard!', 'success');
        }
        setTimeout(() => {
            icon.className = 'fa-solid fa-copy';
            icon.style.color = '#64748B';
        }, 1500);
    });
}

function checkEmailConnection(btn) {
    const alertEl = document.getElementById('connectionStatusAlert');
    const textEl = btn.querySelector('span') || btn;
    const originalText = textEl.textContent;

    btn.disabled = true;
    textEl.textContent = 'Checking...';
    alertEl.style.display = 'none';

    fetch('<?= url('/office/emails/test-smtp') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '<?= csrf_token() ?>'
        }
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        textEl.textContent = originalText;
        alertEl.style.display = 'block';

        if (data.connected || data.success) {
            alertEl.style.background = 'rgba(16, 185, 129, 0.1)';
            alertEl.style.border = '1px solid rgba(16, 185, 129, 0.3)';
            alertEl.style.color = '#065F46';
            alertEl.innerHTML = '<i class="fa-solid fa-circle-check" style="color:#10B981; margin-right:6px;"></i> <strong>Connection successful!</strong> Mail server responded in ' + (data.latency_ms ? data.latency_ms + 'ms' : 'normal time') + '.';
            if (typeof window.vgNotify === 'function') {
                window.vgNotify('Connection test passed!', 'success');
            }
        } else {
            alertEl.style.background = 'rgba(239, 68, 68, 0.1)';
            alertEl.style.border = '1px solid rgba(239, 68, 68, 0.3)';
            alertEl.style.color = '#991B1B';
            alertEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation" style="color:#EF4444; margin-right:6px;"></i> <strong>Connection issue:</strong> ' + (data.message || data.error || 'Could not connect to the mail server.') + ' Please check your server settings in the .env configuration.';
            if (typeof window.vgNotify === 'function') {
                window.vgNotify('Connection failed: ' + (data.message || data.error || 'Server unreachable'), 'error');
            }
        }
    })
    .catch(err => {
        btn.disabled = false;
        textEl.textContent = originalText;
        alertEl.style.display = 'block';
        alertEl.style.background = 'rgba(239, 68, 68, 0.1)';
        alertEl.style.border = '1px solid rgba(239, 68, 68, 0.3)';
        alertEl.style.color = '#991B1B';
        alertEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation" style="color:#EF4444; margin-right:6px;"></i> <strong>Error:</strong> ' + err.message;
    });
}

function handleSettingsSendTest(e, form) {
    e.preventDefault();
    const btn = document.getElementById('btnSettingsSendTest');
    const resultAlert = document.getElementById('settingsTestResult');
    const originalHtml = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Sending...</span>';
    resultAlert.style.display = 'none';

    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        resultAlert.style.display = 'block';

        if (data.success) {
            resultAlert.style.background = 'rgba(16, 185, 129, 0.1)';
            resultAlert.style.border = '1px solid rgba(16, 185, 129, 0.3)';
            resultAlert.style.color = '#065F46';
            resultAlert.innerHTML = '<i class="fa-solid fa-circle-check" style="color:#10B981; margin-right:6px;"></i> ' + (data.message || 'Test email dispatched successfully!');
            if (typeof window.vgNotify === 'function') {
                window.vgNotify('Test email sent successfully!', 'success');
            }
        } else {
            resultAlert.style.background = 'rgba(239, 68, 68, 0.1)';
            resultAlert.style.border = '1px solid rgba(239, 68, 68, 0.3)';
            resultAlert.style.color = '#991B1B';
            resultAlert.innerHTML = '<i class="fa-solid fa-triangle-exclamation" style="color:#EF4444; margin-right:6px;"></i> ' + (data.message || 'Could not send test email.');
            if (typeof window.vgNotify === 'function') {
                window.vgNotify('Test email could not be sent.', 'error');
            }
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        resultAlert.style.display = 'block';
        resultAlert.style.background = 'rgba(239, 68, 68, 0.1)';
        resultAlert.style.border = '1px solid rgba(239, 68, 68, 0.3)';
        resultAlert.style.color = '#991B1B';
        resultAlert.innerHTML = '<i class="fa-solid fa-triangle-exclamation" style="color:#EF4444; margin-right:6px;"></i> ' + err.message;
    });
}
</script>
