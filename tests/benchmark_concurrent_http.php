<?php
declare(strict_types=1);

/**
 * Real Concurrent HTTP Benchmark Suite
 * Spawns an isolated local server instance on port 8888 and executes real simultaneous
 * cURL multi-handle requests across concurrency levels C=1, 5, 10, 25, and 50.
 * Measures real RPS, average latency, p50, p95, p99, and HTTP status distributions.
 */

$phpBinary = 'E:\\xampp\\php\\php.exe';
$port = 8888;
$host = '127.0.0.1';
$docRoot = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public';

echo "========================================================================\n";
echo " REAL CONCURRENT HTTP BENCHMARK SUITE\n";
echo " PHP Built-in Server: http://{$host}:{$port}\n";
echo " Document Root:       {$docRoot}\n";
echo " Concurrency Levels:  C = [1, 5, 10, 25, 50]\n";
echo "========================================================================\n\n";

// 1. Launch PHP Built-in Server with front controller routing (pipe to NUL to prevent Windows pipe buffer deadlocks)
$routerScript = $docRoot . DIRECTORY_SEPARATOR . 'index.php';
$serverCmd = "\"{$phpBinary}\" -S {$host}:{$port} -t \"{$docRoot}\" \"{$routerScript}\"";
$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['file', 'NUL', 'w'],
    2 => ['file', 'NUL', 'w']
];

$serverProc = proc_open($serverCmd, $descriptors, $serverPipes);
if (!is_resource($serverProc)) {
    echo "ERROR: Failed to launch PHP server on port {$port}\n";
    exit(1);
}

// Give server time to bind and verify responsiveness
$serverReady = false;
$checkUrl = "http://{$host}:{$port}/manifest.json";
for ($i = 0; $i < 20; $i++) {
    usleep(150000); // 150ms
    $ctx = stream_context_create(['http' => ['timeout' => 1]]);
    $resp = @file_get_contents($checkUrl, false, $ctx);
    if ($resp !== false) {
        $serverReady = true;
        break;
    }
}

if (!$serverReady) {
    echo "ERROR: Server failed to respond on http://{$host}:{$port}/manifest.json\n";
    proc_terminate($serverProc);
    exit(1);
}

echo "✓ Local web server successfully started and listening on http://{$host}:{$port}\n\n";

/**
 * Execute concurrent requests using robust curl_multi
 */
function benchmarkConcurrency(string $url, int $concurrency, int $totalRequests, array $headers = []): array {
    $completed = 0;
    $errors = 0;
    $latencies = [];
    $statusCodes = [];

    $startTime = microtime(true);
    $runningTotal = 0;

    $mh = curl_multi_init();
    $handles = [];

    // Queue initial batch up to concurrency
    for ($i = 0; $i < min($concurrency, $totalRequests); $i++) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $handles[(int)$ch] = ['ch' => $ch, 'start' => microtime(true)];
        curl_multi_add_handle($mh, $ch);
        $runningTotal++;
    }

    do {
        $mrc = curl_multi_exec($mh, $active);
        if ($active) {
            if (curl_multi_select($mh, 0.05) === -1) {
                usleep(1000);
            }
        }

        while ($info = curl_multi_info_read($mh)) {
            $ch = $info['handle'];
            $chId = (int)$ch;
            $duration = (microtime(true) - $handles[$chId]['start']) * 1000;
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($info['result'] === CURLE_OK && $httpCode >= 200 && $httpCode < 400) {
                $completed++;
                $latencies[] = $duration;
                $statusCodes[$httpCode] = ($statusCodes[$httpCode] ?? 0) + 1;
            } else {
                $errors++;
            }

            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
            unset($handles[$chId]);

            // Add replacement handle if more requests remaining
            if ($runningTotal < $totalRequests) {
                $newCh = curl_init();
                curl_setopt($newCh, CURLOPT_URL, $url);
                curl_setopt($newCh, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($newCh, CURLOPT_TIMEOUT, 10);
                curl_setopt($newCh, CURLOPT_HTTPHEADER, $headers);
                $handles[(int)$newCh] = ['ch' => $newCh, 'start' => microtime(true)];
                curl_multi_add_handle($mh, $newCh);
                $runningTotal++;
            }
        }
    } while ($active > 0 || count($handles) > 0);

    curl_multi_close($mh);
    $totalElapsed = microtime(true) - $startTime;

    sort($latencies);
    $count = count($latencies);

    $avg = $count > 0 ? array_sum($latencies) / $count : 0;
    $min = $count > 0 ? $latencies[0] : 0;
    $max = $count > 0 ? $latencies[$count - 1] : 0;
    $p50 = $count > 0 ? $latencies[(int)floor($count * 0.50)] : 0;
    $p95 = $count > 0 ? $latencies[(int)floor($count * 0.95)] : 0;
    $p99 = $count > 0 ? $latencies[(int)floor($count * 0.99)] : 0;
    $rps = $totalElapsed > 0 ? $completed / $totalElapsed : 0;

    return [
        'concurrency'  => $concurrency,
        'total_reqs'   => $totalRequests,
        'completed'    => $completed,
        'errors'       => $errors,
        'elapsed_sec'  => round($totalElapsed, 3),
        'rps'          => round($rps, 1),
        'avg_ms'       => round($avg, 2),
        'min_ms'       => round($min, 2),
        'p50_ms'       => round($p50, 2),
        'p95_ms'       => round($p95, 2),
        'p99_ms'       => round($p99, 2),
        'max_ms'       => round($max, 2),
        'status_codes' => $statusCodes,
    ];
}

