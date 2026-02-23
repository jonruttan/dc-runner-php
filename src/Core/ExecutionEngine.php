<?php
declare(strict_types=1);

namespace DcRunnerPhp\Core;

final class ExecutionEngine {
    /**
     * @param list<array<string,mixed>> $cases
     * @return list<array<string,mixed>>
     */
    public function execute(string $fixturePath, array $cases): array {
        $results = [];
        foreach ($cases as $case) {
            $id = (string)($case['id'] ?? 'UNKNOWN');
            $results[] = ['id' => $id, 'status' => 'skip', 'category' => 'runtime', 'message' => 'execution delegated to noncanonical engine'];
        }
        return $results;
    }
}
