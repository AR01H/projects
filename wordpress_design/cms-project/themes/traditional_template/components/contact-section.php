<?php
/**
 * Contact section: ported from traditional design.
 */
defined( 'ABSPATH' ) || exit;

$settings = []; // App_Helpers::data('site') could be used here
$phone    = App_Helpers::option( 'general', 'phone', NT_BRAND_PHONE );
$email    = App_Helpers::option( 'general', 'email', NT_BRAND_EMAIL );
$address  = App_Helpers::option( 'general', 'address' );

$content = App_Helpers::data( 'content' )['contact_section'] ?? [];
$sec_tag   = $content['tag']        ?? 'Get in Touch';
$sec_title = $content['title']      ?? 'Contact Us';
$sec_body  = $content['body']       ?? 'We would love to hear from you.';
$form_title = $content['form_title'] ?? 'Send a Message';

$contact_details = [
    'address'             => [ 'icon' => '📍', 'label' => 'Address', 'value' => $address ],
    'business_hours'      => [ 'icon' => '🕐', 'label' => 'Business Hours', 'value' => 'Mon - Sun: 9am - 8pm' ],
];

// Polaroid photo is JSON-driven (content.json -> contact_section.photo /
// photo_caption) so it can be re-skinned per business; the default is a
// verified-loading image, not a placeholder.
$trad_photo   = $content['photo'] ?? 'https://images.unsplash.com/photo-1519671482749-fd09be7ccebf?auto=format&fit=crop&w=600&q=80';
$trad_caption = $content['photo_caption'] ?? 'Good Times. Sweet Memories. ♥';
?>

<section id="contact" class="app-contact-section">
	<div class="app-contact-info fade-left">
		<div class="app-section-tag"><?php echo esc_html( $sec_tag ); ?></div>
		<h2 class="app-section-title"><?php echo wp_kses( $sec_title, [ 'span' => [ 'class' => [] ] ] ); ?></h2>
		<p class="app-section-body" style="margin-top:.8rem;margin-bottom:2rem;"><?php echo esc_html( $sec_body ); ?></p>

		<?php if ( $phone ) : ?>
			<div class="app-contact-detail">
				<div class="app-cd-icon" aria-hidden="true">📞</div>
				<div>
					<div class="app-cd-label">Call Us</div>
					<div class="app-cd-val">
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>">
							<?php echo esc_html( $phone ); ?>
						</a>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $email ) : ?>
			<div class="app-contact-detail">
				<div class="app-cd-icon" aria-hidden="true">📧</div>
				<div>
					<div class="app-cd-label">Email Us</div>
					<div class="app-cd-val">
						<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
					</div>
				</div>
			</div>
		<?php endif;

		foreach ( $contact_details as $key => $detail ) :
			if ( empty( $detail['value'] ) ) continue;
		?>
			<div class="app-contact-detail">
				<div class="app-cd-icon" aria-hidden="true"><?php echo $detail['icon']; ?></div>
				<div>
					<div class="app-cd-label"><?php echo $detail['label']; ?></div>
					<div class="app-cd-val"><?php echo esc_html( $detail['value'] ); ?></div>
				</div>
			</div>
		<?php endforeach; ?>

		<figure class="app-contact-polaroid" aria-hidden="true">
			<span class="app-contact-pin"></span>
			<div class="app-contact-polaroid__mount">
				<img src="<?php echo esc_url( $trad_photo ); ?>" alt="" loading="lazy">
			</div>
			<figcaption class="app-contact-polaroid__cap"><?php echo esc_html( $trad_caption ); ?></figcaption>
		</figure>
	</div>

	<div class="app-contact-form fade-right">
		<span class="app-form-clip" aria-hidden="true"></span>
		<span class="app-contact-stamp" aria-hidden="true">
			<span class="app-contact-stamp__top">Freshly Pressed</span>
			<span class="app-contact-stamp__big">100%</span>
			<span class="app-contact-stamp__bot">Natural</span>
		</span>
		
		<div class="app-form-title"><?php echo esc_html( $form_title ); ?></div>

		<?php
		get_template_part( 'components/forms/generic-form', null, [
			'id'     => 'app-home-contact-form',
			'action' => 'contact_submit',
			'submit' => 'Send Message 🥤',
			'fields' => [
				[
					'type'     => 'text',
					'id'       => 'app-hc-name',
					'name'     => 'name',
					'label'    => 'Full Name',
					'placeholder' => 'Your name',
					'required' => true,
				],
				[
					'type'     => 'email',
					'id'       => 'app-hc-email',
					'name'     => 'email',
					'label'    => 'Email Address',
					'placeholder' => 'you@email.com',
					'required' => true,
				],
				[
					'type'     => 'tel',
					'id'       => 'app-hc-phone',
					'name'     => 'phone',
					'label'    => 'Phone Number',
					'placeholder' => '+44 ...',
					'required' => true,
				],
				[
					'type'     => 'text',
					'id'       => 'app-hc-subject',
					'name'     => 'subject',
					'label'    => 'Subject',
					'placeholder' => 'Subject',
					'required' => false,
				],
				[
					'type'     => 'textarea',
					'id'       => 'app-hc-message',
					'name'     => 'message',
					'label'    => 'Message',
					'placeholder' => 'Tell us more...',
					'required' => true,
				]
			]
		] );
		?>
	</div>
</section>
