<?php
/**
 * Password Reset / Security Notice Email Template
 * Section 4 (Status Card), Section 5 (Security Credentials), Section 7 (Primary CTA), Section 8 (Security Advisory)
 */

$companyName = \App\Services\CompanySettingsService::getCompanyName();
$category = 'SECURITY NOTIFICATION';
$heading = 'Your Password Has Been Reset';
$subheading = 'Your ' . $companyName . ' account security credentials have been updated.';
$recipientName = $user['name'] ?? $user['username'] ?? 'User';
$greetingLead = 'Your account security credentials have been successfully updated.';

$username = $user['username'] ?? 'Account User';
$email = !empty($user['email']) ? $user['email'] : 'On Record';
$resetTime = format_datetime(date('Y-m-d H:i:s'));
$loginUrl = url('/login');
?>

<!-- SECTION 4: IMPORTANT STATUS / SUMMARY CARD -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; margin-bottom: 18px;">
    <tr>
        <td style="padding: 12px 16px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13.5px; font-weight: 700; color: #168653; font-family: Arial, Helvetica, sans-serif;">
                        <span style="font-size: 15px; margin-right: 6px;">🔒</span> Password Updated
                    </td>
                    <td align="right" style="font-size: 12.5px; font-weight: 700; color: #17233F; font-family: Arial, Helvetica, sans-serif;">
                        User: <?= e($username) ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- SECTION 5: MAIN INFORMATION CARD (SECURITY CREDENTIALS) -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F3F0FF; border: 1px solid #E2E8F0; border-radius: 10px; margin-bottom: 20px;">
    <tr>
        <td style="padding: 18px 20px;">
            <div style="font-size: 12px; font-weight: 700; color: #071F50; letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 12px; font-family: Arial, Helvetica, sans-serif;">
                SECURITY CREDENTIALS
            </div>

            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td width="38%" style="padding: 7px 0; font-size: 13px; color: #64748B; font-family: Arial, Helvetica, sans-serif;">Username</td>
                    <td style="padding: 7px 0; font-size: 14px; font-weight: 700; color: #17233F; font-family: Arial, Helvetica, sans-serif;"><?= e($username) ?></td>
                </tr>
                <tr>
                    <td width="38%" style="padding: 7px 0; font-size: 13px; color: #64748B; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;">Registered Email</td>
                    <td style="padding: 7px 0; font-size: 14px; font-weight: 600; color: #17233F; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;"><?= e($email) ?></td>
                </tr>
                <?php if (!empty($temporaryPassword)): ?>
                <tr>
                    <td width="38%" style="padding: 7px 0; font-size: 13px; color: #64748B; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;">Temporary Password</td>
                    <td style="padding: 7px 0; font-size: 14px; font-weight: 700; color: #5520D8; font-family: monospace; letter-spacing: 0.5px; border-top: 1px solid #EAE6FD;"><?= e($temporaryPassword) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td width="38%" style="padding: 7px 0; font-size: 13px; color: #64748B; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;">Updated At</td>
                    <td style="padding: 7px 0; font-size: 14px; font-weight: 600; color: #17233F; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;"><?= e($resetTime) ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- SECTION 7: PRIMARY ACTION BUTTON (Single Prominent Purple CTA) -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 20px;">
    <tr>
        <td align="center">
            <a href="<?= e($loginUrl) ?>" target="_blank" class="btn-purple cta-btn" style="display: inline-block; background-color: #5520D8; color: #FFFFFF; font-size: 14px; font-weight: 700; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-family: Arial, Helvetica, sans-serif;">
                Sign In to <?= e($companyName) ?> &rarr;
            </a>
        </td>
    </tr>
</table>

<!-- SECTION 8: ADDITIONAL INFORMATION NOTE CARD -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #FEF2F2; border: 1px solid #FECACA; border-radius: 8px;">
    <tr>
        <td style="padding: 12px 16px; font-size: 12.5px; color: #991B1B; line-height: 1.5; font-family: Arial, Helvetica, sans-serif;">
            <strong style="color: #DC2626;">Security Advisory:</strong> If you did not authorize this password reset, please contact your <?= e($companyName) ?> system administrator immediately to protect your account.
        </td>
    </tr>
</table>
