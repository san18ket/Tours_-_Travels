<div class="page-container" style="max-width: 1250px; margin: 0 auto; padding: 1.5rem 1rem;">
    <!-- Page Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <h2 style="margin: 0; color: #FFFFFF; font-size: 1.6rem; font-weight: 700; display: flex; align-items: center; gap: 0.65rem;">
                <i class="fa-solid fa-sliders" style="color: #10B981;"></i>
                <span>SMTP Configuration &amp; Deliverability Guidance</span>
            </h2>
            <p style="margin: 0.35rem 0 0; color: #94A3B8; font-size: 0.9rem;">
                Centralized SMTP transport parameters, connection handshakes, and SPF/DKIM/DMARC compliance.
            </p>
        </div>
        <div>
            <button type="button" onclick="testSmtpConnection(this)" class="btn" style="background: #1E3E62; color: #38BDF8; border: 1px solid #335384; padding: 0.55rem 1.15rem; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.2s;">
                <i class="fa-solid fa-bolt"></i>
                <span id="smtpTestBtnText">Test Socket Handshake</span>
            </button>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <?php require __DIR__ . '/_tabs.php'; ?>

    <!-- Flash Notifications -->
    <?php if ($msg = \App\Core\Session::getFlash('success')): ?>
        <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); border-radius: 10px; padding: 0.9rem 1.25rem; margin-bottom: 1.5rem; color: #10B981; display: flex; align-items: center; gap: 0.75rem;">
            <i class="fa-solid fa-circle-check"></i>
            <span style="font-size: 0.9rem;"><?= e($msg) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($msg = \App\Core\Session::getFlash('danger')): ?>
        <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.35); border-radius: 10px; padding: 0.9rem 1.25rem; margin-bottom: 1.5rem; color: #EF4444; display: flex; align-items: center; gap: 0.75rem;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span style="font-size: 0.9rem;"><?= e($msg) ?></span>
        </div>
    <?php endif; ?>

    <!-- Socket Test Inline Result Alert -->
    <div id="smtpSocketResult" style="display: none; margin-bottom: 1.5rem; padding: 0.9rem 1.25rem; border-radius: 10px; font-size: 0.9rem;"></div>

    <!-- Configuration & Test Dispatch Grid -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Card 1: Active SMTP Parameters -->
        <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; padding: 1.5rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; border-bottom: 1px solid #2E5077; padding-bottom: 0.75rem;">
                <div style="font-weight: 700; color: #FFFFFF; font-size: 1.05rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-server" style="color: #38BDF8;"></i>
                    <span>SMTP Relay Parameters</span>
                </div>
                <span class="badge" style="background: <?= !empty($smtpConfig['has_credentials']) ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)' ?>; color: <?= !empty($smtpConfig['has_credentials']) ? '#10B981' : '#EF4444' ?>; border: 1px solid <?= !empty($smtpConfig['has_credentials']) ? 'rgba(16, 185, 129, 0.3)' : 'rgba(239, 68, 68, 0.3)' ?>; font-size: 0.8rem; padding: 0.25rem 0.65rem; border-radius: 6px;">
                    <?= !empty($smtpConfig['has_credentials']) ? 'Active &amp; Authenticated' : 'Missing Auth' ?>
                </span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.88rem;">
                <div>
                    <div style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Host Address</div>
                    <div style="color: #FFFFFF; font-weight: 600; margin-top: 0.25rem;"><?= e($smtpConfig['host'] ?? '127.0.0.1') ?></div>
                </div>
                <div>
                    <div style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Port &amp; Security</div>
                    <div style="color: #FFFFFF; font-weight: 600; margin-top: 0.25rem;">
                        <?= (int)($smtpConfig['port'] ?? 587) ?> • <?= strtoupper(e($smtpConfig['encryption'] ?? 'tls')) ?>
                    </div>
                </div>
                <div>
                    <div style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Auth Username</div>
                    <div style="color: #CBD5E1; margin-top: 0.25rem; word-break: break-all;"><?= e($smtpConfig['username'] ?? 'None') ?></div>
                </div>
                <div>
                    <div style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Password</div>
                    <div style="color: #94A3B8; font-family: monospace; letter-spacing: 2px; margin-top: 0.25rem;"><?= e($smtpConfig['password_masked'] ?? '••••••••') ?></div>
                </div>
                <div>
                    <div style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">From Address</div>
                    <div style="color: #CBD5E1; margin-top: 0.25rem; word-break: break-all;"><?= e($smtpConfig['from_address'] ?? '') ?></div>
                </div>
                <div>
                    <div style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">From Display Name</div>
                    <div style="color: #CBD5E1; margin-top: 0.25rem;"><?= e($smtpConfig['from_name'] ?? '') ?></div>
                </div>
                <div style="grid-column: 1 / -1;">
                    <div style="color: #94A3B8; font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Reply-To Address</div>
                    <div style="color: #CBD5E1; margin-top: 0.25rem;"><?= e($smtpConfig['reply_to'] ?? '') ?></div>
                </div>
            </div>

            <div style="margin-top: 1.25rem; background: #0B192C; border: 1px solid #2E5077; border-radius: 8px; padding: 0.85rem 1rem; font-size: 0.8rem; color: #94A3B8; line-height: 1.5;">
                <i class="fa-solid fa-shield-halved" style="color: #10B981; margin-right: 0.35rem;"></i>
                SMTP credentials and connection settings are securely configured via the application environment configuration file (<code>.env</code>) and are never exposed in client source code.
            </div>
        </div>

        <!-- Card 2: Dispatch Diagnostic Email -->
        <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; padding: 1.5rem; display: flex; flex-direction: column;">
            <div style="font-weight: 700; color: #FFFFFF; font-size: 1.05rem; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem; border-bottom: 1px solid #2E5077; padding-bottom: 0.75rem;">
                <i class="fa-solid fa-paper-plane" style="color: #10B981;"></i>
                <span>Dispatch Live Diagnostic Message</span>
            </div>

            <p style="color: #94A3B8; font-size: 0.85rem; margin: 0 0 1.25rem; line-height: 1.5;">
                Send a real, theme-matched diagnostic test message through the production SMTP pipeline to verify end-to-end inbox delivery, TLS encryption handshakes, and SPF validation.
            </p>

            <form action="<?= url('/office/emails/send-test') ?>" method="POST" id="settingsTestForm" onsubmit="handleSendTest(event, this)" style="flex: 1; display: flex; flex-direction: column;">
                <?= csrf_field() ?>
                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; color: #CBD5E1; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem;">Target Recipient Email Address</label>
                    <input type="email" name="recipient_email" required placeholder="Enter destination email address..." style="width: 100%; box-sizing: border-box; background: #0B192C; border: 1px solid #335384; border-radius: 8px; padding: 0.75rem 1rem; color: #FFFFFF; font-size: 0.95rem;" value="<?= e($user['email'] ?? '') ?>">
                </div>

                <div style="margin-top: auto; display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                    <span style="font-size: 0.8rem; color: #64748B;">
                        Immediate SMTP dispatch
                    </span>
                    <button type="submit" id="sendTestSubmitBtn" class="btn" style="background: #10B981; color: #FFFFFF; border: none; border-radius: 8px; padding: 0.7rem 1.4rem; font-weight: 700; font-size: 0.9rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Send Diagnostic Email</span>
                    </button>
                </div>
            </form>
            <div id="quickTestResult" style="display: none; margin-top: 1rem; padding: 0.85rem 1rem; border-radius: 8px; font-size: 0.85rem;"></div>
        </div>
    </div>

    <!-- Deliverability & DNS Compliance Section -->
    <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; padding: 1.5rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.25rem; border-bottom: 1px solid #2E5077; padding-bottom: 0.75rem;">
            <div>
                <h3 style="margin: 0; color: #FFFFFF; font-size: 1.15rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-shield-virus" style="color: #38BDF8;"></i>
                    <span>Domain Deliverability &amp; DNS Records (SPF, DKIM, DMARC)</span>
                </h3>
                <p style="margin: 0.25rem 0 0; color: #94A3B8; font-size: 0.85rem;">
                    Proper DNS records ensure your transactional emails land in customer inboxes rather than spam filters.
                </p>
            </div>
            <span class="badge" style="background: rgba(56, 189, 248, 0.15); color: #38BDF8; border: 1px solid rgba(56, 189, 248, 0.3); font-size: 0.8rem; padding: 0.3rem 0.75rem; border-radius: 6px;">
                Domain: <?= e($guidance['domain'] ?? 'domain.com') ?>
            </span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
            <!-- SPF Card -->
            <div style="background: #0B192C; border: 1px solid #335384; border-radius: 10px; padding: 1.15rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                    <strong style="color: #FFFFFF; font-size: 0.95rem;">1. SPF (Sender Policy Framework)</strong>
                    <span style="font-size: 0.75rem; background: #1E3E62; color: #38BDF8; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: 700;"><?= e($guidance['spf']['type'] ?? 'TXT') ?></span>
                </div>
                <div style="color: #94A3B8; font-size: 0.8rem; margin-bottom: 0.75rem;">
                    <?= e($guidance['spf']['description'] ?? 'Authorizes SMTP servers to send email on behalf of your domain.') ?>
                </div>
                <div style="background: #040D1A; border: 1px solid #2E5077; border-radius: 6px; padding: 0.65rem 0.85rem; font-family: monospace; font-size: 0.8rem; color: #34D399; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; word-break: break-all;">
                    <span><?= e($guidance['spf']['value'] ?? '') ?></span>
                    <button type="button" onclick="copySnippet('<?= e($guidance['spf']['value'] ?? '') ?>', this)" style="background: transparent; border: none; color: #94A3B8; cursor: pointer; padding: 0.2rem;" title="Copy to clipboard">
                        <i class="fa-solid fa-copy"></i>
                    </button>
                </div>
            </div>

            <!-- DKIM Card -->
            <div style="background: #0B192C; border: 1px solid #335384; border-radius: 10px; padding: 1.15rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                    <strong style="color: #FFFFFF; font-size: 0.95rem;">2. DKIM (DomainKeys Identified Mail)</strong>
                    <span style="font-size: 0.75rem; background: #1E3E62; color: #38BDF8; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: 700;"><?= e($guidance['dkim']['type'] ?? 'TXT / CNAME') ?></span>
                </div>
                <div style="color: #94A3B8; font-size: 0.8rem; margin-bottom: 0.75rem;">
                    <?= e($guidance['dkim']['description'] ?? 'Cryptographically signs outbound emails to verify integrity.') ?>
                </div>
                <div style="background: #040D1A; border: 1px solid #2E5077; border-radius: 6px; padding: 0.65rem 0.85rem; font-family: monospace; font-size: 0.8rem; color: #38BDF8; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; word-break: break-all;">
                    <span>Selector: <?= e($guidance['dkim']['host'] ?? 'google._domainkey') ?></span>
                    <button type="button" onclick="copySnippet('<?= e($guidance['dkim']['host'] ?? '') ?>', this)" style="background: transparent; border: none; color: #94A3B8; cursor: pointer; padding: 0.2rem;" title="Copy to clipboard">
                        <i class="fa-solid fa-copy"></i>
                    </button>
                </div>
            </div>

            <!-- DMARC Card -->
            <div style="background: #0B192C; border: 1px solid #335384; border-radius: 10px; padding: 1.15rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                    <strong style="color: #FFFFFF; font-size: 0.95rem;">3. DMARC Policy</strong>
                    <span style="font-size: 0.75rem; background: #1E3E62; color: #38BDF8; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: 700;"><?= e($guidance['dmarc']['type'] ?? 'TXT') ?></span>
                </div>
                <div style="color: #94A3B8; font-size: 0.8rem; margin-bottom: 0.75rem;">
                    <?= e($guidance['dmarc']['description'] ?? 'Enforces SPF/DKIM alignment and receives delivery aggregate reports.') ?>
                </div>
                <div style="background: #040D1A; border: 1px solid #2E5077; border-radius: 6px; padding: 0.65rem 0.85rem; font-family: monospace; font-size: 0.8rem; color: #F59E0B; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; word-break: break-all;">
                    <span><?= e($guidance['dmarc']['value'] ?? '') ?></span>
                    <button type="button" onclick="copySnippet('<?= e($guidance['dmarc']['value'] ?? '') ?>', this)" style="background: transparent; border: none; color: #94A3B8; cursor: pointer; padding: 0.2rem;" title="Copy to clipboard">
                        <i class="fa-solid fa-copy"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Recommendations Checklist -->
        <?php if (!empty($guidance['recommendations'])): ?>
            <div style="background: rgba(11, 25, 44, 0.7); border: 1px solid #2E5077; border-radius: 8px; padding: 1rem 1.25rem;">
                <div style="color: #FFFFFF; font-weight: 700; font-size: 0.88rem; margin-bottom: 0.5rem;">
                    <i class="fa-solid fa-list-check" style="color: #10B981; margin-right: 0.35rem;"></i>
                    Deliverability Best Practices Checklist
                </div>
                <ul style="margin: 0; padding-left: 1.25rem; color: #CBD5E1; font-size: 0.82rem; line-height: 1.6;">
                    <?php foreach ($guidance['recommendations'] as $rec): ?>
                        <li><?= e($rec) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function copySnippet(text, btn) {
    if (!navigator.clipboard) {
        alert('Copied: ' + text);
        return;
    }
    navigator.clipboard.writeText(text).then(() => {
        const icon = btn.querySelector('i');
        icon.className = 'fa-solid fa-check';
        icon.style.color = '#10B981';
        setTimeout(() => {
            icon.className = 'fa-solid fa-copy';
            icon.style.color = '#94A3B8';
        }, 1500);
    });
}

