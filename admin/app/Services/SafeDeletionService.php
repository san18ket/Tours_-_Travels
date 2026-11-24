<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Employee;
use App\Models\Customer;
use App\Models\Booking;
use PDO;

/**
 * Service to enforce safe deletion rules across Employees, Customers, and Bookings.
 *
 * Checks all related trips, assignments, bookings, and payments before allowing deletion.
 * Returns concise, user-friendly messages and prevents accidental data corruption or
 * deletion of records with active operations or pending finances.
 */
class SafeDeletionService
{
    protected Employee $employeeModel;
    protected Customer $customerModel;
    protected Booking $bookingModel;

    public function __construct()
    {
        $this->employeeModel = new Employee();
        $this->customerModel = new Customer();
        $this->bookingModel = new Booking();
    }

    /**
     * Determine whether an Employee can be safely deleted.
     *
     * Rules:
     * - If assigned to a pending, active, or in-progress trip, block deletion:
     *   "Cannot delete this employee. They have an active trip."
     * - If related trip is completed but related payment/salary is still pending, block deletion:
     *   "Cannot delete this employee. Trip payment is still pending."
     * - If all related trips are completed and all related employee payments are fully settled, allow deletion.
     * - If the employee has no related trips or pending payments, allow deletion.
     *
     * @param int $employeeId
     * @param array|null $employee
     * @return array{allowed: bool, message: string, reason: string}
     */
    public function canDeleteEmployee(int $employeeId, ?array $employee = null): array
    {
        $pdo = Database::getConnection();

        if ($employee === null) {
            $employee = $this->employeeModel->find($employeeId);
            if (!$employee) {
                return [
                    'allowed' => false,
                    'message' => 'Employee record not found.',
                    'reason'  => 'not_found'
                ];
            }
        }

        // 1. Collect all linked driver profile IDs
        $driverProfileIds = [];
        $stmtDriver = $pdo->prepare("SELECT `id` FROM `driver_profiles` WHERE `employee_id` = ? OR (`user_id` IS NOT NULL AND `user_id` = ?)");
        $userId = !empty($employee['user_id']) ? (int)$employee['user_id'] : -1;
        $stmtDriver->execute([$employeeId, $userId]);
        while ($row = $stmtDriver->fetch(PDO::FETCH_ASSOC)) {
            $driverProfileIds[] = (int)$row['id'];
        }

        // 2. Fetch all dispatches (trips) linked to this employee/driver
        $dispatches = [];
        if (!empty($driverProfileIds)) {
            $inClause = implode(',', array_fill(0, count($driverProfileIds), '?'));
            $stmtDisp = $pdo->prepare("
                SELECT d.id, d.dispatch_code, d.status, d.driver_trip_amount, d.driver_paid_amount, d.driver_payment_status,
                       b.id AS booking_id, b.status AS booking_status
                FROM `dispatches` d
                LEFT JOIN `bookings` b ON d.booking_id = b.id
                WHERE d.driver_id IN ({$inClause})
            ");
            $stmtDisp->execute($driverProfileIds);
            $dispatches = $stmtDisp->fetchAll(PDO::FETCH_ASSOC);
        }

        // Also check any dispatches where employee_id is directly recorded in driver_trip_payments
        $stmtDtp = $pdo->prepare("
            SELECT d.id, d.dispatch_code, d.status, d.driver_trip_amount, d.driver_paid_amount, d.driver_payment_status,
                   b.id AS booking_id, b.status AS booking_status
            FROM `driver_trip_payments` dtp
            JOIN `dispatches` d ON dtp.dispatch_id = d.id
            LEFT JOIN `bookings` b ON d.booking_id = b.id
            WHERE dtp.employee_id = ?
        ");
        $stmtDtp->execute([$employeeId]);
        while ($dtpRow = $stmtDtp->fetch(PDO::FETCH_ASSOC)) {
            $found = false;
            foreach ($dispatches as $existing) {
                if ((int)$existing['id'] === (int)$dtpRow['id']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $dispatches[] = $dtpRow;
            }
        }

        // RULE 1: Check for active, pending, or in-progress trips
        foreach ($dispatches as $d) {
            $dispStatus = strtolower((string)($d['status'] ?? ''));
            $bookStatus = strtolower((string)($d['booking_status'] ?? ''));

            $isTripActive = in_array($dispStatus, ['assigned', 'driver_accepted', 'en_route_pickup', 'trip_started'], true)
                || (!in_array($dispStatus, ['completed', 'cancelled'], true));

            $isBookingActive = !empty($bookStatus) && in_array($bookStatus, ['pending', 'confirmed', 'assigned', 'in_progress'], true);

            if ($isTripActive || $isBookingActive) {
                return [
                    'allowed' => false,
                    'message' => 'Cannot delete this employee. They have an active trip.',
                    'reason'  => 'active_trip'
                ];
            }
        }

        // RULE 2: Check completed trips for pending driver trip payment
        foreach ($dispatches as $d) {
            $dispStatus = strtolower((string)($d['status'] ?? ''));
            if ($dispStatus === 'completed') {
                $payStatus = strtolower((string)($d['driver_payment_status'] ?? 'not_due'));
                $tripAmount = $d['driver_trip_amount'] !== null ? (float)$d['driver_trip_amount'] : null;
                $paidAmount = (float)($d['driver_paid_amount'] ?? 0);

                $isPendingPay = in_array($payStatus, ['due', 'partially_paid'], true)
                    || ($tripAmount !== null && $tripAmount > 0 && ($tripAmount - $paidAmount) > 0.009);

                if ($isPendingPay) {
                    return [
                        'allowed' => false,
                        'message' => 'Cannot delete this employee. Trip payment is still pending.',
                        'reason'  => 'pending_trip_payment'
                    ];
                }
            }
        }

        // RULE 3: Check unpaid employee payroll / salary records
        $stmtPayroll = $pdo->prepare("SELECT COUNT(*) FROM `payroll_items` WHERE `employee_id` = ? AND `payment_status` = 'unpaid'");
        $stmtPayroll->execute([$employeeId]);
        if ((int)$stmtPayroll->fetchColumn() > 0) {
            return [
                'allowed' => false,
                'message' => 'Cannot delete this employee. Trip payment is still pending.',
                'reason'  => 'pending_salary_payment'
            ];
        }

        // All checks passed
        return [
            'allowed' => true,
            'message' => 'Employee deleted successfully.',
            'reason'  => 'allowed'
        ];
    }

    /**
     * Determine whether a Customer can be safely deleted.
     *
     * Rules:
     * - If the customer has a pending, confirmed, active, or in-progress booking, block deletion:
     *   "Cannot delete this customer. They have an active booking."
     * - If a completed booking still has an outstanding customer payment, block deletion:
     *   "Cannot delete this customer. Payment is still pending."
     * - If all related bookings are completed and all customer payments are settled, allow deletion.
     * - If the customer has no related bookings or pending payments, allow deletion.
     *
     * @param int $customerId
     * @param array|null $customer
     * @return array{allowed: bool, message: string, reason: string}
     */
    public function canDeleteCustomer(int $customerId, ?array $customer = null): array
    {
        $pdo = Database::getConnection();

        if ($customer === null) {
            $customer = $this->customerModel->find($customerId);
            if (!$customer) {
                return [
                    'allowed' => false,
                    'message' => 'Customer profile not found.',
                    'reason'  => 'not_found'
                ];
            }
        }

        $userId = !empty($customer['user_id']) ? (int)$customer['user_id'] : null;

        // Fetch all bookings for this customer
        if ($userId !== null) {
            $stmtBookings = $pdo->prepare("
                SELECT b.id, b.booking_code, b.status, b.total_amount,
                       d.id AS dispatch_id, d.status AS dispatch_status,
                       inv.id AS invoice_id, inv.payment_status AS invoice_payment_status,
                       inv.total_amount AS invoice_total, inv.paid_amount AS invoice_paid
                FROM `bookings` b
                LEFT JOIN `dispatches` d ON b.id = d.booking_id
                LEFT JOIN `invoices` inv ON b.id = inv.booking_id
                WHERE b.customer_profile_id = ? OR (b.customer_profile_id IS NULL AND b.customer_id = ?)
            ");
            $stmtBookings->execute([$customerId, $userId]);
        } else {
            $stmtBookings = $pdo->prepare("
                SELECT b.id, b.booking_code, b.status, b.total_amount,
                   d.id AS dispatch_id, d.status AS dispatch_status,
                   inv.id AS invoice_id, inv.payment_status AS invoice_payment_status,
                   inv.total_amount AS invoice_total, inv.paid_amount AS invoice_paid
                FROM `bookings` b
                LEFT JOIN `dispatches` d ON b.id = d.booking_id
                LEFT JOIN `invoices` inv ON b.id = inv.booking_id
                WHERE b.customer_profile_id = ?
            ");
            $stmtBookings->execute([$customerId]);
        }
        $bookings = $stmtBookings->fetchAll(PDO::FETCH_ASSOC);

        // RULE 1: Check for pending, confirmed, active, or in-progress bookings
        foreach ($bookings as $b) {
            $bookStatus = strtolower((string)($b['status'] ?? ''));
            $dispStatus = strtolower((string)($b['dispatch_status'] ?? ''));

            $isBookingActive = in_array($bookStatus, ['pending', 'confirmed', 'assigned', 'in_progress'], true)
                || (!in_array($bookStatus, ['completed', 'cancelled'], true));

            $isDispatchActive = !empty($dispStatus) && (
                in_array($dispStatus, ['assigned', 'driver_accepted', 'en_route_pickup', 'trip_started'], true)
                || (!in_array($dispStatus, ['completed', 'cancelled'], true))
            );

            if ($isBookingActive || $isDispatchActive) {
                return [
                    'allowed' => false,
                    'message' => 'Cannot delete this customer. They have an active booking.',
                    'reason'  => 'active_booking'
                ];
            }
        }

        // RULE 2: Check completed bookings for outstanding customer payment
        foreach ($bookings as $b) {
            $bookStatus = strtolower((string)($b['status'] ?? ''));
            if ($bookStatus === 'completed') {
                if (!empty($b['invoice_id'])) {
                    $payStatus = strtolower((string)($b['invoice_payment_status'] ?? 'unpaid'));
                    $invTotal = (float)($b['invoice_total'] ?? 0);
                    $invPaid = (float)($b['invoice_paid'] ?? 0);

                    if ($payStatus !== 'paid' || ($invTotal - $invPaid) > 0.009) {
                        return [
                            'allowed' => false,
                            'message' => 'Cannot delete this customer. Payment is still pending.',
                            'reason'  => 'pending_payment'
                        ];
                    }
                } else {
                    $bookingTotal = (float)($b['total_amount'] ?? 0);
                    if ($bookingTotal > 0.009) {
                        return [
                            'allowed' => false,
                            'message' => 'Cannot delete this customer. Payment is still pending.',
                            'reason'  => 'pending_payment'
                        ];
                    }
                }
            }
        }

        // Check any customer-level invoices
        if ($userId !== null) {
            $stmtInv = $pdo->prepare("
                SELECT COUNT(*) FROM `invoices`
                WHERE (`customer_profile_id` = ? OR (`customer_profile_id` IS NULL AND `customer_id` = ?))
                  AND (`payment_status` != 'paid' OR (`total_amount` - `paid_amount`) > 0.009)
            ");
            $stmtInv->execute([$customerId, $userId]);
        } else {
            $stmtInv = $pdo->prepare("
                SELECT COUNT(*) FROM `invoices`
                WHERE `customer_profile_id` = ?
                  AND (`payment_status` != 'paid' OR (`total_amount` - `paid_amount`) > 0.009)
            ");
            $stmtInv->execute([$customerId]);
        }
        if ((int)$stmtInv->fetchColumn() > 0) {
            return [
                'allowed' => false,
                'message' => 'Cannot delete this customer. Payment is still pending.',
                'reason'  => 'pending_payment'
            ];
        }

        // All checks passed
        return [
            'allowed' => true,
            'message' => 'Customer deleted successfully.',
            'reason'  => 'allowed'
        ];
    }

    /**
     * Determine whether a Booking can be safely deleted.
     *
     * Rules:
     * - If the booking/trip is pending, confirmed, assigned, active, or in progress, block deletion:
     *   "Cannot delete this booking. The trip is still active."
     * - If the booking is completed but the customer's payment is still pending, block deletion:
     *   "Cannot delete this booking. Payment is still pending."
     * - If the customer payment is complete but related employee/driver trip payment is still pending, block deletion:
     *   "Cannot delete this booking. Trip payment is still pending."
     * - Only allow deletion when the booking is completed and all related payments are fully settled.
     * - If there are no outstanding related records, allow deletion.
     *
     * @param int $bookingId
     * @param array|null $booking
     * @return array{allowed: bool, message: string, reason: string}
     */
    public function canDeleteBooking(int $bookingId, ?array $booking = null): array
    {
        $pdo = Database::getConnection();

        if ($booking === null) {
            $booking = $this->bookingModel->find($bookingId);
            if (!$booking) {
                return [
                    'allowed' => false,
                    'message' => 'Booking manifest not found.',
                    'reason'  => 'not_found'
                ];
            }
        }

        // Fetch all linked dispatches
        $stmtDisp = $pdo->prepare("SELECT * FROM `dispatches` WHERE `booking_id` = ?");
        $stmtDisp->execute([$bookingId]);
        $dispatches = $stmtDisp->fetchAll(PDO::FETCH_ASSOC);

        // Fetch all linked invoices
        $stmtInv = $pdo->prepare("SELECT * FROM `invoices` WHERE `booking_id` = ?");
        $stmtInv->execute([$bookingId]);
        $invoices = $stmtInv->fetchAll(PDO::FETCH_ASSOC);

        // RULE 1: Booking / Trip is pending, confirmed, assigned, active, or in progress
        $bookStatus = strtolower((string)($booking['status'] ?? ''));

        $isBookingActive = in_array($bookStatus, ['pending', 'confirmed', 'assigned', 'in_progress'], true)
            || (!in_array($bookStatus, ['completed', 'cancelled'], true));

        if ($isBookingActive) {
            return [
                'allowed' => false,
                'message' => 'Cannot delete this booking. The trip is still active.',
                'reason'  => 'active_trip'
            ];
        }

        foreach ($dispatches as $dispatch) {
            $dispStatus = strtolower((string)($dispatch['status'] ?? ''));
            $isDispatchActive = in_array($dispStatus, ['assigned', 'driver_accepted', 'en_route_pickup', 'trip_started'], true)
                || (!in_array($dispStatus, ['completed', 'cancelled'], true));

            if ($isDispatchActive) {
                return [
                    'allowed' => false,
                    'message' => 'Cannot delete this booking. The trip is still active.',
                    'reason'  => 'active_trip'
                ];
            }
        }

        // RULE 2: Customer payment is still pending
        if (!empty($invoices)) {
            foreach ($invoices as $invoice) {
                $payStatus = strtolower((string)($invoice['payment_status'] ?? 'unpaid'));
                $invTotal = (float)($invoice['total_amount'] ?? 0);
                $invPaid = (float)($invoice['paid_amount'] ?? 0);

                if ($payStatus !== 'paid' || ($invTotal - $invPaid) > 0.009) {
                    return [
                        'allowed' => false,
                        'message' => 'Cannot delete this booking. Payment is still pending.',
                        'reason'  => 'pending_customer_payment'
                    ];
                }
            }
        } else {
            $bookingTotal = (float)($booking['total_amount'] ?? 0);
            if ($bookingTotal > 0.009) {
                return [
                    'allowed' => false,
                    'message' => 'Cannot delete this booking. Payment is still pending.',
                    'reason'  => 'pending_customer_payment'
                ];
            }
        }

        // RULE 3: Related employee/driver trip payment is still pending
        foreach ($dispatches as $dispatch) {
            $payStatus = strtolower((string)($dispatch['driver_payment_status'] ?? 'not_due'));
            $tripAmount = $dispatch['driver_trip_amount'] !== null ? (float)$dispatch['driver_trip_amount'] : null;
            $paidAmount = (float)($dispatch['driver_paid_amount'] ?? 0);

            $isDriverPayPending = in_array($payStatus, ['due', 'partially_paid'], true)
                || ($tripAmount !== null && $tripAmount > 0 && ($tripAmount - $paidAmount) > 0.009);

            if ($isDriverPayPending) {
                return [
                    'allowed' => false,
                    'message' => 'Cannot delete this booking. Trip payment is still pending.',
                    'reason'  => 'pending_driver_trip_payment'
                ];
            }
        }

        // All checks passed
        return [
            'allowed' => true,
            'message' => 'Booking deleted successfully.',
            'reason'  => 'allowed'
        ];
    }
}
