<?php
/**
 * Reusable Email Brand Header Component
 * Variables: $companyName, $companyLogoUrl, $companyTagline, $categoryLabel, $headingText, $subheadingText, $themeColors
 */
$primary = $themeColors['primary'] ?? '#176B5B';
$secondary = $themeColors['secondary'] ?? '#16443B';
$accent = $themeColors['accent'] ?? '#D6B77A';
?>
<!-- SECTION 1: TOP BRAND BAR -->
<tr>
    <td style="background-color: #FFFFFF; padding: 22px 28px 18px 28px; border-bottom: 1px solid #E2E8F0;" class="fluid-cell">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td align="left" style="vertical-align: middle;">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <?php if (!empty($companyLogoUrl)): ?>
                                <td style="vertical-align: middle; padding-right: 12px;">
                                    <img src="<?= e($companyLogoUrl) ?>" alt="<?= e($companyName) ?>" style="max-height: 42px; max-width: 140px; height: auto; display: block; object-fit: contain; border-radius: 6px;">
                                </td>
                            <?php else: ?>
                                <td style="vertical-align: middle; padding-right: 12px;">
                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="width: 40px; height: 40px; border-radius: 10px; background-color: <?= e($primary) ?>; text-align: center; vertical-align: middle; color: #FFFFFF; font-size: 18px; font-weight: 800;">
                                                <?= strtoupper(substr($companyName, 0, 1)) ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            <?php endif; ?>
                            <td style="vertical-align: middle;">
                                <div style="font-size: 19px; font-weight: 800; color: <?= e($secondary) ?>; letter-spacing: -0.3px; line-height: 1.15; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                                    <?= e($companyName) ?>
                                </div>
                                <?php if (!empty($companyTagline)): ?>
                                    <div style="font-size: 11px; font-weight: 600; color: <?= e($primary) ?>; text-transform: uppercase; letter-spacing: 0.8px; margin-top: 3px;">
                                        <?= e($companyTagline) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </td>
                <td align="right" style="vertical-align: middle;" class="hide-on-mobile">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="background-color: #F7F8F4; border: 1px solid #E2E8F0; border-radius: 20px; padding: 5px 12px; font-size: 11.5px; font-weight: 600; color: <?= e($primary) ?>;">
                                <?= date('d M Y') ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </td>
</tr>

<!-- SECTION 2: HERO BANNER (Dynamic Website Theme Gradient) -->
<tr>
    <td style="background-color: <?= e($primary) ?>; background: linear-gradient(135deg, <?= e($primary) ?> 0%, <?= e($secondary) ?> 100%); padding: 26px 28px 24px 28px;" class="hero-cell">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td>
                    <!-- Category Badge -->
                    <?php if (!empty($categoryLabel)): ?>
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin-bottom: 10px;">
                            <tr>
                                <td style="background-color: rgba(255, 255, 255, 0.18); border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 4px; padding: 3px 9px; font-size: 10.5px; font-weight: 800; color: #FFFFFF; text-transform: uppercase; letter-spacing: 1px;">
                                    <?= e($categoryLabel) ?>
                                </td>
                            </tr>
                        </table>
                    <?php endif; ?>

                    <!-- Heading -->
                    <h1 class="hero-title" style="margin: 0 0 6px 0; font-size: 23px; font-weight: 800; color: #FFFFFF; line-height: 1.25; letter-spacing: -0.3px;">
                        <?= e($headingText) ?>
                    </h1>

                    <!-- Subheading -->
                    <?php if (!empty($subheadingText)): ?>
                        <p class="hero-subtitle" style="margin: 0; font-size: 13.5px; line-height: 1.45; color: rgba(255, 255, 255, 0.9); font-weight: 400;">
                            <?= e($subheadingText) ?>
                        </p>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </td>
</tr>

