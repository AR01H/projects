<?php
/**
 * Template Name: Home
 *
 * pages/page-home.php — Home page container.
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

get_header();

?><?php /* ============================== HEADER ============================== */ ?>
<?php adn_component( 'parts/main_header', array( 'chrome' => $ctx['chrome'] ) ); ?>

<?php /* ============================== HERO ============================== */ ?>
<?php if ( adn_home_section_visible( 'hero' ) ) : ?>
<section class="hero-home">
	<div class="container">
		<?php adn_component( 'sections/hero_home', array( 'hero' => $ctx['hero'] ) ); ?>
	</div>
</section>
<?php endif; ?>

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

<?php /* ==================== NEWS + REGULATIONS + HOT TOPICS ==================== */ ?>
<?php if ( adn_home_section_visible( 'news' ) ) : ?>
<section class="news-three-col">
	<div class="container">
		<?php
		adn_component( 'sections/news_three_col', array(
			'news'        => $ctx['news'],
			'regulations' => $ctx['regulations'],
			'hot_topics'  => $ctx['hot_topics'],
		) );
		?>
	</div>
</section>
<?php endif; ?>

<?php /* ============================== CALCULATORS ============================== */ ?>
<?php if ( adn_home_section_visible( 'calculators' ) ) : ?>
<section class="calculators-section">
	<div class="container">
		<?php
		adn_component( 'parts/section_headers/section_header', array(
			'heading' => $ctx['calculators']['heading'],
		) );
		adn_component( 'sections/calculators', array( 'items' => $ctx['calculators']['items'] ) );
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

<?php /* ============================== FOOTER ============================== */ ?>
<?php
adn_component( 'parts/pre_footer' );
adn_component( 'parts/main_footer', array( 'footer' => isset( $ctx['chrome']['footer'] ) ? $ctx['chrome']['footer'] : array() ) );
adn_component( 'parts/post_footer' );
adn_component( 'parts/post_footer_notice' );
?>

<?php get_footer(); ?>