function testSmtpConnection(btn) {
    const textEl = document.getElementById('smtpTestBtnText');
    const resultEl = document.getElementById('smtpSocketResult');
    const originalText = textEl.textContent;

    btn.disabled = true;
    textEl.textContent = 'Testing Handshake...';
    resultEl.style.display = 'none';

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
        resultEl.style.display = 'block';

        if (data.connected) {
            resultEl.style.background = 'rgba(16, 185, 129, 0.15)';
            resultEl.style.border = '1px solid rgba(16, 185, 129, 0.4)';
            resultEl.style.color = '#10B981';
            resultEl.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + (data.message || 'SMTP Socket connection succeeded!') + (data.latency_ms ? ' (' + data.latency_ms + 'ms)' : '');
        } else {
            resultEl.style.background = 'rgba(239, 68, 68, 0.15)';
            resultEl.style.border = '1px solid rgba(239, 68, 68, 0.4)';
            resultEl.style.color = '#EF4444';
            resultEl.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Handshake Failed: ' + (data.error || 'Could not establish connection to SMTP host.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        textEl.textContent = originalText;
        resultEl.style.display = 'block';
        resultEl.style.background = 'rgba(239, 68, 68, 0.15)';
        resultEl.style.border = '1px solid rgba(239, 68, 68, 0.4)';
        resultEl.style.color = '#EF4444';
        resultEl.textContent = 'Network error: ' + err.message;
    });
}

function handleSendTest(e, form) {
    e.preventDefault();
    const btn = document.getElementById('sendTestSubmitBtn');
    const resultEl = document.getElementById('quickTestResult');
    const originalHtml = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Dispatched...';
    resultEl.style.display = 'none';

    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        resultEl.style.display = 'block';

        if (data.success) {
            resultEl.style.background = 'rgba(16, 185, 129, 0.15)';
            resultEl.style.border = '1px solid rgba(16, 185, 129, 0.4)';
            resultEl.style.color = '#10B981';
            resultEl.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + (data.message || 'Test email dispatched successfully!');
        } else {
            resultEl.style.background = 'rgba(239, 68, 68, 0.15)';
            resultEl.style.border = '1px solid rgba(239, 68, 68, 0.4)';
            resultEl.style.color = '#EF4444';
            resultEl.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> ' + (data.message || 'Dispatch failed.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        resultEl.style.display = 'block';
        resultEl.style.background = 'rgba(239, 68, 68, 0.15)';
        resultEl.style.border = '1px solid rgba(239, 68, 68, 0.4)';
        resultEl.style.color = '#EF4444';
        resultEl.textContent = 'Error: ' + err.message;
    });
}
</script>

