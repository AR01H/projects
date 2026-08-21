<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Support\View;

$known_social_icons = array( 'facebook', 'instagram', 'whatsapp', 'youtube' );
?>
<footer class="site-footer" role="contentinfo">
	<span class="site-footer__corner site-footer__corner--left" aria-hidden="true"></span>
	<span class="site-footer__corner site-footer__corner--right" aria-hidden="true"></span>
	<?php if ( '' !== $brand_bg ) : ?>
		<?php

		?>
		<span class="site-footer__brand-bg" aria-hidden="true" style="--footer-brand-bg-image: url('<?php echo esc_url( VINTAGESOUL_URI . '/' . ltrim( $brand_bg, '/' ) ); ?>');"></span>
	<?php endif; ?>

	<div class="container">
		<div class="site-footer__grid">

			<div class="site-footer__col site-footer__col--brand">
				<?php View::component( 'logo/logo', array( 'context' => 'footer' ) ); ?>
				<?php if ( '' !== $tagline ) : ?>
					<p class="site-footer__tagline"><?php echo esc_html( $tagline ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $quick_links ) ) : ?>
				<div class="site-footer__col">
					<h2 class="site-footer__heading"><?php echo esc_html( $labels['quick_links'] ); ?></h2>
					<ul class="nav__list nav__list--vertical">
						<?php foreach ( $quick_links as $link ) : ?>
							<li><a class="nav__link" href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $items ) ) : ?>
				<div class="site-footer__col">
					<h2 class="site-footer__heading"><?php echo esc_html( $labels['items'] ); ?></h2>
					<ul class="nav__list nav__list--vertical">
						<?php foreach ( $items as $link ) : ?>
							<li><a class="nav__link" href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<div class="site-footer__col">
				<h2 class="site-footer__heading"><?php echo esc_html( $labels['contact'] ); ?></h2>
				<?php if ( '' !== $phone ) : ?>
					<p class="site-footer__detail">
						<span class="site-footer__detail-icon site-footer__detail-icon--phone" aria-hidden="true"></span>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
					</p>
				<?php endif; ?>
				<?php if ( '' !== $email ) : ?>
					<p class="site-footer__detail">
						<span class="site-footer__detail-icon site-footer__detail-icon--mail" aria-hidden="true"></span>
						<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
					</p>
				<?php endif; ?>
				<?php if ( '' !== $address ) : ?>
					<p class="site-footer__detail">
						<span class="site-footer__detail-icon site-footer__detail-icon--map-pin" aria-hidden="true"></span>
						<?php echo esc_html( $address ); ?>
					</p>
				<?php endif; ?>

				<?php if ( ! empty( $socials ) ) : ?>
					<div class="site-footer__socials">
						<div class="site-footer__social-list">
							<?php foreach ( $socials as $network => $url ) :
								$icon = in_array( $network, $known_social_icons, true ) ? 'social-' . $network : 'default-social';
								$icon_url = VINTAGESOUL_URI . '/assets/svg/icons/' . $icon . '.svg';
							?>
								<a class="site-footer__social" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( ucfirst( $network ) ); ?>">
									<span class="site-footer__social-icon" style="--social-icon: url('<?php echo esc_url( $icon_url ); ?>');" aria-hidden="true"></span>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>

		</div>
	</div>

	<div class="site-footer__bottom">
		<div class="container site-footer__bottom-inner">
			<?php if ( ! empty( $bottom_links ) ) : ?>
				<div class="site-footer__legal">
					<?php foreach ( $bottom_links as $link ) : ?>
						<a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<span class="site-footer__copyright">&copy; <?php echo esc_html( $year ); ?> <?php bloginfo( 'name' ); ?><?php echo '' !== $labels['rights'] ? '. ' . esc_html( $labels['rights'] ) : ''; ?></span>
		</div>
	</div>
</footer>
