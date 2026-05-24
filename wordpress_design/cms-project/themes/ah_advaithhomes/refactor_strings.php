<?php
/**
 * Enhanced refactor_strings.php
 *
 * Scans all PHP files in the theme (excluding this script and common_terms.php)
 * and replaces hard‑coded user‑facing strings with constants defined in
 * includes/common_terms.php.
 *
 * It now handles:
 *   1. WordPress translation functions (original behaviour)
 *   2. HTML attribute literals (alt, title, placeholder, aria-label, etc.)
 *   3. Plain echo/print statements with quoted strings
 *
 * All extracted texts become constants prefixed with TXT_ and are appended
 * to common_terms.php via handle_defined().
 */

$dir = __DIR__;
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$constants = [];
$filesModified = 0;

foreach ($files as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php') continue;
    $path = $file->getRealPath();

    // Skip this utility script and the constants file
    if (strpos($path, 'refactor_strings.php') !== false) continue;
    if (strpos($path, 'common_terms.php') !== false) continue;

    $content = file_get_contents($path);
    $originalContent = $content;

    // 1️⃣ Translation functions (original regex)
    $translationPattern = '/(esc_html_e|esc_html__|__|esc_attr_e|esc_attr__|_e)\s*\(\s*([\'\"])(.*?)\2\s*,\s*([\'\"])ah-theme\4\s*\)/s';

    // 2️⃣ HTML attribute literals (alt, title, placeholder, aria-label, etc.)
    $attrPattern = '/\b(alt|title|placeholder|aria-label)\s*=\s*([\'\"])(.*?)\2/i';

    // 3️⃣ Echo/print with a plain string (non‑translation)
    $echoPattern = '/\b(echo|print)\s+([\'\"])(.*?)\2\s*;/i';

    // Helper to generate a constant name from a raw string
    $makeConst = function($str) use (&$constants) {
        $slug = strtoupper(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($str)));
        $slug = trim($slug, '_');
        if ($slug === '') $slug = 'EMPTY';
        $base = 'TXT_' . $slug;
        $name = $base;
        $i = 1;
        while (isset($constants[$name]) && $constants[$name] !== $str) {
            $name = $base . '_' . $i;
            $i++;
        }
        $constants[$name] = $str;
        return $name;
    };

    // Process translation functions
    $content = preg_replace_callback($translationPattern, function($m) use ($makeConst) {
        $func = $m[1];
        $str  = stripslashes($m[3]);
        $const = $makeConst($str);
        switch ($func) {
            case 'esc_html_e': return "echo esc_html( $const );";
            case 'esc_attr_e': return "echo esc_attr( $const );";
            case '_e':         return "echo $const;";
            case 'esc_html__': return "esc_html( $const );";
            case 'esc_attr__': return "esc_attr( $const );";
            case '__':         return $const;
        }
        return $m[0];
    }, $content);

    // Process HTML attributes
    $content = preg_replace_callback($attrPattern, function($m) use ($makeConst) {
        $attr = $m[1];
        $str  = stripslashes($m[3]);
        $const = $makeConst($str);
        // Use esc_attr for attributes
        return "$attr=\"<?php echo esc_attr( $const ); ?>\"";
    }, $content);

    // Process plain echo/print statements
    $content = preg_replace_callback($echoPattern, function($m) use ($makeConst) {
        $func = $m[1];
        $str  = stripslashes($m[3]);
        $const = $makeConst($str);
        // Escape HTML output for echo, plain print
        return "{$func} esc_html( $const );";
    }, $content);

    if ($content !== $originalContent) {
        file_put_contents($path, $content);
        $filesModified++;
    }
}

// Append newly collected constants to common_terms.php
if (!empty($constants)) {
    $common_terms_path = $dir . '/includes/common_terms.php';
    $appends = "\n// --- Automated Extracted Text Constants ---\n";
    foreach ($constants as $name => $val) {
        $escaped = addcslashes($val, "'\\");
        $appends .= "handle_defined( '$name', '$escaped' );\n";
    }
    file_put_contents($common_terms_path, $appends, FILE_APPEND);
}

echo "Modified $filesModified files.\n";
echo "Created " . count($constants) . " constants.\n";
?>
