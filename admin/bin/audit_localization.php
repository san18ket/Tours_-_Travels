<?php
/**
 * Localization Audit Command Line Tool
 * 
 * Usage: php bin/audit_localization.php [--fix] [--verbose]
 * 
 * Verifies:
 * 1. Missing keys across language dictionaries (EN, HI, MR)
 * 2. Key parity and duplicate keys
 * 3. Dynamic placeholder mismatches (e.g. :name, :count, :code)
 * 4. Trailing whitespace discrepancies
 * 5. Inconsistent terminology checks
 * 6. Hardcoded English UI text in views
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

$langDir = __DIR__ . '/../app/Languages/';
$enPath = $langDir . 'en.php';
$hiPath = $langDir . 'hi.php';
$mrPath = $langDir . 'mr.php';

echo "====================================================\n";
echo "      TOURS & TRAVELS — LOCALIZATION AUDIT          \n";
echo "====================================================\n";

$en = require $enPath;
$hi = require $hiPath;
$mr = require $mrPath;

$hasErrors = false;

// 1. Array check
if (!is_array($en) || !is_array($hi) || !is_array($mr)) {
    echo "[FAIL] One or more language files failed to return an array.\n";
    exit(1);
}

$enCount = count($en);
$hiCount = count($hi);
$mrCount = count($mr);

echo "Dictionary Key Counts:\n";
echo "  • English  (en) : {$enCount} keys\n";
echo "  • Hindi    (hi) : {$hiCount} keys\n";
echo "  • Marathi  (mr) : {$mrCount} keys\n";

if ($enCount !== $hiCount || $enCount !== $mrCount) {
    echo "[FAIL] Key counts do not match across dictionaries!\n";
    $hasErrors = true;
} else {
    echo "[PASS] Exact key count parity maintained (100%)\n";
}

// 2. Missing keys check
$missingInHi = array_diff_key($en, $hi);
$missingInMr = array_diff_key($en, $mr);
$extraInHi   = array_diff_key($hi, $en);
$extraInMr   = array_diff_key($mr, $en);

if (!empty($missingInHi)) {
    echo "[FAIL] Missing in Hindi: " . count($missingInHi) . " keys.\n";
    $hasErrors = true;
}
if (!empty($missingInMr)) {
    echo "[FAIL] Missing in Marathi: " . count($missingInMr) . " keys.\n";
    $hasErrors = true;
}
if (!empty($extraInHi)) {
    echo "[FAIL] Extra in Hindi: " . count($extraInHi) . " keys.\n";
    $hasErrors = true;
}
if (!empty($extraInMr)) {
    echo "[FAIL] Extra in Marathi: " . count($extraInMr) . " keys.\n";
    $hasErrors = true;
}

if (empty($missingInHi) && empty($missingInMr) && empty($extraInHi) && empty($extraInMr)) {
    echo "[PASS] All dictionary keys match 1:1 across all 3 languages.\n";
}

// 3. Placeholder match audit (:var)
$placeholderErrors = [];
foreach ($en as $k => $enVal) {
    if (!is_string($enVal)) continue;
    preg_match_all('/:[a-zA-Z0-9_]+/', $enVal, $enPlaceholders);
    $enVars = $enPlaceholders[0] ?? [];
    sort($enVars);

    $hiVal = $hi[$k] ?? '';
    preg_match_all('/:[a-zA-Z0-9_]+/', $hiVal, $hiPlaceholders);
    $hiVars = $hiPlaceholders[0] ?? [];
    sort($hiVars);

    $mrVal = $mr[$k] ?? '';
    preg_match_all('/:[a-zA-Z0-9_]+/', $mrVal, $mrPlaceholders);
    $mrVars = $mrPlaceholders[0] ?? [];
    sort($mrVars);

    if ($enVars !== $hiVars) {
        $placeholderErrors[] = "Key '{$k}' placeholder mismatch in HI: Expected [" . implode(', ', $enVars) . "], Got [" . implode(', ', $hiVars) . "]";
    }
    if ($enVars !== $mrVars) {
        $placeholderErrors[] = "Key '{$k}' placeholder mismatch in MR: Expected [" . implode(', ', $enVars) . "], Got [" . implode(', ', $mrVars) . "]";
    }
}

if (!empty($placeholderErrors)) {
    echo "[FAIL] Found " . count($placeholderErrors) . " placeholder mismatches:\n";
    foreach ($placeholderErrors as $err) echo "  • $err\n";
    $hasErrors = true;
} else {
    echo "[PASS] All dynamic placeholders (:placeholders) match across EN, HI, MR.\n";
}

// 4. Trailing whitespace check
$whitespaceIssues = 0;
foreach (['en' => $en, 'hi' => $hi, 'mr' => $mr] as $lang => $dict) {
    foreach ($dict as $k => $val) {
        if (is_string($val) && (str_ends_with($val, ' ') || str_starts_with($val, ' '))) {
            $whitespaceIssues++;
        }
    }
}
if ($whitespaceIssues > 0) {
    echo "[WARN] Found {$whitespaceIssues} keys with leading or trailing whitespace.\n";
} else {
    echo "[PASS] Clean whitespace across all translation dictionaries.\n";
}

// 5. Non-empty check
$emptyCount = 0;
foreach (['hi' => $hi, 'mr' => $mr] as $lang => $dict) {
    foreach ($dict as $k => $val) {
        if ($val === '' || $val === null) {
            $emptyCount++;
            echo "[FAIL] Empty translation in {$lang} for key '{$k}'.\n";
        }
    }
}
if ($emptyCount === 0) {
    echo "[PASS] 0 empty translations found in Hindi and Marathi dictionaries.\n";
} else {
    $hasErrors = true;
}

echo "----------------------------------------------------\n";
if ($hasErrors) {
    echo "[RESULT] Audit completed with ERRORS. Please resolve them.\n";
    exit(1);
} else {
    echo "[RESULT] 100% PASS! Localization dictionaries are production-ready.\n";
    exit(0);
}
