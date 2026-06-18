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

<?php adn_page_close( $ctx ); ?>

