<?php
defined( 'ABSPATH' ) || exit;

$settings = ah_get_settings();
$footer   = ah_get_theme_footer();
$phone    = $settings['contact_phone'] ?? '';
$email    = $settings['email'] ?? '';
$address  = $settings['address'] ?? '';
$fb       = $settings['facebook_url'] ?? '';
$ig       = $settings['instagram_url'] ?? '';
$tw       = $settings['twitter_url'] ?? '';
$li       = $settings['linkedin_url'] ?? '';
$yt       = $settings['youtube_url'] ?? '';
$year     = gmdate( 'Y' );
?>
</div><!-- #page-content -->

<?php $above_footer = ah_get_html_block( 'above_footer' ); if ( $above_footer ) : ?>
<div class="above-footer-block">
  <div class="container"><?php echo $above_footer; ?></div>
</div>
<?php endif; ?>

<footer class="footer" role="contentinfo">
	<div class="container">
		<div class="footer__grid">
			<div>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nav__logo" style="margin-bottom:0">
					<?php $logo = get_template_directory() . '/assets/images/logo.png'; ?>
					<?php if ( file_exists( $logo ) ) : ?>
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>" alt="<?php bloginfo( 'name' ); ?>" style="height:36px">
						<span style="color:white;font-size:1.4rem"><?php echo esc_html( CLIENT_PRIMARY_TITLE ); ?> <em style="font-style:italic;font-family:var(--font-accent)"><?php echo esc_html( CLIENT_SECONDARY_TITLE ); ?></em></span>
					<?php else : ?>
						<div class="nav__logo-mark">AH</div>
						<span style="color:white;font-size:1.4rem"><?php echo esc_html( CLIENT_PRIMARY_TITLE ); ?> <em style="font-style:italic;font-family:var(--font-accent)"><?php echo esc_html( CLIENT_SECONDARY_TITLE ); ?></em></span>
					<?php endif; ?>
				</a>

				<?php if ( ! empty( $footer['brand_description'] ) ) : ?>
					<p class="footer__brand-desc"><?php echo wp_kses_post( $footer['brand_description'] ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $footer['badge_text'] ) ) : ?>
					<div class="footer__badge"><?php echo esc_html( $footer['badge_text'] ); ?></div>
				<?php endif; ?>

				<div class="footer__socials" style="margin-top:16px">
					<?php if ( $fb ) : ?>
					<a href="<?php echo esc_url( $fb ); ?>" target="_blank" rel="noopener" class="footer__social" aria-label="Facebook">
					<svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
						<path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.5-3.88 3.78-3.88 1.1 0 2.24.2 2.24.2v2.46H15.2c-1.24 0-1.63.77-1.63 1.56V12h2.77l-.44 2.89h-2.33v6.99A10 10 0 0 0 22 12"/>
					</svg>
					</a>
					<?php endif; ?>

					<?php if ( $tw ) : ?>
					<a href="<?php echo esc_url( $tw ); ?>" target="_blank" rel="noopener" class="footer__social" aria-label="Twitter/X">
					<svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
						<path d="M18.9 2H22l-6.77 7.74L23 22h-6.1l-4.78-6.25L6.63 22H3.5l7.24-8.27L1 2h6.25l4.32 5.7L18.9 2zm-1.07 18h1.69L6.33 3.9H4.52z"/>
					</svg>
					</a>
					<?php endif; ?>

					<?php if ( $ig ) : ?>
					<a href="<?php echo esc_url( $ig ); ?>" target="_blank" rel="noopener" class="footer__social" aria-label="Instagram">
					<svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
						<path d="M7 2C4.24 2 2 4.24 2 7v10c0 2.76 2.24 5 5 5h10c2.76 0 5-2.24 5-5V7c0-2.76-2.24-5-5-5H7zm0 2h10c1.65 0 3 1.35 3 3v10c0 1.65-1.35 3-3 3H7c-1.65 0-3-1.35-3-3V7c0-1.65 1.35-3 3-3zm10.5 1a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10zm0 2a3 3 0 1 1 0 6 3 3 0 0 1 0-6z"/>
					</svg>
					</a>
					<?php endif; ?>

					<?php if ( $yt ) : ?>
					<a href="<?php echo esc_url( $yt ); ?>" target="_blank" rel="noopener" class="footer__social" aria-label="YouTube">
					<svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
						<path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.4.6A3 3 0 0 0 .5 6.2 31.7 31.7 0 0 0 0 12a31.7 31.7 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.6 9.4.6 9.4.6s7.5 0 9.4-.6a3 3 0 0 0 2.1-2.1A31.7 31.7 0 0 0 24 12a31.7 31.7 0 0 0-.5-5.8zM9.75 15.5v-7l6 3.5-6 3.5z"/>
					</svg>
					</a>
					<?php endif; ?>

					<?php if ( $li ) : ?>
					<a href="<?php echo esc_url( $li ); ?>" target="_blank" rel="noopener" class="footer__social" aria-label="LinkedIn">
					<svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
						<path d="M4.98 3.5A2.48 2.48 0 1 0 5 8.46 2.48 2.48 0 0 0 4.98 3.5zM3 9h4v12H3zm7 0h3.83v1.64h.05c.53-1 1.84-2.05 3.79-2.05 4.05 0 4.8 2.67 4.8 6.14V21h-4v-5.27c0-1.26-.02-2.88-1.75-2.88-1.75 0-2.02 1.37-2.02 2.79V21h-4z"/>
					</svg>
					</a>
					<?php endif; ?>
				</div>
			</div>

			<?php foreach ( (array) ( $footer['columns'] ?? [] ) as $column ) : ?>
				<?php
				$column = is_object( $column ) ? (array) $column : (array) $column;
				if ( empty( $column['items'] ) ) {
					continue;
				}
				?>
				<div>
					<div class="footer__col-title"><?php echo esc_html( $column['title'] ?? 'Links' ); ?></div>
					<div class="footer__links">
						<?php foreach ( (array) $column['items'] as $link ) : ?>
							<?php $link = is_object( $link ) ? (array) $link : (array) $link; ?>
							<a href="<?php echo esc_url( $link['url'] ?? '#' ); ?>" class="footer__link"
								<?php if ( ! empty( $link['highlight'] ) ) echo 'style="color:var(--client-color-100);font-weight:700"'; ?>>
								<?php echo esc_html( $link['label'] ?? '' ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>

			<div>
				<div class="footer__col-title"><?php esc_html_e( 'Get In Touch', 'ah-theme' ); ?></div>

				<?php if ( $phone ) : ?>
					<div class="footer__contact-item">
						<span class="footer__contact-icon">P</span>
						<div>
							<a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>" style="color:white;font-weight:600">
								<?php echo esc_html( $phone ); ?>
							</a>
							<?php if ( ! empty( $footer['contact']['phone_note'] ) ) : ?>
								<div style="font-size:.78rem;margin-top:2px"><?php echo esc_html( $footer['contact']['phone_note'] ); ?></div>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $email ) : ?>
					<div class="footer__contact-item">
						<span class="footer__contact-icon">E</span>
						<div>
							<a href="mailto:<?php echo esc_attr( $email ); ?>" style="color:white;font-weight:600">
								<?php echo esc_html( $email ); ?>
							</a>
							<?php if ( ! empty( $footer['contact']['email_note'] ) ) : ?>
								<div style="font-size:.78rem;margin-top:2px"><?php echo esc_html( $footer['contact']['email_note'] ); ?></div>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $address ) : ?>
					<div class="footer__contact-item">
						<span class="footer__contact-icon">A</span>
						<div>
							<div style="color:white;font-weight:600"><?php echo esc_html( $address ); ?></div>
							<?php if ( ! empty( $footer['contact']['address_note'] ) ) : ?>
								<div style="font-size:.78rem;margin-top:2px"><?php echo esc_html( $footer['contact']['address_note'] ); ?></div>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $footer['cta']['label'] ) ) : ?>
					<div style="margin-top:20px">
						<a href="<?php echo esc_url( $footer['cta']['url'] ?? home_url( '/contact/' ) ); ?>" class="btn btn-gold btn-sm" style="width:100%;justify-content:center">
							<?php echo esc_html( $footer['cta']['label'] ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="footer__bottom">
			<div>&copy; <?php echo esc_html( $year ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'ah-theme' ); ?></div>
			<div class="footer__legal">
				<?php foreach ( (array) ( $footer['legal_links'] ?? [] ) as $link ) : ?>
					<?php $link = is_object( $link ) ? (array) $link : (array) $link; ?>
					<a href="<?php echo esc_url( $link['url'] ?? '#' ); ?>"><?php echo esc_html( $link['label'] ?? '' ); ?></a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
