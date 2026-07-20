<?php
/**
 * Main Footer – Vintage green footer matching reference design.
 */
defined( 'ABSPATH' ) || exit;

$nav_fallback  = nt_data('nav');
$footer_links  = $nav_fallback['footer'] ?? [];
$phone         = nt_option('general', 'phone', NT_BRAND_PHONE);
$email         = nt_option('general', 'email', NT_BRAND_EMAIL);
$address       = nt_option('general', 'address');
$has_logo      = has_custom_logo();
$year          = gmdate('Y');
$nt_socials    = array_filter( (array) nt_option('social') );

$products = [
	[ 'label' => 'Classic Sugarcane', 'url' => '/menu/' ],
	[ 'label' => 'Lemon Cane',        'url' => '/menu/' ],
	[ 'label' => 'Ginger Cane',       'url' => '/menu/' ],
	[ 'label' => 'Mint Cane',         'url' => '/menu/' ],
	[ 'label' => 'All Flavours',      'url' => '/menu/' ],
];
?>

<footer class="nt-footer" role="contentinfo">
	<div class="container">
		<div class="nt-footer__inner">

			<!-- Brand Column -->
			<div>
				<a href="<?php echo esc_url( home_url('/') ); ?>" class="nt-footer__logo-link">
					<?php if ( $has_logo ) :
						the_custom_logo();
					else : ?>
						<div class="nt-footer__brand-name"><?php echo wp_kses_post( NT_BRAND_NAME ); ?></div>
					<?php endif; ?>
				</a>
				<p class="nt-footer__tagline">
					<?php echo esc_html( defined('NT_BRAND_TAGLINE') ? NT_BRAND_TAGLINE : 'Pure. Natural. Refreshing.' ); ?>
				</p>
				<?php if ( $nt_socials ) : ?>
					<div style="display:flex; gap:12px; margin-top:16px; flex-wrap:wrap;">
						<?php foreach ( $nt_socials as $net => $url ) : ?>
							<a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener"
							   style="color:var(--trad-gold); font-size:0.8rem; letter-spacing:0.06em; text-transform:uppercase;">
								<?php echo esc_html(ucfirst($net)); ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- Quick Links -->
			<div>
				<h4 class="nt-footer__heading">Quick Links</h4>
				<ul class="nt-footer__links">
					<?php foreach ( $footer_links as $link ) : ?>
						<li>
							<a href="<?php echo esc_url( nt_link($link['url'] ?? '#') ); ?>">
								<?php echo esc_html($link['label'] ?? ''); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<!-- Our Products -->
			<div>
				<h4 class="nt-footer__heading">Our Products</h4>
				<ul class="nt-footer__links">
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
			<div>
				<h4 class="nt-footer__heading">Contact Us</h4>
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
			</div>

		</div><!-- /.nt-footer__inner -->

		<div class="nt-footer__bottom">
			<strong>
				<?php
				$icons = ['🌾 Freshly Made', '🛡️ Hygienic & Safe', '🏆 Best Quality Sugarcane', '👥 Trusted by Thousands'];
				echo implode(' &nbsp;·&nbsp; ', array_map('esc_html', $icons));
				?>
			</strong>
			<br>
			<span>&copy; <?php echo esc_html($year); ?> <?php echo esc_html(NT_BRAND_NAME); ?>. All Rights Reserved.</span>
		</div>
	</div>
</footer>

<!-- Scroll to Top -->
<button id="nt-scroll-to-top" class="nt-scroll-to-top" aria-label="Scroll to top">
	<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
		 stroke-linecap="round" aria-hidden="true" class="nt-scroll-arrow">
		<path d="M7 14.5l5-5 5 5"/>
	</svg>
</button>
