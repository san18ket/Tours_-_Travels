<?php
use App\Services\CompanySettingsService;

$themeColors = $themeColors ?? CompanySettingsService::getThemeColors();
$primary = $themeColors['primary'] ?? '#176B5B';
$secondary = $themeColors['secondary'] ?? '#16443B';
$accent = $themeColors['accent'] ?? '#D6B77A';

$companyName = $companyName ?? CompanySettingsService::getCompanyName();
$companyTagline = $companyTagline ?? company_tagline();
$companyLogoUrl = $companyLogoUrl ?? CompanySettingsService::getLogoUrl(true);
$companyEmail = $companyEmail ?? CompanySettingsService::getCompanyEmail();
$companyPhone = $companyPhone ?? CompanySettingsService::getPhone();
$companyWebsite = $companyWebsite ?? CompanySettingsService::getWebsite();
$companyAddress = $companyAddress ?? CompanySettingsService::getAddress();

$categoryLabel = $categoryLabel ?? ($category ?? 'OPERATIONS UPDATE');
$headingText = $headingText ?? ($heading ?? ($subject ?? 'Notification from ' . $companyName));
$subheadingText = $subheadingText ?? ($subheading ?? 'Official notification and operational details.');
$previewText = $previewText ?? ($subject ?? $headingText);
?>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= e($subject ?? ($headingText . ' — ' . $companyName)) ?></title>
    <!--[if mso]>
    <style type="text/css">
        table { border-collapse: collapse; }
        body, table, td, a, span { font-family: Arial, Helvetica, sans-serif !important; }
    </style>
    <![endif]-->
    <style type="text/css">
        /* Client Resets */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        body { margin: 0; padding: 0; width: 100% !important; min-width: 100%; background-color: #F7F8F4; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #20352F; }

        /* Mobile-First Responsive Breakpoints */
        @media only screen and (max-width: 600px) {
            .email-container { width: 100% !important; max-width: 100% !important; border-radius: 0 !important; border-left: none !important; border-right: none !important; }
            .fluid-cell { padding-left: 18px !important; padding-right: 18px !important; }
            .hero-cell { padding: 22px 18px !important; }
            .hero-title { font-size: 21px !important; line-height: 1.3 !important; }
            .hero-subtitle { font-size: 13px !important; line-height: 1.4 !important; }
            .content-cell { padding: 20px 18px !important; }
            .info-card { padding: 14px !important; margin: 14px 0 !important; }
            .info-col { display: block !important; width: 100% !important; max-width: 100% !important; box-sizing: border-box !important; text-align: left !important; }
            .info-val { text-align: left !important; padding-top: 2px !important; }
            .cta-btn { display: block !important; width: 100% !important; box-sizing: border-box !important; text-align: center !important; padding: 14px 18px !important; font-size: 15px !important; }
            .footer-cell { padding: 22px 18px !important; }
            .hide-on-mobile { display: none !important; }
            .mobile-full { width: 100% !important; }
        }
        @media only screen and (max-width: 400px) {
            .fluid-cell { padding-left: 14px !important; padding-right: 14px !important; }
            .hero-cell { padding: 18px 14px !important; }
            .hero-title { font-size: 19px !important; }
            .content-cell { padding: 16px 14px !important; }
            .cta-btn { font-size: 14px !important; padding: 12px 14px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #F7F8F4; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #20352F;">
    <!-- Hidden inbox preview text -->
    <div style="display: none; font-size: 1px; color: #F7F8F4; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all;">
        <?= e($previewText) ?>
    </div>

    <!-- Outer Canvas Wrapper -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F7F8F4; table-layout: fixed;">
        <tr>
            <td align="center" style="padding: 24px 10px 36px 10px;">
                <!-- Main Email Card (Max 600px desktop, 100% mobile) -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="email-container" style="max-width: 600px; background-color: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(22, 68, 59, 0.08); border: 1px solid #E2E8F0;">

                    <!-- Dynamic Branded Header Component -->
                    <?php require __DIR__ . '/../components/header.php'; ?>

                    <!-- MAIN CONTENT AREA -->
                    <tr>
                        <td style="background-color: #FFFFFF; padding: 26px 28px;" class="content-cell">
                            <?= $content ?>
                        </td>
                    </tr>

                    <!-- Dynamic Branded Footer Component -->
                    <?php require __DIR__ . '/../components/footer.php'; ?>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
