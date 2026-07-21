<?php
/**
 * Contact section: ported from traditional design.
 */
defined( 'ABSPATH' ) || exit;

$settings = []; // nt_data('site') could be used here
$phone    = nt_option( 'general', 'phone', NT_BRAND_PHONE );
$email    = nt_option( 'general', 'email', NT_BRAND_EMAIL );
$address  = nt_option( 'general', 'address' );

$content = nt_data( 'content' )['contact_section'] ?? [];
$sec_tag   = $content['tag']        ?? 'Get in Touch';
$sec_title = $content['title']      ?? 'Contact Us';
$sec_body  = $content['body']       ?? 'We would love to hear from you.';
$form_title = $content['form_title'] ?? 'Send a Message';

$contact_details = [
    'address'             => [ 'icon' => '📍', 'label' => 'Address', 'value' => $address ],
    'business_hours'      => [ 'icon' => '🕐', 'label' => 'Business Hours', 'value' => 'Mon - Sun: 9am - 8pm' ],
];

$trad_photo  = 'https://images.unsplash.com/photo-1541123437800-1bb1317bc20f?auto=format&fit=crop&w=600&q=80';
?>

<section id="contact" class="nt-contact-section">
	<div class="nt-contact-info fade-left">
		<div class="nt-section-tag"><?php echo esc_html( $sec_tag ); ?></div>
		<h2 class="nt-section-title"><?php echo wp_kses( $sec_title, [ 'span' => [ 'class' => [] ] ] ); ?></h2>
		<p class="nt-section-body" style="margin-top:.8rem;margin-bottom:2rem;"><?php echo esc_html( $sec_body ); ?></p>

		<?php if ( $phone ) : ?>
			<div class="nt-contact-detail">
				<div class="nt-cd-icon" aria-hidden="true">📞</div>
				<div>
					<div class="nt-cd-label">Call Us</div>
					<div class="nt-cd-val">
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>">
							<?php echo esc_html( $phone ); ?>
						</a>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $email ) : ?>
			<div class="nt-contact-detail">
				<div class="nt-cd-icon" aria-hidden="true">📧</div>
				<div>
					<div class="nt-cd-label">Email Us</div>
					<div class="nt-cd-val">
						<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
					</div>
				</div>
			</div>
		<?php endif;

		foreach ( $contact_details as $key => $detail ) :
			if ( empty( $detail['value'] ) ) continue;
		?>
			<div class="nt-contact-detail">
				<div class="nt-cd-icon" aria-hidden="true"><?php echo $detail['icon']; ?></div>
				<div>
					<div class="nt-cd-label"><?php echo $detail['label']; ?></div>
					<div class="nt-cd-val"><?php echo esc_html( $detail['value'] ); ?></div>
				</div>
			</div>
		<?php endforeach; ?>

		<figure class="nt-contact-polaroid" aria-hidden="true">
			<span class="nt-contact-pin"></span>
			<div class="nt-contact-polaroid__mount">
				<img src="<?php echo esc_url( $trad_photo ); ?>" alt="" loading="lazy">
			</div>
			<figcaption class="nt-contact-polaroid__cap">Good Times. Sweet Memories. ♥</figcaption>
		</figure>
	</div>

	<div class="nt-contact-form fade-right">
		<span class="nt-form-clip" aria-hidden="true"></span>
		<span class="nt-contact-stamp" aria-hidden="true">
			<span class="nt-contact-stamp__top">Freshly Pressed</span>
			<span class="nt-contact-stamp__big">100%</span>
			<span class="nt-contact-stamp__bot">Natural</span>
		</span>
		
		<div class="nt-form-title"><?php echo esc_html( $form_title ); ?></div>

		<?php
		get_template_part( 'components/parts/generic-form', null, [
			'id'     => 'nt-home-contact-form',
			'action' => 'contact_submit',
			'submit' => 'Send Message 🥤',
			'fields' => [
				[
					'type'     => 'text',
					'id'       => 'nt-hc-name',
					'name'     => 'name',
					'label'    => 'Full Name',
					'placeholder' => 'Your name',
					'required' => true,
				],
				[
					'type'     => 'email',
					'id'       => 'nt-hc-email',
					'name'     => 'email',
					'label'    => 'Email Address',
					'placeholder' => 'you@email.com',
					'required' => true,
				],
				[
					'type'     => 'tel',
					'id'       => 'nt-hc-phone',
					'name'     => 'phone',
					'label'    => 'Phone Number',
					'placeholder' => '+44 ...',
					'required' => true,
				],
				[
					'type'     => 'text',
					'id'       => 'nt-hc-subject',
					'name'     => 'subject',
					'label'    => 'Subject',
					'placeholder' => 'Subject',
					'required' => false,
				],
				[
					'type'     => 'textarea',
					'id'       => 'nt-hc-message',
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
