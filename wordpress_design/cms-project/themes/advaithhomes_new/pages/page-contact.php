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

wp_enqueue_style( 'adn-page-faqs-style', get_template_directory_uri() . '/assets/css/faqs.css', array(), ADN_THEME_VERSION );
wp_enqueue_script( 'adn-page-faqs-script', get_template_directory_uri() . '/assets/js/faqs.js', array(), ADN_THEME_VERSION, true );

require_once ADN_THEME_DIR . '/intermediate/page_contact_logical.php';
$ctx = adn_contact_get_context();

adn_seo_register( array(
	'title'       => isset( $ctx['hero']['title'] )       ? (string) $ctx['hero']['title']       : '',
	'description' => isset( $ctx['hero']['description'] ) ? wp_strip_all_tags( (string) $ctx['hero']['description'] ) : '',
	'canonical'   => defined( 'SITE_CONTACT_URL' ) ? home_url( SITE_CONTACT_URL ) : '',
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

<?php /* ============================== MAIN: FORM + SIDEBAR ============================== */ ?>
<div class="contact-main-layout">

	<?php /* FORM */ ?>
	<?php adn_component( 'sections/contact_form', array( 'form' => $ctx['form'] ) ); ?>

	<?php /* SIDEBAR */ ?>
	<?php adn_component( 'parts/contact_sidebar', array( 'contact_sidebar' => $ctx['contact_sidebar'] ) ); ?>

</div>

<?php /* ============================== FAQs (only ones attached to Contact) ============================== */ ?>
<?php adn_component( 'sections/faqs_footer', array(
	'groups' => adn_get_page_faqs_grouped( adn_get_cms_page_id( 'contact' ), false ),
) ); ?>

<?php adn_page_close( $ctx ); ?>
