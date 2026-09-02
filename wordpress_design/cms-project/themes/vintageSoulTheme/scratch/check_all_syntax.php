<?php

$dir = __DIR__ . '/..';

function check_php_syntax($dir) {
    $files = scandir($dir);
    $all_clean = true;
    foreach ($files as $f) {
        if ($f === '.' || $f === '..' || $f === 'node_modules' || $f === 'vendor' || $f === '.git') continue;
        $path = $dir . '/' . $f;
        if (is_dir($path)) {
            if (!check_php_syntax($path)) $all_clean = false;
        } else if (substr($f, -4) === '.php') {
            $output = [];
            $code = 0;
            exec("php -l " . escapeshellarg($path), $output, $code);
            if ($code !== 0) {
                echo "SYNTAX ERROR in $path:\n" . implode("\n", $output) . "\n";
                $all_clean = false;
            }
        }
    }
    return $all_clean;
}

if (check_php_syntax($dir)) {
    echo "ALL PHP FILES ARE 100% VALID WITH ZERO SYNTAX ERRORS!\n";
}
