<?php
/**
 * Main Footer – Vintage green footer matching reference design.
 * Entirely driven by CMS Plugin (ah_site_settings and ah_cms_footer)
 */
defined( 'ABSPATH' ) || exit;

global $wpdb;
$settings_table = $wpdb->prefix . 'ah_site_settings';
$db_settings = $wpdb->get_results("SELECT setting_key, setting_val FROM {$settings_table}");
$plugin_settings = [];
if ($db_settings) {
    foreach ($db_settings as $row) {
        $plugin_settings[$row->setting_key] = $row->setting_val;
    }
}

// Extract settings
$phone = $plugin_settings['phone'] ?? '';
$email = $plugin_settings['email'] ?? '';
$address = $plugin_settings['address'] ?? '';
$brand_tagline = $plugin_settings['footer_tagline'] ?? '';
$brand_name = NT_BRAND_NAME; // Fallback, could be dynamically fetched if in settings
$year = gmdate('Y');
$has_logo = has_custom_logo();
$brand_logo = $plugin_settings['footer_logo'] ?? $plugin_settings['logo_image'] ?? '';

// Socials
$social_keys = ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube'];
$nt_socials = [];
foreach ($social_keys as $key) {
    $val = $plugin_settings[$key . '_url'] ?? '';
    if (!empty($val)) {
        $nt_socials[$key] = $val;
    }
}

// Navigation & Columns
$plugin_columns = [];
$bottom_links = [];
$lbl_quick = 'Quick Links';
$lbl_products = 'Our Products';
$lbl_contact = $plugin_settings['contact_heading'] ?? 'Contact Us';
$lbl_connect = $plugin_settings['connect_label'] ?? 'Connect:';
$lbl_rights = $plugin_settings['rights_text'] ?? 'All Rights Reserved.';

if ( class_exists( '\Ah\Cms\Feature\Navigation\Controller\NavigationAdminController' ) ) {
    $plugin_footer = \Ah\Cms\Feature\Navigation\Controller\NavigationAdminController::get_footer_data();
    if (!empty($plugin_footer['columns'])) {
        $plugin_columns = $plugin_footer['columns'];
    }
    if (!empty($plugin_footer['legal_links'])) {
        $bottom_links = $plugin_footer['legal_links'];
    }
}

$footer_classes = 'app-footer';
if ( is_front_page() || is_home() ) {
	$footer_classes .= ' app-footer--home';
}
// Removed hardcoded background image logic as it belongs to old json
$footer_style = '';
?>

