<?php
declare(strict_types=1);

namespace DcRunnerPhp\Cli\Command;

final class ConformanceCommand {
    /** @param list<string> $args */
    public function run(array $args): int {
        $cmd = array_merge(['php', dirname(__DIR__, 3) . '/conformance_runner.php'], $args);
        passthru(implode(' ', array_map('escapeshellarg', $cmd)), $code);
        return (int)$code;
    }
}
