<?php
/**
 * Template Name: Static HTML Page
 *
 * Serves raw or themed HTML static pages created via CMS Static Pages Manager (admin.php?page=ah-static-pages).
 * Append ?raw=1 to get the bare HTML (used internally as the iframe src for style isolation or raw printing).
 */
use VintageSoul\Services\Plugins\PageBridgeService;
use VintageSoul\Services\Plugins\StaticPageBridgeService;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

$slug = (string) get_post_field( 'post_name', get_the_ID() );

// 1. Primary Source: HTML stored in database (wp_ah_static_pages) or local file
$html = StaticPageBridgeService::get_html( $slug );

// ── Raw mode ──────────────────────────────────────────────────────────────────
// Returns bare HTML with no WordPress chrome/wrapper.
// Used as the iframe src for complete style isolation, or for direct raw embedding/printing.
if ( isset( $_GET['raw'] ) && '1' === (string) $_GET['raw'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( '' !== $html ) {
		header( 'Content-Type: text/html; charset=UTF-8' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped - raw HTML component by design
	} else {
		status_header( 404 );
		echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Content Not Found</title></head><body><p>Content not found for static page <code>' . esc_html( $slug ) . '</code>.</p></body></html>';
	}
	exit;
}

// ── Themed mode: Header + Background + Master Subpage Hero + Raw Content + Footer ───
get_header();

$page_record = StaticPageBridgeService::get_page( $slug );
$custom_title = $page_record ? (string) ( $page_record->title ?? '' ) : '';
$post_title   = $custom_title ?: get_the_title();

$hero_data = PageBridgeService::resolve_hero(
	$slug ?: (int) get_the_ID(),
	array(
		'id'    => 'static-page-hero-' . get_the_ID(),
		'tag'   => '✦ THE CANE HOUSE ✦',
		'title' => $post_title,
		'sub'   => has_excerpt() ? get_the_excerpt() : 'Official Information & Policy Details',
		'image' => 'assets/images/backgrounds/pure_sugarcane_forest_trees_engraving.jpg',
	)
);
?>
<main id="main" class="main ah-static-page-outer">
	<?php View::component( 'background/parchment-botanical-bg', array( 'seed' => 31 ) ); ?>

	<!-- Master Subpage Hero -->
	<?php View::component( 'subpage-hero/subpage-hero', $hero_data ); ?>

	<div class="section page-standard-section paper-rough">
		<div class="container container--narrow">
			<article <?php post_class( 'page-standard-card ah-static-content-wrapper' ); ?> id="post-<?php the_ID(); ?>">
				<div class="page-standard-content entry-content">
					<?php if ( '' !== $html ) : ?>
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- intentional raw HTML static page
						echo $html;
						?>
					<?php else : ?>
						<div style="padding:40px 24px;text-align:center;color:var(--text-muted, #6b7280);">
							<p><?php esc_html_e( 'No HTML content found for this static page.', 'vintagesoul' ); ?></p>
							<?php if ( current_user_can( 'manage_options' ) ) : ?>
								<p style="margin-top:16px;">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-static-pages&action=edit&edit=' . rawurlencode( $slug ) ) ); ?>" class="btn btn--primary-vintage btn--sm">
										<span><?php esc_html_e( 'Edit in Static Pages Manager', 'vintagesoul' ); ?></span>
									</a>
								</p>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</article>
		</div>
	</div>
</main>
<?php
get_footer();
