<?php
/**
 * Customer Welcome Email Template
 * Dynamically styled with company website theme colors.
 */
$primary = $themeColors['primary'] ?? '#176B5B';
$secondary = $themeColors['secondary'] ?? '#16443B';
$companyName = $companyName ?? \App\Services\CompanySettingsService::getCompanyName();
$customerCode = $customer['customer_code'] ?? ('CUST-' . str_pad((string)($customer['id'] ?? 1), 5, '0', STR_PAD_LEFT));
$recipientName = $customer['name'] ?? 'Valued Customer';
$phone = !empty($customer['phone']) ? $customer['phone'] : 'On Record';
$email = !empty($customer['email']) ? $customer['email'] : 'On Record';
$city = !empty($customer['city']) ? $customer['city'] : 'India';
?>

<!-- STATUS BADGE -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; margin-bottom: 18px;">
    <tr>
        <td style="padding: 12px 18px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13.5px; font-weight: 700; color: #168653;">
                        ✓ Customer Profile Active
                    </td>
                    <td align="right" style="font-size: 12.5px; font-weight: 700; color: <?= e($secondary) ?>;">
                        ID: <?= e($customerCode) ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- ACCOUNT DETAILS CARD -->
<?php
$cardTitle = 'CUSTOMER PROFILE DETAILS';
$cardRows = [
    ['label' => 'Full Name', 'value' => $recipientName],
    ['label' => 'Customer Code', 'value' => $customerCode],
    ['label' => 'Registered Email', 'value' => $email],
    ['label' => 'Mobile Phone', 'value' => $phone],
    ['label' => 'City / Region', 'value' => $city],
    ['label' => 'Membership Status', 'html' => '<span style="color: #168653; font-weight: 700;">Verified Member</span>'],
];
require __DIR__ . '/../components/info_card.php';
?>

<!-- CTA BUTTON -->
<?php
$btnText = 'Explore Tours & Bookings';
$btnUrl = url('/');
$btnColor = $primary;
require __DIR__ . '/../components/button.php';
?>

<!-- PRIVILEGES NOTE -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F7F8F4; border: 1px solid #E2E8F0; border-radius: 8px; margin-top: 15px;">
    <tr>
        <td style="padding: 14px 18px; font-size: 12.5px; color: #64748B; line-height: 1.5;">
            <strong style="color: <?= e($secondary) ?>;"><?= e($companyName) ?> Quality Guarantee:</strong>
            Enjoy well-maintained fleet vehicles, certified chauffeurs, guaranteed on-time departures, transparent billing, and dedicated operational support.
        </td>
    </tr>
</table>
