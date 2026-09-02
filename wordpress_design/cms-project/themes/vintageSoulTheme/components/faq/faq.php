<?php
/**
 * VintageSoulTheme - Reusable FAQ Accordion Component
 * Standardized luxury vintage accordion matching Homepage, Events, Franchise, Contact & About Cane.
 */

defined( 'ABSPATH' ) || exit;

$items      = isset( $items ) && is_array( $items ) ? $items : array();
$heading    = isset( $heading ) ? (string) $heading : 'Frequently Asked Questions';
$tag        = isset( $tag ) ? (string) $tag : '';
$id         = ( isset( $id ) && '' !== trim( (string) $id ) ) ? sanitize_html_class( (string) $id ) : 'faq';
$group_name = $id . '-group';

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
<section class="section section--faq faq-vintage paper-rough" id="<?php echo esc_attr( $id ); ?>">
	<div class="container container--narrow faq-vintage__container">
		
		<?php if ( '' !== $heading || '' !== $tag ) : ?>
			<div class="faq-vintage__header">
				<?php if ( '' !== $tag ) : ?>
					<span class="section-eyebrow"><?php echo esc_html( $tag ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== $heading ) : ?>
					<h2 class="faq-vintage__title">— <?php echo wp_kses_post( $heading ); ?> —</h2>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="faq-accordion">
			<?php foreach ( $items as $idx => $item ) :
				$q      = $item['question'];
				$a      = $item['answer'];
				$faq_id = $id . '-item-' . sanitize_title( $q );
			?>
				<details class="faq-accordion__item frame--ornate-sm" id="<?php echo esc_attr( $faq_id ); ?>" name="<?php echo esc_attr( $group_name ); ?>">
					<summary class="faq-accordion__summary">
						<span class="faq-accordion__question"><?php echo esc_html( $q ); ?></span>
						<span class="faq-accordion__icon" aria-hidden="true">+</span>
					</summary>
					<div class="faq-accordion__content">
						<p><?php echo esc_html( $a ); ?></p>
					</div>
				</details>
			<?php endforeach; ?>
		</div>

	</div>
</section>
