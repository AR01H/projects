<?php
/**
 * Traditional Home - "Our Story" three-column block.
 *
 *   Left   : a pinned Polaroid photo + a "Made Fresh / Every Day" round stamp.
 *   Centre : heading + script subtitle + body copy.
 *   Right  : a sepia press-machine photo (decorative).
 *
 * Rendered ONLY in the traditional design (gated in front-page.php). Copy comes
 * from CH_Data::story_settings() where available, with mockup-matching defaults.
 */
defined( 'ABSPATH' ) || exit;

$ss = class_exists( 'CH_Data' ) ? (array) CH_Data::story_settings() : [];

$gallery = function_exists( 'ch_get_equipment_media_gallery' ) ? (array) ch_get_equipment_media_gallery() : [];
$first   = (array) ( $gallery[0] ?? [] );
$photo   = $first['src'] ?? $first['url'] ?? $first['image'] ?? '';
if ( ! $photo ) {
	$photo = 'https://images.unsplash.com/photo-1571934811356-5cc061b6821f?auto=format&fit=crop&w=700&q=80';
}

$machine = 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=700&q=80';

$paras = [
	$ss['body_1'] ?? 'The Cane House was born from simple memories of childhood summers - the sound of a sugarcane machine, the joy of watching fresh juice being made, and the taste that stayed with us forever.',
	$ss['body_2'] ?? 'Today, we carry that tradition forward, serving fresh, natural sugarcane juice while you watch.',
	'A taste of home. A tradition shared across generations.',
];
?>
<section class="ch-tstory" id="our-story">
	<div class="container ch-tstory__inner">

		<figure class="ch-tstory__photo ch-photo--trad fade-left">
			<img src="<?php echo esc_url( $photo ); ?>" alt="A glass of fresh sugarcane juice" loading="lazy">
			<span class="ch-tstory__stamp" aria-hidden="true">
				<span>Made Fresh</span>
				<strong>Every Day</strong>
			</span>
		</figure>

		<div class="ch-tstory__text fade-up">
			<span class="ch-section-tag">Our Heritage</span>
			<h2 class="ch-tstory__heading">Our <em>Story</em></h2>
			<p class="ch-tstory__script">Bringing Back Memories For Some<br>Creating New Memories For Others</p>
			<?php foreach ( $paras as $p ) : ?>
				<p class="ch-tstory__body"><?php echo esc_html( $p ); ?></p>
			<?php endforeach; ?>
		</div>

		<div class="ch-tstory__machine fade-right" aria-hidden="true">
			<img src="<?php echo esc_url( $machine ); ?>" alt="" loading="lazy">
		</div>

	</div>
</section>
