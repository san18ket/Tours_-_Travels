<?php
/**
 * Reusable Information Card Component for Data Rows
 * Variables: $cardTitle, $cardRows (array of ['label' => ..., 'value' => ...]), $themeColors
 */
$primary = $themeColors['primary'] ?? '#176B5B';
$secondary = $themeColors['secondary'] ?? '#16443B';
$cardTitle = $cardTitle ?? null;
$cardRows = $cardRows ?? [];
?>
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="info-card" style="background-color: #F7F8F4; border: 1px solid #E2E8F0; border-radius: 10px; margin: 16px 0; overflow: hidden;">
    <?php if (!empty($cardTitle)): ?>
        <tr>
            <td style="padding: 12px 18px; background-color: #EAF2ED; border-bottom: 1px solid #E2E8F0; font-size: 13px; font-weight: 800; color: <?= e($secondary) ?>; text-transform: uppercase; letter-spacing: 0.5px;">
                <?= e($cardTitle) ?>
            </td>
        </tr>
    <?php endif; ?>
    <tr>
        <td style="padding: 14px 18px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <?php foreach ($cardRows as $idx => $row): ?>
                    <tr>
                        <td style="padding: 6px 0; width: 38%; vertical-align: top; font-size: 13px; color: #64748B; font-weight: 600;" class="info-col">
                            <?= e($row['label'] ?? '') ?>
                        </td>
                        <td style="padding: 6px 0; width: 62%; vertical-align: top; font-size: 13.5px; color: #20352F; font-weight: 700; text-align: right;" class="info-col info-val">
                            <?= $row['html'] ?? e($row['value'] ?? '') ?>
                        </td>
                    </tr>
                    <?php if ($idx < count($cardRows) - 1): ?>
                        <tr>
                            <td colspan="2" style="border-bottom: 1px dashed #E2E8F0; height: 1px; padding: 0;"></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>
        </td>
    </tr>
</table>

