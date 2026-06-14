<?php
/**
 * admin/mock-installer.php - seed sample content into the CMS plugin's real
 * data model (exactly how the plugin stores it):
 *
 *   - Parent Terms  → wp_ah_taxonomy_parent_terms  (Buying / Selling / House Movers, + News)
 *   - Terms (topics)→ wp_ah_taxonomies  (type = Category, parent_term_id = the parent)
 *   - Content       → WordPress posts (post_type='post', status='publish')
 *   - Post ↔ Term   → AH_Content_Taxonomy_Model::sync_terms('wp_post', …)
 *                     → wp_ah_content_taxonomies (object_type='wp_post')
 *
 * Idempotent (parent terms + terms matched by slug, posts by slug). No-ops when
 * the plugin tables are absent.
 */

defined( 'ABSPATH' ) || exit;

class ADN_Mock_Installer {

	/** @return array { ok:bool, message:string, summary:array } */
	public static function seed() {
		global $wpdb;

		if ( ! function_exists( 'adn_cms_available' ) || ! adn_cms_available() ) {
			return array(
				'ok'      => false,
				'message' => __( 'CMS plugin tables not found - activate the CMS plugin first, then seed.', ADN_TEXT_DOMAIN ),
				'summary' => array(),
			);
		}

		// Make sure the Parent Terms table + ah_taxonomies.parent_term_id column exist.
		if ( class_exists( 'AH_Taxonomy_Parent_Model' ) ) {
			AH_Taxonomy_Parent_Model::ensure_table();
		}

		$types = $wpdb->prefix . 'ah_taxonomy_types';
		$tax   = $wpdb->prefix . 'ah_taxonomies';
		$pt    = $wpdb->prefix . 'ah_taxonomy_parent_terms';

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $pt ) ) !== $pt ) {
			return array( 'ok' => false, 'message' => __( 'Parent Terms table is missing - open the plugin Taxonomies → Parent Terms once, then seed.', ADN_TEXT_DOMAIN ), 'summary' => array() );
		}

		$summary = array( 'parents' => 0, 'terms' => 0, 'posts' => 0 );

		// 1. "Category" taxonomy type for the terms (plugin seeds it; create if missing).
		$type_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$types}` WHERE slug = %s", 'category' ) );
		if ( ! $type_id ) {
			$wpdb->insert( $types, array( 'name' => 'Category', 'slug' => 'category', 'description' => 'Content categories' ) );
			$type_id = (int) $wpdb->insert_id;
		}

		// 2. Parent Terms (the "journey" cards).
		$parents = array(
			
		);
		$parent_id = array();
		foreach ( $parents as $i => $p ) {
			$row                     = self::ensure_parent_term( $pt, $p['name'], $p['slug'], $p['desc'], $p['icon'], $i );
			$parent_id[ $p['slug'] ] = $row['id'];
			$summary['parents']     += $row['created'] ? 1 : 0;
		}

		// News parent (+ a term to attach news posts to).
		$news_parent  = self::ensure_parent_term( $pt, 'News', 'news', 'Latest property news', '📰', 9 );
		$summary['parents'] += $news_parent['created'] ? 1 : 0;

		// 3. Terms (topics) under each parent term.
		$topics = array(
		);
		$term_id = array(); // slug => id
		foreach ( $topics as $pslug => $names ) {
			foreach ( $names as $j => $tname ) {
				$tslug             = sanitize_title( $pslug . '-' . $tname );
				$row               = self::ensure_term( $tax, $type_id, (int) $parent_id[ $pslug ], $tname, $tslug, $j );
				$term_id[ $tslug ] = $row['id'];
				$summary['terms'] += $row['created'] ? 1 : 0;
			}
		}
		// A term to hang news posts on.
		$news_term         = self::ensure_term( $tax, $type_id, (int) $news_parent['id'], 'Property News', 'news-property', 0 );
		$summary['terms'] += $news_term['created'] ? 1 : 0;

		// 4. Articles → WordPress posts linked to a topic term.
		$articles = array(
		);
		foreach ( $articles as $a ) {
			list( $title, $slug, $excerpt, $topic_slug, $featured ) = $a;
			$ids = isset( $term_id[ $topic_slug ] ) ? array( (int) $term_id[ $topic_slug ] ) : array();
			$summary['posts'] += self::ensure_wp_post( $title, $slug, $excerpt, $ids, (bool) $featured );
		}

		// 5. News → WordPress posts linked to the News term.
		$news = array(

		);
		foreach ( $news as $n ) {
			list( $title, $slug, $excerpt ) = $n;
			$summary['posts'] += self::ensure_wp_post( $title, $slug, $excerpt, array( (int) $news_term['id'] ), false );
		}

		$message = sprintf(
			/* translators: 1: parent terms, 2: terms, 3: posts created */
			__( 'Sample content ready - %1$d parent term(s), %2$d term(s) and %3$d WordPress post(s) created (existing items left untouched).', ADN_TEXT_DOMAIN ),
			$summary['parents'],
			$summary['terms'],
			$summary['posts']
		);

		return array( 'ok' => true, 'message' => $message, 'summary' => $summary );
	}

	/** Insert a Parent Term if its slug is free. @return array{id:int,created:bool} */
	private static function ensure_parent_term( $pt, $name, $slug, $desc, $icon, $sort ) {
		global $wpdb;
		$id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$pt}` WHERE slug = %s", $slug ) );
		if ( $id ) {
			return array( 'id' => $id, 'created' => false );
		}
		$wpdb->insert( $pt, array(
			'name'        => $name,
			'slug'        => $slug,
			'description' => $desc,
			'icon_emoji'  => $icon ? $icon : null,
			'status'      => 'active',
			'sort_order'  => (int) $sort,
		) );
		return array( 'id' => (int) $wpdb->insert_id, 'created' => true );
	}

	/** Insert a Term (topic) if its slug is free within the type. @return array{id:int,created:bool} */
	private static function ensure_term( $tax, $type_id, $parent_term_id, $name, $slug, $sort ) {
		global $wpdb;
		$id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$tax}` WHERE slug = %s AND type_id = %d", $slug, $type_id ) );
		if ( $id ) {
			return array( 'id' => $id, 'created' => false );
		}
		$wpdb->insert( $tax, array(
			'type_id'        => $type_id,
			'parent_id'      => null,
			'parent_term_id' => $parent_term_id ? $parent_term_id : null,
			'name'           => $name,
			'slug'           => $slug,
			'status'         => 'active',
			'sort_order'     => (int) $sort,
		) );
		return array( 'id' => (int) $wpdb->insert_id, 'created' => true );
	}

	/**
	 * Create a WordPress post (if its slug is free) and link it to the given
	 * Term ids the way the plugin does (sync_terms 'wp_post'). @return int created.
	 */
	private static function ensure_wp_post( $title, $slug, $excerpt, $term_ids, $featured = false ) {
		$existing = get_page_by_path( $slug, OBJECT, 'post' );
		$created  = 0;

		if ( $existing instanceof WP_Post ) {
			$post_id = (int) $existing->ID;
		} else {
			$post_id = wp_insert_post( array(
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_excerpt' => $excerpt,
				'post_content' => '<p>' . esc_html( $excerpt ) . '</p>',
			) );
			if ( $post_id && ! is_wp_error( $post_id ) ) {
				$created = 1;
			}
		}

		if ( $post_id && ! is_wp_error( $post_id ) ) {
			$term_ids = array_values( array_filter( array_map( 'intval', (array) $term_ids ) ) );
			if ( class_exists( 'AH_Content_Taxonomy_Model' ) ) {
				( new AH_Content_Taxonomy_Model() )->sync_terms( 'wp_post', (int) $post_id, $term_ids );
			} else {
				global $wpdb;
				$ct = $wpdb->prefix . 'ah_content_taxonomies';
				foreach ( $term_ids as $tid ) {
					$wpdb->query( $wpdb->prepare(
						"INSERT IGNORE INTO `{$ct}` (object_type, object_id, taxonomy_id) VALUES ('wp_post', %d, %d)",
						(int) $post_id,
						$tid
					) );
				}
			}
			update_post_meta( (int) $post_id, '_ah_is_featured', $featured ? '1' : '0' );
		}

		return $created;
	}
}
