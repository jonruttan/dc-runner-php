<?php
declare(strict_types=1);

namespace DcRunnerPhp\Tests\Unit;

use DcRunnerPhp\Core\CaseSchemaValidator;
use PHPUnit\Framework\TestCase;

final class CaseSchemaValidatorTest extends TestCase {
    public function testRejectsMissingRequiredKeys(): void {
        $validator = new CaseSchemaValidator();
        $this->expectException(\RuntimeException::class);
        $validator->validate(['id' => 'x']);
    }

    public function testAcceptsMinimalCaseShape(): void {
        $validator = new CaseSchemaValidator();
        $validator->validate([
            'id' => 'x',
            'spec_version' => 1,
            'schema_ref' => '/specs/01_schema/schema_v1.md',
            'type' => 'contract.check',
            'harness' => ['check' => ['profile' => 'text.file', 'config' => []]],
            'clauses' => ['defaults' => [], 'predicates' => []],
        ]);
        self::assertTrue(true);
    }

    public function testRejectsLegacyContractKey(): void {
        $validator = new CaseSchemaValidator();
        $this->expectException(\RuntimeException::class);
        $validator->validate([
            'id' => 'x',
            'spec_version' => 1,
            'schema_ref' => '/specs/01_schema/schema_v1.md',
            'type' => 'contract.check',
            'contract' => [],
            'clauses' => ['defaults' => [], 'predicates' => []],
        ]);
    }

    public function testRejectsNonMappingClauses(): void {
        $validator = new CaseSchemaValidator();
        $this->expectException(\RuntimeException::class);
        $validator->validate([
            'id' => 'x',
            'spec_version' => 1,
            'schema_ref' => '/specs/01_schema/schema_v1.md',
            'type' => 'contract.check',
            'clauses' => 'not-a-map',
        ]);
    }

    public function testRejectsLegacyClausesStepsKey(): void {
        $validator = new CaseSchemaValidator();
        $this->expectException(\RuntimeException::class);
        $validator->validate([
            'id' => 'x',
            'spec_version' => 1,
            'schema_ref' => '/specs/01_schema/schema_v1.md',
            'type' => 'contract.check',
            'clauses' => ['steps' => []],
        ]);
    }
}
