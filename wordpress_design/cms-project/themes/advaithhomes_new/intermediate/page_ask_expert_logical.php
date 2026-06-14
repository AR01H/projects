<?php
/**
 * intermediate/page_ask_expert_logical.php
 *
 * Builds the context array for pages/page-ask-expert.php.
 *
 * Priority: live DB experts (AH_Expert_DB) → JSON fallback when DB is empty.
 * Hero, breadcrumb, sidebar are all built dynamically - no JSON dependency.
 */

defined( 'ABSPATH' ) || exit;

/** Build the sidebar data array for the ask-an-expert page. */
function adn_ask_expert_sidebar_data() {
	/* ── Contact for help ──────────────────────────────────────────── */
	$contact_help = array(
		'heading'      => __( 'Contact for Help', ADN_TEXT_DOMAIN ),
		'desc'         => __( "Not sure which expert to choose? Get in touch and we'll guide you.", ADN_TEXT_DOMAIN ),
		'button_label' => __( 'Get in Touch', ADN_TEXT_DOMAIN ),
		'button_url'   => '/contact/',
	);

	/* ── Latest news → sidebar_news_mini shape ──────────────────────── */
	// Items need: { gradient, title, date, tag, url }
	$news_items = array();
	if ( function_exists( 'adn_cms_latest_news' ) && function_exists( 'adn_cms_gradient' ) ) {
		$_ni = 0;
		foreach ( (array) adn_cms_latest_news( 3 ) as $np ) {
			if ( empty( $np->title ) ) { continue; }
			$news_items[] = array(
				'gradient' => adn_cms_gradient( $_ni ),
				'title'    => (string) $np->title,
				'date'     => function_exists( 'adn_cms_post_date' ) ? adn_cms_post_date( $np ) : '',
				'tag'      => isset( $np->category_name ) ? (string) $np->category_name : '',
				'url'      => function_exists( 'adn_cms_post_url' ) ? adn_cms_post_url( $np ) : '#',
			);
			$_ni++;
		}
	}
	if ( empty( $news_items ) ) {
		$_q = new WP_Query( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 3,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		) );
		foreach ( $_q->posts as $_ni => $_wp_p ) {
			$news_items[] = array(
				'gradient' => function_exists( 'adn_cms_gradient' ) ? adn_cms_gradient( $_ni ) : '',
				'title'    => $_wp_p->post_title,
				'date'     => get_the_date( 'M j, Y', $_wp_p ),
				'tag'      => '',
				'url'      => get_permalink( $_wp_p ),
			);
		}
		wp_reset_postdata();
	}
	// sidebar_news_mini shape
	$latest_news = array(
		'heading'  => __( 'Latest News', ADN_TEXT_DOMAIN ),
		'items'    => $news_items,
		'view_all' => array( 'label' => __( 'All News →', ADN_TEXT_DOMAIN ), 'url' => '/news/' ),
	);

	/* ── Calculators → sidebar_quick_tools shape ────────────────────── */
	// Items need: { icon, label, url }
	$calc_items = array();
	if ( function_exists( 'adn_calculators' ) ) {
		$_calcs_page = get_permalink( get_page_by_path( 'calculators' ) ) ?: home_url( '/calculators/' );
		$_ci = 0;
		foreach ( adn_calculators() as $_ck => $_calc ) {
			if ( $_ci >= 4 ) { break; }
			$calc_items[] = array(
				'icon'  => isset( $_calc['icon'] )  ? (string) $_calc['icon']  : '🧮',
				'label' => isset( $_calc['title'] ) ? (string) $_calc['title'] : $_ck,
				'url'   => $_calcs_page,
			);
			$_ci++;
		}
	}
	// sidebar_quick_tools shape
	$calculators = array(
		'heading' => __( 'Quick Calculators', ADN_TEXT_DOMAIN ),
		'items'   => $calc_items,
		'cta'     => array(
			'label' => __( 'All Calculators →', ADN_TEXT_DOMAIN ),
			'url'   => '/calculators/',
		),
	);

	/* ── Guide topics → sidebar_guide_parents shape ─────────────────── */
	// Items need: { icon, label, url, count }
	$topic_items = array();
	if ( function_exists( 'adn_cms_guide_parents' ) ) {
		foreach ( (array) adn_cms_guide_parents( 6 ) as $_parent ) {
			$_slug = isset( $_parent->slug ) ? (string) $_parent->slug : '';
			$_name = isset( $_parent->name ) ? (string) $_parent->name : '';
			if ( '' === $_slug || '' === $_name ) { continue; }
			$topic_items[] = array(
				'icon'  => ( isset( $_parent->icon ) && '' !== (string) $_parent->icon ) ? (string) $_parent->icon : '📖',
				'label' => $_name,
				'url'   => '/' . $_slug . '/',
				'count' => 0,
			);
		}
	}
	// sidebar_guide_parents shape
	$guide_topics = array(
		'heading' => __( 'Browse Guides', ADN_TEXT_DOMAIN ),
		'items'   => $topic_items,
	);

	/* ── Newsletter → sidebar_newsletter_signup shape ───────────────── */
	$newsletter_cta = array(
		'heading'      => __( 'Stay Updated', ADN_TEXT_DOMAIN ),
		'description'  => __( 'Get the latest property guides and expert tips delivered to your inbox.', ADN_TEXT_DOMAIN ),
		'placeholder'  => __( 'Your email address', ADN_TEXT_DOMAIN ),
		'button_label' => __( 'Subscribe', ADN_TEXT_DOMAIN ),
		'note'         => __( 'No spam. Unsubscribe anytime.', ADN_TEXT_DOMAIN ),
	);

	return array(
		'contact_help'   => $contact_help,
		'latest_news'    => $latest_news,
		'calculators'    => $calculators,
		'guide_topics'   => $guide_topics,
		'newsletter_cta' => $newsletter_cta,
	);
}

