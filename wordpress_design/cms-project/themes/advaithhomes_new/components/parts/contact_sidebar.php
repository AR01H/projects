<?php
/**
 * components/parts/contact_sidebar.php
 * Props: $contact_sidebar or $page_sidebar { whatsapp{}, email{}, phone{}, faqs[], address{} }
 */
defined( 'ABSPATH' ) || exit;

$_sb = isset( $page_sidebar )    ? (array) $page_sidebar    :
       ( isset( $contact_sidebar ) ? (array) $contact_sidebar : array() );
$_wa = isset( $_sb['whatsapp'] ) ? (array) $_sb['whatsapp'] : array();
$_em = isset( $_sb['email'] )    ? (array) $_sb['email']    : array();
$_ph = isset( $_sb['phone'] )    ? (array) $_sb['phone']    : array();
?>
<aside class="contact-sidebar">

	<?php /* Single contact box: WhatsApp + Phone + Email */ ?>
	<?php
	$_wa_num  = ! empty( $_wa ) ? esc_html( isset( $_wa['number'] )       ? (string) $_wa['number']       : '' ) : '';
	$_wa_raw  = preg_replace( '/\D/', '', isset( $_wa['number'] ) ? (string) $_wa['number'] : '' );
	$_wa_href = ! empty( $_wa_raw ) ? esc_url( 'https://wa.me/' . $_wa_raw ) : '';
	$_wa_btn  = ! empty( $_wa ) ? esc_html( isset( $_wa['button_label'] ) ? (string) $_wa['button_label'] : SITE_SIDEBAR_WHATSAPP_BTN ) : '';
	$_ph_num  = ! empty( $_ph ) ? esc_html( isset( $_ph['number'] ) ? (string) $_ph['number'] : '' ) : '';
	$_ph_href = ! empty( $_ph ) ? esc_url( isset( $_ph['url'] ) ? (string) $_ph['url'] : '' ) : '';
	$_em_adr  = ! empty( $_em ) ? esc_html( isset( $_em['address'] ) ? (string) $_em['address'] : '' ) : '';
	$_em_raw  = ! empty( $_em ) ? ( isset( $_em['address'] ) ? (string) $_em['address'] : '' ) : '';
	$_em_href = '' !== $_em_raw ? 'mailto:' . sanitize_email( $_em_raw ) : '';
	$_em_btn  = ! empty( $_em ) ? esc_html( isset( $_em['button_label'] ) ? (string) $_em['button_label'] : SITE_SIDEBAR_EMAIL_BTN ) : '';
	$_has_any = '' !== $_wa_num || '' !== $_ph_num || '' !== $_em_adr;
	?>
	<?php if ( $_has_any ) : ?>
	<div class="contact-alt-box contact-alt-wa">
		<div class="contact-alt-head">
			<div class="contact-alt-icon" aria-hidden="true"><i class="fa-solid fa-address-book"></i></div>
			<h3>Contact Us</h3>
		</div>
		<div class="contact-channel-list">
			<?php if ( '' !== $_wa_num ) : ?>
			<a href="<?php echo $_wa_href; ?>" class="contact-channel-item" target="_blank" rel="noopener noreferrer">
				<span class="contact-channel-meta">
					<span class="contact-channel-icon"><i class="fa-brands fa-whatsapp"></i></span>
					<span class="contact-channel-label">WhatsApp</span>
				</span>
				<span class="contact-channel-num"><?php echo $_wa_num; ?></span>
			</a>
			<?php endif; ?>
			<?php if ( '' !== $_ph_num ) : ?>
			<a href="<?php echo $_ph_href; ?>" class="contact-channel-item">
				<span class="contact-channel-meta">
					<span class="contact-channel-icon"><i class="fa-solid fa-phone"></i></span>
					<span class="contact-channel-label">Phone</span>
				</span>
				<span class="contact-channel-num"><?php echo $_ph_num; ?></span>
			</a>
			<?php endif; ?>
			<?php if ( '' !== $_em_adr ) : ?>
			<a href="<?php echo esc_url( $_em_href ); ?>" class="contact-channel-item">
				<span class="contact-channel-meta">
					<span class="contact-channel-icon"><i class="fa-solid fa-envelope"></i></span>
					<span class="contact-channel-label">Email</span>
				</span>
				<span class="contact-channel-num contact-channel-email"><?php echo $_em_adr; ?></span>
			</a>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>

	<?php /* Address + Google Maps */ ?>
	<?php
	$_addr     = isset( $_sb['address'] ) && is_array( $_sb['address'] ) ? $_sb['address'] : array();
	$_addr_txt = isset( $_addr['text'] )     ? trim( (string) $_addr['text'] )     : '';
	$_maps_url = isset( $_addr['maps_url'] ) ? trim( (string) $_addr['maps_url'] ) : '';
	?>
	<?php if ( '' !== $_addr_txt || '' !== $_maps_url ) : ?>
	<div class="contact-alt-box contact-address-box">
		<div class="contact-alt-head">
			<div class="contact-alt-icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></div>
			<h3><?php esc_html_e( 'Our Office', ADN_TEXT_DOMAIN ); ?></h3>
		</div>
		<?php if ( '' !== $_addr_txt ) : ?>
			<p class="contact-address-text"><?php echo esc_html( $_addr_txt ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== $_maps_url ) : ?>
			<div class="contact-map-wrap">
				<iframe
					src="<?php echo esc_url( $_maps_url ); ?>"
					width="100%"
					height="200"
					style="border:0;"
					allowfullscreen=""
					loading="lazy"
					referrerpolicy="no-referrer-when-downgrade"
					title="<?php esc_attr_e( 'Office location', ADN_TEXT_DOMAIN ); ?>">
				</iframe>
			</div>
			<a href="<?php echo esc_url( $_maps_url ); ?>" class="btn btn-secondary contact-alt-btn" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Get Directions', ADN_TEXT_DOMAIN ); ?> →
			</a>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<?php /* FAQs box */ ?>
	<?php if ( ! empty( $_sb['faqs'] ) && is_array( $_sb['faqs'] ) ) :
		$_faq_heading = esc_html( isset( $_sb['faqs_heading'] ) ? (string) $_sb['faqs_heading'] : SITE_SIDEBAR_FAQS_HEAD );
		$_faq_view_url = adn_link( SITE_FAQS_URL );
		if ( isset( $_sb['faqs_url'] ) && '' !== (string) $_sb['faqs_url'] ) {
			$_faq_view_url = adn_link( (string) $_sb['faqs_url'] );
		}
		$_faq_view_label = isset( $_sb['faqs_button_label'] ) && '' !== (string) $_sb['faqs_button_label']
			? (string) $_sb['faqs_button_label']
			: SITE_SIDEBAR_VIEW_FAQS;
	?>
	<div class="contact-alt-box contact-alt-faqs">
		<h3><?php echo $_faq_heading; ?></h3>
		<div class="contact-faq-list">
			<?php foreach ( $_sb['faqs'] as $_faq ) :
				$_faq_question  = isset( $_faq->question )  ? (string) $_faq->question  : ( isset( $_faq['question'] )  ? (string) $_faq['question']  : '' );
				$_faq_answer    = isset( $_faq->answer )    ? (string) $_faq->answer    : ( isset( $_faq['answer'] )    ? (string) $_faq['answer']    : '' );
				$_faq_link_url  = is_object( $_faq ) ? ( isset( $_faq->link_url )  ? (string) $_faq->link_url  : '' ) : ( isset( $_faq['link_url'] )  ? (string) $_faq['link_url']  : '' );
				$_faq_link_text = is_object( $_faq ) ? ( isset( $_faq->link_text ) ? (string) $_faq->link_text : '' ) : ( isset( $_faq['link_text'] ) ? (string) $_faq['link_text'] : '' );
				if ( '' === trim( $_faq_question ) ) { continue; }
			?>
				<details class="contact-faq-item">
					<summary class="contact-faq-question"><?php echo esc_html( $_faq_question ); ?></summary>
					<div class="contact-faq-a">
						<?php if ( '' !== trim( $_faq_answer ) ) : ?>
							<p class="contact-faq-answer"><?php echo esc_html( wp_trim_words( $_faq_answer, 40, '...' ) ); ?></p>
						<?php endif; ?>
						<?php if ( '' !== trim( $_faq_link_url ) ) : ?>
							<p class="contact-faq-small-link"><a href="<?php echo esc_url( adn_link( $_faq_link_url ) ); ?>"><?php echo esc_html( $_faq_link_text ?: $_faq_link_url ); ?></a></p>
						<?php endif; ?>
					</div>
				</details>
			<?php endforeach; ?>
		</div>
		<a href="<?php echo esc_url( $_faq_view_url ); ?>" class="btn btn-secondary contact-alt-btn contact-alt-faq-viewall">
			<?php echo esc_html( $_faq_view_label ); ?> →
		</a>
	</div>
	<?php endif; ?>


</aside>
<!-- Sidebar FAQ interactions moved to /assets/js/faqs.js -->
