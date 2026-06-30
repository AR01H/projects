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
		'heading'      => adn_term( 'sidebar.contact_for_help_heading', 'Contact for Help' ),
		'desc'         => adn_term( 'sidebar.contact_for_help_desc', "Not sure which expert to choose? Get in touch and we'll guide you." ),
		'button_label' => adn_term( 'sidebar.contact_for_help_btn', 'Get in Touch' ),
		'button_url'   => SITE_CONTACT_URL,
	);

	/* ── Latest news → sidebar_news_mini shape ──────────────────────── */
	// Items need: { gradient, title, date, tag, url }
	$news_items = array();
	if ( function_exists( 'adn_cms_newsbar_items' ) && function_exists( 'adn_cms_gradient' ) ) {
		$_ni = 0;
		foreach ( adn_cms_newsbar_items( 3 ) as $np ) {
			if ( empty( $np->text ) ) { continue; }
			$_stamp       = ! empty( $np->start_date ) ? $np->start_date : ( isset( $np->created_at ) ? $np->created_at : '' );
			$news_items[] = array(
				'gradient' => adn_cms_gradient( $_ni ),
				'title'    => (string) $np->text,
				'date'     => $_stamp ? date_i18n( 'M j, Y', strtotime( $_stamp ) ) : '',
				'tag'      => 'NEWS',
				'url'      => function_exists( 'adn_newsbar_item_url' ) ? adn_newsbar_item_url( $np->id ) : '',
			);
			$_ni++;
		}
	}
	// sidebar_news_mini shape
	$latest_news = array(
		'heading'  => SITE_LABEL_LATEST_NEWS,
		'items'    => $news_items,
		'view_all' => array( 'label' => CONTENT_VIEW_ALL_NEWS, 'url' => SITE_NEWS_URL ),
	);

	/* ── Calculators → sidebar_quick_tools shape ────────────────────── */
	// Items need: { icon, label, url }
	$calc_items = array();
	if ( function_exists( 'adn_calculators' ) ) {
		$_tools_page = get_permalink( get_page_by_path( trim( SITE_TOOLS_URL, '/' ) ) ) ?: home_url( SITE_TOOLS_URL );
		$_ci = 0;
		foreach ( adn_calculators() as $_ck => $_calc ) {
			if ( $_ci >= 4 ) { break; }
			$calc_items[] = array(
				'icon'  => isset( $_calc['icon'] )  ? (string) $_calc['icon']  : adn_term( 'icons.tools', '🧮' ),
				'label' => isset( $_calc['title'] ) ? (string) $_calc['title'] : $_ck,
				'url'   => $_tools_page,
			);
			$_ci++;
		}
	}
	// sidebar_quick_tools shape
	$tools = array(
		'heading' => SITE_TOOLS_PLURAL,
		'items'   => $calc_items,
		'cta'     => array(
			'label' => CONTENT_VIEW_ALL_TOOLS,
			'url'   => SITE_CALCULATORS_URL,
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
				'icon'  => ( isset( $_parent->icon ) && '' !== (string) $_parent->icon ) ? (string) $_parent->icon : adn_term( 'icons.guide_parent', '📚' ),
				'label' => $_name,
				'url'   => '/' . $_slug . '/',
				'count' => 0,
			);
		}
	}
	// sidebar_guide_parents shape
	$guide_topics = array(
		'heading' => adn_term( 'sidebar.browse_guides', 'Browse Guides' ),
		'items'   => $topic_items,
	);

	/* ── Newsletter → sidebar_newsletter_signup shape ───────────────── */
	$newsletter_cta = array(
		'heading'      => adn_term( 'sidebar.newsletter_heading', 'Stay Updated' ),
		'description'  => adn_term( 'sidebar.newsletter_desc', 'Get the latest guides and expert tips delivered to your inbox.' ),
		'placeholder'  => adn_term( 'sidebar.newsletter_placeholder', 'Your email address' ),
		'button_label' => adn_term( 'sidebar.newsletter_btn', 'Subscribe' ),
		'note'         => adn_term( 'sidebar.newsletter_note', 'No spam. Unsubscribe anytime.' ),
	);

	return array(
		'contact_help'   => $contact_help,
		'latest_news'    => $latest_news,
		'tools'          => $tools,
		'guide_topics'   => $guide_topics,
		'newsletter_cta' => $newsletter_cta,
	);
}

