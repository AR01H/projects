<?php
defined( 'ABSPATH' ) || exit;

$settings  = ch_get_settings();
$footer    = ch_get_theme_footer();
$phone     = $settings['phone']     ?? $settings['contact_phone']  ?? '';
$email     = $settings['email']     ?? '';
$whatsapp  = $settings['whatsapp']  ?? $settings['whatsapp_number'] ?? WHATASPP_CONTACT_NUMBER;
$address   = $settings['address']   ?? '';
$wa_number = preg_replace( '/[^0-9]/', '', $whatsapp );
$logo_url  = get_template_directory_uri() . '/assets/images/logo.png';
$has_logo  = file_exists( get_template_directory() . '/assets/images/logo.png' );
$social    = $footer['social'] ?? [];
$year      = gmdate( 'Y' );

// Fallback link columns (identical to reference design)
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

<footer class="ch-footer" role="contentinfo">
	<div class="container">
		<div class="ch-footer__grid">

			<!-- ── Brand Column ───────────────────────────────────────────────── -->
			<div class="ch-footer__brand-col">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ch-nav__logo ch-footer__logo-link">
					<?php if ( $has_logo ) : ?>
						<img src="<?php echo esc_url( $logo_url ); ?>" alt="The Cane House">
					<?php else : ?>
						<div class="ch-nav__logo-mark">🌿</div>
					<?php endif; ?>
					<span class="ch-nav__logo-text">THE CANE <em>HOUSE</em></span>
				</a>

				<p class="ch-footer__brand-desc">
					<?php echo wp_kses_post( $footer['brand_description'] ?? 'Fresh sugarcane juice pressed live, served cool. No added sugar, no preservatives - just pure natural refreshment wherever you are.' ); ?>
				</p>

				<?php if ( ! empty( $footer['badge_text'] ) ) : ?>
					<div class="ch-footer__badge"><?php echo esc_html( $footer['badge_text'] ); ?></div>
				<?php endif; ?>

				<!-- Social icons -->
				<div class="ch-footer__socials">
					<?php
					$social_map = [
						'instagram' => [ 'label' => 'Instagram', 'svg' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>' ],
						'facebook'  => [ 'label' => 'Facebook',  'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>' ],
						'tiktok'    => [ 'label' => 'TikTok',    'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.27 6.27 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.72a8.16 8.16 0 004.77 1.54V6.79a4.85 4.85 0 01-1-.1z"/></svg>' ],
						'youtube'   => [ 'label' => 'YouTube',   'svg' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.97C18.88 4 12 4 12 4s-6.88 0-8.59.45A2.78 2.78 0 001.46 6.42 29 29 0 001 12a29 29 0 00.46 5.58 2.78 2.78 0 001.95 1.97C5.12 20 12 20 12 20s6.88 0 8.59-.45a2.78 2.78 0 001.95-1.97A29 29 0 0023 12a29 29 0 00-.46-5.58zM9.75 15.02V8.98l5.75 3.02-5.75 3.02z"/></svg>' ],
					];
					foreach ( $social_map as $key => $info ) :
						$url = ! empty( $social[ $key ] ) ? $social[ $key ] : '';
						if(strlen( $url ) <= 0) {
							continue;
						}
					?>
						<a href="<?php echo $url ? esc_url( $url ) : '#'; ?>"
							class="ch-footer__social"
							aria-label="<?php echo esc_attr( $info['label'] ); ?>"
							<?php echo $url ? 'target="_blank" rel="noopener"' : ''; ?>>
							<?php echo $info['svg']; ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- ── Link Columns (from DB) ─────────────────────────────────────── -->
			<?php foreach ( $cols as $col ) :
				$col = (array) $col;
				if ( empty( $col['items'] ) ) continue;
			?>
				<div class="ch-footer__accordion">
					<button class="ch-footer__col-title ch-footer__acc-toggle" aria-expanded="false">
						<?php echo esc_html( $col['title'] ?? 'Links' ); ?>
						<span class="ch-footer__acc-icon" aria-hidden="true">+</span>
					</button>
					<div class="ch-footer__acc-body">
						<div class="ch-footer__links">
							<?php foreach ( (array) $col['items'] as $link ) :
								$link  = (array) $link;
								$is_hl = ! empty( $link['highlight'] );
							?>
								<a href="<?php echo esc_url( $link['url'] ?? '#' ); ?>"
									class="ch-footer__link<?php echo $is_hl ? ' ch-footer__link--highlight' : ''; ?>">
									<?php echo esc_html( $link['label'] ?? '' ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>

			<!-- ── Get in Touch Column ────────────────────────────────────────── -->
			<div class="ch-footer__accordion hidden" style="display:none;">

				<div class="ch-footer__acc-body">

					<?php if ( $phone ) : ?>
						<div class="ch-footer__contact-item">
							<span class="ch-footer__contact-icon">P</span>
							<div>
								<a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>"
									style="color:white;font-weight:600;text-decoration:none;">
									<?php echo esc_html( $phone ); ?>
								</a>
								<?php if ( ! empty( $footer['contact']['phone_note'] ) ) : ?>
									<div style="font-size:.78rem;margin-top:2px;color:rgba(255,255,255,.45);">
										<?php echo esc_html( $footer['contact']['phone_note'] ); ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( $email ) : ?>
						<div class="ch-footer__contact-item">
							<span class="ch-footer__contact-icon">E</span>
							<div>
								<a href="mailto:<?php echo esc_attr( $email ); ?>"
									style="color:white;font-weight:600;text-decoration:none;word-break:break-all;">
									<?php echo esc_html( $email ); ?>
								</a>
								<?php if ( ! empty( $footer['contact']['email_note'] ) ) : ?>
									<div style="font-size:.78rem;margin-top:2px;color:rgba(255,255,255,.45);">
										<?php echo esc_html( $footer['contact']['email_note'] ); ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( $address ) : ?>
						<div class="ch-footer__contact-item">
							<span class="ch-footer__contact-icon">A</span>
							<div style="color:white;font-weight:600;">
								<?php echo esc_html( $address ); ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $footer['cta']['label'] ) ) : ?>
						<div style="margin-top:1.2rem;">
							<a href="<?php echo esc_url( $footer['cta']['url'] ?? home_url( '/#contact' ) ); ?>"
								class="btn-lime" style="width:100%;justify-content:center;display:flex;">
								<?php echo esc_html( $footer['cta']['label'] ); ?>
							</a>
						</div>
					<?php else : ?>
						<div style="margin-top:1.2rem;">
							<a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>"
								class="btn-lime" style="width:100%;justify-content:center;display:flex;">
								Send a Message 🌿
							</a>
						</div>
					<?php endif; ?>

				</div>
			</div>

		</div>

		<!-- Bottom bar -->
		<div class="ch-footer__bottom">
			<div>
				&copy; <?php echo esc_html( $year ); ?> The Cane House.
				<?php echo esc_html( $footer['copyright_suffix'] ?? 'Pressed Fresh. Served Cool.' ); ?>
			</div>
			<?php if ( ! empty( $footer['legal_links'] ) ) : ?>
				<div class="ch-footer__legal">
					<?php foreach ( (array) $footer['legal_links'] as $it ) :
						$it = (array) $it;
					?>
						<a href="<?php echo esc_url( $it['url'] ?? '#' ); ?>">
							<?php echo esc_html( $it['label'] ?? '' ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

	</div><!-- .container -->
	<p class="ch-footer__sitemap hidden" style="display:none;">
		<a href="<?php echo esc_url( home_url( '/sitemap.xml' ) ); ?>" target="_blank" rel="noopener">Sitemap</a>
	</p>
</footer>

<!-- ── WhatsApp FAB ──────────────────────────────────────────────────────── -->
<?php if ( $wa_number ) : ?>
<a href="<?php echo esc_url( 'https://wa.me/' . $wa_number . '?text=' . rawurlencode( 'Hi! I\'d love to find out more about The Cane House.' ) ); ?>"
	class="ch-wa-fab" target="_blank" rel="noopener noreferrer" aria-label="Chat on WhatsApp">
	<svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
	</svg>
	<span class="ch-wa-fab__label">WhatsApp</span>
</a>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
