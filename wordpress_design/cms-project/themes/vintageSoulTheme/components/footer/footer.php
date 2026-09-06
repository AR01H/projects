<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\SettingsService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

if ( ! function_exists( 'vst_resolve_footer_item' ) ) {
	function vst_resolve_footer_item( array $q_link ): array {
		$raw_label = (string) ( $q_link['label'] ?? '' );
		$url       = (string) ( $q_link['url'] ?? '/' );
		$icon      = (string) ( $q_link['icon'] ?? '' );

		if ( empty( $icon ) ) {
			if ( str_starts_with( $url, 'tel:' ) || preg_match( '/\b(\+?[\d\s\-()]{9,})\b/', $raw_label ) || str_contains( $raw_label, '📞' ) || str_contains( $raw_label, '☎' ) ) {
				$icon = 'phone';
			} elseif ( str_starts_with( $url, 'mailto:' ) || str_contains( $raw_label, '@' ) || str_contains( $raw_label, '✉' ) || str_contains( $raw_label, '📧' ) ) {
				$icon = 'mail';
			} elseif ( str_contains( $url, 'wa.me' ) || str_contains( strtolower( $raw_label ), 'whatsapp' ) || str_contains( $raw_label, '💬' ) ) {
				$icon = 'whatsapp';
			} elseif ( str_contains( $raw_label, '📍' ) || str_contains( strtolower( $raw_label ), 'london' ) || str_contains( strtolower( $raw_label ), 'sutton' ) || str_contains( strtolower( $raw_label ), 'location' ) || str_contains( strtolower( $raw_label ), 'address' ) ) {
				$icon = 'pin';
			} elseif ( str_contains( $raw_label, '⏰' ) || str_contains( $raw_label, '⏱' ) || str_contains( strtolower( $raw_label ), 'mon –' ) || str_contains( strtolower( $raw_label ), 'hours' ) || str_contains( strtolower( $raw_label ), 'am –' ) ) {
				$icon = 'clock';
			}
		}

		$clean_label = preg_replace( '/^[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{1F600}-\x{1F64F}\x{1F680}-\x{1F6FF}\s]+/u', '', $raw_label );
		$clean_label = trim( (string) $clean_label );
		if ( '' === $clean_label ) {
			$clean_label = $raw_label;
		}

		return array(
			'label' => $clean_label,
			'icon'  => $icon,
			'url'   => $url,
		);
	}
}

$footer_data = (array) ( JsonFileProvider::read( 'data/content/footer.json' ) ?? array() );
$ticker_data = (array) ( JsonFileProvider::read( 'data/content/ticker.json' ) ?? array() );

$ticker_items = (array) ( $ticker_data['items'] ?? array() );
$labels       = (array) ( $labels ?? ( $footer_data['labels'] ?? array() ) );
$columns      = (array) ( $columns ?? array() );
$quick_links  = (array) ( $quick_links ?? ( $footer_data['quick_links'] ?? array() ) );
$legal_links  = (array) ( $legal_links ?? ( $footer_data['bottom_links'] ?? array() ) );
$standards    = (array) ( $standards ?? ( $footer_data['standards'] ?? array() ) );

