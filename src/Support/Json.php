<?php
declare(strict_types=1);

namespace DcRunnerPhp\Support;

final class Json {
    public static function encode(array $data): string {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('failed to encode json');
        }
        return $json;
    }
}
