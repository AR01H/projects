<?php
defined( 'ABSPATH' ) || exit;

$settings  = ch_get_settings();
$footer    = ch_get_theme_footer();
$phone     = $settings['phone']     ?? $settings['contact_phone']  ?? '';
$email     = $settings['email']     ?? '';
$whatsapp  =  WHATASPP_CONTACT_NUMBER;
$address   = $settings['address']   ?? '';
$wa_number = $settings['whatsapp']     ?? $settings['whatsapp'] ??$whatsapp;
$logo_url  = get_template_directory_uri() . '/assets/images/logo.png';
$has_logo  = file_exists( get_template_directory() . '/assets/images/logo.png' );
$year      = gmdate( 'Y' );

// Fallback link columns (identical to reference design)
$cols = (array) ( $footer['columns'] ?? [] );
if ( empty( $cols ) ) {
	$cols = [];
}
?>

</div><!-- #ch-page-content -->

<footer class="ch-footer" role="contentinfo">
	<div class="container">
		<div class="ch-footer__grid">

			<!-- ── Brand Column ───────────────────────────────────────────────── -->
			<div class="ch-footer__brand-col">
				<a href="<?php echo esc_url( ( '/' ) ); ?>" class="ch-nav__logo ch-footer__logo-link">
					<?php if ( $has_logo ) : ?>
						<img src="<?php echo esc_url( $logo_url ); ?>" alt="The Cane House">
					<?php else : ?>
						<div class="ch-nav__logo-mark">🌿</div>
						<span class="ch-nav__logo-text">THE CANE <em>HOUSE</em></span>
					<?php endif; ?>
				</a>

				<!-- <p class="ch-footer__brand-desc">
					<?php echo wp_kses_post( $footer['brand_description'] ?? 'Fresh sugarcane juice pressed live, served cool. No added sugar, no preservatives - just pure natural refreshment wherever you are.' ); ?>
				</p> -->

				<?php if ( ! empty( $footer['badge_text'] ) ) : ?>
					<div class="ch-footer__badge"><?php echo esc_html( $footer['badge_text'] ); ?></div>
				<?php endif; ?>

				<!-- Social icons -->
				<div class="ch-footer__socials">
					<?php
					$social_map = [
						'instagram_url' => [ 'label' => 'Instagram', 'svg' => get_template_directory_uri().'/assets/images/icons/instagram.svg' ],
						'facebook_url'  => [ 'label' => 'Facebook',  'svg' => get_template_directory_uri().'/assets/images/icons/facebook.svg' ],
						'youtube_url'   => [ 'label' => 'YouTube',   'svg' => get_template_directory_uri().'/assets/images/icons/youtube.svg' ],
					];
					foreach ( $social_map as $key => $info ) :
						$url = ! empty( $settings[ $key ] ) ? $settings[ $key ] : '';
						if(strlen( $url ) <= 0) {
							continue;
						}
					?>
						<a href="<?php echo $url ? esc_url( $url ) : '#'; ?>"
							class="ch-footer__social"
							aria-label="<?php echo esc_attr( $info['label'] ); ?>"
							<?php echo $url ? 'target="_blank" rel="noopener"' : ''; ?>>
							<img src="<?php echo esc_url( $info['svg'] ); ?>" alt="<?php echo esc_attr( $info['label'] ); ?>" class="ch-footer__social-icon">
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
								<a href="<?php echo ( $link['url'] ?? '#' ); ?>"
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
							<a href="<?php echo esc_url( $footer['cta']['url'] ?? ( '/#contact' ) ); ?>"
								class="btn-lime" style="width:100%;justify-content:center;display:flex;">
								<?php echo esc_html( $footer['cta']['label'] ); ?>
							</a>
						</div>
					<?php else : ?>
						<div style="margin-top:1.2rem;">
							<a href="<?php echo esc_url( ( '/#contact' ) ); ?>"
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
				<?php echo esc_html( $footer['copyright_suffix'] ?? '' ); ?>
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

<!-- ── Scroll to Top Button ──────────────────────────────────────────────── -->
<button id="ch-scroll-to-top" class="ch-scroll-to-top" aria-label="Scroll to top">
	<!-- Glass of Sugarcane Juice -->
	<div class="ch-scroll-glass">
		<div class="ch-scroll-glass__fill"></div>
		<svg viewBox="0 0 24 24" class="ch-scroll-glass__cup" fill="none" stroke="currentColor" stroke-width="1.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
			<path d="M6 4h12v12c0 2-1.5 3-6 3s-6-1-6-3V4z" stroke-linecap="round" stroke-linejoin="round"/>
			<path d="M18 6h2c1 0 1.5.5 1.5 1.5v4c0 1-.5 1.5-1.5 1.5h-2" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
	</div>
	<!-- Arrow Icon -->
	<svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="ch-scroll-arrow">
		<path d="M7 14.5l5-5 5 5" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
	</svg>
</button>

<!-- ── WhatsApp FAB ──────────────────────────────────────────────────────── -->
<?php if ( $wa_number ) : ?>
<a href="<?php echo esc_url( 'https://wa.me/' . $wa_number . '?text=' . rawurlencode( 'Hi! I\'d love to find out more about The Cane House.' ) ); ?>"
	class="ch-wa-fab" target="_blank" rel="noopener noreferrer" aria-label="Chat on WhatsApp">
	<img height="25px" width="25px" src="<?php echo ( get_template_directory_uri() . '/assets/images/icons/whatsapp.svg' ); ?>" alt="WhatsApp" class="ch-wa-fab__icon">
	<span class="ch-wa-fab__label">WhatsApp</span>
</a>
<?php endif; ?>

<?php get_template_part( 'components/privacy-policy-modal' ); ?>

<?php wp_footer(); ?>
</body>
</html>
