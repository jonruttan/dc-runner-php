<?php
declare(strict_types=1);

namespace DcRunnerPhp\Core;

final class CaseDiscovery {
    /** @return list<string> */
    public function discover(string $root, string $pattern = '*.spec.md'): array {
        if (is_file($root)) {
            return [realpath($root) ?: $root];
        }
        $files = [];
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $item) {
            if (!$item->isFile()) {
                continue;
            }
            if (fnmatch($pattern, $item->getFilename())) {
                $files[] = $item->getPathname();
            }
        }
        sort($files, SORT_STRING);
        return $files;
    }
}
