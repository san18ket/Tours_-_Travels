<?php
/**
 * Employee Welcome / Onboarding Email Template
 * Section 4 (Status Card), Section 5 (Staff Profile Details), Section 7 (Primary CTA), Section 8 (Security Advisory)
 */

$companyName = \App\Services\CompanySettingsService::getCompanyName();
$category = 'STAFF ONBOARDING';
$heading = 'Welcome to the ' . $companyName . ' Operations Team!';
$subheading = 'Your staff profile and operations credentials have been created.';
$recipientName = $employee['first_name'] ?? 'Team Member';
$greetingLead = 'Your employee profile has been officially created in our operational management platform.';

$employeeCode = $employee['employee_code'] ?? ('EMP-' . str_pad((string)($employee['id'] ?? 1), 5, '0', STR_PAD_LEFT));
$fullName = trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''));
if (empty($fullName)) {
    $fullName = $recipientName;
}
$department = ucwords(str_replace(['_', '-'], ' ', (string)($employee['department'] ?? 'Operations')));
$designation = !empty($employee['designation']) ? $employee['designation'] : 'Operations Officer';
$phone = !empty($employee['phone']) ? $employee['phone'] : 'On Record';
$email = !empty($employee['email']) ? $employee['email'] : 'On Record';
$portalUrl = url('/login');
?>

<!-- SECTION 4: IMPORTANT STATUS / SUMMARY CARD -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; margin-bottom: 18px;">
    <tr>
        <td style="padding: 12px 16px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13.5px; font-weight: 700; color: #168653; font-family: Arial, Helvetica, sans-serif;">
                        <span style="font-size: 15px; margin-right: 6px;">✓</span> Staff Profile Active
                    </td>
                    <td align="right" style="font-size: 12.5px; font-weight: 700; color: #17233F; font-family: Arial, Helvetica, sans-serif;">
                        Code: <?= e($employeeCode) ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- SECTION 5: MAIN INFORMATION CARD (STAFF PROFILE DETAILS) -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F3F0FF; border: 1px solid #E2E8F0; border-radius: 10px; margin-bottom: 20px;">
    <tr>
        <td style="padding: 18px 20px;">
            <div style="font-size: 12px; font-weight: 700; color: #071F50; letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 12px; font-family: Arial, Helvetica, sans-serif;">
                STAFF PROFILE DETAILS
            </div>

            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td width="38%" style="padding: 7px 0; font-size: 13px; color: #64748B; font-family: Arial, Helvetica, sans-serif;">Full Name</td>
                    <td style="padding: 7px 0; font-size: 14px; font-weight: 700; color: #17233F; font-family: Arial, Helvetica, sans-serif;"><?= e($fullName) ?></td>
                </tr>
                <tr>
                    <td width="38%" style="padding: 7px 0; font-size: 13px; color: #64748B; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;">Staff Code</td>
                    <td style="padding: 7px 0; font-size: 14px; font-weight: 700; color: #5520D8; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;"><?= e($employeeCode) ?></td>
                </tr>
                <tr>
                    <td style="padding: 7px 0; font-size: 13px; color: #64748B; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;">Department</td>
                    <td style="padding: 7px 0; font-size: 14px; font-weight: 600; color: #17233F; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;"><?= e($department) ?></td>
                </tr>
                <tr>
                    <td style="padding: 7px 0; font-size: 13px; color: #64748B; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;">Designation</td>
                    <td style="padding: 7px 0; font-size: 14px; font-weight: 600; color: #17233F; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;"><?= e($designation) ?></td>
                </tr>
                <tr>
                    <td style="padding: 7px 0; font-size: 13px; color: #64748B; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;">Official Email</td>
                    <td style="padding: 7px 0; font-size: 14px; font-weight: 600; color: #17233F; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;"><?= e($email) ?></td>
                </tr>
                <tr>
                    <td style="padding: 7px 0; font-size: 13px; color: #64748B; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;">Contact Mobile</td>
                    <td style="padding: 7px 0; font-size: 14px; font-weight: 600; color: #17233F; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;"><?= e($phone) ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- SECTION 7: PRIMARY ACTION BUTTON (Single Prominent Purple CTA) -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 20px;">
    <tr>
        <td align="center">
            <a href="<?= e($portalUrl) ?>" target="_blank" class="btn-purple cta-btn" style="display: inline-block; background-color: #5520D8; color: #FFFFFF; font-size: 14px; font-weight: 700; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-family: Arial, Helvetica, sans-serif;">
                Sign In to Operations Portal &rarr;
            </a>
        </td>
    </tr>
</table>

<!-- SECTION 8: ADDITIONAL INFORMATION NOTE CARD -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px;">
    <tr>
        <td style="padding: 12px 16px; font-size: 12.5px; color: #64748B; line-height: 1.5; font-family: Arial, Helvetica, sans-serif;">
            <strong style="color: #17233F;">Credential Confidentiality:</strong> For your security, account passwords are never sent over email. Please log in using your authorized company credentials or contact the systems administrator if you need your access password provisioned.
        </td>
    </tr>
</table>
