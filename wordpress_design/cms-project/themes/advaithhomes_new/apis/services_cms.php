<?php
/**
 * apis/services_cms.php — Read-only data services backed by the CMS plugin DB.
 *
 * The companion plugin (plugin1, class prefix AH_) stores the real content in
 * its own tables: wp_ah_taxonomy_types / wp_ah_taxonomies (a parent→child term
 * tree) and wp_ah_posts (articles, guides, news), linked by
 * wp_ah_content_taxonomies. This file reads those tables directly so the theme
 * does not depend on plugin PHP classes being loaded on the front end.
 *
 * The content hierarchy the home page renders:
 *   Guide (taxonomy type)
 *     └─ Buying / Selling / Moving        (parent terms  → "journey" cards)
 *          └─ Topics                       (child terms  → article categories)
 *               └─ Articles (ah_posts)     (post_type article/guide)
 *   Latest news (ah_posts post_type=news)  is independent of the tree.
 *
 * Every function degrades to an empty array when the plugin/tables/data are
 * absent, so callers can fall back to their JSON defaults and never crash.
 */

defined( 'ABSPATH' ) || exit;

/** Fully-qualified plugin table name, e.g. adn_cms_table('posts') → wp_ah_posts. */
function adn_cms_table( $suffix ) {
	global $wpdb;
	return $wpdb->prefix . 'ah_' . preg_replace( '/[^a-z0-9_]/', '', (string) $suffix );
}

/** True only when the plugin's taxonomy + posts tables exist. Cached per request. */
function adn_cms_available() {
	static $ready = null;
	if ( null !== $ready ) {
		return $ready;
	}
	global $wpdb;
	$tax   = adn_cms_table( 'taxonomies' );
	$posts = adn_cms_table( 'posts' );
	$found = $wpdb->get_col( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tax ) );
	$found = array_merge( $found, $wpdb->get_col( $wpdb->prepare( 'SHOW TABLES LIKE %s', $posts ) ) );
	$ready = ( in_array( $tax, $found, true ) && in_array( $posts, $found, true ) );
	return $ready;
}

/**
 * The taxonomy-type id that represents "Guide" (Buying/Selling/Moving live
 * under it). Matched loosely by slug/name; 0 means "no specific type — use all
 * top-level terms".
 */
function adn_cms_guide_type_id() {
	if ( ! adn_cms_available() ) {
		return 0;
	}
	global $wpdb;
	$types = adn_cms_table( 'taxonomy_types' );
	return (int) $wpdb->get_var(
		"SELECT id FROM `{$types}`
		 WHERE slug IN ('guide','guides') OR name LIKE '%Guide%'
		 ORDER BY id ASC LIMIT 1"
	);
}

/**
 * Top-level Guide terms (Buying / Selling / Moving …).
 *
 * @return object[] taxonomy rows (id, name, slug, description, icon_emoji…).
 */
function adn_cms_guide_parents( $limit = 12 ) {
	if ( ! adn_cms_available() ) {
		return array();
	}
	global $wpdb;
	$tax     = adn_cms_table( 'taxonomies' );
	$type_id = adn_cms_guide_type_id();
	$limit   = max( 1, (int) $limit );

	if ( $type_id ) {
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM `{$tax}`
			 WHERE ( parent_id IS NULL OR parent_id = 0 ) AND status = 'active' AND type_id = %d
			 ORDER BY sort_order ASC, name ASC LIMIT %d",
			$type_id,
			$limit
		) ) ?: array();
	}

	return $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM `{$tax}`
		 WHERE ( parent_id IS NULL OR parent_id = 0 ) AND status = 'active'
		 ORDER BY sort_order ASC, name ASC LIMIT %d",
		$limit
	) ) ?: array();
}

/**
 * Child topic terms under one Guide parent.
 *
 * @return object[] taxonomy rows.
 */
function adn_cms_topics( $parent_id, $limit = 50 ) {
	$parent_id = (int) $parent_id;
	if ( ! adn_cms_available() || ! $parent_id ) {
		return array();
	}
	global $wpdb;
	$tax = adn_cms_table( 'taxonomies' );
	return $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM `{$tax}`
		 WHERE parent_id = %d AND status = 'active'
		 ORDER BY sort_order ASC, name ASC LIMIT %d",
		$parent_id,
		max( 1, (int) $limit )
	) ) ?: array();
}

