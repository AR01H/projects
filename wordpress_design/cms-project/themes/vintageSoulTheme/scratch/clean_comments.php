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

foreach ($files as $file) {
    $content = file_get_contents($file);
    $new_content = preg_replace('/-webkit-\/\*[^*]*\*\//', '', $content);
    $new_content = preg_replace('/\/\* filter isolated[^*]*\*\//', '', $new_content);
    // Remove empty lines if created
    $new_content = preg_replace("/\n\s*\n\s*\n/", "\n\n", $new_content);
    
    if ($new_content !== $content) {
        file_put_contents($file, $new_content);
        echo "Cleaned comments in " . basename($file) . "\n";
    }
}

echo "Done!\n";
