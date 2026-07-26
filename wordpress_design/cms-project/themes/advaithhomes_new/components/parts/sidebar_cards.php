<?php
/**
 * components/parts/sidebar_cards.php - Sidebar: Quick-access CTA cards from sidebar_cards.json.
 *
 * Props: $sidebar_cards { items: [ { icon, heading, description, button_label, url, class } ] }
 */
defined( 'ABSPATH' ) || exit;

$sidebar_cards = isset( $sidebar_cards ) && is_array( $sidebar_cards ) ? $sidebar_cards : array();
$items = isset( $sidebar_cards['items'] ) ? (array) $sidebar_cards['items'] : array();

if ( empty( $items ) ) { return; }
?>
<div class="sc-cards">
	<?php foreach ( $items as $card ) :
		$_icon   = isset( $card['icon'] )   ? (string) $card['icon']   : '';
		$_title  = isset( $card['heading'] ) ? (string) $card['heading'] : '';
		$_desc   = isset( $card['description'] ) ? (string) $card['description'] : '';
		$_label  = isset( $card['button_label'] ) ? (string) $card['button_label'] : '';
		$_url    = isset( $card['url'] )  ? (string) $card['url']  : '#';
		if ( '' === $_title && '' === $_label ) { continue; }
	?>
	<div class="sc-card">
		<?php if ( '' !== $_icon ) : ?>
			<div class="sc-card__icon"><i class="<?php echo esc_attr( $_icon ); ?>" aria-hidden="true"></i></div>
		<?php endif; ?>
		<?php if ( '' !== $_title ) : ?>
			<h4 class="sc-card__title"><?php echo esc_html( $_title ); ?></h4>
		<?php endif; ?>
		<?php if ( '' !== $_desc ) : ?>
			<p class="sc-card__desc"><?php echo esc_html( $_desc ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== $_label ) : ?>
			<a href="<?php echo esc_url( adn_link( $_url ) ); ?>" class="sc-card__btn btn btn-primary">
				<?php echo esc_html( $_label ); ?>
				<i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
			</a>
		<?php endif; ?>
	</div>
	<?php endforeach; ?>
</div>
