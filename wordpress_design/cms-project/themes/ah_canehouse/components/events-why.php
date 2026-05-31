<?php
/**
 * "Why Choose Us" section for the events page.
 *
 * Args (all optional):
 *  tag    (string)  Eyebrow tag.            Default: 'Why Choose Us'
 *  title  (string)  Heading HTML.           Default: 'What Makes Us <span class="accent">Different</span>'
 *  body   (string)  Intro paragraph.        Default: preset copy
 *  image  (string)  Image URL.              Default: Unsplash placeholder
 *  items  (array)   Array of why-items, each: [ 'icon', 'title', 'text' ]
 */
defined( 'ABSPATH' ) || exit;

$tag   = $args['tag']   ?? 'Why Choose Us';
$title = $args['title'] ?? 'What Makes Us <span class="accent">Different</span>';
$body  = $args['body']  ?? 'We\'re not just another catering option - we\'re an experience your guests will be talking about for weeks.';
$image = $args['image'] ?? 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=600&h=700&q=80';

$default_items = [
	[
		'icon'  => '👀',
		'title' => 'Live Pressing in Front of Guests',
		'text'  => 'Watching fresh cane being pressed is a spectacle in itself - creating a natural talking point and crowd magnet at your event.',
	],
	[
		'icon'  => '🌿',
		'title' => '100% Natural - No Compromise',
		'text'  => 'Everything we serve is pure, natural, and fresh. No artificial syrups, no chemicals - just real sugarcane juice.',
	],
	[
		'icon'  => '📋',
		'title' => 'Fully Insured & Certified',
		'text'  => 'We carry full public liability insurance and comply with all food hygiene regulations - complete peace of mind for you.',
	],
	[
		'icon'  => '🤝',
		'title' => 'Flexible & Responsive',
		'text'  => 'We work around your schedule, venue, and guest count. Packages from 50 to 1,000+ guests across the UK.',
	],
];

$items   = $args['items'] ?? $default_items;
$allowed = [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ];
?>

<section class="ch-events-why-section">
	<div class="container">
		<div class="ch-why-grid">
			<div class="fade-left">
				<div class="section-tag"><?php echo esc_html( $tag ); ?></div>
				<h2 class="section-title"><?php echo wp_kses( $title, $allowed ); ?></h2>
				<p class="section-body"><?php echo esc_html( $body ); ?></p>
				<div class="ch-why-list">
					<?php foreach ( $items as $item ) : ?>
						<div class="ch-why-item">
							<div class="ch-why-icon"><?php echo esc_html( $item['icon'] ?? '✓' ); ?></div>
							<div>
								<strong><?php echo esc_html( $item['title'] ?? '' ); ?></strong>
								<p><?php echo esc_html( $item['text'] ?? '' ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="ch-why-visual fade-right">
				<img src="<?php echo esc_url( $image ); ?>"
					alt="The Cane House at an event"
					loading="lazy"
					style="width:100%;height:100%;object-fit:cover;border-radius:20px;">
			</div>
		</div>
	</div>
</section>
