<?php
/**
 * Main Footer – Vintage green footer matching reference design.
 */
defined( 'ABSPATH' ) || exit;

$footer_data   = NT_Data_Provider::get('footer');
$footer_links  = $footer_data['quick_links'] ?? [];
$products      = $footer_data['products'] ?? [];
$bottom_links  = $footer_data['bottom_links'] ?? [];

// Column headings + small chrome strings - edit in admin/data/footer.json ("labels").
$labels        = $footer_data['labels'] ?? [];
$lbl_quick     = $labels['quick_links_heading'] ?? 'Quick Links';
$lbl_products  = $labels['products_heading']    ?? 'Our Products';
$lbl_contact   = $labels['contact_heading']     ?? 'Contact Us';
$lbl_connect   = $labels['connect_label']       ?? 'Connect:';
$lbl_rights    = $labels['rights_text']         ?? 'All Rights Reserved.';
$aria_top      = app_data('ui')['aria']['scroll_to_top'] ?? 'Scroll to top';

$brand_data    = $footer_data['brand'] ?? [];
$brand_logo    = $brand_data['logo_image'] ?? '';
$brand_name    = $brand_data['name'] ?? NT_BRAND_NAME;
$brand_est     = $brand_data['established'] ?? '';
$brand_tagline = $brand_data['tagline'] ?? (defined('NT_BRAND_TAGLINE') ? NT_BRAND_TAGLINE : '');

$phone         = app_option('general', 'phone', NT_BRAND_PHONE);
$email         = app_option('general', 'email', NT_BRAND_EMAIL);
$address       = app_option('general', 'address');
$has_logo      = has_custom_logo();
$year          = gmdate('Y');
$nt_socials    = array_filter( (array) ($footer_data['socials'] ?? []) );

$footer_bg     = $footer_data['background']['image'] ?? '';
$footer_classes = 'app-footer';
if ( is_front_page() || is_home() ) {
	$footer_classes .= ' app-footer--home';
}
$footer_style = '';
if ( $footer_bg ) {
	$footer_style = ' style="background-image: url(' . esc_url(get_template_directory_uri() . '/' . $footer_bg) . '); background-size: 450px auto; background-repeat: no-repeat; background-position: bottom right; background-blend-mode: soft-light;"';
}
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
							<?php if ( $brand_est ) : ?>
								<div class="app-footer__brand-est"><?php echo esc_html( $brand_est ); ?></div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</a>
				<?php if ( $brand_tagline ) : ?>
					<p class="app-footer__tagline">
						<?php echo esc_html( $brand_tagline ); ?>
					</p>
				<?php endif; ?>
			</div>

			<!-- Quick Links -->
			<div class="app-footer__col">
				<h4 class="app-footer__heading"><?php echo esc_html( $lbl_quick ); ?></h4>
				<ul class="app-footer__links">
					<?php foreach ( $footer_links as $link ) : ?>
						<li>
							<a href="<?php echo esc_url( app_link($link['url'] ?? '#') ); ?>">
								<?php echo esc_html($link['label'] ?? ''); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<!-- Our Products -->
			<div class="app-footer__col">
				<h4 class="app-footer__heading"><?php echo esc_html( $lbl_products ); ?></h4>
				<ul class="app-footer__links">
					<?php foreach ( $products as $p ) : ?>
						<li>
							<a href="<?php echo esc_url( home_url($p['url']) ); ?>">
								<?php echo esc_html($p['label']); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

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
							✉ <?php echo esc_html($email); ?>
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
							$icon_file = in_array($net, ['instagram', 'youtube', 'facebook', 'whatsapp']) ? 'social-' . $net . '.svg' : 'default-social.svg';
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
						<a href="<?php echo esc_url( app_link($link['url'] ?? '#') ); ?>">
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
<button id="app-scroll-to-top" class="app-scroll-to-top" aria-label="<?php echo esc_attr( $aria_top ); ?>">
	<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
		 stroke-linecap="round" aria-hidden="true" class="app-scroll-arrow">
		<path d="M7 14.5l5-5 5 5"/>
	</svg>
</button>
