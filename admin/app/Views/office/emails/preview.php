<?php
/**
 * Email Templates Previewer
 * Friendly template previews with realistic mock payloads and device switcher.
 */
?>
<div class="email-templates-page" style="max-width: 1350px; margin: 0 auto; padding: 1.25rem 0.5rem 2rem;">

    <!-- 1. Page Header -->
    <?= render_page_header([
        'title'       => 'Email Templates',
        'description' => 'Preview the emails your customers, employees, and drivers receive from the system.',
        'badge'       => 'Design & Branding',
        'breadcrumbs' => [
            ['label' => 'Dashboard', 'url' => url('/office/dashboard')],
            ['label' => 'Email Center', 'url' => url('/office/emails')],
            ['label' => 'Email Templates', 'url' => ''],
        ],
        'actions'     => '<div style="display:flex; gap:0.65rem; align-items:center; flex-wrap:wrap;">'
            . '<a href="' . url('/office/emails/preview/render?template=' . urlencode($selectedTemplate)) . '" target="_blank" class="btn btn-outline btn-sm">'
            . '<i class="fa-solid fa-arrow-up-right-from-square" style="margin-right:5px;"></i> Open in New Tab</a>'
            . '<a href="' . url('/office/emails') . '" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-left" style="margin-right:5px;"></i> Back to Overview</a>'
            . '</div>',
    ]) ?>

    <!-- 2. Navigation Tabs -->
    <?php require __DIR__ . '/_tabs.php'; ?>

    <!-- 3. Toolbar Card -->
    <div class="vg-card" style="margin-bottom: 1.5rem; padding: 1.15rem 1.5rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            
            <!-- Template Selection Dropdown -->
            <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1; min-width: 280px;">
                <label for="templateSelect" style="color: #334155; font-weight: 700; font-size: 0.88rem; white-space: nowrap;">
                    <i class="fa-solid fa-file-lines" style="color: #176B5B; margin-right: 0.35rem;"></i> Select Template:
                </label>
                <select id="templateSelect" onchange="changeTemplate(this.value)" class="form-select" style="flex: 1; max-width: 380px; font-weight: 600; padding: 0.55rem 0.85rem; font-size: 0.88rem; border-radius: 8px; border: 1px solid var(--vg-pearl-200, #CBD5E1);">
                    <?php
                        $categories = [
                            'Bookings & Journeys' => [
                                'booking/created'         => 'Booking Created (Pending)',
                                'booking/confirmed'       => 'Booking Confirmation',
                                'booking/cancelled'       => 'Booking Cancellation',
                                'booking/driver-assigned' => 'Driver Assignment (Customer)',
                                'trip/reminder'           => 'Upcoming Trip Reminder',
                                'trip/started'            => 'Trip Started',
                                'trip/completed'          => 'Trip Completed',
                            ],
                            'Driver Duty Notifications' => [
                                'driver/trip-assigned' => 'Driver Trip Assignment',
                            ],
                            'Invoices & Payments' => [
                                'payment/successful'           => 'Payment Receipt',
                                'finance/invoice-notification' => 'Invoice Copy',
                                'finance/refund-confirmation'  => 'Refund Confirmation',
                            ],
                            'Customer & Employee Accounts' => [
                                'customer/welcome' => 'Welcome Email (Customer)',
                                'employee/welcome' => 'Welcome Email (Employee)',
                            ],
                            'Security & Verification' => [
                                'auth/otp'                   => 'Login Verification Code (OTP)',
                                'auth/recovery-password-otp' => 'Password Reset Code',
                                'auth/recovery-username-otp' => 'Username Recovery Code',
                                'auth/username-recovered'    => 'Username Reminder',
                            ],
                            'System Testing' => [
                                'test' => 'Test Email',
                            ],
                        ];
                    ?>
                    <?php foreach ($categories as $catName => $items): ?>
                        <optgroup label="<?= e($catName) ?>">
                            <?php foreach ($items as $key => $friendlyName): ?>
                                <?php if (isset($templates[$key])): ?>
                                    <option value="<?= e($key) ?>" <?= $selectedTemplate === $key ? 'selected' : '' ?>>
                                        <?= e($friendlyName) ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Brand Theme Tokens Indicator -->
            <div style="display: flex; align-items: center; gap: 0.65rem; background: #F8FAFC; border: 1px solid var(--vg-pearl-200, #E2E8F0); border-radius: 8px; padding: 0.4rem 0.85rem;">
                <span style="color: #64748B; font-size: 0.78rem; text-transform: uppercase; font-weight: 700;">Brand:</span>
                <div style="display: flex; align-items: center; gap: 0.35rem;" title="<?= e(__('tooltip.primary_theme_color', 'Primary Theme Color')) ?>">
                    <span style="width: 14px; height: 14px; border-radius: 50%; background: <?= e($themeColors['primary']) ?>; display: inline-block; border: 1px solid #CBD5E1;"></span>
                    <span style="color: #334155; font-size: 0.78rem; font-family: monospace; font-weight: 600;"><?= e($themeColors['primary']) ?></span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.35rem;" title="<?= e(__('tooltip.secondary_theme_color', 'Secondary Theme Color')) ?>">
                    <span style="width: 14px; height: 14px; border-radius: 50%; background: <?= e($themeColors['secondary']) ?>; display: inline-block; border: 1px solid #CBD5E1;"></span>
                    <span style="color: #334155; font-size: 0.78rem; font-family: monospace; font-weight: 600;"><?= e($themeColors['secondary']) ?></span>
                </div>
            </div>

            <!-- Viewport Switcher -->
            <div style="display: flex; align-items: center; gap: 0.35rem; background: #F1F5F9; border: 1px solid var(--vg-pearl-200, #E2E8F0); border-radius: 8px; padding: 0.25rem;">
                <button type="button" id="viewportDesktopBtn" onclick="setViewport('desktop')" class="btn btn-sm" style="background: #176B5B; color: #FFFFFF; border: none; border-radius: 6px; padding: 0.4rem 0.85rem; font-size: 0.82rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.2s;">
                    <i class="fa-solid fa-desktop"></i>
                    <span>Desktop (600px)</span>
                </button>
                <button type="button" id="viewportMobileBtn" onclick="setViewport('mobile')" class="btn btn-sm" style="background: transparent; color: #64748B; border: none; border-radius: 6px; padding: 0.4rem 0.85rem; font-size: 0.82rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.2s;">
                    <i class="fa-solid fa-mobile-screen"></i>
                    <span>Mobile (375px)</span>
                </button>
            </div>

        </div>
    </div>

    <!-- 4. Interactive Preview Stage -->
    <div style="background: #F8FAFC; border: 1px solid var(--vg-pearl-200, #E2E8F0); border-radius: 16px; padding: 2rem 1rem; min-height: 650px; display: flex; flex-direction: column; align-items: center; justify-content: flex-start;">
        <div id="previewContainer" style="width: 100%; max-width: 600px; transition: max-width 0.3s cubic-bezier(0.4, 0, 0.2, 1); background: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08), 0 0 0 1px var(--vg-pearl-200, #E2E8F0);">
            <!-- Device Window Topbar -->
            <div style="background: #FAFAFA; padding: 0.65rem 1rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--vg-pearl-200, #E2E8F0);">
                <div style="display: flex; gap: 0.35rem; align-items: center;">
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #EF4444; display: inline-block;"></span>
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #F59E0B; display: inline-block;"></span>
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #10B981; display: inline-block;"></span>
                </div>
                <div id="previewDeviceLabel" style="color: #64748B; font-size: 0.75rem; font-family: monospace; text-transform: uppercase; font-weight: 700;">
                    Desktop Max 600px
                </div>
                <button type="button" onclick="refreshPreviewFrame()" style="background: transparent; border: none; color: #64748B; cursor: pointer; font-size: 0.82rem;" title="<?= e(__('tooltip.reload_template_preview', 'Reload Template Preview')) ?>">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </button>
            </div>

            <!-- Sandboxed Rendered Template Frame -->
            <iframe id="previewIframe" src="<?= url('/office/emails/preview/render?template=' . urlencode($selectedTemplate)) ?>" style="width: 100%; height: 750px; border: none; display: block; background: #FFFFFF;" sandbox="allow-same-origin"></iframe>
        </div>
    </div>

</div>

<script>
function changeTemplate(templateKey) {
    const baseUrl = '<?= url('/office/emails/preview') ?>';
    window.location.href = baseUrl + '?template=' + encodeURIComponent(templateKey);
}

function refreshPreviewFrame() {
    const iframe = document.getElementById('previewIframe');
    iframe.src = iframe.src;
}

function setViewport(mode) {
    const container = document.getElementById('previewContainer');
    const label = document.getElementById('previewDeviceLabel');
    const desktopBtn = document.getElementById('viewportDesktopBtn');
    const mobileBtn = document.getElementById('viewportMobileBtn');

    if (mode === 'mobile') {
        container.style.maxWidth = '375px';
        label.textContent = 'Mobile 375px';

        mobileBtn.style.background = '#176B5B';
        mobileBtn.style.color = '#FFFFFF';
        desktopBtn.style.background = 'transparent';
        desktopBtn.style.color = '#64748B';
    } else {
        container.style.maxWidth = '600px';
        label.textContent = 'Desktop Max 600px';

        desktopBtn.style.background = '#176B5B';
        desktopBtn.style.color = '#FFFFFF';
        mobileBtn.style.background = 'transparent';
        mobileBtn.style.color = '#64748B';
    }
}
</script>