$testLevels = [1, 5, 10, 25, 50];
$requestsPerBatch = 50;
$targetUrl = "http://{$host}:{$port}/manifest.json";

echo "Running Benchmark against Static Asset ({$targetUrl}):\n";
echo sprintf("%-6s | %-8s | %-8s | %-8s | %-12s | %-9s | %-9s | %-9s | %-9s\n",
    "Conc", "Reqs", "Pass", "Errors", "Throughput", "Avg (ms)", "p50 (ms)", "p95 (ms)", "p99 (ms)");
echo str_repeat("-", 92) . "\n";

$staticResults = [];
foreach ($testLevels as $c) {
    $res = benchmarkConcurrency($targetUrl, $c, $requestsPerBatch);
    $staticResults[] = $res;
    echo sprintf("%-6d | %-8d | %-8d | %-8d | %-12s | %-9.2f | %-9.2f | %-9.2f | %-9.2f\n",
        $res['concurrency'],
        $res['total_reqs'],
        $res['completed'],
        $res['errors'],
        $res['rps'] . " rps",
        $res['avg_ms'],
        $res['p50_ms'],
        $res['p95_ms'],
        $res['p99_ms']
    );
}

// Benchmark Conditional 304 Validation against Public Reference Endpoint
echo "\nRunning Conditional ETag Validation Benchmark (HTTP 304 Not Modified):\n";
$etagUrl = "http://{$host}:{$port}/api/reference/app-config";
// First get fresh etag
$ch = curl_init($etagUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$rawResp = curl_exec($ch);
curl_close($ch);

$etag = '';
if (preg_match('/ETag:\s*"?([^"\r\n]+)"?/i', $rawResp, $m)) {
    $etag = trim($m[1], '"');
}

echo "Detected Endpoint ETag: \"{$etag}\"\n";
$conditionalHeaders = [
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest',
    'If-None-Match: "' . $etag . '"'
];

$res304 = benchmarkConcurrency($etagUrl, 10, 50, $conditionalHeaders);
echo sprintf("Conditional 304 Latency at C=10: Avg=%.2f ms, p50=%.2f ms, p95=%.2f ms, Throughput=%.1f rps (Status: %s)\n",
    $res304['avg_ms'],
    $res304['p50_ms'],
    $res304['p95_ms'],
    $res304['rps'],
    json_encode($res304['status_codes'])
);

// Clean up server
proc_terminate($serverProc);
echo "\n✓ Server shutdown cleanly.\n";
