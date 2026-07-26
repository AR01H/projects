<?php
/**
 * React Template — Serve React SPA
 * Self-contained: finds assets, outputs clean HTML
 * NO hooks, NO external functions, NO dependencies
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$dir  = get_stylesheet_directory();
$uri  = get_stylesheet_directory_uri();
$assets_dir = $dir . '/build/assets';
$assets_url = $uri . '/build/assets';

// Find main JS
$main_js = '';
$js = glob( $assets_dir . '/index-*.js' );
if ( $js ) $main_js = basename( $js[0] );

// Find main CSS
$main_css = '';
$css = glob( $assets_dir . '/index-*.css' );
if ( $css ) $main_css = basename( $css[0] );

header( 'Content-Type: text/html; charset=UTF-8' );
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <link rel="icon" type="image/svg+xml" href="<?php echo esc_url( $uri . '/build/favicon.svg' ); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <title><?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
<?php if ( $main_js ) : ?>
    <script type="module" src="<?php echo esc_url( $assets_url . '/' . $main_js ); ?>"></script>
<?php endif; ?>
<?php if ( $main_css ) : ?>
    <link rel="stylesheet" href="<?php echo esc_url( $assets_url . '/' . $main_css ); ?>" />
<?php endif; ?>
  </head>
  <body>
    <div id="root"></div>
  </body>
</html>
<?php exit; ?>
