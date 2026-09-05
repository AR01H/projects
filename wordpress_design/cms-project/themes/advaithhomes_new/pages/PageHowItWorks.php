<?php
/**
 * Template Name: How It Works
 *
 * pages/PageHowItWorks.php - "How It Works" process explainer page.
 *
 * Architecture:
 *   data/json/how-it-works.json
 *     → apis/services.php  adn_service_how_it_works_data()
 *       → src/Feature/HowItWorks/Service/HowItWorksContext.php
 *         → THIS FILE (structure only)
 *
 * RULE: No hardcoded content or data reads here - only structure.
 * RULE: Header/footer come from header.php / footer.php via get_header() / get_footer().
 */

defined( 'ABSPATH' ) || exit;

$ctx = \Adn\Theme\Feature\HowItWorks\Controller\HowItWorksController::getContext();

// Register SEO before get_header() - adn_seo_head_output() runs on wp_head priority 1.
adn_seo_register( array(
	'title'       => isset( $ctx['hero']['title'] )       ? (string) $ctx['hero']['title']       : '',
	'description' => isset( $ctx['hero']['description'] ) ? wp_strip_all_tags( (string) $ctx['hero']['description'] ) : '',
	'canonical'   => defined( 'SITE_HOW_IT_WORKS_URL' ) ? home_url( SITE_HOW_IT_WORKS_URL ) : '',
	'breadcrumb'  => isset( $ctx['breadcrumb'] )          ? $ctx['breadcrumb']                   : array(),
) );

get_header();

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

<?php /* ============================== STATS (animated count-up strip) ============================== */ ?>
<?php if ( ! empty( $ctx['stats']['items'] ) ) : ?>
	<?php adn_component( 'sections/animated_stat_strip', array( 'stats' => $ctx['stats'] ) ); ?>
<?php endif; ?>

<?php /* ============================== PROCESS (graphical step timeline) ============================== */ ?>
<?php if ( ! empty( $ctx['process']['steps'] ) ) : ?>
	<?php adn_component( 'sections/step_timeline', array( 'timeline' => $ctx['process'] ) ); ?>
<?php endif; ?>

<?php /* ============================== YOUR STORY (editorial narrative sequence) ============================== */ ?>
<?php if ( ! empty( $ctx['story']['chapters'] ) ) : ?>
	<?php adn_component( 'sections/story_narrative', array( 'story' => $ctx['story'] ) ); ?>
<?php endif; ?>

<?php /* ============================== WHAT YOU GET (benefits) ============================== */ ?>
<?php if ( ! empty( $ctx['benefits']['items'] ) ) : ?>
	<?php adn_component( 'sections/icon_feature_grid', array( 'grid' => $ctx['benefits'] ) ); ?>
<?php endif; ?>

<?php /* ============================== COMPARISON (alone vs with us) ============================== */ ?>
<?php if ( ! empty( $ctx['comparison'] ) ) : ?>
	<?php adn_component( 'sections/two_column_compare', array( 'compare' => $ctx['comparison'] ) ); ?>
<?php endif; ?>

<?php /* ============================== WHY CHOOSE US ============================== */ ?>
<?php if ( ! empty( $ctx['why_choose'] ) ) : ?>
	<?php adn_component( 'sections/icon_feature_grid', array( 'grid' => $ctx['why_choose'] ) ); ?>
<?php endif; ?>

<?php /* ============================== CLIENT REVIEWS (same source/model + markup as
        the Home page carousel - kept out of the cached Context on purpose:
        render_carousel_card() requires a real object, and ADN_Cache's JSON
        round-trip would decode it back into a plain array on a cache hit) ============================== */ ?>
<?php
$_hiw_reviews = class_exists( 'AH_Reviews_Model' ) ? ( new AH_Reviews_Model() )->get_carousel_reviews( 50 ) : array();
if ( ! empty( $_hiw_reviews ) ) :
?>
	<section class="reviews-carousel-section hiw-reviews-section">
		<div class="container">
			<?php adn_component( 'sections/reviews_carousel', array( 'reviews' => array(
				'items'   => $_hiw_reviews,
				'heading' => 'What Our Clients Say',
			) ) ); ?>
		</div>
	</section>
<?php endif; ?>

<?php /* ============================== IS THIS FOR YOU? (final self-check before
        the ask, so people only come to us once they're sure) ============================== */ ?>
<?php if ( ! empty( $ctx['fit_check'] ) ) : ?>
	<?php adn_component( 'sections/two_column_compare', array( 'compare' => $ctx['fit_check'] ) ); ?>
<?php endif; ?>

<?php /* ============================== CTA ============================== */ ?>
<?php if ( ! empty( $ctx['cta_banner'] ) ) : ?>
	<div class="container hiw-cta-wrap">
		<?php adn_component( 'parts/cta_banner', array( 'cta_banner' => $ctx['cta_banner'] ) ); ?>
	</div>
<?php endif; ?>

<?php /* ============================== FAQ teaser -> /faqs/ ============================== */ ?>
<?php if ( ! empty( $ctx['faq_teaser']['cta']['url'] ) ) : ?>
	<div class="container">
		<div class="hiw-faq-teaser">
			<span class="hiw-faq-teaser-icon" aria-hidden="true"><?php echo adn_icon( 'fa-solid fa-circle-question' ); ?></span>
			<span class="hiw-faq-teaser-text"><?php echo esc_html( isset( $ctx['faq_teaser']['text'] ) ? (string) $ctx['faq_teaser']['text'] : '' ); ?></span>
			<a href="<?php echo esc_url( adn_link( $ctx['faq_teaser']['cta']['url'] ) ); ?>" class="hiw-faq-teaser-link">
				<?php echo esc_html( isset( $ctx['faq_teaser']['cta']['label'] ) ? (string) $ctx['faq_teaser']['cta']['label'] : '' ); ?> →
			</a>
		</div>
	</div>
<?php endif; ?>

<?php /* ============================== FAQs (only ones attached to this page) ============================== */ ?>
<?php adn_component( 'sections/faqs_footer', array(
	'groups' => adn_get_page_faqs_grouped( adn_get_cms_page_id( 'how-it-works' ), false ),
) ); ?>

<?php adn_page_close( $ctx ); ?>

<?php get_footer(); ?>
