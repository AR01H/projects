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

/**
 * True only when the plugin's taxonomy tree + the post↔term link table exist.
 * (Content itself lives in WordPress posts, which always exist.) Cached.
 */
function adn_cms_available() {
	static $ready = null;
	if ( null !== $ready ) {
		return $ready;
	}
	global $wpdb;
	$tax = adn_cms_table( 'taxonomies' );
	$ct  = adn_cms_table( 'content_taxonomies' );
	$found = $wpdb->get_col( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tax ) );
	$found = array_merge( $found, $wpdb->get_col( $wpdb->prepare( 'SHOW TABLES LIKE %s', $ct ) ) );
	$ready = ( in_array( $tax, $found, true ) && in_array( $ct, $found, true ) );
	return $ready;
}

/**
 * The taxonomy-type id used for content categories. The plugin seeds a
 * "Category" type (slug 'category') whose top-level terms are the site sections
 * (Buying/Selling/House Movers …). 0 means "no specific type — use all terms".
 */
function adn_cms_guide_type_id() {
	if ( ! adn_cms_available() ) {
		return 0;
	}
	global $wpdb;
	$types = adn_cms_table( 'taxonomy_types' );
	return (int) $wpdb->get_var(
		"SELECT id FROM `{$types}`
		 WHERE slug IN ('category','guide','guides') OR name LIKE '%Categor%' OR name LIKE '%Guide%'
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
	$pt = adn_cms_table( 'taxonomy_parent_terms' );
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $pt ) ) !== $pt ) {
		return array();
	}
	return $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM `{$pt}`
		 WHERE status = 'active' AND slug <> 'news'
		 ORDER BY sort_order ASC, name ASC LIMIT %d",
		max( 1, (int) $limit )
	) ) ?: array();
}

/** A single active Parent Term by slug (from ah_taxonomy_parent_terms), or null. */
function adn_cms_parent_by_slug( $slug ) {
	$slug = sanitize_title( (string) $slug );
	if ( ! adn_cms_available() || '' === $slug ) {
		return null;
	}
	global $wpdb;
	$pt = adn_cms_table( 'taxonomy_parent_terms' );
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $pt ) ) !== $pt ) {
		return null;
	}
	return $wpdb->get_row( $wpdb->prepare(
		"SELECT * FROM `{$pt}` WHERE slug = %s AND status = 'active' LIMIT 1",
		$slug
	) );
}

/**
 * Child topic terms under one Guide parent.
 *
 * @return object[] taxonomy rows.
 */
function adn_cms_topics( $parent_term_id, $limit = 100 ) {
	$parent_term_id = (int) $parent_term_id;
	if ( ! adn_cms_available() || ! $parent_term_id ) {
		return array();
	}
	global $wpdb;
	$pt = adn_cms_table( 'taxonomy_parent_terms' );
	// The parent_term_id column is created alongside this table; if the table is
	// absent the parent-term feature isn't set up, so there are no topics.
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $pt ) ) !== $pt ) {
		return array();
	}
	$tax = adn_cms_table( 'taxonomies' );
	return $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM `{$tax}`
		 WHERE parent_term_id = %d AND status = 'active'
		 ORDER BY sort_order ASC, name ASC LIMIT %d",
		$parent_term_id,
		max( 1, (int) $limit )
	) ) ?: array();
}

/**
 * Published WordPress posts, optionally restricted to a set of taxonomy term ids
 * (the plugin links posts ↔ terms in ah_content_taxonomies with
 * object_type = 'wp_post'). Rows are aliased to a stable shape so the card
 * mappers stay source-agnostic:
 *   ID, title, slug, excerpt, content, published_at, created_at, category_name
 *
 * @param int   $limit
 * @param int[] $taxonomy_ids  Restrict to posts linked to any of these terms.
 * @return object[]
 */
function adn_cms_articles( $limit = 6, $taxonomy_ids = array() ) {
	if ( ! adn_cms_available() ) {
		return array();
	}
	global $wpdb;
	$ct    = adn_cms_table( 'content_taxonomies' );
	$tax   = adn_cms_table( 'taxonomies' );
	$limit = max( 1, (int) $limit );

	// Primary category label for the card (prefer a child topic term).
	$category = "(
		SELECT t.name FROM `{$tax}` t
		INNER JOIN `{$ct}` c2 ON c2.taxonomy_id = t.id
		WHERE c2.object_type = 'wp_post' AND c2.object_id = p.ID
		ORDER BY ( t.parent_id IS NOT NULL ) DESC, t.id ASC LIMIT 1
	) AS category_name";

	$select = "p.ID AS ID, p.post_title AS title, p.post_name AS slug,
		p.post_excerpt AS excerpt, p.post_content AS content,
		p.post_date AS published_at, p.post_date AS created_at, {$category}";

	if ( ! empty( $taxonomy_ids ) ) {
		$ids = implode( ',', array_map( 'absint', (array) $taxonomy_ids ) );
		if ( '' === $ids ) {
			$ids = '0';
		}
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT DISTINCT {$select}
			 FROM `{$wpdb->posts}` p
			 INNER JOIN `{$ct}` ct ON ct.object_type = 'wp_post' AND ct.object_id = p.ID
			 WHERE p.post_type = 'post' AND p.post_status = 'publish'
			   AND ct.taxonomy_id IN ({$ids})
			 ORDER BY p.post_date DESC
			 LIMIT %d",
			$limit
		) ) ?: array();
	}

	return $wpdb->get_results( $wpdb->prepare(
		"SELECT {$select}
		 FROM `{$wpdb->posts}` p
		 WHERE p.post_type = 'post' AND p.post_status = 'publish'
		 ORDER BY p.post_date DESC
		 LIMIT %d",
		$limit
	) ) ?: array();
}

