<?php
defined( 'ABSPATH' ) || exit;

$settings = ch_get_settings();
$footer   = ch_get_theme_footer();
$phone    = $settings['phone']    ?? '+44 7887 699 208';
$whatsapp = $settings['whatsapp'] ?? '447887699208';
$email    = $settings['email']    ?? 'hello@thecanehouse.co.uk';
$social   = $footer['social']     ?? [];
?>

</div><!-- #ch-page-content -->

<footer class="ch-footer">
	<div class="ch-footer__grid">
		<div class="ch-footer__brand">
			<div class="ch-footer__logo">The Cane <em>House</em> 🌿</div>
			<p class="ch-footer__brand-desc">
				<?php echo esc_html( $footer['brand_description'] ?? 'Fresh sugarcane juice pressed live, served cool. No added sugar, no preservatives - pure natural refreshment wherever you are.' ); ?>
			</p>
			<div class="ch-footer__social">
				<?php if ( ! empty( $social['instagram'] ) ) : ?>
					<a class="ch-footer__social-btn" href="<?php echo esc_url( $social['instagram'] ); ?>" target="_blank" rel="noopener" aria-label="Instagram">📸</a>
				<?php else : ?>
					<a class="ch-footer__social-btn" href="#" aria-label="Instagram">📸</a>
				<?php endif; ?>
				<?php if ( ! empty( $social['facebook'] ) ) : ?>
					<a class="ch-footer__social-btn" href="<?php echo esc_url( $social['facebook'] ); ?>" target="_blank" rel="noopener" aria-label="Facebook">📘</a>
				<?php else : ?>
					<a class="ch-footer__social-btn" href="#" aria-label="Facebook">📘</a>
				<?php endif; ?>
				<?php if ( ! empty( $social['tiktok'] ) ) : ?>
					<a class="ch-footer__social-btn" href="<?php echo esc_url( $social['tiktok'] ); ?>" target="_blank" rel="noopener" aria-label="TikTok">🎵</a>
				<?php else : ?>
					<a class="ch-footer__social-btn" href="#" aria-label="TikTok">🎵</a>
				<?php endif; ?>
				<?php if ( ! empty( $social['youtube'] ) ) : ?>
					<a class="ch-footer__social-btn" href="<?php echo esc_url( $social['youtube'] ); ?>" target="_blank" rel="noopener" aria-label="YouTube">▶️</a>
				<?php else : ?>
					<a class="ch-footer__social-btn" href="#" aria-label="YouTube">▶️</a>
				<?php endif; ?>
			</div>
		</div>

		<?php foreach ( (array) ( $footer['columns'] ?? [] ) as $col ) : ?>
			<div class="ch-footer__col">
				<h4><?php echo esc_html( $col['title'] ?? '' ); ?></h4>
				<ul>
					<?php foreach ( (array) ( $col['items'] ?? [] ) as $it ) : ?>
						<li>
							<a href="<?php echo esc_url( $it['url'] ?? '#' ); ?>">
								<?php echo esc_html( $it['label'] ?? '' ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="ch-footer__bottom">
		<span><?php echo esc_html( $footer['copyright'] ?? '© ' . date( 'Y' ) . ' The Cane House. Pressed Fresh. Served Cool.' ); ?></span>
		<?php if ( ! empty( $footer['legal_links'] ) ) : ?>
			<div class="ch-footer__legal">
				<?php foreach ( (array) $footer['legal_links'] as $it ) : ?>
					<a href="<?php echo esc_url( $it['url'] ?? '#' ); ?>"><?php echo esc_html( $it['label'] ?? '' ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</footer>

<?php if ( $whatsapp ) : ?>
	<a href="<?php echo esc_url( 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $whatsapp ) ); ?>"
		class="ch-whatsapp-float" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
		<div class="ch-wa-tooltip">Chat with us! 🌿</div>
		<svg viewBox="0 0 32 32" aria-hidden="true">
			<path fill="currentColor" d="M16 2a13.79 13.79 0 0 0-11.85 20.8L2 30l7.45-1.95A13.73 13.73 0 0 0 16 30c7.61 0 13.8-6.19 13.8-13.8A13.84 13.84 0 0 0 16 2zm6.65 19.38c-.3.84-1.49 1.54-2.45 1.76-.66.15-1.53.27-4.43-.93-3.71-1.54-6.1-5.32-6.29-5.57s-1.52-2.02-1.52-3.85 1-2.73 1.35-3.1.7-.46.93-.46.45 0 .64.01c.19 0 .45-.07.7.53.26.63.89 2.16.96 2.31s.12.33.02.53-.15.3-.3.47-.3.34-.45.51-.33.36-.14.68c.19.32.83 1.36 1.78 2.21 1.23 1.09 2.26 1.43 2.58 1.59s.53.13.73-.09.85-.99 1.08-1.33.45-.28.76-.17 1.95.92 2.28 1.08.55.25.63.38.12.78-.18 1.62z"/>
		</svg>
	</a>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
