<?php
/**
 * "Why Sugarcane Juice is Loved Worldwide" section.
 *
 * Args (all optional):
 *  tag    (string)  Eyebrow tag.    Default: 'Global Love'
 *  title  (string)  Heading HTML.   Default: preset
 *  body   (string)  Intro text.     Default: preset
 *  items  (array)   Benefit cards.  Default: preset list
 */
defined( 'ABSPATH' ) || exit;

$tag   = $args['tag']   ?? 'Global Love';
$title = $args['title'] ?? 'Why Sugarcane Juice is <span class="accent">Loved Worldwide</span>';
$body  = $args['body']  ?? 'From South Asian street corners to West African markets and Caribbean festivals, sugarcane juice is one of the few drinks that transcends every border, culture, and generation.';

$default_items = [
	[
		'icon'  => '⚡',
		'title' => 'Instant Natural Energy',
		'text'  => 'A single glass provides a rapid, sustained energy boost. The natural sucrose is absorbed immediately — no crash, no additives. Athletes worldwide swear by it.',
		'stat'  => '~180 kcal per glass',
	],
	[
		'icon'  => '🧬',
		'title' => 'Antioxidant Powerhouse',
		'text'  => 'Rich in polyphenols and flavonoids that fight free radicals. Raw, live-pressed juice retains these compounds — pasteurised or bottled juice loses most of them.',
		'stat'  => 'Higher than orange juice',
	],
	[
		'icon'  => '💧',
		'title' => 'Deep Hydration',
		'text'  => 'Packed with electrolytes — potassium, sodium, magnesium, calcium. Traditionally used in South Asia to treat dehydration and heat stroke during summer months.',
		'stat'  => '5 key electrolytes',
	],
	[
		'icon'  => '🌍',
		'title' => 'Loved on 5 Continents',
		'text'  => 'Brazil (caldo de cana), India (ganne ka ras), Pakistan, Egypt, West Africa, Thailand, Philippines — sugarcane juice is a global staple, not a niche trend.',
		'stat'  => '120+ countries grow cane',
	],
	[
		'icon'  => '🫀',
		'title' => 'Liver & Gut Health',
		'text'  => 'Ayurvedic tradition credits sugarcane with detoxifying the liver, aiding digestion, and treating jaundice. Modern studies support its hepatoprotective properties.',
		'stat'  => '2,000+ years of use',
	],
	[
		'icon'  => '🌱',
		'title' => 'Fully Sustainable Crop',
		'text'  => 'Sugarcane is one of the most efficient crops on earth. The pressed fibre (bagasse) is composted or used as biofuel. Zero waste from root to glass.',
		'stat'  => '90% water efficiency',
	],
];

$items   = $args['items'] ?? $default_items;
$allowed = [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ];
?>

<section class="ch-sc-benefits-section">
	<div class="container">
		<div class="ch-section-center fade-up">
			<div class="section-tag"><?php echo esc_html( $tag ); ?></div>
			<h2 class="section-title"><?php echo wp_kses( $title, $allowed ); ?></h2>
			<p class="section-body"><?php echo esc_html( $body ); ?></p>
		</div>
		<div class="ch-scb-grid fade-up">
			<?php foreach ( $items as $item ) : ?>
				<div class="ch-scb-card">
					<div class="ch-scb-icon"><?php echo esc_html( $item['icon'] ?? '🌿' ); ?></div>
					<h3 class="ch-scb-title"><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
					<p class="ch-scb-text"><?php echo esc_html( $item['text'] ?? '' ); ?></p>
					<?php if ( ! empty( $item['stat'] ) ) : ?>
						<div class="ch-scb-stat"><?php echo esc_html( $item['stat'] ); ?></div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
