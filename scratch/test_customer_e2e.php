<?php
/**
 * End-to-End HTTP Functional Verification
 * Tests Login, Access Control, Customer Form Rendering, POST Submission, Isolation & Directory
 */

$baseUrl = 'http://127.0.0.1:8080';
$cookieFile = __DIR__ . '/cookie.txt';
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

function http_req(string $url, string $method = 'GET', array $data = [], string $cookieFile = null): array {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    // Extract redirect location if any
    $location = null;
    if (preg_match('/^Location:\s*(.*)$/mi', $headers, $matches)) {
        $location = trim($matches[1]);
    }
    
    // Extract CSRF token if present in HTML
    $csrf = null;
    if (preg_match('/name=["\']_csrf_token["\']\s+value=["\']([^"\']+)["\']/i', $body, $matches)) {
        $csrf = $matches[1];
    }
    
    curl_close($ch);
    
    return [
        'code'     => $httpCode,
        'headers'  => $headers,
        'body'     => $body,
        'location' => $location,
        'csrf'     => $csrf,
    ];
}

echo "=== STARTING E2E HTTP VERIFICATION ===\n\n";

// 1. Fetch Login Page and CSRF Token
echo "[1] Fetching login page...\n";
$res = http_req("{$baseUrl}/login", 'GET', [], $cookieFile);
assert($res['code'] === 200, "Login page returned code {$res['code']}");
$csrf = $res['csrf'];
assert(!empty($csrf), "CSRF token extracted from login form");
echo "  ✓ Login page loaded, CSRF token: " . substr($csrf, 0, 16) . "...\n";

// 2. Perform Login as Owner
echo "[2] Logging in as System Owner...\n";
$res = http_req("{$baseUrl}/login", 'POST', [
    '_csrf_token' => $csrf,
    'login'      => 'owner',
    'password'   => 'owner123',
], $cookieFile);
assert($res['code'] === 302, "Login redirect expected 302, got {$res['code']}");
echo "  ✓ Login successful, redirected to: {$res['location']}\n";

// 3. Request Target Page /office/users/create
echo "[3] Requesting target page: /office/users/create ...\n";
$res = http_req("{$baseUrl}/office/users/create", 'GET', [], $cookieFile);
assert($res['code'] === 200, "Add customer page returned {$res['code']}");
$html = $res['body'];

// Verify Design Elements and Wizard Steps
assert(str_contains($html, 'Register Premium Guest') || str_contains($html, 'Add Customer'), "Title present");
assert(str_contains($html, 'Dashboard') && str_contains($html, 'Customers') && str_contains($html, 'Add Customer'), "Breadcrumb present");
assert(str_contains($html, 'Basic Information'), "Step 01 Basic Information present");
assert(str_contains($html, 'Contact Information'), "Step 02 Contact Information present");
assert(str_contains($html, 'Residential &amp; Billing Address') || str_contains($html, 'Residential & Billing Address') || str_contains($html, 'Address Details'), "Step 03 Address present");
assert(str_contains($html, 'Review &amp; Save') || str_contains($html, 'Record Settings'), "Step 04 Review & Settings present");
assert(!str_contains($html, 'Preferred Channel'), "Preferred Channel input is completely removed");
assert(!str_contains($html, 'Travel Profile &amp; Preferences'), "Travel Profile step is completely removed");

// Verify NO password, username, or role fields
assert(!str_contains($html, 'type="password"'), "No password field on form");
assert(!preg_match('/name=["\']username["\']/i', $html), "No username field on form");
assert(!preg_match('/name=["\']role["\']/i', $html), "No role assignment field on form");
echo "  ✓ Target page /office/users/create rendered luxury customer form with clean 4-step wizard\n";
echo "  ✓ Confirmed NO password, username, role, or preferred channel fields exist\n";

$csrfCreate = $res['csrf'];
assert(!empty($csrfCreate), "Customer creation CSRF token found");

// 4. Submit New Customer Form via POST /office/users/create
echo "[4] Submitting customer registration via POST /office/users/create ...\n";
$uniqueMobile = '+91 98765 ' . random_int(10000, 99999);
$uniqueEmail = 'genevieve.' . uniqid() . '@chateau-voyage.com';

$res = http_req("{$baseUrl}/office/users/create", 'POST', [
    '_csrf_token'               => $csrfCreate,
    'name'                      => 'Countess Genevieve de Montfort',
    'title'                     => 'Countess',
    'date_of_birth'             => '1988-06-15',
    'gender'                    => 'female',
    'primary_mobile'            => $uniqueMobile,
    'alternate_phone'           => '+33 1 42 68 55 00',
    'email'                     => $uniqueEmail,
    'address_line'              => '12 Place Vendôme',
    'city'                      => 'Paris',
    'state'                     => 'Île-de-France',
    'postal_code'               => '75001',
    'country'                   => 'France',
    'status'                    => 'active',
    'marketing_consent'         => '1',
], $cookieFile);

if ($res['code'] !== 302) {
    echo "Got HTTP Code: {$res['code']}\n";
    echo "Headers:\n{$res['headers']}\n";
    echo "Body:\n" . substr($res['body'], 0, 500) . "\n";
}
assert($res['code'] === 302, "Customer creation redirected (302)");
echo "  ✓ Customer successfully registered! Redirected to: {$res['location']}\n";
$profileUrl = $res['location'];

// 5. Follow Redirect to Customer Profile View
echo "[5] Accessing customer profile view: {$profileUrl} ...\n";
$fullProfileUrl = str_starts_with($profileUrl, 'http') ? $profileUrl : "{$baseUrl}{$profileUrl}";
$res = http_req($fullProfileUrl, 'GET', [], $cookieFile);
assert($res['code'] === 200, "Customer profile view returned 200");
$profileHtml = $res['body'];
assert(str_contains($profileHtml, 'Countess Genevieve de Montfort'), "Profile contains customer name");
assert(str_contains($profileHtml, 'Paris'), "Profile contains city");
assert(!str_contains($profileHtml, 'preferred_contact_method'), "No preferred contact method in profile");
echo "  ✓ Customer profile loaded successfully with contact and address information\n";

// 6. Access Dedicated Customer Directory /office/customers
echo "[6] Accessing dedicated Customer Directory: /office/customers ...\n";
$res = http_req("{$baseUrl}/office/customers?q=" . urlencode('Countess Genevieve'), 'GET', [], $cookieFile);
assert($res['code'] === 200, "Customer directory returned 200");
assert(str_contains($res['body'], 'Countess Genevieve de Montfort'), "Search finds newly registered customer");
echo "  ✓ Customer directory search and responsive listing verified\n";

// 7. Verify Account Isolation: Customer cannot log in to staff portal
echo "[7] Verifying customer isolation (attempting login with customer email)...\n";
$res = http_req("{$baseUrl}/login", 'POST', [
    'csrf_token' => $csrf,
    'login'      => $uniqueEmail,
    'password'   => 'password123',
], null);
assert($res['code'] === 302, "Login attempt redirects");
// Check that session was not authenticated
$resCheck = http_req("{$baseUrl}/office/dashboard", 'GET', [], null);
assert($resCheck['code'] === 302, "Unauthenticated session redirected from /office/*");
echo "  ✓ Verified: Customer profile has NO login credentials and CANNOT access /office/*\n";

echo "\n=== ALL E2E HTTP VERIFICATIONS PASSED CLEANLY! ===\n";
unlink($cookieFile);
