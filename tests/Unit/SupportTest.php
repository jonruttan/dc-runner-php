<?php
declare(strict_types=1);

namespace DcRunnerPhp\Tests\Unit;

use DcRunnerPhp\Support\Json;
use DcRunnerPhp\Support\PathGuard;
use PHPUnit\Framework\TestCase;

final class SupportTest extends TestCase {
    public function testJsonEncodeProducesObject(): void {
        $json = Json::encode(['ok' => true]);
        self::assertSame('{"ok":true}', $json);
    }

    public function testPathGuardRequireExistsThrowsForMissingPath(): void {
        $this->expectException(\RuntimeException::class);
        PathGuard::requireExists('/definitely/missing/path');
    }
}
