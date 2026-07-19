<?php
/**
 * components/cards/sidebar_contact_card.php
 *
 * Renders a contact/expert/guidance card from sidebar_cards.json
 *
 * Props:
 *   $card         array  The card data (icon, heading, description, button_label, url, class)
 *   $inline_style string Optional inline style for the wrapper
 */

defined( 'ABSPATH' ) || exit;

$card         = isset( $card ) && is_array( $card ) ? $card : array();
$inline_style = isset( $inline_style ) ? (string) $inline_style : '';

if ( empty( $card ) ) {
	return;
}

$_c_icon = ! empty( $card['icon'] ) ? (string) $card['icon'] : 'fa-solid fa-circle-info';
$_c_head = ! empty( $card['heading'] ) ? (string) $card['heading'] : '';
$_c_desc = ! empty( $card['description'] ) ? (string) $card['description'] : '';
$_c_btn  = ! empty( $card['button_label'] ) ? (string) $card['button_label'] : '';
$_c_url  = ! empty( $card['url'] ) ? (string) $card['url'] : '';
$_c_cls  = ! empty( $card['class'] ) ? (string) $card['class'] : '';
?>
<div class="contact-alt-box<?php echo $_c_cls ? ' ' . esc_attr( $_c_cls ) : ''; ?>"<?php echo $inline_style ? ' style="' . esc_attr( $inline_style ) . '"' : ''; ?>>
	<div class="contact-alt-head">
		<div class="contact-alt-icon" aria-hidden="true"><i class="<?php echo esc_attr( $_c_icon ); ?>"></i></div>
		<h3><?php echo esc_html( $_c_head ); ?></h3>
	</div>
	<p class="contact-guidance-text"><?php echo esc_html( $_c_desc ); ?></p>
	<a href="<?php echo esc_url( home_url( $_c_url ) ); ?>" class="btn btn-primary contact-alt-btn">
		<?php echo esc_html( $_c_btn ); ?> &rarr;
	</a>
</div>
