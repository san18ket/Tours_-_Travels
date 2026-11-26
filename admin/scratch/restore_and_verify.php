<?php
require_once __DIR__ . '/../app/bootstrap.php';
$db = App\Core\Database::getConnection();

// Clean any leftover test rows
$db->prepare("DELETE FROM driver_trip_payments WHERE dispatch_id NOT IN (SELECT id FROM dispatches)")->execute();
$db->prepare("DELETE FROM dispatches WHERE dispatch_code = 'DSP-TEST-FLOW'")->execute();
$db->prepare("DELETE FROM bookings WHERE booking_code = 'BK-TEST-FLOW'")->execute();

// Check primary account balance
$db->prepare("UPDATE finance_accounts SET current_balance = 53000.00 WHERE id = 1")->execute();

$acc = $db->query("SELECT id, account_name, current_balance FROM finance_accounts WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$disp = $db->query("SELECT id, dispatch_code, driver_trip_amount, driver_paid_amount, driver_payment_status FROM dispatches WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$vouchers = $db->query("SELECT id, payment_reference, amount, status FROM driver_trip_payments WHERE dispatch_id = 1")->fetchAll(PDO::FETCH_ASSOC);

echo "Primary Account Balance: ₹" . $acc['current_balance'] . "\n";
echo "Dispatch #1: Amount: ₹" . $disp['driver_trip_amount'] . ", Paid: ₹" . $disp['driver_paid_amount'] . ", Status: " . $disp['driver_payment_status'] . "\n";
echo "Dispatch #1 Vouchers:\n";
print_r($vouchers);

