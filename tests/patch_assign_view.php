<?php
$file = __DIR__ . '/../app/Views/office/trips/assign.php';
$content = file_get_contents($file);

// Normalize to LF first
$content = str_replace("\r\n", "\n", $content);

// 1. Fix vehicles option tag if needed
$badVehicle = "                                        <?= e(\$v['make_model']) ?> (<?= e(\$v['vehicle_number']) ?>) · <?= (int)\$v['seating_capacity'] ?> Seats · <?= strtoupper(e(\$v['fuel_type'])) ?>\n<?php endif; ?>";
$goodVehicle = "                                        <?= e(\$v['make_model']) ?> (<?= e(\$v['vehicle_number']) ?>) · <?= (int)\$v['seating_capacity'] ?> Seats · <?= strtoupper(e(\$v['fuel_type'])) ?>\n                                    </option>";
$content = str_replace($badVehicle, $goodVehicle, $content);

// 2. Replace driver options block
$oldDriverBlock = <<<HTML
                                <?php foreach (\$availableDrivers as \$d): ?>
                                    <?php
                                        \$empCode = !empty(\$d['employee_code']) ? " [{\$d['employee_code']}]" : '';
                                        \$displayName = !empty(\$d['first_name']) ? trim(\$d['first_name'] . ' ' . (\$d['last_name'] ?? '')) : \$d['name'];
                                        \$displayPhone = \$d['employee_phone'] ?? \$d['phone'] ?? '';
                                    ?>
                                    <option value="<?= (int)\$d['id'] ?>" data-name="<?= e(\$displayName) ?>" data-code="<?= e(\$d['employee_code'] ?? '') ?>" data-phone="<?= e(\$displayPhone) ?>" data-license="<?= e(\$d['license_number']) ?>"><?= e(\$displayName) ?><?= e(\$empCode) ?> · <?= e(\$displayPhone) ?> · Lic: <?= e(\$d['license_number']) ?></option><?php if (false): ?>
                                        <?= e(\$d['name']) ?> (<?= e(\$d['phone']) ?>) · License: <?= e(\$d['license_number']) ?>
<?php endif; ?>
                                <?php endforeach; ?>
HTML;

$newDriverBlock = <<<HTML
                                <?php foreach (\$availableDrivers as \$d): ?>
                                    <?php
                                        \$empCode = !empty(\$d['employee_code']) ? " [{\$d['employee_code']}]" : '';
                                        \$displayName = !empty(\$d['first_name']) ? trim(\$d['first_name'] . ' ' . (\$d['last_name'] ?? '')) : \$d['name'];
                                        \$displayPhone = \$d['employee_phone'] ?? \$d['phone'] ?? '';
                                    ?>
                                    <option value="<?= (int)\$d['id'] ?>"
                                            data-name="<?= e(\$displayName) ?>"
                                            data-code="<?= e(\$d['employee_code'] ?? '') ?>"
                                            data-phone="<?= e(\$displayPhone) ?>"
                                            data-license="<?= e(\$d['license_number']) ?>">
                                        <?= e(\$displayName) ?><?= e(\$empCode) ?> · <?= e(\$displayPhone) ?> · Lic: <?= e(\$d['license_number']) ?>
                                    </option>
                                <?php endforeach; ?>
HTML;

$content = str_replace($oldDriverBlock, $newDriverBlock, $content);

// 3. Add notification preview banner after driver selection input wrapper
$driverWrapperEnd = <<<HTML
                    <div class="modern-input-wrapper">
                        <span class="modern-input-icon"><i class="fa-solid fa-id-badge"></i></span>
                        <select id="driver_id" name="driver_id" class="modern-select-pill" required onchange="onDriverChange(this)">
HTML;

$driverPreviewBox = <<<HTML
                    <!-- Live Employee Notification Preview Banner -->
                    <div id="driverNotificationPreview" class="trip-driver-notif-preview" style="display: none; margin-top: 0.85rem;">
                        <div class="notif-preview-box">
                            <div class="notif-preview-icon"><i class="fa-solid fa-paper-plane"></i></div>
                            <div class="notif-preview-text">
                                <span class="notif-preview-headline"><i class="fa-solid fa-bell"></i> Instant Duty Notification Dispatch</span>
                                <span id="driverNotifTarget" class="notif-preview-target">A professional assignment notification will be automatically sent to the selected employee upon deployment.</span>
                            </div>
                        </div>
                    </div>
HTML;

if (!str_contains($content, 'trip-driver-notif-preview')) {
    $targetDivEnd = "                    </div>\n                </div>\n            </div>\n\n            <!-- Bottom Actions -->";
    $replacementDiv = "                    </div>\n" . $driverPreviewBox . "\n                </div>\n            </div>\n\n            <!-- Bottom Actions -->";
    $content = str_replace($targetDivEnd, $replacementDiv, $content);
}

// 4. Add onDriverChange JS
$jsTarget = "function onBookingChange(sel) {";
$jsNew = <<<JS
function onDriverChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    const preview = document.getElementById('driverNotificationPreview');
    const target = document.getElementById('driverNotifTarget');
    if (!opt || !opt.value) {
        if (preview) preview.style.display = 'none';
        return;
    }
    const name = opt.getAttribute('data-name') || 'Chauffeur';
    const code = opt.getAttribute('data-code') || '';
    const phone = opt.getAttribute('data-phone') || '';

    let label = 'A professional duty assignment notification (In-App & SMS) will be automatically dispatched to <strong>' + name + '</strong>';
    if (code) label += ' (ID: <strong>' + code + '</strong>)';
    if (phone) label += ' at <strong>' + phone + '</strong>';
    label += ' upon deployment.';

    if (target) target.innerHTML = label;
    if (preview) preview.style.display = 'block';
}

function onBookingChange(sel) {
JS;

if (!str_contains($content, 'function onDriverChange')) {
    $content = str_replace($jsTarget, $jsNew, $content);
}

// 5. Add CSS for driver notification preview
$cssTarget = "/* Full-Width Workspace Form Wrapper & Master Card */";
$cssNew = <<<CSS
/* Instant Chauffeur Notification Preview */
.trip-driver-notif-preview {
    background: linear-gradient(135deg, rgba(23, 107, 91, 0.06), rgba(212, 175, 55, 0.08));
    border: 1px solid rgba(23, 107, 91, 0.25);
    border-radius: 14px;
    padding: 0.85rem 1.15rem;
    box-shadow: 0 2px 10px rgba(23, 107, 91, 0.04);
}
.notif-preview-box {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}
.notif-preview-icon {
    width: 34px;
    height: 34px;
    min-width: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, #176B5B, #0E483D);
    color: #FFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    box-shadow: 0 2px 6px rgba(23, 107, 91, 0.25);
}
.notif-preview-text {
    font-size: 0.825rem;
    color: #1E293B;
    line-height: 1.45;
}
.notif-preview-headline {
    font-weight: 700;
    color: #176B5B;
    display: block;
    margin-bottom: 2px;
}
.notif-preview-target strong {
    color: #0F172A;
}

/* Full-Width Workspace Form Wrapper & Master Card */
CSS;

if (!str_contains($content, '.trip-driver-notif-preview')) {
    $content = str_replace($cssTarget, $cssNew, $content);
}

file_put_contents($file, $content);
echo "Assign view patched successfully!\n";
