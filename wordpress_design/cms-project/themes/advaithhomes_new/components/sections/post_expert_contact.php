<?php
/**
 * components/sections/post_expert_contact.php
 *
 * Expert + contact block rendered below article comments on single posts.
 * Contact data (whatsapp / phone / email) comes from DB via adn_service_contact_data(),
 * same source as the /contact page — never hardcoded here.
 *
 * Props: $experts (array), $contact (array)
 */

defined( 'ABSPATH' ) || exit;

$expert_data  = isset( $experts ) && is_array( $experts ) ? $experts : array();
$contact_data = isset( $contact ) && is_array( $contact ) ? $contact : array();

/* ── Left panel: fixed site-navigation tiles ── */
$hero      = isset( $expert_data['hero'] ) && is_array( $expert_data['hero'] ) ? $expert_data['hero'] : array();
$cant_find = isset( $expert_data['cant_find_cta'] ) && is_array( $expert_data['cant_find_cta'] ) ? $expert_data['cant_find_cta'] : array();

$nav_tiles = array(
	array( 'icon' => '🤝', 'label' => 'Ask an Expert', 'url' => '/ask-an-expert/' ),
	array( 'icon' => '📰', 'label' => 'News',        'url' => '/news/' ),
	array( 'icon' => '📞', 'label' => 'Contact',           'url' => '/contact' ),
	array( 'icon' => '📚', 'label' => 'Guides',         'url' => '/guides/' ),
);

/* ── Right panel: contact from DB/JSON only ── */
$contact_sidebar = isset( $contact_data['contact_sidebar'] ) && is_array( $contact_data['contact_sidebar'] ) ? $contact_data['contact_sidebar'] : array();
$whatsapp        = isset( $contact_sidebar['whatsapp'] ) && is_array( $contact_sidebar['whatsapp'] ) ? $contact_sidebar['whatsapp'] : array();
$phone           = isset( $contact_sidebar['phone'] )    && is_array( $contact_sidebar['phone'] )    ? $contact_sidebar['phone']    : array();
$email           = isset( $contact_sidebar['email'] )    && is_array( $contact_sidebar['email'] )    ? $contact_sidebar['email']    : array();
?>
<div class="pec-wrap">

	<?php /* ── LEFT: Navigation tiles ── */ ?>
	<div class="pec-experts">
		<div class="pec-eyebrow">Professional Help</div>
		<h3 class="pec-heading">
			<?php echo esc_html( ! empty( $hero['title'] ) ? $hero['title'] : 'Need Professional Help?' ); ?>
		</h3>
		<p class="pec-sub">
			<?php echo esc_html( ! empty( $hero['description'] ) ? $hero['description'] : 'Connect with trusted property professionals who can guide you at every step.' ); ?>
		</p>

		<div class="pec-types">
			<?php foreach ( $nav_tiles as $tile ) : ?>
			<a href="<?php echo esc_url( function_exists( 'adn_link' ) ? adn_link( $tile['url'] ) : $tile['url'] ); ?>" class="pec-type-item">
				<span class="pec-type-icon" aria-hidden="true"><?php echo esc_html( $tile['icon'] ); ?></span>
				<span class="pec-type-label"><?php echo esc_html( $tile['label'] ); ?></span>
			</a>
			<?php endforeach; ?>
		</div>

		<?php if ( ! empty( $cant_find['button_url'] ) ) : ?>
		<a href="<?php echo esc_url( function_exists( 'adn_link' ) ? adn_link( $cant_find['button_url'] ) : $cant_find['button_url'] ); ?>" class="pec-cta-btn">
			<?php echo esc_html( ! empty( $cant_find['button_label'] ) ? $cant_find['button_label'] : 'Find Trusted Experts' ); ?>
			<svg width="14" height="14" viewBox="0 0 12 12" fill="none" aria-hidden="true">
				<path d="M2 6h8M7 3l3 3-3 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</a>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $phone ) || ! empty( $email ) ) : ?>
	<?php /* ── RIGHT: Contact options from DB/JSON ── */ ?>
	<div class="pec-contact">

		<?php if ( ! empty( $whatsapp ) ) : ?>
		<a href="<?php echo esc_url( isset( $whatsapp['url'] ) ? $whatsapp['url'] : '#' ); ?>" class="pec-contact-item" target="_blank" rel="noopener noreferrer">
			<span class="pec-contact-icon" aria-hidden="true"><?php echo esc_html( isset( $whatsapp['icon'] ) ? $whatsapp['icon'] : '💬' ); ?></span>
			<div class="pec-contact-body">
				<strong class="pec-contact-heading"><?php echo esc_html( isset( $whatsapp['heading'] ) ? $whatsapp['heading'] : 'Prefer WhatsApp?' ); ?></strong>
				<?php if ( ! empty( $whatsapp['number'] ) ) : ?>
					<span class="pec-contact-meta"><?php echo esc_html( $whatsapp['number'] ); ?></span>
				<?php endif; ?>
				<span class="pec-contact-link"><?php echo esc_html( isset( $whatsapp['button_label'] ) ? $whatsapp['button_label'] : 'Start Chat' ); ?> →</span>
			</div>
		</a>
		<?php endif; ?>

		<?php if ( ! empty( $phone ) ) : ?>
		<a href="<?php echo esc_url( isset( $phone['url'] ) ? $phone['url'] : '#' ); ?>" class="pec-contact-item">
			<span class="pec-contact-icon" aria-hidden="true"><?php echo esc_html( isset( $phone['icon'] ) ? $phone['icon'] : '📞' ); ?></span>
			<div class="pec-contact-body">
				<strong class="pec-contact-heading"><?php echo esc_html( isset( $phone['heading'] ) ? $phone['heading'] : 'Prefer a Call?' ); ?></strong>
				<?php if ( ! empty( $phone['number'] ) ) : ?>
					<span class="pec-contact-meta"><?php echo esc_html( $phone['number'] ); ?></span>
				<?php endif; ?>
				<span class="pec-contact-link"><?php echo esc_html( isset( $phone['button_label'] ) ? $phone['button_label'] : 'Call Us' ); ?> →</span>
			</div>
		</a>
		<?php endif; ?>

		<?php if ( ! empty( $email ) ) : ?>
		<a href="<?php echo esc_url( ! empty( $email['url'] ) ? $email['url'] : 'mailto:' . ( isset( $email['address'] ) ? rawurlencode( $email['address'] ) : '' ) ); ?>" class="pec-contact-item">
			<span class="pec-contact-icon" aria-hidden="true"><?php echo esc_html( isset( $email['icon'] ) ? $email['icon'] : '📧' ); ?></span>
			<div class="pec-contact-body">
				<strong class="pec-contact-heading"><?php echo esc_html( isset( $email['heading'] ) ? $email['heading'] : 'Prefer Email?' ); ?></strong>
				<?php if ( ! empty( $email['address'] ) ) : ?>
					<span class="pec-contact-meta"><?php echo esc_html( $email['address'] ); ?></span>
				<?php endif; ?>
				<span class="pec-contact-link"><?php echo esc_html( isset( $email['button_label'] ) ? $email['button_label'] : 'Send an Email' ); ?> →</span>
			</div>
		</a>
		<?php endif; ?>
	</div>
	<?php endif; ?>

</div>
