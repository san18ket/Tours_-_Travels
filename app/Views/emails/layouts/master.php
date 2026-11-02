<?php
use App\Services\CompanySettingsService;
use App\Services\EmailIconService;

$companyName = CompanySettingsService::getCompanyName();
$companyTagline = company_tagline();
$companyLogoUrl = CompanySettingsService::getLogoUrl(true);
$companyEmail = CompanySettingsService::getCompanyEmail();
$companyPhone = CompanySettingsService::getPhone();
$websiteUrl = CompanySettingsService::getWebsite();
$companyAddress = CompanySettingsService::getAddress();

$categoryLabel = $categoryLabel ?? ($category ?? 'OPERATIONS UPDATE');
$headingText = $headingText ?? ($heading ?? ($subject ?? 'Notification from ' . $companyName));
$subheadingText = $subheadingText ?? ($subheading ?? 'Official operational notification and itinerary details.');
$greetingName = $greetingName ?? ($recipientName ?? ($customer['name'] ?? ($employee['first_name'] ?? ($user['name'] ?? ($driver['name'] ?? 'Valued Guest')))));
$leadText = $leadText ?? ($greetingLead ?? 'Thank you for choosing ' . $companyName . '. Here are the details of your service.');
$supportEmail = $companyEmail;
$supportPhone = $companyPhone;
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
        /* Client-specific Resets */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        body { margin: 0; padding: 0; width: 100% !important; min-width: 100%; background-color: #F5F7FB; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #17233F; }

        /* Design Token Declarations:
           Primary Navy: #071F50
           Secondary Navy: #12366B
           Primary Purple: #5520D8
           Accent Purple: #7655F5
           Light Purple: #F3F0FF
           Main Background: #F5F7FB
           White: #FFFFFF
           Main Text: #17233F
           Secondary Text: #64748B
           Border: #E2E8F0
           Status Success: #168653
        */

        /* Responsive Mobile Layout & Auto-Arranging Fluid Typography */
        @media only screen and (max-width: 600px) {
            .email-container { width: 100% !important; max-width: 100% !important; border-radius: 0 !important; }
            .fluid-cell { padding-left: 16px !important; padding-right: 16px !important; }
            .hero-cell { padding: 24px 16px !important; }
            .hero-title { font-size: 20px !important; line-height: 1.3 !important; }
            .hero-subtitle { font-size: 13px !important; line-height: 1.4 !important; }
            .greeting-cell { padding: 18px 16px !important; }
            .greeting-title { font-size: 17px !important; }
            .greeting-lead { font-size: 13px !important; }
            .content-cell { padding: 20px 16px !important; }
            .info-card { padding: 14px 14px !important; }
            .info-col { display: block !important; width: 100% !important; max-width: 100% !important; box-sizing: border-box !important; }
            .info-item { padding: 6px 0 !important; }
            .info-lbl { font-size: 11.5px !important; }
            .info-val { font-size: 13px !important; }
            .priv-grid td { display: block !important; width: 100% !important; box-sizing: border-box !important; padding: 6px 0 !important; }
            .priv-col { display: block !important; width: 100% !important; max-width: 100% !important; box-sizing: border-box !important; text-align: left !important; }
            .support-grid td { display: block !important; width: 100% !important; box-sizing: border-box !important; text-align: left !important; }
            .support-contacts { padding-top: 10px !important; border-top: 1px dashed #E2E8F0 !important; margin-top: 10px !important; text-align: left !important; }
            .cta-btn { display: block !important; width: 100% !important; box-sizing: border-box !important; text-align: center !important; padding: 14px 16px !important; font-size: 15px !important; }
            .footer-cell { padding: 24px 16px !important; }
            .hide-on-mobile { display: none !important; }
            .mobile-full { width: 100% !important; }
        }
        @media only screen and (max-width: 420px) {
            .fluid-cell { padding-left: 12px !important; padding-right: 12px !important; }
            .hero-cell { padding: 20px 12px !important; }
            .hero-title { font-size: 18px !important; }
            .hero-subtitle { font-size: 12.5px !important; }
            .greeting-title { font-size: 16px !important; }
            .content-cell { padding: 16px 12px !important; }
            .cta-btn { font-size: 14px !important; padding: 12px 14px !important; }
            .badge-chip { font-size: 10.5px !important; padding: 3px 8px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #F5F7FB; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #17233F;">
    <!-- Preheader preview text (hidden from inbox display) -->
    <div style="display: none; font-size: 1px; color: #F5F7FB; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all;">
        <?= e($previewText ?? ($headingText . ' — ' . $companyName)) ?>
    </div>

    <!-- Outer Wrapper Canvas -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F5F7FB; table-layout: fixed;">
        <tr>
            <td align="center" style="padding: 24px 12px 36px 12px;">
                <!-- Main Container (600px Max Desktop, 100% Mobile) -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="email-container" style="max-width: 600px; background-color: #FFFFFF; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 24px rgba(7, 31, 80, 0.08); border: 1px solid #E2E8F0;">

                    <!-- SECTION 1: BRAND HEADER (White background, subtle bottom border, NO distracting navigation menus) -->
                    <tr>
                        <td style="background-color: #FFFFFF; padding: 20px 28px; border-bottom: 1px solid #E2E8F0;" class="fluid-cell">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="left" style="vertical-align: middle;">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="vertical-align: middle; padding-right: 12px;">
                                                    <?php if (!empty($companyLogoUrl)): ?>
                                                        <img src="<?= e($companyLogoUrl) ?>" alt="<?= e($companyName) ?>" style="max-height: 38px; max-width: 140px; height: auto; display: block; object-fit: contain; border-radius: 6px;">
                                                    <?php else: ?>
                                                        <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #5520D8 0%, #7655F5 100%); text-align: center; line-height: 38px; box-shadow: 0 3px 8px rgba(85, 32, 216, 0.25);">
                                                            <?= EmailIconService::render('airplane-fill', 19, '#FFFFFF', '', 'margin-top: 9px;') ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="vertical-align: middle;">
                                                    <div style="font-size: 20px; font-weight: 800; color: #071F50; letter-spacing: -0.4px; line-height: 1.1;">
                                                        <?= e($companyName) ?>
                                                    </div>
                                                    <?php if (!empty($companyTagline)): ?>
                                                        <div style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.8px; margin-top: 2px;">
                                                            <span style="color: #5520D8;"><?= e($companyTagline) ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td align="right" style="vertical-align: middle;" class="hide-on-mobile">
                                        <div style="display: inline-block; background-color: #F3F0FF; border: 1px solid #E2E8F0; border-radius: 20px; padding: 5px 12px; font-size: 11.5px; font-weight: 600; color: #5520D8;">
                                            <?= EmailIconService::render('calendar-event', 13, '#5520D8', '', 'margin-right: 4px; vertical-align: -1px;') ?>
                                            <?= date('d M Y') ?>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- SECTION 2: HERO (Deep Executive Navy Gradient, Category Badge, Title, Subtitle) -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #071F50 0%, #12366B 100%); background-color: #071F50; padding: 28px 28px 24px 28px;" class="hero-cell">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td>
                                        <div class="badge-chip" style="display: inline-block; background-color: rgba(118, 85, 245, 0.22); border: 1px solid rgba(165, 180, 252, 0.35); border-radius: 16px; padding: 4px 12px; font-size: 11px; font-weight: 700; color: #C7D2FE; letter-spacing: 0.8px; text-transform: uppercase;">
                                            <?= EmailIconService::render('stars', 12, '#C7D2FE', '', 'margin-right: 4px; vertical-align: -1px;') ?>
                                            <?= e($categoryLabel) ?>
                                        </div>
                                        <h1 class="hero-title" style="margin: 12px 0 6px 0; font-size: 24px; font-weight: 800; color: #FFFFFF; line-height: 1.25; letter-spacing: -0.3px;">
                                            <?= e($headingText) ?>
                                        </h1>
                                        <p class="hero-subtitle" style="margin: 0; font-size: 14px; color: #CBD5E1; line-height: 1.5; font-weight: 400;">
                                            <?= e($subheadingText) ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- SECTION 3: PERSONALIZED GREETING (Hello, [Name]! + concise lead sentence) -->
                    <tr>
                        <td style="padding: 20px 28px 16px 28px; background-color: #FFFFFF; border-bottom: 1px solid #F1F5F9;" class="greeting-cell">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td width="42" style="vertical-align: top; padding-right: 12px;">
                                        <div style="width: 36px; height: 36px; border-radius: 50%; background-color: #F3F0FF; border: 1px solid #E2E8F0; text-align: center; line-height: 36px;">
                                            <?= EmailIconService::render('person-fill', 18, '#5520D8', '', 'margin-top: 8px;') ?>
                                        </div>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div class="greeting-title" style="font-size: 18px; font-weight: 700; color: #071F50; line-height: 1.2;">
                                            Hello, <?= e($greetingName) ?>!
                                        </div>
                                        <div class="greeting-lead" style="font-size: 13.5px; color: #64748B; line-height: 1.45; margin-top: 4px;">
                                            <?= e($leadText) ?>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- MAIN DYNAMIC CONTENT (Sections 4-8) -->
                    <tr>
                        <td style="padding: 22px 28px 18px 28px; background-color: #FFFFFF;" class="content-cell">
                            <?= $content ?? '' ?>
                        </td>
                    </tr>

                    <!-- ENTERPRISE PRIVILEGES & VALUE PROPS (Responsive 4-column stackable grid with Bootstrap Icons) -->
                    <tr>
                        <td style="padding: 0 28px 20px 28px; background-color: #FFFFFF;" class="fluid-cell">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px 16px;">
                                <tr>
                                    <td align="center" style="padding-bottom: 10px; font-size: 11.5px; font-weight: 700; color: #071F50; text-transform: uppercase; letter-spacing: 0.8px;">
                                        <?= e($companyName) ?> Operational Standards:
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="priv-grid">
                                            <tr>
                                                <td width="25%" align="center" class="priv-col" style="padding: 4px 6px; vertical-align: top;">
                                                    <div style="font-size: 11.5px; font-weight: 600; color: #17233F;">
                                                        <span style="color: #5520D8;"><?= EmailIconService::render('car-front-fill', 14, '#5520D8', '', 'vertical-align: -2px; margin-right: 4px;') ?></span>Verified Luxury Fleet
                                                    </div>
                                                </td>
                                                <td width="25%" align="center" class="priv-col" style="padding: 4px 6px; vertical-align: top;">
                                                    <div style="font-size: 11.5px; font-weight: 600; color: #17233F;">
                                                        <span style="color: #168653;"><?= EmailIconService::render('person-check-fill', 14, '#168653', '', 'vertical-align: -2px; margin-right: 4px;') ?></span>Vetted Chauffeurs
                                                    </div>
                                                </td>
                                                <td width="25%" align="center" class="priv-col" style="padding: 4px 6px; vertical-align: top;">
                                                    <div style="font-size: 11.5px; font-weight: 600; color: #17233F;">
                                                        <span style="color: #5520D8;"><?= EmailIconService::render('clock-history', 14, '#5520D8', '', 'vertical-align: -2px; margin-right: 4px;') ?></span>Guaranteed On-Time
                                                    </div>
                                                </td>
                                                <td width="25%" align="center" class="priv-col" style="padding: 4px 6px; vertical-align: top;">
                                                    <div style="font-size: 11.5px; font-weight: 600; color: #17233F;">
                                                        <span style="color: #7655F5;"><?= EmailIconService::render('headset', 14, '#7655F5', '', 'vertical-align: -2px; margin-right: 4px;') ?></span>24/7 Concierge
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- SECTION 9: SUPPORT SECTION (NEED HELP?, support email, support telephone) -->
                    <tr>
                        <td style="padding: 0 28px 24px 28px; background-color: #FFFFFF;" class="fluid-cell">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px;">
                                <tr>
                                    <td style="padding: 16px 20px;">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="support-grid">
                                            <tr>
                                                <td style="vertical-align: middle;">
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td width="36" style="vertical-align: middle; padding-right: 10px;">
                                                                <div style="width: 34px; height: 34px; border-radius: 8px; background-color: #F3F0FF; text-align: center; line-height: 34px;">
                                                                    <?= EmailIconService::render('headset', 18, '#5520D8', '', 'margin-top: 7px;') ?>
                                                                </div>
                                                            </td>
                                                            <td style="vertical-align: middle;">
                                                                <div style="font-size: 12px; font-weight: 800; color: #071F50; letter-spacing: 0.5px;">
                                                                    NEED HELP?
                                                                </div>
                                                                <div style="font-size: 12px; color: #64748B; margin-top: 2px;">
                                                                    Our operational dispatch team is available 24/7.
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td align="right" style="vertical-align: middle;" class="support-contacts">
                                                    <div style="font-size: 12px; color: #17233F; margin-bottom: 3px;">
                                                        <?= EmailIconService::render('envelope-fill', 13, '#5520D8', '', 'margin-right: 5px; vertical-align: -1px;') ?>
                                                        <a href="mailto:<?= e($supportEmail) ?>" style="color: #5520D8; text-decoration: none; font-weight: 600;"><?= e($supportEmail) ?></a>
                                                    </div>
                                                    <div style="font-size: 12px; color: #17233F;">
                                                        <?= EmailIconService::render('telephone-fill', 13, '#168653', '', 'margin-right: 5px; vertical-align: -1px;') ?>
                                                        <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $supportPhone)) ?>" style="color: #17233F; text-decoration: none; font-weight: 600;"><?= e($supportPhone) ?></a>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- SECTION 10: PROFESSIONAL FOOTER (Dark Navy #071F50, Logo, Tagline, Website, Socials, Dynamic Copyright) -->
                    <tr>
                        <td style="background-color: #071F50; padding: 28px 28px 24px 28px; color: #94A3B8; text-align: center;" class="footer-cell">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" style="padding-bottom: 12px;">
                                        <div style="font-size: 18px; font-weight: 800; color: #FFFFFF; letter-spacing: -0.3px;">
                                            <?= EmailIconService::render('airplane-fill', 16, '#7655F5', '', 'margin-right: 6px; vertical-align: -2px;') ?>
                                            <?= e($companyName) ?>
                                        </div>
                                        <div style="font-size: 12px; color: #CBD5E1; margin-top: 4px; font-style: italic;">
                                            <?= e($companyTagline) ?>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td align="center" style="padding-bottom: 16px;">
                                        <div style="font-size: 12px; color: #94A3B8;">
                                            <a href="<?= e($websiteUrl) ?>" target="_blank" style="color: #A5B4FC; text-decoration: none; font-weight: 600; margin: 0 8px;"><?= e(preg_replace('#^https?://#', '', rtrim($websiteUrl, '/'))) ?></a>
                                            &bull;
                                            <a href="mailto:<?= e($supportEmail) ?>" style="color: #CBD5E1; text-decoration: none; margin: 0 8px;"><?= e($supportEmail) ?></a>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Bootstrap Icons Vector Social Links -->
                                <tr>
                                    <td align="center" style="padding-bottom: 16px;">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center">
                                            <tr>
                                                <td style="padding: 0 6px;">
                                                    <a href="<?= e($websiteUrl) ?>" target="_blank" style="display: inline-block; width: 30px; height: 30px; border-radius: 50%; background-color: rgba(255, 255, 255, 0.08); text-align: center; line-height: 30px; color: #CBD5E1;" aria-label="Facebook">
                                                        <?= EmailIconService::render('facebook', 14, '#CBD5E1', '', 'margin-top: 7px;') ?>
                                                    </a>
                                                </td>
                                                <td style="padding: 0 6px;">
                                                    <a href="<?= e($websiteUrl) ?>" target="_blank" style="display: inline-block; width: 30px; height: 30px; border-radius: 50%; background-color: rgba(255, 255, 255, 0.08); text-align: center; line-height: 30px; color: #CBD5E1;" aria-label="Instagram">
                                                        <?= EmailIconService::render('instagram', 14, '#CBD5E1', '', 'margin-top: 7px;') ?>
                                                    </a>
                                                </td>
                                                <td style="padding: 0 6px;">
                                                    <a href="<?= e($websiteUrl) ?>" target="_blank" style="display: inline-block; width: 30px; height: 30px; border-radius: 50%; background-color: rgba(255, 255, 255, 0.08); text-align: center; line-height: 30px; color: #CBD5E1;" aria-label="YouTube">
                                                        <?= EmailIconService::render('youtube', 14, '#CBD5E1', '', 'margin-top: 7px;') ?>
                                                    </a>
                                                </td>
                                                <td style="padding: 0 6px;">
                                                    <a href="<?= e($websiteUrl) ?>" target="_blank" style="display: inline-block; width: 30px; height: 30px; border-radius: 50%; background-color: rgba(255, 255, 255, 0.08); text-align: center; line-height: 30px; color: #CBD5E1;" aria-label="LinkedIn">
                                                        <?= EmailIconService::render('linkedin', 14, '#CBD5E1', '', 'margin-top: 7px;') ?>
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td align="center" style="border-top: 1px solid rgba(255, 255, 255, 0.12); padding-top: 14px; font-size: 11.5px; color: #64748B; line-height: 1.5;">
                                        &copy; <?= date('Y') ?> <?= e($companyName) ?>. All rights reserved.<br>
                                        Automated notification from <?= e($companyName) ?> Executive Dispatch &amp; Fleet Management System.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