function adn_ask_expert_get_context() {
	$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();

	/* ── Hero from admin banner option + WP page title ─────────────── */
	$banner      = get_option( 'adn_expert_banner', array() );
	$hero_title  = ( ! empty( $banner['heading'] ) )
		? (string) $banner['heading']
		: ( get_the_title() ?: __( 'Ask an Expert', ADN_TEXT_DOMAIN ) );
	$hero_desc   = ( ! empty( $banner['info'] ) )
		? (string) $banner['info']
		: __( 'Connect with trusted property professionals who can provide the right advice for your situation.', ADN_TEXT_DOMAIN );

	$hero = array(
		'title'       => $hero_title,
		'description' => $hero_desc,
		'bg_icon'     => '🤝',
	);

	/* ── Breadcrumb ─────────────────────────────────────────────────── */
	$breadcrumb = array(
		array( 'label' => __( 'Home', ADN_TEXT_DOMAIN ),                                           'url' => home_url( '/' ) ),
		array( 'label' => get_the_title() ?: __( 'Ask an Expert', ADN_TEXT_DOMAIN ), 'url' => null ),
	);

	/* ── Page meta ──────────────────────────────────────────────────── */
	$meta = array(
		'page_title'       => get_the_title() ?: __( 'Ask an Expert', ADN_TEXT_DOMAIN ),
		'meta_description' => __( 'Connect with vetted UK property professionals - mortgage advisers, solicitors, surveyors, buyer-side agents and more.', ADN_TEXT_DOMAIN ),
	);

	/* ── DB experts ─────────────────────────────────────────────────── */
	$db_experts  = array();
	$use_db      = false;
	if ( class_exists( 'AH_Expert_DB' ) ) {
		$db_rows = AH_Expert_DB::get_all( 'active' );
		if ( ! empty( $db_rows ) ) {
			$use_db = true;
			foreach ( $db_rows as $row ) {
				$photo_id  = isset( $row['photo_id'] ) ? (int) $row['photo_id'] : 0;
				$photo_url = ( $photo_id > 0 ) ? wp_get_attachment_image_url( $photo_id, 'thumbnail' ) : '';
				if ( ! $photo_url ) { $photo_url = ''; }

				$bullets_raw = isset( $row['bullets'] ) ? $row['bullets'] : '';
				$bullets     = array();
				if ( '' !== $bullets_raw ) {
					$dec = json_decode( $bullets_raw, true );
					if ( is_array( $dec ) ) { $bullets = $dec; }
				}

				$slug        = isset( $row['expert_slug'] ) ? (string) $row['expert_slug'] : '';
				$profile_url = $slug ? home_url( '/?ah_expert=' . rawurlencode( $slug ) ) : home_url( '/ask-an-expert/' );

				$db_experts[] = array(
					'slug'          => $slug,
					'photo_url'     => $photo_url,
					'avatar'        => '👤',
					'name'          => isset( $row['name'] )          ? (string) $row['name']          : '',
					'title'         => isset( $row['title'] )         ? (string) $row['title']         : '',
					'category'      => isset( $row['category'] )      ? (string) $row['category']      : '',
					'rating'        => isset( $row['rating'] )        ? (float)  $row['rating']        : 0.0,
					'reviews_count' => isset( $row['reviews_count'] ) ? (int)    $row['reviews_count'] : 0,
					'reviews'       => isset( $row['reviews_count'] ) ? (int)    $row['reviews_count'] : 0,
					'description'   => isset( $row['bio'] )           ? (string) $row['bio']           : '',
					'location'      => isset( $row['location'] )      ? (string) $row['location']      : '',
					'phone'         => isset( $row['phone'] )         ? (string) $row['phone']         : '',
					'email'         => isset( $row['email'] )         ? (string) $row['email']         : '',
					'tags'          => array_slice( $bullets, 0, 3 ),
					'bullets'       => $bullets,
					'url'           => $profile_url,
				);
			}
		}
	}

	/* ── Experts list ───────────────────────────────────────────────── */
	$experts = $db_experts; // Empty array if DB has no active experts - shows "No experts" state.

	/* ── Marquee trust items for hero (replaces static stats bar) ────── */
	$marquee_items = ( ! empty( $banner['marquee_items'] ) && is_array( $banner['marquee_items'] ) )
		? $banner['marquee_items']
		: array();

	if ( empty( $marquee_items ) && $use_db ) {
		$_mq_cat_keys  = array_unique( array_filter( array_column( $db_rows, 'category' ) ) );
		$marquee_items = array(
			array( 'icon' => '🏠', 'label' => count( $db_rows ) . '+',        'note' => __( 'Verified Experts', ADN_TEXT_DOMAIN ) ),
			array( 'icon' => '📋', 'label' => count( $_mq_cat_keys ) . '+',   'note' => __( 'Specialisms', ADN_TEXT_DOMAIN ) ),
			array( 'icon' => '⚡', 'label' => '24h',                           'note' => __( 'Avg Response Time', ADN_TEXT_DOMAIN ) ),
			array( 'icon' => '✅', 'label' => '100%',                          'note' => __( 'Free to Use', ADN_TEXT_DOMAIN ) ),
		);
	}

	$hero['trust_items'] = $marquee_items;
	$stats               = array(); // Marquee is now rendered via page_hero trust_items branch.

	/* ── Category icon map ───────────────────────────────────────────── */
	$_cat_icons = array(
		'all'          => '⭐',
		'mortgage'     => '💰',
		'solicitor'    => '📋',
		'surveyor'     => '🔍',
		'buyer-agent'  => '🏠',
		'removal'      => '🚛',
		'tax'          => '⚖️',
		'conveyancing' => '📜',
		'insurance'    => '🛡️',
		'financial'    => '💎',
		'legal'        => '⚖️',
		'planning'     => '📐',
	);

	/* ── Categories: derived from DB experts ────────────────────────── */
	if ( $use_db ) {
		$db_cat_keys = array();
		foreach ( $db_experts as $_de ) {
			$_ck = isset( $_de['category'] ) ? (string) $_de['category'] : '';
			if ( '' !== $_ck ) { $db_cat_keys[ $_ck ] = true; }
		}
		$categories = array(
			array( 'key' => 'all', 'label' => __( 'All Experts', ADN_TEXT_DOMAIN ), 'icon' => '⭐', 'active' => true ),
		);
		foreach ( array_keys( $db_cat_keys ) as $_dck ) {
			$categories[] = array(
				'key'   => $_dck,
				'label' => ucwords( str_replace( array( '-', '_' ), ' ', $_dck ) ),
				'icon'  => isset( $_cat_icons[ $_dck ] ) ? $_cat_icons[ $_dck ] : '👤',
			);
		}
	} else {
		// Default categories when no DB experts exist yet.
		$categories = array(
			array( 'key' => 'all',         'label' => __( 'All Experts', ADN_TEXT_DOMAIN ),     'icon' => '⭐', 'active' => true ),
			array( 'key' => 'mortgage',    'label' => __( 'Mortgage Advisers', ADN_TEXT_DOMAIN ), 'icon' => '💰' ),
			array( 'key' => 'solicitor',   'label' => __( 'Solicitors', ADN_TEXT_DOMAIN ),        'icon' => '📋' ),
			array( 'key' => 'surveyor',    'label' => __( 'Surveyors', ADN_TEXT_DOMAIN ),         'icon' => '🔍' ),
			array( 'key' => 'buyer-agent', 'label' => __( 'Buyer-side Agents', ADN_TEXT_DOMAIN ), 'icon' => '🏠' ),
		);
	}

	/* ── Sidebar (dynamic) ──────────────────────────────────────────── */
	$sidebar = adn_ask_expert_sidebar_data();

	/* ── Can't-find CTA ─────────────────────────────────────────────── */
	$cant_find_cta = array(
		'icon'         => '🔍',
		'heading'      => __( "Can't find the right expert?", ADN_TEXT_DOMAIN ),
		'desc'         => __( "Tell us what you need and we'll recommend the best expert for your situation.", ADN_TEXT_DOMAIN ),
		'button_label' => __( 'Get Matched Now', ADN_TEXT_DOMAIN ),
		'button_url'   => '/guidance/',
	);

	/* ── Contact nonce for AJAX form ────────────────────────────────── */
	$ajax_url      = admin_url( 'admin-ajax.php' );
	$contact_nonce = wp_create_nonce( 'adn_expert_contact' );

	return array(
		'meta'          => $meta,
		'breadcrumb'    => $breadcrumb,
		'hero'          => $hero,
		'stats'         => $stats,
		'categories'    => $categories,
		'experts'       => $experts,
		'sidebar'       => $sidebar,
		'cant_find_cta' => $cant_find_cta,
		'chrome'        => $chrome,
		'ajax_url'      => $ajax_url,
		'contact_nonce' => $contact_nonce,
	);
}
