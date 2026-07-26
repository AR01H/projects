<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * React Template — WordPress Theme Functions
 * Enqueues the React SPA assets from build/assets/
 */

add_action( 'wp_enqueue_scripts', function () {
  $theme_url = get_stylesheet_directory_uri();
  $build_dir = get_stylesheet_directory() . '/build/assets';
  $build_url = $theme_url . '/build/assets';

  // Find main JS bundle (index-*.js)
  $main_js = '';
  $js_files = glob( $build_dir . '/index-*.js' );
  if ( $js_files ) {
    $main_js = basename( $js_files[0] );
  }

  // Find main CSS (index-*.css)
  $main_css = '';
  $css_files = glob( $build_dir . '/index-*.css' );
  if ( $css_files ) {
    $main_css = basename( $css_files[0] );
  }

  // Enqueue CSS
  if ( $main_css ) {
    wp_enqueue_style(
      'rh-react-css',
      $build_url . '/' . $main_css,
      [],
      filemtime( $build_dir . '/' . $main_css )
    );
  }

  // Enqueue main JS bundle
  if ( $main_js ) {
    wp_enqueue_script(
      'rh-react-js',
      $build_url . '/' . $main_js,
      [],
      filemtime( $build_dir . '/' . $main_js ),
      [ 'type' => 'module' ]
    );
  }

  // Remove WordPress default block styles that conflict
  wp_dequeue_style( 'wp-block-library' );
  wp_dequeue_style( 'wp-block-library-theme' );
  wp_dequeue_style( 'wp-block-style' );
  wp_dequeue_style( 'global-styles' );
  wp_dequeue_style( 'classic-theme-styles' );
} );

// Remove WordPress emoji scripts
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

// Remove admin bar on front-end for cleaner look
add_filter( 'show_admin_bar', '__return_false' );