function adn_ask_expert_get_context() {
	$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();

	/* ── Hero from admin banner option + WP page title ─────────────── */
	$banner      = get_option( 'adn_expert_banner', array() );

	/* ── Unlock state: check cookie against HMAC of stored password ─── */
	$_stored_pw      = isset( $banner['unlock_password'] ) ? (string) $banner['unlock_password'] : '';
	$_expected_token = ( '' !== $_stored_pw ) ? hash_hmac( 'sha256', $_stored_pw, wp_salt( 'secure_auth' ) ) : '';
	$_cookie_val     = isset( $_COOKIE['adn_experts_unlocked'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['adn_experts_unlocked'] ) ) : '';
	$_is_unlocked    = ( '' !== $_expected_token && '' !== $_cookie_val && hash_equals( $_expected_token, $_cookie_val ) );

	$hero_title  = ( ! empty( $banner['heading'] ) )
		? (string) $banner['heading']
		: ( get_the_title() ?: SITE_EXPERT_LABEL );
	$hero_desc   = ( ! empty( $banner['info'] ) )
		? (string) $banner['info']
		: adn_term( 'expert_page.hero_desc_default', 'Connect with trusted professionals who can provide the right advice for your situation.' );

	$hero = array(
		'title'       => $hero_title,
		'description' => $hero_desc,
		'bg_icon'     => adn_term( 'icons.expert_hero', '🤝' ),
	);

	/* ── Breadcrumb ─────────────────────────────────────────────────── */
	$breadcrumb = array(
		array( 'label' => PAGE_TITLE_HOME, 'url' => home_url( '/' ) ),
		array( 'label' => get_the_title() ?: SITE_EXPERT_LABEL, 'url' => null ),
	);

	/* ── Page meta ──────────────────────────────────────────────────── */
	$meta = array(
		'page_title'       => get_the_title() ?: SITE_EXPERT_LABEL,
		'meta_description' => adn_term( 'expert_page.meta_desc_default', 'Connect with vetted UK professionals - mortgage advisers, solicitors, surveyors, buyer-side agents and more.' ),
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
				$profile_url = $slug ? home_url( '/?ah_expert=' . rawurlencode( $slug ) ) : home_url( SITE_EXPERT_URL );

				$_row_locked  = ( isset( $row['is_locked'] ) && $row['is_locked'] ) && ! $_is_unlocked;

				$db_experts[] = array(
					'slug'          => $slug,
					'photo_url'     => $photo_url,
					'avatar'        => adn_term( 'icons.expert_avatar', '👤' ),
					'name'          => isset( $row['name'] )          ? (string) $row['name']          : '',
					'title'         => isset( $row['title'] )         ? (string) $row['title']         : '',
					'category'      => isset( $row['category'] )      ? (string) $row['category']      : '',
					'rating'        => isset( $row['rating'] )        ? (float)  $row['rating']        : 0.0,
					'reviews_count' => isset( $row['reviews_count'] ) ? (int)    $row['reviews_count'] : 0,
					'reviews'       => isset( $row['reviews_count'] ) ? (int)    $row['reviews_count'] : 0,
					'description'   => isset( $row['bio'] )           ? (string) $row['bio']           : '',
					'location'      => isset( $row['location'] )      ? (string) $row['location']      : '',
					'phone'         => '',
					'email'         => '',
					'tags'          => array_slice( $bullets, 0, 3 ),
					'bullets'       => $bullets,
					'url'           => $profile_url,
					'is_locked'     => $_row_locked ? 1 : 0,
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
			array( 'icon' => adn_term( 'icons.trust_verified', '🏠' ), 'label' => count( $db_rows ) . '+',        'note' => adn_term( 'expert_page.trust_verified_experts', 'Verified Experts' ) ),
			array( 'icon' => adn_term( 'icons.trust_specialism', '📋' ), 'label' => count( $_mq_cat_keys ) . '+',   'note' => adn_term( 'expert_page.trust_specialisms', 'Specialisms' ) ),
			array( 'icon' => adn_term( 'icons.trust_time', '⚡' ), 'label' => '24h',                           'note' => adn_term( 'expert_page.trust_avg_response', 'Avg Response Time' ) ),
			array( 'icon' => adn_term( 'icons.trust_free', '✅' ), 'label' => '100%',                          'note' => adn_term( 'expert_page.trust_free_to_use', 'Free to Use' ) ),
		);
	}

	$hero['trust_items'] = $marquee_items;
	$stats               = array(); // Marquee is now rendered via page_hero trust_items branch.

	/* ── Category icon map ───────────────────────────────────────────── */
	$_cat_icons = array(
		'all'          => '⭐',
		'consultant'     => '💰',
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

	/* ── Virtual category tabs (admin-defined teasers) ─────────────── */
	$virtual_cats = ( isset( $banner['virtual_cats'] ) && is_array( $banner['virtual_cats'] ) )
		? $banner['virtual_cats'] : array();

	/* ── Categories: derived from DB experts ────────────────────────── */
	if ( $use_db ) {
		/* Normalize key with sanitize_key() - same function expert_card.php uses on data-cat,
		 * so filter buttons always match card attributes. Deduplicates "Conveyancing" vs "conveyancing". */
		$db_cat_keys = array(); // sanitize_key => original_raw (for display label)
		foreach ( $db_experts as $_de ) {
			$_raw = isset( $_de['category'] ) ? trim( (string) $_de['category'] ) : '';
			if ( '' === $_raw ) { continue; }
			$_nk = sanitize_key( $_raw ); // e.g. "conveyancing"
			if ( ! isset( $db_cat_keys[ $_nk ] ) ) {
				$db_cat_keys[ $_nk ] = $_raw; // keep first-seen original for display
			}
		}
		$categories = array(
			array( 'key' => 'all', 'label' => adn_term( 'expert_page.filter_all_experts', 'All Experts' ), 'icon' => adn_term( 'icons.expert_all', '⭐' ), 'active' => true ),
		);
		foreach ( $db_cat_keys as $_nk => $_orig ) {
			$categories[] = array(
				'key'   => $_nk,
				'label' => ucwords( str_replace( array( '-', '_' ), ' ', $_orig ) ),
				'icon'  => isset( $_cat_icons[ $_nk ] ) ? $_cat_icons[ $_nk ] : ( isset( $_cat_icons[ $_orig ] ) ? $_cat_icons[ $_orig ] : adn_term( 'icons.expert_avatar', '👤' ) ),
			);
		}
	} else {
		// Default categories when no DB experts exist yet.
		$categories = array(
			array( 'key' => 'all', 'label' => adn_term( 'expert_page.filter_all_experts', 'All Experts' ), 'icon' => adn_term( 'icons.expert_all', '⭐' ), 'active' => true ),
		);
	}

	/* Append admin-defined virtual tabs after real categories. */
	foreach ( $virtual_cats as $_vi => $_vc ) {
		if ( empty( $_vc['label'] ) ) { continue; }
		$categories[] = array(
			'key'     => 'vcat-' . $_vi,
			'label'   => (string) $_vc['label'],
			'virtual' => true,
		);
	}

	/* ── Sidebar (dynamic) ──────────────────────────────────────────── */
	$sidebar = adn_ask_expert_sidebar_data();

	/* ── Can't-find CTA ─────────────────────────────────────────────── */
	$cant_find_cta = array(
		'icon'         => adn_term( 'icons.search', '🔍' ),
		'heading'      => adn_term( 'expert_page.cant_find_heading', "Can't find the right expert?" ),
		'desc'         => adn_term( 'expert_page.cant_find_desc', "Tell us what you need and we'll recommend the best expert for your situation." ),
		'button_label' => adn_term( 'expert_page.cant_find_btn', 'Get Matched Now' ),
		'button_url'   => SITE_GUIDANCE_URL,
	);

	/* ── Contact nonce for AJAX form ────────────────────────────────── */
	$ajax_url      = admin_url( 'admin-ajax.php' );
	$contact_nonce = wp_create_nonce( 'adn_expert_contact' );

	/* ── Lock/unlock state ──────────────────────────────────────────── */
	$_has_locked   = false;
	foreach ( $experts as $_ex ) {
		if ( ! empty( $_ex['is_locked'] ) ) { $_has_locked = true; break; }
	}
	$unlock_nonce  = wp_create_nonce( 'adn_expert_unlock' );

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
		'has_locked'    => $_has_locked,
		'is_unlocked'   => $_is_unlocked,
		'unlock_nonce'  => $unlock_nonce,
		'virtual_cats'  => $virtual_cats,
		'latest_news'   => array(
			'heading' => array(
				'title'      => adn_term( 'labels.latest_news', 'Latest News' ),
				'link_label' => adn_term( 'buttons.view_all', 'View all →' ),
				'link_url'   => defined( 'SITE_NEWS_URL' ) ? SITE_NEWS_URL : '/',
			),
			'items' => adn_shared_latest_news_items( 3 ),
		),
	);
}

