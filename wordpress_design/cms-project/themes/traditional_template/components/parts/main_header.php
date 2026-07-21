<?php
/**
 * Main Header – Vintage parchment-style navigation for traditional template.
 * Matches reference: parchment background, green logo and text, green/gold CTA button.
 */
defined( 'ABSPATH' ) || exit;

$nav_items   = nt_data('nav')['header'] ?? [];
$site_data   = nt_data('site');
$logo_path   = $site_data['brand']['logoImage'] ?? 'assets/images/logo.png';
$has_logo    = has_custom_logo();
$current_url = trailingslashit( strtok( $_SERVER['REQUEST_URI'], '?' ) );
$brand_name  = NT_BRAND_NAME;

function nt_nav_is_active( $url, $current ) {
	return trailingslashit( $url ) === $current ? ' is-active' : '';
}
?>
<!-- ── Main Navigation ─────────────────────────────────────────── -->
<header id="nt-nav" class="nt-nav" role="banner">
	<div class="container">
		<div class="nt-nav__inner">

			<!-- Brand / Logo -->
			<a href="<?php echo esc_url( home_url('/') ); ?>" class="nt-nav__logo" aria-label="Home">
				<?php if ( $has_logo ) :
					the_custom_logo();
				else : ?>
					<img src="<?php echo esc_url( get_theme_file_uri( $logo_path ) ); ?>" 
						 alt="<?php echo esc_attr( $brand_name ); ?> Logo" 
						 class="nt-nav__logo-img">
				<?php endif; ?>
			</a>

			<!-- Desktop Nav Links -->
			<ul class="nt-nav__links" id="nt-nav-links" role="list">
				<?php foreach ( (array) $nav_items as $item ) : ?>
					<?php $has_children = !empty($item['children']); ?>
					<li class="<?php echo $has_children ? 'nt-nav__has-sub' : ''; ?>">
						<a href="<?php echo esc_url( nt_link( $item['url'] ?? $item['href'] ?? '/' ) ); ?>"
						   class="nt-nav__link<?php echo nt_nav_is_active( nt_link( $item['url'] ?? $item['href'] ?? '/' ), $current_url ); ?>">
							<?php echo esc_html( $item['label'] ); ?>
							<?php if ($has_children) : ?>
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 4px; vertical-align: middle;"><path d="M6 9l6 6 6-6"/></svg>
							<?php endif; ?>
						</a>
						<?php if ( $has_children ) : ?>
							<ul class="nt-nav__submenu">
								<?php foreach ( (array) $item['children'] as $child ) : ?>
									<li>
										<a href="<?php echo esc_url( nt_link( $child['url'] ?? $child['href'] ?? '/' ) ); ?>">
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
			<div class="nt-nav__actions">
				<a href="<?php echo esc_url( home_url('/#contact') ); ?>" class="nt-nav__cta-btn">
					<span class="nt-nav__cta-text">
						<span class="nt-nav__cta-line1">BOOK US</span>
						<span class="nt-nav__cta-line2">YOUR EVENT</span>
					</span>
					<span class="nt-nav__cta-icon">
						<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 2h14v20H5V2z"/><path d="M9 6h6"/><path d="M12 6v8"/><path d="M9 14h6"/><circle cx="12" cy="18" r="1.5"/></svg>
					</span>
				</a>
				<button class="nt-nav__hamburger" id="nt-hamburger"
						aria-label="Open menu" aria-expanded="false" aria-controls="nt-mobile-nav">
					<span></span><span></span><span></span>
				</button>
			</div>

		</div>
	</div>
</header>

<!-- Mobile Nav -->
<nav class="nt-mobile-nav" id="nt-mobile-nav" aria-label="Mobile Navigation">
	<?php foreach ( (array) $nav_items as $item ) : ?>
		<?php $has_children = !empty($item['children']); ?>
		<div class="nt-mobile-nav__item <?php echo $has_children ? 'nt-nav__has-sub' : ''; ?>">
			<a href="<?php echo esc_url( nt_link( $item['url'] ?? $item['href'] ?? '/' ) ); ?>"
			   class="nt-mobile-nav__link<?php echo nt_nav_is_active( nt_link( $item['url'] ?? $item['href'] ?? '/' ), $current_url ); ?>">
				<?php echo esc_html( $item['label'] ); ?>
			</a>
			<?php if ( $has_children ) : ?>
				<div class="nt-mobile-nav__submenu">
					<?php foreach ( (array) $item['children'] as $child ) : ?>
						<a href="<?php echo esc_url( nt_link( $child['url'] ?? $child['href'] ?? '/' ) ); ?>">
							<?php echo esc_html( $child['label'] ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
	<a href="<?php echo esc_url( home_url('/#contact') ); ?>" class="nt-mobile-nav__cta">
		Book Us For Your Event
	</a>
</nav>
