<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

class SeoService {

	private static ?array $config = null;

	private static function readJson( string $path ): array {
		if ( ! file_exists( $path ) ) {
			return array();
		}
		$raw = file_get_contents( $path );
		$cfg = json_decode( $raw, true );
		return is_array( $cfg ) ? $cfg : array();
	}

	public static function config(): array {
		if ( null !== self::$config ) {
			return self::$config;
		}

		$custom = ( defined( 'DATA_FILES' ) && DATA_FILES ) ? trim( DATA_FILES, '/' ) . '/' : '';
		$paths  = array(
			get_template_directory() . '/data/' . $custom . 'config/seo.json',
			get_template_directory() . '/data/config/seo.json',
		);

		foreach ( $paths as $path ) {
			if ( file_exists( $path ) ) {
				self::$config = self::readJson( $path );
				return self::$config;
			}
		}

		self::$config = array();
		return self::$config;
	}

	public static function getConfigValue( string $key, $default = '' ) {
		$config = self::config();
		$parts  = explode( '.', $key );
		$value  = $config;

		foreach ( $parts as $part ) {
			if ( is_array( $value ) && array_key_exists( $part, $value ) ) {
				$value = $value[ $part ];
			} else {
				return $default;
			}
		}

		return $value;
	}

	/**
	 * Internal link graph for search engines, driven by seo.json -> site_links.
	 *
	 * Emitted as JSON-LD in <head>, which is the sanctioned way to expose an
	 * internal link graph: invisible to visitors, read by crawlers. Rendering the
	 * same links as hidden HTML (display:none, off-screen, zero opacity) is
	 * classed as hidden-link spam by Google and risks a demotion, so it is
	 * deliberately not done here. For links that should carry real crawl weight,
	 * put them somewhere visible - a footer block or an HTML sitemap page.
	 *
	 * @return array Empty when disabled or out of scope, so nothing is printed.
	 */
	public static function siteLinksSchema(): array {
		$cfg = self::getConfigValue( 'site_links', array() );
		if ( ! is_array( $cfg ) || empty( $cfg['enabled'] ) ) {
			return array();
		}

		// scope: "front" (default) puts it on the home page only, "all" site-wide.
		$scope = isset( $cfg['scope'] ) ? (string) $cfg['scope'] : 'front';
		if ( 'all' !== $scope && ! is_front_page() ) {
			return array();
		}

		$items    = array();
		$position = 0;
		foreach ( (array) ( $cfg['items'] ?? array() ) as $item ) {
			$name = isset( $item['name'] ) ? trim( wp_strip_all_tags( (string) $item['name'] ) ) : '';
			$url  = isset( $item['url'] ) ? trim( (string) $item['url'] ) : '';
			if ( '' === $name || '' === $url ) {
				continue;
			}
			// An absolute URL is used as authored; only a site-relative path is resolved.
			$absolute = preg_match( '~^https?://~i', $url ) ? $url : home_url( $url );
			$items[]  = array(
				'@type'    => 'SiteNavigationElement',
				'position' => ++$position,
				'name'     => $name,
				'url'      => $absolute,
			);
		}

		if ( ! $items ) {
			return array();
		}

		return array(
			'@context'        => 'https://schema.org',
			'@type'           => 'ItemList',
			'name'            => isset( $cfg['name'] ) ? (string) $cfg['name'] : 'Site navigation',
			'itemListElement' => $items,
		);
	}

	/**
	 * The site_links items, normalised to { name, url } with empties dropped.
	 */
	public static function siteLinksItems(): array {
		$cfg  = self::getConfigValue( 'site_links', array() );
		$out  = array();
		foreach ( (array) ( is_array( $cfg ) ? ( $cfg['items'] ?? array() ) : array() ) as $item ) {
			$name = isset( $item['name'] ) ? trim( wp_strip_all_tags( (string) $item['name'] ) ) : '';
			$url  = isset( $item['url'] ) ? trim( (string) $item['url'] ) : '';
			if ( '' !== $name && '' !== $url ) {
				$out[] = array( 'name' => $name, 'url' => $url );
			}
		}
		return $out;
	}

