<?php
/**
 * includes/seo.php
 *
 * Theme SEO layer.
 * Outputs: meta description, canonical, Open Graph, Twitter Card, JSON-LD schema.
 *
 * Usage from page templates (BEFORE adn_page_open):
 *   adn_seo_register( array(
 *       'description' => $ctx['meta_description'],
 *       'title'       => $ctx['hero']['title'] ?? '',
 *       'image'       => $ctx['hero']['image'] ?? '',
 *   ) );
 */

defined( 'ABSPATH' ) || exit;

/* ── Global store for per-page SEO data ─────────────────────────────────── */
$GLOBALS['adn_seo'] = array();

/**
 * Register per-page SEO meta. Call this BEFORE adn_page_open().
 *
 * @param array{
 *   title?:       string,
 *   description?: string,
 *   image?:       string,
 *   canonical?:   string,
 *   type?:        string,
 * } $meta
 */
function adn_seo_register( array $meta ): void {
	$GLOBALS['adn_seo'] = array_merge( $GLOBALS['adn_seo'], $meta );
}

/**
 * Override the <title> tag when a custom title is registered.
 * Format: "Page Title | Site Name"
 */
function adn_seo_document_title( string $title ): string {
	$reg        = (array) $GLOBALS['adn_seo'];
	$custom     = trim( (string) ( $reg['title'] ?? '' ) );
	if ( '' === $custom ) {
		return $title;
	}
	$site_name = (string) get_bloginfo( 'name' );
	return $custom . ' | ' . $site_name;
}
add_filter( 'pre_get_document_title', 'adn_seo_document_title', 10 );

/**
 * Build the full SEO data set for the current request,
 * merging page-registered data with smart WP-native fallbacks.
 */
function adn_seo_resolve(): array {
	$reg  = (array) $GLOBALS['adn_seo'];
	$post = get_queried_object();

	/* ── Title ── */
	$title = trim( (string) ( $reg['title'] ?? '' ) );
	if ( '' === $title ) {
		if ( $post instanceof WP_Post ) {
			$title = (string) get_the_title( $post->ID );
		} elseif ( $post instanceof WP_Term ) {
			$title = (string) $post->name;
		} else {
			$title = (string) get_bloginfo( 'name' );
		}
	}
	$site_name = (string) get_bloginfo( 'name' );
	$full_title = $title . ( $title !== $site_name ? ' | ' . $site_name : '' );

	/* ── Description ── */
	$desc = trim( (string) ( $reg['description'] ?? '' ) );
	if ( '' === $desc ) {
		if ( $post instanceof WP_Post ) {
			$excerpt = (string) get_the_excerpt( $post->ID );
			$desc    = wp_strip_all_tags( $excerpt );
		}
		if ( '' === $desc ) {
			$desc = (string) get_bloginfo( 'description' );
		}
	}
	$desc = wp_strip_all_tags( $desc );

	/* ── Canonical ── */
	$canonical = trim( (string) ( $reg['canonical'] ?? '' ) );
	if ( '' === $canonical ) {
		if ( $post instanceof WP_Post ) {
			$custom = (string) get_post_meta( $post->ID, ADN_META_CANONICAL, true );
			$canonical = '' !== $custom ? $custom : (string) get_permalink( $post->ID );
		} elseif ( $post instanceof WP_Term ) {
			$canonical = (string) get_term_link( $post );
			if ( is_wp_error( $canonical ) ) {
				$canonical = '';
			}
		} else {
			$canonical = (string) home_url( '/' );
		}
	}

	/* ── Image ── */
	$image = trim( (string) ( $reg['image'] ?? '' ) );
	if ( '' === $image && $post instanceof WP_Post ) {
		$thumb = (string) get_the_post_thumbnail_url( $post->ID, 'large' );
		if ( '' !== $thumb ) {
			$image = $thumb;
		}
	}
	if ( '' === $image ) {
		$image = (string) get_site_icon_url( 512 );
	}

	/* ── OG type ── */
	$type = trim( (string) ( $reg['type'] ?? '' ) );
	if ( '' === $type ) {
		$type = is_singular( 'post' ) ? 'article' : 'website';
	}

	return compact( 'title', 'full_title', 'site_name', 'desc', 'canonical', 'image', 'type' );
}

/**
 * Output all SEO tags into <head>. Hooked to wp_head at priority 1.
 * Skips meta/OG/Twitter output when Yoast SEO is active to avoid duplicates.
 */
