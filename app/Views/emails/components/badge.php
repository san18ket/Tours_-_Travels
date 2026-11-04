<?php
/**
 * Reusable Status Badge Component
 * Variables: $badgeText, $badgeType ('success', 'warning', 'danger', 'info', 'primary'), $themeColors
 */
$type = $badgeType ?? 'primary';
$text = $badgeText ?? 'Active';
$primary = $themeColors['primary'] ?? '#176B5B';

switch ($type) {
    case 'success':
    case 'confirmed':
    case 'paid':
        $bg = '#D1FAE5';
        $fg = '#065F46';
        break;
    case 'warning':
    case 'pending':
    case 'partial':
        $bg = '#FEF3C7';
        $fg = '#92400E';
        break;
    case 'danger':
    case 'cancelled':
    case 'failed':
        $bg = '#FEE2E2';
        $fg = '#991B1B';
        break;
    case 'info':
    case 'assigned':
        $bg = '#E0F2FE';
        $fg = '#0369A1';
        break;
    default:
        $bg = '#EAF2ED';
        $fg = $primary;
        break;
}
?>
<span style="display: inline-block; padding: 4px 10px; border-radius: 6px; background-color: <?= e($bg) ?>; color: <?= e($fg) ?>; font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
    <?= e($text) ?>
</span>

