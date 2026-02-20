<?php
declare(strict_types=1);

namespace DcRunnerPhp\Tests\Unit;

use DcRunnerPhp\Core\MarkdownSpecParser;
use PHPUnit\Framework\TestCase;

final class MarkdownSpecParserTest extends TestCase {
    public function testParsesYamlContractSpecFence(): void {
        $fixtureFile = __DIR__ . '/../Fixtures/simple.spec.md';
        @mkdir(dirname($fixtureFile), 0777, true);
        file_put_contents($fixtureFile, <<<MD
# Fixture

```yaml contract-spec
id: T-1
spec_version: 1
schema_ref: /specs/schema/schema_v1.md
type: contract.check
harness: { check: { profile: text.file, config: {} } }
contract: { defaults: { class: MUST }, steps: [] }
```
MD);

        $cases = (new MarkdownSpecParser())->parseCases($fixtureFile);
        self::assertCount(1, $cases);
        self::assertSame('T-1', $cases[0]['id']);
    }
}
