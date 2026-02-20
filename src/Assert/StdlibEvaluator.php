<?php
declare(strict_types=1);

namespace DcRunnerPhp\Assert;

final class StdlibEvaluator {
    public function contains(string $haystack, string $needle): bool {
        return str_contains($haystack, $needle);
    }
}
