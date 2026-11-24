<?php
/**
 * Test Suite: Integrated Mobile Card and Single Horizontal Action Row
 * 
 * Verifies:
 * 1. Single unified card architecture (outer container has background, border, radius, shadow, overflow: hidden)
 * 2. Card body has transparent background and no borders (seamless inside card)
 * 3. Action toolbar has margin: 0 (NO GAP, NO SEPARATE BOX)
 * 4. Action items have width: auto (never stretch to full 100% width, no giant View button)
 * 5. Action items maintain single row with horizontal scrolling on narrow screens
 * 6. Zero inline styles on mv-card-actions causing gaps or detachment
 */

$rootDir = realpath(__DIR__ . '/..');
$passes = 0;
$fails = 0;

function assertCondition(string $desc, bool $condition): void {
    global $passes, $fails;
    if ($condition) {
        $passes++;
        echo "  [PASS] $desc\n";
    } else {
        $fails++;
        echo "  [FAIL] $desc\n";
    }
}

echo "=======================================================\n";
echo "1. VERIFYING UNIFIED SINGLE-CARD CONTAINER ARCHITECTURE\n";
echo "=======================================================\n";

$appCss = file_get_contents($rootDir . '/public/assets/css/app.css');

// Outer card wrapper is the single physical card
assertCondition("Card wrapper has border-radius 14px", 
    preg_match('/\.mv-booking-card-wrapper[^{]*\{[^}]*border-radius:\s*14px\s*!important/i', $appCss) === 1
);

assertCondition("Card wrapper has overflow: hidden to clip bottom toolbar", 
    preg_match('/\.mv-booking-card-wrapper[^{]*\{[^}]*overflow:\s*hidden\s*!important/i', $appCss) === 1
);

assertCondition("Card wrapper has background #ffffff", 
    preg_match('/\.mv-booking-card-wrapper[^{]*\{[^}]*background:\s*#ffffff\s*!important/i', $appCss) === 1
);

assertCondition("Card wrapper has border #e2e8f0", 
    preg_match('/\.mv-booking-card-wrapper[^{]*\{[^}]*border:\s*1px\s*solid\s*#e2e8f0\s*!important/i', $appCss) === 1
);

// Inner card body has transparent background and no borders
assertCondition("Inner card body has transparent background", 
    preg_match('/\.mv-booking-card-wrapper\s*>\s*\.mv-booking-card[^{]*\{[^}]*background:\s*transparent\s*!important/i', $appCss) === 1
);

assertCondition("Inner card body has border: none (no inner box)", 
    preg_match('/\.mv-booking-card-wrapper\s*>\s*\.mv-booking-card[^{]*\{[^}]*border:\s*none\s*!important/i', $appCss) === 1
);

assertCondition("Inner card body has border-radius: 0 (no independent rounded bottom)", 
    preg_match('/\.mv-booking-card-wrapper\s*>\s*\.mv-booking-card[^{]*\{[^}]*border-radius:\s*0\s*!important/i', $appCss) === 1
);

echo "\n=======================================================\n";
echo "2. VERIFYING ACTION TOOLBAR PHYSICAL INTEGRATION (NO GAP)\n";
echo "=======================================================\n";

// Action toolbar has zero margin (no gap)
assertCondition("Action toolbar has margin: 0 (no visible gap)", 
    preg_match('/\.mv-card-actions[^{]*\{[^}]*margin:\s*0\s*!important/i', $appCss) === 1
);

assertCondition("Action toolbar has margin-top: 0", 
    preg_match('/\.mv-card-actions[^{]*\{[^}]*margin-top:\s*0\s*!important/i', $appCss) === 1
);

assertCondition("Action toolbar has subtle internal divider border-top", 
    preg_match('/\.mv-card-actions[^{]*\{[^}]*border-top:\s*1px\s*solid/i', $appCss) === 1
);

assertCondition("Action toolbar has border-radius: 0 (conforms to outer card)", 
    preg_match('/\.mv-card-actions[^{]*\{[^}]*border-radius:\s*0\s*!important/i', $appCss) === 1
);

echo "\n=======================================================\n";
echo "3. VERIFYING ACTION BUTTONS ROW FITTING (NO GIANT VIEW BUTTON)\n";
echo "=======================================================\n";

assertCondition("Action items enforce width: auto (prevents giant full-width buttons)", 
    preg_match('/\.mv-card-actions\s+\.action-dropdown-item[\s\S]*?width:\s*auto\s*!important/i', $appCss) === 1
);

assertCondition("Action items enforce flex: 0 0 auto (prevents stretching and squishing)", 
    preg_match('/\.mv-card-actions\s+\.action-dropdown-item[\s\S]*?flex:\s*0\s*0\s*auto\s*!important/i', $appCss) === 1
);

assertCondition("Action wrap enforces width: auto (prevents wrapping container from forcing 100%)", 
    preg_match('/\.mv-card-actions\s+\.action-dropdown-wrap[\s\S]*?width:\s*auto\s*!important/i', $appCss) === 1
);

assertCondition("Action menu enforces width: auto", 
    preg_match('/\.mv-card-actions\s+\.action-dropdown-menu[\s\S]*?width:\s*auto\s*!important/i', $appCss) === 1
);

echo "\n=======================================================\n";
echo "4. VERIFYING ZERO INLINE GAPS IN PHP VIEW TEMPLATES\n";
echo "=======================================================\n";

$viewFiles = glob($rootDir . '/app/Views/office/**/*.php');
$viewsWithInlineGaps = 0;
foreach ($viewFiles as $vf) {
    $content = file_get_contents($vf);
    if (preg_match('/class="[^"]*mv-card-actions[^"]*"[^>]*style="[^"]*(?:margin-top|padding-top)[^"]*"/i', $content, $m)) {
        $viewsWithInlineGaps++;
        echo "  [FOUND GAP INLINE STYLE] " . basename($vf) . ": " . $m[0] . "\n";
    }
}
assertCondition("Zero view files have inline margin-top on mv-card-actions", $viewsWithInlineGaps === 0);

echo "\n-------------------------------------------------------\n";
echo "TEST RESULTS: Total Passes = $passes, Total Failures = $fails\n";
echo "-------------------------------------------------------\n";

exit($fails > 0 ? 1 : 0);
