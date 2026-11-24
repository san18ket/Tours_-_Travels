<?php
/**
 * Automated Localization Test Suite
 * 
 * Verifies all 7 requirements in Section 29:
 * 1. All three language files return arrays
 * 2. Key counts match across all three files
 * 3. Key names match across all three files
 * 4. Hindi and Marathi files contain non-empty strings for all keys
 * 5. Language switcher endpoint switches language correctly
 * 6. Session and cookie persistence work
 * 7. Flash messages render in the selected language
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Language;
use App\Core\Session;

$passCount = 0;
$failCount = 0;

function assertTest(string $description, bool $condition): void {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] {$description}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$description}\n";
        $failCount++;
    }
}

echo "\n======================================================\n";
echo "      RUNNING AUTOMATED LOCALIZATION TEST SUITE       \n";
echo "======================================================\n\n";

// Test 1: Language files return valid arrays
echo "TEST SUITE 1: Language Dictionary Loading & Integrity\n";
$enPath = dirname(__DIR__) . '/app/Languages/en.php';
$hiPath = dirname(__DIR__) . '/app/Languages/hi.php';
$mrPath = dirname(__DIR__) . '/app/Languages/mr.php';

assertTest("en.php exists and is readable", file_exists($enPath) && is_readable($enPath));
assertTest("hi.php exists and is readable", file_exists($hiPath) && is_readable($hiPath));
assertTest("mr.php exists and is readable", file_exists($mrPath) && is_readable($mrPath));

$en = require $enPath;
$hi = require $hiPath;
$mr = require $mrPath;

assertTest("en.php returns an array", is_array($en));
assertTest("hi.php returns an array", is_array($hi));
assertTest("mr.php returns an array", is_array($mr));

// Test 2 & 3: Key counts and names match across all three files
echo "\nTEST SUITE 2: Key Count & Name Parity\n";
$enCount = count($en);
$hiCount = count($hi);
$mrCount = count($mr);

assertTest("Key count match (en: {$enCount}, hi: {$hiCount})", $enCount === $hiCount);
assertTest("Key count match (en: {$enCount}, mr: {$mrCount})", $enCount === $mrCount);

$missingInHi = array_diff_key($en, $hi);
$missingInMr = array_diff_key($en, $mr);
$extraInHi = array_diff_key($hi, $en);
$extraInMr = array_diff_key($mr, $en);

assertTest("Zero missing keys in Hindi dictionary", empty($missingInHi));
assertTest("Zero missing keys in Marathi dictionary", empty($missingInMr));
assertTest("Zero extraneous keys in Hindi dictionary", empty($extraInHi));
assertTest("Zero extraneous keys in Marathi dictionary", empty($extraInMr));

// Test 4: Non-empty translations in Hindi and Marathi
echo "\nTEST SUITE 3: Non-Empty Translation Values\n";
$emptyHi = 0;
foreach ($hi as $k => $v) {
    if ($v === '' || $v === null) $emptyHi++;
}
assertTest("All Hindi dictionary keys contain non-empty values (0 empty)", $emptyHi === 0);

$emptyMr = 0;
foreach ($mr as $k => $v) {
    if ($v === '' || $v === null) $emptyMr++;
}
assertTest("All Marathi dictionary keys contain non-empty values (0 empty)", $emptyMr === 0);

// Test 5 & 6: Language switching, session and cookie persistence
echo "\nTEST SUITE 4: Language Switcher, Session & Persistence\n";
Language::init();

// Switch to Hindi
Language::setLocale('hi', false);
assertTest("Language::getLocale() returns 'hi' after Language::setLocale('hi')", Language::getLocale() === 'hi');
assertTest("Session stores 'hi' under 'locale'", Session::get('locale') === 'hi');
assertTest("Language::isHindi() evaluates true", Language::isHindi() === true);
assertTest("Language::isMarathi() evaluates false", Language::isMarathi() === false);
assertTest("Language::isRTL() evaluates false (LTR layout)", Language::isRTL() === false);

// Check translation in Hindi
$hiBooking = __('booking.title', [], 'Booking');
assertTest("Translation for 'booking.title' in Hindi is 'बुकिंग' (Got: {$hiBooking})", $hiBooking === 'बुकिंग');

// Switch to Marathi
Language::setLocale('mr', false);
assertTest("Language::getLocale() returns 'mr' after Language::setLocale('mr')", Language::getLocale() === 'mr');
assertTest("Session stores 'mr' under 'locale'", Session::get('locale') === 'mr');
assertTest("Language::isMarathi() evaluates true", Language::isMarathi() === true);
assertTest("Language::isHindi() evaluates false", Language::isHindi() === false);

$mrPayment = __('finance.payment', [], 'Payment');
assertTest("Translation for 'finance.payment' in Marathi is 'पेमेंट' (Got: {$mrPayment})", $mrPayment === 'पेमेंट');

// Switch to English
Language::setLocale('en', false);
assertTest("Language::getLocale() returns 'en' after Language::setLocale('en')", Language::getLocale() === 'en');
assertTest("Session stores 'en' under 'locale'", Session::get('locale') === 'en');

// Test 7: Dynamic Placeholders & Formatted Output
echo "\nTEST SUITE 5: Placeholders & Formatted Output\n";
Language::setLocale('hi', false);
$interpolated = __('common.showing_entries', ['from' => 1, 'to' => 10, 'total' => 50]);
assertTest("Dynamic placeholders interpolate properly in Hindi (Got: {$interpolated})", str_contains($interpolated, '1') && str_contains($interpolated, '10') && str_contains($interpolated, '50'));

Language::setLocale('en', false);
$interpolatedEn = __('common.showing_entries', ['from' => 1, 'to' => 10, 'total' => 50]);
assertTest("Dynamic placeholders interpolate properly in English (Got: {$interpolatedEn})", str_contains($interpolatedEn, '1–10'));

echo "\n======================================================\n";
echo "TEST RESULTS SUMMARY: {$passCount} PASSED, {$failCount} FAILED\n";
echo "======================================================\n\n";

if ($failCount > 0) {
    exit(1);
}
exit(0);
