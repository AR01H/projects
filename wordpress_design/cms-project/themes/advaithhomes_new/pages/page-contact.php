<?php
/**
 * Template Name: Contact Us
 *
 * pages/page-contact.php - "How can we help you?" contact & enquiry page.
 *
 * Architecture:
 *   data/json/contact.json
 *     → apis/services.php  adn_service_contact_data()
 *       → intermediate/page_contact_logical.php  adn_contact_get_context()
 *         → THIS FILE (structure only)
 *
 * RULE: No hardcoded content or data reads here - only structure.
 * RULE: Header/footer come from header.php / footer.php via get_header() / get_footer().
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/page_contact_logical.php';
$ctx = adn_contact_get_context();

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

<?php adn_page_close( $ctx ); ?>
