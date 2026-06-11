<?php
/**
 * components/parts/contact_sidebar.php
 * Props: $contact_sidebar { whatsapp{}, email{}, coming_soon[] }
 */
defined( 'ABSPATH' ) || exit;

$_sb = isset( $contact_sidebar ) ? (array) $contact_sidebar : array();
$_wa = isset( $_sb['whatsapp'] )     ? (array) $_sb['whatsapp']     : array();
$_em = isset( $_sb['email'] )        ? (array) $_sb['email']        : array();
$_cs = isset( $_sb['coming_soon'] )  ? (array) $_sb['coming_soon']  : array();
?>
<aside class="contact-sidebar">

	<?php /* WhatsApp box */ ?>
	<?php if ( ! empty( $_wa ) ) :
		$_wa_ico = esc_html( isset( $_wa['icon'] )         ? (string) $_wa['icon']         : '💬' );
		$_wa_hdg = esc_html( isset( $_wa['heading'] )      ? (string) $_wa['heading']      : '' );
		$_wa_num = esc_html( isset( $_wa['number'] )       ? (string) $_wa['number']       : '' );
		$_wa_nte = esc_html( isset( $_wa['note'] )         ? (string) $_wa['note']         : '' );
		$_wa_btn = esc_html( isset( $_wa['button_label'] ) ? (string) $_wa['button_label'] : 'Start WhatsApp Chat' );
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
		$_em_ico = esc_html( isset( $_em['icon'] )         ? (string) $_em['icon']         : '📧' );
		$_em_hdg = esc_html( isset( $_em['heading'] )      ? (string) $_em['heading']      : '' );
		$_em_adr = esc_html( isset( $_em['address'] )      ? (string) $_em['address']      : '' );
		$_em_nte = esc_html( isset( $_em['note'] )         ? (string) $_em['note']         : '' );
		$_em_btn = esc_html( isset( $_em['button_label'] ) ? (string) $_em['button_label'] : 'Send an Email' );
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

	<?php /* Coming Soon */ ?>
	<?php if ( ! empty( $_cs ) ) : ?>
	<div class="contact-alt-box contact-coming-soon-box">
		<h4><?php esc_html_e( 'More ways to connect', ADN_TEXT_DOMAIN ); ?> <span class="cs-tag"><?php esc_html_e( 'Coming Soon', ADN_TEXT_DOMAIN ); ?></span></h4>
		<ul class="contact-cs-list">
			<?php foreach ( $_cs as $_c ) :
				$_c_ico = esc_html( isset( $_c['icon'] )  ? (string) $_c['icon']  : '' );
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
