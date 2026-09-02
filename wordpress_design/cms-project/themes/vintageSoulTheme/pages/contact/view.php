<?php

use VintageSoul\Controllers\ContactController;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

$data = ( new ContactController() )->prepare();

$hero          = (array) ( $data['hero'] ?? array() );
$contact_info  = (array) ( $data['contact_info'] ?? array() );
$event_bar     = (array) ( $data['event_bar'] ?? array() );
$enquiry_types = (array) ( $data['enquiry_types'] ?? array() );
$faqs          = (array) ( $data['faqs'] ?? array() );

$phone   = (string) ( $contact_info['phone'] ?? '' );
$email   = (string) ( $contact_info['email'] ?? '' );
$address = (string) ( $contact_info['address'] ?? '' );
$hours   = (string) ( $contact_info['hours'] ?? '' );
$socials = (array) ( $contact_info['socials'] ?? array() );
?>

<div class="contact-page">
	<?php View::component( 'background/parchment-botanical-bg', array( 'seed' => 61 ) ); ?>
	<!-- 1. Master Subpage Hero -->
	<?php
	View::component(
		'subpage-hero/subpage-hero',
		array(
			'id'    => 'contact-hero',
			'tag'   => (string) ( $hero['tag'] ?? '' ),
			'title' => (string) ( $hero['title'] ?? '' ),
			'sub'   => (string) ( $hero['sub'] ?? '' ),
			'image' => (string) ( $hero['image'] ?? '' ),
		)
	);
	?>

	<!-- 2. Side-by-Side Main Contact Stage -->
	<section class="section contact-stage-section paper-rough">
		<div class="container contact-stage-container">
			<div class="contact-stage-grid">
				
				<!-- Left Column: Contact & Booking Form -->
				<div class="contact-form-card frame--ornate">
					<div class="contact-card-header">
						<h2 class="contact-card-header__title">SEND US A <em>Message</em></h2>
						<p class="contact-card-header__sub">Fill out the form below and we will respond promptly.</p>
					</div>

					<form class="vintage-form" method="post" action="#">
						<div class="form-row form-row--duo">
							<div class="form-group">
								<label class="form-label" for="contact-name">YOUR FULL NAME <span class="required">*</span></label>
								<input class="form-input" type="text" id="contact-name" name="name" placeholder="e.g. Ramesh Patel" required>
							</div>
							<div class="form-group">
								<label class="form-label" for="contact-phone">PHONE / WHATSAPP <span class="required">*</span></label>
								<input class="form-input" type="tel" id="contact-phone" name="phone" placeholder="+44 7770 000 000" required>
							</div>
						</div>

						<div class="form-row form-row--duo">
							<div class="form-group">
								<label class="form-label" for="contact-email">EMAIL ADDRESS <span class="required">*</span></label>
								<input class="form-input" type="email" id="contact-email" name="email" placeholder="you@example.com" required>
							</div>
							<div class="form-group">
								<label class="form-label" for="contact-type">ENQUIRY TYPE</label>
								<select class="form-select" id="contact-type" name="enquiry_type">
									<?php foreach ( $enquiry_types as $type ) : ?>
										<option value="<?php echo esc_attr( (string) ( $type['value'] ?? '' ) ); ?>">
											<?php echo esc_html( (string) ( $type['label'] ?? '' ) ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>

						<div class="form-row form-row--duo">
							<div class="form-group">
								<label class="form-label" for="contact-date">EVENT DATE (IF APPLICABLE)</label>
								<input class="form-input" type="date" id="contact-date" name="event_date">
							</div>
							<div class="form-group">
								<label class="form-label" for="contact-guests">ESTIMATED GUESTS / CUPS</label>
								<input class="form-input" type="number" id="contact-guests" name="guests" min="10" placeholder="e.g. 150">
							</div>
						</div>

						<div class="form-group">
							<label class="form-label" for="contact-message">YOUR MESSAGE / EVENT REQUIREMENTS <span class="required">*</span></label>
							<textarea class="form-textarea" id="contact-message" name="message" rows="4" placeholder="Tell us about your event, location, flavor preferences, or any questions..." required></textarea>
						</div>

						<div class="form-actions">
							<button class="btn btn--primary-vintage btn--submit" type="submit">
								<span>SEND MESSAGE / ENQUIRY</span>
								<span class="btn__arrow">➔</span>
							</button>
							<span class="form-note">🔒 We respect your privacy. Zero spam guaranteed.</span>
						</div>
					</form>
				</div>

				<!-- Right Column: Side Contact Details & Cards -->
				<div class="contact-details-col">
					
					<!-- Card 1: Direct Contact Info -->
					<div class="side-info-card frame--ornate-sm">
						<h3 class="side-info-card__title">🌿 DIRECT CONTACT DETAILS</h3>
						<ul class="side-info-list">
							<?php if ( '' !== $phone ) : ?>
								<li class="side-info-item">
									<span class="side-info-item__icon"><?php echo IconHelper::get( 'phone', '#172b15', 18 ); // phpcs:ignore ?></span>
									<div class="contact-card-v2__info">
										<span class="contact-card-v2__label">DIRECT PHONE</span>
										<a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
									</div>
								</li>
							<?php endif; ?>
							<?php
							$wa_num = (string) ( $contact_info['whatsapp'] ?? $phone );
							$wa_url = (string) ( $contact_info['whatsapp_url'] ?? \VintageSoul\Services\SettingsService::whatsapp_url() );
							if ( '' !== $wa_num ) : ?>
								<li class="side-info-item">
									<span class="side-info-item__icon"><?php echo IconHelper::get( 'whatsapp', '#172b15', 18 ); // phpcs:ignore ?></span>
									<div class="contact-card-v2__info">
										<span class="contact-card-v2__label">WHATSAPP CHAT</span>
										<a href="<?php echo esc_url( $wa_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $wa_num ); ?></a>
									</div>
								</li>
							<?php endif; ?>
							<?php if ( '' !== $email ) : ?>
								<li class="side-info-item">
									<span class="side-info-item__icon"><?php echo IconHelper::get( 'mail', '#172b15', 18 ); // phpcs:ignore ?></span>
									<div class="side-info-item__text">
										<strong>Email Us:</strong>
										<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
									</div>
								</li>
							<?php endif; ?>
							<?php if ( '' !== $address ) : ?>
								<li class="side-info-item">
									<span class="side-info-item__icon"><?php echo IconHelper::get( 'stall', '#172b15', 18 ); // phpcs:ignore ?></span>
									<div class="side-info-item__text">
										<strong>Main Hub &amp; Stall:</strong>
										<span><?php echo esc_html( $address ); ?></span>
									</div>
								</li>
							<?php endif; ?>
							<?php if ( '' !== $hours ) : ?>
								<li class="side-info-item">
									<span class="side-info-item__icon"><?php echo IconHelper::get( 'growth', '#172b15', 18 ); // phpcs:ignore ?></span>
									<div class="side-info-item__text">
										<strong>Opening Hours:</strong>
										<span><?php echo esc_html( $hours ); ?></span>
									</div>
								</li>
							<?php endif; ?>
						</ul>
					</div>

					<!-- Card 2: Live Sugarcane Bar Event Banner -->
					<div class="event-callout-card">
						<h3 class="event-callout-card__title"><?php echo wp_kses_post( (string) ( $event_bar['title'] ?? '🎪 BOOK OUR LIVE <em>Cane Bar</em>' ) ); ?></h3>
						<p class="event-callout-card__sub"><?php echo esc_html( (string) ( $event_bar['sub'] ?? '' ) ); ?></p>
						<ul class="event-callout-card__list">
							<?php foreach ( (array) ( $event_bar['highlights'] ?? array() ) as $hl ) : ?>
								<li><span class="check-icon">✓</span> <?php echo esc_html( (string) $hl ); ?></li>
							<?php endforeach; ?>
						</ul>
						<div class="event-callout-card__cta">
							<a class="btn btn--outline-vintage" href="<?php echo esc_url( \VintageSoul\Services\SettingsService::whatsapp_url() ); ?>" target="_blank" rel="noopener">
								<span>CHAT ON WHATSAPP</span>
							</a>
						</div>
					</div>

					<!-- Card 3: Social Connect Links -->
					<div class="social-connect-card frame--ornate-sm">
						<h4 class="social-connect-card__title">FOLLOW OUR <em>Journey</em></h4>
						<p class="social-connect-card__sub">Stay updated with our weekend stall locations & seasonal juices.</p>
						<div class="social-pills-row">
							<a class="social-pill" href="<?php echo esc_url( \VintageSoul\Services\SettingsService::social_url( 'facebook', 'https://facebook.com/thecanehouseuk' ) ); ?>" target="_blank" rel="noopener">Facebook</a>
							<a class="social-pill" href="<?php echo esc_url( \VintageSoul\Services\SettingsService::social_url( 'instagram', 'https://instagram.com/thecanehouseuk' ) ); ?>" target="_blank" rel="noopener">Instagram</a>
							<a class="social-pill" href="<?php echo esc_url( \VintageSoul\Services\SettingsService::whatsapp_url() ); ?>" target="_blank" rel="noopener">WhatsApp</a>
						</div>
					</div>

				</div>

			</div>
		</div>
	</section>

	<!-- Gold Wave Divider -->
	<div class="gold-wave-divider" aria-hidden="true">
		<img src="<?php echo esc_url( \VintageSoul\Support\UrlHelper::resolve( 'assets/images/textures/border/gold-wave.svg' ) ); ?>" alt="" loading="lazy">
	</div>

	<!-- 3. FAQs Section -->
	<?php if ( ! empty( $faqs ) ) : ?>
		<section class="section contact-faq-section paper-rough">
			<div class="container container--narrow">
				<h2 class="contact-faq__title">FREQUENTLY ASKED <em>Questions</em></h2>
				<div class="faq-accordion">
					<?php foreach ( $faqs as $idx => $f ) :
						$q      = (string) ( $f['question'] ?? '' );
						$a      = (string) ( $f['answer'] ?? '' );
						$faq_id = 'faq-' . sanitize_title( $q );
					?>
						<details class="faq-accordion__item frame--ornate-sm" id="<?php echo esc_attr( $faq_id ); ?>" name="contact-faq">
							<summary class="faq-accordion__summary">
								<span class="faq-accordion__question"><?php echo esc_html( $q ); ?></span>
								<span class="faq-accordion__icon" aria-hidden="true">+</span>
							</summary>
							<div class="faq-accordion__content">
								<p><?php echo esc_html( $a ); ?></p>
							</div>
						</details>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- 4. Trust Ribbon -->
	<?php View::component( 'sections/trust-ribbon-section' ); ?>
</div>
