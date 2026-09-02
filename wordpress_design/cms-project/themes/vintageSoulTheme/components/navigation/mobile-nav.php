<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

$items = isset( $items ) && is_array( $items ) ? $items : array();

$cta          = isset( $cta ) && is_array( $cta ) ? $cta : array();
$cta_label    = trim( (string) ( $cta['label'] ?? 'BOOK OUR LIVE BAR' ) );
$cta_route    = (string) ( $cta['route'] ?? 'contact' );
$cta_sublabel = (string) ( $cta['sublabel'] ?? 'Weddings & Events' );
$has_cta      = true;
?>
<!-- Hamburger Trigger Button -->
<button type="button" class="mobile-nav-toggle" id="mobile-nav-toggle" aria-expanded="false" aria-controls="mobile-nav"
	aria-label="<?php esc_attr_e( 'Open menu', 'vintagesoul' ); ?>">
	<span class="mobile-nav-toggle__bar" aria-hidden="true"></span>
	<span class="mobile-nav-toggle__bar" aria-hidden="true"></span>
	<span class="mobile-nav-toggle__bar" aria-hidden="true"></span>
</button>

<!-- Backdrop Overlay -->
<div class="mobile-nav-scrim" id="mobile-nav-scrim"></div>

<!-- Slide-Out Drawer -->
<nav class="mobile-nav" id="mobile-nav" aria-label="<?php esc_attr_e( 'Mobile Menu', 'vintagesoul' ); ?>" inert>
	
	<!-- Top Bar inside Drawer -->
	<div class="mobile-nav__header">
		<div class="mobile-nav__brand">
			<?php View::component( 'logo/logo', array( 'context' => 'mobile-nav' ) ); ?>
		</div>
		<button type="button" class="mobile-nav__close-btn" id="mobile-nav-close" aria-label="<?php esc_attr_e( 'Close menu', 'vintagesoul' ); ?>">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#184b25" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
				<line x1="18" y1="6" x2="6" y2="18"></line>
				<line x1="6" y1="6" x2="18" y2="18"></line>
			</svg>
		</button>
	</div>

	<!-- Navigation Links -->
	<ul class="mobile-nav__list">
		<?php
		$current_route = (string) ( \VintageSoul\Services\RouteService::current_key() ?? 'home' );

		foreach ( $items as $item ) :
			$item  = (array) $item;
			$label = trim( (string) ( $item['label'] ?? '' ) );
			if ( '' === $label ) {
				continue;
			}
			$url       = UrlHelper::resolve( (string) ( $item['url'] ?? '#' ) );
			$item_path = trim( (string) parse_url( $url, PHP_URL_PATH ), '/' );
			$children  = ( isset( $item['children'] ) && is_array( $item['children'] ) ) ? $item['children'] : array();
			$has_kids  = ! empty( $children );

			// Accurate 1-to-1 active route matching
			$is_active = false;
			if ( $has_kids ) {
				// Parent dropdown (Blog) should NEVER be active on the Home page
				if ( 'home' !== $current_route && ( 'blog' === $current_route || is_singular( 'post' ) || is_category() || is_tag() ) ) {
					$is_active = true;
				}
			} else {
				if ( 'home' === $current_route ) {
					$is_active = ( '' === $item_path || '/' === ( $item['url'] ?? '' ) || 'home' === $item_path );
				} else {
					$is_active = ( '' !== $item_path && ( $item_path === $current_route || ltrim( (string) ( $item['url'] ?? '' ), '/' ) === $current_route ) );
				}
			}
		?>
			<li class="mobile-nav__item<?php echo $is_active ? ' is-active' : ''; ?>">
				<div class="mobile-nav__row">
					<?php if ( $has_kids ) : ?>
						<button type="button" class="mobile-nav__link mobile-nav__link--has-children<?php echo $is_active ? ' is-active' : ''; ?>" aria-expanded="false"
							aria-label="<?php echo esc_attr( sprintf( __( '%s submenu', 'vintagesoul' ), $label ) ); ?>">
							<span class="mobile-nav__link-text"><?php echo esc_html( $label ); ?></span>
							<span class="mobile-nav__chevron" aria-hidden="true"></span>
						</button>
					<?php else : ?>
						<a class="mobile-nav__link<?php echo $is_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>"<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
							<span class="mobile-nav__link-text"><?php echo esc_html( $label ); ?></span>
						</a>
					<?php endif; ?>
				</div>
				<?php if ( $has_kids ) : ?>
					<div class="mobile-nav__submenu">
						<ul class="mobile-nav__sublist">
							<?php foreach ( $children as $child ) :
								$child       = (array) $child;
								$child_label = trim( (string) ( $child['label'] ?? '' ) );
								if ( '' === $child_label ) {
									continue;
								}
							?>
								<li><a href="<?php echo esc_url( UrlHelper::resolve( (string) ( $child['url'] ?? '#' ) ) ); ?>"><?php echo esc_html( $child_label ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>

	<!-- CTA & Quick Contact in Drawer -->
	<div class="mobile-nav__footer">
		<a class="mobile-nav__cta" href="<?php echo esc_url( RouteService::url( $cta_route ) ); ?>">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
				<line x1="16" x2="16" y1="2" y2="6"/>
				<line x1="8" x2="8" y1="2" y2="6"/>
				<line x1="3" x2="21" y1="10" y2="10"/>
			</svg>
			<span class="mobile-nav__cta-text">
				<span class="mobile-nav__cta-line1"><?php echo esc_html( $cta_label ); ?></span>
				<?php if ( '' !== $cta_sublabel ) : ?>
					<span class="mobile-nav__cta-line2"><?php echo esc_html( $cta_sublabel ); ?></span>
				<?php endif; ?>
			</span>
		</a>
		<div class="mobile-nav__contact-strip">
			<a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', \VintageSoul\Services\SettingsService::phone() ) ); ?>" class="mobile-nav__contact-pill">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#184b25" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
				<span>Call</span>
			</a>
			<a href="<?php echo esc_url( \VintageSoul\Services\SettingsService::whatsapp_url() ); ?>" target="_blank" rel="noopener" class="mobile-nav__contact-pill">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#184b25" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
				<span>WhatsApp</span>
			</a>
		</div>
	</div>

</nav>
