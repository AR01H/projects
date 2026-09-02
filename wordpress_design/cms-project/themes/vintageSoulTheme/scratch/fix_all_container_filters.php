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

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Parse CSS rules
    $modified = false;
    $new_content = preg_replace_callback('/([^{}]+)\{([^}]+)\}/s', function($match) use (&$modified) {
        $selector = $match[1];
        $body = $match[2];
        
        // If this rule contains an SVG rough cut filter
        if (preg_match('/filter:\s*url\(#rough-button-cut/i', $body)) {
            // Check if selector is a pseudo element (::before, ::after, :before, :after)
            $is_pseudo = (
                strpos($selector, '::before') !== false ||
                strpos($selector, '::after') !== false ||
                strpos($selector, ':before') !== false ||
                strpos($selector, ':after') !== false
            );
            
            // If it's NOT a pseudo-element (it's a container holding text/children), remove the filter from the container!
            if (!$is_pseudo) {
                $body = preg_replace('/-?webkit-filter:\s*url\(#rough-button-cut[^)]*\)\s*(!important)?\s*;/i', '/* filter isolated */', $body);
                $body = preg_replace('/filter:\s*url\(#rough-button-cut[^)]*\)\s*(!important)?\s*;/i', '/* filter isolated */', $body);
                $modified = true;
                return $selector . '{' . $body . '}';
            }
        }
        return $match[0];
    }, $content);
    
    if ($modified) {
        file_put_contents($file, $new_content);
        echo "Cleaned container filters in: " . basename($file) . "\n";
    }
}

echo "\nCompleted universal container filter cleanup!\n";
