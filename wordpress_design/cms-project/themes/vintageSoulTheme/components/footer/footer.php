<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\SettingsService;
use VintageSoul\Support\View;

$phone   = (string) ( $phone ?? SettingsService::phone() );
$email   = (string) ( $email ?? SettingsService::email() );
$address = (string) ( $address ?? SettingsService::address() );
$tagline = (string) ( $tagline ?? SettingsService::tagline_fallback() );
$year    = gmdate( 'Y' );
?>
<!-- Pre-Footer Ticker Ribbon -->
<div class="ribbon-ticker ribbon-ticker--green" aria-hidden="true">
	<div class="ribbon-ticker__track">
		<?php for ( $r = 0; $r < 4; $r++ ) : ?>
			<span class="ribbon-ticker__heart">♥</span>
			<span class="ribbon-ticker__text">FRESHLY PRESSED</span>
			<span class="ribbon-ticker__heart">♥</span>
			<span class="ribbon-ticker__text">100% NATURAL</span>
			<span class="ribbon-ticker__heart">♥</span>
			<span class="ribbon-ticker__text">NATURALLY REFRESHING</span>
			<span class="ribbon-ticker__heart">♥</span>
			<span class="ribbon-ticker__text">ALWAYS MADE WITH CARE</span>
		<?php endfor; ?>
	</div>
</div>

<footer class="site-footer" role="contentinfo">
	
	<!-- Top Gold Border Accent -->
	<div class="site-footer__gold-bar" aria-hidden="true"></div>

	<!-- Sugarcane Stalk Botanical Watermark on Right Edge -->
	<div class="site-footer__cane-watermark" aria-hidden="true" style="background-image: url('<?php echo esc_url( \VintageSoul\Support\UrlHelper::resolve( 'assets/images/backgrounds/pure_sugarcane_forest_trees_engraving.jpg' ) ); ?>');"></div>

	<div class="container site-footer__container">
		<div class="site-footer__grid">

			<!-- Column 1: Brand Heritage -->
			<div class="site-footer__col site-footer__col--brand">
				<?php View::component( 'logo/logo', array( 'context' => 'footer' ) ); ?>
				<p class="site-footer__tagline"><?php echo esc_html( $tagline ); ?></p>
				
				<div class="site-footer__social-list">
					<a class="site-footer__social-btn" href="<?php echo esc_url( SettingsService::social_url( 'instagram', 'https://instagram.com/thecanehouseuk' ) ); ?>" target="_blank" rel="noopener" aria-label="Instagram">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
					</a>
					<a class="site-footer__social-btn" href="<?php echo esc_url( SettingsService::social_url( 'facebook', 'https://facebook.com/thecanehouseuk' ) ); ?>" target="_blank" rel="noopener" aria-label="Facebook">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
					</a>
					<a class="site-footer__social-btn" href="<?php echo esc_url( SettingsService::whatsapp_url() ); ?>" target="_blank" rel="noopener" aria-label="WhatsApp">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
					</a>
				</div>
			</div>

			<!-- Column 2: Quick Links -->
			<div class="site-footer__col">
				<h3 class="site-footer__heading">QUICK LINKS</h3>
				<ul class="site-footer__links">
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a></li>
					<li><a href="<?php echo esc_url( home_url( '/history' ) ); ?>">All About Cane</a></li>
					<li><a href="<?php echo esc_url( home_url( '/about' ) ); ?>">About Us</a></li>
					<li><a href="<?php echo esc_url( home_url( '/events' ) ); ?>">Events & Catering</a></li>
					<li><a href="<?php echo esc_url( home_url( '/franchise' ) ); ?>">Franchise</a></li>
					<li><a href="<?php echo esc_url( home_url( '/blog' ) ); ?>">The Cane Chronicle</a></li>
					<li><a href="<?php echo esc_url( home_url( '/contact' ) ); ?>">Contact</a></li>
				</ul>
			</div>

			<!-- Column 3: Direct Contact -->
			<div class="site-footer__col site-footer__col--contact">
				<h3 class="site-footer__heading">CONTACT US</h3>
				<div class="site-footer__contact-items">
					<a class="site-footer__contact-row" href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ); ?>">
						<span class="site-footer__contact-icon">
							<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
						</span>
						<span><?php echo esc_html( $phone ); ?></span>
					</a>
					<a class="site-footer__contact-row" href="mailto:<?php echo esc_attr( $email ); ?>">
						<span class="site-footer__contact-icon">
							<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
						</span>
						<span><?php echo esc_html( $email ); ?></span>
					</a>
					<div class="site-footer__contact-row">
						<span class="site-footer__contact-icon">
							<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
						</span>
						<span><?php echo esc_html( $address ); ?></span>
					</div>
				</div>
			</div>

			<!-- Column 4: Food Hygiene & Standards (Right Side) -->
			<div class="site-footer__col site-footer__col--standards">
				<h3 class="site-footer__heading">FOOD HYGIENE & TRUST</h3>
				
				<!-- Official 5-Star Food Hygiene Stamp -->
				<div class="footer-hygiene-badge">
					<div class="footer-hygiene-badge__header">
						<span class="fsa-title">FOOD HYGIENE RATING</span>
					</div>
					<div class="footer-hygiene-badge__score-row">
						<span class="fsa-num">0</span>
						<span class="fsa-num">1</span>
						<span class="fsa-num">2</span>
						<span class="fsa-num">3</span>
						<span class="fsa-num">4</span>
						<span class="fsa-num fsa-num--active">5</span>
					</div>
					<div class="footer-hygiene-badge__verdict">
						<span class="fsa-rating-text">VERY GOOD</span>
						<span class="fsa-star-icon">★★★★★</span>
					</div>
				</div>

				<div class="footer-accreditations-row">
					<span class="footer-cert-pill">✓ ISO 22000:2018 Certified</span>
					<span class="footer-cert-pill">✓ Soil Association Organic</span>
					<span class="footer-cert-pill">✓ SALSA UK Accredited</span>
					<span class="footer-cert-pill">✓ 100% Zero Additive Tested</span>
				</div>
			</div>

		</div>
	</div>

	<!-- Bottom Legal Bar -->
	<div class="site-footer__bottom">
		<div class="container site-footer__bottom-inner">
			<span class="site-footer__copyright">&copy; <?php echo esc_html( $year ); ?> The Cane House. All Rights Reserved.</span>
			<div class="site-footer__legal">
				<a href="<?php echo esc_url( home_url( '/privacy' ) ); ?>">Privacy Policy</a>
				<span>·</span>
				<a href="<?php echo esc_url( home_url( '/terms' ) ); ?>">Terms of Service</a>
			</div>
		</div>
	</div>

</footer>
