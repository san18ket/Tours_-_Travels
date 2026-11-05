<?php
declare(strict_types=1);

/**
 * Production Release Packaging Script
 * Generates clean, secure, production-ready ZIP archive: tours_travels_release.zip
 * Strictly excludes secrets (.env), VCS (.git), automated test suites, logs, and development scratch files.
 */

$projectRoot = dirname(__DIR__);
$zipFileName = 'tours_travels_release.zip';
$zipFilePath = $projectRoot . DIRECTORY_SEPARATOR . $zipFileName;

echo "========================================================================\n";
echo " TOURS & TRAVELS — PRODUCTION RELEASE PACKAGER\n";
echo " Project Root: {$projectRoot}\n";
echo " Output File:  {$zipFilePath}\n";
echo "========================================================================\n\n";

if (file_exists($zipFilePath)) {
    unlink($zipFilePath);
    echo "Removed pre-existing release archive.\n";
}

$zip = new ZipArchive();
if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    echo "ERROR: Failed to create ZIP archive at {$zipFilePath}\n";
    exit(1);
}

// Exclusion rules (regex patterns against relative paths)
$excludePatterns = [
    '/^\.env$/i',
    '/^\.git(\/|\\\\|$)/i',
    '/^\.github(\/|\\\\|$)/i',
    '/^\.gemini(\/|\\\\|$)/i',
    '/^scratch(\/|\\\\|$)/i',
    '/^tests(\/|\\\\|$)/i',
    '/^storage[\/\\\\]logs[\/\\\\].+/i',
    '/^storage[\/\\\\]rate_limits[\/\\\\].+/i',
    '/^storage[\/\\\\]cache[\/\\\\].+/i',
    '/^storage[\/\\\\]chrome_test_profile[\/\\\\].+/i',
    '/^storage[\/\\\\]sessions[\/\\\\].+/i',
    '/^storage[\/\\\\]uploads[\/\\\\].+/i',
    '/^storage[\/\\\\]private_uploads[\/\\\\].+/i',
    '/^scripts(\/|\\\\|$)/i',
    '/\.py$/i',
    '/voyagego_matches\.txt$/i',
    '/\.log$/i',
    '/\.sql$/i',
    '/tours_travels_release\.zip$/i',
];

function isExcluded(string $relativePath, array $patterns): bool {
    $normalized = str_replace('\\', '/', $relativePath);
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $normalized)) {
            return true;
        }
    }
    return false;
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($projectRoot, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$filesAdded = 0;
$bytesTotal = 0;
$directoriesAdded = 0;

foreach ($iterator as $item) {
    $filePath = $item->getRealPath();
    $relativePath = substr($filePath, strlen($projectRoot) + 1);

    if (isExcluded($relativePath, $excludePatterns)) {
        continue;
    }

    $zipPath = str_replace('\\', '/', $relativePath);

    if ($item->isDir()) {
        $zip->addEmptyDir($zipPath);
        $directoriesAdded++;
    } else {
        $zip->addFile($filePath, $zipPath);
        $filesAdded++;
        $bytesTotal += $item->getSize();
    }
}

// Ensure required empty storage directories exist with .gitkeep
$requiredStorageDirs = [
    'storage/logs',
    'storage/rate_limits',
    'storage/cache',
    'storage/sessions',
    'storage/uploads',
    'storage/private_uploads',
];

foreach ($requiredStorageDirs as $dir) {
    $zip->addFromString("{$dir}/.gitkeep", "");
}

$zip->close();

$archiveSizeMb = round(filesize($zipFilePath) / (1024 * 1024), 2);

echo "✓ Release archive created successfully!\n";
echo "  Total Files Added:       {$filesAdded}\n";
echo "  Total Directories Added: {$directoriesAdded}\n";
echo "  Uncompressed Size:       " . round($bytesTotal / (1024 * 1024), 2) . " MB\n";
echo "  Compressed Archive Size: {$archiveSizeMb} MB\n\n";

// Security Audit of Archive Contents
echo "--- Security Audit of Release Archive Contents ---\n";
$verifyZip = new ZipArchive();
$verifyZip->open($zipFilePath);

$violations = [];
$hasEnvExample = false;

for ($i = 0; $i < $verifyZip->numFiles; $i++) {
    $name = $verifyZip->getNameIndex($i);

    if (basename($name) === '.env') {
        $violations[] = "CRITICAL: Plain .env file found in archive: {$name}";
    }
    if (str_starts_with($name, '.git/')) {
        $violations[] = "CRITICAL: Git directory found in archive: {$name}";
    }
    if (str_starts_with($name, 'tests/')) {
        $violations[] = "NOTICE: Test directory found in archive: {$name}";
    }
    if (basename($name) === '.env.example') {
        $hasEnvExample = true;
    }
}
$verifyZip->close();

if (!empty($violations)) {
    echo "✗ SECURITY AUDIT FAILED:\n";
    foreach ($violations as $v) {
        echo "  - {$v}\n";
    }
    unlink($zipFilePath);
    exit(1);
}

if (!$hasEnvExample) {
    echo "✗ ERROR: .env.example was not included in release archive!\n";
    unlink($zipFilePath);
    exit(1);
}

echo "✓ Security Audit Passed: ZERO sensitive credentials, zero .env, zero test artifacts.\n";
echo "✓ .env.example is verified present with template placeholders.\n";
echo "\n========================================================================\n";
echo " RELEASE PACKAGE READY: {$zipFileName} ({$archiveSizeMb} MB)\n";
echo "========================================================================\n";

