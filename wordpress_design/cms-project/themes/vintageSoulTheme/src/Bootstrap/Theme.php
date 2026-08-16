<?php
namespace VintageSoul\Bootstrap;

use VintageSoul\Services\AssetService;
use VintageSoul\Services\RoutePageService;
use VintageSoul\Services\RouteService;
use VintageSoul\Services\SeoService;

defined( 'ABSPATH' ) || exit;

final class Theme {

	public static function init(): void {
		add_action( 'after_setup_theme', array( self::class, 'theme_support' ) );
		add_action( 'wp_enqueue_scripts', array( AssetService::class, 'enqueue' ) );
		add_action( 'wp_enqueue_scripts', array( AssetService::class, 'enqueue_page_assets' ) );
		add_action( 'wp_head', array( SeoService::class, 'render_meta_description' ) );
		add_action( 'admin_init', array( RoutePageService::class, 'sync' ) );
		add_filter( 'get_canonical_url', array( SeoService::class, 'filter_canonical_url' ), 10, 2 );
		add_filter( 'document_title_separator', array( self::class, 'title_separator' ) );
		add_filter( 'body_class', array( RouteService::class, 'add_body_class' ) );
	}

	public static function title_separator(): string {
		return SeoService::title_separator();
	}

	public static function theme_support(): void {
		load_theme_textdomain( 'vintagesoul', VINTAGESOUL_DIR . '/languages' );
		require VINTAGESOUL_DIR . '/config/theme-support.php';
	}
}
