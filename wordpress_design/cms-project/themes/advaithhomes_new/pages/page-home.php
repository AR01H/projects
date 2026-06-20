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

adn_page_open( $ctx );
?>

<?php /* ============================== HERO ============================== */ ?>
<?php if ( adn_home_section_visible( 'hero' ) ) : ?>
<section class="hero-home">
	<div class="container">
		<?php adn_component( 'sections/hero_home', array( 'hero' => $ctx['hero'] ) ); ?>
	</div>
</section>
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

<?php
/* Resolve spotlight term once — used inside news row */
$_home_secs    = get_option( 'adn_home_sections', array() );
$_sp_term_slug = sanitize_key( $_home_secs['spotlight_term'] ?? '' );
$_sp_active    = adn_home_section_visible( 'spotlights' ) && '' !== $_sp_term_slug;
?>

<?php /* ==================== NEWS + REGULATIONS + HOT TOPICS + SPOTLIGHTS ==================== */ ?>
<?php if ( adn_home_section_visible( 'news' ) || $_sp_active ) : ?>
<section class="news-three-col<?php echo $_sp_active ? ' news-three-col--has-sp' : ''; ?>">
	<div class="container">
		<?php adn_component( 'parts/section_headers/section_header', array(
			'heading' => array( 'title' => adn_term( 'labels.news_section', '' ) ),
			'center'  => true,
		) ); ?>
		<div class="news-sp-row">
			<?php if ( adn_home_section_visible( 'news' ) ) : ?>
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
<?php if ( adn_home_section_visible( 'calculators' ) ) : ?>
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

<?php /* ============================== NEWSLETTER ============================== */ ?>
<?php if ( adn_home_section_visible( 'newsletter' ) ) : ?>
<section class="newsletter-cta">
	<div class="container">
		<?php adn_component( 'sections/newsletter_cta', array( 'newsletter' => $ctx['newsletter'] ) ); ?>
	</div>
</section>
<?php endif; ?>

<?php adn_page_close( $ctx ); ?>
