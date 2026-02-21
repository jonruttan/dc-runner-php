<?php
declare(strict_types=1);

namespace DcRunnerPhp\Cli;

use DcRunnerPhp\Cli\Command\ConformanceCommand;
use DcRunnerPhp\Cli\Command\GovernanceCommand;
use DcRunnerPhp\Cli\Command\RunnerCertifyCommand;
use DcRunnerPhp\Cli\Command\SpecsRunCommand;
use DcRunnerPhp\Support\Json;

final class Application {
    /** @var callable */
    private $runConformance;
    /** @var callable */
    private $runGovernance;
    /** @var callable */
    private $runSpecsRun;
    /** @var callable */
    private $runRunnerCertify;
    /** @var resource */
    private $stdout;
    /** @var resource */
    private $stderr;

    public function __construct(
        ?callable $runConformance = null,
        ?callable $runGovernance = null,
        ?callable $runSpecsRun = null,
        mixed $stdout = null,
        mixed $stderr = null,
        ?callable $runRunnerCertify = null
    ) {
        $this->runConformance = $runConformance ?? static fn(array $args): int => (new ConformanceCommand())->run($args);
        $this->runGovernance = $runGovernance ?? static fn(array $args, bool $json): int => (new GovernanceCommand())->run($args, $json);
        $this->runSpecsRun = $runSpecsRun ?? static fn(array $args): int => (new SpecsRunCommand())->run($args);
        $this->runRunnerCertify = $runRunnerCertify ?? static fn(array $args): int => (new RunnerCertifyCommand())->run($args);
        $this->stdout = is_resource($stdout) ? $stdout : STDOUT;
        $this->stderr = is_resource($stderr) ? $stderr : STDERR;
    }

    public function run(array $argv): int {
        $args = $argv;
        array_shift($args);

        $json = false;
        $filtered = [];
        foreach ($args as $arg) {
            if ($arg === '--json') {
                $json = true;
                continue;
            }
            $filtered[] = $arg;
        }
        $args = $filtered;

        $command = $args[0] ?? '--help';
        $rest = array_slice($args, 1);

        if ($command === '--help' || $command === '-h' || $command === 'help') {
            return $this->help($json);
        }

        return match ($command) {
            'conformance' => ($this->runConformance)($rest),
            'governance' => ($this->runGovernance)($rest, $json),
            'runner-certify' => ($this->runRunnerCertify)($rest),
            'specs-run', 'spec-runner' => ($this->runSpecsRun)($rest),
            default => $this->unknown($command, $json),
        };
    }

    private function help(bool $json): int {
        if ($json) {
            fwrite($this->stdout, Json::encode([
                'runner' => 'dc-runner-php',
                'commands' => ['runner --help', 'runner conformance', 'runner governance', 'runner runner-certify', 'runner specs-run'],
                'structured_mode' => '--json'
            ]) . "\n");
            return 0;
        }

        fwrite($this->stdout, "runner --help\n");
        fwrite($this->stdout, "runner conformance\n");
        fwrite($this->stdout, "runner governance\n");
        fwrite($this->stdout, "runner runner-certify\n");
        fwrite($this->stdout, "runner specs-run\n");
        fwrite($this->stdout, "use --json for structured output\n");
        return 0;
    }

    private function unknown(string $command, bool $json): int {
        if ($json) {
            fwrite($this->stdout, Json::encode(['ok' => false, 'error' => 'unknown command', 'command' => $command]) . "\n");
        } else {
            fwrite($this->stderr, "unknown command: {$command}\n");
        }
        return 2;
    }
}
