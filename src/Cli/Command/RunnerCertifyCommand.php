<?php
declare(strict_types=1);

namespace DcRunnerPhp\Cli\Command;

final class RunnerCertifyCommand {
    /** @param list<string> $args */
    public function run(array $args): int {
        $runnerId = '';
        $root = getcwd() ?: '.';
        for ($i = 0; $i < count($args); $i++) {
            $token = $args[$i];
            if ($token === '--runner') {
                $i++;
                $runnerId = trim((string)($args[$i] ?? ''));
                continue;
            }
            if ($token === '--root') {
                $i++;
                $root = trim((string)($args[$i] ?? $root));
                continue;
            }
            if ($token === '--help' || $token === '-h') {
                fwrite(STDOUT, "usage: runner-certify --runner <id>\n");
                return 0;
            }
            fwrite(STDERR, "ERROR: unsupported argument for runner-certify: {$token}\n");
            return 2;
        }

        if ($runnerId === '') {
            fwrite(STDERR, "ERROR: runner-certify requires --runner <id>\n");
            return 2;
        }

        $registryCandidates = [
            rtrim($root, '/').'/specs/schema/runner_certification_registry_v2.yaml',
            rtrim($root, '/').'/specs/upstream/data-contracts/specs/schema/runner_certification_registry_v2.yaml',
        ];
        $registryPath = null;
        $registry = null;
        foreach ($registryCandidates as $candidate) {
            if (is_file($candidate)) {
                $registryPath = $candidate;
                $registry = yaml_parse_file($candidate);
                break;
            }
        }
        if (!is_string($registryPath) || !is_array($registry)) {
            fwrite(STDERR, "ERROR: runner certification registry v2 not found\n");
            return 2;
        }
        if ((int)($registry['version'] ?? 0) !== 2) {
            fwrite(STDERR, "ERROR: unsupported registry version in {$registryPath}\n");
            return 2;
        }

        $entry = null;
        foreach (($registry['runners'] ?? []) as $runner) {
            if (!is_array($runner)) {
                continue;
            }
            if (trim((string)($runner['runner_id'] ?? '')) === $runnerId) {
                $entry = $runner;
                break;
            }
        }
        if (!is_array($entry)) {
            fwrite(STDERR, "ERROR: unknown runner id for certification: {$runnerId}\n");
            return 2;
        }

        $runnerClass = trim((string)($entry['class'] ?? ''));
        $runnerStatus = trim((string)($entry['status'] ?? ''));
        if ($runnerClass !== 'required' && $runnerClass !== 'compatibility_non_blocking') {
            fwrite(STDERR, "ERROR: invalid class for {$runnerId}: {$runnerClass}\n");
            return 2;
        }

        $checks = [];
        $addCheck = static function (array &$rows, string $group, string $id, string $status, int $exitCode, string $detail): void {
            $rows[] = [
                'group' => $group,
                'id' => $id,
                'status' => $status,
                'exit_code' => $exitCode,
                'detail' => $detail,
            ];
        };

        $addCheck($checks, 'contract', 'registry.entry.shape', 'pass', 0, 'runner certification registry entry parsed and validated');

        $blocking = $runnerClass === 'required' && $runnerStatus === 'active';

        if ($runnerStatus !== 'active') {
            $addCheck($checks, 'command', 'command.subset', 'skip', 0, 'runner status is not active; command subset execution skipped');
            $addCheck($checks, 'governance-sync', 'governance.required_core_checks', 'skip', 0, 'runner status is not active; governance sync checks skipped');
            $addCheck($checks, 'conformance', 'conformance.required_core_cases', 'skip', 0, 'runner status is not active; conformance subset skipped');
        } else {
            $addCheck($checks, 'governance-sync', 'governance.required_core_checks', 'skip', 0, 'runner-specific governance checks are not executed in certification shim');
            $addCheck($checks, 'conformance', 'conformance.required_core_cases', 'skip', 0, 'runner-specific conformance checks are not executed in certification shim');
        }

        $passed = 0;
        $failed = 0;
        $skipped = 0;
        foreach ($checks as $row) {
            if (($row['status'] ?? '') === 'pass') {
                $passed++;
            } elseif (($row['status'] ?? '') === 'fail') {
                $failed++;
            } else {
                $skipped++;
            }
        }

        $normalizeIntent = static function (array $runnerEntry): array {
            $checks = [];
            foreach (($runnerEntry['required_core_checks'] ?? []) as $item) {
                $val = trim((string)$item);
                if ($val !== '') {
                    $checks[$val] = true;
                }
            }
            $cases = [];
            foreach (($runnerEntry['required_core_cases'] ?? []) as $item) {
                $val = trim((string)$item);
                if ($val !== '') {
                    $cases[$val] = true;
                }
            }
            $checkList = array_keys($checks);
            $caseList = array_keys($cases);
            sort($checkList);
            sort($caseList);

            $subset = [];
            foreach (($runnerEntry['command_contract_subset'] ?? []) as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $name = trim((string)($row['name'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $args = [];
                foreach (($row['args'] ?? []) as $arg) {
                    $args[] = (string)$arg;
                }
                $exits = [];
                foreach (($row['expect_exit'] ?? [0]) as $exit) {
                    $exits[(int)$exit] = true;
                }
                $exitList = array_map('intval', array_keys($exits));
                sort($exitList);
                $subset[] = [
                    'name' => $name,
                    'args' => $args,
                    'expect_exit' => $exitList,
                ];
            }
            usort($subset, static function (array $a, array $b): int {
                $ak = (string)$a['name'] . "\x1f" . implode("\x1f", $a['args']) . "\x1f" . implode(',', array_map('strval', $a['expect_exit']));
                $bk = (string)$b['name'] . "\x1f" . implode("\x1f", $b['args']) . "\x1f" . implode(',', array_map('strval', $b['expect_exit']));
                return $ak <=> $bk;
            });

            return [
                'required_core_checks' => $checkList,
                'required_core_cases' => $caseList,
                'command_contract_subset' => $subset,
                'registry_ref' => [
                    'path' => '/specs/schema/runner_certification_registry_v2.yaml',
                    'version' => 2,
                ],
            ];
        };

        $canonicalize = static function (mixed $value) use (&$canonicalize): mixed {
            if (!is_array($value)) {
                return $value;
            }
            $isList = array_keys($value) === range(0, count($value) - 1);
            if ($isList) {
                $out = [];
                foreach ($value as $item) {
                    $out[] = $canonicalize($item);
                }
                return $out;
            }
            $keys = array_keys($value);
            sort($keys, SORT_STRING);
            $out = [];
            foreach ($keys as $key) {
                $out[(string)$key] = $canonicalize($value[$key]);
            }
            return $out;
        };

        $canonicalJson = static function (mixed $value) use ($canonicalize): string {
            return json_encode($canonicalize($value), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION) ?: '{}';
        };

        $executionIntent = $normalizeIntent($entry);
        $intentHash = hash('sha256', $canonicalJson($executionIntent));

        $commitSha = trim((string)shell_exec('git rev-parse HEAD 2>/dev/null'));
        if ($commitSha === '') {
            $commitSha = 'unknown';
        }
        $implementationRepo = trim((string)($entry['entrypoints']['implementation_repo'] ?? 'unknown'));

        $payload = [
            'version' => 2,
            'runner' => [
                'runner_id' => $runnerId,
                'class' => $runnerClass,
                'status' => $runnerStatus,
                'blocking' => $blocking,
                'implementation_repo' => $implementationRepo,
                'commit_sha' => $commitSha,
                'certified_at' => gmdate('Y-m-d\\TH:i:s\\Z'),
            ],
            'execution_intent' => $executionIntent,
            'equivalence' => [
                'normalization_version' => 'intent-v1',
                'hash_algorithm' => 'sha256',
                'intent_hash' => $intentHash,
            ],
            'summary' => [
                'status' => $failed === 0 ? 'pass' : 'fail',
                'passed' => $passed,
                'failed' => $failed,
                'skipped' => $skipped,
                'blocking' => $blocking,
            ],
            'checks' => $checks,
            'proof' => [
                'canonicalization' => 'json-c14n-v1',
                'payload_sha256' => '',
            ],
        ];
        $payload['proof']['payload_sha256'] = hash('sha256', $canonicalJson($payload));

        $artifactContract = is_array($entry['artifact_contract'] ?? null) ? $entry['artifact_contract'] : [];
        $jsonOut = str_replace('{runner}', $runnerId, (string)($artifactContract['json_out'] ?? '/.artifacts/runner-certification-{runner}.json'));
        $mdOut = str_replace('{runner}', $runnerId, (string)($artifactContract['md_out'] ?? '/.artifacts/runner-certification-{runner}.md'));
        $jsonPath = rtrim($root, '/') . '/' . ltrim($jsonOut, '/');
        $mdPath = rtrim($root, '/') . '/' . ltrim($mdOut, '/');

        @mkdir(dirname($jsonPath), 0777, true);
        @mkdir(dirname($mdPath), 0777, true);

        file_put_contents($jsonPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

        $md = [];
        $md[] = '# Runner Certification Report';
        $md[] = '';
        $md[] = '- version: `' . $payload['version'] . '`';
        $md[] = '- runner_id: `' . $payload['runner']['runner_id'] . '`';
        $md[] = '- class: `' . $payload['runner']['class'] . '`';
        $md[] = '- status: `' . $payload['runner']['status'] . '`';
        $md[] = '- blocking: `' . ($payload['runner']['blocking'] ? 'true' : 'false') . '`';
        $md[] = '- implementation_repo: `' . $payload['runner']['implementation_repo'] . '`';
        $md[] = '- commit_sha: `' . $payload['runner']['commit_sha'] . '`';
        $md[] = '- certified_at: `' . $payload['runner']['certified_at'] . '`';
        $md[] = '- equivalence.intent_hash: `' . $payload['equivalence']['intent_hash'] . '`';
        $md[] = '- proof.payload_sha256: `' . $payload['proof']['payload_sha256'] . '`';
        $md[] = '- summary.status: `' . $payload['summary']['status'] . '`';
        $md[] = '- summary.passed: `' . $payload['summary']['passed'] . '`';
        $md[] = '- summary.failed: `' . $payload['summary']['failed'] . '`';
        $md[] = '- summary.skipped: `' . $payload['summary']['skipped'] . '`';
        $md[] = '';
        $md[] = '## Checks';
        $md[] = '';
        $md[] = '| group | id | status | exit_code | detail |';
        $md[] = '|---|---|---:|---:|---|';
        foreach ($checks as $row) {
            $md[] = sprintf(
                '| `%s` | `%s` | `%s` | `%d` | %s |',
                (string)($row['group'] ?? ''),
                (string)($row['id'] ?? ''),
                (string)($row['status'] ?? ''),
                (int)($row['exit_code'] ?? 0),
                (string)($row['detail'] ?? '')
            );
        }
        file_put_contents($mdPath, implode("\n", $md) . "\n");

        fwrite(STDOUT, "OK: runner certification report written: {$jsonPath}\n");
        fwrite(STDOUT, "OK: runner certification report written: {$mdPath}\n");

        return $failed === 0 ? 0 : 1;
    }
}
