<?php
defined( 'ABSPATH' ) || exit;

class AH_Theme_Setup {

	public static function init(): void {
		add_action( 'after_setup_theme', array( self::class, 'setup' ) );
	}

	public static function setup(): void {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
		add_theme_support( 'custom-logo' );
		add_theme_support( 'menus' );

		load_theme_textdomain( 'ah-theme', AH_THEME_DIR . '/languages' );

		register_nav_menus( array(
			'primary' => __( 'Primary Menu', 'ah-theme' ),
			'footer'  => __( 'Footer Menu', 'ah-theme' ),
		) );

		add_shortcode( 'ah_form', array( 'AH_Form_Builder', 'render' ) );
	}
}
