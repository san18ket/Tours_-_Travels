<div class="page-container" style="max-width: 1300px; margin: 0 auto; padding: 1.5rem 1rem;">
    <!-- Page Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <h2 style="margin: 0; color: #FFFFFF; font-size: 1.6rem; font-weight: 700; display: flex; align-items: center; gap: 0.65rem;">
                <i class="fa-solid fa-palette" style="color: #D6B77A;"></i>
                <span>Email Template Design &amp; Brand Previewer</span>
            </h2>
            <p style="margin: 0.35rem 0 0; color: #94A3B8; font-size: 0.9rem;">
                Live mobile-first rendering with dynamic website theme colors and realistic mock payloads.
            </p>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <a href="<?= url('/office/emails/preview/render?template=' . urlencode($selectedTemplate)) ?>" target="_blank" class="btn" style="background: #1E3E62; color: #38BDF8; border: 1px solid #335384; padding: 0.55rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>Open Raw in New Tab</span>
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <?php require __DIR__ . '/_tabs.php'; ?>

    <!-- Control Toolbar Card -->
    <div style="background: #1E3E62; border: 1px solid #2E5077; border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <!-- Template Selection Dropdown -->
        <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1; min-width: 280px;">
            <label for="templateSelect" style="color: #CBD5E1; font-weight: 700; font-size: 0.88rem; white-space: nowrap;">
                <i class="fa-solid fa-file-lines" style="color: #38BDF8; margin-right: 0.35rem;"></i> Select Template:
            </label>
            <select id="templateSelect" onchange="changeTemplate(this.value)" style="flex: 1; background: #0B192C; border: 1px solid #335384; border-radius: 8px; padding: 0.6rem 0.85rem; color: #FFFFFF; font-size: 0.9rem; font-weight: 600;">
                <?php
                    // Categorize templates
                    $categories = [
                        'Authentication & Security' => ['auth/otp', 'auth/recovery-password-otp', 'auth/username-recovered'],
                        'Bookings & Trips'          => ['booking/created', 'booking/confirmed', 'booking/cancelled', 'booking/driver-assigned', 'trip/reminder', 'trip/started', 'trip/completed'],
                        'Drivers & Chauffeurs'      => ['driver/trip-assigned'],
                        'Customers & Staff'         => ['customer/welcome', 'employee/welcome'],
                        'Finance & Invoicing'       => ['payment/successful', 'finance/invoice-notification', 'finance/refund-confirmation'],
                        'System Diagnostics'        => ['test'],
                    ];
                ?>
                <?php foreach ($categories as $catName => $keys): ?>
                    <optgroup label="<?= e($catName) ?>">
                        <?php foreach ($keys as $key): ?>
                            <?php if (isset($templates[$key])): ?>
                                <option value="<?= e($key) ?>" <?= $selectedTemplate === $key ? 'selected' : '' ?>>
                                    <?= is_array($templates[$key]) ? e($templates[$key]['name']) : e($templates[$key]) ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Active Theme Color Tokens Badges -->
        <div style="display: flex; align-items: center; gap: 0.65rem; background: #0B192C; border: 1px solid #335384; border-radius: 8px; padding: 0.4rem 0.85rem;">
            <span style="color: #94A3B8; font-size: 0.78rem; text-transform: uppercase; font-weight: 700;">Theme:</span>
            <div style="display: flex; align-items: center; gap: 0.35rem;" title="Primary Color">
                <span style="width: 14px; height: 14px; border-radius: 50%; background: <?= e($themeColors['primary']) ?>; display: inline-block; border: 1px solid #FFFFFF;"></span>
                <span style="color: #CBD5E1; font-size: 0.78rem; font-family: monospace;"><?= e($themeColors['primary']) ?></span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.35rem;" title="Secondary Color">
                <span style="width: 14px; height: 14px; border-radius: 50%; background: <?= e($themeColors['secondary']) ?>; display: inline-block; border: 1px solid #FFFFFF;"></span>
                <span style="color: #CBD5E1; font-size: 0.78rem; font-family: monospace;"><?= e($themeColors['secondary']) ?></span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.35rem;" title="Accent Color">
                <span style="width: 14px; height: 14px; border-radius: 50%; background: <?= e($themeColors['accent']) ?>; display: inline-block; border: 1px solid #FFFFFF;"></span>
                <span style="color: #CBD5E1; font-size: 0.78rem; font-family: monospace;"><?= e($themeColors['accent']) ?></span>
            </div>
        </div>

        <!-- Viewport Switcher Controls -->
        <div style="display: flex; align-items: center; gap: 0.35rem; background: #0B192C; border: 1px solid #335384; border-radius: 8px; padding: 0.25rem;">
            <button type="button" id="viewportDesktopBtn" onclick="setViewport('desktop')" class="viewport-toggle-btn active" style="background: #10B981; color: #FFFFFF; border: none; border-radius: 6px; padding: 0.45rem 0.85rem; font-size: 0.85rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; transition: all 0.2s;">
                <i class="fa-solid fa-desktop"></i>
                <span>Desktop (600px)</span>
            </button>
            <button type="button" id="viewportMobileBtn" onclick="setViewport('mobile')" class="viewport-toggle-btn" style="background: transparent; color: #94A3B8; border: none; border-radius: 6px; padding: 0.45rem 0.85rem; font-size: 0.85rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; transition: all 0.2s;">
                <i class="fa-solid fa-mobile-screen"></i>
                <span>Mobile (375px)</span>
            </button>
        </div>
    </div>

    <!-- Interactive Preview Stage Area -->
    <div style="background: #0B192C; border: 1px solid #2E5077; border-radius: 12px; padding: 2rem 1rem; min-height: 650px; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; box-shadow: inset 0 2px 10px rgba(0,0,0,0.4);">
        <!-- Stage Frame Mock Chrome -->
        <div id="previewContainer" style="width: 100%; max-width: 600px; transition: max-width 0.3s cubic-bezier(0.4, 0, 0.2, 1); background: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6), 0 0 0 1px #335384;">
            <!-- Browser / Device Bar -->
            <div style="background: #1E3E62; padding: 0.65rem 1rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #2E5077;">
                <div style="display: flex; gap: 0.35rem; align-items: center;">
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #EF4444; display: inline-block;"></span>
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #F59E0B; display: inline-block;"></span>
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #10B981; display: inline-block;"></span>
                </div>
                <div id="previewDeviceLabel" style="color: #94A3B8; font-size: 0.75rem; font-family: monospace; text-transform: uppercase; font-weight: 700;">
                    Desktop Max 600px
                </div>
                <button type="button" onclick="refreshPreviewFrame()" style="background: transparent; border: none; color: #94A3B8; cursor: pointer; font-size: 0.8rem;" title="Reload Preview">
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
        label.textContent = 'Mobile Portrait 375px';

        mobileBtn.style.background = '#10B981';
        mobileBtn.style.color = '#FFFFFF';
        desktopBtn.style.background = 'transparent';
        desktopBtn.style.color = '#94A3B8';
    } else {
        container.style.maxWidth = '600px';
        label.textContent = 'Desktop Max 600px';

        desktopBtn.style.background = '#10B981';
        desktopBtn.style.color = '#FFFFFF';
        mobileBtn.style.background = 'transparent';
        mobileBtn.style.color = '#94A3B8';
    }
}
</script>
