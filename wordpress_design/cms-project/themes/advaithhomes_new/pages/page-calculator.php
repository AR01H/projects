<?php
/**
 * Template Name: Calculators
 *
 * pages/page-calculator.php — All Calculators listing page.
 *
 * All content comes from data/json/calculators.json via the service layer.
 * No content is hardcoded here — only structure.
 *
 * Architecture:
 *   data/json/calculators.json
 *     → apis/services.php  adn_service_calculators_data()
 *       → intermediate/page_calculators_logical.php  adn_calculators_get_context()
 *         → THIS FILE  (structure only)
 *
 * RULE: No hardcoded content and no data reads here — only structure.
 * RULE: Header/footer come from header.php / footer.php via get_header() / get_footer().
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/page_calculators_logical.php';
$ctx = adn_calculators_get_context();

get_header();
?>

<?php adn_component( 'parts/main_header', array( 'chrome' => $ctx['chrome'] ) ); ?>

<?php /* ============================== BREADCRUMB ============================== */ ?>
<?php if ( ! empty( $ctx['breadcrumb'] ) ) : ?>
	<?php adn_component( 'parts/breadcrumb', array( 'items' => $ctx['breadcrumb'] ) ); ?>
<?php endif; ?>

<?php /* ============================== HERO ============================== */ ?>
<?php if ( ! empty( $ctx['hero'] ) ) : ?>
	<?php adn_component( 'sections/calcs_hero', array( 'hero' => $ctx['hero'] ) ); ?>
<?php endif; ?>

<?php /* ============================== TRUST BAR ============================== */ ?>
<?php if ( ! empty( $ctx['trust_items'] ) ) : ?>
	<?php adn_component( 'sections/calcs_trust_bar', array( 'trust_items' => $ctx['trust_items'] ) ); ?>
<?php endif; ?>

<?php /* ============================== SEARCH BAR ============================== */ ?>
<?php adn_component( 'sections/calcs_search_bar', array( 'search' => $ctx['search'] ) ); ?>

<?php /* ============================== MAIN LAYOUT: SIDEBAR + CONTENT ============================== */ ?>
<div class="calcs-main-layout">

	<?php /* LEFT SIDEBAR — category nav + help CTA */ ?>
	<?php if ( ! empty( $ctx['sidebar'] ) ) : ?>
		<?php adn_component( 'parts/calcs_sidebar', array( 'sidebar' => $ctx['sidebar'] ) ); ?>
	<?php endif; ?>

	<?php /* MAIN — popular grid + all calculators list */ ?>
	<main>

		<?php if ( ! empty( $ctx['popular_calcs'] ) ) : ?>
			<?php adn_component( 'sections/calcs_popular', array( 'popular_calcs' => $ctx['popular_calcs'] ) ); ?>
		<?php endif; ?>

		<?php if ( ! empty( $ctx['all_calcs'] ) ) : ?>
			<?php adn_component( 'sections/calcs_all', array(
				'filter_tabs' => $ctx['filter_tabs'],
				'all_calcs'   => $ctx['all_calcs'],
				'find_cta'    => $ctx['find_cta'],
			) ); ?>
		<?php endif; ?>

	</main>

</div>

<?php /* ============================== FOOTER ============================== */ ?>
<?php
adn_component( 'parts/pre_footer' );
adn_component( 'parts/main_footer', array( 'footer' => isset( $ctx['chrome']['footer'] ) ? $ctx['chrome']['footer'] : array() ) );
adn_component( 'parts/post_footer' );
adn_component( 'parts/post_footer_notice' );

get_footer();
?>
