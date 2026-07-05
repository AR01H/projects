<?php
/**
 * apis/services_cms.php - Read-only data services backed by the CMS plugin DB.
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
 * (Buying/Selling/House Movers …). 0 means "no specific type - use all terms".
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
 * Child terms under one parent - category type only (excludes glossary etc.).
 *
 * @return object[] taxonomy rows.
 */
function adn_cms_category_topics( $parent_term_id, $limit = 100 ) {
	$parent_term_id = (int) $parent_term_id;
	if ( ! adn_cms_available() || ! $parent_term_id ) {
		return array();
	}
	global $wpdb;
	$pt    = adn_cms_table( 'taxonomy_parent_terms' );
	$tax   = adn_cms_table( 'taxonomies' );
	$types = adn_cms_table( 'taxonomy_types' );
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $pt ) ) !== $pt ) {
		return array();
	}
	return $wpdb->get_results( $wpdb->prepare(
		"SELECT t.* FROM `{$tax}` t
		 LEFT JOIN `{$types}` tt ON tt.id = t.type_id
		 WHERE t.parent_term_id = %d AND t.status = 'active'
		   AND ( tt.slug IS NULL OR tt.slug NOT IN ('glossary','news') )
		 ORDER BY t.sort_order ASC, t.name ASC LIMIT %d",
		$parent_term_id,
		max( 1, (int) $limit )
	) ) ?: array();
}

/**
 * All active child taxonomy terms (categories) across every parent term.
 * Returns rows from ah_taxonomies joined with ah_taxonomy_parent_terms so
 * callers get parent_name / parent_slug without a second query.
 *
 * @return object[]
 */