	/**
	 * Footer link list for every page, driven by seo.json -> site_links.footer.
	 *
	 * mode:
	 *   "collapsed" - a <details> block, closed until clicked. Recommended: the
	 *                 links are genuinely reachable by visitors, so search
	 *                 engines treat them as ordinary content.
	 *   "visible"   - a plain list, always shown.
	 *   "hidden"    - display:none. Note this is what Google's spam policies call
	 *                 hidden links; it can get a site demoted rather than boosted.
	 *                 Kept because it was explicitly asked for.
	 */
	public static function siteLinksFooterHtml(): string {
		$cfg = self::getConfigValue( 'site_links', array() );
		if ( ! is_array( $cfg ) ) {
			return '';
		}
		$foot = isset( $cfg['footer'] ) && is_array( $cfg['footer'] ) ? $cfg['footer'] : array();
		if ( empty( $foot['enabled'] ) ) {
			return '';
		}

		$items = self::siteLinksItems();
		if ( ! $items ) {
			return '';
		}

		$mode  = isset( $foot['mode'] ) ? sanitize_key( (string) $foot['mode'] ) : 'collapsed';
		$mode  = in_array( $mode, array( 'collapsed', 'visible', 'hidden' ), true ) ? $mode : 'collapsed';
		$title = isset( $foot['title'] ) ? (string) $foot['title'] : 'All pages';

		$links = '';
		foreach ( $items as $item ) {
			$links .= '<li><a href="' . esc_url( adn_link( $item['url'] ) ) . '">'
				. esc_html( $item['name'] ) . '</a></li>';
		}
		$list = '<ul class="adn-sitelinks__list">' . $links . '</ul>';

		if ( 'collapsed' === $mode ) {
			return '<nav class="adn-sitelinks adn-sitelinks--collapsed" aria-label="' . esc_attr( $title ) . '">'
				. '<details><summary>' . esc_html( $title ) . '</summary>' . $list . '</details></nav>';
		}
		if ( 'visible' === $mode ) {
			return '<nav class="adn-sitelinks adn-sitelinks--visible" aria-label="' . esc_attr( $title ) . '">'
				. '<h3 class="adn-sitelinks__title">' . esc_html( $title ) . '</h3>' . $list . '</nav>';
		}
		// hidden
		return '<nav class="adn-sitelinks adn-sitelinks--hidden hidden" aria-label="' . esc_attr( $title ) . '">'
			. $list . '</nav>';
	}

	/**
	 * Site-wide keyword block for the footer, from seo.json -> site_keywords.
	 *
	 * The same words already go out as <meta name="keywords"> on every page, which
	 * is the sanctioned slot. Repeating them as hidden on-page text is keyword
	 * stuffing plus hidden text - the pair automated spam detection is tuned
	 * hardest for. Set "enabled": false, or "mode": "visible", to step away from
	 * that. Kept because it was explicitly asked for.
	 */
	public static function siteKeywordsHtml(): string {
		$cfg = self::getConfigValue( 'site_keywords', array() );
		if ( ! is_array( $cfg ) || empty( $cfg['enabled'] ) ) {
			return '';
		}

		$words = array();
		foreach ( (array) ( $cfg['words'] ?? array() ) as $word ) {
			$word = trim( wp_strip_all_tags( (string) $word ) );
			if ( '' !== $word ) {
				$words[] = $word;
			}
		}
		if ( ! $words ) {
			return '';
		}

		$mode = isset( $cfg['mode'] ) ? sanitize_key( (string) $cfg['mode'] ) : 'hidden';
		$mode = in_array( $mode, array( 'hidden', 'visible' ), true ) ? $mode : 'hidden';

		return '<p class="adn-keywords adn-keywords--' . esc_attr( $mode ) . '">'
			. esc_html( implode( ', ', $words ) ) . '</p>';
	}

