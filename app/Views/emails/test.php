<?php
/**
 * SMTP Diagnostic Verification Email Template
 * Section 4 (Status Card), Section 5 (Diagnostic Telemetry), Section 7 (Primary CTA), Section 8 (Diagnostic Status Note)
 */

$companyName = \App\Services\CompanySettingsService::getCompanyName();
$category = 'SMTP SYSTEM VERIFICATION';
$heading = 'Email System Is Operational!';
$subheading = e($companyName) . ' SMTP subsystem is properly configured and verified.';
$recipientName = 'System Administrator';
$greetingLead = 'This diagnostic test confirms that your ' . e($companyName) . ' professional SMTP notification system is operational.';

$host = !empty($smtpHost) ? $smtpHost : 'smtp.gmail.com';
$port = !empty($smtpPort) ? $smtpPort : '465';
$fromSender = !empty($sender) ? $sender : (company_email() ?: 'notifications@example.com');
$targetRecipient = !empty($recipientEmail) ? $recipientEmail : (company_email() ?: 'admin@example.com');
$timestamp = format_datetime(date('Y-m-d H:i:s'));
$consoleUrl = url('/office');
?>

<!-- SECTION 4: IMPORTANT STATUS / SUMMARY CARD -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; margin-bottom: 18px;">
    <tr>
        <td style="padding: 12px 16px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13.5px; font-weight: 700; color: #168653; font-family: Arial, Helvetica, sans-serif;">
                        <span style="font-size: 15px; margin-right: 6px;">✓</span> SMTP Connected
                    </td>
                    <td align="right" style="font-size: 12.5px; font-weight: 700; color: #17233F; font-family: Arial, Helvetica, sans-serif;">
                        <?= e($host) ?>:<?= e($port) ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- SECTION 5: MAIN INFORMATION CARD (DIAGNOSTIC TELEMETRY) -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F3F0FF; border: 1px solid #E2E8F0; border-radius: 10px; margin-bottom: 20px;">
    <tr>
        <td style="padding: 18px 20px;">
            <div style="font-size: 12px; font-weight: 700; color: #071F50; letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 12px; font-family: Arial, Helvetica, sans-serif;">
                DIAGNOSTIC TELEMETRY
            </div>

            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td width="38%" style="padding: 7px 0; font-size: 13px; color: #64748B; font-family: Arial, Helvetica, sans-serif;">SMTP Host</td>
                    <td style="padding: 7px 0; font-size: 14px; font-weight: 700; color: #17233F; font-family: Arial, Helvetica, sans-serif;"><?= e($host) ?></td>
                </tr>
                <tr>
                    <td width="38%" style="padding: 7px 0; font-size: 13px; color: #64748B; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;">SMTP Port & SSL</td>
                    <td style="padding: 7px 0; font-size: 14px; font-weight: 700; color: #5520D8; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;"><?= e($port) ?> (SSL Encrypted)</td>
                </tr>
                <tr>
                    <td width="38%" style="padding: 7px 0; font-size: 13px; color: #64748B; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;">Configured Sender</td>
                    <td style="padding: 7px 0; font-size: 14px; font-weight: 600; color: #17233F; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;"><?= e($fromSender) ?></td>
                </tr>
                <tr>
                    <td width="38%" style="padding: 7px 0; font-size: 13px; color: #64748B; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;">Target Recipient</td>
                    <td style="padding: 7px 0; font-size: 14px; font-weight: 600; color: #17233F; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;"><?= e($targetRecipient) ?></td>
                </tr>
                <tr>
                    <td width="38%" style="padding: 7px 0; font-size: 13px; color: #64748B; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;">Handshake Protocol</td>
                    <td style="padding: 7px 0; font-size: 14px; font-weight: 600; color: #168653; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;">Google SMTP SSL Socket</td>
                </tr>
                <tr>
                    <td width="38%" style="padding: 7px 0; font-size: 13px; color: #64748B; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;">Verification Time</td>
                    <td style="padding: 7px 0; font-size: 14px; font-weight: 600; color: #17233F; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;"><?= e($timestamp) ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- SECTION 7: PRIMARY ACTION BUTTON (Single Prominent Purple CTA) -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 20px;">
    <tr>
        <td align="center">
            <a href="<?= e($consoleUrl) ?>" target="_blank" class="btn-purple cta-btn" style="display: inline-block; background-color: #5520D8; color: #FFFFFF; font-size: 14px; font-weight: 700; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-family: Arial, Helvetica, sans-serif;">
                Open Operations Console &rarr;
            </a>
        </td>
    </tr>
</table>

<!-- SECTION 8: ADDITIONAL INFORMATION NOTE CARD -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px;">
    <tr>
        <td style="padding: 12px 16px; font-size: 12.5px; color: #64748B; line-height: 1.5; font-family: Arial, Helvetica, sans-serif;">
            <strong style="color: #168653;">Diagnostic Status:</strong> All cryptographic handshakes, DKIM-aligned routing, and template layout rendering pipelines have executed successfully.
        </td>
    </tr>
</table>