function adn_cms_all_categories( $limit = 300 ) {
	if ( ! adn_cms_available() ) { return array(); }
	global $wpdb;
	$pt  = adn_cms_table( 'taxonomy_parent_terms' );
	$tax = adn_cms_table( 'taxonomies' );
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $pt ) ) !== $pt ) {
		return array();
	}
	return $wpdb->get_results( $wpdb->prepare(
		"SELECT t.*, pt.name AS parent_name, pt.slug AS parent_slug
		 FROM `{$tax}` t
		 INNER JOIN `{$pt}` pt ON pt.id = t.parent_term_id
		 WHERE t.parent_term_id IS NOT NULL AND t.status = 'active'
		 ORDER BY pt.sort_order ASC, t.sort_order ASC, t.name ASC
		 LIMIT %d",
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
 * Latest news posts - WordPress posts linked to the "news" term. Falls back to
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
 * Articles belonging to a Guide parent - i.e. linked to that parent term OR any
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
	// Parent has no child terms → no articles to show for this parent.
	if ( empty( $term_ids ) ) {
		return array();
	}
	return adn_cms_articles( $limit, $term_ids );
}

/**
 * Active News Bar items from the plugin's ah_news_bar_items table.
 * status=active, within start/end dates, newest-first (by start_date, then
 * created_at). No dependency on plugin PHP classes.
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
		 ORDER BY COALESCE( start_date, created_at ) DESC, id DESC
		 LIMIT %d",
		$today, $today, max( 1, (int) $limit )
	) ) ?: array();
}

/**
 * Internal URL for a single news-bar item single view.
 * Format: /news/?ah_news_id={id}
 *
 * @param int $id  news_bar_items.id
 * @return string
 */
function adn_newsbar_item_url( $id ) {
	return add_query_arg( 'ah_news_id', absint( $id ), trailingslashit( home_url( SITE_NEWS_URL ) ) );
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
 * One card per active taxonomy term that has at least one published WP post.
 *
 * Queries directly against wp_ah_taxonomies - does NOT require the
 * parent_term / journey hierarchy.  This means flat terms (like "First Time
 * Buyers") work even when parent_term_id is NULL.
 *
 * Each returned stdClass has:
 *   ID, title, slug, excerpt, published_at, created_at  (WP post fields)
 *   category_name  - the taxonomy term name
 *   _term_slug     - taxonomy term slug (for building the category page URL)
 *   _term_desc     - taxonomy term description
 *   parent_name    - parent term name (via parent_id within same table,
 *                    or via parent_term_id → taxonomy_parent_terms)
 *   parent_icon    - parent icon emoji
 *
 * @param int   $limit      Max category cards (default 10).
 * @param int[] $topic_ids  If non-empty, restrict to these term IDs.
 * @return stdClass[]
 */
/**
 * Category-card data for one active taxonomy "Category" term per row.
 *
 * Returns term rows - NOT articles. Cards display the taxonomy term's own name,
 * icon and description.  The card URL goes to the category listing page
 * (/<term-slug>/), which then shows linked articles.
 *
 * Cards are shown even when a term has zero linked WP posts, because the term
 * itself carries all display data (icon, description) set in the CMS admin.
 *
 * @param int   $limit      Max terms to return.
 * @param int[] $topic_ids  Restrict to these specific term IDs (empty = all active Category terms).
 * @return object[]  stdClass per term: category_name, _term_slug, _term_desc, term_icon, parent_name, parent_icon.
 */
function adn_cms_guides_by_category( $limit = 10, $topic_ids = array() ) {
	if ( ! adn_cms_available() ) {
		return array();
	}

	global $wpdb;
	$tax   = adn_cms_table( 'taxonomies' );
	$pt    = adn_cms_table( 'taxonomy_parent_terms' );
	$limit = max( 1, (int) $limit );

	// Filter by topic IDs when provided (used for parent-term category page).
	$id_filter = '';
	if ( ! empty( $topic_ids ) ) {
		$ids       = implode( ',', array_map( 'absint', (array) $topic_ids ) );
		$id_filter = "AND t.id IN ({$ids})";
	}

	// Restrict to the "Category" taxonomy type so review-types, FAQ tags etc. are excluded.
	// When topic_ids is given we trust those IDs directly; type filter is still applied for safety.
	$type_id   = adn_cms_guide_type_id();
	$type_cond = '';
	if ( $type_id ) {
		$type_cond = "AND t.type_id = {$type_id}";
	} elseif ( empty( $topic_ids ) ) {
		// No recognised type and no ID filter - require a parent_term_id so we
		// don't accidentally show flat utility terms.
		$type_cond = 'AND t.parent_term_id IS NOT NULL';
	}

	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$rows = $wpdb->get_results(
		"SELECT t.id AS term_id, t.name AS category_name, t.slug AS term_slug,
		        t.description AS term_desc, t.icon_emoji AS term_icon,
		        t.image_id AS term_image_id,
		        pt_self.name AS parent_name, pt_self.icon_emoji AS parent_icon
		 FROM `{$tax}` t
		 LEFT JOIN `{$tax}` pt_self ON pt_self.id = t.parent_id
		 WHERE t.status = 'active'
		   {$type_cond}
		   {$id_filter}
		 ORDER BY t.sort_order ASC, t.name ASC
		 LIMIT " . ( $limit * 2 )
	) ?: array();

	if ( empty( $rows ) ) {
		return array();
	}

	// Enrich parent_name from ah_taxonomy_parent_terms when the self-join gave nothing.
	$has_pt = ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $pt ) ) === $pt );

	$result = array();

	foreach ( $rows as $row ) {
		if ( count( $result ) >= $limit ) {
			break;
		}

		$parent_name = ! empty( $row->parent_name ) ? (string) $row->parent_name : '';
		$parent_icon = ! empty( $row->parent_icon ) ? (string) $row->parent_icon : '';

		if ( '' === $parent_name && $has_pt ) {
			$ptid = (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT parent_term_id FROM `{$tax}` WHERE id = %d LIMIT 1",
				(int) $row->term_id
			) );
			if ( $ptid ) {
				$pt_row = $wpdb->get_row( $wpdb->prepare(
					"SELECT name, icon_emoji FROM `{$pt}` WHERE id = %d LIMIT 1",
					$ptid
				) );
				if ( $pt_row ) {
					$parent_name = (string) $pt_row->name;
					$parent_icon = ! empty( $pt_row->icon_emoji ) ? (string) $pt_row->icon_emoji : '';
				}
			}
		}

		$post               = new stdClass();
		$post->category_name   = (string) $row->category_name;
		$post->_term_slug      = (string) $row->term_slug;
		$post->_term_desc      = ! empty( $row->term_desc ) ? (string) $row->term_desc : '';
		$post->term_icon       = ! empty( $row->term_icon ) ? (string) $row->term_icon : '';
		$post->term_image_id   = ! empty( $row->term_image_id ) ? (int) $row->term_image_id : 0;
		$post->parent_name     = $parent_name;
		$post->parent_icon     = $parent_icon ?: $post->term_icon;

		$result[] = $post;
	}

	return $result;
}

