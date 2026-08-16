<?php

defined( 'ABSPATH' ) || exit;

add_theme_support( 'title-tag' );
add_theme_support( 'post-thumbnails' );
add_theme_support( 'responsive-embeds' );
add_theme_support( 'automatic-feed-links' );
add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
add_theme_support( 'customize-selective-refresh-widgets' );
add_theme_support( 'align-wide' );
add_theme_support(
	'custom-logo',
	array(
		'height'      => 48,
		'width'       => 200,
		'flex-height' => true,
		'flex-width'  => true,
	)
);

register_nav_menus( array(
	'primary' => __( 'Primary Navigation', 'vintagesoul' ),
	'footer'  => __( 'Footer Navigation', 'vintagesoul' ),
) );

add_image_size( 'vintagesoul-card', 640, 480, true );
add_image_size( 'vintagesoul-hero', 1920, 1080, true );
