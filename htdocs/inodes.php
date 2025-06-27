<?php

set_time_limit(120);
ini_set('memory_limit', '128M');
ob_implicit_flush(true);

$results = [];

function scanInodes($dir, $depth = 0)
{
    global $results;

    $count = 0;
    $items = @scandir($dir);
    if (!$items) return 0;

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        $count++;

        if (is_dir($path)) {
            $count += scanInodes($path, $depth + 1);
        }
    }

    $results[] = ['path' => $dir, 'count' => $count, 'depth' => $depth];

    echo str_repeat("\t", $depth) . str_pad($count, 8, ' ', STR_PAD_LEFT) . "  " . $dir . "\n";
    flush();

    return $count;
}

echo "<pre>";
$dir = realpath(__DIR__ . '/../');
echo "Scanning from: " . $dir . "\n\n";
scanInodes($dir);
echo "\n\nTop 30 directories by inode count:\n\n";

usort($results, fn($a, $b) => $b['count'] <=> $a['count']);

foreach (array_slice($results, 0, 30) as $entry) {
    echo str_pad($entry['count'], 8, ' ', STR_PAD_LEFT) . "  " . $entry['path'] . "\n";
}

echo "</pre>";
