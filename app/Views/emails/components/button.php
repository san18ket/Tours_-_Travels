<?php
/**
 * Reusable Mobile-Friendly CTA Button Component
 * Variables: $btnText, $btnUrl, $btnColor, $themeColors
 */
$color = $btnColor ?? ($themeColors['primary'] ?? '#176B5B');
$text = $btnText ?? 'View Details';
$url = $btnUrl ?? '#';
?>
<table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 20px 0 10px 0; width: 100%;">
    <tr>
        <td align="center">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="mobile-full">
                <tr>
                    <td align="center" style="background-color: <?= e($color) ?>; border-radius: 8px;">
                        <a href="<?= e($url) ?>" target="_blank" rel="noopener" class="cta-btn" style="display: inline-block; padding: 14px 28px; font-size: 15px; font-weight: 700; color: #FFFFFF; text-decoration: none; border-radius: 8px; text-align: center; min-height: 44px; box-sizing: border-box; line-height: 1.2;">
                            <?= e($text) ?> &rarr;
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

