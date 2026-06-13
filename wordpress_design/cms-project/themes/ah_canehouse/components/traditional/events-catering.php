<?php
/**
 * Traditional Home - "Events & Catering" split block.
 *
 *   Left  : heading + script line + a row of feature icons + "Book Us" CTA.
 *   Right : a sepia event-stall photo (matted Polaroid frame).
 *
 * Rendered ONLY in the traditional design (gated in front-page.php). The feature
 * list is filterable so it stays data-driven.
 */
defined( 'ABSPATH' ) || exit;

$features = apply_filters( 'ch_traditional_event_features', [
	[ 'icon' => '🍹', 'label' => 'Live Pressing' ],
	[ 'icon' => '🧼', 'label' => 'Fresh & Hygienic' ],
	[ 'icon' => '👨‍🍳', 'label' => 'Trained Staff' ],
	[ 'icon' => '🎁', 'label' => 'Custom Packages' ],
	[ 'icon' => '🎉', 'label' => 'Perfect For Any Occasion' ],
] );

$photo   = apply_filters( 'ch_traditional_events_photo', 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?auto=format&fit=crop&w=900&q=80' );
$book_url = home_url( '/events/' );
?>
<section class="ch-tevents" id="events-catering">
	<div class="container ch-tevents__inner">

		<div class="ch-tevents__text fade-left">
			<span class="ch-section-tag">Events &amp; Catering</span>
			<h2 class="ch-tevents__heading">Make Your Event <em>Memorable</em></h2>
			<p class="ch-tevents__script">with our live sugarcane juice stall</p>

			<div class="ch-tevents__features">
				<?php foreach ( $features as $f ) :
					$f = (array) $f; ?>
					<div class="ch-tevents__feature">
						<span class="ch-tevents__feature-icon"><?php echo esc_html( $f['icon'] ?? '🌿' ); ?></span>
						<span class="ch-tevents__feature-label"><?php echo esc_html( $f['label'] ?? '' ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>

			<a href="<?php echo esc_url( $book_url ); ?>" class="btn-lime">🌿 Book Us For Your Event</a>
		</div>

		<div class="ch-tevents__visual ch-photo--trad fade-right">
			<img src="<?php echo esc_url( $photo ); ?>" alt="The Cane House live event stall" loading="lazy">
		</div>

	</div>
</section>
