<?php
declare(strict_types=1);

namespace DcRunnerPhp\Core;

final class CaseSchemaValidator {
    public function validate(array $case): void {
        foreach (['id','spec_version','schema_ref','type','clauses'] as $required) {
            if (!array_key_exists($required, $case)) {
                throw new \RuntimeException("case missing required key: {$required}");
            }
        }
        if (array_key_exists('contract', $case)) {
            throw new \RuntimeException("noncanonical key contract is forbidden; use clauses");
        }
        if (!is_array($case['clauses'])) {
            throw new \RuntimeException("clauses must be a mapping");
        }
        if (array_key_exists('steps', $case['clauses'])) {
            throw new \RuntimeException("noncanonical key clauses.steps is forbidden; use clauses.predicates");
        }
    }
}