	public static function pageConfig( string $page, array $default = array() ): array {
		$pages = self::getConfigValue( 'pages', array() );
		if ( ! is_array( $pages ) ) {
			return $default;
		}

		$page = sanitize_key( $page );
		if ( '' === $page || empty( $pages[ $page ] ) || ! is_array( $pages[ $page ] ) ) {
			return $default;
		}

		return array_merge( $default, $pages[ $page ] );
	}

	public static function register( array $meta ): void {
		$GLOBALS['adn_seo'] = array_merge( $GLOBALS['adn_seo'] ?? array(), $meta );
	}

	public static function documentTitle( string $title ): string {
		$reg    = (array) ( $GLOBALS['adn_seo'] ?? array() );
		$custom = trim( (string) ( $reg['title'] ?? '' ) );
		if ( '' === $custom ) {
			return $title;
		}
		$site_name = (string) get_bloginfo( 'name' );
		return $custom . ' | ' . $site_name;
	}

	public static function resolve(): array {
		$reg  = (array) ( $GLOBALS['adn_seo'] ?? array() );
		$post = get_queried_object();

		$title = trim( (string) ( $reg['title'] ?? '' ) );
		if ( '' === $title ) {
			if ( $post instanceof \WP_Post ) {
				$title = (string) get_the_title( $post->ID );
			} elseif ( $post instanceof \WP_Term ) {
				$title = (string) $post->name;
			} else {
				$title = (string) get_bloginfo( 'name' );
			}
		}
		$site_name = (string) get_bloginfo( 'name' );
		$full_title = $title . ( $title !== $site_name ? ' | ' . $site_name : '' );

		$desc = trim( (string) ( $reg['description'] ?? '' ) );
		if ( '' === $desc ) {
			if ( $post instanceof \WP_Post ) {
				$excerpt = (string) get_the_excerpt( $post->ID );
				$desc    = wp_strip_all_tags( $excerpt );
			}
			if ( '' === $desc ) {
				$desc = (string) self::getConfigValue( 'defaults.description', get_bloginfo( 'description' ) );
			}
		}
		$desc = wp_strip_all_tags( $desc );

		$canonical = trim( (string) ( $reg['canonical'] ?? '' ) );
		// If canonical is explicitly set in SEO registration, use it (includes seo.json values)
		if ( '' !== $canonical ) {
			// Already set via adn_seo_register(), use as-is
		} elseif ( $post instanceof \WP_Post ) {
			$custom = (string) get_post_meta( $post->ID, \ADN_META_CANONICAL, true );
			$canonical = '' !== $custom ? $custom : (string) get_permalink( $post->ID );
		} elseif ( $post instanceof \WP_Term ) {
			$canonical = (string) get_term_link( $post );
			if ( is_wp_error( $canonical ) ) { $canonical = ''; }
		} else {
			$canonical = (string) home_url( '/' );
		}

		$image = trim( (string) ( $reg['image'] ?? '' ) );
		if ( '' === $image && $post instanceof \WP_Post ) {
			$thumb = (string) get_the_post_thumbnail_url( $post->ID, 'large' );
			if ( '' !== $thumb ) { $image = $thumb; }
		}
		if ( '' === $image ) {
			// Try to get default image from SEO config
			$default_image = self::getConfigValue( 'defaults.image', '' );
			if ( '' !== $default_image ) {
				$image = (string) $default_image;
			} else {
				$image = (string) get_site_icon_url( 512 );
			}
		}

		$type = trim( (string) ( $reg['type'] ?? '' ) );
		if ( '' === $type ) {
			$type = is_singular( 'post' ) ? 'article' : 'website';
		}

		return compact( 'title', 'full_title', 'site_name', 'desc', 'canonical', 'image', 'type' );
	}

