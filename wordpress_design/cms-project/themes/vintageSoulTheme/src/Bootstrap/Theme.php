<?php
namespace VintageSoul\Bootstrap;

use VintageSoul\Services\AssetService;
use VintageSoul\Services\PostSeedService;
use VintageSoul\Services\RoutePageService;
use VintageSoul\Services\RouteService;
use VintageSoul\Services\SeoService;

defined( 'ABSPATH' ) || exit;

final class Theme {

	public static function init(): void {
		add_action( 'after_setup_theme', array( self::class, 'theme_support' ) );
		add_action( 'wp_enqueue_scripts', array( AssetService::class, 'enqueue' ) );
		add_action( 'wp_enqueue_scripts', array( AssetService::class, 'enqueue_page_assets' ) );
		add_action( 'wp_head', array( SeoService::class, 'render_page_seo' ) );
		add_action( 'init', array( RoutePageService::class, 'sync' ) );
		add_action( 'init', array( PostSeedService::class, 'seed' ) );
		add_action( 'admin_init', array( RoutePageService::class, 'sync' ) );
		add_action( 'admin_init', array( PostSeedService::class, 'seed' ) );
		add_action( 'pre_get_posts', array( self::class, 'filter_search_query' ) );
		add_filter( 'get_canonical_url', array( SeoService::class, 'filter_canonical_url' ), 10, 2 );
		add_filter( 'pre_get_document_title', array( SeoService::class, 'page_title' ) );
		add_filter( 'document_title_separator', array( self::class, 'title_separator' ) );
		add_filter( 'body_class', array( RouteService::class, 'add_body_class' ) );
	}

	public static function title_separator(): string {
		return SeoService::title_separator();
	}

	public static function filter_search_query( \WP_Query $query ): void {
		if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
			$dummy_page = get_page_by_path( 'sample-page', OBJECT, 'page' );
			if ( $dummy_page instanceof \WP_Post ) {
				$excluded = (array) $query->get( 'post__not_in' );
				$excluded[] = $dummy_page->ID;
				$query->set( 'post__not_in', array_unique( $excluded ) );
			}
		}
	}

	public static function theme_support(): void {
		load_theme_textdomain( 'vintagesoul', VINTAGESOUL_DIR . '/languages' );
		require VINTAGESOUL_DIR . '/config/theme-support.php';
	}
}
