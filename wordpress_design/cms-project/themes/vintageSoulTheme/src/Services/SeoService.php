<?php
namespace VintageSoul\Services;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

/**
 * SeoService — Centralized SEO manager for VintageSoulTheme.
 *
 * Provides:
 * - Dynamic meta titles, descriptions, and keywords
 * - Robots indexing directives (index/noindex, max-image-preview:large, max-snippet:-1)
 * - Clean self-referencing canonical URLs & UK English hreflang tags
 * - OpenGraph & Twitter Cards (supporting websites & articles)
 * - JSON-LD Structured Data: WebSite (with search), Organization/FoodEstablishment, Article/BlogPosting, BreadcrumbList
 * - Seamless compatibility and filters for Rank Math & Yoast SEO
 */
final class SeoService {

	/** @var array<string,mixed>|null */
	private static ?array $data = null;

	/** Load and merge seo data from data/content/seo.json and config/seo.json */
	private static function data(): array {
		if ( null === self::$data ) {
			$content = JsonFileProvider::read( 'data/content/seo.json' );
			$config  = JsonFileProvider::read( 'config/seo.json' );
			$merged  = is_array( $content ) ? $content : array();

			if ( is_array( $config ) ) {
				$merged['global'] = array_merge(
					array(
						'default_description' => $config['default_meta_description'] ?? '',
						'separator'           => $config['title_separator'] ?? ' | ',
						'og_image'            => $config['og_default_image'] ?? '',
					),
					$merged['global'] ?? array()
				);
				if ( ! empty( $config['organization'] ) && empty( $merged['organization'] ) ) {
					$merged['organization'] = $config['organization'];
				}
			}

			self::$data = $merged;
		}
		return self::$data;
	}

	public static function global( string $key, string $default = '' ): string {
		$g = self::data()['global'] ?? array();
		return (string) ( $g[ $key ] ?? $default );
	}

	public static function page( string $page_key, string $field, string $default = '' ): string {
		$p = self::data()['pages'][ $page_key ] ?? array();
		return (string) ( $p[ $field ] ?? $default );
	}

	public static function organization(): array {
		return (array) ( self::data()['organization'] ?? array() );
	}

	public static function site_name(): string {
		return self::global( 'site_name', get_bloginfo( 'name' ) ?: 'The Cane House' );
	}

	public static function title_separator(): string {
		return self::global( 'separator', ' | ' );
	}

	/** Detect current page key from WordPress context */
	public static function current_page_key(): string {
		if ( is_front_page() || is_home() ) {
			return 'home';
		}
		if ( is_404() ) {
			return '404';
		}
		if ( is_search() ) {
			return 'search';
		}

		$current_slug = get_post_field( 'post_name', get_queried_object_id() );
		if ( $current_slug ) {
			$key = RouteService::key_for_slug( (string) $current_slug );
			if ( $key ) {
				return $key;
			}
			$known = array( 'about', 'history', 'events', 'franchise', 'contact', 'blog' );
			if ( in_array( $current_slug, $known, true ) ) {
				return (string) $current_slug;
			}
		}

		return 'home';
	}

	/** Whether the current request should be marked noindex */
	public static function is_noindex(): bool {
		if ( is_404() || is_search() || is_attachment() || is_author() || is_date() ) {
			return true;
		}
		$key = self::current_page_key();
		if ( '404' === $key ) {
			return true;
		}
		$noindex = self::page( $key, 'noindex' );
		return '1' === $noindex || 'true' === $noindex;
	}

	/** Get page document title */
	public static function page_title( string $page_key = '' ): string {
		if ( is_singular( 'post' ) ) {
			$title = get_the_title();
			return ( '' !== $title ? $title : 'Article' ) . self::title_separator() . self::site_name();
		}
		if ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			$name = $term instanceof \WP_Term ? $term->name : '';
			return ( '' !== $name ? $name : 'Archive' ) . self::title_separator() . self::site_name();
		}
		if ( is_search() ) {
			return 'Search: ' . get_search_query() . self::title_separator() . self::site_name();
		}
		if ( is_404() ) {
			return 'Page Not Found' . self::title_separator() . self::site_name();
		}

