const http = require('http');
const fs = require('fs');
const path = require('path');
const { spawn } = require('child_process');

const PORT = 9876;
const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const PROJECT_ROOT = path.resolve(__dirname, '..');

console.log('========================================================================');
console.log(' REAL BROWSER INDEXEDDB BENCHMARK (GOOGLE CHROME ENGINE)');
console.log(' Chrome Executable: ' + CHROME_PATH);
console.log(' Server Endpoint:   http://127.0.0.1:' + PORT);
console.log('========================================================================\n');

// 1. Create temporary HTTP server to serve test runner & capture benchmark results
const server = http.createServer((req, res) => {
    if (req.method === 'GET') {
        let filePath = '';
        if (req.url === '/' || req.url === '/index.html') {
            filePath = path.join(__dirname, 'test_indexeddb_runner.html');
        } else if (req.url.startsWith('/public/')) {
            filePath = path.join(PROJECT_ROOT, req.url);
        } else if (req.url.startsWith('/assets/')) {
            filePath = path.join(PROJECT_ROOT, 'public', req.url);
        }

        if (filePath && fs.existsSync(filePath)) {
            const ext = path.extname(filePath);
            const contentType = ext === '.js' ? 'application/javascript' : 'text/html';
            res.writeHead(200, { 'Content-Type': contentType });
            fs.createReadStream(filePath).pipe(res);
            return;
        }

        res.writeHead(404);
        res.end('Not found: ' + req.url);
    } else if (req.method === 'POST' && req.url === '/results') {
        let body = '';
        req.on('data', chunk => { body += chunk; });
        req.on('end', () => {
            res.writeHead(200, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({ received: true }));

            handleResults(JSON.parse(body));
        });
    }
});

let chromeProc = null;

server.listen(PORT, '127.0.0.1', () => {
    // Launch Google Chrome in headless mode pointing to local server
    const args = [
        '--headless=new',
        '--disable-gpu',
        '--no-sandbox',
        '--user-data-dir=' + path.join(__dirname, '..', 'storage', 'chrome_test_profile'),
        `http://127.0.0.1:${PORT}/`
    ];

    chromeProc = spawn(CHROME_PATH, args);

    chromeProc.on('error', (err) => {
        console.error('Failed to spawn Chrome:', err);
        cleanup(1);
    });
});

// Timeout safeguard
const timeout = setTimeout(() => {
    console.error('Benchmark timed out after 15 seconds without response from browser.');
    cleanup(1);
}, 15000);

function handleResults(data) {
    clearTimeout(timeout);
    let passedCount = 0;
    let failedCount = 0;

    data.tests.forEach((t) => {
        if (t.passed) {
            passedCount++;
            console.log(`  [PASS] ${t.name}${t.timeMs ? ' (' + t.timeMs + ' ms)' : ''} - ${t.details}`);
        } else {
            failedCount++;
            console.log(`  [FAIL] ${t.name} - ${t.details}`);
        }
    });

    console.log('\n--- Real Measured Browser Latency Summary ---');
    console.log('  IndexedDB Single Write Latency: ' + (data.measurements.idb_write_ms ?? 'N/A') + ' ms');
    console.log('  IndexedDB Single Read Latency:  ' + (data.measurements.idb_read_ms ?? 'N/A') + ' ms');
    console.log('  Bulk 50 Records Write Time:     ' + (data.measurements.bulk_50_write_ms ?? 'N/A') + ' ms');
    console.log('  Bulk 50 Records Read Time:      ' + (data.measurements.bulk_50_read_ms ?? 'N/A') + ' ms');
    console.log('  Average Read Throughput / Item: ' + (data.measurements.avg_read_latency_ms ?? 'N/A') + ' ms');

    console.log('\n========================================================================');
    console.log(' INDEXEDDB BENCHMARK SUMMARY');
    console.log(' Total Tests: ' + data.tests.length);
    console.log(' Passed:      ' + passedCount);
    console.log(' Failed:      ' + failedCount);
    console.log(' Overall:     ' + (data.success ? 'ALL PASS' : 'FAILURES DETECTED'));
    console.log('========================================================================\n');

    cleanup(data.success ? 0 : 1);
}

function cleanup(exitCode) {
    if (chromeProc) {
        try { chromeProc.kill(); } catch (e) {}
    }
    server.close(() => {
        process.exit(exitCode);
    });
}

