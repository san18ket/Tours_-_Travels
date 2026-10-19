<?php
namespace App\Core;

class Config
{
    private static array $configs = [];

    /**
     * Get a configuration value using dot notation (e.g. 'app.name', 'database.default.host')
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key);
        $file = array_shift($parts);

        if (!isset(self::$configs[$file])) {
            $configPath = dirname(dirname(__DIR__)) . '/config/' . $file . '.php';
            if (file_exists($configPath)) {
                self::$configs[$file] = require $configPath;
            } else {
                return $default;
            }
        }

        $current = self::$configs[$file];
        foreach ($parts as $part) {
            if (is_array($current) && array_key_exists($part, $current)) {
                $current = $current[$part];
            } else {
                return $default;
            }
        }

        return $current;
    }

    /**
     * Set/override config at runtime
     */
    public static function set(string $key, mixed $value): void
    {
        $parts = explode('.', $key);
        $file = array_shift($parts);

        if (!isset(self::$configs[$file])) {
            self::$configs[$file] = [];
        }

        $current = &self::$configs[$file];
        foreach ($parts as $part) {
            if (!isset($current[$part]) || !is_array($current[$part])) {
                $current[$part] = [];
            }
            $current = &$current[$part];
        }

        $current = $value;
    }
}
