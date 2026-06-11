<?php
/**
 * Template Name: Contact Us
 *
 * pages/page-contact.php - sample contact page.
 * Flow: form builder component → POST /advaithhomes/v1/contact →
 * saved to {prefix}adn_contact_submissions → fires ADN_Rules::CONTACT_FORM
 * in the rules engine (attach email/WhatsApp actions in the CMS plugin).
 *
 * RULE: Page templates fetch and render only - no business logic here.
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<main id="primary" class="adn-page adn-page--contact" style="max-width:960px;margin:0 auto;padding:2rem 1rem;">

	<header class="adn-page__header">
		<h1><?php echo esc_html( lang_translate( 'contact_us' ) ); ?></h1>
		<p><?php echo esc_html( lang_translate( 'description' ) ); ?></p>
	</header>

	<?php /* ── Company contact details (from includes/core_info.php) ── */ ?>
	<section class="adn-contact-info">
		<ul style="list-style:none;padding:0;display:flex;gap:2rem;flex-wrap:wrap;">
			<li>
				<strong><?php esc_html_e( 'Phone', ADN_TEXT_DOMAIN ); ?>:</strong>
				<a href="tel:<?php echo esc_attr( COMPANY_PHONE_NO ); ?>"><?php echo esc_html( COMPANY_EXTENDED_PHONE_NO ); ?></a>
			</li>
			<li>
				<strong><?php esc_html_e( 'Email', ADN_TEXT_DOMAIN ); ?>:</strong>
				<a href="mailto:<?php echo esc_attr( COMPANY_EMAIL ); ?>"><?php echo esc_html( COMPANY_EMAIL ); ?></a>
			</li>
			<?php if ( '' !== COMPANY_WHATSAPP_NO ) : ?>
			<li>
				<strong><?php esc_html_e( 'WhatsApp', ADN_TEXT_DOMAIN ); ?>:</strong>
				<a href="https://wa.me/<?php echo esc_attr( preg_replace( '/\D/', '', COMPANY_WHATSAPP_NO ) ); ?>" target="_blank" rel="noopener">
					<?php echo esc_html( COMPANY_WHATSAPP_NO ); ?>
				</a>
			</li>
			<?php endif; ?>
		</ul>
	</section>

	<?php /* ── Contact form (form builder → REST API → rules engine) ── */ ?>
	<section class="adn-contact-form">
		<?php
		adn_render_form( array(
			'id'              => 'contact',
			'endpoint'        => rest_url( ADN_API_NS . '/contact' ),
			'submit_label'    => lang_translate( 'contact_us' ),
			'success_message' => 'Thanks! Your message has been received - we will be in touch shortly.',
			'fields'          => array(
				array( 'type' => 'text',  'name' => 'name',  'label' => 'Your Name', 'required' => true, 'width' => 'half', 'placeholder' => 'Full name' ),
				array( 'type' => 'email', 'name' => 'email', 'label' => 'Email',     'required' => true, 'width' => 'half', 'placeholder' => 'you@example.com' ),
				array( 'type' => 'tel',   'name' => 'phone', 'label' => 'Phone',     'width' => 'half', 'placeholder' => '+91 ...' ),
				array(
					'type'    => 'select',
					'name'    => 'topic',
					'label'   => 'I am interested in',
					'width'   => 'half',
					'options' => array(
						'general'  => 'General Enquiry',
						'buying'   => 'Buying a Home',
						'site_visit' => 'Booking a Site Visit',
						'support'  => 'Customer Support',
					),
				),
				array( 'type' => 'textarea', 'name' => 'message', 'label' => 'Message', 'required' => true, 'rows' => 6, 'help' => 'Tell us a little about what you are looking for.' ),
			),
		) );
		?>
	</section>

</main>

<?php wp_footer(); ?>
</body>
</html>
