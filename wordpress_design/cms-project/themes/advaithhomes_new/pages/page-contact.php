<?php
/**
 * Template Name: Contact Us
 *
 * pages/page-contact.php — "How can we help you?" contact & enquiry page.
 *
 * Architecture:
 *   data/json/contact.json
 *     → apis/services.php  adn_service_contact_data()
 *       → intermediate/page_contact_logical.php  adn_contact_get_context()
 *         → THIS FILE (structure only)
 *
 * RULE: No hardcoded content or data reads here — only structure.
 * RULE: Header/footer come from header.php / footer.php via get_header() / get_footer().
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/page_contact_logical.php';
$ctx = adn_contact_get_context();

get_header();
?>

<?php adn_component( 'parts/main_header', array( 'chrome' => $ctx['chrome'] ) ); ?>

<?php /* ============================== BREADCRUMB ============================== */ ?>
<?php if ( ! empty( $ctx['breadcrumb'] ) ) : ?>
	<?php adn_component( 'parts/breadcrumb', array( 'items' => $ctx['breadcrumb'] ) ); ?>
<?php endif; ?>

<?php /* ============================== HERO + TRUST BAR ============================== */ ?>
<?php if ( ! empty( $ctx['hero'] ) ) : ?>
	<?php adn_component( 'sections/contact_hero', array( 'hero' => $ctx['hero'] ) ); ?>
<?php endif; ?>

<?php /* ============================== MAIN: FORM + SIDEBAR ============================== */ ?>
<div class="contact-main-layout">

	<?php /* FORM */ ?>
	<?php adn_component( 'sections/contact_form', array( 'form' => $ctx['form'] ) ); ?>

	<?php /* SIDEBAR */ ?>
	<?php adn_component( 'parts/contact_sidebar', array( 'contact_sidebar' => $ctx['contact_sidebar'] ) ); ?>

</div>

<?php /* ============================== PROCESS STEPS ============================== */ ?>
<?php if ( ! empty( $ctx['process_steps'] ) ) : ?>
	<?php adn_component( 'sections/contact_process', array( 'process_steps' => $ctx['process_steps'] ) ); ?>
<?php endif; ?>

<?php /* ============================== RESOURCE CARDS ============================== */ ?>
<?php if ( ! empty( $ctx['resources'] ) ) : ?>
	<?php adn_component( 'sections/contact_resources', array( 'resources' => $ctx['resources'] ) ); ?>
<?php endif; ?>

<?php /* ============================== FOOTER ============================== */ ?>
<?php
adn_component( 'parts/pre_footer' );
adn_component( 'parts/main_footer', array( 'footer' => isset( $ctx['chrome']['footer'] ) ? $ctx['chrome']['footer'] : array() ) );
adn_component( 'parts/post_footer' );
adn_component( 'parts/post_footer_notice' );

get_footer();
?>
