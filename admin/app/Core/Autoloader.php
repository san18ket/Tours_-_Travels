<?php
namespace App\Core;

class Autoloader
{
    /**
     * Register PSR-4 style autoloader for App\ namespace
     */
    public static function register(): void
    {
        spl_autoload_register(function ($class) {
            $prefix = 'App\\';
            $baseDir = dirname(__DIR__) . DIRECTORY_SEPARATOR;

            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }

            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

            if (file_exists($file)) {
                require $file;
            }
        });
    }
}
