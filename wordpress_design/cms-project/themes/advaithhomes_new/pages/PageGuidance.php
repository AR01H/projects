<?php
/**
 * Template Name: Get Expert Guidance
 *
 * pages/PageGuidance.php - "Get Expert Guidance" expert-matching request form.
 *
 * Architecture:
 *   data/json/guidance.json
 *     → apis/services.php  adn_service_guidance_data()
 *       → intermediate/page_guidance_logical.php  adn_guidance_get_context()
 *         → THIS FILE (structure only)
 *
 * RULE: No hardcoded content or data reads here - only structure.
 * RULE: Header/footer come from header.php / footer.php via get_header() / get_footer().
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ctx = \Adn\Theme\Feature\Guidance\Controller\GuidanceController::getContext();

adn_seo_register( array(
	'title'       => isset( $ctx['hero']['title'] )       ? (string) $ctx['hero']['title']       : '',
	'description' => isset( $ctx['hero']['description'] ) ? wp_strip_all_tags( (string) $ctx['hero']['description'] ) : '',
	'canonical'   => defined( 'SITE_GUIDANCE_URL' ) ? home_url( SITE_GUIDANCE_URL ) : '',
	'breadcrumb'  => isset( $ctx['breadcrumb'] )          ? $ctx['breadcrumb']                   : array(),
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

<?php /* ============================== MAIN: FORM LEFT + SERVICES RIGHT ============================== */ ?>
<div class="guidance-main-layout">

	<?php /* Request form */ ?>
	<?php adn_component( 'sections/guidance_form', array( 'form' => $ctx['form'] ) ); ?>

	<?php /* SIDEBAR */ ?>
	<?php if ( ! empty( $ctx['contact_sidebar'] ) ) : ?>
		<?php adn_component( 'parts/contact_sidebar', array( 'contact_sidebar' => $ctx['contact_sidebar'] ) ); ?>
	<?php endif; ?>

</div>



<?php adn_page_close( $ctx ); ?>

<?php get_footer(); ?>
