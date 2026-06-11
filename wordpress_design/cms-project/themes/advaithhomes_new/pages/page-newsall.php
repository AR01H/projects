<?php
/**
 * Template Name: News & Insights
 *
 * pages/page-newsall.php — News listing page.
 *
 * All content comes from data/json/news.json via the service layer.
 * No content is hardcoded here — only structure.
 *
 * Architecture:
 *   data/json/news.json
 *     → apis/services.php  adn_service_news_data()
 *       → intermediate/page_news_logical.php  adn_news_get_context()
 *         → THIS FILE  (structure only)
 *
 * RULE: No hardcoded content and no data reads here — only structure.
 * RULE: Header/footer come from header.php / footer.php via get_header() / get_footer().
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/page_news_logical.php';
$ctx = adn_news_get_context();

get_header();
?>

<?php adn_component( 'parts/main_header', array( 'chrome' => $ctx['chrome'] ) ); ?>

<?php /* ============================== BREADCRUMB ============================== */ ?>
<?php if ( ! empty( $ctx['breadcrumb'] ) ) : ?>
	<?php adn_component( 'parts/breadcrumb', array( 'items' => $ctx['breadcrumb'] ) ); ?>
<?php endif; ?>

<?php /* ============================== HERO ============================== */ ?>
<?php if ( ! empty( $ctx['hero'] ) ) : ?>
<section class="news-hero">
	<?php adn_component( 'sections/news_hero', array( 'hero' => $ctx['hero'] ) ); ?>
</section>
<?php endif; ?>

<?php /* ============================== CATEGORY TABS ============================== */ ?>
<?php if ( ! empty( $ctx['categories'] ) ) : ?>
	<?php adn_component( 'sections/news_cats_strip', array( 'categories' => $ctx['categories'] ) ); ?>
<?php endif; ?>

<?php /* ============================== MAIN LAYOUT ============================== */ ?>
<div class="news-layout">

	<main class="news-main" id="newsMain">

		<?php /* FEATURED */ ?>
		<?php if ( ! empty( $ctx['featured'] ) ) : ?>
			<?php adn_component( 'sections/news_featured', array( 'featured' => $ctx['featured'] ) ); ?>
		<?php endif; ?>

		<?php /* SECTIONS (grid / list) */ ?>
		<?php if ( ! empty( $ctx['sections'] ) ) : ?>
			<?php foreach ( $ctx['sections'] as $sec ) : ?>
				<?php adn_component( 'sections/news_section', array( 'section' => $sec ) ); ?>
			<?php endforeach; ?>
		<?php endif; ?>

		<div class="load-more-wrap">
			<button class="load-more-btn" id="loadMoreBtn" type="button">
				<?php echo esc_html__( 'Load More Stories', ADN_TEXT_DOMAIN ); ?>
			</button>
		</div>

	</main>

	<aside class="news-sidebar">
		<?php adn_component( 'parts/sidebar_news_search' ); ?>

		<?php if ( ! empty( $ctx['sidebar']['trending'] ) ) : ?>
			<?php adn_component( 'parts/sidebar_trending', array( 'trending' => $ctx['sidebar']['trending'] ) ); ?>
		<?php endif; ?>

		<?php if ( ! empty( $ctx['sidebar']['market_snapshot'] ) ) : ?>
			<?php adn_component( 'parts/sidebar_market_snapshot', array( 'market_snapshot' => $ctx['sidebar']['market_snapshot'] ) ); ?>
		<?php endif; ?>

		<?php if ( ! empty( $ctx['categories'] ) ) : ?>
			<?php adn_component( 'parts/sidebar_browse_cats', array( 'categories' => $ctx['categories'] ) ); ?>
		<?php endif; ?>

		<?php if ( ! empty( $ctx['sidebar']['newsletter'] ) ) : ?>
			<?php adn_component( 'parts/sidebar_newsletter_signup', array( 'newsletter' => $ctx['sidebar']['newsletter'] ) ); ?>
		<?php endif; ?>
	</aside>

</div>

<?php /* ============================== BOTTOM NEWSLETTER BANNER ============================== */ ?>
<?php if ( ! empty( $ctx['bottom_newsletter'] ) ) : ?>
<section class="newsletter-cta">
	<div class="container">
		<?php adn_component( 'sections/newsletter_cta', array( 'newsletter' => $ctx['bottom_newsletter'] ) ); ?>
	</div>
</section>
<?php endif; ?>

<?php /* ============================== FOOTER ============================== */ ?>
<?php
adn_component( 'parts/pre_footer' );
adn_component( 'parts/main_footer', array( 'footer' => isset( $ctx['chrome']['footer'] ) ? $ctx['chrome']['footer'] : array() ) );
adn_component( 'parts/post_footer' );
adn_component( 'parts/post_footer_notice' );

get_footer();
?>
