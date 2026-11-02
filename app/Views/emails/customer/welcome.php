<?php
/**
 * Customer Welcome Email Template
 * Section 4 (Status Card), Section 5 (Account Information), Section 7 (Primary CTA), Section 8 (Privileges Note)
 */

$companyName = \App\Services\CompanySettingsService::getCompanyName();
$category = 'MEMBERSHIP WELCOME';
$heading = 'Welcome to ' . $companyName . '!';
$subheading = 'Your customer membership account is active. Luxury journeys await.';
$recipientName = $customer['name'] ?? 'Valued Guest';
$greetingLead = 'Thank you for choosing ' . $companyName . '. Your customer membership account has been successfully created.';

$customerCode = $customer['customer_code'] ?? ('CUST-' . str_pad((string)($customer['id'] ?? 1), 5, '0', STR_PAD_LEFT));
$phone = !empty($customer['phone']) ? $customer['phone'] : 'On Record';
$email = !empty($customer['email']) ? $customer['email'] : 'On Record';
$city = !empty($customer['city']) ? $customer['city'] : 'India';
$exploreUrl = url('/');
?>

<!-- SECTION 4: IMPORTANT STATUS / SUMMARY CARD -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; margin-bottom: 18px;">
    <tr>
        <td style="padding: 12px 16px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="left" style="font-size: 13.5px; font-weight: 700; color: #168653; font-family: Arial, Helvetica, sans-serif;">
                        <span style="font-size: 15px; margin-right: 6px;">✓</span> Account Active
                    </td>
                    <td align="right" style="font-size: 12.5px; font-weight: 700; color: #17233F; font-family: Arial, Helvetica, sans-serif;">
                        Code: <?= e($customerCode) ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- SECTION 5: MAIN INFORMATION CARD (ACCOUNT INFORMATION) -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F3F0FF; border: 1px solid #E2E8F0; border-radius: 10px; margin-bottom: 20px;">
    <tr>
        <td style="padding: 18px 20px;">
            <div style="font-size: 12px; font-weight: 700; color: #071F50; letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 12px; font-family: Arial, Helvetica, sans-serif;">
                ACCOUNT INFORMATION
            </div>

            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td width="38%" style="padding: 7px 0; font-size: 13px; color: #64748B; font-family: Arial, Helvetica, sans-serif;">Member Name</td>
                    <td style="padding: 7px 0; font-size: 14px; font-weight: 700; color: #17233F; font-family: Arial, Helvetica, sans-serif;"><?= e($recipientName) ?></td>
                </tr>
                <tr>
                    <td style="padding: 7px 0; font-size: 13px; color: #64748B; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;">Customer Code</td>
                    <td style="padding: 7px 0; font-size: 14px; font-weight: 700; color: #5520D8; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;"><?= e($customerCode) ?></td>
                </tr>
                <tr>
                    <td style="padding: 7px 0; font-size: 13px; color: #64748B; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;">Registered Email</td>
                    <td style="padding: 7px 0; font-size: 14px; font-weight: 600; color: #17233F; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;"><?= e($email) ?></td>
                </tr>
                <tr>
                    <td style="padding: 7px 0; font-size: 13px; color: #64748B; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;">Contact Mobile</td>
                    <td style="padding: 7px 0; font-size: 14px; font-weight: 600; color: #17233F; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;"><?= e($phone) ?></td>
                </tr>
                <tr>
                    <td style="padding: 7px 0; font-size: 13px; color: #64748B; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;">Primary City</td>
                    <td style="padding: 7px 0; font-size: 14px; font-weight: 600; color: #17233F; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;"><?= e($city) ?></td>
                </tr>
                <tr>
                    <td style="padding: 7px 0; font-size: 13px; color: #64748B; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;">Membership Tier</td>
                    <td style="padding: 7px 0; font-size: 14px; font-weight: 600; color: #168653; border-top: 1px solid #EAE6FD; font-family: Arial, Helvetica, sans-serif;">Executive Club Member</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- SECTION 7: PRIMARY ACTION BUTTON (Single Prominent Purple CTA) -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 20px;">
    <tr>
        <td align="center">
            <a href="<?= e($exploreUrl) ?>" target="_blank" class="btn-purple cta-btn" style="display: inline-block; background-color: #5520D8; color: #FFFFFF; font-size: 14px; font-weight: 700; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-family: Arial, Helvetica, sans-serif;">
                Explore Travel Destinations &rarr;
            </a>
        </td>
    </tr>
</table>

<!-- SECTION 8: ADDITIONAL INFORMATION NOTE CARD -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px;">
    <tr>
        <td style="padding: 12px 16px; font-size: 12.5px; color: #64748B; line-height: 1.5; font-family: Arial, Helvetica, sans-serif;">
            <strong style="color: #17233F;"><?= e($companyName) ?> Privileges:</strong> Enjoy pristine verified luxury vehicles, top-tier vetted chauffeurs, guaranteed on-time pickup, and 24/7 concierge assistance for all your corporate and holiday travel.
        </td>
    </tr>
</table>
