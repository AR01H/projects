<?php
/**
 * components/cards/tool_list_item.php - Calculator grid card (icon + title + desc + arrow).
 */

defined( 'ABSPATH' ) || exit;

$item      = isset( $item ) && is_array( $item ) ? $item : array();
$cats_raw  = isset( $item['categories'] ) ? (array) $item['categories'] : array();
$cats_safe = implode( ' ', array_map( 'sanitize_key', $cats_raw ) );
$url       = esc_url( adn_link( isset( $item['url'] ) ? $item['url'] : '' ) );
$title     = isset( $item['title'] ) ? (string) $item['title'] : '';
$desc      = isset( $item['desc'] )  ? (string) $item['desc']  : '';
$icon      = isset( $item['icon'] )  ? $item['icon']           : '';
?>
<a
	href="<?php echo $url; ?>"
	class="calc-list-item"
	data-category="<?php echo esc_attr( $cats_safe ); ?>"
>
	<div class="calc-list-item-left">
		<div class="calc-list-icon" aria-hidden="true">
			<?php echo adn_icon( $icon ); ?>
		</div>
		<div class="calc-list-text">
			<?php if ( $title ) : ?>
				<h4><?php echo esc_html( $title ); ?></h4>
			<?php endif; ?>
		</div>
	</div>
	<?php if ( $desc ) : ?>
		<div class="calc-list-desc">
			<p><?php echo esc_html( $desc ); ?></p>
		</div>
	<?php endif; ?>
	<div class="calc-list-item-bottom">
		<span class="calc-list-arrow" aria-hidden="true">&rarr;</span>
	</div>
</a>

