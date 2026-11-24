<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

use App\Models\TripCategory;
use App\Models\Booking;
use App\Services\BookingService;
use App\Core\Database;

$pdo = Database::getConnection();
$failures = [];
$successCount = 0;

function assertCondition(bool $cond, string $msg, array &$failures, int &$successCount): void {
    if (!$cond) {
        $failures[] = $msg;
        echo " [FAIL] {$msg}\n";
    } else {
        $successCount++;
        echo " [PASS] {$msg}\n";
    }
}

echo "=== TRIP CATEGORIES TEST SUITE ===\n\n";

// 1. Verify default 13 categories
$expectedCategories = [
    'Holiday / Tour'        => 'holiday-tour',
    'Wedding'               => 'wedding',
    'Family Trip'           => 'family-trip',
    'Business Trip'         => 'business-trip',
    'Airport Trip'          => 'airport-trip',
    'Train Station Trip'    => 'train-station-trip',
    'Local Trip'            => 'local-trip',
    'Outstation Trip'       => 'outstation-trip',
    'School / College Trip' => 'school-college-trip',
    'Religious Trip'        => 'religious-trip',
    'Event / Function'      => 'event-function',
    'Hotel Trip'            => 'hotel-trip',
    'Other'                 => 'other',
];

$tripCatModel = new TripCategory();
$allCategories = $tripCatModel->getAllCategories();
assertCondition(count($allCategories) >= 13, "Master table has at least 13 categories (found " . count($allCategories) . ")", $failures, $successCount);

foreach ($expectedCategories as $name => $slug) {
    $found = false;
    foreach ($allCategories as $cat) {
        if ($cat['name'] === $name && $cat['slug'] === $slug) {
            $found = true;
            break;
        }
    }
    assertCondition($found, "Default category '{$name}' with slug '{$slug}' exists in master table", $failures, $successCount);
}

// 2. Active categories query
$activeCategories = $tripCatModel->getActiveCategories();
assertCondition(!empty($activeCategories), "getActiveCategories() returns active categories", $failures, $successCount);

// 3. Test Booking Creation with Trip Category
$holidayCat = $tripCatModel->findBySlug('holiday-tour');
assertCondition(!empty($holidayCat), "Found 'holiday-tour' category", $failures, $successCount);

$bookingModel = new Booking();
$bookingService = new BookingService();

// Find an existing customer or user to create a test booking
$customerStmt = $pdo->query("SELECT id, name, phone, email FROM customers LIMIT 1");
$customer = $customerStmt->fetch(\PDO::FETCH_ASSOC);

if (!$customer) {
    // Insert temporary customer
    $pdo->prepare("INSERT INTO customers (name, phone, email, status, created_at, updated_at) VALUES ('Test Guest', '9876543210', 'testguest@example.com', 'active', NOW(), NOW())")->execute();
    $customerId = (int)$pdo->lastInsertId();
    $customerName = 'Test Guest';
} else {
    $customerId = (int)$customer['id'];
    $customerName = $customer['name'];
}

$testBookingData = [
    'booking_code'     => 'TC-TEST-' . time(),
    'customer_id'      => $customerId,
    'customer_name'    => $customerName,
    'trip_category_id' => $holidayCat['id'],
    'pickup_address'   => '123 Test Street, Mumbai',
    'drop_address'     => '456 Resort Road, Lonavala',
    'start_date'       => date('Y-m-d', strtotime('+3 days')),
    'end_date'         => date('Y-m-d', strtotime('+4 days')),
    'pickup_time'      => '09:00:00',
    'passenger_count'  => 4,
    'status'           => 'pending',
    'payment_status'   => 'unpaid',
    'subtotal'         => 5000.00,
    'discount_amount'  => 0.00,
    'tax_amount'       => 250.00,
    'total_amount'     => 5250.00,
    'special_requests' => 'Test trip category persistence',
];

