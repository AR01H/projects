<?php
/**
 * Our Drinks – Vintage Flavours Grid
 * Matches reference: "Our Signature Flavours" product card grid.
 */
defined( 'ABSPATH' ) || exit;

$data     = NT_Data_Provider::get('flavours') ?: [];
$flavours = $data['flavours'] ?? [];
$drinks   = array_slice( $flavours, 0, 6 );

if ( empty($drinks) ) return;
?>
<section class="app-flavours" id="our-drinks">
	<div class="container">

		<div class="app-flavours__header">
			<span class="app-section-tag">Pressed Fresh, Served Cool</span>
			<h2 class="app-flavours__title">Our <em>Signature Flavours</em></h2>
			<p class="app-flavours__sub">Crafted naturally. Served perfectly.</p>
		</div>

		<div class="app-flavours__grid">
			<?php foreach ( $drinks as $fl ) :
				$fl   = (array) $fl;
				$name = $fl['name'] ?? '';
				$desc = $fl['desc'] ?? ( $fl['type'] ?? '' );
				$img  = $fl['image'] ?? ( $fl['src'] ?? '' );
				if ( '' === trim( (string) $name ) ) continue;
			?>
			<article class="app-flavour-card">
				<?php if ( $img ) : ?>
					<img src="<?php echo esc_url( $img ); ?>"
						 alt="<?php echo esc_attr( $name ); ?>"
						 class="app-flavour-card__img"
						 loading="lazy">
				<?php else : ?>
					<div class="app-flavour-card__img" style="background: var(--trad-bg-alt); display:flex; align-items:center; justify-content:center; font-size:3rem;">🥤</div>
				<?php endif; ?>
				<div class="app-flavour-card__body">
					<h3 class="app-flavour-card__name"><?php echo esc_html( $name ); ?></h3>
					<?php if ( $desc ) : ?>
						<p class="app-flavour-card__desc"><?php echo esc_html( $desc ); ?></p>
					<?php endif; ?>
				</div>
			</article>
			<?php endforeach; ?>
		</div>

		<div style="text-align:center; margin-top: 40px;">
			<a href="<?php echo esc_url( home_url('/menu/') ); ?>" class="btn">
				View All Flavours &rarr;
			</a>
		</div>

	</div>
</section>
