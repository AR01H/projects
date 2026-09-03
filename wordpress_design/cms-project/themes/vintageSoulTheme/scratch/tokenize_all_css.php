<?php
/**
 * Systematic CSS Color & Token Refactoring Script
 */

$cssDir = dirname(__DIR__) . '/assets/css';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cssDir));

$map = [
    // Brand Greens -> Dynamic client variables
    '#0c6434' => 'var(--client-brand-primary, #0c6434)',
    '#11381b' => 'var(--client-brand-dark, #11381b)',
    '#184b25' => 'var(--client-brand-rich, #184b25)',
    '#0a2512' => 'var(--client-brand-deep, #0a2512)',
    '#1b542a' => 'var(--client-brand-mid, #1b542a)',
    '#236836' => 'var(--client-brand-glow, #236836)',
    '#172b15' => 'var(--client-brand-moss, #172b15)',
    '#2d6e3c' => 'var(--client-brand-forest, #2d6e3c)',
    '#3d854e' => 'var(--client-brand-primary, #3d854e)',
    '#0d2f16' => 'var(--client-brand-deep, #0d2f16)',
    '#102d18' => 'var(--client-brand-dark, #102d18)',
    '#0a1f10' => 'var(--client-brand-deep, #0a1f10)',
    '#05140a' => 'var(--client-brand-deep, #05140a)',
    '#06150a' => 'var(--client-brand-deep, #06150a)',
    '#061a0c' => 'var(--client-brand-deep, #061a0c)',

    // Barks & Golds
    '#8e622d' => 'var(--vintage-bark-border, #8e622d)',
    '#caa06d' => 'var(--vintage-gold-inner, #caa06d)',
    '#f6d599' => 'var(--vintage-gold-bright, #f6d599)',
    '#cb924d' => 'var(--vintage-gold-accent, #cb924d)',
    '#d49842' => 'var(--vintage-gold-amber, #d49842)',
    '#fff4d9' => 'var(--vintage-gold-light, #fff4d9)',
    '#d6b88b' => 'var(--vintage-gold-inner, #d6b88b)',
    '#e5cdab' => 'var(--paper-100, #e5cdab)',
    '#a38965' => 'var(--bark-700, #a38965)',
    '#2b1406' => 'var(--vintage-bark-dark, #2b1406)',
    '#1a0b03' => 'var(--vintage-bark-deep, #1a0b03)',
    '#2b1705' => 'var(--color-text-strong, #2b1705)',

    // Parchments
    '#fbf2e6' => 'var(--vintage-parchment-light, #fbf2e6)',
    '#fdf8ef' => 'var(--vintage-parchment-warm, #fdf8ef)',
    '#fefaf2' => 'var(--vintage-parchment-paper, #fefaf2)',
    '#fdfaf4' => 'var(--vintage-parchment-clean, #fdfaf4)',
    '#f4dcc0' => 'var(--vintage-parchment-mid, #f4dcc0)',
    '#f7e6ce' => 'var(--paper-50, #f7e6ce)',
    '#f6e6ce' => 'var(--paper-100, #f6e6ce)',
    '#eed6b6' => 'var(--vintage-parchment-sand, #eed6b6)',
    '#eed2ae' => 'var(--vintage-parchment-deep, #eed2ae)',
    '#edd5ad' => 'var(--vintage-parchment-deep, #edd5ad)',
    '#e2c290' => 'var(--vintage-parchment-aged, #e2c290)',
    '#fcf2e3' => 'var(--vintage-parchment-light, #fcf2e3)',
    '#f6e0c3' => 'var(--paper-100, #f6e0c3)',
];

$totalReplaced = 0;
$modifiedFiles = 0;

foreach ($files as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'css') {
        continue;
    }
    
    $path = $file->getRealPath();
    if (strpos($path, 'variables.css') !== false) {
        // Do not touch definition file
        continue;
    }

    $content = file_get_contents($path);
    $original = $content;

    foreach ($map as $hex => $var) {
        // Match only standalone hex not inside data URIs
        // Ensure not preceded by var(-- or another replacement
        $pattern = '/(?<!var\([^)]*)\b' . preg_quote($hex, '/') . '\b(?![^<]*\))/i';
        
        // Simpler, robust replacement: don't replace if it's already inside var(..., #hex)
        $content = preg_replace_callback(
            '/(var\([^)]+\))|(' . preg_quote($hex, '/') . '\b)/i',
            function($matches) use ($hex, $var, &$totalReplaced) {
                if (!empty($matches[1])) {
                    // Already inside a var()
                    return $matches[1];
                }
                $totalReplaced++;
                return $var;
            },
            $content
        );
    }

    if ($content !== $original) {
        file_put_contents($path, $content);
        $modifiedFiles++;
        echo "Refactored: " . basename($path) . "\n";
    }
}

echo "\nSUCCESS: Replaced {$totalReplaced} hardcoded color instances across {$modifiedFiles} CSS files!\n";