/**
 * Articles/guides from ah_posts. Optionally restricted to a set of term ids
 * (e.g. all topics under a Guide). Each row carries a `category_name` (its
 * primary linked term, preferring a child topic over a parent).
 *
 * @param int   $limit
 * @param int[] $taxonomy_ids  Restrict to posts linked to any of these terms.
 * @return object[] ah_posts rows + category_name.
 */
function adn_cms_articles( $limit = 6, $taxonomy_ids = array() ) {
	if ( ! adn_cms_available() ) {
		return array();
	}
	global $wpdb;
	$posts = adn_cms_table( 'posts' );
	$ct    = adn_cms_table( 'content_taxonomies' );
	$tax   = adn_cms_table( 'taxonomies' );
	$limit = max( 1, (int) $limit );

	// Primary category label for the card (prefer a child topic term).
	$category = "(
		SELECT t.name FROM `{$tax}` t
		INNER JOIN `{$ct}` c2 ON c2.taxonomy_id = t.id
		WHERE c2.object_type = 'ah_post' AND c2.object_id = p.id
		ORDER BY ( t.parent_id IS NOT NULL ) DESC, t.id ASC LIMIT 1
	) AS category_name";

	if ( ! empty( $taxonomy_ids ) ) {
		$ids = implode( ',', array_map( 'absint', (array) $taxonomy_ids ) );
		if ( '' === $ids ) {
			$ids = '0';
		}
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT DISTINCT p.*, {$category}
			 FROM `{$posts}` p
			 INNER JOIN `{$ct}` ct ON ct.object_type = 'ah_post' AND ct.object_id = p.id
			 WHERE p.status = 'active' AND p.post_type IN ('article','guide','blog')
			   AND ct.taxonomy_id IN ({$ids})
			 ORDER BY p.is_featured DESC, p.published_at DESC, p.created_at DESC
			 LIMIT %d",
			$limit
		) ) ?: array();
	}

	return $wpdb->get_results( $wpdb->prepare(
		"SELECT p.*, {$category}
		 FROM `{$posts}` p
		 WHERE p.status = 'active' AND p.post_type IN ('article','guide','blog')
		 ORDER BY p.is_featured DESC, p.published_at DESC, p.created_at DESC
		 LIMIT %d",
		$limit
	) ) ?: array();
}

/**
 * Latest news posts — independent of the Guide tree.
 *
 * @return object[] ah_posts rows (post_type = news).
 */
function adn_cms_latest_news( $limit = 4 ) {
	if ( ! adn_cms_available() ) {
		return array();
	}
	global $wpdb;
	$posts = adn_cms_table( 'posts' );
	return $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM `{$posts}`
		 WHERE status = 'active' AND post_type = 'news'
		 ORDER BY published_at DESC, created_at DESC LIMIT %d",
		max( 1, (int) $limit )
	) ) ?: array();
}

/* ── Presentation helpers (shared by any page mapping CMS rows to cards) ───── */

/** Cycle a soft gradient for card thumbnails, matching the existing palette. */
function adn_cms_gradient( $index ) {
	$palette = array(
		'linear-gradient(135deg,#dce8d4,#a8c5a0)',
		'linear-gradient(135deg,#f0e0d6,#d4956a)',
		'linear-gradient(135deg,#e0e8f4,#8eb4d8)',
		'linear-gradient(135deg,#ede8f8,#a890d8)',
		'linear-gradient(135deg,#e4e8c4,#909848)',
		'linear-gradient(135deg,#e8d4e8,#9868a8)',
	);
	return $palette[ (int) $index % count( $palette ) ];
}

/** Relative URL for a taxonomy term (resolved through adn_link() by callers). */
function adn_cms_term_url( $term ) {
	$slug = is_object( $term ) && isset( $term->slug ) ? (string) $term->slug : '';
	return '' !== $slug ? '/' . trim( $slug, '/' ) . '/' : '#';
}

/** Relative URL for an ah_posts row (resolved through adn_link() by callers). */
function adn_cms_post_url( $post ) {
	$slug = is_object( $post ) && isset( $post->slug ) ? (string) $post->slug : '';
	return '' !== $slug ? '/' . trim( $slug, '/' ) . '/' : '#';
}