function adn_seo_head_output(): void {
	$s        = adn_seo_resolve();
	$reg      = (array) $GLOBALS['adn_seo'];
	$yoast_on = defined( 'WPSEO_VERSION' );

	/* ── Preconnect for external font/icon CDNs (always output) ── */
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	echo '<link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">' . "\n";

	/* ── RSS feed discovery (always output) ── */
	echo '<link rel="alternate" type="application/rss+xml" title="' . esc_attr( get_bloginfo( 'name' ) . ' &raquo; Feed' ) . '" href="' . esc_url( get_feed_link() ) . '">' . "\n";

	/* ── Robots meta — consolidated ── */
	$_is_bare    = isset( $_GET['content'] ) && 'true' === (string) $_GET['content'];
	$_is_search  = isset( $_GET['search'] )  && '' !== (string) $_GET['search'];
	$_cur_paged  = isset( $_GET['paged'] )   ? (int) $_GET['paged'] : 1;
	$_noindex    = ! empty( $reg['noindex'] ) || $_is_bare || $_is_search;
	if ( $_noindex ) {
		echo '<meta name="robots" content="noindex, follow">' . "\n";
	} else {
		echo '<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">' . "\n";
	}

	/* ── Pagination prev / next link hints ── */
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

	/* ── Canonical — paged=1 always points to base URL ── */
	$_canonical = $s['canonical'];
	if ( 1 === $_cur_paged && '' !== $_canonical ) {
		$_canonical = strtok( $_canonical, '?' );
		$_canonical = trailingslashit( $_canonical );
	}

	/* Skip everything below if Yoast is handling it */
	if ( $yoast_on ) {
		return;
	}

	/* ── Meta description ── */
	if ( '' !== $s['desc'] ) {
		echo '<meta name="description" content="' . esc_attr( $s['desc'] ) . '">' . "\n";
	}

	/* ── Canonical (uses paged=1-corrected URL) ── */
	if ( '' !== $_canonical ) {
		echo '<link rel="canonical" href="' . esc_url( $_canonical ) . '">' . "\n";
	}

	/* ── Keywords meta (from registered keywords array) ── */
	$_kw = ! empty( $reg['keywords'] ) ? $reg['keywords'] : array();
	if ( ! empty( $_kw ) ) {
		$_kw_str = is_array( $_kw )
			? implode( ', ', array_map( 'sanitize_text_field', (array) $_kw ) )
			: sanitize_text_field( (string) $_kw );
		if ( '' !== $_kw_str ) {
			echo '<meta name="keywords" content="' . esc_attr( $_kw_str ) . '">' . "\n";
		}
	}

	/* ── Open Graph ── */
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
		/* Resolve image dimensions for social platforms */
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
	/* Article-specific OG tags */
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
	/* article:section — output for all types when registered (used by category/topic pages too) */
	if ( ! empty( $reg['article_section'] ) ) {
		echo '<meta property="og:article:section" content="' . esc_attr( $reg['article_section'] ) . '">' . "\n";
	}
	/* og:article:tag — content tags from registered keywords/tags array */
	if ( ! empty( $reg['tags'] ) && is_array( $reg['tags'] ) ) {
		foreach ( $reg['tags'] as $_atag ) {
			$_atag = trim( (string) $_atag );
			if ( '' !== $_atag ) {
				echo '<meta property="og:article:tag" content="' . esc_attr( $_atag ) . '">' . "\n";
			}
		}
	}

	/* ── Twitter Card ── */
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
	}

	/* ── JSON-LD: Organization + WebSite ── */
	$site_url  = esc_url( home_url( '/' ) );
	$logo_url  = esc_url( get_template_directory_uri() . '/assets/images/logos/logo_with_text.png' );
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
		'@context'    => 'https://schema.org',
		'@type'       => 'RealEstateAgent',
		'name'        => $co_name,
		'url'         => home_url( '/' ),
		'logo'        => array(
			'@type' => 'ImageObject',
			'url'   => get_template_directory_uri() . '/assets/images/logos/logo_with_text.png',
		),
	);
	if ( '' !== $co_phone )  { $org_schema['telephone'] = $co_phone; }
	if ( '' !== $co_email )  { $org_schema['email']     = $co_email; }
	if ( '' !== $co_phone || '' !== $co_email ) {
		$cp = array( '@type' => 'ContactPoint', 'contactType' => 'customer service', 'areaServed' => 'GB', 'availableLanguage' => 'English' );
		if ( '' !== $co_phone ) { $cp['telephone'] = $co_phone; }
		if ( '' !== $co_email ) { $cp['email']     = $co_email; }
		$org_schema['contactPoint'] = $cp;
	}
	if ( ! empty( $social_urls ) ) {
		$org_schema['sameAs'] = array_values( $social_urls );
	}

	echo '<script type="application/ld+json">' . "\n";
	echo wp_json_encode( $org_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	echo "\n</script>\n";

	/* ── JSON-LD: WebSite (with SearchAction - front page only) ── */
	if ( is_front_page() ) {
		$website_schema = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'WebSite',
			'name'            => $co_name,
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
		echo '<script type="application/ld+json">' . "\n";
		echo wp_json_encode( $website_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		echo "\n</script>\n";
	}

	/* ── JSON-LD: Article (single posts) ── */
	if ( is_singular( 'post' ) && $s['type'] === 'article' ) {
		$post_obj   = get_queried_object();
		$author_id  = (int) $post_obj->post_author;
		$author_name = (string) get_the_author_meta( 'display_name', $author_id );
		$pub_date   = (string) get_the_date( 'c', $post_obj->ID );
		$mod_date   = (string) get_the_modified_date( 'c', $post_obj->ID );

		$article_schema = array(
			'@context'         => 'https://schema.org',
			'@type'            => 'Article',
			'headline'         => $s['title'],
			'description'      => $s['desc'],
			'url'              => $s['canonical'],
			'datePublished'    => $pub_date,
			'dateModified'     => $mod_date,
			'publisher'        => array(
				'@type' => 'Organization',
				'name'  => $co_name,
				'logo'  => array(
					'@type' => 'ImageObject',
					'url'   => get_template_directory_uri() . '/assets/images/logos/logo_with_text.png',
				),
			),
		);
		if ( '' !== $author_name ) {
			$article_schema['author'] = array( '@type' => 'Person', 'name' => $author_name );
		}
		if ( '' !== $s['image'] ) {
			$article_schema['image'] = $s['image'];
		}

		echo '<script type="application/ld+json">' . "\n";
		echo wp_json_encode( $article_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		echo "\n</script>\n";
	}

	/* ── JSON-LD: BreadcrumbList ── */
	$bc_items = (array) ( $reg['breadcrumb'] ?? array() );
	if ( ! empty( $bc_items ) ) {
		$list_items = array();
		foreach ( array_values( $bc_items ) as $i => $item ) {
			$entry = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'name'     => (string) ( $item['label'] ?? $item['name'] ?? '' ),
			);
			if ( ! empty( $item['url'] ) ) {
				$entry['item'] = (string) $item['url'];
			}
			$list_items[] = $entry;
		}
		$bc_schema = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $list_items,
		);
		echo '<script type="application/ld+json">' . "\n";
		echo wp_json_encode( $bc_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		echo "\n</script>\n";
	}

	/* ── JSON-LD: FAQPage (when schema_faqs registered) ── */
	$faq_items = ! empty( $reg['schema_faqs'] ) && is_array( $reg['schema_faqs'] ) ? $reg['schema_faqs'] : array();
	if ( ! empty( $faq_items ) ) {
		$faq_entities = array();
		foreach ( $faq_items as $faq ) {
			$q = trim( (string) ( $faq['question'] ?? $faq['q'] ?? '' ) );
			$a = trim( wp_strip_all_tags( (string) ( $faq['answer'] ?? $faq['a'] ?? '' ) ) );
			if ( '' === $q || '' === $a ) { continue; }
			$faq_entities[] = array(
				'@type'          => 'Question',
				'name'           => $q,
				'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $a ),
			);
		}
		if ( ! empty( $faq_entities ) ) {
			$faq_schema = array(
				'@context'   => 'https://schema.org',
				'@type'      => 'FAQPage',
				'mainEntity' => $faq_entities,
			);
			echo '<script type="application/ld+json">' . "\n";
			echo wp_json_encode( $faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			echo "\n</script>\n";
		}
	}

	/* ── JSON-LD: Person (expert profile pages) ── */
	$person = ! empty( $reg['schema_person'] ) && is_array( $reg['schema_person'] ) ? $reg['schema_person'] : array();
	if ( ! empty( $person ) ) {
		$person_schema = array(
			'@context' => 'https://schema.org',
			'@type'    => 'Person',
			'name'     => (string) ( $person['name']  ?? '' ),
		);
		if ( ! empty( $person['job_title'] ) ) { $person_schema['jobTitle']    = (string) $person['job_title']; }
		if ( ! empty( $person['bio'] ) )       { $person_schema['description'] = wp_strip_all_tags( (string) $person['bio'] ); }
		if ( ! empty( $person['image'] ) )     { $person_schema['image']       = (string) $person['image']; }
		if ( ! empty( $person['url'] ) )       { $person_schema['url']         = (string) $person['url']; }
		if ( ! empty( $person['employer'] ) ) {
			$person_schema['worksFor'] = array( '@type' => 'Organization', 'name' => (string) $person['employer'] );
		}
		echo '<script type="application/ld+json">' . "\n";
		echo wp_json_encode( $person_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		echo "\n</script>\n";
	}

	/* ── JSON-LD: SoftwareApplication (calculator pages) ── */
	$app = ! empty( $reg['schema_app'] ) && is_array( $reg['schema_app'] ) ? $reg['schema_app'] : array();
	if ( ! empty( $app ) ) {
		$app_schema = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'SoftwareApplication',
			'applicationCategory' => 'FinanceApplication',
			'name'            => (string) ( $app['name'] ?? '' ),
			'url'             => (string) ( $app['url']  ?? '' ),
			'operatingSystem' => 'Web',
			'offers'          => array( '@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'GBP' ),
		);
		if ( ! empty( $app['description'] ) ) { $app_schema['description'] = wp_strip_all_tags( (string) $app['description'] ); }
		echo '<script type="application/ld+json">' . "\n";
		echo wp_json_encode( $app_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		echo "\n</script>\n";
	}

	/* ── JSON-LD: CollectionPage + ItemList (topic/category listing pages) ── */
	$_col = ! empty( $reg['schema_collection'] ) && is_array( $reg['schema_collection'] ) ? $reg['schema_collection'] : array();
	if ( ! empty( $_col ) ) {
		$_col_schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'CollectionPage',
			'name'        => (string) ( $_col['name']        ?? $s['title'] ),
			'url'         => (string) ( $_col['url']         ?? $_canonical ),
			'description' => (string) ( $_col['description'] ?? $s['desc'] ),
			'publisher'   => array(
				'@type' => 'Organization',
				'name'  => $co_name,
				'logo'  => array(
					'@type' => 'ImageObject',
					'url'   => get_template_directory_uri() . '/assets/images/logos/logo_with_text.png',
				),
			),
		);
		if ( ! empty( $_col['items'] ) && is_array( $_col['items'] ) ) {
			$_col_list = array();
			foreach ( array_values( $_col['items'] ) as $_ci => $_citem ) {
				$_cname = trim( (string) ( $_citem['title'] ?? '' ) );
				$_curl  = trim( (string) ( $_citem['url']   ?? '' ) );
				if ( '' === $_cname && '' === $_curl ) { continue; }
				$_col_list[] = array(
					'@type'    => 'ListItem',
					'position' => $_ci + 1,
					'name'     => $_cname,
					'url'      => $_curl,
				);
			}
			if ( ! empty( $_col_list ) ) {
				$_col_schema['mainEntity'] = array(
					'@type'           => 'ItemList',
					'itemListElement' => $_col_list,
				);
			}
		}
		echo '<script type="application/ld+json">' . "\n";
		echo wp_json_encode( $_col_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		echo "\n</script>\n";
	}

	/* ── JSON-LD: NewsArticle (CMS news items) ── */
	$news = ! empty( $reg['schema_news'] ) && is_array( $reg['schema_news'] ) ? $reg['schema_news'] : array();
	if ( ! empty( $news ) ) {
		$co_name_n = defined( 'COMPANY_NAME' ) ? COMPANY_NAME : get_bloginfo( 'name' );
		$news_schema = array(
			'@context'      => 'https://schema.org',
			'@type'         => 'NewsArticle',
			'headline'      => (string) ( $news['title']    ?? $s['title'] ),
			'description'   => (string) ( $news['excerpt']  ?? $s['desc'] ),
			'url'           => (string) ( $news['url']      ?? $s['canonical'] ),
			'datePublished' => (string) ( $news['date']     ?? '' ),
			'publisher'     => array(
				'@type' => 'Organization',
				'name'  => $co_name_n,
				'logo'  => array(
					'@type' => 'ImageObject',
					'url'   => get_template_directory_uri() . '/assets/images/logos/logo_with_text.png',
				),
			),
		);
		if ( ! empty( $news['image'] ) ) { $news_schema['image'] = (string) $news['image']; }
		if ( ! empty( $news['label'] ) ) { $news_schema['articleSection'] = (string) $news['label']; }
		echo '<script type="application/ld+json">' . "\n";
		echo wp_json_encode( $news_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		echo "\n</script>\n";
	}
}
add_action( 'wp_head', 'adn_seo_head_output', 1 );

/**
 * Redirect /favicon.png to the theme logo.
 */
add_action( 'template_redirect', function() {
	if ( '/favicon.png' === wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ) {
		wp_redirect( get_template_directory_uri() . '/assets/images/logos/logo_with_text.png', 301 );
		exit;
	}
} );
