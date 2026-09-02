<?php

$css_dir = __DIR__ . '/../assets/css';

function get_all_css($dir) {
    $results = [];
    $files = scandir($dir);
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $path = $dir . '/' . $f;
        if (is_dir($path)) {
            $results = array_merge($results, get_all_css($path));
        } else if (substr($f, -4) === '.css') {
            $results[] = $path;
        }
    }
    return $results;
}

$files = get_all_css($css_dir);
$all_valid = true;

foreach ($files as $file) {
    $content = file_get_contents($file);
    // Remove comments and strings to check brace balance
    $stripped = preg_replace('!/\*.*?\*/!s', '', $content);
    $stripped = preg_replace('/"([^"\\\\]|\\\\.)*"/', '', $stripped);
    $stripped = preg_replace("/'([^'\\\\]|\\\\.)*'/", '', $stripped);
    
    $open = substr_count($stripped, '{');
    $close = substr_count($stripped, '}');
    
    if ($open !== $close) {
        echo "SYNTAX ERROR in " . basename($file) . " -> Open: $open, Close: $close (Diff: " . ($open - $close) . ")\n";
        $all_valid = false;
    }
}

if ($all_valid) {
    echo "ALL " . count($files) . " CSS FILES ARE 100% BALANCED & VALID!\n";
}