$phone             = (string) ( $phone ?? SettingsService::phone() );
$email             = (string) ( $email ?? SettingsService::email() );
$address           = (string) ( $address ?? SettingsService::address() );
$tagline           = (string) ( $tagline ?? ( $footer_data['brand']['tagline'] ?? SettingsService::tagline_fallback() ) );
$watermark_img     = (string) ( $watermark ?? ( $footer_data['brand']['watermark'] ?? 'assets/images/backgrounds/pure_sugarcane_forest_trees_engraving.jpg' ) );
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

			<!-- Dynamic Navigation Columns from CMS Navigation Editor / DB -->
			<?php if ( ! empty( $columns ) && is_array( $columns ) ) : ?>
				<?php foreach ( $columns as $column ) :
					$col_title = (string) ( $column['title'] ?? 'Links' );
					$col_items = (array) ( $column['items'] ?? array() );
					if ( empty( $col_items ) && empty( $col_title ) ) continue;
				?>
					<div class="site-footer__col">
						<h3 class="site-footer__heading"><?php echo esc_html( $col_title ); ?></h3>
						<?php if ( ! empty( $col_items ) ) : ?>
							<ul class="site-footer__links">
								<?php foreach ( $col_items as $q_link ) :
									$q_link   = (array) $q_link;
									$resolved = vst_resolve_footer_item( $q_link );
									$q_label  = $resolved['label'];
									if ( '' === $q_label ) continue;
									$q_icon   = $resolved['icon'];
									$q_path   = (string) $resolved['url'];
									$q_url    = 0 === strpos( $q_path, 'http' ) || 0 === strpos( $q_path, 'tel:' ) || 0 === strpos( $q_path, 'mailto:' ) ? $q_path : UrlHelper::resolve( $q_path );
									$is_hl    = ! empty( $q_link['highlight'] );
									$has_icon = ! empty( $q_icon );
								?>
									<li class="<?php echo $is_hl ? 'site-footer__link-highlight ' : ''; ?><?php echo $has_icon ? 'site-footer__item--has-icon' : ''; ?>">
										<a href="<?php echo esc_url( $q_url ); ?>" class="<?php echo $has_icon ? 'site-footer__link-with-icon' : ''; ?>">
											<?php if ( $has_icon ) : ?>
												<span class="site-footer__icon-badge" aria-hidden="true">
													<?php echo IconHelper::get( $q_icon, '#f6d599', 13 ); // phpcs:ignore ?>
												</span>
											<?php endif; ?>
											<span class="site-footer__link-text"><?php echo esc_html( $q_label ); ?></span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<!-- Fallback Single Column if no DB columns configured -->
				<?php if ( ! empty( $quick_links ) ) : ?>
					<div class="site-footer__col">
						<h3 class="site-footer__heading"><?php echo esc_html( (string) ( $labels['quick_links_heading'] ?? ( $labels['quick_links'] ?? 'QUICK LINKS' ) ) ); ?></h3>
						<ul class="site-footer__links">
							<?php foreach ( $quick_links as $q_link ) :
								$q_link   = (array) $q_link;
								$resolved = vst_resolve_footer_item( $q_link );
								$q_label  = $resolved['label'];
								if ( '' === $q_label ) continue;
								$q_icon   = $resolved['icon'];
								$q_path   = (string) $resolved['url'];
								$q_url    = 0 === strpos( $q_path, 'http' ) || 0 === strpos( $q_path, 'tel:' ) || 0 === strpos( $q_path, 'mailto:' ) ? $q_path : UrlHelper::resolve( $q_path );
								$has_icon = ! empty( $q_icon );
							?>
								<li class="<?php echo $has_icon ? 'site-footer__item--has-icon' : ''; ?>">
									<a href="<?php echo esc_url( $q_url ); ?>" class="<?php echo $has_icon ? 'site-footer__link-with-icon' : ''; ?>">
										<?php if ( $has_icon ) : ?>
											<span class="site-footer__icon-badge" aria-hidden="true">
												<?php echo IconHelper::get( $q_icon, '#f6d599', 13 ); // phpcs:ignore ?>
											</span>
										<?php endif; ?>
										<span class="site-footer__link-text"><?php echo esc_html( $q_label ); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<!-- Food Hygiene & Standards (Right Side Stamp / Extra Logo) -->
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

	<!-- Bottom Legal Bar (Dynamic from CMS Plugin Navigation / JSON) -->
	<div class="site-footer__bottom">
		<div class="container site-footer__bottom-inner">
			<span class="site-footer__copyright">&copy; <?php echo esc_html( $year ); ?> The Cane House. <?php echo esc_html( (string) ( $labels['rights_text'] ?? ( $labels['rights'] ?? 'All Rights Reserved.' ) ) ); ?></span>
			<div class="site-footer__legal">
				<?php foreach ( (array) $legal_links as $b_idx => $b_link ) :
					$b_link  = (array) $b_link;
					$b_label = (string) ( $b_link['label'] ?? '' );
					if ( '' === $b_label ) continue;
					$b_url   = (string) ( $b_link['url'] ?? '#' );
				?>
					<?php if ( $b_idx > 0 ) : ?><span>·</span><?php endif; ?>
					<a href="<?php echo esc_url( UrlHelper::resolve( $b_url ) ); ?>"><?php echo esc_html( $b_label ); ?></a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

</footer>
