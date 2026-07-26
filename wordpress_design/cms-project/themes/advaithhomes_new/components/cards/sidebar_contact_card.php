<?php
/**
 * components/cards/sidebar_contact_card.php
 *
 * Renders a contact/expert/guidance card from sidebar_cards.json
 * Uses sc-card class for consistent styling across all pages.
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
?>
<div class="sidebar-contact-card sc-card"<?php echo $inline_style ? ' style="' . esc_attr( $inline_style ) . '"' : ''; ?>>
	<div class="sc-card__icon"><i class="<?php echo esc_attr( $_c_icon ); ?>" aria-hidden="true"></i></div>
	<?php if ( '' !== $_c_head ) : ?>
		<h4 class="sc-card__title"><?php echo esc_html( $_c_head ); ?></h4>
	<?php endif; ?>
	<?php if ( '' !== $_c_desc ) : ?>
		<p class="sc-card__desc"><?php echo esc_html( $_c_desc ); ?></p>
	<?php endif; ?>
	<?php if ( '' !== $_c_btn ) : ?>
		<a href="<?php echo esc_url( home_url( $_c_url ) ); ?>" class="sc-card__btn btn btn-primary">
			<?php echo esc_html( $_c_btn ); ?>
			<i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
		</a>
	<?php endif; ?>
</div>
