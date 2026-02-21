<?php
declare(strict_types=1);

namespace DcRunnerPhp\Tests\Unit;

use DcRunnerPhp\Cli\Application;
use PHPUnit\Framework\TestCase;

final class ApplicationTest extends TestCase {
    private function appWithSilentIo(
        ?callable $runConformance = null,
        ?callable $runGovernance = null,
        ?callable $runSpecsRun = null,
        ?callable $runRunnerCertify = null
    ): Application {
        $stdout = fopen('php://temp', 'w+');
        $stderr = fopen('php://temp', 'w+');
        self::assertIsResource($stdout);
        self::assertIsResource($stderr);
        return new Application($runConformance, $runGovernance, $runSpecsRun, $stdout, $stderr, $runRunnerCertify);
    }

    public function testHelpCommandSucceeds(): void {
        $app = $this->appWithSilentIo();
        $exit = $app->run(['bin/spec-runner', '--help']);
        self::assertSame(0, $exit);
    }

    public function testUnknownCommandFailsWithUsageExit(): void {
        $app = $this->appWithSilentIo();
        $exit = $app->run(['bin/spec-runner', '__unknown__']);
        self::assertSame(2, $exit);
    }

    public function testJsonHelpModeSucceeds(): void {
        $app = $this->appWithSilentIo();
        $exit = $app->run(['bin/spec-runner', '--json', '--help']);
        self::assertSame(0, $exit);
    }

    public function testGovernanceCommandFailsWhileUnimplemented(): void {
        $app = $this->appWithSilentIo(null, static fn(array $args, bool $json): int => 1);
        $exit = $app->run(['bin/spec-runner', 'governance']);
        self::assertSame(1, $exit);
    }

    public function testConformanceCommandDispatches(): void {
        $app = $this->appWithSilentIo(static fn(array $args): int => 2);
        $exit = $app->run(['bin/spec-runner', 'conformance']);
        self::assertSame(2, $exit);
    }

    public function testSpecsRunnerAliasDispatches(): void {
        $app = $this->appWithSilentIo(null, null, static fn(array $args): int => 2);
        $exit = $app->run(['bin/spec-runner', 'spec-runner']);
        self::assertSame(2, $exit);
    }

    public function testRunnerCertifyDispatches(): void {
        $app = $this->appWithSilentIo(null, null, null, static fn(array $args): int => 2);
        $exit = $app->run(['bin/spec-runner', 'runner-certify', '--runner', 'php']);
        self::assertSame(2, $exit);
    }

    public function testUnknownJsonCommandFailsWithUsageExit(): void {
        $app = $this->appWithSilentIo();
        $exit = $app->run(['bin/spec-runner', '--json', '__unknown__']);
        self::assertSame(2, $exit);
    }
}
