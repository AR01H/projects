<?php
/**
 * Traditional Home - "Our Drinks" grid.
 *
 * Data-driven from ch_get_flavours() (the same source the booking wizard uses).
 * Each drink renders as a parchment product card: a photo when one is supplied,
 * otherwise an emoji medallion so the section never shows a broken image. Ends
 * with a "View All Drinks" CTA.
 *
 * Rendered ONLY in the traditional design (gated in front-page.php).
 */
defined( 'ABSPATH' ) || exit;

$flavours = function_exists( 'ch_get_flavours' ) ? (array) ch_get_flavours() : [];
$drinks   = array_slice( $flavours, 0, 6 );

$cta_url = apply_filters( 'ch_traditional_drinks_cta_url', home_url( '/events/' ) );
?>
<section class="ch-tdrinks" id="our-drinks">
	<div class="container">

		<div class="ch-section-header ch-section-center">
			<span class="ch-section-tag">Pressed Fresh, Served Cool</span>
			<h2 class="section-title">Our <em>Drinks</em></h2>
		</div>

		<div class="ch-tdrinks__grid">
			<?php foreach ( $drinks as $fl ) :
				$fl    = (array) $fl;
				$name  = $fl['name']  ?? '';
				$emoji = $fl['emoji'] ?? '🥤';
				$desc  = $fl['type']  ?? ( $fl['desc'] ?? '' );
				$img   = $fl['image'] ?? ( $fl['src'] ?? '' );
				if ( '' === trim( (string) $name ) ) {
					continue;
				}
			?>
				<article class="ch-tdrink-card">
					<div class="ch-tdrink-card__media">
						<?php if ( $img ) : ?>
							<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $name ); ?> sugarcane juice" loading="lazy">
						<?php else : ?>
							<span class="ch-tdrink-card__emoji"><?php echo esc_html( $emoji ); ?></span>
						<?php endif; ?>
					</div>
					<h3 class="ch-tdrink-card__name"><?php echo esc_html( $name ); ?></h3>
					<?php if ( $desc ) : ?>
						<p class="ch-tdrink-card__desc"><?php echo esc_html( $desc ); ?></p>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>

		<div class="ch-tdrinks__cta">
			<a href="<?php echo esc_url( $cta_url ); ?>" class="btn-lime">View All Drinks</a>
		</div>

	</div>
</section>
