<?php
$dirs = [
    __DIR__ . '/../pages',
    __DIR__ . '/../components',
    __DIR__ . '/../templates',
    __DIR__ . '/../src'
];

$findings = [];

function scanDirRecursive($dir, &$findings) {
    if (!is_dir($dir)) return;
    $files = scandir($dir);
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $path = $dir . '/' . $f;
        if (is_dir($path)) {
            scanDirRecursive($path, $findings);
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            checkPhpFile($path, $findings);
        }
    }
}

function checkPhpFile($path, &$findings) {
    $content = file_get_contents($path);
    $lines = explode("\n", $content);
    $rel = str_replace(realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR, '', realpath($path));

    foreach ($lines as $i => $line) {
        $lineNum = $i + 1;
        // Check for ?? '...' with non-empty string
        if (preg_match('/\?\?\s*([\'"][^\'"]+[\'"])/', $line, $m)) {
            // ignore empty quotes like ?? '' or ?? array()
            $val = trim($m[1], "'\"");
            if ($val !== '' && !in_array($val, ['ltr', 'rtl', 'dark', 'light', 'parchment', 'primary', 'secondary', 'outline', 'vertical', 'horizontal', 'gallery', 'product', 'review', 'social', 'team', 'memory'])) {
                $findings[] = [
                    'file' => $rel,
                    'line' => $lineNum,
                    'type' => 'FALLBACK_STRING',
                    'text' => trim($line)
                ];
            }
        }
        // Check for esc_html_e( '...', 'vintagesoul' ) or _e( '...' )
        if (preg_match('/(esc_html_e|esc_html__|__|_e)\s*\(\s*[\'"]([^\'"]+)[\'"]/', $line, $m)) {
            $findings[] = [
                'file' => $rel,
                'line' => $lineNum,
                'type' => 'GETTEXT_HARDCODED',
                'text' => trim($line)
            ];
        }
    }
}

foreach ($dirs as $d) {
    scanDirRecursive($d, $findings);
}

echo "TOTAL FINDINGS: " . count($findings) . "\n\n";
foreach ($findings as $f) {
    echo "[{$f['type']}] {$f['file']}:{$f['line']}\n  {$f['text']}\n\n";
}
