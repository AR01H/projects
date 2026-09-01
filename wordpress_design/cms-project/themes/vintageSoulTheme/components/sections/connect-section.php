<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\SettingsService;

$title   = (string) ( $title ?? "LET'S CONNECT" );
$address = (string) ( $address ?? SettingsService::address() );
$phone   = (string) ( $phone ?? SettingsService::phone() );
$email   = (string) ( $email ?? SettingsService::email() );
$website = (string) ( $website ?? 'www.thecanehouse.com' );
$hours   = (string) ( $hours ?? 'Mon - Sun 9:00 AM - 9:00 PM' );
$socials = (array) ( $socials ?? array() );
?>
<section class="section section--connect connect-vintage" id="connect">
	<div class="container container--narrow connect-vintage__container frame--ornate">
		<div class="connect-vintage__header">
			<h2 class="connect-vintage__title">— CONNECT WITH <em>The Cane House</em> —</h2>
		</div>

		<ul class="connect-vintage__list">
			<?php if ( '' !== $address ) : ?>
				<li class="connect-item">
					<span class="connect-item__icon">📍</span>
					<span class="connect-item__text"><?php echo esc_html( $address ); ?></span>
				</li>
			<?php endif; ?>
			<?php if ( '' !== $phone ) : ?>
				<li class="connect-item">
					<span class="connect-item__icon">📞</span>
					<a class="connect-item__link" href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
				</li>
			<?php endif; ?>
			<?php if ( '' !== $email ) : ?>
				<li class="connect-item">
					<span class="connect-item__icon">✉️</span>
					<a class="connect-item__link" href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
				</li>
			<?php endif; ?>
			<?php if ( '' !== $website ) : ?>
				<li class="connect-item">
					<span class="connect-item__icon">🌐</span>
					<span class="connect-item__text"><?php echo esc_html( $website ); ?></span>
				</li>
			<?php endif; ?>
			<?php if ( '' !== $hours ) : ?>
				<li class="connect-item">
					<span class="connect-item__icon">🕒</span>
					<span class="connect-item__text"><?php echo esc_html( $hours ); ?></span>
				</li>
			<?php endif; ?>
		</ul>

		<?php if ( ! empty( $socials ) ) : ?>
			<div class="connect-vintage__socials">
				<?php foreach ( $socials as $soc ) :
					$s_icon = (string) ( $soc['icon'] ?? 'link' );
					$s_url  = (string) ( $soc['url'] ?? '#' );
				?>
					<a class="social-circle social-circle--<?php echo esc_attr( $s_icon ); ?>" href="<?php echo esc_url( $s_url ); ?>" target="_blank" rel="noopener noreferrer">
						<span class="social-circle__name"><?php echo esc_html( ucfirst( $s_icon ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
