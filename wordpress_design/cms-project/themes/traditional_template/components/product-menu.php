<?php
/**
 * Product menu grid - vintage flavour cards with prices.
 *
 * Reads admin/data/flavours.json -> flavours[] (name, desc, price, image).
 * Reuses the styled .nt-flavour-card look and adds a price + Order button.
 * Fully JSON-driven; renders nothing if there are no flavours.
 */
defined( 'ABSPATH' ) || exit;

$data     = nt_data( 'flavours' ) ?: array();
$flavours = $data['flavours'] ?? array();
if ( empty( $flavours ) ) {
	return;
}

$order_url = home_url( '/order/' );
?>
<section class="nt-flavours nt-menu-grid" id="menu">
	<div class="container">
		<div class="nt-flavours__header">
			<span class="nt-section-tag">Our Menu</span>
			<h2 class="nt-flavours__title">Fresh <em>By The Glass</em></h2>
			<p class="nt-flavours__sub">Every drink is pressed to order. Prices are per 250ml glass.</p>
		</div>

		<div class="nt-flavours__grid">
			<?php foreach ( $flavours as $fl ) :
				$fl    = (array) $fl;
				$name  = $fl['name'] ?? '';
				$desc  = $fl['desc'] ?? '';
				$price = $fl['price'] ?? '';
				$img   = $fl['image'] ?? '';
				if ( '' === trim( (string) $name ) ) {
					continue;
				}
			?>
				<article class="nt-flavour-card nt-menu-card">
					<?php if ( $img ) : ?>
						<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $name ); ?>" class="nt-flavour-card__img" loading="lazy">
					<?php else : ?>
						<div class="nt-flavour-card__img" style="display:flex;align-items:center;justify-content:center;font-size:3rem;background:var(--trad-bg-alt);">🥤</div>
					<?php endif; ?>
					<div class="nt-flavour-card__body">
						<div class="nt-menu-card__row">
							<h3 class="nt-flavour-card__name"><?php echo esc_html( $name ); ?></h3>
							<?php if ( $price ) : ?><span class="nt-menu-card__price"><?php echo esc_html( $price ); ?></span><?php endif; ?>
						</div>
						<?php if ( $desc ) : ?><p class="nt-flavour-card__desc"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
						<a href="<?php echo esc_url( $order_url ); ?>" class="btn nt-menu-card__btn">Order Now &rarr;</a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
