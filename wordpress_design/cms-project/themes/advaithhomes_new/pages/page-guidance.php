<?php
/**
 * Template Name: Get Expert Guidance
 *
 * pages/page-guidance.php — "Get Expert Guidance" expert-matching request form.
 *
 * Architecture:
 *   data/json/guidance.json
 *     → apis/services.php  adn_service_guidance_data()
 *       → intermediate/page_guidance_logical.php  adn_guidance_get_context()
 *         → THIS FILE (structure only)
 *
 * RULE: No hardcoded content or data reads here — only structure.
 * RULE: Header/footer come from header.php / footer.php via get_header() / get_footer().
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/page_guidance_logical.php';
$ctx = adn_guidance_get_context();

get_header();
?>

<?php adn_component( 'parts/main_header', array( 'chrome' => $ctx['chrome'] ) ); ?>

<?php /* ============================== BREADCRUMB ============================== */ ?>
<?php if ( ! empty( $ctx['breadcrumb'] ) ) : ?>
	<?php adn_component( 'parts/breadcrumb', array( 'items' => $ctx['breadcrumb'] ) ); ?>
<?php endif; ?>

<?php /* ============================== HERO + TRUST STRIP ============================== */ ?>
<?php if ( ! empty( $ctx['hero'] ) ) : ?>
	<?php adn_component( 'sections/guidance_hero', array( 'hero' => $ctx['hero'] ) ); ?>
<?php endif; ?>

<?php /* ============================== MAIN: FORM LEFT + SERVICES RIGHT ============================== */ ?>
<div class="guidance-main-layout">

	<?php /* Request form */ ?>
	<?php adn_component( 'sections/guidance_form', array( 'form' => $ctx['form'] ) ); ?>

	<?php /* "We can help you with" service categories */ ?>
	<?php if ( ! empty( $ctx['services'] ) ) : ?>
		<?php adn_component( 'sections/guidance_services', array( 'services' => $ctx['services'] ) ); ?>
	<?php endif; ?>

</div>

<?php /* ============================== WHY CHOOSE ============================== */ ?>
<?php if ( ! empty( $ctx['why_choose'] ) ) : ?>
	<?php adn_component( 'sections/guidance_why_choose', array( 'why_choose' => $ctx['why_choose'] ) ); ?>
<?php endif; ?>

<?php /* ============================== FOOTER ============================== */ ?>
<?php
adn_component( 'parts/pre_footer' );
adn_component( 'parts/main_footer', array( 'footer' => isset( $ctx['chrome']['footer'] ) ? $ctx['chrome']['footer'] : array() ) );
adn_component( 'parts/post_footer' );
adn_component( 'parts/post_footer_notice' );

get_footer();
?>