/**
 * Latest news posts — WordPress posts linked to the "news" term. Falls back to
 * the most recent posts when no news term exists.
 *
 * @return object[]
 */
function adn_cms_latest_news( $limit = 4 ) {
	if ( ! adn_cms_available() ) {
		return array();
	}
	// Prefer a "News" Parent Term and the posts under its terms…
	$news_parent = adn_cms_parent_by_slug( 'news' );
	if ( $news_parent ) {
		$term_ids = array();
		foreach ( adn_cms_topics( (int) $news_parent->id, 200 ) as $topic ) {
			$term_ids[] = (int) $topic->id;
		}
		if ( ! empty( $term_ids ) ) {
			return adn_cms_articles( $limit, $term_ids );
		}
	}
	// …else a single "news" Term, …else just the most recent posts.
	$news_term = adn_cms_term_by_slug( 'news' );
	if ( $news_term ) {
		return adn_cms_articles( $limit, array( (int) $news_term->id ) );
	}
	return adn_cms_articles( $limit );
}

/**
 * A single active taxonomy term by slug (any type), or null.
 */
function adn_cms_term_by_slug( $slug ) {
	$slug = sanitize_title( (string) $slug );
	if ( ! adn_cms_available() || '' === $slug ) {
		return null;
	}
	global $wpdb;
	$tax = adn_cms_table( 'taxonomies' );
	return $wpdb->get_row( $wpdb->prepare(
		"SELECT * FROM `{$tax}` WHERE slug = %s AND status = 'active' LIMIT 1",
		$slug
	) );
}

/**
 * One representative article per Guide parent term (Buying/Selling/House Movers).
 * Used by the home-page Guides & Insights section so each card represents a
 * distinct category instead of random articles.
 *
 * @return object[]  One post per active parent (with category_name = parent name).
 */
function adn_cms_one_article_per_parent() {
	$parents = adn_cms_guide_parents( 12 );
	$results = array();
	foreach ( $parents as $parent ) {
		$slug = isset( $parent->slug ) ? (string) $parent->slug : '';
		if ( '' === $slug ) {
			continue;
		}
		$articles = adn_cms_articles_for_parent( $slug, 1 );
		if ( ! empty( $articles ) ) {
			$article                = $articles[0];
			$article->category_name = isset( $parent->name ) ? $parent->name : '';
			$article->_parent_slug  = $slug;
			$article->_parent_icon  = isset( $parent->icon_emoji ) ? $parent->icon_emoji : '';
			$results[]              = $article;
		}
	}
	return $results;
}

/**
 * Articles belonging to a Guide parent — i.e. linked to that parent term OR any
 * of its child topics. Used by the guides-listing / category pages.
 *
 * @param string $parent_slug e.g. 'buying'.
 * @return object[] ah_posts rows (+ category_name).
 */
function adn_cms_articles_for_parent( $parent_slug, $limit = 12 ) {
	$parent = adn_cms_parent_by_slug( $parent_slug );
	if ( ! $parent ) {
		return array();
	}
	// Posts link to Terms (ah_taxonomies), so gather this parent's term ids.
	$term_ids = array();
	foreach ( adn_cms_topics( (int) $parent->id, 200 ) as $topic ) {
		$term_ids[] = (int) $topic->id;
	}
	if ( empty( $term_ids ) ) {
		return array();
	}
	return adn_cms_articles( $limit, $term_ids );
}

/**
 * Active News Bar items from the plugin's ah_news_bar_items table.
 * Mirrors AH_Newsbar_Model::get_active(): status=active, within start/end dates,
 * ordered by sort_order. No dependency on plugin PHP classes.
 *
 * @param int $limit
 * @return object[]  { id, text, content, image_id, link_url, link_target, start_date, end_date, sort_order }
 */
function adn_cms_newsbar_items( $limit = 6 ) {
	global $wpdb;
	$t     = adn_cms_table( 'news_bar_items' );
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $t ) ) !== $t ) {
		return array();
	}
	$today = current_time( 'Y-m-d' );
	return $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM `{$t}`
		 WHERE status = 'active'
		   AND ( start_date IS NULL OR start_date <= %s )
		   AND ( end_date   IS NULL OR end_date   >= %s )
		 ORDER BY sort_order ASC
		 LIMIT %d",
		$today, $today, max( 1, (int) $limit )
	) ) ?: array();
}

/* ── Presentation helpers (shared by any page mapping CMS rows to cards) ───── */

/** Rough "N min read" from body content (≈200 words/min). */
function adn_cms_read_time( $content ) {
	$words = str_word_count( wp_strip_all_tags( (string) $content ) );
	return max( 1, (int) ceil( $words / 200 ) ) . ' min read';
}

/** Format an ah_posts timestamp ("M j, Y"); falls back to created_at. */
function adn_cms_post_date( $post ) {
	$stamp = '';
	if ( is_object( $post ) ) {
		$stamp = ! empty( $post->published_at ) ? $post->published_at : ( isset( $post->created_at ) ? $post->created_at : '' );
	}
	return $stamp ? date_i18n( 'M j, Y', strtotime( $stamp ) ) : '';
}


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

/**
 * URL for a post row. These are real WordPress posts, so prefer the actual
 * permalink (which routes to single.php — no 404). Falls back to a slug path.
 */
function adn_cms_post_url( $post ) {
	if ( is_object( $post ) && ! empty( $post->ID ) && function_exists( 'get_permalink' ) ) {
		$link = get_permalink( (int) $post->ID );
		if ( $link ) {
			return $link;
		}
	}
	$slug = is_object( $post ) && isset( $post->slug ) ? (string) $post->slug : '';
	return '' !== $slug ? '/' . trim( $slug, '/' ) . '/' : '#';
}
