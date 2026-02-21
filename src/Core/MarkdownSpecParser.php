<?php
declare(strict_types=1);

namespace DcRunnerPhp\Core;

use DcRunnerPhp\Support\Yaml;

final class MarkdownSpecParser {
    /** @return list<array<string,mixed>> */
    public function parseCases(string $path): array {
        $raw = @file_get_contents($path);
        if ($raw === false) {
            throw new \RuntimeException("cannot read case file: {$path}");
        }
        $lines = preg_split('/\R/', $raw) ?: [];
        $fences = [];
        $in = false;
        $buf = [];
        foreach ($lines as $line) {
            $trim = trim($line);
            if (!$in && str_starts_with($trim, '```yaml contract-spec')) {
                $in = true;
                $buf = [];
                continue;
            }
            if ($in && str_starts_with($trim, '```')) {
                $in = false;
                $parsed = Yaml::parse(implode("\n", $buf));
                if (!is_array($parsed)) {
                    throw new \RuntimeException("contract-spec payload in {$path} must be a mapping");
                }
                $fences[] = $parsed;
                $buf = [];
                continue;
            }
            if ($in) {
                $buf[] = $line;
            }
        }
        if (count($fences) !== 1) {
            throw new \RuntimeException("contract-spec markdown in {$path} must contain exactly one yaml contract-spec block");
        }

        $suite = $fences[0];
        if (!array_key_exists('spec_version', $suite) || !array_key_exists('schema_ref', $suite)) {
            throw new \RuntimeException("contract-spec suite in {$path} must include spec_version and schema_ref");
        }
        $contracts = $suite['contracts'] ?? null;
        if (!is_array($contracts) || $contracts === []) {
            throw new \RuntimeException("contract-spec suite in {$path} must define non-empty contracts list");
        }
        $defaults = $suite['defaults'] ?? [];
        if (!is_array($defaults)) {
            throw new \RuntimeException("contract-spec suite defaults in {$path} must be a mapping when provided");
        }

        $cases = [];
        foreach (array_values($contracts) as $idx => $contract) {
            if (!is_array($contract)) {
                throw new \RuntimeException("contract-spec suite contracts[{$idx}] in {$path} must be a mapping");
            }
            $merged = array_replace($defaults, $contract);
            foreach (['spec_version', 'schema_ref', 'title', 'purpose', 'domain'] as $headerKey) {
                if (!array_key_exists($headerKey, $merged) && array_key_exists($headerKey, $suite)) {
                    $merged[$headerKey] = $suite[$headerKey];
                }
            }
            if (!array_key_exists('id', $merged) || trim((string)$merged['id']) === '') {
                throw new \RuntimeException("contract-spec in {$path} contracts[{$idx}] must include id");
            }
            if (!array_key_exists('type', $merged) || trim((string)$merged['type']) === '') {
                throw new \RuntimeException("contract-spec in {$path} contracts[{$idx}] must include type or suite.defaults.type");
            }
            $cases[] = $merged;
        }

        return $cases;
    }
}
