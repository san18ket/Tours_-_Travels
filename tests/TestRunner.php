<?php
/**
 * VoyageGo Automated Test Suite Runner
 * Executes verification tests covering security, migrations, business logic, and integrity.
 */

namespace Tests;

require_once dirname(__DIR__) . '/app/bootstrap.php';

class TestRunner
{
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];

    public function assert(bool $condition, string $message): void
    {
        if ($condition) {
            $this->passed++;
            echo "  ✓ PASS: {$message}\n";
        } else {
            $this->failed++;
            $this->failures[] = $message;
            echo "  ✗ FAIL: {$message}\n";
        }
    }

    public function assertThrows(callable $callback, string $expectedExceptionClass, string $message): void
    {
        try {
            $callback();
            $this->failed++;
            $this->failures[] = "{$message} (Expected exception {$expectedExceptionClass} was not thrown)";
            echo "  ✗ FAIL: {$message} (Expected {$expectedExceptionClass} was not thrown)\n";
        } catch (\Throwable $e) {
            if ($e instanceof $expectedExceptionClass || get_class($e) === $expectedExceptionClass) {
                $this->passed++;
                echo "  ✓ PASS: {$message} [Caught: " . basename(str_replace('\\', '/', get_class($e))) . ": {$e->getMessage()}]\n";
            } else {
                $this->failed++;
                $this->failures[] = "{$message} (Expected {$expectedExceptionClass}, got " . get_class($e) . ")";
                echo "  ✗ FAIL: {$message} (Expected {$expectedExceptionClass}, got " . get_class($e) . ")\n";
            }
        }
    }

    public function runSuite(string $name, callable $suite): void
    {
        echo "\n======================================================\n";
        echo " RUNNING SUITE: {$name}\n";
        echo "======================================================\n";
        $suite($this);
    }

    public function summary(): int
    {
        echo "\n======================================================\n";
        echo " TEST EXECUTION SUMMARY\n";
        echo "======================================================\n";
        echo " Passed: {$this->passed}\n";
        echo " Failed: {$this->failed}\n";
        echo " Total:  " . ($this->passed + $this->failed) . "\n";

        if ($this->failed > 0) {
            echo "\n Failures:\n";
            foreach ($this->failures as $f) {
                echo "  - {$f}\n";
            }
            return 1;
        }

        echo "\n ALL TESTS PASSED! Verification gates satisfied.\n";
        return 0;
    }
}