$testBookingId = $bookingModel->create($testBookingData);
assertCondition($testBookingId > 0, "Created test booking with ID {$testBookingId}", $failures, $successCount);

// 4. Retrieve booking with details and verify trip category fields
$detailedBooking = $bookingModel->findWithDetails($testBookingId);
assertCondition(!empty($detailedBooking), "findWithDetails({$testBookingId}) returned booking", $failures, $successCount);
assertCondition((int)($detailedBooking['trip_category_id'] ?? 0) === (int)$holidayCat['id'], "Booking trip_category_id matches holiday-tour ID", $failures, $successCount);
assertCondition(($detailedBooking['trip_category_name'] ?? '') === 'Holiday / Tour', "detailedBooking trip_category_name is 'Holiday / Tour'", $failures, $successCount);
assertCondition(($detailedBooking['trip_category_slug'] ?? '') === 'holiday-tour', "detailedBooking trip_category_slug is 'holiday-tour'", $failures, $successCount);

// 5. Test updateBooking with a different category (e.g. Wedding)
$weddingCat = $tripCatModel->findBySlug('wedding');
assertCondition(!empty($weddingCat), "Found 'wedding' category", $failures, $successCount);

$updateResult = $bookingService->updateBooking($testBookingId, [
    'trip_category_id' => $weddingCat['id'],
    'pickup_address'   => 'Grand Palace, Pune',
    'drop_address'     => 'Wedding Hall, Pune',
    'start_date'       => date('Y-m-d', strtotime('+5 days')),
    'end_date'         => date('Y-m-d', strtotime('+5 days')),
    'pickup_time'      => '10:30:00',
    'passenger_count'  => 8,
    'special_requests' => 'Wedding guests transfer',
]);

assertCondition(!empty($updateResult) && (int)$updateResult['trip_category_id'] === (int)$weddingCat['id'], "updateBooking successfully returned updated booking with Wedding category", $failures, $successCount);

$updatedBooking = $bookingModel->findWithDetails($testBookingId);
assertCondition((int)($updatedBooking['trip_category_id'] ?? 0) === (int)$weddingCat['id'], "Booking trip_category_id successfully updated to Wedding ID", $failures, $successCount);
assertCondition(($updatedBooking['trip_category_name'] ?? '') === 'Wedding', "Booking trip_category_name is now 'Wedding'", $failures, $successCount);
assertCondition($updatedBooking['pickup_address'] === 'Grand Palace, Pune', "Booking pickup_address updated correctly", $failures, $successCount);

// 6. Test Filtering Bookings by trip_category_id
$weddingBookings = $bookingModel->getPaginatedDetailed("b.trip_category_id = ?", [$weddingCat['id']], 1, 10);
$foundOurBooking = false;
foreach ($weddingBookings as $wb) {
    if ((int)$wb['id'] === $testBookingId) {
        $foundOurBooking = true;
        break;
    }
}
assertCondition($foundOurBooking, "getPaginatedDetailed filtered by trip_category_id={$weddingCat['id']} returns our test booking", $failures, $successCount);

// Filter by holiday-tour should NOT contain our booking now
$holidayBookings = $bookingModel->getPaginatedDetailed("b.trip_category_id = ?", [$holidayCat['id']], 1, 10);
$notInHoliday = true;
foreach ($holidayBookings as $hb) {
    if ((int)$hb['id'] === $testBookingId) {
        $notInHoliday = false;
        break;
    }
}
assertCondition($notInHoliday, "getPaginatedDetailed filtered by trip_category_id={$holidayCat['id']} does NOT return our wedding booking", $failures, $successCount);

// 7. Test Inactive Category Safety & Deletion Logic
// Create a temporary dummy category
$tempSlug = 'temp-test-' . time();
$tempCatId = $tripCatModel->create([
    'name'        => 'Temp Test Category',
    'slug'        => $tempSlug,
    'description' => 'Temporary test category',
    'status'      => 'active',
    'sort_order'  => 99,
]);
assertCondition($tempCatId > 0, "Created temporary category with ID {$tempCatId}", $failures, $successCount);

