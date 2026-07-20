<?php
/**
 * Our Story – Vintage three-column layout.
 * Matches reference: parchment section with photo, text, and press machine.
 */
defined( 'ABSPATH' ) || exit;

$about = NT_Data_Provider::get('about');
$about = ( is_array($about) && !empty($about) ) ? (array) $about[0] : [];

$photo   = $about['image']          ?? 'https://images.unsplash.com/photo-1571934811356-5cc061b6821f?auto=format&fit=crop&w=700&q=80';
$machine = $about['machine_image']  ?? 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=700&q=80';

$paras = [
	$about['body_1'] ?? 'The concept was born from simple memories of childhood summers – the sound of a traditional press, the joy of watching fresh juice being made, and the taste that stayed with us forever.',
	$about['body_2'] ?? 'Today, we carry that tradition forward, serving fresh, natural refreshments while you watch – pure cane, pressed right in front of you.',
	'A taste of home. A tradition shared across generations.',
];
?>
<section class="nt-tstory" id="our-story">
	<div class="container nt-tstory__inner">

		<!-- Left: Vintage photo with stamp -->
		<figure class="nt-tstory__photo">
			<img src="<?php echo esc_url( $photo ); ?>" alt="Our story photo" loading="lazy">
			<span class="nt-tstory__stamp" aria-hidden="true">
				<span>Made Fresh</span>
				<strong>Every Day</strong>
			</span>
		</figure>

		<!-- Centre: text content -->
		<div class="nt-tstory__text">
			<span class="nt-section-tag">Our Heritage</span>
			<h2 class="nt-tstory__heading">
				Our <em>Story</em>
			</h2>
			<p class="nt-tstory__script">
				Bringing Back Memories For Some<br>
				Creating New Memories For Others
			</p>
			<?php foreach ( $paras as $p ) : ?>
				<p class="nt-tstory__body"><?php echo esc_html( $p ); ?></p>
			<?php endforeach; ?>
			<a href="<?php echo esc_url( home_url('/about/') ); ?>" class="btn">
				Read Our Story &rarr;
			</a>
		</div>

		<!-- Right: press machine photo -->
		<div class="nt-tstory__machine" aria-hidden="true">
			<img src="<?php echo esc_url( $machine ); ?>" alt="" loading="lazy">
		</div>

	</div>
</section>