		$key   = '' !== $page_key ? $page_key : self::current_page_key();
		$title = self::page( $key, 'title' );
		if ( '' !== $title ) {
			return $title;
		}
		return self::site_name();
	}

	/** Filter for pre_get_document_title */
	public static function document_title( string $title = '' ): string {
		$custom = self::page_title();
		return '' !== $custom ? $custom : $title;
	}

	/** Get page meta description */
	public static function page_description( string $page_key = '' ): string {
		if ( is_singular() ) {
			global $post;
			if ( $post instanceof \WP_Post ) {
				$excerpt = get_the_excerpt( $post );
				if ( '' !== trim( $excerpt ) ) {
					return wp_strip_all_tags( $excerpt );
				}
				$content = wp_strip_all_tags( $post->post_content );
				if ( '' !== $content ) {
					return wp_trim_words( $content, 28, '...' );
				}
			}
		}
		if ( is_category() || is_tag() || is_tax() ) {
			$desc = term_description();
			if ( '' !== trim( $desc ) ) {
				return wp_strip_all_tags( $desc );
			}
		}
		if ( is_search() ) {
			return 'Search results for ' . esc_html( get_search_query() ) . ' on ' . self::site_name() . '.';
		}
		if ( is_404() ) {
			return 'The page you requested could not be found on ' . self::site_name() . '.';
		}

		$key  = '' !== $page_key ? $page_key : self::current_page_key();
		$desc = self::page( $key, 'description' );
		if ( '' !== $desc ) {
			return $desc;
		}
		return self::global( 'default_description' );
	}

	/** Get page keywords */
	public static function page_keywords( string $page_key = '' ): string {
		$key = '' !== $page_key ? $page_key : self::current_page_key();
		return self::page( $key, 'keywords' );
	}

	/** Get canonical URL */
	public static function canonical_url( string $page_key = '' ): string {
		if ( is_front_page() ) {
			return home_url( '/' );
		}
		if ( is_singular() ) {
			$permalink = (string) get_permalink();
			return '' !== $permalink ? strtok( $permalink, '?' ) : home_url( '/' );
		}
		if ( is_category() || is_tag() || is_tax() ) {
			$link = (string) get_term_link( get_queried_object() );
			return ! is_wp_error( $link ) && '' !== $link ? strtok( $link, '?' ) : home_url( '/' );
		}

		$key = '' !== $page_key ? $page_key : self::current_page_key();
		if ( 'home' === $key ) {
			return home_url( '/' );
		}
		$route_url = RouteService::url( $key );
		if ( '' !== $route_url ) {
			return trailingslashit( strtok( $route_url, '?' ) );
		}

		$queried = get_queried_object_id();
		if ( $queried > 0 ) {
			$link = (string) get_permalink( $queried );
			if ( '' !== $link ) {
				return trailingslashit( strtok( $link, '?' ) );
			}
		}

		return home_url( '/' );
	}

	/** Get OG title */
	public static function og_title( string $page_key = '' ): string {
		$key = '' !== $page_key ? $page_key : self::current_page_key();
		$og  = self::page( $key, 'og_title' );
		return '' !== $og ? $og : self::page_title( $key );
	}

	/** Get OG description */
	public static function og_description( string $page_key = '' ): string {
		$key = '' !== $page_key ? $page_key : self::current_page_key();
		$og  = self::page( $key, 'og_description' );
		return '' !== $og ? $og : self::page_description( $key );
	}

	/** Get OG image URL */
	public static function og_image_url( string $page_key = '' ): string {
		if ( is_singular() ) {
			$thumb_id = get_post_thumbnail_id();
			if ( $thumb_id ) {
				$thumb_url = wp_get_attachment_image_url( $thumb_id, 'large' );
				if ( $thumb_url ) {
					return $thumb_url;
				}
			}
		}
		$img = self::global( 'og_image' );
		return '' !== $img ? UrlHelper::resolve( $img ) : '';
	}

	/** Build WebSite Schema (JSON-LD) */
	public static function schema_website(): array {
		return array(
			'@context'        => 'https://schema.org',
			'@type'           => 'WebSite',
			'name'            => self::site_name(),
			'url'             => home_url( '/' ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => home_url( '/?s={search_term_string}' ),
				),
				'query-input' => 'required name=search_term_string',
			),
		);
	}

	/** Build Organization / FoodEstablishment Schema (JSON-LD) */
	public static function schema_organization(): array {
		$org   = self::organization();
		$name  = (string) ( $org['name'] ?? self::site_name() );
		$url   = (string) ( $org['url'] ?? home_url( '/' ) );
		$logo  = (string) ( $org['logo'] ?? 'assets/images/logos/logo.png' );
		$image = (string) ( $org['image'] ?? 'assets/images/backgrounds/og-image.jpg' );

		$schema = array(
			'@context' => 'https://schema.org',
			'@type'    => (string) ( $org['type'] ?? 'FoodEstablishment' ),
			'name'     => $name,
			'url'      => UrlHelper::resolve( $url ),
			'logo'     => array(
				'@type' => 'ImageObject',
				'url'   => UrlHelper::resolve( $logo ),
			),
			'image'    => UrlHelper::resolve( $image ),
		);

		if ( ! empty( $org['legalName'] ) ) {
			$schema['legalName'] = (string) $org['legalName'];
		}
		if ( ! empty( $org['telephone'] ) ) {
			$schema['telephone'] = (string) $org['telephone'];
		}
		if ( ! empty( $org['email'] ) ) {
			$schema['email'] = (string) $org['email'];
		}
		if ( ! empty( $org['priceRange'] ) ) {
			$schema['priceRange'] = (string) $org['priceRange'];
		}
		if ( ! empty( $org['servesCuisine'] ) ) {
			$schema['servesCuisine'] = (array) $org['servesCuisine'];
		}
		if ( ! empty( $org['address'] ) && is_array( $org['address'] ) ) {
			$schema['address'] = array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => (string) ( $org['address']['streetAddress'] ?? '' ),
				'addressLocality' => (string) ( $org['address']['addressLocality'] ?? 'London' ),
				'addressRegion'   => (string) ( $org['address']['addressRegion'] ?? 'Greater London' ),
				'postalCode'      => (string) ( $org['address']['postalCode'] ?? '' ),
				'addressCountry'  => (string) ( $org['address']['addressCountry'] ?? 'GB' ),
			);
		}
		if ( ! empty( $org['geo'] ) && is_array( $org['geo'] ) ) {
			$schema['geo'] = array(
				'@type'     => 'GeoCoordinates',
				'latitude'  => (float) ( $org['geo']['latitude'] ?? 51.5074 ),
				'longitude' => (float) ( $org['geo']['longitude'] ?? -0.1278 ),
			);
		}
		if ( ! empty( $org['sameAs'] ) && is_array( $org['sameAs'] ) ) {
			$schema['sameAs'] = array_values( array_filter( array_map( 'trim', (array) $org['sameAs'] ) ) );
		}

		return $schema;
	}

	/** Build Article / BlogPosting Schema (JSON-LD) */
	public static function schema_article(): ?array {
		if ( ! is_singular( 'post' ) ) {
			return null;
		}
		global $post;
		if ( ! $post instanceof \WP_Post ) {
			return null;
		}

		$canonical = self::canonical_url();
		$thumb_id  = get_post_thumbnail_id( $post );
		$thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'large' ) : self::og_image_url();
		$author    = get_the_author_meta( 'display_name', $post->post_author ) ?: self::site_name();
		$org_logo  = UrlHelper::resolve( (string) ( self::organization()['logo'] ?? 'assets/images/logos/logo.png' ) );

		return array(
			'@context'         => 'https://schema.org',
			'@type'            => 'BlogPosting',
			'mainEntityOfPage' => array(
				'@type' => 'WebPage',
				'@id'   => $canonical,
			),
			'headline'         => get_the_title( $post ),
			'description'      => self::page_description(),
			'image'            => $thumb_url,
			'url'              => $canonical,
			'datePublished'    => get_the_date( 'c', $post ),
			'dateModified'     => get_the_modified_date( 'c', $post ),
			'author'           => array(
				'@type' => 'Person',
				'name'  => $author,
			),
			'publisher'        => array(
				'@type' => 'Organization',
				'name'  => self::site_name(),
				'logo'  => array(
					'@type' => 'ImageObject',
					'url'   => $org_logo,
				),
			),
		);
	}

	/** Build BreadcrumbList Schema (JSON-LD) */
	public static function schema_breadcrumbs(): array {
		$items = array(
			array(
				'@type'    => 'ListItem',
				'position' => 1,
				'name'     => 'Home',
				'item'     => home_url( '/' ),
			),
		);

		if ( is_front_page() || is_home() ) {
			return array();
		}

		if ( is_singular( 'post' ) ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => 2,
				'name'     => 'The Cane Chronicle',
				'item'     => UrlHelper::resolve( '/blog' ),
			);
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => 3,
				'name'     => get_the_title(),
				'item'     => self::canonical_url(),
			);
		} elseif ( is_page() ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => 2,
				'name'     => get_the_title(),
				'item'     => self::canonical_url(),
			);
		}

		if ( count( $items ) <= 1 ) {
			return array();
		}

		return array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $items,
		);
	}

	/** Generate JSON-LD FAQPage Schema for Rich FAQ Snippets */
	public static function schema_faq( string $page_key = '' ): array {
		$key       = '' !== $page_key ? $page_key : self::current_page_key();
		$faq_items = array();

		if ( 'events' === $key ) {
			$data      = JsonFileProvider::read( 'data/content/events.json' );
			$faq_items = (array) ( $data['faqs']['items'] ?? array() );
		} elseif ( 'franchise' === $key ) {
			$data      = JsonFileProvider::read( 'data/content/franchise.json' );
			$faq_items = (array) ( $data['faqs']['items'] ?? array() );
		} elseif ( 'contact' === $key ) {
			$data      = JsonFileProvider::read( 'data/content/contact-page.json' );
			$faq_items = (array) ( $data['faqs'] ?? array() );
		} elseif ( 'about' === $key ) {
			$data      = JsonFileProvider::read( 'data/content/faqs-product.json' );
			$faq_items = (array) ( $data['items'] ?? array() );
		} elseif ( 'home' === $key || is_front_page() || is_home() ) {
			$data      = JsonFileProvider::read( 'data/content/faqs.json' );
			$faq_items = (array) ( $data['items'] ?? array() );
		}

		if ( empty( $faq_items ) ) {
			return array();
		}

		$entities = array();
		foreach ( $faq_items as $item ) {
			$item = (array) $item;
			$q    = trim( (string) ( $item['q'] ?? $item['question'] ?? $item['title'] ?? '' ) );
			$a    = trim( (string) ( $item['a'] ?? $item['answer'] ?? $item['desc'] ?? '' ) );
			if ( '' !== $q && '' !== $a ) {
				$entities[] = array(
					'@type'          => 'Question',
					'name'           => wp_strip_all_tags( $q ),
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => wp_strip_all_tags( $a ),
					),
				);
			}
		}

		if ( empty( $entities ) ) {
			return array();
		}

		return array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $entities,
		);
	}

	/** Render all SEO tags and JSON-LD markup into wp_head */
	public static function render_page_seo( string $page_key = '' ): void {
		$yoast_on    = defined( 'WPSEO_VERSION' );
		$rankmath_on = defined( 'RANK_MATH_VERSION' );

		$key         = '' !== $page_key ? $page_key : self::current_page_key();
		$title       = self::page_title( $key );
		$description = self::page_description( $key );
		$keywords    = self::page_keywords( $key );
		$canonical   = self::canonical_url( $key );
		$og_title    = self::og_title( $key );
		$og_desc     = self::og_description( $key );
		$og_image    = self::og_image_url( $key );
		$site_name   = self::site_name();
		$locale      = self::global( 'locale', 'en_GB' );
		$twitter     = self::global( 'twitter_handle', '@thecanehouseuk' );
		$is_article  = is_singular( 'post' );
		$noindex     = self::is_noindex();

		// Always output prefetch & feeds
		echo '<link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">' . "\n";
		echo '<link rel="alternate" type="application/rss+xml" title="' . esc_attr( $site_name . ' &raquo; Feed' ) . '" href="' . esc_url( get_feed_link() ) . '">' . "\n";

		// Self-referencing Hreflang for UK English
		echo '<link rel="alternate" hreflang="en-GB" href="' . esc_url( $canonical ) . '">' . "\n";
		echo '<link rel="alternate" hreflang="x-default" href="' . esc_url( $canonical ) . '">' . "\n";

		// Direct meta tags when no SEO plugin manages them
		if ( ! $yoast_on && ! $rankmath_on ) {
			if ( $noindex ) {
				echo '<meta name="robots" content="noindex, follow">' . "\n";
			} else {
				echo '<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">' . "\n";
			}

			if ( '' !== $description ) {
				echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
			}
			if ( '' !== $keywords ) {
				echo '<meta name="keywords" content="' . esc_attr( $keywords ) . '">' . "\n";
			}
			if ( '' !== $canonical ) {
				echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
			}

			// OpenGraph
			echo '<meta property="og:type" content="' . ( $is_article ? 'article' : 'website' ) . '">' . "\n";
			echo '<meta property="og:locale" content="' . esc_attr( $locale ) . '">' . "\n";
			echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '">' . "\n";
			echo '<meta property="og:title" content="' . esc_attr( $og_title ) . '">' . "\n";
			if ( '' !== $og_desc ) {
				echo '<meta property="og:description" content="' . esc_attr( $og_desc ) . '">' . "\n";
			}
			echo '<meta property="og:url" content="' . esc_url( $canonical ) . '">' . "\n";
			if ( '' !== $og_image ) {
				echo '<meta property="og:image" content="' . esc_url( $og_image ) . '">' . "\n";
			}
			if ( $is_article ) {
				echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c' ) ) . '">' . "\n";
				echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . "\n";
				echo '<meta property="article:author" content="' . esc_attr( get_the_author_meta( 'display_name' ) ?: $site_name ) . '">' . "\n";
			}

			// Twitter Card
			echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
			if ( '' !== $twitter ) {
				echo '<meta name="twitter:site" content="' . esc_attr( $twitter ) . '">' . "\n";
				echo '<meta name="twitter:creator" content="' . esc_attr( $twitter ) . '">' . "\n";
			}
			echo '<meta name="twitter:title" content="' . esc_attr( $og_title ) . '">' . "\n";
			if ( '' !== $og_desc ) {
				echo '<meta name="twitter:description" content="' . esc_attr( $og_desc ) . '">' . "\n";
			}
			if ( '' !== $og_image ) {
				echo '<meta name="twitter:image" content="' . esc_url( $og_image ) . '">' . "\n";
			}
		}

		// JSON-LD Structured Data
		$schemas = array();
		if ( is_front_page() || is_home() ) {
			$schemas[] = self::schema_website();
		}
		$schemas[] = self::schema_organization();

		$article_schema = self::schema_article();
		if ( $article_schema ) {
			$schemas[] = $article_schema;
		}

		$breadcrumb_schema = self::schema_breadcrumbs();
		if ( ! empty( $breadcrumb_schema ) ) {
			$schemas[] = $breadcrumb_schema;
		}

		$faq_schema = self::schema_faq( $key );
		if ( ! empty( $faq_schema ) ) {
			$schemas[] = $faq_schema;
		}

		foreach ( $schemas as $schema ) {
			if ( ! empty( $schema ) ) {
				echo '<script type="application/ld+json">' . "\n";
				echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
				echo "\n</script>\n";
			}
		}
	}

	// ───── Rank Math & Yoast Filter Bridges ──────────────────────────────────

	public static function filter_rank_math_canonical( $canonical ): string {
		$custom = self::canonical_url();
		return '' !== $custom ? $custom : (string) $canonical;
	}

	public static function filter_rank_math_description( $desc ): string {
		$custom = self::page_description();
		return '' !== $custom ? $custom : (string) $desc;
	}

	public static function filter_rank_math_robots( array $robots ): array {
		if ( self::is_noindex() ) {
			return array(
				'noindex' => 'noindex',
				'follow'  => 'follow',
			);
		}
		return array(
			'index'             => 'index',
			'follow'            => 'follow',
			'max-snippet'       => 'max-snippet:-1',
			'max-video-preview' => 'max-video-preview:-1',
			'max-image-preview' => 'max-image-preview:large',
		);
	}

	public static function filter_canonical_url( string $canonical_url, ?\WP_Post $post = null ): string {
		return self::canonical_url();
	}

	public static function register_hooks(): void {
		add_action( 'wp_head', array( self::class, 'render_page_seo' ), 1 );
		add_filter( 'pre_get_document_title', array( self::class, 'document_title' ), 99 );
		add_filter( 'document_title_separator', array( self::class, 'title_separator' ) );
		add_filter( 'get_canonical_url', array( self::class, 'filter_canonical_url' ), 10, 2 );

		// Rank Math Filters
		add_filter( 'rank_math/frontend/title', array( self::class, 'document_title' ), 99 );
		add_filter( 'rank_math/frontend/description', array( self::class, 'filter_rank_math_description' ), 99 );
		add_filter( 'rank_math/frontend/canonical', array( self::class, 'filter_rank_math_canonical' ), 99 );
		add_filter( 'rank_math/frontend/robots', array( self::class, 'filter_rank_math_robots' ), 99 );

		// Yoast Filters
		add_filter( 'wpseo_title', array( self::class, 'document_title' ), 99 );
		add_filter( 'wpseo_metadesc', array( self::class, 'filter_rank_math_description' ), 99 );
		add_filter( 'wpseo_canonical', array( self::class, 'filter_rank_math_canonical' ), 99 );
		add_filter( 'wpseo_robots_array', array( self::class, 'filter_rank_math_robots' ), 99 );

		// Suppress duplicate WP core tags when no plugin is running
		if ( ! defined( 'WPSEO_VERSION' ) && ! defined( 'RANK_MATH_VERSION' ) ) {
			remove_action( 'wp_head', 'rel_canonical' );
			remove_action( 'wp_head', 'wp_robots', 1 );
		}
	}
}