	public static function headOutput(): void {
		$s        = self::resolve();
		$reg      = (array) ( $GLOBALS['adn_seo'] ?? array() );
		$yoast_on    = defined( 'WPSEO_VERSION' );
		$rankmath_on = defined( 'RANK_MATH_VERSION' );

		echo '<link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">' . "\n";
		echo '<link rel="alternate" type="application/rss+xml" title="' . esc_attr( get_bloginfo( 'name' ) . ' &raquo; Feed' ) . '" href="' . esc_url( get_feed_link() ) . '">' . "\n";
		
		// Sitemap link for search engines
		$sitemap_url = self::getConfigValue( 'sitemap.url', '/sitemap_index.xml' );
		if ( $sitemap_url ) {
			echo '<link rel="sitemap" type="application/xml" href="' . esc_url( home_url( $sitemap_url ) ) . '">' . "\n";
		}
		
		// Hreflang for UK English
		echo '<link rel="alternate" hreflang="en-GB" href="' . esc_url( home_url( '/' ) ) . '">' . "\n";
		echo '<link rel="alternate" hreflang="x-default" href="' . esc_url( home_url( '/' ) ) . '">' . "\n";

		$_is_bare    = isset( $_GET['content'] ) && 'true' === (string) $_GET['content'];
		$_is_search  = isset( $_GET['search'] )  && '' !== (string) $_GET['search'];
		$_cur_paged  = isset( $_GET['paged'] )   ? (int) $_GET['paged'] : 1;
		$_noindex    = ! empty( $reg['noindex'] ) || $_is_bare || $_is_search;
		if ( $_noindex ) {
			echo '<meta name="robots" content="noindex, follow">' . "\n";
		} else {
			echo '<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">' . "\n";
		}

		$_total_pages = isset( $reg['total_pages'] ) ? (int) $reg['total_pages'] : 0;
		$_paged_base  = '' !== $s['canonical'] ? rtrim( $s['canonical'], '/' ) : '';
		if ( $_total_pages > 1 && '' !== $_paged_base ) {
			if ( $_cur_paged > 1 ) {
				$_prev_url = $_cur_paged === 2 ? $s['canonical'] : $_paged_base . '/?paged=' . ( $_cur_paged - 1 );
				echo '<link rel="prev" href="' . esc_url( $_prev_url ) . '">' . "\n";
			}
			if ( $_cur_paged < $_total_pages ) {
				echo '<link rel="next" href="' . esc_url( $_paged_base . '/?paged=' . ( $_cur_paged + 1 ) ) . '">' . "\n";
			}
		}

		$_canonical = $s['canonical'];
		if ( 1 === $_cur_paged && '' !== $_canonical ) {
			$_canonical = strtok( $_canonical, '?' );
			$_canonical = trailingslashit( $_canonical );
		}

		if ( $yoast_on || $rankmath_on ) { return; }

		// Description meta tag
		if ( '' !== $s['desc'] ) {
			echo '<meta name="description" content="' . esc_attr( $s['desc'] ) . '">' . "\n";
		}
		
		// Canonical URL
		if ( '' !== $_canonical ) {
			echo '<link rel="canonical" href="' . esc_url( $_canonical ) . '">' . "\n";
		}
		
		// OpenSearch description
		echo '<link rel="search" type="application/opensearchdescription+xml" title="' . esc_attr( ' Search' ) . '" href="' . esc_url( home_url( '/osd.xml' ) ) . '">' . "\n";

		// Page keywords, falling back to the site-wide list so every page carries
		// them, not just the two that had their own.
		$_kw = ! empty( $reg['keywords'] ) ? $reg['keywords'] : array();
		if ( empty( $_kw ) ) {
			$_kw = self::getConfigValue( 'defaults.keywords', array() );
		}
		if ( ! empty( $_kw ) ) {
			$_kw_str = is_array( $_kw )
				? implode( ', ', array_map( 'sanitize_text_field', (array) $_kw ) )
				: sanitize_text_field( (string) $_kw );
			if ( '' !== $_kw_str ) {
				echo '<meta name="keywords" content="' . esc_attr( $_kw_str ) . '">' . "\n";
			}
		}

		echo '<meta property="og:locale"      content="en_GB">' . "\n";
		echo '<meta property="og:type"        content="' . esc_attr( $s['type'] ) . '">' . "\n";
		echo '<meta property="og:site_name"   content="' . esc_attr( $s['site_name'] ) . '">' . "\n";
		echo '<meta property="og:title"       content="' . esc_attr( $s['full_title'] ) . '">' . "\n";
		if ( '' !== $s['desc'] ) {
			echo '<meta property="og:description" content="' . esc_attr( $s['desc'] ) . '">' . "\n";
		}
		if ( '' !== $_canonical ) {
			echo '<meta property="og:url"         content="' . esc_url( $_canonical ) . '">' . "\n";
		}
		if ( '' !== $s['image'] ) {
			echo '<meta property="og:image"       content="' . esc_url( $s['image'] ) . '">' . "\n";
			$_img_id = attachment_url_to_postid( $s['image'] );
			if ( $_img_id > 0 ) {
				$_img_meta = wp_get_attachment_metadata( $_img_id );
				if ( ! empty( $_img_meta['width'] ) ) {
					echo '<meta property="og:image:width"  content="' . (int) $_img_meta['width']  . '">' . "\n";
					echo '<meta property="og:image:height" content="' . (int) $_img_meta['height'] . '">' . "\n";
				}
			}
			echo '<meta property="og:image:type"   content="image/jpeg">' . "\n";
		}
		if ( 'article' === $s['type'] ) {
			$_pub = ! empty( $reg['published'] ) ? $reg['published'] : '';
			$_mod = ! empty( $reg['modified'] )  ? $reg['modified']  : '';
			if ( '' !== $_pub ) {
				echo '<meta property="og:article:published_time" content="' . esc_attr( $_pub ) . '">' . "\n";
			}
			if ( '' !== $_mod ) {
				echo '<meta property="og:article:modified_time"  content="' . esc_attr( $_mod ) . '">' . "\n";
			}
		}
		if ( ! empty( $reg['article_section'] ) ) {
			echo '<meta property="og:article:section" content="' . esc_attr( $reg['article_section'] ) . '">' . "\n";
		}
		if ( ! empty( $reg['tags'] ) && is_array( $reg['tags'] ) ) {
			foreach ( $reg['tags'] as $_atag ) {
				$_atag = trim( (string) $_atag );
				if ( '' !== $_atag ) {
					echo '<meta property="og:article:tag" content="' . esc_attr( $_atag ) . '">' . "\n";
				}
			}
		}

		echo '<meta name="twitter:card"        content="summary_large_image">' . "\n";
		echo '<meta name="twitter:title"       content="' . esc_attr( $s['full_title'] ) . '">' . "\n";
		if ( '' !== $s['desc'] ) {
			echo '<meta name="twitter:description" content="' . esc_attr( $s['desc'] ) . '">' . "\n";
		}
		if ( '' !== $s['image'] ) {
			echo '<meta name="twitter:image"      content="' . esc_url( $s['image'] ) . '">' . "\n";
		}
		if ( defined( 'SOCIAL_TWITTER' ) && '' !== SOCIAL_TWITTER ) {
			$tw_handle = '@' . ltrim( basename( rtrim( SOCIAL_TWITTER, '/' ) ), '@' );
			echo '<meta name="twitter:site"       content="' . esc_attr( $tw_handle ) . '">' . "\n";
			echo '<meta name="twitter:creator"    content="' . esc_attr( $tw_handle ) . '">' . "\n";
		}
		
		// Additional SEO meta tags
		echo '<meta name="language"             content="en-GB">' . "\n";
		echo '<meta name="revisit-after"       content="7 days">' . "\n";
		echo '<meta name="distribution"        content="global">' . "\n";
		echo '<meta name="rating"              content="general">' . "\n";
		echo '<meta name="geo.region"          content="GB">' . "\n";
		echo '<meta name="geo.placename"       content="United Kingdom">' . "\n";
		echo '<meta name="geo.position"        content="54.702354;-3.276575">' . "\n"; // UK center coordinates
		echo '<meta name="ICBM"                content="54.702354, -3.276575">' . "\n";
		if ( defined( 'COMPANY_NAME' ) && '' !== COMPANY_NAME ) {
			echo '<meta name="author"            content="' . esc_attr( COMPANY_NAME ) . '">' . "\n";
			echo '<meta name="copyright"         content="' . esc_attr( COMPANY_NAME ) . '">' . "\n";
			echo '<meta name="publisher"         content="' . esc_attr( COMPANY_NAME ) . '">' . "\n";
		}

		$site_url  = esc_url( home_url( '/' ) );
		$co_name   = defined( 'COMPANY_NAME' ) ? COMPANY_NAME : get_bloginfo( 'name' );
		$co_phone  = defined( 'COMPANY_PHONE_NO' ) ? COMPANY_PHONE_NO : '';
		$co_email  = defined( 'COMPANY_EMAIL' ) ? COMPANY_EMAIL : '';

		$social_urls = array_filter( array(
			defined( 'SOCIAL_FACEBOOK' )  ? SOCIAL_FACEBOOK  : '',
			defined( 'SOCIAL_INSTAGRAM' ) ? SOCIAL_INSTAGRAM : '',
			defined( 'SOCIAL_TWITTER' )   ? SOCIAL_TWITTER   : '',
			defined( 'SOCIAL_LINKEDIN' )  ? SOCIAL_LINKEDIN  : '',
			defined( 'SOCIAL_YOUTUBE' )   ? SOCIAL_YOUTUBE   : '',
		) );

		$org_schema = array(
			'@context' => 'https://schema.org',
			'@type'    => 'RealEstateAgent',
			'name'     => $co_name,
			'url'      => home_url( '/' ),
			'logo'     => array( '@type' => 'ImageObject', 'url' => get_template_directory_uri() . '/assets/images/logos/logo_with_text.png' ),
			'telephone' => $co_phone ? $co_phone : null,
			'email' => $co_email ? $co_email : null,
		);
		// Remove null values
		$org_schema = array_filter( $org_schema, function( $v ) { return $v !== null; } );
		
		if ( '' !== $co_phone || '' !== $co_email ) {
			$cp = array( '@type' => 'ContactPoint', 'contactType' => 'customer service', 'areaServed' => 'GB', 'availableLanguage' => 'English' );
			if ( '' !== $co_phone ) { $cp['telephone'] = $co_phone; }
			if ( '' !== $co_email ) { $cp['email']     = $co_email; }
			$org_schema['contactPoint'] = $cp;
		}
		if ( ! empty( $social_urls ) ) { $org_schema['sameAs'] = array_values( $social_urls ); }

		echo '<script type="application/ld+json">' . "\n";
		echo wp_json_encode( $org_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		echo "\n</script>\n";

		if ( is_front_page() ) {
			$website_schema = array(
				'@context'        => 'https://schema.org',
				'@type'           => 'WebSite',
				'name'            => $co_name,
				'url'             => home_url( '/' ),
				'potentialAction' => array(
					'@type'       => 'SearchAction',
					'target'      => array( '@type' => 'EntryPoint', 'urlTemplate' => home_url( '/?s={search_term_string}' ) ),
					'query-input' => 'required name=search_term_string',
				),
			);
			echo '<script type="application/ld+json">' . "\n";
			echo wp_json_encode( $website_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			echo "\n</script>\n";
		}

		$site_links = self::siteLinksSchema();
		if ( ! empty( $site_links ) ) {
			echo '<script type="application/ld+json">' . "\n";
			echo wp_json_encode( $site_links, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			echo "\n</script>\n";
		}

		if ( is_singular( 'post' ) && $s['type'] === 'article' ) {
			$post_obj    = get_queried_object();
			$author_id   = (int) $post_obj->post_author;
			$author_name = (string) get_the_author_meta( 'display_name', $author_id );
			$pub_date    = (string) get_the_date( 'c', $post_obj->ID );
			$mod_date    = (string) get_the_modified_date( 'c', $post_obj->ID );
			$article_schema = array(
				'@context'      => 'https://schema.org',
				'@type'         => 'Article',
				'headline'      => $s['title'],
				'description'   => $s['desc'],
				'url'           => $s['canonical'],
				'datePublished' => $pub_date,
				'dateModified'  => $mod_date,
				'publisher'     => array( '@type' => 'Organization', 'name' => $co_name, 'logo' => array( '@type' => 'ImageObject', 'url' => get_template_directory_uri() . '/assets/images/logos/logo_with_text.png' ) ),
			);
			if ( '' !== $author_name ) { $article_schema['author'] = array( '@type' => 'Person', 'name' => $author_name ); }
			if ( '' !== $s['image'] ) { $article_schema['image'] = $s['image']; }
			echo '<script type="application/ld+json">' . "\n";
			echo wp_json_encode( $article_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			echo "\n</script>\n";
		}

		$bc_items = (array) ( $reg['breadcrumb'] ?? array() );
		if ( ! empty( $bc_items ) ) {
			$list_items = array();
			foreach ( array_values( $bc_items ) as $i => $item ) {
				$entry = array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => (string) ( $item['label'] ?? $item['name'] ?? '' ) );
				if ( ! empty( $item['url'] ) ) { $entry['item'] = (string) $item['url']; }
				$list_items[] = $entry;
			}
			echo '<script type="application/ld+json">' . "\n";
			echo wp_json_encode( array( '@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $list_items ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			echo "\n</script>\n";
		}

		$faq_items = ! empty( $reg['schema_faqs'] ) && is_array( $reg['schema_faqs'] ) ? $reg['schema_faqs'] : array();
		if ( ! empty( $faq_items ) ) {
			$faq_entities = array();
			foreach ( $faq_items as $faq ) {
				$q = trim( (string) ( $faq['question'] ?? $faq['q'] ?? '' ) );
				$a = trim( wp_strip_all_tags( (string) ( $faq['answer'] ?? $faq['a'] ?? '' ) ) );
				if ( '' === $q || '' === $a ) { continue; }
				$faq_entities[] = array( '@type' => 'Question', 'name' => $q, 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $a ) );
			}
			if ( ! empty( $faq_entities ) ) {
				echo '<script type="application/ld+json">' . "\n";
				echo wp_json_encode( array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $faq_entities ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
				echo "\n</script>\n";
			}
		}

		$person = ! empty( $reg['schema_person'] ) && is_array( $reg['schema_person'] ) ? $reg['schema_person'] : array();
		if ( ! empty( $person ) ) {
			$person_schema = array( '@context' => 'https://schema.org', '@type' => 'Person', 'name' => (string) ( $person['name'] ?? '' ) );
			if ( ! empty( $person['job_title'] ) ) { $person_schema['jobTitle']    = (string) $person['job_title']; }
			if ( ! empty( $person['bio'] ) )       { $person_schema['description'] = wp_strip_all_tags( (string) $person['bio'] ); }
			if ( ! empty( $person['image'] ) )     { $person_schema['image']       = (string) $person['image']; }
			if ( ! empty( $person['url'] ) )       { $person_schema['url']         = (string) $person['url']; }
			if ( ! empty( $person['employer'] ) )  { $person_schema['worksFor']    = array( '@type' => 'Organization', 'name' => (string) $person['employer'] ); }
			echo '<script type="application/ld+json">' . "\n";
			echo wp_json_encode( $person_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			echo "\n</script>\n";
		}

		$app = ! empty( $reg['schema_app'] ) && is_array( $reg['schema_app'] ) ? $reg['schema_app'] : array();
		if ( ! empty( $app ) ) {
			$app_schema = array( '@context' => 'https://schema.org', '@type' => 'SoftwareApplication', 'applicationCategory' => 'FinanceApplication', 'name' => (string) ( $app['name'] ?? '' ), 'url' => (string) ( $app['url'] ?? '' ), 'operatingSystem' => 'Web', 'offers' => array( '@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'GBP' ) );
			if ( ! empty( $app['description'] ) ) { $app_schema['description'] = wp_strip_all_tags( (string) $app['description'] ); }
			echo '<script type="application/ld+json">' . "\n";
			echo wp_json_encode( $app_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			echo "\n</script>\n";
		}

		$_col = ! empty( $reg['schema_collection'] ) && is_array( $reg['schema_collection'] ) ? $reg['schema_collection'] : array();
		if ( ! empty( $_col ) ) {
			$_col_schema = array( '@context' => 'https://schema.org', '@type' => 'CollectionPage', 'name' => (string) ( $_col['name'] ?? $s['title'] ), 'url' => (string) ( $_col['url'] ?? $_canonical ), 'description' => (string) ( $_col['description'] ?? $s['desc'] ), 'publisher' => array( '@type' => 'Organization', 'name' => $co_name, 'logo' => array( '@type' => 'ImageObject', 'url' => get_template_directory_uri() . '/assets/images/logos/logo_with_text.png' ) ) );
			if ( ! empty( $_col['items'] ) && is_array( $_col['items'] ) ) {
				$_col_list = array();
				foreach ( array_values( $_col['items'] ) as $_ci => $_citem ) {
					$_cname = trim( (string) ( $_citem['title'] ?? '' ) );
					$_curl  = trim( (string) ( $_citem['url'] ?? '' ) );
					if ( '' === $_cname && '' === $_curl ) { continue; }
					$_col_list[] = array( '@type' => 'ListItem', 'position' => $_ci + 1, 'name' => $_cname, 'url' => $_curl );
				}
				if ( ! empty( $_col_list ) ) {
					$_col_schema['mainEntity'] = array( '@type' => 'ItemList', 'itemListElement' => $_col_list );
				}
			}
			echo '<script type="application/ld+json">' . "\n";
			echo wp_json_encode( $_col_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			echo "\n</script>\n";
		}

		$news = ! empty( $reg['schema_news'] ) && is_array( $reg['schema_news'] ) ? $reg['schema_news'] : array();
		if ( ! empty( $news ) ) {
			$news_schema = array( '@context' => 'https://schema.org', '@type' => 'NewsArticle', 'headline' => (string) ( $news['title'] ?? $s['title'] ), 'description' => (string) ( $news['excerpt'] ?? $s['desc'] ), 'url' => (string) ( $news['url'] ?? $s['canonical'] ), 'datePublished' => (string) ( $news['date'] ?? '' ), 'publisher' => array( '@type' => 'Organization', 'name' => ( defined( 'COMPANY_NAME' ) ? COMPANY_NAME : get_bloginfo( 'name' ) ), 'logo' => array( '@type' => 'ImageObject', 'url' => get_template_directory_uri() . '/assets/images/logos/logo_with_text.png' ) ) );
			if ( ! empty( $news['image'] ) ) { $news_schema['image'] = (string) $news['image']; }
			if ( ! empty( $news['label'] ) ) { $news_schema['articleSection'] = (string) $news['label']; }
			echo '<script type="application/ld+json">' . "\n";
			echo wp_json_encode( $news_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			echo "\n</script>\n";
		}
	}
}
