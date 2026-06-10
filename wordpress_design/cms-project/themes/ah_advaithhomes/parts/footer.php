<?php
defined( 'ABSPATH' ) || exit;

$settings  = ah_get_settings();
$footer    = ah_get_theme_footer();
$phone     = $settings['contact_phone'] ?? '';
$email     = $settings['email'] ?? '';
$wa_raw    = $settings['whatsapp'] ?? $settings['whatsapp_number'] ?? $phone;
$wa_number = preg_replace( '/[^0-9]/', '', $wa_raw );
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

<!-- ── Newsletter signup band ─────────────────────────────────────────────── -->
<section class="ah-news-cta" aria-label="<?php echo esc_attr( 'Newsletter signup' ); ?>">
  <div class="container ah-news-cta__inner">
    <div class="ah-news-cta__text">
      <h3 class="ah-news-cta__title">Stay informed with expert guides, updates &amp; practical tips.</h3>
      <p class="ah-news-cta__sub">Straight to your inbox. No spam — unsubscribe anytime.</p>
    </div>
    <form class="ah-news-cta__form" id="ahNewsletterForm" novalidate>
      <input type="hidden" name="page_url" value="<?php echo esc_url( ( is_ssl() ? 'https://' : 'http://' ) . ( $_SERVER['HTTP_HOST'] ?? '' ) . ( $_SERVER['REQUEST_URI'] ?? '' ) ); ?>">
      <label for="ahNewsletterEmail" class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0)">Your email address</label>
      <input type="email" id="ahNewsletterEmail" name="email" class="ah-news-cta__input" placeholder="Your email address" autocomplete="email" required>
      <button type="submit" class="btn btn-gold ah-news-cta__btn">Subscribe</button>
      <span class="ah-news-cta__msg" id="ahNewsletterMsg" role="status" aria-live="polite"></span>
    </form>
  </div>
</section>
<script>
(function () {
  var form = document.getElementById('ahNewsletterForm');
  if (!form || typeof window.ahTheme === 'undefined') return;
  var msg = document.getElementById('ahNewsletterMsg');
  var btn = form.querySelector('.ah-news-cta__btn');
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var email = form.querySelector('[name=email]').value.trim();
    if (!email) { msg.textContent = 'Please enter your email.'; msg.className = 'ah-news-cta__msg is-error'; return; }
    btn.disabled = true; var label = btn.textContent; btn.textContent = 'Subscribing…';
    var body = new URLSearchParams({
      action: 'ah_theme_form_submit', form_type: 'newsletter',
      nonce: window.ahTheme.nonce, email: email,
      page_url: (form.querySelector('[name=page_url]') || {}).value || location.href
    });
    fetch(window.ahTheme.ajaxUrl, { method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString() })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        msg.textContent = (res.data && res.data.message) ? res.data.message : (res.success ? 'Subscribed!' : 'Something went wrong.');
        msg.className = 'ah-news-cta__msg ' + (res.success ? 'is-ok' : 'is-error');
        if (res.success) form.reset();
      })
      .catch(function () { msg.textContent = 'Network error. Please try again.'; msg.className = 'ah-news-cta__msg is-error'; })
      .finally(function () { btn.disabled = false; btn.textContent = label; });
  });
})();
</script>

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
				<div class="footer__accordion">
					<button class="footer__col-title footer__acc-toggle" aria-expanded="false">
						<?php echo esc_html( $column['title'] ?? 'Links' ); ?>
						<span class="footer__acc-icon" aria-hidden="true">+</span>
					</button>
					<div class="footer__acc-body">
						<div class="footer__links">
							<?php foreach ( (array) $column['items'] as $link ) : ?>
								<?php $link = is_object( $link ) ? (array) $link : (array) $link; ?>
								<a href="<?php echo esc_url( $link['url'] ?? '#' ); ?>" class="footer__link"
									<?php if ( ! empty( $link['highlight'] ) ) echo ( 'style="color:var(--client-color-100);font-weight:700"' ); ?>>
									<?php echo esc_html( $link['label'] ?? '' ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>

			<div class="footer__accordion">
				<button class="footer__col-title footer__acc-toggle" aria-expanded="false">
					<?php echo esc_html( TXT_GET_IN_TOUCH_1 ); ?>
					<span class="footer__acc-icon" aria-hidden="true">+</span>
				</button>
				<div class="footer__acc-body">

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
<p class="footer__sitemap"><a href="<?php echo esc_url( home_url( '/sitemap.xml' ) ); ?>" target="_blank" rel="noopener">Sitemap</a></p>
</footer>

<?php if ( $wa_number ) : ?>
<a href="https://wa.me/<?php echo esc_attr( $wa_number ); ?>?text=<?php echo rawurlencode( 'Hi, I\'d like to find out more about your services.' ); ?>"
   class="wa-fab"
   target="_blank"
   rel="noopener noreferrer"
   aria-label="Chat on WhatsApp">
  <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
  </svg>
  <span class="wa-fab__label">WhatsApp</span>
</a>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
