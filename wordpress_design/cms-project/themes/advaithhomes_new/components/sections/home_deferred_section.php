<?php
/**
 * components/sections/home_deferred_section.php
 *
 * Single markup source for the home page sections that load lazily via the
 * /api/v1/home/section/{section} endpoint (see ADN_Theme_Rest_Routes).
 * The markup here is exactly what page-home.php used to render inline; the
 * page now emits skeleton placeholders and premium.js swaps in this HTML.
 *
 * Props:
 *   $section string  one of: banners | news_row | tools | guides | resources
 *   $ctx     array   full home context from adn_home_get_context()
 *
 * Renders nothing (empty output) when the section is disabled or has no data —
 * the fragment loader removes the placeholder in that case.
 */

defined( 'ABSPATH' ) || exit;

$section = isset( $section ) ? sanitize_key( $section ) : '';
$ctx     = isset( $ctx ) && is_array( $ctx ) ? $ctx : array();

switch ( $section ) {

	/* ==================== HOME BANNERS CAROUSEL ==================== */
	case 'banners':
		if ( adn_home_section_visible( 'banners' ) && ! empty( $ctx['banners']['items'] ) ) : ?>
			<section class="banners-promo-section">
				<?php adn_component( 'sections/home_banners_carousel', array(
					'items'    => $ctx['banners']['items'],
					'autoplay' => class_exists( 'AH_Banners_Helper' ) ? AH_Banners_Helper::get_autoplay() : 5000,
				) ); ?>
			</section>
		<?php endif;
		break;

	/* ========== NEWS + REGULATIONS + HOT TOPICS + SPOTLIGHTS ========== */
	case 'news_row':
		$_home_secs    = get_option( 'adn_home_sections', array() );
		$_sp_term_slug = sanitize_key( $_home_secs['spotlight_term'] ?? '' );
		$_sp_active    = adn_home_section_visible( 'spotlights' ) && '' !== $_sp_term_slug;

		$_has_news_data = ! empty( $ctx['news']['items'] )
			|| ! empty( $ctx['regulations']['items'] )
			|| ! empty( $ctx['hot_topics']['items'] );

		if ( ( adn_home_section_visible( 'news' ) && $_has_news_data ) || $_sp_active ) : ?>
			<section class="news-three-col<?php echo $_sp_active ? ' news-three-col--has-sp' : ''; ?>">
				<div class="container">
					<?php adn_component( 'parts/section_headers/section_header', array(
						'heading' => array( 'title' => adn_term( 'labels.news_section', '' ) ),
						'center'  => true,
					) ); ?>
					<?php if ( $_sp_active ) : ?>
					<div class="news-spotlight-upside" style="margin-bottom: 24px;">
						<?php adn_component( 'parts/spotlights_widget', array( 'term_slug' => $_sp_term_slug, 'compact' => true ) ); ?>
					</div>
					<?php endif; ?>
					
					<div class="news-sp-row <?php echo ( adn_home_section_visible( 'news' ) && $_has_news_data ) ? 'news-sp-row--4col' : ''; ?>">
						<?php if ( adn_home_section_visible( 'news' ) && $_has_news_data ) : ?>
						<div class="news-sp-row__news" style="flex: 1; min-width: 100%;">
							<?php adn_component( 'sections/news_three_col', array(
								'news'         => $ctx['news'],
								'regulations'  => $ctx['regulations'],
								'hot_topics'   => $ctx['hot_topics'],
								'is_home_news' => true,
							) ); ?>
						</div>
						<?php endif; ?>
					</div>
				</div>
			</section>
		<?php endif;
		break;

	/* ============================== TOOLS ============================== */
	case 'tools':
		if ( adn_home_section_visible( 'calculators' ) && ! empty( $ctx['tools']['items'] ) ) : ?>
			<section class="tools-section">
				<div class="container">
					<?php
					adn_component( 'parts/section_headers/section_header', array(
						'heading' => $ctx['tools']['heading'],
					) );
					adn_component( 'sections/tools', array( 'items' => $ctx['tools']['items'] ) );
					?>
				</div>
			</section>
		<?php endif;
		break;

	/* ========================= GUIDES & INSIGHTS ========================= */
	case 'guides':
		if ( adn_home_section_visible( 'guides' ) ) : ?>
			<section class="guides-section">
				<div class="container">
					<?php
					adn_component( 'parts/section_headers/section_header', array(
						'heading' => $ctx['guides']['heading'],
					) );
					adn_component( 'sections/guides', array( 'items' => $ctx['guides']['items'] ) );
					?>
				</div>
			</section>
		<?php endif;
		break;

	/* ============================== RESOURCES ============================== */
	case 'resources':
		$_home_res_opt     = get_option( 'adn_home_resources', array() );
		$_home_res_ids     = ( isset( $_home_res_opt['library_ids'] ) && is_array( $_home_res_opt['library_ids'] ) )
			? array_filter( array_map( 'absint', $_home_res_opt['library_ids'] ) )
			: array();
		$_home_res_heading = isset( $_home_res_opt['heading'] ) && '' !== $_home_res_opt['heading']
			? (string) $_home_res_opt['heading'] : '';
		$_home_res_items   = array();
		if ( ! empty( $_home_res_ids ) && class_exists( 'AH_Resources_Model' ) ) {
			global $wpdb;
			$_hr_table       = $wpdb->prefix . 'ah_resources';
			$_hr_id_in       = implode( ',', array_map( 'intval', $_home_res_ids ) );
			$_home_res_items = $wpdb->get_results( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- IDs are absint()ed above
				"SELECT * FROM `{$_hr_table}` WHERE id IN ({$_hr_id_in}) AND status = 'active' ORDER BY FIELD(id, {$_hr_id_in})"
			) ?: array();
		}
		if ( ! empty( $_home_res_items ) ) : ?>
			<section class="home-resources-section">
				<div class="container">
					<?php adn_component( 'sections/category_resources', array(
						'resources' => array( 'items' => $_home_res_items, 'heading' => $_home_res_heading ),
					) ); ?>
				</div>
			</section>
		<?php endif;
		break;
}
