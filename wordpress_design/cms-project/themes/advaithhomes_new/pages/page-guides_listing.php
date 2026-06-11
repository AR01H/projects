<?php
/**
 * Template Name: Guides Listing
 *
 * pages/page-guides_listing.php — Guides directory / listing page.
 *
 * Works for any category's guide index: /buying-guides/, /selling-guides/, etc.
 * The page slug drives which JSON is loaded — no content is hardcoded here.
 *
 * Architecture:
 *   data/json/{slug}.json  (e.g. buying-guides.json)
 *     → apis/services.php  adn_service_guides_listing_data($slug)
 *       → intermediate/page_guides_listing_logical.php  adn_guides_listing_get_context()
 *         → THIS FILE  (structure only)
 *
 * RULE: No hardcoded content and no data reads here — only structure.
 * RULE: Header/footer come from header.php / footer.php via get_header() / get_footer().
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/page_guides_listing_logical.php';
$ctx = adn_guides_listing_get_context();

get_header();
?>

<?php adn_component( 'parts/main_header', array( 'chrome' => $ctx['chrome'] ) ); ?>

<?php /* ============================== BREADCRUMB ============================== */ ?>
<?php if ( ! empty( $ctx['breadcrumb'] ) ) : ?>
	<?php adn_component( 'parts/breadcrumb', array( 'items' => $ctx['breadcrumb'] ) ); ?>
<?php endif; ?>

<?php /* ============================== HERO ============================== */ ?>
<?php if ( ! empty( $ctx['hero'] ) ) : ?>
	<?php adn_component( 'sections/guides_hero', array( 'hero' => $ctx['hero'] ) ); ?>
<?php endif; ?>

<?php /* ============================== LISTING: SIDEBAR + MAIN ============================== */ ?>
<div class="container">
	<div class="guides-listing-inner">

		<?php /* LEFT SIDEBAR — categories, level/format filters, help CTA */ ?>
		<?php if ( ! empty( $ctx['sidebar'] ) ) : ?>
			<?php adn_component( 'parts/guides_sidebar_filter', array( 'sidebar' => $ctx['sidebar'] ) ); ?>
		<?php endif; ?>

		<?php /* MAIN — toolbar, cards grid, pagination, download CTA */ ?>
		<?php if ( ! empty( $ctx['guides'] ) ) : ?>
			<?php adn_component( 'sections/guides_grid', array( 'guides' => $ctx['guides'] ) ); ?>
		<?php endif; ?>

	</div>
</div>

<?php /* ============================== FOOTER ============================== */ ?>
<?php
adn_component( 'parts/pre_footer' );
adn_component( 'parts/main_footer', array( 'footer' => isset( $ctx['chrome']['footer'] ) ? $ctx['chrome']['footer'] : array() ) );
adn_component( 'parts/post_footer' );
adn_component( 'parts/post_footer_notice' );

get_footer();
?>
