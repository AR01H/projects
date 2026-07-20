<?php
/**
 * Template Name: Contact
 *
 * Registered as 'contact' in config/pages.php. The form posts through
 * NT.ajax('contact_submit') -> config/ajax.php -> handlers/ajax/contact.php.
 * No nonce code here - the dispatcher and the JS helper handle it.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="nt-container nt-section">

	<?php
	nt_component( 'parts/page_header', array(
		'title'    => __( 'Contact Us', NT_TEXT_DOMAIN ),
		'subtitle' => __( 'Send us a message - we usually reply within one business day.', NT_TEXT_DOMAIN ),
	) );
	?>

	<div class="nt-contact-layout">

		<div class="nt-contact-info">
			<h3><?php esc_html_e( 'Reach us', NT_TEXT_DOMAIN ); ?></h3>
			<ul>
				<li><?php echo esc_html( nt_option( 'general', 'phone', NT_BRAND_PHONE ) ); ?></li>
				<li><?php echo esc_html( nt_option( 'general', 'email', NT_BRAND_EMAIL ) ); ?></li>
				<?php if ( '' !== (string) nt_option( 'general', 'address' ) ) : ?>
					<li><?php echo esc_html( nt_option( 'general', 'address' ) ); ?></li>
				<?php endif; ?>
			</ul>
		</div>

		<?php
		get_template_part( 'components/parts/generic-form', null, [
			'id'     => 'nt-contact-form',
			'action' => 'contact_submit',
			'submit' => __( 'Send Message', NT_TEXT_DOMAIN ),
			'fields' => [
				[
					'type'     => 'text',
					'id'       => 'nt-cf-name',
					'name'     => 'name',
					'label'    => __( 'Name', NT_TEXT_DOMAIN ),
					'required' => true,
				],
				[
					'type'     => 'email',
					'id'       => 'nt-cf-email',
					'name'     => 'email',
					'label'    => __( 'Email', NT_TEXT_DOMAIN ),
					'required' => true,
				],
				[
					'type'     => 'tel',
					'id'       => 'nt-cf-phone',
					'name'     => 'phone',
					'label'    => __( 'Phone', NT_TEXT_DOMAIN ),
					'required' => false,
				],
				[
					'type'     => 'textarea',
					'id'       => 'nt-cf-message',
					'name'     => 'message',
					'label'    => __( 'Message', NT_TEXT_DOMAIN ),
					'required' => true,
				]
			]
		] );
		?>

	</div>

</div>
<?php
get_footer();
