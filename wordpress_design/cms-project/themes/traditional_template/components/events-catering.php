<?php
/**
 * Events & Catering – Vintage split layout.
 * Matches reference: text left, sepia event-stall photo right.
 */
defined( 'ABSPATH' ) || exit;

$features = [
	[ 'icon' => '🍹', 'label' => 'Live Pressing On-Site' ],
	[ 'icon' => '❄️', 'label' => 'Naturally Chilled' ],
	[ 'icon' => '🌿', 'label' => 'Unlimited Serving Options' ],
	[ 'icon' => '🛡️', 'label' => 'Fully Insured & Certified' ],
	[ 'icon' => '🚐', 'label' => 'Mobile Unit Available' ],
	[ 'icon' => '📡', 'label' => 'UK-Wide Coverage' ],
];

$photo    = 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?auto=format&fit=crop&w=900&q=80';
$book_url = home_url('/events/');
?>
<section class="nt-events-trad" id="events-catering">
	<div class="container nt-events-trad__inner">

		<div class="nt-events-trad__text">
			<span class="nt-section-tag">Events & Catering</span>
			<h2 class="nt-events-trad__title">Make Your Event<br><em>A Memory</em></h2>
			<p class="nt-events-trad__sub">Live Pressed. Naturally Refreshing.</p>

			<ul class="nt-events-trad__feature-list">
				<?php foreach ( $features as $f ) : ?>
					<li>
						<span style="font-size:1.2rem;"><?php echo esc_html($f['icon']); ?></span>
						<?php echo esc_html($f['label']); ?>
					</li>
				<?php endforeach; ?>
			</ul>

			<a href="<?php echo esc_url($book_url); ?>" class="btn">
				Book Us For Your Event &rarr;
			</a>
		</div>

		<div>
			<img src="<?php echo esc_url($photo); ?>"
				 alt="Live event stall"
				 class="nt-events-trad__img"
				 loading="lazy">
		</div>

	</div>
</section>
