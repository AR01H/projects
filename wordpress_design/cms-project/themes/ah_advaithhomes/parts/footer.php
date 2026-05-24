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
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>" alt="<?php echo esc_attr( TXT_PHP_BLOGINFO_NAME ); ?>" style="height:36px">
						<span style="color:white;font-size:1.4rem"><?php echo esc_html( CLIENT_PRIMARY_TITLE ); ?> <em style="font-style:italic;font-family:var(--font-accent)"><?php echo esc_html( CLIENT_SECONDARY_TITLE ); ?></em></span>
					<?php else : ?>
						<div class="nav__logo-mark"><?php echo esc_html( CLIENT_SHORT_TITLE ); ?></div>
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
					<a href="<?php echo esc_url( $fb ); ?>" target="_blank" rel="noopener" class="footer__social" aria-label="<?php echo esc_attr( TXT_FACEBOOK ); ?>">
					<?php echo file_get_contents(get_template_directory() . '/assets/images/svgs/facebook.svg');?>
					</a>
					<?php endif; ?>

					<?php if ( $tw ) : ?>
					<a href="<?php echo esc_url( $tw ); ?>" target="_blank" rel="noopener" class="footer__social" aria-label="<?php echo esc_attr( TXT_TWITTER_X ); ?>">
						<?php echo file_get_contents(get_template_directory() . '/assets/images/svgs/twitter.svg');?>
					</a>
					<?php endif; ?>

					<?php if ( $ig ) : ?>
					<a href="<?php echo esc_url( $ig ); ?>" target="_blank" rel="noopener" class="footer__social" aria-label="<?php echo esc_attr( TXT_INSTAGRAM ); ?>">
						<?php echo file_get_contents(get_template_directory() . '/assets/images/svgs/instagram.svg');?>
					</a>
					<?php endif; ?>

					<?php if ( $yt ) : ?>
					<a href="<?php echo esc_url( $yt ); ?>" target="_blank" rel="noopener" class="footer__social" aria-label="<?php echo esc_attr( TXT_YOUTUBE ); ?>">
					<?php echo file_get_contents(get_template_directory() . '/assets/images/svgs/youtube.svg');?>
					</a>
					<?php endif; ?>

					<?php if ( $li ) : ?>
					<a href="<?php echo esc_url( $li ); ?>" target="_blank" rel="noopener" class="footer__social" aria-label="<?php echo esc_attr( TXT_LINKEDIN ); ?>">
						<?php echo file_get_contents(get_template_directory() . '/assets/images/svgs/linkedin.svg');?>
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
								<?php if ( ! empty( $link['highlight'] ) ) echo esc_html( TXT_STYLE_COLOR_VAR_CLIENT_COLOR_100_FONT_WEIGHT_700 ); ?>>
								<?php echo esc_html( $link['label'] ?? '' ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>

			<div>
				<div class="footer__col-title"><?php echo esc_html( TXT_GET_IN_TOUCH_1 ); ?></div>

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
						<a href="<?php echo esc_url( $footer['cta']['url'] ?? home_url( AH_LINK_CONTACT ) ); ?>" class="btn btn-gold btn-sm" style="width:100%;justify-content:center">
							<?php echo esc_html( $footer['cta']['label'] ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="footer__bottom">
			<div>&copy; <?php echo esc_html( $year ); ?> <?php bloginfo( 'name' ); ?>. <?php echo esc_html( TXT_ALL_RIGHTS_RESERVED ); ?></div>
			<div class="footer__legal">
				<?php foreach ( (array) ( $footer['legal_links'] ?? [] ) as $link ) : ?>
					<?php $link = is_object( $link ) ? (array) $link : (array) $link; ?>
					<a href="<?php echo esc_url( $link['url'] ?? '#' ); ?>"><?php echo esc_html( $link['label'] ?? '' ); ?></a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
<p class="footer__sitemap"><a href="<?php echo esc_url( home_url( '/sitemap.xml' ) ); ?>" target="_blank" rel="noopener">Sitemap</a></p>
</footer>

<?php wp_footer(); ?>
</body>
</html>