/**
 * All published WP posts linked to a single taxonomy term slug.
 * Used by the topic category page (page-topic_category_guide.php).
 *
 * @param string $term_slug  Slug from wp_ah_taxonomies.
 * @param int    $limit
 * @return object[]  Standard WP post objects.
 */
function adn_cms_posts_for_term_slug( $term_slug, $limit = 20 ) {
	$term_slug = sanitize_key( (string) $term_slug );
	if ( ! adn_cms_available() || '' === $term_slug ) {
		return array();
	}
	global $wpdb;
	$tax = adn_cms_table( 'taxonomies' );
	$ct  = adn_cms_table( 'content_taxonomies' );

	$term = $wpdb->get_row( $wpdb->prepare(
		"SELECT id FROM `{$tax}` WHERE slug = %s AND status = 'active' LIMIT 1",
		$term_slug
	) );
	if ( ! $term ) {
		return array();
	}
	return adn_cms_articles( max( 1, (int) $limit ), array( (int) $term->id ) );
}

/**
 * Full taxonomy term row from wp_ah_taxonomies by slug, or null.
 */
function adn_cms_taxonomy_term_by_slug( $slug ) {
	$slug = sanitize_key( (string) $slug );
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
 * CMS taxonomy breadcrumb for a WP post.
 *
 * Looks up the plugin tables to find the child term (and its parent) linked to
 * the given WP post ID, then builds a breadcrumb trail:
 *   Home > ParentTermName > ChildTermName > PostTitle
 *
 * Returns null when tables are absent or the post has no linked CMS terms
 * (caller falls back to WP categories).
 *
 * @param int    $post_id
 * @param string $post_title
 * @return array[]|null
 */
function adn_cms_post_breadcrumb( $post_id, $post_title ) {
	$post_id = absint( $post_id );
	if ( ! $post_id || ! adn_cms_available() ) {
		return null;
	}
	global $wpdb;
	$ct  = adn_cms_table( 'content_taxonomies' );
	$tax = adn_cms_table( 'taxonomies' );
	$pt  = adn_cms_table( 'taxonomy_parent_terms' );

	$has_pt = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $pt ) );

	if ( $has_pt ) {
		$term = $wpdb->get_row( $wpdb->prepare(
			"SELECT t.id, t.name, t.slug, t.parent_term_id,
			        pt.name AS parent_name, pt.slug AS parent_slug
			 FROM `{$ct}` ct
			 INNER JOIN `{$tax}` t  ON t.id  = ct.taxonomy_id
			 LEFT  JOIN `{$pt}`  pt ON pt.id = t.parent_term_id
			 WHERE ct.object_type = 'wp_post' AND ct.object_id = %d
			   AND t.status = 'active'
			 ORDER BY (t.parent_term_id IS NOT NULL) DESC, t.id ASC
			 LIMIT 1",
			$post_id
		) );
	} else {
		$term = $wpdb->get_row( $wpdb->prepare(
			"SELECT t.id, t.name, t.slug, t.parent_term_id
			 FROM `{$ct}` ct
			 INNER JOIN `{$tax}` t ON t.id = ct.taxonomy_id
			 WHERE ct.object_type = 'wp_post' AND ct.object_id = %d
			   AND t.status = 'active'
			 ORDER BY t.id ASC
			 LIMIT 1",
			$post_id
		) );
	}

	if ( ! $term ) {
		return null;
	}

	$crumbs = array( array( 'label' => PAGE_TITLE_HOME, 'url' => '/' ) );

	$parent_name = ! empty( $term->parent_name ) ? (string) $term->parent_name : '';
	$parent_slug = ! empty( $term->parent_slug ) ? (string) $term->parent_slug : '';

	if ( '' !== $parent_name && '' !== $parent_slug ) {
		$crumbs[] = array( 'label' => $parent_name, 'url' => '/' . trim( $parent_slug, '/' ) . '/' );
	}

	$crumbs[] = array( 'label' => (string) $term->name, 'url' => '/' . trim( (string) $term->slug, '/' ) . '/' );
	$crumbs[] = array( 'label' => (string) $post_title, 'url' => null );

	return $crumbs;
}

