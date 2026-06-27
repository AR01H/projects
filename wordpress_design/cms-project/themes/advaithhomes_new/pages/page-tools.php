<?php
/**
 * Template Name: Tools
 *
 * pages/page-tools.php - All Tools listing page.
 *
 * All content comes from the calculator registry via the service layer.
 * No content is hardcoded here - only structure.
 *
 * Architecture:
 *   adn_calculators() registry
 *     → intermediate/page_tools_logical.php  adn_calculators_get_context()
 *       → components/sections/tools_hero.php
 *       → components/sections/tools_trust_bar.php
 *       → components/sections/tools_search_bar.php
 *       → components/sections/tools_popular.php
 *       → components/sections/tools_all.php
 *       → components/parts/tools_sidebar.php
 *       → components/sections/newsletter_cta.php
 *
 * RULE: No hardcoded content and no data reads here - only structure.
 * RULE: Header/footer come from header.php / footer.php via get_header() / get_footer().
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/page_tools_logical.php';
$ctx = adn_calculators_get_context();

adn_seo_register( array(
	'title'       => isset( $ctx['hero']['title'] )       ? (string) $ctx['hero']['title']       : '',
	'description' => isset( $ctx['hero']['description'] ) ? wp_strip_all_tags( (string) $ctx['hero']['description'] ) : '',
	'canonical'   => defined( 'SITE_CALCULATORS_URL' ) ? home_url( SITE_CALCULATORS_URL ) : '',
	'breadcrumb'  => isset( $ctx['breadcrumb'] )          ? $ctx['breadcrumb']                   : array(),
) );

// Breadcrumb renders inside the hero banner - suppress from adn_page_open.
$_open_ctx               = $ctx;
$_open_ctx['breadcrumb'] = array();
adn_page_open( $_open_ctx );
?>

<?php /* ============================== HERO ============================== */ ?>
<?php if ( ! empty( $ctx['hero'] ) ) : ?>
	<?php adn_component( 'sections/page_hero', array(
		'hero'       => $ctx['hero'],
		'breadcrumb' => $ctx['breadcrumb'],
	) ); ?>
<?php endif; ?>

<?php /* ============================== TRUST BAR ============================== */ ?>
<?php if ( ! empty( $ctx['trust_items'] ) ) : ?>
	<?php adn_component( 'sections/tools_trust_bar', array( 'trust_items' => $ctx['trust_items'] ) ); ?>
<?php endif; ?>

<?php /* ============================== SEARCH BAR ============================== */ ?>
<!-- <?php adn_component( 'sections/tools_search_bar', array( 'search' => $ctx['search'] ) ); ?> -->

<?php /* ============================== MAIN LAYOUT: SIDEBAR + CONTENT ============================== */ ?>
<div class="tools-main-layout">

	<?php /* LEFT SIDEBAR - category nav + help CTA */ ?>
	<?php if ( ! empty( $ctx['sidebar'] ) ) : ?>
		<?php adn_component( 'parts/tools_sidebar', array( 'sidebar' => $ctx['sidebar'] ) ); ?>
	<?php endif; ?>

	<?php /* MAIN - popular grid + all tools list */ ?>
	<main>

		<?php if ( ! empty( $ctx['popular_tools'] ) ) : ?>
			<?php adn_component( 'sections/tools_popular', array( 'popular_tools' => $ctx['popular_tools'] ) ); ?>
		<?php endif; ?>

		<?php if ( ! empty( $ctx['all_tools'] ) ) : ?>
			<?php adn_component( 'sections/tools_all', array(
				'filter_tabs' => $ctx['filter_tabs'],
				'all_tools'   => $ctx['all_tools'],
				'find_cta'    => $ctx['find_cta'],
			) ); ?>
		<?php endif; ?>

	</main>

</div>

<?php if ( ! empty( $ctx['news']['items'] ) || ! empty( $ctx['regulations']['items'] ) || ! empty( $ctx['hot_topics']['items'] ) ) : ?>
<section class="cat-highlight-section">
	<div class="container">
		<?php adn_component( 'sections/news_three_col', array(
			'news'        => $ctx['news'],
			'regulations' => $ctx['regulations'],
			'hot_topics'  => $ctx['hot_topics'],
		) ); ?>
	</div>
</section>
<?php endif; ?>

<?php /* ============================== NEWSLETTER ============================== */ ?>
<?php if ( ! empty( $ctx['newsletter'] ) ) : ?>
<section class="newsletter-cta">
	<div class="container">
		<?php adn_component( 'sections/newsletter_cta', array( 'newsletter' => $ctx['newsletter'] ) ); ?>
	</div>
</section>
<?php endif; ?>

<?php
$_fi_tools     = get_option( 'adn_calculators_general', array() );
$_fi_tools_sec = ( is_array( $_fi_tools ) && ! empty( $_fi_tools['featured_in_section'] ) )
	? sanitize_key( $_fi_tools['featured_in_section'] ) : '';
adn_component( 'parts/featured_in', array( 'section' => $_fi_tools_sec ) );
?>

<?php adn_page_close( $ctx ); ?>

