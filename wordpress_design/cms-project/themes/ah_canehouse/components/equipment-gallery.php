<?php
/**
 * Equipment / machine gallery section for the About page.
 *
 * Args (all optional):
 *  tag    (string)  Eyebrow tag.    Default: 'Our Setup'
 *  title  (string)  Heading HTML.   Default: preset
 *  body   (string)  Intro text.     Default: preset
 *  items  (array)   Gallery items: [ 'image', 'label', 'desc' ]
 */
defined( 'ABSPATH' ) || exit;

$tag   = $args['tag']   ?? 'Our Setup';
$title = $args['title'] ?? 'The <span class="accent">Machine</span> Behind the Magic';
$body  = $args['body']  ?? 'Every glass starts with our purpose-built stainless steel press. Hygienic, powerful, and built to handle high volumes at live events without missing a beat.';

$default_items = [];
// Load gallery data from JSON if available
$json_data = CH_Real_Loader::json('equipment-gallery');
if ( is_array( $json_data ) && isset( $json_data['items'] ) ) {
    $default_items = $json_data['items'];
}

$items   = $args['items'] ?? $default_items;
$allowed = [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ];
?>

<section class="ch-equipment-section">
	<div class="container">
		<?php get_template_part( 'components/section-header', null, [
			'tag'   => $tag,
			'title' => $title,
			'body'  => $body,
		] ); ?>

		<div class="ch-equip-gallery fade-up">
			<!-- Featured large item -->
			<?php $first = $items[0]; ?>
			<div class="ch-equip-featured">
				<img src="<?php echo esc_url( $first['image'] ?? '' ); ?>"
					alt="<?php echo esc_attr( $first['label'] ?? '' ); ?>"
					loading="lazy" class="ch-equip-img">
				<div class="ch-equip-caption">
					<strong><?php echo esc_html( $first['label'] ?? '' ); ?></strong>
					<span><?php echo esc_html( $first['desc'] ?? '' ); ?></span>
				</div>
			</div>

			<!-- Supporting grid -->
			<div class="ch-equip-grid">
				<?php foreach ( array_slice( $items, 1 ) as $item ) : ?>
					<div class="ch-equip-item">
						<img src="<?php echo esc_url( $item['image'] ?? '' ); ?>"
							alt="<?php echo esc_attr( $item['label'] ?? '' ); ?>"
							loading="lazy" class="ch-equip-img">
						<div class="ch-equip-caption">
							<strong><?php echo esc_html( $item['label'] ?? '' ); ?></strong>
							<span><?php echo esc_html( $item['desc'] ?? '' ); ?></span>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
