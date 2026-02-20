<?php
declare(strict_types=1);

namespace DcRunnerPhp\Support;

final class PathGuard {
    public static function requireExists(string $path): void {
        if (!file_exists($path)) {
            throw new \RuntimeException("path does not exist: {$path}");
        }
    }
}
