<?php
/**
 * Template Name: Guides Listing
 *
 * pages/page-guides_listing.php - Guides directory / listing page.
 *
 * Works for any category's guide index: /buying-guides/, /selling-guides/, etc.
 * The page slug drives which JSON is loaded - no content is hardcoded here.
 *
 * Architecture:
 *   data/json/{slug}.json  (e.g. buying-guides.json)
 *     → apis/services.php  adn_service_guides_listing_data($slug)
 *       → intermediate/page_guides_listing_logical.php  adn_guides_listing_get_context()
 *         → THIS FILE  (structure only)
 *
 * RULE: No hardcoded content and no data reads here - only structure.
 * RULE: Header/footer come from header.php / footer.php via get_header() / get_footer().
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/page_guides_listing_logical.php';
$ctx = adn_guides_listing_get_context();

adn_seo_register( array(
	'description' => isset( $ctx['meta_description'] ) ? (string) $ctx['meta_description'] : '',
	'title'       => isset( $ctx['hero']['title'] )    ? (string) $ctx['hero']['title']    : '',
) );

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

<?php /* ============================== LISTING: SIDEBAR + MAIN ============================== */ ?>
<div class="container">
	<div class="guides-listing-inner">

		<?php /* LEFT SIDEBAR - categories, level/format filters, help CTA */ ?>
		<?php if ( ! empty( $ctx['sidebar'] ) ) : ?>
			<?php adn_component( 'parts/guides_sidebar_filter', array( 'sidebar' => $ctx['sidebar'] ) ); ?>
		<?php endif; ?>

		<?php /* MAIN - toolbar, cards grid, pagination, download CTA */ ?>
		<?php if ( ! empty( $ctx['guides'] ) ) : ?>
			<?php adn_component( 'sections/guides_grid', array( 'guides' => $ctx['guides'] ) ); ?>
		<?php endif; ?>

	</div>
</div>

<?php /* ============================== BOTTOM QUICK LINKS (4 fixed cards) ============================== */ ?>
<?php
$_bgl = $ctx['bottom_grid']['links'] ?? array();
if ( ! empty( $_bgl ) ) :
?>
<section class="guides-bottom-grid">
	<div class="container">
		<div class="gbg-row">
			<?php foreach ( $_bgl as $_lnk ) :
				$_licon  = isset( $_lnk['icon'] )  ? (string) $_lnk['icon']  : '🔗';
				$_llabel = isset( $_lnk['label'] ) ? (string) $_lnk['label'] : '';
				$_lurl   = isset( $_lnk['url'] )   ? (string) $_lnk['url']   : '#';
				if ( '' === $_llabel ) { continue; }
			?>
			<a href="<?php echo esc_url( adn_link( $_lurl ) ); ?>" class="gbg-tool-card">
				<span class="gbg-tool-icon" aria-hidden="true"><?php echo esc_html( $_licon ); ?></span>
				<span class="gbg-tool-label"><?php echo esc_html( $_llabel ); ?></span>
				<span class="gbg-tool-arrow" aria-hidden="true">›</span>
			</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php /* ============================== NEWSLETTER ============================== */ ?>
<?php if ( ! empty( $ctx['newsletter']['title'] ) ) : ?>
<section class="newsletter-cta">
	<div class="container">
		<?php adn_component( 'sections/newsletter_cta', array( 'newsletter' => $ctx['newsletter'] ) ); ?>
	</div>
</section>
<?php endif; ?>

<?php /* ============================== CTA BANNER ============================== */ ?>
<?php if ( ! empty( $ctx['cta_banner']['title'] ) ) : ?>
<div class="guides-cta-wrap">
	<?php adn_component( 'parts/cta_banner', array( 'cta_banner' => $ctx['cta_banner'] ) ); ?>
</div>
<?php endif; ?>

<?php adn_page_close( $ctx ); ?>
