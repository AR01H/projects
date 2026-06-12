<?php
/**
 * components/cards/calc_list_item.php — Calculator list row (icon, title, desc, arrow).
 *
 * Props: $item { icon, categories[], title, desc, url }
 * categories[] values are joined into a space-separated data-category attribute
 * used by calculators.js for client-side filtering.
 *
 * Usage: adn_component( 'cards/calc_list_item', array( 'item' => $item ) );
 */

defined( 'ABSPATH' ) || exit;

$item       = isset( $item ) && is_array( $item ) ? $item : array();
$cats_raw   = isset( $item['categories'] ) ? (array) $item['categories'] : array();
$cats_safe  = implode( ' ', array_map( 'sanitize_key', $cats_raw ) );
$url        = esc_url( adn_link( isset( $item['url'] ) ? $item['url'] : '' ) );
?>
<a
	href="<?php echo $url; ?>"
	class="calc-list-item"
	data-category="<?php echo esc_attr( $cats_safe ); ?>"
>
	<div class="calc-list-item-left">
		<div class="calc-list-icon" aria-hidden="true">
			<?php echo adn_icon( isset( $item['icon'] ) ? $item['icon'] : '' ); ?>
		</div>
		<div class="calc-list-text">
			<?php if ( ! empty( $item['title'] ) ) : ?>
				<h4><?php echo esc_html( $item['title'] ); ?></h4>
			<?php endif; ?>
			<?php if ( ! empty( $item['desc'] ) ) : ?>
				<p><?php echo esc_html( $item['desc'] ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<span class="calc-list-arrow" aria-hidden="true">›</span>
</a>
