<?php
/**
 * Template Name: Home
 *
 * pages/page-home.php - Home page container.
 *
 * Architecture (mirrors some_styles/new_advaithhomes_design/index.html):
 *   data/json/home_page.json + site_chrome.json   (content - the mock API response)
 *     → apis/services.php                          (adn_service_home_data / adn_service_site_chrome)
 *       → intermediate/page_home_logical.php       (adn_home_get_context - defaults + shaping)
 *         → THIS FILE                              (section wrappers + classes only)
 *           → components/sections/* + parts/*      (markup, receives data via props)
 *             → components/cards/*                 (repeated items)
 *
 * RULE: No hardcoded content and no data reads here - only structure.
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/page_home_logical.php';
$ctx = adn_home_get_context();

wp_enqueue_style( 'adn-resources', get_template_directory_uri() . '/assets/css/resources.css', array(), ADN_THEME_VERSION );

adn_seo_register( array(
	'description' => isset( $ctx['hero']['description'] ) ? (string) $ctx['hero']['description'] : get_bloginfo( 'description' ),
	'image'       => isset( $ctx['hero']['image'] )       ? (string) $ctx['hero']['image']       : '',
) );

adn_page_open( $ctx );
?>

<?php /* ============================== HERO ============================== */ ?>
<?php if ( adn_home_section_visible( 'hero' ) ) : ?>
<section class="hero-home">
	<div class="container">
		<?php adn_component( 'sections/hero_home', array( 'hero' => $ctx['hero'] ) ); ?>
	</div>
</section>
<?php /* Mobile-only: diagram shown below the hero (hidden on desktop via CSS) */ ?>
<div class="hero-diagram-mobile">
	<div class="container">
		<?php adn_component( 'sections/hero_home_diagram', array( 'diagram' => $ctx['hero']['diagram'] ?? array() ) ); ?>
	</div>
</div>
<?php endif; ?>

<?php if ( ! empty( $ctx['hero']['trust_items'] ) ) {
		$_trust = $ctx['hero']['trust_items'];
		$_first     = reset( $_trust );
		$_is_string = is_string( $_first );
		$_is_icon   = ! $_is_string && is_array( $_first ) && isset( $_first['icon'] );

		get_template_part( 'components/marque_scroll/point_marque', null, [
			'trust'     => $_trust,
			'is_string' => $_is_string,
			'is_icon'   => $_is_icon,
		] );
	}
?>

<?php /* ==================== WHERE ARE YOU IN YOUR JOURNEY ==================== */ ?>
<?php if ( adn_home_section_visible( 'journey' ) ) : ?>
<section class="journey-section">
	<div class="container">
		<?php
		adn_component( 'parts/section_headers/section_header', array(
			'heading'       => $ctx['journey']['heading'],
			'wrapper_class' => 'journey-title',
			'underline'     => true,
		) );
		adn_component( 'sections/journey', array( 'cards' => $ctx['journey']['cards'] ) );
		?>
	</div>
</section>
<?php endif; ?>

<?php /* ============================== FEATURED IN (below journey cards) ============================== */ ?>
<?php
$_fi_home     = get_option( 'adn_home_sections', array() );
$_fi_home_sec = ( is_array( $_fi_home ) && ! empty( $_fi_home['featured_in_section'] ) )
	? sanitize_key( $_fi_home['featured_in_section'] ) : '';
adn_component( 'parts/featured_in', array( 'section' => $_fi_home_sec ) );
?>

<?php /* ==================== HOME BANNERS CAROUSEL ==================== */ ?>
<?php if ( adn_home_section_visible( 'banners' ) && ! empty( $ctx['banners']['items'] ) ) : ?>
<section class="banners-promo-section">
	<?php adn_component( 'sections/home_banners_carousel', array(
		'items'    => $ctx['banners']['items'],
		'autoplay' => class_exists( 'AH_Banners_Helper' ) ? AH_Banners_Helper::get_autoplay() : 5000,
	) ); ?>
</section>
<?php endif; ?>

<?php
/* Resolve spotlight term once - used inside news row */
$_home_secs    = get_option( 'adn_home_sections', array() );
$_sp_term_slug = sanitize_key( $_home_secs['spotlight_term'] ?? '' );
$_sp_active    = adn_home_section_visible( 'spotlights' ) && '' !== $_sp_term_slug;
?>

<?php /* ==================== NEWS + REGULATIONS + HOT TOPICS + SPOTLIGHTS ==================== */ ?>
<?php
$_has_news_data = ! empty( $ctx['news']['items'] )
	|| ! empty( $ctx['regulations']['items'] )
	|| ! empty( $ctx['hot_topics']['items'] );
?>
<?php if ( ( adn_home_section_visible( 'news' ) && $_has_news_data ) || $_sp_active ) : ?>
<section class="news-three-col<?php echo $_sp_active ? ' news-three-col--has-sp' : ''; ?>">
	<div class="container">
		<?php adn_component( 'parts/section_headers/section_header', array(
			'heading' => array( 'title' => adn_term( 'labels.news_section', '' ) ),
			'center'  => true,
		) ); ?>
		<div class="news-sp-row">
			<?php if ( adn_home_section_visible( 'news' ) && $_has_news_data ) : ?>
			<div class="news-sp-row__news">
				<?php adn_component( 'sections/news_three_col', array(
					'news'        => $ctx['news'],
					'regulations' => $ctx['regulations'],
					'hot_topics'  => $ctx['hot_topics'],
				) ); ?>
			</div>
			<?php endif; ?>
			<?php if ( $_sp_active ) : ?>
			<div class="news-sp-row__spotlight">
				<?php adn_component( 'parts/spotlights_widget', array( 'term_slug' => $_sp_term_slug ) ); ?>
			</div>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php /* ============================== TOOLS ============================== */ ?>
<?php if ( adn_home_section_visible( 'calculators' ) && ! empty( $ctx['tools']['items'] ) ) : ?>
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
<?php endif; ?>

<?php /* ============================== GUIDES & INSIGHTS ============================== */ ?>
<?php if ( adn_home_section_visible( 'guides' ) ) : ?>
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
<?php endif; ?>

<?php /* ============================== RESOURCES ============================== */ ?>
<?php
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
	$_home_res_items = $wpdb->get_results( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		"SELECT * FROM `{$_hr_table}` WHERE id IN ({$_hr_id_in}) AND status = 'active' ORDER BY FIELD(id, {$_hr_id_in})"
	) ?: array();
}
?>
<?php if ( ! empty( $_home_res_items ) ) : ?>
<section class="home-resources-section">
	<div class="container">
		<?php adn_component( 'sections/category_resources', array(
			'resources' => array( 'items' => $_home_res_items, 'heading' => $_home_res_heading ),
		) ); ?>
	</div>
</section>
<?php endif; ?>

<?php /* ============================== NEWSLETTER ============================== */ ?>
<?php if ( adn_home_section_visible( 'newsletter' ) ) : ?>
<section class="newsletter-cta">
	<div class="container">
		<?php adn_component( 'sections/newsletter_cta', array( 'newsletter' => $ctx['newsletter'] ) ); ?>
	</div>
</section>
<?php endif; ?>

<?php adn_page_close( $ctx ); ?>
