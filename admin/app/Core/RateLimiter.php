<?php
namespace App\Core;

use PDO;

class RateLimiter
{
    /**
     * Determine if the given key has exceeded max attempts
     */
    public static function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        $attempts = self::getAttempts($key);
        return $attempts >= $maxAttempts;
    }

    /**
     * Increment the counter for a given key with expiration window
     */
    public static function hit(string $key, int $decaySeconds = 60): int
    {
        $cacheFile = self::getCacheFilePath($key);
        $now = time();
        $data = ['count' => 0, 'expires_at' => $now + $decaySeconds];

        if (file_exists($cacheFile)) {
            $content = @file_get_contents($cacheFile);
            $parsed = json_decode($content, true);
            if (is_array($parsed) && ($parsed['expires_at'] ?? 0) > $now) {
                $data['count'] = (int) ($parsed['count'] ?? 0);
                $data['expires_at'] = (int) $parsed['expires_at'];
            }
        }

        $data['count']++;
        @file_put_contents($cacheFile, json_encode($data), LOCK_EX);

        return $data['count'];
    }

    /**
     * Get remaining seconds until the key is released
     */
    public static function availableIn(string $key): int
    {
        $cacheFile = self::getCacheFilePath($key);
        if (!file_exists($cacheFile)) {
            return 0;
        }

        $content = @file_get_contents($cacheFile);
        $parsed = json_decode($content, true);
        if (is_array($parsed)) {
            $diff = ($parsed['expires_at'] ?? 0) - time();
            return max(0, $diff);
        }

        return 0;
    }

    /**
     * Reset rate limit attempts for a key
     */
    public static function clear(string $key): void
    {
        $cacheFile = self::getCacheFilePath($key);
        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }
    }

    /**
     * Get current attempts count
     */
    public static function getAttempts(string $key): int
    {
        $cacheFile = self::getCacheFilePath($key);
        if (!file_exists($cacheFile)) {
            return 0;
        }

        $content = @file_get_contents($cacheFile);
        $parsed = json_decode($content, true);
        if (is_array($parsed) && ($parsed['expires_at'] ?? 0) > time()) {
            return (int) ($parsed['count'] ?? 0);
        }

        return 0;
    }

    private static function getCacheFilePath(string $key): string
    {
        $dir = Config::get('paths.storage') . '/rate_limits';
        if (!is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }
        $hash = hash('sha256', $key);
        return $dir . '/rl_' . $hash . '.json';
    }
}
