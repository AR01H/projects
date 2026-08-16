<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Support\UrlHelper;

$label = isset( $label ) ? (string) $label : '';
$items = isset( $items ) && is_array( $items ) ? $items : array();

if ( '' === trim( $label ) || empty( $items ) ) {
	return;
}
?>
<div class="dropdown" data-vs-dropdown>
	<button type="button" class="dropdown__trigger" aria-expanded="false" aria-haspopup="true">
		<?php echo esc_html( $label ); ?>
	</button>
	<div class="dropdown__panel" hidden>
		<?php foreach ( $items as $item ) :
			$item      = (array) $item;
			$item_label = trim( (string) ( $item['label'] ?? '' ) );
			if ( '' === $item_label ) {
				continue;
			}
		?>
			<a class="dropdown__item" href="<?php echo esc_url( UrlHelper::resolve( (string) ( $item['url'] ?? '#' ) ) ); ?>">
				<?php echo esc_html( $item_label ); ?>
			</a>
		<?php endforeach; ?>
	</div>
</div>
