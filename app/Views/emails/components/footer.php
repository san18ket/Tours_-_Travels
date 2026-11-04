<?php
/**
 * Reusable Email Footer Component
 * Variables: $companyName, $companyEmail, $companyPhone, $companyWebsite, $companyAddress, $themeColors
 */
$primary = $themeColors['primary'] ?? '#176B5B';
$secondary = $themeColors['secondary'] ?? '#16443B';
?>
<!-- SECTION: FOOTER (Clean, compliant, security note, company contacts) -->
<tr>
    <td style="background-color: #F7F8F4; border-top: 1px solid #E2E8F0; padding: 26px 28px 28px 28px;" class="footer-cell">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
            <!-- Need assistance row -->
            <tr>
                <td style="padding-bottom: 18px; border-bottom: 1px solid #E2E8F0;">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td align="left" style="vertical-align: middle;">
                                <div style="font-size: 13.5px; font-weight: 700; color: <?= e($secondary) ?>; margin-bottom: 3px;">
                                    Questions or Need Assistance?
                                </div>
                                <div style="font-size: 12.5px; color: #64748B;">
                                    Our support desk is available to assist with your bookings and services.
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 10px;">
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <?php if (!empty($companyPhone)): ?>
                                            <td style="padding-right: 18px; font-size: 12px; font-weight: 600;">
                                                <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $companyPhone)) ?>" style="color: <?= e($primary) ?>; text-decoration: none;">
                                                    📞 <?= e($companyPhone) ?>
                                                </a>
                                            </td>
                                        <?php endif; ?>
                                        <?php if (!empty($companyEmail)): ?>
                                            <td style="padding-right: 18px; font-size: 12px; font-weight: 600;">
                                                <a href="mailto:<?= e($companyEmail) ?>" style="color: <?= e($primary) ?>; text-decoration: none;">
                                                    ✉️ <?= e($companyEmail) ?>
                                                </a>
                                            </td>
                                        <?php endif; ?>
                                        <?php if (!empty($companyWebsite)): ?>
                                            <td style="font-size: 12px; font-weight: 600;">
                                                <a href="<?= e($companyWebsite) ?>" target="_blank" rel="noopener" style="color: <?= e($primary) ?>; text-decoration: none;">
                                                    🌐 Visit Website
                                                </a>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- Company Legal & Address -->
            <tr>
                <td style="padding-top: 16px; font-size: 11.5px; line-height: 1.5; color: #72827A; text-align: center;">
                    <div style="font-weight: 700; color: <?= e($secondary) ?>; margin-bottom: 4px;">
                        <?= e($companyName) ?>
                    </div>
                    <?php if (!empty($companyAddress)): ?>
                        <div style="margin-bottom: 6px;">
                            <?= e($companyAddress) ?>
                        </div>
                    <?php endif; ?>
                    <div style="color: #94A3B8; font-size: 11px;">
                        This is an official automated notification. Please do not reply directly to this automated email if unmonitored.
                        <br>&copy; <?= date('Y') ?> <?= e($companyName) ?>. All rights reserved.
                    </div>
                </td>
            </tr>
        </table>
    </td>
</tr>

