<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use App\Models\FinanceTransaction;
use App\Models\AuditLog;
use RuntimeException;
use PDO;

class DriverTripPayment extends Model
{
    protected string $table = 'driver_trip_payments';
    protected array $fillable = [
        'payment_reference',
        'receipt_number',
        'dispatch_id',
        'driver_id',
        'employee_id',
        'booking_id',
        'account_id',
        'amount',
        'trip_amount_snapshot',
        'previous_paid_amount',
        'total_paid_after',
        'remaining_after',
        'payment_method',
        'transaction_ref',
        'payment_date',
        'notes',
        'attachment_file',
        'status',
        'reversed_by',
        'reversed_at',
        'reversal_reason',
        'created_by',
    ];

    /**
     * Get paginated driver trip payments with joined driver, employee, dispatch, booking, and account information
     */
    public function getPaymentsWithDetails(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $pdo = $this->getPdo();
        [$sqlWhere, $params] = $this->buildFilterConditions($filters, 'dtp');

        $sql = "SELECT 
                    dtp.*,
                    d.dispatch_code,
                    d.status AS dispatch_status,
                    d.driver_trip_amount,
                    d.driver_paid_amount,
                    d.driver_payment_status,
                    b.booking_code,
                    b.pickup_address,
                    b.drop_address,
                    b.start_date AS trip_date,
                    u.name AS driver_name,
                    u.phone AS driver_phone,
                    dp.license_number,
                    emp.employee_code,
                    emp.first_name,
                    emp.last_name,
                    emp.designation,
                    v.vehicle_number,
                    v.make_model,
                    fa.account_name,
                    fa.account_type,
                    creator.name AS created_by_name,
                    reverser.name AS reversed_by_name
                FROM `driver_trip_payments` dtp
                INNER JOIN `dispatches` d ON dtp.dispatch_id = d.id
                LEFT JOIN `driver_profiles` dp ON dtp.driver_id = dp.id
                LEFT JOIN `users` u ON dp.user_id = u.id
                LEFT JOIN `employees` emp ON (dtp.employee_id = emp.id OR dp.employee_id = emp.id)
                LEFT JOIN `bookings` b ON d.booking_id = b.id
                LEFT JOIN `vehicles` v ON d.vehicle_id = v.id
                LEFT JOIN `finance_accounts` fa ON dtp.account_id = fa.id
                LEFT JOIN `users` creator ON dtp.created_by = creator.id
                LEFT JOIN `users` reverser ON dtp.reversed_by = reverser.id
                WHERE {$sqlWhere}
                ORDER BY dtp.payment_date DESC, dtp.id DESC
                LIMIT ? OFFSET ?";

        $stmt = $pdo->prepare($sql);
        $paramIdx = 1;
        foreach ($params as $p) {
            $stmt->bindValue($paramIdx++, $p);
        }
        $stmt->bindValue($paramIdx++, $limit, PDO::PARAM_INT);
        $stmt->bindValue($paramIdx++, $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count total payment records matching filters
     */
    public function countPayments(array $filters = []): int
    {
        $pdo = $this->getPdo();
        [$sqlWhere, $params] = $this->buildFilterConditions($filters, 'dtp');

        $sql = "SELECT COUNT(*)
                FROM `driver_trip_payments` dtp
                INNER JOIN `dispatches` d ON dtp.dispatch_id = d.id
                LEFT JOIN `driver_profiles` dp ON dtp.driver_id = dp.id
                LEFT JOIN `users` u ON dp.user_id = u.id
                LEFT JOIN `employees` emp ON (dtp.employee_id = emp.id OR dp.employee_id = emp.id)
                LEFT JOIN `bookings` b ON d.booking_id = b.id
                WHERE {$sqlWhere}";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Calculate summary aggregates for driver trip payments matching filters
     */
    public function getPaymentTotals(array $filters = []): array
    {
        $pdo = $this->getPdo();
        [$sqlWhere, $params] = $this->buildFilterConditions($filters, 'dtp');

        $sql = "SELECT 
                    COALESCE(SUM(dtp.amount), 0) AS total_paid_sum,
                    COUNT(dtp.id) AS total_count
                FROM `driver_trip_payments` dtp
                INNER JOIN `dispatches` d ON dtp.dispatch_id = d.id
                LEFT JOIN `driver_profiles` dp ON dtp.driver_id = dp.id
                LEFT JOIN `users` u ON dp.user_id = u.id
                LEFT JOIN `employees` emp ON (dtp.employee_id = emp.id OR dp.employee_id = emp.id)
                LEFT JOIN `bookings` b ON d.booking_id = b.id
                WHERE {$sqlWhere} AND dtp.status = 'completed'";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_paid_sum' => 0.0, 'total_count' => 0];
    }

    /**
     * Get all payment transaction vouchers for a specific dispatch
     */
    public function getPaymentsByDispatch(int $dispatchId): array
    {
        $pdo = $this->getPdo();
        $sql = "SELECT dtp.*, fa.account_name, fa.account_type, creator.name AS created_by_name, reverser.name AS reversed_by_name
                FROM `driver_trip_payments` dtp
                LEFT JOIN `finance_accounts` fa ON dtp.account_id = fa.id
                LEFT JOIN `users` creator ON dtp.created_by = creator.id
                LEFT JOIN `users` reverser ON dtp.reversed_by = reverser.id
                WHERE dtp.dispatch_id = ?
                ORDER BY dtp.payment_date DESC, dtp.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dispatchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all trip payment records for an authenticated driver
     */
    public function getPaymentsByDriver(int $driverId, int $limit = 50, int $offset = 0): array
    {
        return $this->getPaymentsWithDetails(['driver_id' => $driverId], $limit, $offset);
    }

    /**
     * Get all trip payment records for an employee
     */
    public function getPaymentsByEmployee(int $employeeId, int $limit = 50, int $offset = 0): array
    {
        return $this->getPaymentsWithDetails(['employee_id' => $employeeId], $limit, $offset);
    }

    /**
     * Get executive financial summary for a driver (Earnings, Paid, Pending, Counts)
     */
    public function getDriverFinancialSummary(int $driverId): array
    {
        $pdo = $this->getPdo();

        // 1. Total completed trips & total earned from dispatches
        $stmt = $pdo->prepare("SELECT 
                    COUNT(id) AS total_trips,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_trips,
                    COALESCE(SUM(CASE WHEN status = 'completed' AND driver_trip_amount IS NOT NULL THEN driver_trip_amount ELSE 0 END), 0) AS total_earned,
                    COALESCE(SUM(driver_paid_amount), 0) AS total_paid
                FROM `dispatches`
                WHERE driver_id = ?");
        $stmt->execute([$driverId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalEarned = (float)($row['total_earned'] ?? 0);
        $totalPaid   = (float)($row['total_paid'] ?? 0);
        $totalPending = max(0.0, $totalEarned - $totalPaid);

        // Dispatches with unspecified driver amount
        $stmtUnspecified = $pdo->prepare("SELECT COUNT(*) FROM `dispatches` WHERE driver_id = ? AND status = 'completed' AND driver_trip_amount IS NULL");
        $stmtUnspecified->execute([$driverId]);
        $unspecifiedCount = (int)$stmtUnspecified->fetchColumn();

        return [
            'total_trips'       => (int)($row['total_trips'] ?? 0),
            'completed_trips'   => (int)($row['completed_trips'] ?? 0),
            'total_earned'      => $totalEarned,
            'total_paid'        => $totalPaid,
            'total_pending'     => $totalPending,
            'unspecified_count' => $unspecifiedCount,
        ];
    }

    /**
     * Get employee combined trip earnings summary
     */
    public function getEmployeeTripEarningsSummary(int $employeeId): array
    {
        $pdo = $this->getPdo();
        // Resolve driver profile id for this employee if any
        $stmt = $pdo->prepare("SELECT id FROM `driver_profiles` WHERE employee_id = ? LIMIT 1");
        $stmt->execute([$employeeId]);
        $driverId = $stmt->fetchColumn();

        if (!$driverId) {
            return [
                'has_driver_profile' => false,
                'total_trips'        => 0,
                'completed_trips'    => 0,
                'total_earned'       => 0.0,
                'total_paid'         => 0.0,
                'total_pending'      => 0.0,
                'unspecified_count'  => 0,
            ];
        }

        $summary = $this->getDriverFinancialSummary((int)$driverId);
        $summary['has_driver_profile'] = true;
        $summary['driver_id'] = (int)$driverId;
        return $summary;
    }

    /**
     * Query company-wide summary for finance dashboard:
     * Returns total pending completed driver trip payments
     */
    public function getCompanyPendingDriverTripPayments(): float
    {
        $pdo = $this->getPdo();
        $sql = "SELECT COALESCE(SUM(driver_trip_amount - driver_paid_amount), 0)
                FROM `dispatches`
                WHERE `status` = 'completed'
                  AND `driver_trip_amount` IS NOT NULL
                  AND `driver_trip_amount` > `driver_paid_amount`";
        return (float)($pdo->query($sql)->fetchColumn() ?: 0.0);
    }

    /**
     * Atomically generate a unique, sequential professional receipt number.
     * Pattern: DPR-YYYY-XXXXX (e.g. DPR-2026-00001)
     */
    public function generateReceiptNumber(?int $year = null): string
    {
        $year = $year ?: (int)date('Y');
        $pdo = $this->getPdo();
        $prefix = "DPR-{$year}-";
        $stmt = $pdo->prepare("SELECT receipt_number FROM `driver_trip_payments` WHERE `receipt_number` LIKE ? ORDER BY `id` DESC LIMIT 1");
        $stmt->execute([$prefix . '%']);
        $lastReceipt = $stmt->fetchColumn();

        $nextSeq = 1;
        if ($lastReceipt && preg_match('/^DPR-\d{4}-(\d+)$/', $lastReceipt, $m)) {
            $nextSeq = ((int)$m[1]) + 1;
        } else {
            $stmtMax = $pdo->query("SELECT COUNT(*) FROM `driver_trip_payments`");
            $count = (int)$stmtMax->fetchColumn();
            $nextSeq = max(1, $count + 1);
        }

        // Check if collision exists (defensive check)
        do {
            $candidate = sprintf('DPR-%d-%05d', $year, $nextSeq);
            $chk = $pdo->prepare("SELECT 1 FROM `driver_trip_payments` WHERE `receipt_number` = ? LIMIT 1");
            $chk->execute([$candidate]);
            if ($chk->fetchColumn()) {
                $nextSeq++;
            } else {
                break;
            }
        } while (true);

        return $candidate;
    }

    /**
     * Fetch complete details of a driver payment receipt for professional presentation / print
     */
    public function findWithFullReceiptDetails(int $id): ?array
    {
        $pdo = $this->getPdo();
        $sql = "SELECT 
                    dtp.*,
                    d.dispatch_code,
                    d.status AS dispatch_status,
                    d.driver_trip_amount,
                    d.driver_paid_amount,
                    d.driver_payment_status,
                    d.start_time,
                    d.end_time,
                    d.start_odometer,
                    d.end_odometer,
                    d.driver_notes,
                    b.booking_code,
                    b.pickup_address,
                    b.drop_address,
                    b.start_date AS trip_date,
                    b.passenger_count,
                    u.name AS driver_name,
                    u.phone AS driver_phone,
                    u.email AS driver_email,
                    dp.license_number,
                    dp.experience_years,
                    emp.employee_code,
                    emp.first_name,
                    emp.last_name,
                    emp.designation,
                    emp.department,
                    v.vehicle_number,
                    v.make_model,
                    v.vehicle_type,
                    fa.account_name,
                    fa.account_type,
                    fa.bank_name,
                    fa.account_number,
                    creator.name AS created_by_name,
                    creator.email AS created_by_email,
                    reverser.name AS reversed_by_name
                FROM `driver_trip_payments` dtp
                INNER JOIN `dispatches` d ON dtp.dispatch_id = d.id
                LEFT JOIN `driver_profiles` dp ON dtp.driver_id = dp.id
                LEFT JOIN `users` u ON dp.user_id = u.id
                LEFT JOIN `employees` emp ON (dtp.employee_id = emp.id OR dp.employee_id = emp.id)
                LEFT JOIN `bookings` b ON d.booking_id = b.id
                LEFT JOIN `vehicles` v ON d.vehicle_id = v.id
                LEFT JOIN `finance_accounts` fa ON dtp.account_id = fa.id
                LEFT JOIN `users` creator ON dtp.created_by = creator.id
                LEFT JOIN `users` reverser ON dtp.reversed_by = reverser.id
                WHERE dtp.id = ?
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Find payment receipt by unique receipt number or payment reference
     */
    public function findByReceiptNumber(string $identifier): ?array
    {
        $pdo = $this->getPdo();
        $stmt = $pdo->prepare("SELECT id FROM `driver_trip_payments` WHERE `receipt_number` = ? OR `payment_reference` = ? LIMIT 1");
        $stmt->execute([$identifier, $identifier]);
        $id = $stmt->fetchColumn();
        return $id ? $this->findWithFullReceiptDetails((int)$id) : null;
    }

    /**
     * Get all eligible completed trips with outstanding dues for a specific driver
     */
    public function getEligibleTripsForDriver(int $driverId): array
    {
        $pdo = $this->getPdo();
        $sql = "SELECT 
                    d.id,
                    d.id AS dispatch_id,
                    d.dispatch_code,
                    d.driver_id,
                    d.booking_id,
                    d.driver_trip_amount,
                    d.driver_paid_amount,
                    d.driver_payment_status,
                    d.start_time,
                    d.end_time,
                    b.booking_code,
                    b.pickup_address,
                    b.drop_address,
                    b.start_date AS trip_date,
                    v.vehicle_number,
                    v.make_model,
                    ROUND(d.driver_trip_amount - d.driver_paid_amount, 2) AS remaining_amount,
                    ROUND(d.driver_trip_amount - d.driver_paid_amount, 2) AS still_to_pay
                FROM `dispatches` d
                LEFT JOIN `bookings` b ON d.booking_id = b.id
                LEFT JOIN `vehicles` v ON d.vehicle_id = v.id
                WHERE d.driver_id = ?
                  AND d.status = 'completed'
                  AND d.driver_trip_amount IS NOT NULL
                  AND (d.driver_trip_amount - d.driver_paid_amount) > 0.009
                ORDER BY d.end_time DESC, d.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$driverId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search drivers with information on their pending trips & still to pay amounts
     */
    public function searchDriversWithDues(string $search = ''): array
    {
        $pdo = $this->getPdo();
        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $term = '%' . trim($search) . '%';
            $where[] = "(u.name LIKE ? OR u.phone LIKE ? OR emp.employee_code LIKE ? OR dp.license_number LIKE ?)";
            $params = [$term, $term, $term, $term];
        }

        $sqlWhere = implode(' AND ', $where);

        $sql = "SELECT 
                    dp.id,
                    dp.id AS driver_id,
                    u.name AS driver_name,
                    u.phone AS driver_phone,
                    emp.employee_code,
                    dp.license_number,
                    COALESCE(pending.pending_trips_count, 0) AS pending_trips_count,
                    COALESCE(pending.total_still_to_pay, 0) AS total_still_to_pay,
                    COALESCE(pending.total_still_to_pay, 0) AS total_due
                FROM `driver_profiles` dp
                JOIN `users` u ON dp.user_id = u.id
                LEFT JOIN `employees` emp ON dp.employee_id = emp.id
                LEFT JOIN (
                    SELECT 
                        driver_id,
                        COUNT(id) AS pending_trips_count,
                        SUM(driver_trip_amount - driver_paid_amount) AS total_still_to_pay
                    FROM `dispatches`
                    WHERE `status` = 'completed' 
                      AND `driver_trip_amount` IS NOT NULL 
                      AND (driver_trip_amount - driver_paid_amount) > 0.009
                    GROUP BY driver_id
                ) pending ON dp.id = pending.driver_id
                WHERE {$sqlWhere}
                ORDER BY pending.total_still_to_pay DESC, u.name ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Atomically disburse a partial or full payment against a completed driver trip payable.
     * Prevents overpayment with row-level locking (SELECT FOR UPDATE).
     * Integrates with company cash/bank accounts & finance_ledger.
     * Generates immutable audit trail and unique receipt number.
     */
    public function recordPayment(array $data): array
    {
        $pdo = $this->getPdo();
        $inTx = $pdo->inTransaction();
        if (!$inTx) {
            $pdo->beginTransaction();
        }

        try {
            $dispatchId = (int)($data['dispatch_id'] ?? 0);
            $rawAmount = (float)($data['amount'] ?? 0);
            if ($rawAmount <= 0) {
                throw new RuntimeException("Payment amount must be greater than zero.");
            }
            $amountToPay = round($rawAmount, 2);
            $paymentMethod = trim((string)($data['payment_method'] ?? 'bank_transfer'));
            $accountId = !empty($data['account_id']) ? (int)$data['account_id'] : null;
            $paymentDate = !empty($data['payment_date']) ? trim((string)$data['payment_date']) : date('Y-m-d');
            $txRef = !empty($data['transaction_ref']) ? trim((string)$data['transaction_ref']) : null;
            $notes = !empty($data['notes']) ? trim((string)$data['notes']) : null;
            $attachmentFile = !empty($data['attachment_file']) ? trim((string)$data['attachment_file']) : null;
            $userId = !empty($data['created_by']) ? (int)$data['created_by'] : null;

            if ($dispatchId <= 0) {
                throw new RuntimeException("A valid trip dispatch reference is required.");
            }

            // 1. Lock dispatch row FOR UPDATE to prevent race conditions & concurrent overpayment
            $stmt = $pdo->prepare("SELECT * FROM `dispatches` WHERE `id` = ? FOR UPDATE");
            $stmt->execute([$dispatchId]);
            $dispatch = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$dispatch) {
                throw new RuntimeException("Trip assignment #{$dispatchId} does not exist.");
            }

            if ($dispatch['status'] !== 'completed') {
                throw new RuntimeException("Driver payment cannot be recorded for a trip with status '{$dispatch['status']}'. Trip must be completed first.");
            }

            if ($dispatch['driver_trip_amount'] === null) {
                throw new RuntimeException("Cannot record payment: Agreed Driver Trip Amount has not been specified for this completed trip. Please specify the amount first.");
            }

            $totalEarned  = (float)$dispatch['driver_trip_amount'];
            $alreadyPaid  = (float)$dispatch['driver_paid_amount'];
            $remainingDue = round(max(0.0, $totalEarned - $alreadyPaid), 2);

            if ($remainingDue <= 0.0) {
                throw new RuntimeException("This driver trip payable has already been fully paid (Paid: " . format_inr($alreadyPaid) . ").");
            }

            // Strict server-side overpayment defense
            if ($amountToPay > $remainingDue) {
                throw new RuntimeException("Payment amount (" . format_inr($amountToPay) . ") cannot exceed the remaining balance (" . format_inr($remainingDue) . ").");
            }

            // Financial snapshots at time of payment
            $newPaidTotal = round($alreadyPaid + $amountToPay, 2);
            $newRemaining = round(max(0.0, $totalEarned - $newPaidTotal), 2);
            $newPaymentStatus = ($newRemaining <= 0.0) ? 'paid' : 'partially_paid';

            // Determine driver and employee identity
            $driverId = (int)$dispatch['driver_id'];
            $stmtDriver = $pdo->prepare("SELECT user_id, employee_id FROM `driver_profiles` WHERE `id` = ?");
            $stmtDriver->execute([$driverId]);
            $driverProf = $stmtDriver->fetch(PDO::FETCH_ASSOC);
            $employeeId = !empty($driverProf['employee_id']) ? (int)$driverProf['employee_id'] : null;
            $bookingId  = !empty($dispatch['booking_id']) ? (int)$dispatch['booking_id'] : null;

            // Generate unique payment voucher & receipt reference
            $voucherRef = 'DTR-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
            $receiptNumber = $this->generateReceiptNumber((int)date('Y', strtotime($paymentDate)));

            // 2. Insert payment record into driver_trip_payments with financial snapshots
            $now = date('Y-m-d H:i:s');
            $insStmt = $pdo->prepare("INSERT INTO `driver_trip_payments` (
                `payment_reference`, `receipt_number`, `dispatch_id`, `driver_id`, `employee_id`, `booking_id`,
                `account_id`, `amount`, `trip_amount_snapshot`, `previous_paid_amount`, `total_paid_after`, `remaining_after`,
                `payment_method`, `transaction_ref`, `payment_date`, `notes`, `attachment_file`, `status`,
                `created_by`, `created_at`, `updated_at`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed', ?, ?, ?)");

            $insStmt->execute([
                $voucherRef,
                $receiptNumber,
                $dispatchId,
                $driverId,
                $employeeId,
                $bookingId,
                $accountId,
                $amountToPay,
                $totalEarned,
                $alreadyPaid,
                $newPaidTotal,
                $newRemaining,
                $paymentMethod,
                $txRef,
                $paymentDate,
                $notes,
                $attachmentFile,
                $userId,
                $now,
                $now
            ]);

            $paymentId = (int)$pdo->lastInsertId();

            // 3. Update dispatch driver paid amount and payment status
            $updDispatch = $pdo->prepare("UPDATE `dispatches` SET 
                `driver_paid_amount` = ?, 
                `driver_payment_status` = ?, 
                `updated_at` = ? 
                WHERE `id` = ?");
            $updDispatch->execute([$newPaidTotal, $newPaymentStatus, $now, $dispatchId]);

            // 4. Actual company cash/bank movement via FinanceTransaction & finance_ledger
            $ledgerTxId = null;
            if ($accountId !== null && $accountId > 0) {
                // Fetch driver employee name for ledger description
                $driverName = 'Driver #' . $driverId;
                if (!empty($driverProf['user_id'])) {
                    $stmtU = $pdo->prepare("SELECT name FROM `users` WHERE `id` = ?");
                    $stmtU->execute([(int)$driverProf['user_id']]);
                    $nameVal = $stmtU->fetchColumn();
                    if ($nameVal) $driverName = $nameVal;
                }

                $financeTxModel = new FinanceTransaction();
                $ledgerTxId = $financeTxModel->recordTransaction([
                    'account_id'       => $accountId,
                    'transaction_type' => 'debit',
                    'amount'           => $amountToPay,
                    'category'         => 'Driver Trip Settlement',
                    'reference_type'   => 'driver_trip_payment',
                    'reference_id'     => $paymentId,
                    'payment_method'   => $paymentMethod,
                    'transaction_ref'  => $txRef ?: $receiptNumber,
                    'notes'            => "Driver Payment Receipt {$receiptNumber} ({$voucherRef}) to {$driverName} for Trip #{$dispatch['dispatch_code']}. " . ($notes ?? ''),
                    'created_by'       => $userId,
                    'transacted_at'    => $paymentDate . ' ' . date('H:i:s'),
                ]);
            }

            // 5. Audit trail
            $auditLog = new AuditLog();
            $actionName = ($newPaymentStatus === 'paid') ? 'driver_payment_completed' : 'driver_partial_payment';
            $auditLog->record(
                $actionName,
                'driver_trip_payments',
                $paymentId,
                [
                    'dispatch_id'   => $dispatchId,
                    'previous_paid' => $alreadyPaid,
                    'previous_stat' => $dispatch['driver_payment_status'],
                ],
                [
                    'receipt_number'=> $receiptNumber,
                    'amount_paid'   => $amountToPay,
                    'new_total_paid'=> $newPaidTotal,
                    'remaining'     => $newRemaining,
                    'status'        => $newPaymentStatus,
                    'account_id'    => $accountId,
                    'voucher_ref'   => $voucherRef,
                ],
                $userId
            );

            if (!$inTx) {
                $pdo->commit();
            }

            return [
                'success'              => true,
                'id'                   => $paymentId,
                'payment_id'           => $paymentId,
                'receipt_number'       => $receiptNumber,
                'payment_reference'    => $voucherRef,
                'dispatch_id'          => $dispatchId,
                'amount_paid'          => $amountToPay,
                'total_earned'         => $totalEarned,
                'trip_amount_snapshot' => $totalEarned,
                'previous_paid'        => $alreadyPaid,
                'previous_paid_amount' => $alreadyPaid,
                'new_total_paid'       => $newPaidTotal,
                'total_paid_after'     => $newPaidTotal,
                'remaining'            => $newRemaining,
                'remaining_after'      => $newRemaining,
                'payment_status'       => $newPaymentStatus,
                'ledger_tx_id'         => $ledgerTxId,
            ];
        } catch (\Throwable $e) {
            if (!$inTx && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Atomically reverse an issued driver payment voucher.
     * Restores company cash/bank balance, recalculates dispatch total paid & payment status,
     * updates financial ledger, and creates an audit trail.
     */
    public function reversePayment(int $paymentId, ?string $reason = null, ?int $userId = null): array
    {
        $pdo = $this->getPdo();
        $inTx = $pdo->inTransaction();
        if (!$inTx) {
            $pdo->beginTransaction();
        }

        try {
            if ($paymentId <= 0) {
                throw new RuntimeException("Invalid payment voucher reference.");
            }

            $reason = trim((string)$reason);
            if (empty($reason)) {
                throw new RuntimeException("A reversal reason is required to reverse a recorded payment.");
            }

            // 1. Lock payment voucher FOR UPDATE
            $stmt = $pdo->prepare("SELECT * FROM `driver_trip_payments` WHERE `id` = ? FOR UPDATE");
            $stmt->execute([$paymentId]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) {
                throw new RuntimeException("Payment voucher #{$paymentId} does not exist.");
            }

            if (($payment['status'] ?? '') === 'reversed') {
                throw new RuntimeException("Payment receipt #{$payment['receipt_number']} ({$payment['payment_reference']}) has already been reversed.");
            }

            $dispatchId = (int)$payment['dispatch_id'];
            $amountToReverse = round(abs((float)$payment['amount']), 2);
            $accountId = !empty($payment['account_id']) ? (int)$payment['account_id'] : null;
            $voucherRef = $payment['payment_reference'];
            $receiptNumber = $payment['receipt_number'] ?? $voucherRef;

            // 2. Lock dispatch row FOR UPDATE
            $stmtDisp = $pdo->prepare("SELECT * FROM `dispatches` WHERE `id` = ? FOR UPDATE");
            $stmtDisp->execute([$dispatchId]);
            $dispatch = $stmtDisp->fetch(PDO::FETCH_ASSOC);

            if (!$dispatch) {
                throw new RuntimeException("Associated trip dispatch #{$dispatchId} does not exist.");
            }

            $totalEarned = $dispatch['driver_trip_amount'] !== null ? (float)$dispatch['driver_trip_amount'] : null;
            $currentPaid = (float)$dispatch['driver_paid_amount'];

            // 3. Mark payment voucher as reversed with audit metadata
            $now = date('Y-m-d H:i:s');
            $updPay = $pdo->prepare("UPDATE `driver_trip_payments` SET 
                `status` = 'reversed', 
                `reversed_by` = ?, 
                `reversed_at` = ?, 
                `reversal_reason` = ?, 
                `updated_at` = ? 
                WHERE `id` = ?");
            $updPay->execute([$userId, $now, $reason, $now, $paymentId]);

            // 4. Recalculate total paid from all remaining completed payment vouchers for this dispatch
            $stmtSum = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM `driver_trip_payments` WHERE `dispatch_id` = ? AND `status` = 'completed'");
            $stmtSum->execute([$dispatchId]);
            $newPaidTotal = round((float)$stmtSum->fetchColumn(), 2);

            // 5. Determine new status based on authoritative financial state model
            $newRemaining = ($totalEarned !== null) ? round(max(0.0, $totalEarned - $newPaidTotal), 2) : null;
            if ($totalEarned === null || $totalEarned <= 0) {
                $newPaymentStatus = 'amount_not_specified';
            } elseif ($newRemaining <= 0.0) {
                $newPaymentStatus = 'paid';
            } elseif ($newPaidTotal > 0.0) {
                $newPaymentStatus = 'partially_paid';
            } else {
                $newPaymentStatus = 'due';
            }

            $updDisp = $pdo->prepare("UPDATE `dispatches` SET 
                `driver_paid_amount` = ?, 
                `driver_payment_status` = ?, 
                `updated_at` = ? 
                WHERE `id` = ?");
            $updDisp->execute([$newPaidTotal, $newPaymentStatus, $now, $dispatchId]);

            // 6. Restore funds to company cash/bank account & finance ledger
            $ledgerReversalId = null;
            if ($accountId !== null && $accountId > 0) {
                $financeTxModel = new FinanceTransaction();
                $ledgerReversalId = $financeTxModel->recordTransaction([
                    'account_id'       => $accountId,
                    'transaction_type' => 'credit',
                    'amount'           => $amountToReverse,
                    'category'         => 'Driver Payment Reversal',
                    'reference_type'   => 'driver_trip_payment_reversal',
                    'reference_id'     => $paymentId,
                    'payment_method'   => $payment['payment_method'] ?? 'bank_transfer',
                    'transaction_ref'  => 'REV-' . $receiptNumber,
                    'notes'            => "Reversal of Driver Payment Receipt {$receiptNumber} (Trip #{$dispatch['dispatch_code']}). Reason: {$reason}",
                    'created_by'       => $userId,
                    'transacted_at'    => $now,
                ]);
            }

            // 7. Record audit log
            $auditLog = new AuditLog();
            $auditLog->record(
                'driver_payment_reversed',
                'driver_trip_payments',
                $paymentId,
                [
                    'dispatch_id'   => $dispatchId,
                    'voucher_ref'   => $voucherRef,
                    'receipt_number'=> $receiptNumber,
                    'amount'        => $amountToReverse,
                    'previous_paid' => $currentPaid,
                    'previous_stat' => $dispatch['driver_payment_status'],
                ],
                [
                    'new_total_paid'=> $newPaidTotal,
                    'remaining'     => $newRemaining,
                    'new_status'    => $newPaymentStatus,
                    'reversed_by'   => $userId,
                    'reason'        => $reason,
                ],
                $userId
            );

            if (!$inTx) {
                $pdo->commit();
            }

            return [
                'success'           => true,
                'payment_id'        => $paymentId,
                'receipt_number'    => $receiptNumber,
                'voucher_ref'       => $voucherRef,
                'dispatch_id'       => $dispatchId,
                'amount_reversed'   => $amountToReverse,
                'new_total_paid'    => $newPaidTotal,
                'remaining'         => $newRemaining,
                'payment_status'    => $newPaymentStatus,
                'ledger_reversal_id'=> $ledgerReversalId,
            ];
        } catch (\Throwable $e) {
            if (!$inTx && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Build standard SQL WHERE clauses and bound parameter arrays for payments
     */
    private function buildFilterConditions(array $filters, string $alias = 'dtp'): array
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['driver_id'])) {
            $where[] = "{$alias}.driver_id = ?";
            $params[] = (int)$filters['driver_id'];
        }

        if (!empty($filters['employee_id'])) {
            $where[] = "({$alias}.employee_id = ? OR dp.employee_id = ?)";
            $params[] = (int)$filters['employee_id'];
            $params[] = (int)$filters['employee_id'];
        }

        if (!empty($filters['dispatch_id'])) {
            $where[] = "{$alias}.dispatch_id = ?";
            $params[] = (int)$filters['dispatch_id'];
        }

        if (!empty($filters['account_id'])) {
            $where[] = "{$alias}.account_id = ?";
            $params[] = (int)$filters['account_id'];
        }

        if (!empty($filters['status'])) {
            $where[] = "{$alias}.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['payment_method'])) {
            $where[] = "{$alias}.payment_method = ?";
            $params[] = $filters['payment_method'];
        }

        if (!empty($filters['receipt_number'])) {
            $where[] = "({$alias}.receipt_number LIKE ? OR {$alias}.payment_reference LIKE ?)";
            $params[] = '%' . trim($filters['receipt_number']) . '%';
            $params[] = '%' . trim($filters['receipt_number']) . '%';
        }

        if (!empty($filters['from_date'])) {
            $where[] = "{$alias}.payment_date >= ?";
            $params[] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $where[] = "{$alias}.payment_date <= ?";
            $params[] = $filters['to_date'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "({$alias}.receipt_number LIKE ? OR {$alias}.payment_reference LIKE ? OR {$alias}.transaction_ref LIKE ? OR d.dispatch_code LIKE ? OR b.booking_code LIKE ? OR u.name LIKE ? OR emp.first_name LIKE ? OR emp.last_name LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        return [implode(' AND ', $where), $params];
    }
}
