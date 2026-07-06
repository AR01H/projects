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

/*
 * Speed: the heavy below-fold sections (banners, news row, tools, guides,
 * resources) are NOT built or rendered here. Their data fetches are skipped
 * (fast first paint) and premium.js lazy-loads each one from
 * /api/v1/fragment/home/{section} as it approaches the viewport. Markup
 * source for those sections: components/sections/home_deferred_section.php.
 */
$ctx = adn_home_get_context( array( 'banners', 'news', 'guides', 'tools' ) );

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

<?php
/* ==================== DEFERRED SECTIONS (AJAX fragments) ====================
 * Each placeholder below is swapped for server-rendered HTML from
 * /api/v1/fragment/home/{section} by premium.js as it nears the viewport.
 * Placeholders are only emitted when the section is enabled (cheap options
 * check); the fragment endpoint re-checks data and returns '' when empty,
 * in which case the placeholder is removed. Markup lives in
 * components/sections/home_deferred_section.php. */

$_home_secs    = get_option( 'adn_home_sections', array() );
$_sp_term_slug = sanitize_key( $_home_secs['spotlight_term'] ?? '' );
$_sp_active    = adn_home_section_visible( 'spotlights' ) && '' !== $_sp_term_slug;

$_deferred = array();
if ( adn_home_section_visible( 'banners' ) )                 { $_deferred[] = 'banners'; }
if ( adn_home_section_visible( 'news' ) || $_sp_active )     { $_deferred[] = 'news_row'; }
if ( adn_home_section_visible( 'calculators' ) )             { $_deferred[] = 'tools'; }
if ( adn_home_section_visible( 'guides' ) )                  { $_deferred[] = 'guides'; }
$_deferred[] = 'resources'; // data check happens in the fragment
?>
<?php foreach ( $_deferred as $_df ) : ?>
<div class="adn-defer"
     data-fragment="<?php echo esc_attr( $_df ); ?>"
     data-endpoint="<?php echo esc_url( rest_url( ADN_API_NS . '/fragment/home/' . $_df ) ); ?>"
     aria-busy="true">
	<div class="container">
		<div class="adn-defer-skel" aria-hidden="true">
			<span class="adn-defer-line adn-defer-line--head"></span>
			<span class="adn-defer-line"></span>
			<span class="adn-defer-line adn-defer-line--short"></span>
		</div>
	</div>
</div>
<?php endforeach; ?>

<?php /* ============================== NEWSLETTER ============================== */ ?>
<?php if ( adn_home_section_visible( 'newsletter' ) ) : ?>
<section class="newsletter-cta">
	<div class="container">
		<?php adn_component( 'sections/newsletter_cta', array( 'newsletter' => $ctx['newsletter'] ) ); ?>
	</div>
</section>
<?php endif; ?>

<?php adn_page_close( $ctx ); ?>
