<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\SettingsService;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

$footer_data = (array) ( JsonFileProvider::read( 'data/content/footer.json' ) ?? array() );
$ticker_data = (array) ( JsonFileProvider::read( 'data/content/ticker.json' ) ?? array() );

$ticker_items = (array) ( $ticker_data['items'] ?? array() );
$labels       = (array) ( $footer_data['labels'] ?? array() );
$quick_links  = (array) ( $footer_data['quick_links'] ?? array() );
$standards    = (array) ( $footer_data['standards'] ?? array() );

$phone             = (string) ( $phone ?? SettingsService::phone() );
$email             = (string) ( $email ?? SettingsService::email() );
$address           = (string) ( $address ?? SettingsService::address() );
$tagline           = (string) ( $tagline ?? ( $footer_data['brand']['tagline'] ?? SettingsService::tagline_fallback() ) );
$watermark_img     = (string) ( $footer_data['brand']['watermark'] ?? 'assets/images/backgrounds/pure_sugarcane_forest_trees_engraving.jpg' );
$standards_heading = (string) ( $standards['heading'] ?? ( $labels['standards_heading'] ?? 'FOOD HYGIENE & TRUST' ) );
$standards_img     = (string) ( $standards['badge_img'] ?? 'assets/images/certifications/food-hygiene-rating-5.png' );
$standards_alt     = (string) ( $standards['alt'] ?? 'Food Hygiene Rating 5 — Very Good' );
$standards_title   = (string) ( $standards['title'] ?? 'Verify 5-Star Food Hygiene Rating' );
$standards_url     = (string) ( $standards['url'] ?? 'https://ratings.food.gov.uk/' );
$year              = gmdate( 'Y' );
?>
<!-- Pre-Footer Ticker Ribbon -->
<?php if ( ! empty( $ticker_items ) ) : ?>
	<div class="ribbon-ticker ribbon-ticker--red" aria-hidden="true">
		<div class="ribbon-ticker__track">
			<?php for ( $r = 0; $r < 4; $r++ ) : ?>
				<?php foreach ( $ticker_items as $t_item ) : ?>
					<span class="ribbon-ticker__heart">♥</span>
					<span class="ribbon-ticker__text"><?php echo esc_html( (string) $t_item ); ?></span>
				<?php endforeach; ?>
			<?php endfor; ?>
		</div>
	</div>
<?php endif; ?>

<footer class="site-footer" role="contentinfo">
	
	<!-- Top Gold Border Accent -->
	<div class="site-footer__gold-bar" aria-hidden="true"></div>

	<!-- Sugarcane Stalk Botanical Watermark on Right Edge -->
	<div class="site-footer__cane-watermark" aria-hidden="true" style="background-image: url('<?php echo esc_url( UrlHelper::resolve( $watermark_img ) ); ?>');"></div>

	<div class="container site-footer__container">
		<div class="site-footer__grid">

			<!-- Column 1: Brand Heritage -->
			<div class="site-footer__col site-footer__col--brand">
				<?php View::component( 'logo/logo', array( 'context' => 'footer' ) ); ?>
				<?php if ( '' !== $tagline ) : ?>
					<p class="site-footer__tagline"><?php echo esc_html( $tagline ); ?></p>
				<?php endif; ?>
				
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

			<!-- Column 2: Quick Links (Dynamic from JSON) -->
			<div class="site-footer__col">
				<h3 class="site-footer__heading"><?php echo esc_html( (string) ( $labels['quick_links_heading'] ?? 'QUICK LINKS' ) ); ?></h3>
				<?php if ( ! empty( $quick_links ) ) : ?>
					<ul class="site-footer__links">
						<?php foreach ( $quick_links as $q_link ) :
							$q_label = (string) ( $q_link['label'] ?? '' );
							$q_path  = (string) ( $q_link['url'] ?? '/' );
							$q_url   = 0 === strpos( $q_path, 'http' ) ? $q_path : home_url( $q_path );
						?>
							<li><a href="<?php echo esc_url( $q_url ); ?>"><?php echo esc_html( $q_label ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<!-- Column 3: Direct Contact -->
			<div class="site-footer__col site-footer__col--contact">
				<h3 class="site-footer__heading"><?php echo esc_html( (string) ( $labels['contact_heading'] ?? 'CONTACT US' ) ); ?></h3>
				<div class="site-footer__contact-items">
					<?php if ( '' !== $phone ) : ?>
						<a class="site-footer__contact-row" href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ); ?>">
							<span class="site-footer__contact-icon">
								<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
							</span>
							<span><?php echo esc_html( $phone ); ?></span>
						</a>
					<?php endif; ?>
					<?php if ( '' !== $email ) : ?>
						<a class="site-footer__contact-row" href="mailto:<?php echo esc_attr( $email ); ?>">
							<span class="site-footer__contact-icon">
								<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
							</span>
							<span><?php echo esc_html( $email ); ?></span>
						</a>
					<?php endif; ?>
					<?php if ( '' !== $address ) : ?>
						<div class="site-footer__contact-row">
							<span class="site-footer__contact-icon">
								<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f6d599" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
							</span>
							<span><?php echo esc_html( $address ); ?></span>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Column 4: Food Hygiene & Standards (Right Side - Dynamic from JSON) -->
			<?php if ( '' !== $standards_img ) : ?>
				<div class="site-footer__col site-footer__col--standards">
					<h3 class="site-footer__heading"><?php echo esc_html( $standards_heading ); ?></h3>
					
					<!-- Official 5-Star Food Hygiene Stamp Image -->
					<a href="<?php echo esc_url( $standards_url ); ?>" target="_blank" rel="noopener noreferrer" class="footer-hygiene-badge-link" title="<?php echo esc_attr( $standards_title ); ?>">
						<img src="<?php echo esc_url( UrlHelper::resolve( $standards_img ) ); ?>" 
							 alt="<?php echo esc_attr( $standards_alt ); ?>" 
							 class="footer-hygiene-badge__img" 
							 width="260" 
							 height="110" 
							 loading="lazy">
					</a>
				</div>
			<?php endif; ?>

		</div>
	</div>

	<!-- Bottom Legal Bar -->
	<div class="site-footer__bottom">
		<div class="container site-footer__bottom-inner">
			<span class="site-footer__copyright">&copy; <?php echo esc_html( $year ); ?> The Cane House. <?php echo esc_html( (string) ( $labels['rights_text'] ?? 'All Rights Reserved.' ) ); ?></span>
			<div class="site-footer__legal">
				<?php foreach ( (array) ( $footer_data['bottom_links'] ?? array() ) as $b_idx => $b_link ) : ?>
					<?php if ( $b_idx > 0 ) : ?><span>·</span><?php endif; ?>
					<a href="<?php echo esc_url( home_url( (string) ( $b_link['url'] ?? '#' ) ) ); ?>"><?php echo esc_html( (string) ( $b_link['label'] ?? '' ) ); ?></a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

</footer>
