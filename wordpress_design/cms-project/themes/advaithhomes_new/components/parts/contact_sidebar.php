<?php
/**
 * components/parts/contact_sidebar.php
 * Props: $contact_sidebar or $page_sidebar { whatsapp{}, email{}, faqs[], coming_soon[] }
 * Either prop name works — use $page_sidebar when including from non-contact pages.
 */
defined( 'ABSPATH' ) || exit;

$_sb = isset( $page_sidebar )    ? (array) $page_sidebar    :
       ( isset( $contact_sidebar ) ? (array) $contact_sidebar : array() );
$_wa = isset( $_sb['whatsapp'] )     ? (array) $_sb['whatsapp']     : array();
$_em = isset( $_sb['email'] )        ? (array) $_sb['email']        : array();
$_cs = isset( $_sb['coming_soon'] )  ? (array) $_sb['coming_soon']  : array();
?>
<aside class="contact-sidebar">

	<?php /* WhatsApp box */ ?>
	<?php if ( ! empty( $_wa ) ) :
		$_wa_ico = adn_icon( isset( $_wa['icon'] )         ? (string) $_wa['icon']         : '💬' );
		$_wa_hdg = esc_html( isset( $_wa['heading'] )      ? (string) $_wa['heading']      : '' );
		$_wa_num = esc_html( isset( $_wa['number'] )       ? (string) $_wa['number']       : '' );
		$_wa_nte = esc_html( isset( $_wa['note'] )         ? (string) $_wa['note']         : '' );
		$_wa_btn = esc_html( isset( $_wa['button_label'] ) ? (string) $_wa['button_label'] : SITE_SIDEBAR_WHATSAPP_BTN );
		$_wa_url = esc_url( adn_link( isset( $_wa['url'] ) ? (string) $_wa['url'] : '#' ) );
		$_wa_raw = preg_replace( '/\D/', '', isset( $_wa['number'] ) ? (string) $_wa['number'] : '' );
		$_wa_href = ! empty( $_wa_raw ) ? 'https://wa.me/' . $_wa_raw : $_wa_url;
	?>
	<div class="contact-alt-box contact-alt-wa">
		<div class="contact-alt-icon" aria-hidden="true"><?php echo $_wa_ico; ?></div>
		<h3><?php echo $_wa_hdg; ?></h3>
		<?php if ( '' !== $_wa_num ) : ?>
			<p class="contact-alt-number"><?php echo $_wa_num; ?></p>
		<?php endif; ?>
		<?php if ( '' !== $_wa_nte ) : ?>
			<p class="contact-alt-note"><?php echo $_wa_nte; ?></p>
		<?php endif; ?>
		<a href="<?php echo esc_url( $_wa_href ); ?>" class="btn btn-secondary contact-alt-btn" target="_blank" rel="noopener noreferrer">
			<?php echo $_wa_btn; ?> →
		</a>
	</div>
	<?php endif; ?>

	<?php /* Email box */ ?>
	<?php if ( ! empty( $_em ) ) :
		$_em_ico = adn_icon( isset( $_em['icon'] )         ? (string) $_em['icon']         : '📧' );
		$_em_hdg = esc_html( isset( $_em['heading'] )      ? (string) $_em['heading']      : '' );
		$_em_adr = esc_html( isset( $_em['address'] )      ? (string) $_em['address']      : '' );
		$_em_nte = esc_html( isset( $_em['note'] )         ? (string) $_em['note']         : '' );
		$_em_btn = esc_html( isset( $_em['button_label'] ) ? (string) $_em['button_label'] : SITE_SIDEBAR_EMAIL_BTN );
		$_em_raw = isset( $_em['address'] ) ? (string) $_em['address'] : '';
		$_em_href = ( '' !== $_em_raw ) ? 'mailto:' . sanitize_email( $_em_raw ) : '#';
	?>
	<div class="contact-alt-box">
		<div class="contact-alt-icon" aria-hidden="true"><?php echo $_em_ico; ?></div>
		<h3><?php echo $_em_hdg; ?></h3>
		<?php if ( '' !== $_em_adr ) : ?>
			<p class="contact-alt-number"><a href="<?php echo esc_url( $_em_href ); ?>"><?php echo $_em_adr; ?></a></p>
		<?php endif; ?>
		<?php if ( '' !== $_em_nte ) : ?>
			<p class="contact-alt-note"><?php echo $_em_nte; ?></p>
		<?php endif; ?>
		<a href="<?php echo esc_url( $_em_href ); ?>" class="btn btn-secondary contact-alt-btn">
			<?php echo $_em_btn; ?> →
		</a>
	</div>
	<?php endif; ?>

	<?php /* FAQs box */ ?>
	<?php if ( ! empty( $_sb['faqs'] ) && is_array( $_sb['faqs'] ) ) :
		$_faq_heading = esc_html( isset( $_sb['faqs_heading'] ) ? (string) $_sb['faqs_heading'] : SITE_SIDEBAR_FAQS_HEAD );
	?>
	<?php
		// Configurable view-all URL/label for FAQs
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
					$_faq_question = isset( $_faq->question ) ? (string) $_faq->question : ( isset( $_faq['question'] ) ? (string) $_faq['question'] : '' );
					$_faq_answer = isset( $_faq->answer ) ? (string) $_faq->answer : ( isset( $_faq['answer'] ) ? (string) $_faq['answer'] : '' );
					// Optional link fields from DB: link_url, link_text
					$_faq_link_url  = '';
					$_faq_link_text = '';
					if ( is_object( $_faq ) ) {
						$_faq_link_url  = isset( $_faq->link_url ) ? (string) $_faq->link_url : '';
						$_faq_link_text = isset( $_faq->link_text ) ? (string) $_faq->link_text : '';
					} else {
						$_faq_link_url  = isset( $_faq['link_url'] ) ? (string) $_faq['link_url'] : '';
						$_faq_link_text = isset( $_faq['link_text'] ) ? (string) $_faq['link_text'] : '';
					}
				if ( '' === trim( $_faq_question ) ) {
					continue;
				}
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
    

	<?php /* Coming Soon */ ?>
	<?php if ( ! empty( $_cs ) ) : ?>
	<div class="contact-alt-box contact-coming-soon-box">
		<h4><?php esc_html_e( 'More ways to connect', ADN_TEXT_DOMAIN ); ?> <span class="cs-tag"><?php esc_html_e( 'Coming Soon', ADN_TEXT_DOMAIN ); ?></span></h4>
		<ul class="contact-cs-list">
			<?php foreach ( $_cs as $_c ) :
				$_c_ico = adn_icon( isset( $_c['icon'] )  ? (string) $_c['icon']  : '' );
				$_c_lbl = esc_html( isset( $_c['label'] ) ? (string) $_c['label'] : '' );
			?>
				<li>
					<span class="cs-icon" aria-hidden="true"><?php echo $_c_ico; ?></span>
					<span><?php echo $_c_lbl; ?></span>
					<span class="cs-pill"><?php esc_html_e( 'Coming Soon', ADN_TEXT_DOMAIN ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php endif; ?>

	</aside>
	<!-- Sidebar FAQ interactions moved to /assets/js/faqs.js -->
