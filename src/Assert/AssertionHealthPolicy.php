<?php
declare(strict_types=1);

namespace DcRunnerPhp\Assert;

final class AssertionHealthPolicy {
    public function mode(array $case): string {
        return (string)($case['assert_health']['mode'] ?? 'warn');
    }
}
