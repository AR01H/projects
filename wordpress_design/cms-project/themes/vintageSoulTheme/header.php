<?php

use VintageSoul\Services\NavigationService;
use VintageSoul\Services\RouteService;
use VintageSoul\Services\SettingsService;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script>document.documentElement.classList.add('js');</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- SVG Filters for Authentic Rough Cut Button & Card Edges (Loaded before all UI elements) -->
<svg style="position: absolute; width: 0; height: 0; overflow: hidden; pointer-events: none;" aria-hidden="true">
	<defs>
		<filter id="rough-button-cut" x="-10%" y="-10%" width="120%" height="120%">
			<feTurbulence type="fractalNoise" baseFrequency="0.045 0.045" numOctaves="3" seed="33" result="noise"/>
			<feDisplacementMap in="SourceGraphic" in2="noise" scale="3" xChannelSelector="R" yChannelSelector="G"/>
		</filter>
		<filter id="rough-button-cut-sm" x="-10%" y="-10%" width="120%" height="120%">
			<feTurbulence type="fractalNoise" baseFrequency="0.06 0.06" numOctaves="3" seed="18" result="noise"/>
			<feDisplacementMap in="SourceGraphic" in2="noise" scale="2" xChannelSelector="R" yChannelSelector="G"/>
		</filter>
	</defs>
</svg>

<?php if ( is_front_page() || is_home() ) : ?>
	<?php View::component( 'loader/loader' ); ?>
<?php endif; ?>

<!-- Top Vintage Ticker Ribbon -->
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

<header class="site-header" role="banner">
	<span class="site-header__bg roughness-bottom-b" aria-hidden="true"></span>
	<div class="container site-header__inner">
		<?php View::component( 'logo/logo', array( 'context' => 'header' ) ); ?>
		<nav class="nav" aria-label="<?php esc_attr_e( 'Primary', 'vintagesoul' ); ?>">
			<ul class="nav__list">
				<?php
				$current_route = (string) ( \VintageSoul\Services\RouteService::current_key() ?? 'home' );

				foreach ( NavigationService::menu( 'primary' ) as $item ) :
					$children  = ( isset( $item['children'] ) && is_array( $item['children'] ) ) ? $item['children'] : array();
					$has_kids  = ! empty( $children );
					$item_url  = UrlHelper::resolve( (string) ( $item['url'] ?? '#' ) );
					$item_path = trim( (string) parse_url( $item_url, PHP_URL_PATH ), '/' );

					// Exact 1-to-1 active route matching
					$is_active = false;
					if ( 'home' === $current_route ) {
						$is_active = ( '' === $item_path || '/' === ( $item['url'] ?? '' ) || '#' === ( $item['url'] ?? '' ) );
					} else {
						$is_active = ( $item_path === $current_route || ltrim( (string) ( $item['url'] ?? '' ), '/' ) === $current_route );
					}
					$item_href = $has_kids ? 'javascript:void(0)' : esc_url( $item_url );
				?>
					<li class="nav__item<?php echo $has_kids ? ' nav__item--has-children' : ''; ?><?php echo $is_active ? ' is-active' : ''; ?>">
						<a class="nav__link<?php echo $is_active ? ' is-active' : ''; ?>" href="<?php echo esc_attr( $item_href ); ?>"<?php echo $has_kids ? ' aria-haspopup="true" role="button"' : ''; ?><?php echo ( ! $has_kids && $is_active ) ? ' aria-current="page"' : ''; ?>>
							<?php echo esc_html( $item['label'] ); ?>
							<?php if ( $has_kids ) : ?>
								<span class="nav__chevron" aria-hidden="true"></span>
							<?php endif; ?>
						</a>
						<?php if ( $has_kids ) : ?>
							<ul class="nav__submenu">
								<?php foreach ( $children as $child ) :
									$child       = (array) $child;
									$child_label = trim( (string) ( $child['label'] ?? '' ) );
									if ( '' === $child_label ) {
										continue;
									}
									$parts    = explode( ' ', $child_label, 2 );
									$has_icon = ( 2 === count( $parts ) && 1 === mb_strlen( $parts[0] ) );
									$icon = $has_icon ? $parts[0] : '';
									$text = $has_icon ? $parts[1] : $child_label;
								?>
									<li>
										<a class="nav__submenu-link" href="<?php echo esc_url( UrlHelper::resolve( (string) ( $child['url'] ?? '#' ) ) ); ?>">
											<?php if ( '' !== $icon ) : ?>
												<span class="nav__submenu-icon"><?php echo esc_html( $icon ); ?></span>
											<?php endif; ?>
											<span class="nav__submenu-text"><?php echo esc_html( $text ); ?></span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<div class="site-header__actions">
			<?php
			$header_cta = NavigationService::header_cta();
			if ( '' !== $header_cta['label'] && '' !== $header_cta['route'] ) :
				?>
				<a class="header-cta-button roughness-a" href="<?php echo esc_url( RouteService::url( $header_cta['route'] ) ); ?>">
					<span class="header-cta__text">
						<span class="header-cta__line1"><?php echo esc_html( $header_cta['label'] ); ?></span>
						<?php if ( '' !== $header_cta['sublabel'] ) : ?>
							<span class="header-cta__line2"><?php echo esc_html( $header_cta['sublabel'] ); ?></span>
						<?php endif; ?>
					</span>
					<span class="header-cta__icon" aria-hidden="true"></span>
				</a>
			<?php endif; ?>
<?php

			View::component(
				'navigation/mobile-nav',
				array(
					'items' => NavigationService::menu( 'primary' ),
					'cta'   => $header_cta,
				)
			);

			?>
		</div>
	</div>
	<!-- Random Deckle Rough Cut Bottom Edge -->
	<div class="site-header__deckle-edge" aria-hidden="true"></div>
</header>
<script>
(function() {
	function updateHeaderScroll() {
		var header = document.querySelector('.site-header');
		if (!header) return;
		var heroSection = document.getElementById('home-hero') || document.querySelector('.hero-sugarcane') || document.querySelector('.section--hero');
		var triggerHeight = heroSection ? (heroSection.offsetTop + heroSection.offsetHeight - 120) : 400;
		if (window.scrollY > triggerHeight) {
			header.classList.add('is-scrolled');
		} else {
			header.classList.remove('is-scrolled');
		}
	}
	window.addEventListener('scroll', updateHeaderScroll, { passive: true });
	window.addEventListener('resize', updateHeaderScroll, { passive: true });
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', updateHeaderScroll);
	} else {
		updateHeaderScroll();
	}
})();
</script>

<main class="site-main site-canvas" id="main">
