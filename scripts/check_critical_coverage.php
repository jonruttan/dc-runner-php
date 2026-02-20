#!/usr/bin/env php
<?php
declare(strict_types=1);

if ($argc < 2) {
    fwrite(STDERR, "usage: check_critical_coverage.php <clover.xml>\n");
    exit(2);
}

$path = (string)$argv[1];
if (!is_file($path)) {
    fwrite(STDERR, "coverage file not found: {$path}\n");
    exit(2);
}

$xml = @simplexml_load_file($path);
if ($xml === false) {
    fwrite(STDERR, "invalid clover xml: {$path}\n");
    exit(2);
}

$critical = [
    'src/Core/MarkdownSpecParser.php',
    'src/Core/CaseSchemaValidator.php',
    'src/Core/ExecutionEngine.php',
    'src/Assert/AssertionEvaluator.php',
    'src/Assert/StdlibEvaluator.php',
    'src/Harness/TextFileHarness.php',
    'src/Harness/CliRunHarness.php',
    'src/Harness/ApiHttpHarness.php',
    'src/Cli/Application.php',
];

$cwd = getcwd();
if ($cwd === false) {
    fwrite(STDERR, "cannot resolve cwd\n");
    exit(2);
}
$cwd = str_replace('\\', '/', $cwd);

$required = [];
foreach ($critical as $rel) {
    $full = realpath($cwd . '/' . $rel);
    if ($full === false) {
        fwrite(STDERR, "critical file missing: {$rel}\n");
        exit(2);
    }
    $required[str_replace('\\', '/', $full)] = $rel;
}

$seen = [];
$files = $xml->xpath('//file');
if ($files === false) {
    fwrite(STDERR, "cannot read file nodes from coverage xml\n");
    exit(2);
}
foreach ($files as $file) {
    $name = str_replace('\\', '/', (string)$file['name']);
    if (!array_key_exists($name, $required)) {
        continue;
    }
    $metrics = $file->metrics;
    if (!isset($metrics['statements'], $metrics['coveredstatements'])) {
        fwrite(STDERR, "missing statement metrics for {$required[$name]}\n");
        exit(1);
    }
    $statements = (int)$metrics['statements'];
    $covered = (int)$metrics['coveredstatements'];
    if ($statements <= 0) {
        fwrite(STDERR, "no statements tracked for {$required[$name]}\n");
        exit(1);
    }
    if ($covered !== $statements) {
        $pct = round(($covered / $statements) * 100, 2);
        fwrite(STDERR, "coverage below 100% for {$required[$name]} ({$covered}/{$statements}, {$pct}%)\n");
        exit(1);
    }
    $seen[$name] = true;
}

foreach ($required as $full => $rel) {
    if (!isset($seen[$full])) {
        fwrite(STDERR, "critical file not present in coverage report: {$rel}\n");
        exit(1);
    }
}

fwrite(STDOUT, "critical coverage check passed\n");
exit(0);
