<?php
declare(strict_types=1);

namespace DcRunnerPhp\Tests\Unit;

use DcRunnerPhp\Assert\AssertionEvaluator;
use DcRunnerPhp\Assert\StdlibEvaluator;
use DcRunnerPhp\Core\ExecutionEngine;
use DcRunnerPhp\Core\MarkdownSpecParser;
use DcRunnerPhp\Harness\ApiHttpHarness;
use DcRunnerPhp\Harness\CliRunHarness;
use DcRunnerPhp\Harness\TextFileHarness;
use PHPUnit\Framework\TestCase;

final class CriticalPathsSmokeTest extends TestCase {
    public function testExecutionEngineReturnsDelegatedRuntimeSkips(): void {
        $engine = new ExecutionEngine();
        $results = $engine->execute(__FILE__, [['id' => 'A'], []]);
        self::assertCount(2, $results);
        self::assertSame('A', $results[0]['id']);
        self::assertSame('UNKNOWN', $results[1]['id']);
        self::assertSame('skip', $results[0]['status']);
    }

    public function testAssertionEvaluatorAcceptsPortableScalarAndRejectsObject(): void {
        $eval = new AssertionEvaluator();
        self::assertTrue($eval->evaluate('ok'));
        self::assertFalse($eval->evaluate((object)['x' => 1]));
    }

    public function testStdlibContains(): void {
        $stdlib = new StdlibEvaluator();
        self::assertTrue($stdlib->contains('alpha beta', 'beta'));
        self::assertFalse($stdlib->contains('alpha beta', 'gamma'));
    }

    public function testHarnessProfilesAreStable(): void {
        self::assertSame('text.file', (new TextFileHarness())->profile());
        self::assertSame('cli.run', (new CliRunHarness())->profile());
        self::assertSame('api.http', (new ApiHttpHarness())->profile());
    }

    public function testMarkdownParserThrowsForMissingFile(): void {
        $parser = new MarkdownSpecParser();
        $this->expectException(\RuntimeException::class);
        $parser->parseCases('/definitely/missing/spec.md');
    }
}
