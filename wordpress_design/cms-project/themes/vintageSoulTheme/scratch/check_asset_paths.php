<?php
$css_files = array_merge(
    glob('assets/css/*.css'),
    glob('assets/css/*/*.css'),
    glob('assets/css/*/*/*.css')
);

$missing = [];

foreach ($css_files as $file) {
    $dir = dirname($file);
    $content = file_get_contents($file);
    
    if (preg_match_all('/url\(\s*[\'"]?([^\'")]+)[\'"]?\s*\)/', $content, $matches)) {
        foreach ($matches[1] as $url) {
            $url = trim($url);
            if (strpos($url, 'data:') === 0 || strpos($url, '#') === 0 || strpos($url, 'http') === 0) {
                continue;
            }
            
            $target = realpath($dir . '/' . $url);
            if (!$target || !file_exists($target)) {
                $missing[] = [
                    'file' => $file,
                    'url' => $url,
                    'resolved' => $dir . '/' . $url
                ];
            }
        }
    }
}

if (empty($missing)) {
    echo "ALL CSS URLS RESOLVED PERFECTLY (NO 404s)!\n";
} else {
    echo "FOUND " . count($missing) . " MISSING ASSET URLS:\n";
    foreach ($missing as $m) {
        echo "- File: " . $m['file'] . "\n  URL: " . $m['url'] . "\n";
    }
}
