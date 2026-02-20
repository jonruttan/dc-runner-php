<?php
declare(strict_types=1);

namespace DcRunnerPhp\Assert;

final class AssertionEvaluator {
    public function evaluate(mixed $assertion): bool {
        return is_array($assertion) || is_string($assertion) || is_bool($assertion) || is_numeric($assertion) || $assertion === null;
    }
}
