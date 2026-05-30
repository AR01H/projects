<?php
defined( 'ABSPATH' ) || exit;

$settings = ch_get_settings();
$footer   = ch_get_theme_footer();
$phone    = $settings['phone']    ?? '+44 7887 699 208';
$whatsapp = $settings['whatsapp'] ?? '447887699208';
$social   = $footer['social']     ?? [];

$cols = (array) ( $footer['columns'] ?? [] );
if ( empty( $cols ) ) {
	$cols = [
		[
			'title' => 'Our Juice',
			'items' => [
				[ 'label' => 'Build Your Juice',  'url' => home_url( '/#build' ) ],
				[ 'label' => 'Sizes & Pricing',   'url' => home_url( '/#build' ) ],
				[ 'label' => 'Cane Types',         'url' => home_url( '/#build' ) ],
				[ 'label' => 'Flavour Blends',     'url' => home_url( '/#build' ) ],
				[ 'label' => 'Health Benefits',    'url' => home_url( '/#benefits' ) ],
			],
		],
		[
			'title' => 'Services',
			'items' => [
				[ 'label' => 'Event Hire',          'url' => home_url( '/#hire' ) ],
				[ 'label' => 'Weddings',             'url' => home_url( '/#hire' ) ],
				[ 'label' => 'Parties & Gatherings', 'url' => home_url( '/#hire' ) ],
				[ 'label' => 'Franchise',            'url' => home_url( '/#franchise' ) ],
				[ 'label' => 'Contact Us',           'url' => home_url( '/#contact' ) ],
			],
		],
	];
}
?>

</div><!-- #ch-page-content -->

<footer class="ch-footer">
	<div class="ch-footer-grid">
		<!-- Brand column -->
		<div class="ch-footer-brand">
			<div class="ch-footer-logo">The Cane <span>House</span> 🌿</div>
			<p><?php echo esc_html( $footer['brand_description'] ?? 'Fresh sugarcane juice pressed live, served cool. No added sugar, no preservatives — just pure natural refreshment wherever you are.' ); ?></p>
			<div class="ch-footer-social">
				<?php
				$social_icons = [
					'instagram' => '📸',
					'facebook'  => '📘',
					'tiktok'    => '🎵',
					'youtube'   => '▶️',
				];
				foreach ( $social_icons as $key => $icon ) :
					$url = ! empty( $social[ $key ] ) ? $social[ $key ] : '#';
				?>
					<a class="ch-footer-social-btn" href="<?php echo esc_url( $url ); ?>"
						<?php echo $url !== '#' ? 'target="_blank" rel="noopener"' : ''; ?>
						aria-label="<?php echo esc_attr( ucfirst( $key ) ); ?>"><?php echo $icon; ?></a>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Link columns -->
		<?php foreach ( $cols as $col ) : ?>
			<div class="ch-footer-col">
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

	<div class="ch-footer-bottom">
		<span><?php echo esc_html( $footer['copyright'] ?? '© ' . date( 'Y' ) . ' The Cane House. Pressed Fresh. Served Cool.' ); ?></span>
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