<footer class="<?php echo esc_attr( $footer_classes ); ?>" role="contentinfo"<?php echo $footer_style; ?>>
	<div class="app-footer__torn-edge-top"></div>
	<!-- Decorative Corners -->
	<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/corner-ornament.svg' ); ?>" class="app-footer__corner app-footer__corner--left" alt="" aria-hidden="true" />
	<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/corner-ornament.svg' ); ?>" class="app-footer__corner app-footer__corner--right" alt="" aria-hidden="true" />

	<div class="container">
		<div class="app-footer__inner">

			<!-- Brand Column -->
			<div class="app-footer__col">
				<a href="<?php echo esc_url( home_url('/') ); ?>" class="app-footer__logo-link">
					<?php if ( $has_logo ) :
						the_custom_logo();
					elseif ( $brand_logo ) : ?>
						<img src="<?php echo esc_url( get_template_directory_uri() . '/' . $brand_logo ); ?>" alt="<?php echo esc_attr( $brand_name ); ?> Logo" class="app-footer__brand-img" style=" margin-bottom: 12px; display: block;" />
					<?php else : ?>
						<div class="app-footer__brand-fallback">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icons/brand-star.svg' ); ?>" class="app-footer__brand-icon" alt="" aria-hidden="true" />
							<div class="app-footer__brand-name"><?php echo esc_html( $brand_name ); ?></div>
						</div>
					<?php endif; ?>
				</a>
				<?php if ( $brand_tagline ) : ?>
					<p class="app-footer__tagline">
						<?php echo esc_html( $brand_tagline ); ?>
					</p>
				<?php endif; ?>
			</div>

			<?php if ( !empty($plugin_columns) ) : ?>
				<?php foreach ( $plugin_columns as $col ) : ?>
					<div class="app-footer__col">
						<h4 class="app-footer__heading"><?php echo esc_html( $col['title'] ); ?></h4>
						<ul class="app-footer__links">
							<?php foreach ( $col['items'] as $link ) : ?>
								<li>
									<a href="<?php echo esc_url( App_Helpers::link($link['url'] ?? '#') ); ?>">
										<?php echo esc_html($link['label'] ?? ''); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>

			<!-- Contact -->
			<div class="app-footer__col">
				<h4 class="app-footer__heading"><?php echo esc_html( $lbl_contact ); ?></h4>
				<?php if ($phone) : ?>
					<p style="margin:0 0 8px; font-size:0.9rem;">
						<a href="tel:<?php echo esc_attr(preg_replace('/[^+0-9]/', '', $phone)); ?>"
						   style="color:var(--trad-cream);">
							📞 <?php echo esc_html($phone); ?>
						</a>
					</p>
				<?php endif; ?>
				<?php if ($email) : ?>
					<p style="margin:0 0 8px; font-size:0.9rem;">
						<a href="mailto:<?php echo esc_attr($email); ?>"
						   style="color:var(--trad-cream); word-break:break-all;">
							✉️ <?php echo esc_html($email); ?>
						</a>
					</p>
				<?php endif; ?>
				<?php if ($address) : ?>
					<p style="margin:0; font-size:0.88rem; color:rgba(250,240,216,0.65);">
						📍 <?php echo esc_html($address); ?>
					</p>
				<?php endif; ?>
				
				<?php if ( $nt_socials ) : ?>
					<div style="display:flex; gap:16px; margin-top:24px; flex-wrap:wrap; align-items:center;">
						<span style="font-size:0.8rem; text-transform:uppercase; color:var(--trad-gold); letter-spacing:0.1em; margin-right:4px;"><?php echo esc_html( $lbl_connect ); ?></span>
						<?php foreach ( $nt_socials as $net => $url ) : 
							$icon_file = in_array($net, ['instagram', 'youtube', 'facebook', 'whatsapp', 'linkedin']) ? 'social-' . $net . '.svg' : 'default-social.svg';
							$icon_svg = '<img src="' . esc_url( get_template_directory_uri() . '/assets/images/icons/' . $icon_file ) . '" alt="' . esc_attr( ucfirst( $net ) ) . '" width="22" height="22" />';
						?>
							<a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr(ucfirst($net)); ?>"
							   style="color:var(--trad-gold); display:flex; align-items:center; justify-content:center; transition: opacity 0.2s; opacity: 0.85;"
							   onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.85'">
								<?php echo $icon_svg; ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

		</div><!-- /.app-footer__inner -->

		<div class="app-footer__torn-edge-bottom"></div>
		<div class="app-footer__bottom">
			<?php if ( ! empty( $bottom_links ) ) : ?>
				<div class="app-footer__policies">
					<?php foreach ( $bottom_links as $link ) : ?>
						<a href="<?php echo esc_url( App_Helpers::link($link['url'] ?? '#') ); ?>">
							<?php echo esc_html($link['label'] ?? ''); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<span>&copy; <?php echo esc_html($year); ?> <?php echo esc_html($brand_name); ?>. <?php echo esc_html( $lbl_rights ); ?></span>
		</div>
	</div>
</footer>

<!-- Scroll to Top -->
<button id="app-scroll-to-top" class="app-scroll-to-top" aria-label="Scroll to top">
	<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
		 stroke-linecap="round" aria-hidden="true" class="app-scroll-arrow">
		<path d="M7 14.5l5-5 5 5"/>
	</svg>
</button>