/**
 * Shared latest-news items for any page widget.
 * Priority: newsbar → CMS news posts → WP_Query.
 * Returns card-ready arrays: { title, date, tag, thumbnail, gradient, url }.
 *
 * @param int $limit
 * @return array[]
 */
function adn_shared_latest_news_items( $limit = 3 ) {
	$items = array();

	if ( function_exists( 'adn_cms_newsbar_items' ) ) {
		foreach ( adn_cms_newsbar_items( $limit ) as $i => $item ) {
			$title = isset( $item->text ) ? (string) $item->text : '';
			if ( '' === $title ) { continue; }
			$stamp     = ! empty( $item->start_date ) ? $item->start_date : '';
			$thumb_url = '';
			if ( ! empty( $item->image_id ) ) {
				$_tu = wp_get_attachment_image_url( (int) $item->image_id, 'thumbnail' );
				if ( $_tu ) { $thumb_url = (string) $_tu; }
			}
			$items[] = array(
				'title'     => $title,
				'date'      => $stamp ? date_i18n( 'M j, Y', strtotime( $stamp ) ) : '',
				'tag'       => ! empty( $item->label ) ? (string) $item->label : '',
				'thumbnail' => $thumb_url,
				'gradient'  => adn_cms_gradient( $i ),
				'url'       => ! empty( $item->link_url ) ? (string) $item->link_url : ( defined( 'SITE_NEWS_URL' ) ? SITE_NEWS_URL : '/' ),
			);
		}
	}

	return $items;
}

/**
 * Shared latest-updates (regulations) items for any page widget.
 * Reads the admin-selected posts from adn_home_newsblocks → regulations.
 * Returns news_widget-compatible arrays: { title, date, tag, thumbnail, url }.
 *
 * @param int $limit
 * @return array[]
 */
function adn_shared_latest_updates_items( $limit = 3 ) {
	$opt = get_option( 'adn_home_newsblocks', array() );
	$raw = ( isset( $opt['regulations']['items'] ) && is_array( $opt['regulations']['items'] ) )
	       ? $opt['regulations']['items'] : array();
	$items = array();
	foreach ( $raw as $i => $row ) {
		if ( count( $items ) >= $limit ) { break; }
		$pid = (int) ( isset( $row['post_id'] ) ? $row['post_id'] : 0 );
		if ( ! $pid ) { continue; }
		$post = get_post( $pid );
		if ( ! $post || 'publish' !== $post->post_status ) { continue; }
		$badge_raw  = isset( $row['badge'] ) ? sanitize_text_field( $row['badge'] ) : 'GOV UK';
		$badge_text = trim( str_replace( "\n", ' ', $badge_raw ) );
		$thumb      = get_the_post_thumbnail_url( $pid, 'thumbnail' ) ?: '';
		$item       = array(
			'title'    => $post->post_title,
			'date'     => get_the_date( 'M j, Y', $post ),
			'url'      => get_permalink( $post ),
			'gradient' => adn_cms_gradient( $i ),
		);
		if ( '' !== $thumb ) {
			$item['thumbnail'] = $thumb;
			if ( '' !== $badge_text ) {
				$item['overlay'] = $badge_text; // badge shown on top of thumbnail
			}
		} else {
			$item['icon'] = '📋';
			if ( '' !== $badge_text ) {
				$item['tag'] = $badge_text; // badge shown as tag chip when no image
			}
		}
		$items[] = $item;
	}
	return $items;
}

/**
 * URL for a post row. These are real WordPress posts, so prefer the actual
 * permalink (which routes to single.php - no 404). Falls back to a slug path.
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
