<?php
namespace App\Core;

use Throwable;

class ErrorMessageTranslator
{
    /**
     * Translate any exception or technical error string into a short,
     * simple, human-friendly message for end-users.
     * Full technical details are preserved in the server/application log.
     *
     * @param Throwable|string $error
     * @param string|null $context Context entity, e.g. 'employee', 'customer', 'vehicle', 'booking', 'payment'
     * @param string|null $action Action attempted, e.g. 'delete', 'create', 'update', 'status'
     * @return string Short, human-friendly message
     */
    public static function translate(mixed $error, ?string $context = null, ?string $action = null): string
    {
        $rawMessage = '';
        $sqlState = '';
        $errorCode = 0;

        if ($error instanceof Throwable) {
            $rawMessage = $error->getMessage();
            $errorCode = (int)$error->getCode();

            // Log complete technical trace to developer log
            Logger::error("Technical exception [Context: {$context}, Action: {$action}]: " . $rawMessage, [
                'exception' => get_class($error),
                'code'      => $errorCode,
                'file'      => $error->getFile(),
                'line'      => $error->getLine(),
                'trace'     => $error->getTraceAsString(),
            ]);

            // Check if PDOException contains SQLSTATE
            if ($error instanceof \PDOException && isset($error->errorInfo[0])) {
                $sqlState = (string)$error->errorInfo[0];
            }
        } elseif (is_string($error)) {
            $rawMessage = $error;
        }

        $rawLower = strtolower($rawMessage);
        $contextLower = strtolower((string)$context);
        $actionLower = strtolower((string)$action);

        // ---------------------------------------------------------------------
        // 1. Duplicate Entry / Unique Key Constraint Violations (MySQL 1062)
        // ---------------------------------------------------------------------
        $isDuplicate = str_contains($rawLower, 'duplicate entry') ||
                       str_contains($rawLower, '1062') ||
                       str_contains($rawLower, 'unique constraint');

        if ($isDuplicate) {
            if (str_contains($rawLower, 'email')) {
                return 'This email is already in use.';
            }
            if (str_contains($rawLower, 'phone') || str_contains($rawLower, 'mobile')) {
                return 'This phone number is already in use.';
            }
            if (str_contains($rawLower, 'username')) {
                return 'This username is already taken.';
            }
            if (str_contains($rawLower, 'vehicle_number') || str_contains($rawLower, 'registration')) {
                return 'This vehicle number is already registered.';
            }
            if (str_contains($rawLower, 'code') || str_contains($rawLower, 'reference')) {
                return 'This reference code already exists.';
            }
            return 'This information is already in use.';
        }

        // ---------------------------------------------------------------------
        // 2. Null / Missing Required Field Violations (MySQL 1048, 1364)
        // ---------------------------------------------------------------------
        $isNotNull = str_contains($rawLower, 'cannot be null') ||
                     str_contains($rawLower, "doesn't have a default value") ||
                     str_contains($rawLower, '1048') ||
                     str_contains($rawLower, '1364');

        if ($isNotNull) {
            return 'Please enter the required information.';
        }

        // ---------------------------------------------------------------------
        // 3. Foreign Key / Constraint Violations (MySQL 1451, 1452)
        // ---------------------------------------------------------------------
        $isForeignKey = str_contains($rawLower, 'foreign key') ||
                        str_contains($rawLower, '1451') ||
                        str_contains($rawLower, '1452') ||
                        str_contains($rawLower, 'cannot delete or update a parent row') ||
                        str_contains($rawLower, 'integrity constraint');

        if ($isForeignKey) {
            // Check explicit context first
            if ($contextLower === 'employee') {
                return 'Cannot delete this employee. They have an active trip.';
            }
            if ($contextLower === 'customer') {
                return 'Cannot delete this customer. They have an active booking.';
            }
            if ($contextLower === 'vehicle') {
                return 'Cannot delete this vehicle because it is linked to a trip.';
            }
            if ($contextLower === 'booking') {
                return 'Cannot delete this booking. The trip is still active.';
            }
            if ($contextLower === 'user') {
                return 'Cannot delete this user because they have linked records.';
            }
            if ($contextLower === 'tour') {
                return 'Cannot delete this tour because it has existing bookings.';
            }
            if ($contextLower === 'role') {
                return 'Cannot delete this role because it is assigned to users.';
            }
            if ($contextLower === 'category' || str_contains($rawLower, 'category')) {
                return 'Cannot delete this category because it is currently in use.';
            }
            if ($contextLower === 'account' || str_contains($rawLower, 'account')) {
                return 'Cannot delete this account because it has recorded transactions.';
            }

            // Fallback to table/field hints in message
            if (str_contains($rawLower, 'driver') || str_contains($rawLower, 'employee')) {
                return 'Cannot delete this employee. They have an active trip.';
            }
            if (str_contains($rawLower, 'customer') || str_contains($rawLower, 'customer_profile')) {
                return 'Cannot delete this customer. They have an active booking.';
            }
            if (str_contains($rawLower, 'vehicle')) {
                return 'Cannot delete this vehicle because it is linked to a trip.';
            }
            if (str_contains($rawLower, 'booking') || str_contains($rawLower, 'invoice') || str_contains($rawLower, 'dispatch')) {
                return 'Cannot delete this booking. The trip is still active.';
            }

            return 'Cannot delete this record because it is linked to other data.';
        }

        // ---------------------------------------------------------------------
        // 4. Permission & Authorization Violations
        // ---------------------------------------------------------------------
        $isPermission = str_contains($rawLower, 'unauthorized') ||
                        str_contains($rawLower, 'forbidden') ||
                        str_contains($rawLower, '403') ||
                        str_contains($rawLower, 'access denied') ||
                        str_contains($rawLower, 'not authorized') ||
                        str_contains($rawLower, 'permission');

        if ($isPermission) {
            return "You don't have permission to do this.";
        }

        // ---------------------------------------------------------------------
        // 5. CSRF / Session Timeout
        // ---------------------------------------------------------------------
        if (str_contains($rawLower, 'csrf') || str_contains($rawLower, 'token expired') || str_contains($rawLower, 'session expired')) {
            return 'Your session has expired. Please refresh the page and try again.';
        }

        // ---------------------------------------------------------------------
        // 6. If it's already a clean, short human message (no technical jargon)
        // ---------------------------------------------------------------------
        if (self::isAlreadyHumanFriendly($rawMessage)) {
            return trim($rawMessage);
        }

        // ---------------------------------------------------------------------
        // 7. Fallback for all other raw technical exceptions
        // ---------------------------------------------------------------------
        return 'Something went wrong. Please try again.';
    }

    /**
     * Check if a message is already a short, clean, human-friendly message
     * and does NOT contain raw technical leakages.
     */
    public static function isAlreadyHumanFriendly(string $msg): bool
    {
        $len = strlen($msg);
        if ($len === 0 || $len > 250) {
            return false;
        }

        $lower = strtolower($msg);
        $technicalPatterns = [
            'sqlstate',
            'integrity constraint',
            'syntax error',
            'stack trace',
            'uncaught exception',
            'pdoexception',
            'runtimeexception',
            'fatal error',
            'parse error',
            'select ',
            'insert into',
            'update ',
            'delete from',
            'where ',
            'table \'',
            'column \'',
            'foreign key',
            'errno:',
            'call to undefined',
            'in /',
            'in c:\\',
            'in e:\\',
            'on line ',
            '.php:',
            'query failed',
            'connection refused',
            'access denied for user',
        ];

        foreach ($technicalPatterns as $pattern) {
            if (str_contains($lower, $pattern)) {
                return false;
            }
        }

        return true;
    }
}
