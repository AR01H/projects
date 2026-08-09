<?php
/**
 * Main Header – Vintage parchment-style navigation for traditional template.
 * Matches reference: parchment background, green logo and text, green/gold CTA button.
 */
defined( 'ABSPATH' ) || exit;

$nav_items = [];
$cta_url = '/#contact';
$cta_line1 = 'BOOK US';
$cta_line2 = 'YOUR EVENT';
$cta_mobile = 'Book Us For Your Event';

global $wpdb;
$settings_table = $wpdb->prefix . 'ah_site_settings';
$db_settings = $wpdb->get_results("SELECT setting_key, setting_val FROM {$settings_table}");
$plugin_settings = [];
if ($db_settings) {
    foreach ($db_settings as $row) {
        $plugin_settings[$row->setting_key] = $row->setting_val;
    }
}

$logo_path   = $plugin_settings['header_logo'] ?? $plugin_settings['logo_image'] ?? 'assets/images/logo.png';
$has_logo    = has_custom_logo();
$current_url = trailingslashit( strtok( $_SERVER['REQUEST_URI'], '?' ) );
$brand_name  = NT_BRAND_NAME;

if ( class_exists( '\Ah\Cms\Feature\Navigation\Controller\NavigationAdminController' ) ) {
	$nav_items = \Ah\Cms\Feature\Navigation\Controller\NavigationAdminController::get_navigation_data();
	$cta_data = \Ah\Cms\Feature\Navigation\Controller\NavigationAdminController::get_nav_cta_data();
	$full_label = $cta_data['label'] ?? 'BOOK US YOUR EVENT';
	$parts = explode(' ', $full_label);
	$cta_line1 = $parts[0] ?? 'BOOK';
	if (count($parts) > 1) {
		$cta_line1 .= ' ' . $parts[1];
		$cta_line2 = implode(' ', array_slice($parts, 2));
	} else {
		$cta_line2 = '';
	}
	$cta_mobile = $full_label;
	$cta_url = $cta_data['url'] ?? '/#contact';
}

// Accessibility labels - edit in admin/data/ui.json ("aria").
$aria         = App_Data_Provider::get('ui')['aria'] ?? [];
$aria_home    = $aria['home']       ?? 'Home';
$aria_menu    = $aria['open_menu']  ?? 'Open menu';
$aria_mobnav  = $aria['mobile_nav'] ?? 'Mobile Navigation';

function app_nav_is_active( $url, $current ) {
	return trailingslashit( $url ) === $current ? ' is-active' : '';
}
?>
<!-- ── Main Navigation ─────────────────────────────────────────── -->
<header id="app-nav" class="app-nav" role="banner">
	<div class="container">
		<div class="app-nav__inner">

			<!-- Brand / Logo -->
			<a href="<?php echo esc_url( home_url('/') ); ?>" class="app-nav__logo" aria-label="<?php echo esc_attr( $aria_home ); ?>">
				<?php if ( $has_logo ) :
					the_custom_logo();
				else : ?>
					<img src="<?php echo esc_url( get_theme_file_uri( $logo_path ) ); ?>" 
						 alt="<?php echo esc_attr( $brand_name ); ?> Logo" 
						 class="app-nav__logo-img">
				<?php endif; ?>
			</a>

			<!-- Desktop Nav Links -->
			<ul class="app-nav__links" id="app-nav-links" role="list">
				<?php foreach ( (array) $nav_items as $item ) : ?>
					<?php $has_children = !empty($item['submenu']); ?>
					<li class="<?php echo $has_children ? 'app-nav__has-sub' : ''; ?>">
						<a href="<?php echo esc_url( App_Helpers::link( $item['url'] ?? $item['href'] ?? '/' ) ); ?>"
						   class="app-nav__link<?php echo app_nav_is_active( App_Helpers::link( $item['url'] ?? $item['href'] ?? '/' ), $current_url ); ?>">
							<?php echo esc_html( $item['label'] ); ?>
							<?php if ($has_children) : ?>
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 4px; vertical-align: middle;"><path d="M6 9l6 6 6-6"/></svg>
							<?php endif; ?>
						</a>
						<?php if ( $has_children ) : ?>
							<ul class="app-nav__submenu">
								<?php foreach ( (array) $item['submenu'] as $child ) : ?>
									<li>
										<a href="<?php echo esc_url( App_Helpers::link( $child['url'] ?? $child['href'] ?? '/' ) ); ?>">
											<?php echo esc_html( $child['label'] ); ?>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>

			<!-- CTA + Hamburger -->
			<div class="app-nav__actions">
				<a href="<?php echo esc_url( App_Helpers::link( $cta_url ) ); ?>" class="app-nav__cta-btn">
					<span class="app-nav__cta-text">
						<span class="app-nav__cta-line1"><?php echo esc_html( $cta_line1 ); ?></span>
						<span class="app-nav__cta-line2"><?php echo esc_html( $cta_line2 ); ?></span>
					</span>
					<span class="app-nav__cta-icon">
						<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 2h14v20H5V2z"/><path d="M9 6h6"/><path d="M12 6v8"/><path d="M9 14h6"/><circle cx="12" cy="18" r="1.5"/></svg>
					</span>
				</a>
				<button class="app-nav__hamburger" id="app-hamburger"
						aria-label="<?php echo esc_attr( $aria_menu ); ?>" aria-expanded="false" aria-controls="app-mobile-nav">
					<span></span><span></span><span></span>
				</button>
			</div>

		</div>
	</div>
</header>

<!-- Mobile Nav -->
<nav class="app-mobile-nav" id="app-mobile-nav" aria-label="<?php echo esc_attr( $aria_mobnav ); ?>">
	<?php foreach ( (array) $nav_items as $item ) : ?>
		<?php $has_children = !empty($item['submenu']); ?>
		<div class="app-mobile-nav__item <?php echo $has_children ? 'app-nav__has-sub' : ''; ?>">
			<a href="<?php echo esc_url( App_Helpers::link( $item['url'] ?? $item['href'] ?? '/' ) ); ?>"
			   class="app-mobile-nav__link<?php echo app_nav_is_active( App_Helpers::link( $item['url'] ?? $item['href'] ?? '/' ), $current_url ); ?>">
				<?php echo esc_html( $item['label'] ); ?>
			</a>
			<?php if ( $has_children ) : ?>
				<div class="app-mobile-nav__submenu">
					<?php foreach ( (array) $item['submenu'] as $child ) : ?>
						<a href="<?php echo esc_url( App_Helpers::link( $child['url'] ?? $child['href'] ?? '/' ) ); ?>">
							<?php echo esc_html( $child['label'] ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
	<a href="<?php echo esc_url( App_Helpers::link( $cta_url ) ); ?>" class="app-mobile-nav__cta">
		<?php echo esc_html( $cta_mobile ); ?>
	</a>
</nav>
