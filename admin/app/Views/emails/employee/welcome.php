<?php
/**
 * Employee Welcome / Onboarding Email Template
 * Dynamically styled with company website theme colors.
 */
$primary = $themeColors['primary'] ?? '#176B5B';
$secondary = $themeColors['secondary'] ?? '#16443B';
$companyName = $companyName ?? \App\Services\CompanySettingsService::getCompanyName();

$employeeCode = $employee['employee_code'] ?? ('EMP-' . str_pad((string)($employee['id'] ?? 1), 5, '0', STR_PAD_LEFT));
$fullName = trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''));
if (empty($fullName)) {
    $fullName = $employee['first_name'] ?? 'Team Member';
}
$department = ucwords(str_replace(['_', '-'], ' ', (string)($employee['department'] ?? 'Operations')));
$designation = !empty($employee['designation']) ? $employee['designation'] : 'Operations Officer';
$phone = !empty($employee['phone']) ? $employee['phone'] : 'On Record';
$email = !empty($employee['email']) ? $employee['email'] : 'On Record';
?>

<!-- STATUS BADGE -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; margin-bottom: 18px;">
    <tr>
        <td style="padding: 12px 18px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13.5px; font-weight: 700; color: #168653;">
                        ✓ Staff Profile Activated
                    </td>
                    <td align="right" style="font-size: 12.5px; font-weight: 700; color: <?= e($secondary) ?>;">
                        Code: <?= e($employeeCode) ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- STAFF PROFILE DETAILS CARD -->
<?php
$cardTitle = 'STAFF PROFILE DETAILS';
$cardRows = [
    ['label' => 'Staff Name', 'value' => $fullName],
    ['label' => 'Staff ID Code', 'value' => $employeeCode],
    ['label' => 'Department', 'value' => $department],
    ['label' => 'Designation', 'value' => $designation],
    ['label' => 'Official Email', 'value' => $email],
    ['label' => 'Mobile Phone', 'value' => $phone],
    ['label' => 'System Status', 'html' => '<span style="color: #168653; font-weight: 700;">Active Staff</span>'],
];
require __DIR__ . '/../components/info_card.php';
?>

<!-- CTA BUTTON -->
<?php
$btnText = 'Sign In to Operations Portal';
$btnUrl = url('/login');
$btnColor = $primary;
require __DIR__ . '/../components/button.php';
?>

<!-- CONFIDENTIALITY NOTE -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F7F8F4; border: 1px solid #E2E8F0; border-radius: 8px; margin-top: 15px;">
    <tr>
        <td style="padding: 14px 18px; font-size: 12.5px; color: #64748B; line-height: 1.5;">
            <strong style="color: <?= e($secondary) ?>;">Credential Security Notice:</strong>
            For security compliance, initial login passwords are never transmitted via unencrypted email. Please access the portal using your authorized company credentials.
        </td>
    </tr>
</table>
