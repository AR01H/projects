<?php
/**
 * Template Name: How It Works
 *
 * pages/PageHowItWorks.php
 *
 * Sections (render order):
 *   page_hero  →  animated_stat_strip  →  step_timeline (expandable cards)
 *   →  story_narrative (cinematic)  →  two_column_compare (fit_check)
 *   →  cta_banner  →  trust_callout  →  faqs_footer (CMS-backed)
 *
 * RULE: No hardcoded content here — structure only.
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

<?php /* ============================== BUYER COMMITMENT NOTICE (STANDALONE) ============================== */ ?>
<?php if ( ! empty( $ctx['commitment_notice']['text'] ) ) : ?>
<section class="hiw-commitment-notice-section">
	<div class="container">
		<?php adn_component( 'parts/commitment_notice', array( 'notice' => $ctx['commitment_notice'] ) ); ?>
	</div>
</section>
<?php endif; ?>

<?php /* ============================== PROCESS — expandable step cards ============================== */ ?>
<?php if ( ! empty( $ctx['process']['steps'] ) ) : ?>
	<?php adn_component( 'sections/step_timeline', array( 'timeline' => $ctx['process'] ) ); ?>
<?php endif; ?>

<?php /* ============================== STORY — cinematic chapter sequence ============================== */ ?>
<?php if ( ! empty( $ctx['story']['chapters'] ) ) : ?>
	<?php adn_component( 'sections/story_narrative', array( 'story' => $ctx['story'] ) ); ?>
<?php endif; ?>

<?php /* ============================== IS THIS RIGHT FOR YOU? ============================== */ ?>
<?php if ( ! empty( $ctx['fit_check'] ) ) : ?>
	<?php adn_component( 'sections/two_column_compare', array( 'compare' => $ctx['fit_check'] ) ); ?>
<?php endif; ?>

<?php /* ============================== TRUST CALLOUT (animated "Why It's Safe to Start" info strip) ============================== */ ?>
<?php if ( ! empty( $ctx['trust_callout']['items'] ) ) : ?>
	<?php adn_component( 'sections/trust_callout', array( 'callout' => $ctx['trust_callout'] ) ); ?>
<?php endif; ?>

<?php /* ============================== CTA ============================== */ ?>
<?php if ( ! empty( $ctx['cta_banner'] ) ) : ?>
	<div class="container hiw-cta-wrap">
		<?php adn_component( 'parts/cta_banner', array( 'cta_banner' => $ctx['cta_banner'] ) ); ?>
	</div>
<?php endif; ?>

<?php /* ============================== FAQs (CMS-backed: only ones attached to this page) ============================== */ ?>
<?php
$_hiw_faqs = function_exists( 'adn_get_page_faqs_grouped' ) ? adn_get_page_faqs_grouped( adn_get_cms_page_id( 'how-it-works' ), false ) : array();
if ( ! empty( $_hiw_faqs ) ) :
?>
<div class="hiw-faqs-section-wrap">
	<div class="container">
		<?php adn_component( 'parts/section_headers/eyebrow_heading', array(
			'eyebrow'       => 'Common Questions',
			'heading'       => 'Frequently Asked Questions',
			'wrapper_class' => 'hiw-faqs-header',
		) ); ?>
		<?php adn_component( 'sections/faqs_footer', array( 'groups' => $_hiw_faqs ) ); ?>
	</div>
</div>
<?php endif; ?>

<?php adn_page_close( $ctx ); ?>

<?php get_footer(); ?>
