<?php
/**
 * Tours & Travels — Professional Username Recovery Email Template
 * Sent PRIVATELY to the registered email after successful OTP verification.
 * The username is NEVER rendered or returned on the webpage.
 */
$primary = $themeColors['primary'] ?? '#176B5B';
$secondary = $themeColors['secondary'] ?? '#16443B';
$accent = $themeColors['accent'] ?? '#D6B77A';
$companyName = $companyName ?? \App\Services\CompanySettingsService::getCompanyName();
$greetingName = $recipientName ?? 'Valued Customer';
?>

<!-- SECTION: RECOVERED USERNAME CALLOUT -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F7F8F4; border: 1.5px solid #E2E8F0; border-radius: 12px; margin-bottom: 24px; text-align: center;">
    <tr>
        <td style="padding: 26px 20px;">
            <div style="font-size: 12px; font-weight: 700; color: #64748B; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 12px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                VERIFIED ACCOUNT USERNAME
            </div>

            <!-- Username Box -->
            <div style="display: inline-block; background: #FFFFFF; border: 2.5px solid <?= e($primary) ?>; border-radius: 10px; padding: 14px 28px; margin-bottom: 14px; box-shadow: 0 4px 14px rgba(23, 107, 91, 0.12);">
                <span style="font-family: 'Courier New', Courier, monospace, sans-serif; font-size: 28px; font-weight: 800; color: <?= e($secondary) ?>; letter-spacing: 1px; line-height: 1;">
                    <?= htmlspecialchars($username ?? '', ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

            <div style="font-size: 13.5px; color: #475569; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-weight: 500;">
                You can now sign in to your <?= e($companyName) ?> account using this verified username.
            </div>
        </td>
    </tr>
</table>

<!-- SECTION: GREETING & DETAILS -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 10px; margin-bottom: 20px;">
    <tr>
        <td style="padding: 18px 20px; font-size: 14px; line-height: 1.6; color: #20352F;">
            <p style="margin: 0 0 12px 0;">
                Hello <strong><?= htmlspecialchars($greetingName, ENT_QUOTES, 'UTF-8') ?></strong>,
            </p>
            <p style="margin: 0 0 12px 0;">
                We received and verified a request to recover your account username for <strong><?= e($companyName) ?></strong>.
            </p>
            <p style="margin: 0;">
                Please store this username in a safe place. For your privacy, your username was delivered only to this verified email address and was not revealed on the recovery website.
            </p>
        </td>
    </tr>
</table>

<!-- SECTION: SECURITY NOTICE -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #FEF3C7; border: 1px solid #FDE68A; border-radius: 8px; margin-bottom: 20px;">
    <tr>
        <td style="padding: 14px 18px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td width="28" valign="top" style="font-size: 18px; line-height: 1; padding-right: 10px;">🛡️</td>
                    <td style="font-size: 13px; color: #92400E; line-height: 1.45;">
                        <strong>Security Reminder:</strong> Never share your password, login credentials, or verification codes with anyone. If you did not initiate this username recovery request, please contact our support team immediately.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Action Button -->
<?php
$btnText = 'Sign In to Your Account';
$btnUrl = url('/login');
require __DIR__ . '/../components/button.php';
?>

