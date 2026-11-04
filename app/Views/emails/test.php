<?php
/**
 * SMTP Diagnostic Verification Email Template
 */
$primary = $themeColors['primary'] ?? '#176B5B';
$secondary = $themeColors['secondary'] ?? '#16443B';
$companyName = $companyName ?? \App\Services\CompanySettingsService::getCompanyName();
$host = !empty($smtpHost) ? $smtpHost : 'smtp.gmail.com';
$port = !empty($smtpPort) ? $smtpPort : '465';
$fromSender = !empty($sender) ? $sender : (company_email() ?: 'notifications@example.com');
$targetRecipient = !empty($recipientEmail) ? $recipientEmail : (company_email() ?: 'admin@example.com');
$timestamp = format_datetime(date('Y-m-d H:i:s'));
?>

<!-- STATUS BADGE -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; margin-bottom: 18px;">
    <tr>
        <td style="padding: 12px 18px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13.5px; font-weight: 700; color: #168653;">
                        ✓ SMTP Subsystem Operational
                    </td>
                    <td align="right" style="font-size: 12.5px; font-weight: 700; color: <?= e($secondary) ?>;">
                        <?= e($host) ?>:<?= e($port) ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- DIAGNOSTIC TELEMETRY CARD -->
<?php
$cardTitle = 'DIAGNOSTIC TELEMETRY';
$cardRows = [
    ['label' => 'SMTP Host', 'value' => $host],
    ['label' => 'Port & Encryption', 'value' => "{$port} (SSL/TLS Secure)"],
    ['label' => 'Authenticated Sender', 'value' => $fromSender],
    ['label' => 'Target Recipient', 'value' => $targetRecipient],
    ['label' => 'Verification Timestamp', 'value' => $timestamp],
    ['label' => 'Delivery Engine', 'html' => '<span style="color: #168653; font-weight: 700;">Direct Transactional SMTP (No Worker Needed)</span>'],
];
require __DIR__ . '/components/info_card.php';
?>

<!-- CTA BUTTON -->
<?php
$btnText = 'Return to Email Management';
$btnUrl = url('/office/emails');
$btnColor = $primary;
require __DIR__ . '/components/button.php';
?>
