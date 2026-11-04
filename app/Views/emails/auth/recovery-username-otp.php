<?php
/**
 * Tours & Travels — Professional Username Recovery OTP Template
 * Rendered inside master email layout with dynamic company header and footer.
 */

$companyName = \App\Services\CompanySettingsService::getCompanyName();
$expiryMinutes = 5;
?>

<!-- SECTION: OTP HIGHLIGHT BOX -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 12px; margin-bottom: 24px; text-align: center;">
    <tr>
        <td style="padding: 28px 20px;">
            <div style="font-size: 12px; font-weight: 700; color: #64748B; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 12px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                USERNAME RECOVERY CODE
            </div>

            <!-- 4-Digit Prominent OTP Display -->
            <div style="display: inline-block; background: #FFFFFF; border: 2px solid #2563EB; border-radius: 10px; padding: 14px 28px; margin-bottom: 14px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.08);">
                <span style="font-family: 'Courier New', Courier, monospace, sans-serif; font-size: 38px; font-weight: 800; color: #1D4ED8; letter-spacing: 14px; margin-right: -14px; line-height: 1;">
                    <?= htmlspecialchars($otp ?? '----', ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

            <div style="font-size: 13.5px; color: #475569; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-weight: 500;">
                Valid for <strong style="color: #0F172A;"><?= $expiryMinutes ?> minutes</strong>. Enter this 4-digit code to recover your account username.
            </div>
        </td>
    </tr>
</table>

<!-- SECTION: PURPOSE & RECIPIENT DETAILS -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 10px; margin-bottom: 20px;">
    <tr>
        <td style="padding: 16px 20px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td width="35%" style="padding: 6px 0; font-size: 13px; color: #64748B; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">Request Type</td>
                    <td style="padding: 6px 0; font-size: 13.5px; font-weight: 600; color: #0F172A; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">Forgot Username Recovery</td>
                </tr>
                <tr>
                    <td width="35%" style="padding: 6px 0; font-size: 13px; color: #64748B; border-top: 1px solid #F1F5F9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">Registered Email</td>
                    <td style="padding: 6px 0; font-size: 13.5px; font-weight: 600; color: #0F172A; border-top: 1px solid #F1F5F9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;"><?= htmlspecialchars($recipientEmail ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- SECTION: SECURITY NOTICE -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #FEF3C7; border: 1px solid #FDE68A; border-radius: 8px; margin-bottom: 20px;">
    <tr>
        <td style="padding: 14px 18px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td width="28" valign="top" style="font-size: 18px; line-height: 1; padding-right: 10px;">🔒</td>
                    <td style="font-size: 13px; color: #92400E; line-height: 1.45; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                        <strong>Security Warning:</strong> Never share this code with anyone. <?= htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') ?> staff or administrators will never ask you for your verification code.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- SECTION: HELP / DISCARD NOTICE -->
<p style="font-size: 12.5px; color: #94A3B8; margin: 0; line-height: 1.5; text-align: center; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    If you did not request username recovery, please disregard this email. Your account remains secure and this code will expire automatically in <?= $expiryMinutes ?> minutes.
</p>

