<?php

defined( 'ABSPATH' ) || exit;

$items   = isset( $items ) && is_array( $items ) ? $items : array();
$heading = isset( $heading ) ? (string) $heading : '';
$id      = ( isset( $id ) && '' !== trim( (string) $id ) ) ? sanitize_html_class( (string) $id ) : 'faq';

$items = array_values(
	array_filter(
		array_map(
			static function ( $item ) {
				$item = (array) $item;
				return array(
					'question' => trim( (string) ( $item['question'] ?? '' ) ),
					'answer'   => trim( (string) ( $item['answer'] ?? '' ) ),
				);
			},
			$items
		),
		static function ( $item ) {
			return '' !== $item['question'] && '' !== $item['answer'];
		}
	)
);

if ( empty( $items ) ) {
	return;
}
?>
<?php

?>
<div class="faq tex-leaf-fall-a">
	<?php if ( '' !== $heading ) : ?>
		<h2 class="faq__heading"><?php echo esc_html( $heading ); ?></h2>
	<?php endif; ?>
	<div class="faq__list">
		<?php foreach ( $items as $i => $item ) :
			$panel_id = $id . '-panel-' . $i;
		?>
			<div class="faq__item">
				<h3 class="faq__question">
					<button type="button" class="faq__trigger" aria-expanded="false" aria-controls="<?php echo esc_attr( $panel_id ); ?>">
						<span><?php echo esc_html( $item['question'] ); ?></span>
						<span class="faq__icon" aria-hidden="true"></span>
					</button>
				</h3>
				<div class="faq__panel" id="<?php echo esc_attr( $panel_id ); ?>">
					<div class="faq__panel-inner">
						<p><?php echo esc_html( $item['answer'] ); ?></p>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
