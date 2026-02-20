<?php
declare(strict_types=1);

namespace DcRunnerPhp\Support;

final class Yaml {
    public static function parse(string $yaml): mixed {
        if (!function_exists('yaml_parse')) {
            throw new \RuntimeException('yaml extension is required');
        }
        $parsed = yaml_parse($yaml);
        if ($parsed === false) {
            throw new \RuntimeException('failed to parse yaml');
        }
        return $parsed;
    }
}
