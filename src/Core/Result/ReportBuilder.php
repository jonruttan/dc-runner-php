<?php
declare(strict_types=1);

namespace DcRunnerPhp\Core\Result;

final class ReportBuilder {
    /** @param list<array<string,mixed>> $results */
    public function build(array $results): array {
        return ['version' => 1, 'results' => $results];
    }
}
