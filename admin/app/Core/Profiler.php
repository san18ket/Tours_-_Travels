<?php
namespace App\Core;

/**
 * Enterprise Performance Profiler & Query Timing Diagnostics.
 * Tracks request execution time, query count, and flags slow queries (>= 100ms).
 * Strictly masks SQL in production to prevent technical leaks.
 */
class Profiler
{
    private static float $requestStartTime = 0.0;
    private static array $timers = [];
    private static int $queryCount = 0;
    private static float $totalQueryTimeMs = 0.0;
    private static array $slowQueries = [];
    private static array $devQueryLog = [];
    private const SLOW_QUERY_THRESHOLD_MS = 100.0;

    /**
     * Initialize profiler at bootstrap
     */
    public static function init(): void
    {
        if (self::$requestStartTime <= 0.0) {
            self::$requestStartTime = isset($_SERVER['REQUEST_TIME_FLOAT']) 
                ? (float)$_SERVER['REQUEST_TIME_FLOAT'] 
                : microtime(true);
        }
    }

    /**
     * Start a named timer
     */
    public static function startTimer(string $key): void
    {
        self::$timers[$key] = microtime(true);
    }

    /**
     * Stop a named timer and return duration in milliseconds
     */
    public static function stopTimer(string $key): float
    {
        if (!isset(self::$timers[$key])) {
            return 0.0;
        }
        $elapsed = (microtime(true) - self::$timers[$key]) * 1000;
        unset(self::$timers[$key]);
        return round($elapsed, 2);
    }

    /**
     * Record a database query execution
     */
    public static function recordQuery(string $sql, float $timeMs, array $params = []): void
    {
        self::$queryCount++;
        self::$totalQueryTimeMs += $timeMs;

        $isSlow = ($timeMs >= self::SLOW_QUERY_THRESHOLD_MS);

        if ($isSlow) {
            $maskedSql = self::maskSensitiveSql($sql);
            self::$slowQueries[] = [
                'time_ms' => round($timeMs, 2),
                'sql'     => $maskedSql,
            ];

            // Securely log slow query without leaking passwords or private data
            Logger::warning("Performance Notice: Slow database query ({$timeMs}ms) detected: {$maskedSql}");
        }

        // In local/development environments only, keep query log for diagnostics
        if (!Env::isProduction()) {
            if (count(self::$devQueryLog) < 100) { // Limit memory footprint
                self::$devQueryLog[] = [
                    'sql'     => self::maskSensitiveSql($sql),
                    'time_ms' => round($timeMs, 2),
                    'is_slow' => $isSlow,
                ];
            }
        }
    }

    /**
     * Measure and execute a callable database query
     */
    public static function profileQuery(string $sql, callable $execute, array $params = []): mixed
    {
        $start = microtime(true);
        try {
            return $execute();
        } finally {
            $durationMs = (microtime(true) - $start) * 1000;
            self::recordQuery($sql, $durationMs, $params);
        }
    }

    /**
     * Mask potential passwords or sensitive tokens from SQL strings
     */
    private static function maskSensitiveSql(string $sql): string
    {
        // Truncate excessively long query strings
        $cleaned = trim(preg_replace('/\s+/', ' ', $sql));
        if (strlen($cleaned) > 250) {
            $cleaned = substr($cleaned, 0, 250) . '...';
        }
        return preg_replace('/password\s*=\s*[\'"][^\'"]+[\'"]/i', 'password=\'***\'', $cleaned);
    }

    /**
     * Get aggregate performance diagnostic summary
     */
    public static function getSummary(): array
    {
        self::init();
        $totalElapsedMs = (microtime(true) - self::$requestStartTime) * 1000;

        return [
            'total_time_ms'       => round($totalElapsedMs, 2),
            'query_count'         => self::$queryCount,
            'total_query_time_ms' => round(self::$totalQueryTimeMs, 2),
            'slow_queries_count'  => count(self::$slowQueries),
            'slow_queries'        => self::$slowQueries,
            'is_production'       => Env::isProduction(),
        ];
    }

    /**
     * Attach safe timing headers to response in development mode or for admin users
     */
    public static function attachResponseHeaders(Response $response): void
    {
        $summary = self::getSummary();

        // In development or for authorized debug requests, attach timing headers
        if (!Env::isProduction() || (Session::isLoggedIn() && Session::getUserRole() === 'admin' && isset($_GET['_profile']))) {
            $response->setHeader('X-Response-Time-Ms', (string)$summary['total_time_ms']);
            $response->setHeader('X-DB-Query-Count', (string)$summary['query_count']);
            $response->setHeader('X-DB-Time-Ms', (string)$summary['total_query_time_ms']);
        }
    }

    /**
     * Reset profiler state (useful during automated testing)
     */
    public static function reset(): void
    {
        self::$requestStartTime = microtime(true);
        self::$timers = [];
        self::$queryCount = 0;
        self::$totalQueryTimeMs = 0.0;
        self::$slowQueries = [];
        self::$devQueryLog = [];
    }
}