// Before linking, isUsedInBookings should be false
assertCondition(!$tripCatModel->isUsedInBookings($tempCatId), "Unused category isUsedInBookings is false", $failures, $successCount);

// Assign test booking to temp category
$bookingModel->update($testBookingId, ['trip_category_id' => $tempCatId]);
assertCondition($tripCatModel->isUsedInBookings($tempCatId), "After assignment, category isUsedInBookings is true", $failures, $successCount);
assertCondition($tripCatModel->countBookingsUsing($tempCatId) === 1, "countBookingsUsing({$tempCatId}) is exactly 1", $failures, $successCount);

// Test soft-disable when trying to delete used category
// Controller logic sets status to inactive if category is used
if ($tripCatModel->isUsedInBookings($tempCatId)) {
    $tripCatModel->update($tempCatId, ['status' => 'inactive']);
}
$reloadedTemp = $tripCatModel->find($tempCatId);
assertCondition($reloadedTemp['status'] === 'inactive', "Category in use was safely marked inactive instead of hard-deleted", $failures, $successCount);

// Now reassign test booking back to Wedding
$bookingModel->update($testBookingId, ['trip_category_id' => $weddingCat['id']]);
assertCondition(!$tripCatModel->isUsedInBookings($tempCatId), "After reassignment, temp category is no longer used", $failures, $successCount);

// Unused temp category can now be hard-deleted cleanly
$deleteResult = $tripCatModel->delete($tempCatId);
assertCondition($deleteResult === true, "Unused temporary category hard-deleted cleanly", $failures, $successCount);
$deletedLookup = $tripCatModel->find($tempCatId);
assertCondition(empty($deletedLookup), "Deleted temporary category no longer exists in database", $failures, $successCount);

// 8. Backward Compatibility: Booking with NULL trip_category_id
$nullBookingId = $bookingModel->create([
    'booking_code'     => 'TC-NULL-' . time(),
    'customer_id'      => $customerId,
    'customer_name'    => $customerName,
    'trip_category_id' => null,
    'pickup_address'   => 'Historical Origin',
    'drop_address'     => 'Historical Destination',
    'start_date'       => date('Y-m-d'),
    'end_date'         => date('Y-m-d'),
    'pickup_time'      => '12:00:00',
    'passenger_count'  => 2,
    'status'           => 'completed',
    'payment_status'   => 'paid',
    'subtotal'         => 3000.00,
    'discount_amount'  => 0.00,
    'tax_amount'       => 150.00,
    'total_amount'     => 3150.00,
]);
assertCondition($nullBookingId > 0, "Created historical simulation booking with trip_category_id = NULL", $failures, $successCount);
$nullDetailed = $bookingModel->findWithDetails($nullBookingId);
assertCondition(!empty($nullDetailed), "Historical booking retrieved successfully with LEFT JOIN", $failures, $successCount);
assertCondition($nullDetailed['trip_category_id'] === null, "Historical booking trip_category_id is null", $failures, $successCount);
assertCondition($nullDetailed['trip_category_name'] === null, "Historical booking trip_category_name is null (displays default 'Not specified')", $failures, $successCount);

// Cleanup test bookings
$pdo->prepare("DELETE FROM bookings WHERE id IN (?, ?)")->execute([$testBookingId, $nullBookingId]);
echo " Cleaned up test bookings.\n\n";

echo "=== RESULTS ===\n";
echo "Passes: {$successCount}\n";
echo "Failures: " . count($failures) . "\n";

if (!empty($failures)) {
    echo "\nFailed assertions:\n";
    foreach ($failures as $f) {
        echo " - {$f}\n";
    }
    exit(1);
}

echo "\nALL TRIP CATEGORY TESTS PASSED SUCCESSFULLY!\n";
exit(0);
