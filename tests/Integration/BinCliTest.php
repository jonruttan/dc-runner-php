<?php
declare(strict_types=1);

namespace DcRunnerPhp\Tests\Integration;

use PHPUnit\Framework\TestCase;

final class BinCliTest extends TestCase {
    public function testBinHelpContainsPortableCommands(): void {
        $cmd = sprintf('php %s --help', escapeshellarg(__DIR__ . '/../../bin/spec-runner'));
        exec($cmd, $out, $exit);
        self::assertSame(0, $exit);
        $text = implode("\n", $out);
        self::assertStringContainsString('runner --help', $text);
        self::assertStringContainsString('runner conformance', $text);
        self::assertStringContainsString('runner governance', $text);
    }

    public function testRunnerAdapterConformanceHelpStillWorks(): void {
        $cmd = sprintf('%s conformance --help', escapeshellarg(__DIR__ . '/../../runner_adapter.sh'));
        exec($cmd, $out, $exit);
        self::assertSame(0, $exit);
        $text = implode("\n", $out);
        self::assertStringContainsString('usage: conformance_runner.php', $text);
    }

    public function testGovernanceReturnsNonZeroUntilImplemented(): void {
        $cmd = sprintf('php %s governance', escapeshellarg(__DIR__ . '/../../bin/spec-runner'));
        exec($cmd, $out, $exit);
        self::assertSame(1, $exit);
    }
}
