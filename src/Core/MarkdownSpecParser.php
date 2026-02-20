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
        $cases = [];
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
                if (is_array($parsed)) {
                    $cases[] = $parsed;
                }
                $buf = [];
                continue;
            }
            if ($in) {
                $buf[] = $line;
            }
        }
        return $cases;
    }
}
