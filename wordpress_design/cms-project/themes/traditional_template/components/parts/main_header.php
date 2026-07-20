<?php
/**
 * Main Header – Vintage forest-green navigation.
 * Matches reference: dark green bar, brand box top-left, white/gold nav links, CTA button.
 */
defined( 'ABSPATH' ) || exit;

$nav_items   = nt_data('nav')['header'] ?? [];
$has_logo    = has_custom_logo();
$current_url = trailingslashit( strtok( $_SERVER['REQUEST_URI'], '?' ) );
$brand_name  = NT_BRAND_NAME;

function nt_nav_is_active( $url, $current ) {
	return trailingslashit( $url ) === $current ? ' is-active' : '';
}
?>
<!-- ── Main Navigation ─────────────────────────────────────────── -->
<header id="nt-site-header" class="nt-nav" role="banner">
	<div class="container">
		<div class="nt-nav__inner">

			<!-- Brand / Logo -->
			<a href="<?php echo esc_url( home_url('/') ); ?>" class="nt-nav__logo" aria-label="Home">
				<?php if ( $has_logo ) :
					the_custom_logo();
				else : ?>
					<div class="nt-nav__logo-box">
						<span class="nt-nav__logo-leaf">🌿</span>
						<span class="nt-nav__logo-text"><?php echo wp_kses_post( $brand_name ); ?></span>
					</div>
				<?php endif; ?>
			</a>

			<!-- Desktop Nav Links -->
			<ul class="nt-nav__links" id="nt-nav-links" role="list">
				<?php foreach ( (array) $nav_items as $item ) : ?>
					<li>
						<a href="<?php echo esc_url( nt_link( $item['url'] ?? $item['href'] ?? '/' ) ); ?>"
						   class="nt-nav__link<?php echo nt_nav_is_active( nt_link( $item['url'] ?? $item['href'] ?? '/' ), $current_url ); ?>">
							<?php echo esc_html( $item['label'] ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>

			<!-- CTA + Hamburger -->
			<div class="nt-nav__actions">
				<a href="<?php echo esc_url( home_url('/#contact') ); ?>" class="nt-nav__cta-btn">
					Book Us For Your Event
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
		<a href="<?php echo esc_url( nt_link( $item['url'] ?? $item['href'] ?? '/' ) ); ?>"
		   class="nt-mobile-nav__link<?php echo nt_nav_is_active( nt_link( $item['url'] ?? $item['href'] ?? '/' ), $current_url ); ?>">
			<?php echo esc_html( $item['label'] ); ?>
		</a>
	<?php endforeach; ?>
	<a href="<?php echo esc_url( home_url('/#contact') ); ?>" class="nt-mobile-nav__cta">
		Book Us For Your Event
	</a>
</nav>
