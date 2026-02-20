<?php
declare(strict_types=1);

namespace DcRunnerPhp\Core;

final class CaseSchemaValidator {
    public function validate(array $case): void {
        foreach (['id','spec_version','schema_ref','type','harness','contract'] as $required) {
            if (!array_key_exists($required, $case)) {
                throw new \RuntimeException("case missing required key: {$required}");
            }
        }
    }
}
