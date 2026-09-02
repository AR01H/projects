<?php

$css_dir = __DIR__ . '/../assets/css';

function get_all_files($dir) {
    $results = [];
    $files = scandir($dir);
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $path = $dir . '/' . $f;
        if (is_dir($path)) {
            $results = array_merge($results, get_all_files($path));
        } else if (substr($f, -4) === '.css') {
            $results[] = $path;
        }
    }
    return $results;
}

$files = get_all_files($css_dir);
echo "Scanning " . count($files) . " CSS files for filter: url(#rough-button-cut)...\n";

foreach ($files as $file) {
    $content = file_get_contents($file);
    if (preg_match_all('/([^{}]+)\s*\{([^}]*filter:\s*url\(#rough-button-cut[^}]*)\}/i', $content, $matches, PREG_SET_ORDER)) {
        echo "\nFile: " . basename($file) . "\n";
        foreach ($matches as $m) {
            $selector = trim(preg_replace('/\s+/', ' ', $m[1]));
            // Check if selector is a pseudo-element
            $is_pseudo = (strpos($selector, '::before') !== false || strpos($selector, '::after') !== false || strpos($selector, ':before') !== false || strpos($selector, ':after') !== false);
            $tag = $is_pseudo ? "[PSEUDO - SAFE]" : "[CONTAINER - CAUSES JAGGED TEXT!]";
            echo "  $tag $selector\n";
        }
    }
}
