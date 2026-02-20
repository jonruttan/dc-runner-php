<?php
declare(strict_types=1);

namespace DcRunnerPhp\Tests\Unit;

use DcRunnerPhp\Core\CaseDiscovery;
use PHPUnit\Framework\TestCase;

final class CaseDiscoveryTest extends TestCase {
    public function testDiscoverSpecMarkdownFilesRecursively(): void {
        $fixtureRoot = __DIR__ . '/../Fixtures/cases';
        @mkdir($fixtureRoot . '/nested', 0777, true);
        file_put_contents($fixtureRoot . '/a.spec.md', "# a\n");
        file_put_contents($fixtureRoot . '/nested/b.spec.md', "# b\n");
        file_put_contents($fixtureRoot . '/nested/c.txt', "# c\n");

        $files = (new CaseDiscovery())->discover($fixtureRoot, '*.spec.md');
        self::assertCount(2, $files);
        self::assertStringEndsWith('/a.spec.md', $files[0]);
        self::assertStringEndsWith('/b.spec.md', $files[1]);
    }
}
