<?php
namespace Adn\Theme\Shared;

defined( 'ABSPATH' ) || exit;

/**
 * BreadcrumbBuilder - Shared breadcrumb builders.
 *
 * Standardizes breadcrumb construction across all pages.
 * Each method returns a breadcrumb array (list of label/url items).
 */
class BreadcrumbBuilder {

	/**
	 * Home breadcrumb item.
	 */
	public static function home(): array {
		return array( 'label' => PAGE_TITLE_HOME, 'url' => '/' );
	}

	/**
	 * Simple 2-level: Home → Current page.
	 *
	 * Used by: Category, Tools, GuidesListing, AskExpert
	 */
	public static function simple( string $label, ?string $url = null ): array {
		return array(
			self::home(),
			array( 'label' => $label, 'url' => $url ),
		);
	}

	/**
	 * 3-level: Home → Parent → Current page.
	 *
	 * Used by: ToolSingle, ExpertSingle, TopicCategory
	 */
	public static function threeLevel( string $parentLabel, string $parentUrl, string $childLabel ): array {
		return array(
			self::home(),
			array( 'label' => $parentLabel, 'url' => $parentUrl ),
			array( 'label' => $childLabel, 'url' => null ),
		);
	}

	/**
	 * Category breadcrumb: Home → Category name.
	 *
	 * Used by: CategoryContext
	 */
	public static function category( string $name ): array {
		return self::simple( $name );
	}

	/**
	 * Tools listing breadcrumb: Home → Tools.
	 *
	 * Used by: ToolsContext
	 */
	public static function toolsListing(): array {
		return self::simple(
			defined( 'SITE_TOOLS_PLURAL' ) ? SITE_TOOLS_PLURAL : 'Calculators'
		);
	}

	/**
	 * Single tool breadcrumb: Home → Tools → Tool name.
	 *
	 * Used by: ToolSingleContext
	 */
	public static function toolSingle( string $title ): array {
		return self::threeLevel(
			defined( 'SITE_TOOLS_PLURAL' ) ? SITE_TOOLS_PLURAL : 'Calculators',
			home_url( defined( 'SITE_CALCULATORS_URL' ) ? SITE_CALCULATORS_URL : '/calculators/' ),
			$title
		);
	}

	/**
	 * Expert listing breadcrumb: Home → Expert page.
	 *
	 * Used by: AskExpertContext
	 */
	public static function expertListing(): array {
		$page_title = function_exists( 'get_the_title' ) ? ( get_the_title() ?: SITE_EXPERT_LABEL ) : SITE_EXPERT_LABEL;
		return self::simple( $page_title );
	}

	/**
	 * Single expert breadcrumb: Home → Experts → Expert name.
	 *
	 * Used by: ExpertSingleContext
	 */
	public static function expertSingle( string $name ): array {
		return self::threeLevel(
			SITE_EXPERT_LABEL,
			home_url( defined( 'SITE_EXPERT_URL' ) ? SITE_EXPERT_URL : '/experts/' ),
			$name
		);
	}

	/**
	 * Topic category breadcrumb: Home → Parent → Topic name.
	 *
	 * Used by: TopicCategoryContext
	 */
	public static function topicCategory( ?object $parent, string $term_name ): array {
		$bc = array( self::home() );
		if ( $parent && ! empty( $parent->name ) ) {
			$bc[] = array(
				'label' => $parent->name,
				'url'   => home_url( '/' . trim( $parent->slug, '/' ) . '/' ),
			);
		}
		$bc[] = array( 'label' => $term_name, 'url' => '' );
		return $bc;
	}

	/**
	 * Post/article breadcrumb with CMS fallback.
	 *
	 * Used by: PostContext
	 */
	public static function post( $post ): array {
		$_cms_bc = function_exists( 'adn_cms_post_breadcrumb' )
			? adn_cms_post_breadcrumb( $post->ID, get_the_title() )
			: null;
		if ( $_cms_bc ) {
			return $_cms_bc;
		}
		$bc   = array( self::home() );
		$cats = get_the_category( $post->ID );
		if ( ! empty( $cats ) ) {
			$bc[] = array( 'label' => $cats[0]->name, 'url' => get_category_link( $cats[0]->term_id ) );
		}
		$bc[] = array( 'label' => get_the_title(), 'url' => null );
		return $bc;
	}

	/**
	 * Guides listing breadcrumb: Home → Content section title.
	 *
	 * Used by: GuidesListingContext
	 */
	public static function guidesListing(): array {
		return self::simple(
			defined( 'SITE_CONTENT_PLURAL' ) ? SITE_CONTENT_PLURAL : 'Guides'
		);
	}
}
