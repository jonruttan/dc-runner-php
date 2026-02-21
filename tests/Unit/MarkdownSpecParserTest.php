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
spec_version: 1
schema_ref: /specs/schema/schema_v1.md
defaults: { type: contract.check }
contracts:
  - id: T-1
    harness: { check: { profile: text.file, config: {} } }
    clauses: { defaults: {}, predicates: [] }
```
MD);

        $cases = (new MarkdownSpecParser())->parseCases($fixtureFile);
        self::assertCount(1, $cases);
        self::assertSame('T-1', $cases[0]['id']);
    }

    public function testRejectsUnreadableFile(): void {
        $this->expectException(\RuntimeException::class);
        (new MarkdownSpecParser())->parseCases('/path/that/does/not/exist.spec.md');
    }

    public function testRejectsWhenFenceCountIsNotOne(): void {
        $fixtureFile = __DIR__ . '/../Fixtures/no_fence.spec.md';
        file_put_contents($fixtureFile, "# No executable fence\n");
        $this->expectException(\RuntimeException::class);
        (new MarkdownSpecParser())->parseCases($fixtureFile);
    }

    public function testRejectsNonMappingFencePayload(): void {
        $fixtureFile = __DIR__ . '/../Fixtures/non_mapping_payload.spec.md';
        file_put_contents($fixtureFile, <<<MD
```yaml contract-spec
42
```
MD);
        $this->expectException(\RuntimeException::class);
        (new MarkdownSpecParser())->parseCases($fixtureFile);
    }

    public function testRejectsMissingSuiteHeaders(): void {
        $fixtureFile = __DIR__ . '/../Fixtures/missing_headers.spec.md';
        file_put_contents($fixtureFile, <<<MD
```yaml contract-spec
defaults: { type: contract.check }
contracts:
  - id: T-1
    clauses: { defaults: {}, predicates: [] }
```
MD);
        $this->expectException(\RuntimeException::class);
        (new MarkdownSpecParser())->parseCases($fixtureFile);
    }

    public function testRejectsEmptyContractsList(): void {
        $fixtureFile = __DIR__ . '/../Fixtures/empty_contracts.spec.md';
        file_put_contents($fixtureFile, <<<MD
```yaml contract-spec
spec_version: 1
schema_ref: /specs/schema/schema_v1.md
contracts: []
```
MD);
        $this->expectException(\RuntimeException::class);
        (new MarkdownSpecParser())->parseCases($fixtureFile);
    }

    public function testRejectsInvalidDefaultsShape(): void {
        $fixtureFile = __DIR__ . '/../Fixtures/invalid_defaults.spec.md';
        file_put_contents($fixtureFile, <<<MD
```yaml contract-spec
spec_version: 1
schema_ref: /specs/schema/schema_v1.md
defaults: bad
contracts:
  - id: T-1
    type: contract.check
    clauses: { defaults: {}, predicates: [] }
```
MD);
        $this->expectException(\RuntimeException::class);
        (new MarkdownSpecParser())->parseCases($fixtureFile);
    }

    public function testRejectsNonMappingContractItem(): void {
        $fixtureFile = __DIR__ . '/../Fixtures/non_mapping_contract.spec.md';
        file_put_contents($fixtureFile, <<<MD
```yaml contract-spec
spec_version: 1
schema_ref: /specs/schema/schema_v1.md
contracts:
  - bad
```
MD);
        $this->expectException(\RuntimeException::class);
        (new MarkdownSpecParser())->parseCases($fixtureFile);
    }

    public function testRejectsMissingContractId(): void {
        $fixtureFile = __DIR__ . '/../Fixtures/missing_id.spec.md';
        file_put_contents($fixtureFile, <<<MD
```yaml contract-spec
spec_version: 1
schema_ref: /specs/schema/schema_v1.md
defaults: { type: contract.check }
contracts:
  - clauses: { defaults: {}, predicates: [] }
```
MD);
        $this->expectException(\RuntimeException::class);
        (new MarkdownSpecParser())->parseCases($fixtureFile);
    }

    public function testRejectsMissingTypeWithoutDefault(): void {
        $fixtureFile = __DIR__ . '/../Fixtures/missing_type.spec.md';
        file_put_contents($fixtureFile, <<<MD
```yaml contract-spec
spec_version: 1
schema_ref: /specs/schema/schema_v1.md
contracts:
  - id: T-1
    clauses: { defaults: {}, predicates: [] }
```
MD);
        $this->expectException(\RuntimeException::class);
        (new MarkdownSpecParser())->parseCases($fixtureFile);
    }
}
