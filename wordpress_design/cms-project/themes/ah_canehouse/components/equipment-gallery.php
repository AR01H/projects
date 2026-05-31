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

$default_items = [
	[
		'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=600&h=450&q=80',
		'label' => 'Commercial Press',
		'desc'  => 'Heavy-duty stainless steel sugarcane press. Processes a full cane stalk in seconds.',
	],
	[
		'image' => 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=600&h=450&q=80',
		'label' => 'Event Stall Setup',
		'desc'  => 'Fully branded, mobile setup that fits in any venue. Ready to serve within 30 minutes.',
	],
	[
		'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=600&h=450&q=80',
		'label' => 'Live Pressing',
		'desc'  => 'Every cup pressed fresh to order, right in front of your guests.',
	],
	[
		'image' => 'https://images.unsplash.com/photo-1546833998-877b37c2e5c6?auto=format&fit=crop&w=600&h=450&q=80',
		'label' => 'Fresh Ingredients',
		'desc'  => 'Whole sugarcane stalks sourced fresh. Optional ginger, lemon, and mint pairings.',
	],
];

$items   = $args['items'] ?? $default_items;
$allowed = [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ];
?>

<section class="ch-equipment-section">
	<div class="container">
		<div class="ch-section-center fade-up">
			<div class="section-tag"><?php echo esc_html( $tag ); ?></div>
			<h2 class="section-title"><?php echo wp_kses( $title, $allowed ); ?></h2>
			<p class="section-body"><?php echo esc_html( $body ); ?></p>
		</div>

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
