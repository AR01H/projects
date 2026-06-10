<?php
/**
 * Structured data (schema.org JSON-LD) for SEO rich results.
 *
 * Purely additive - emits machine-readable data in <head>, no visual change:
 *   • BlogPosting    on single posts  (author, dates, image, publisher)
 *   • BreadcrumbList on single posts  (mirrors the visual breadcrumb trail)
 *   • FAQPage        on the FAQ page  (eligible for Google FAQ rich results)
 *
 * The companion plugin already emits Organization + WebSite, so those are not
 * duplicated here. Hooked late on wp_head so titles/excerpts are resolved.
 */
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'ah_sd_emit' ) ) {
	/** Print one JSON-LD <script> block. */
	function ah_sd_emit( array $data ): void {
		echo "\n" . '<script type="application/ld+json">'
			. wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
			. '</script>' . "\n";
	}
}

if ( ! function_exists( 'ah_sd_publisher' ) ) {
	/** Shared Organization/publisher node (with logo when available). */
	function ah_sd_publisher(): array {
		$pub = array(
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
		);
		$logo = get_template_directory() . '/assets/images/logo.png';
		if ( file_exists( $logo ) ) {
			$pub['logo'] = array(
				'@type' => 'ImageObject',
				'url'   => get_template_directory_uri() . '/assets/images/logo.png',
			);
		}
		return $pub;
	}
}

/* ── BlogPosting on single posts ──────────────────────────────────────────── */
add_action( 'wp_head', function () {
	if ( ! is_singular( 'post' ) ) {
		return;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return;
	}

	$data = array(
		'@context'         => 'https://schema.org',
		'@type'            => 'BlogPosting',
		'mainEntityOfPage' => array( '@type' => 'WebPage', '@id' => get_permalink( $post ) ),
		'headline'         => wp_strip_all_tags( get_the_title( $post ) ),
		'datePublished'    => get_the_date( 'c', $post ),
		'dateModified'     => get_the_modified_date( 'c', $post ),
		'author'           => array(
			'@type' => 'Person',
			'name'  => get_the_author_meta( 'display_name', (int) $post->post_author ),
			'url'   => get_author_posts_url( (int) $post->post_author ),
		),
		'publisher'        => ah_sd_publisher(),
	);

	$desc = get_the_excerpt( $post );
	if ( $desc ) {
		$data['description'] = wp_strip_all_tags( $desc );
	}
	$img = get_the_post_thumbnail_url( $post, 'large' );
	if ( $img ) {
		$data['image'] = array( $img );
	}
	$reviewer = get_post_meta( $post->ID, '_ah_reviewed_by', true );
	if ( $reviewer ) {
		$data['reviewedBy'] = array( '@type' => 'Person', 'name' => wp_strip_all_tags( $reviewer ) );
	}

	ah_sd_emit( $data );
}, 20 );

/* ── BreadcrumbList on single posts (mirrors single.php's visual crumbs) ───── */
add_action( 'wp_head', function () {
	if ( ! is_singular( 'post' ) ) {
		return;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return;
	}

	$cats  = get_the_category( $post->ID );
	$cat   = $cats ? $cats[0] : null;
	$items = array();
	$pos   = 1;
	$push  = function ( $name, $url ) use ( &$items, &$pos ) {
		$entry = array( '@type' => 'ListItem', 'position' => $pos, 'name' => wp_strip_all_tags( $name ) );
		if ( $url ) {
			$entry['item'] = $url;
		}
		$items[] = $entry;
		$pos++;
	};

	$push( 'Home', home_url( '/' ) );

	$pt = ( $cat && function_exists( 'ah_get_parent_term_for_cat' ) ) ? ah_get_parent_term_for_cat( $cat->slug ) : null;
	if ( $pt ) {
		$push( $pt->name, home_url( '/' . $pt->slug . '/' ) );
		$push( $cat->name, home_url( '/' . $pt->slug . '/' . $cat->slug . '/' ) );
	} elseif ( $cat ) {
		$push( $cat->name, get_category_link( $cat ) );
	}
	$push( get_the_title( $post ), '' );

	ah_sd_emit( array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
	) );
}, 20 );

/* ── FAQPage on the FAQ page ──────────────────────────────────────────────── */
add_action( 'wp_head', function () {
	$is_faq = is_page_template( 'page-faq.php' ) || is_page( array( 'faq', 'faqs' ) );
	if ( ! $is_faq || ! function_exists( 'ah_get_faqs' ) ) {
		return;
	}

	$faqs     = ah_get_faqs( 30 );
	$entities = array();
	foreach ( (array) $faqs as $f ) {
		$q = wp_strip_all_tags( $f->question ?? '' );
		$a = wp_strip_all_tags( $f->answer ?? '' );
		if ( $q === '' || $a === '' ) {
			continue;
		}
		$entities[] = array(
			'@type'          => 'Question',
			'name'           => $q,
			'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $a ),
		);
	}
	if ( ! $entities ) {
		return;
	}

	ah_sd_emit( array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $entities,
	) );
}, 20 );
