<?php
declare(strict_types=1);

namespace DcRunnerPhp\Cli\Command;

use DcRunnerPhp\Support\Json;

final class GovernanceCommand {
    /** @param list<string> $args */
    public function run(array $args, bool $json): int {
        $payload = [
            'command' => 'governance',
            'ok' => false,
            'status' => 'not_implemented',
            'notes' => 'governance command surface is present for portable CLI contract compatibility'
        ];
        if ($json) {
            fwrite(STDOUT, Json::encode($payload) . "\n");
        } else {
            fwrite(STDOUT, "governance: not implemented in this lane\n");
        }
        return 1;
    }
}
