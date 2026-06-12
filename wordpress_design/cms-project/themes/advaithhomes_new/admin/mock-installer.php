<?php
/**
 * admin/mock-installer.php — seed sample content into the CMS plugin tables.
 *
 * Creates the Guide hierarchy the home page reads through apis/services_cms.php:
 *   Guide (type) → Buying / Selling / House Movers (parents) → topics → articles
 * plus a few news posts (independent of the tree).
 *
 * Idempotent: every row is matched by slug first, so running it twice never
 * duplicates. Writes directly to the plugin's wp_ah_* tables (the documented
 * integration point) and no-ops cleanly if those tables are absent.
 */

defined( 'ABSPATH' ) || exit;

class ADN_Mock_Installer {

	/**
	 * Seed the sample Guide terms, topics, articles and news.
	 *
	 * @return array { ok:bool, message:string, summary:array }
	 */
	public static function seed() {
		global $wpdb;

		if ( ! function_exists( 'adn_cms_available' ) || ! adn_cms_available() ) {
			return array(
				'ok'      => false,
				'message' => __( 'CMS plugin tables not found — activate the CMS plugin first, then seed.', ADN_TEXT_DOMAIN ),
				'summary' => array(),
			);
		}

		$types = $wpdb->prefix . 'ah_taxonomy_types';
		$tax   = $wpdb->prefix . 'ah_taxonomies';
		$posts = $wpdb->prefix . 'ah_posts';
		$ct    = $wpdb->prefix . 'ah_content_taxonomies';

		$summary = array( 'type' => 0, 'parents' => 0, 'topics' => 0, 'articles' => 0, 'news' => 0 );

		// 1. "Guide" taxonomy type.
		$type_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$types}` WHERE slug = %s", 'guide' ) );
		if ( ! $type_id ) {
			$wpdb->insert( $types, array(
				'name'        => 'Guide',
				'slug'        => 'guide',
				'description' => 'Property guide categories',
			) );
			$type_id          = (int) $wpdb->insert_id;
			$summary['type']  = 1;
		}
		if ( ! $type_id ) {
			return array( 'ok' => false, 'message' => __( 'Could not create the Guide taxonomy type.', ADN_TEXT_DOMAIN ), 'summary' => $summary );
		}

		// 2. Parent terms (the "journey" cards).
		$parents = array(
			array( 'name' => 'Buying',       'slug' => 'buying',       'icon' => '🏡', 'desc' => 'Step-by-step guides from budgeting to moving in.' ),
			array( 'name' => 'Selling',      'slug' => 'selling',      'icon' => '🏘️', 'desc' => 'Expert information to help you sell with confidence.' ),
			array( 'name' => 'House Movers', 'slug' => 'house-movers', 'icon' => '📦', 'desc' => 'Planning to move? Find checklists, timelines & tips.' ),
		);
		$parent_id = array();
		foreach ( $parents as $i => $p ) {
			$row                      = self::ensure_term( $tax, $type_id, 0, $p['name'], $p['slug'], $p['desc'], $p['icon'], $i );
			$parent_id[ $p['slug'] ]  = $row['id'];
			$summary['parents']      += $row['created'] ? 1 : 0;
		}

		// 3. Topics (child terms) per parent.
		$topics = array(
			'buying'       => array( 'First-Time Buyers', 'Mortgages', 'Conveyancing', 'Stamp Duty' ),
			'selling'      => array( 'Preparing to Sell', 'Estate Agents', 'Selling Costs' ),
			'house-movers' => array( 'Moving Checklist', 'Removals', 'Change of Address' ),
		);
		$topic_id = array(); // slug => id
		foreach ( $topics as $pslug => $names ) {
			foreach ( $names as $j => $tname ) {
				$tslug              = sanitize_title( $pslug . '-' . $tname );
				$row                = self::ensure_term( $tax, $type_id, (int) $parent_id[ $pslug ], $tname, $tslug, '', '', $j );
				$topic_id[ $tslug ] = $row['id'];
				$summary['topics'] += $row['created'] ? 1 : 0;
			}
		}

		// 4. Articles (post_type=article) linked to a topic term.
		$articles = array(
			array( 'First-Time Buyer Guide in the UK',      'first-time-buyer-guide', 'A complete step-by-step guide for first-time buyers.',        'buying-first-time-buyers', 1 ),
			array( 'Understanding Mortgage in Principle',   'mortgage-in-principle',  'Why it matters and how it strengthens your offer.',           'buying-mortgages',         0 ),
			array( 'The Conveyancing Process Explained',    'conveyancing-process',   'Each step of the legal process in plain English.',            'buying-conveyancing',      0 ),
			array( 'How to Sell Your Home Successfully',    'how-to-sell-your-home',  'Expert tips to help you sell faster and for the right price.', 'selling-preparing-to-sell',0 ),
			array( 'Moving Home Checklist',                 'moving-home-checklist',  'Your ultimate checklist for a smooth move.',                  'house-movers-moving-checklist', 0 ),
		);
		foreach ( $articles as $a ) {
			list( $title, $slug, $excerpt, $topic_slug, $featured ) = $a;
			$row = self::ensure_post( $posts, 'article', $title, $slug, $excerpt, '<p>' . esc_html( $excerpt ) . '</p>', (bool) $featured );
			if ( $row['created'] ) {
				$summary['articles']++;
			}
			if ( isset( $topic_id[ $topic_slug ] ) ) {
				self::link_term( $ct, $row['id'], (int) $topic_id[ $topic_slug ] );
			}
		}

		// 5. News (post_type=news), independent of the Guide tree.
		$news = array(
			array( 'UK House Prices Rise 1.4% in April – Latest ONS Data', 'news-house-prices-april',     'The latest ONS figures show a modest monthly rise.' ),
			array( 'Mortgage Rates Hold Steady – What It Means for Buyers', 'news-mortgage-rates-steady',  'Lenders keep rates flat as markets settle.' ),
			array( 'RICS: Buyer Enquiries Reach 12-Month High',            'news-rics-buyer-enquiries',   'Surveyors report renewed buyer demand.' ),
			array( 'Stamp Duty Receipts Hit Record High in Q1',            'news-stamp-duty-receipts',    'HMRC data shows record SDLT receipts.' ),
		);
		foreach ( $news as $n ) {
			list( $title, $slug, $excerpt ) = $n;
			$row = self::ensure_post( $posts, 'news', $title, $slug, $excerpt, '<p>' . esc_html( $excerpt ) . '</p>', false );
			if ( $row['created'] ) {
				$summary['news']++;
			}
		}

		$message = sprintf(
			/* translators: 1: parents, 2: topics, 3: articles, 4: news created */
			__( 'Sample content ready — %1$d guide(s), %2$d topic(s), %3$d article(s), %4$d news created (existing items were left untouched).', ADN_TEXT_DOMAIN ),
			$summary['parents'],
			$summary['topics'],
			$summary['articles'],
			$summary['news']
		);

		return array( 'ok' => true, 'message' => $message, 'summary' => $summary );
	}

	/** Insert a taxonomy term if its slug is not already present (within the type). */
	private static function ensure_term( $tax, $type_id, $parent_id, $name, $slug, $desc, $icon, $sort ) {
		global $wpdb;
		$id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$tax}` WHERE slug = %s AND type_id = %d", $slug, $type_id ) );
		if ( $id ) {
			return array( 'id' => $id, 'created' => false );
		}
		$wpdb->insert( $tax, array(
			'type_id'     => $type_id,
			'parent_id'   => $parent_id ? $parent_id : null,
			'name'        => $name,
			'slug'        => $slug,
			'description' => $desc,
			'status'      => 'active',
			'sort_order'  => (int) $sort,
			'icon_emoji'  => $icon ? $icon : null,
		) );
		return array( 'id' => (int) $wpdb->insert_id, 'created' => true );
	}

	/** Insert an ah_posts row if its (slug, post_type) is not already present. */
	private static function ensure_post( $posts, $type, $title, $slug, $excerpt, $content, $featured ) {
		global $wpdb;
		$id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$posts}` WHERE slug = %s AND post_type = %s", $slug, $type ) );
		if ( $id ) {
			return array( 'id' => $id, 'created' => false );
		}
		$wpdb->insert( $posts, array(
			'post_type'    => $type,
			'title'        => $title,
			'slug'         => $slug,
			'excerpt'      => $excerpt,
			'content'      => $content,
			'status'       => 'active',
			'is_featured'  => $featured ? 1 : 0,
			'published_at' => current_time( 'mysql' ),
		) );
		return array( 'id' => (int) $wpdb->insert_id, 'created' => true );
	}

	/** Link a post to a taxonomy term (no-op if already linked). */
	private static function link_term( $ct, $post_id, $term_id ) {
		global $wpdb;
		if ( ! $post_id || ! $term_id ) {
			return;
		}
		$wpdb->query( $wpdb->prepare(
			"INSERT IGNORE INTO `{$ct}` (object_type, object_id, taxonomy_id) VALUES ('ah_post', %d, %d)",
			$post_id,
			$term_id
		) );
	}
}
