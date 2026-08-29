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

<!-- SVG Filters for Authentic Rough Cut Button & Card Edges -->
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

<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'vintagesoul' ); ?></a>

<header class="site-header" role="banner">
	<span class="site-header__bg roughness-bottom-b" aria-hidden="true"></span>
	<div class="container site-header__inner">
		<?php View::component( 'logo/logo', array( 'context' => 'header' ) ); ?>
		<nav class="nav" aria-label="<?php esc_attr_e( 'Primary', 'vintagesoul' ); ?>">
			<ul class="nav__list">
				<?php foreach ( NavigationService::menu( 'primary' ) as $item ) :
					$children = ( isset( $item['children'] ) && is_array( $item['children'] ) ) ? $item['children'] : array();
					$has_kids = ! empty( $children );
				?>
					<li class="nav__item<?php echo $has_kids ? ' nav__item--has-children' : ''; ?>">
						<a class="nav__link" href="<?php echo esc_url( UrlHelper::resolve( (string) ( $item['url'] ?? '#' ) ) ); ?>"<?php echo $has_kids ? ' aria-haspopup="true"' : ''; ?>>
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
								?>
									<li><a class="nav__submenu-link" href="<?php echo esc_url( UrlHelper::resolve( (string) ( $child['url'] ?? '#' ) ) ); ?>"><?php echo esc_html( $child_label ); ?></a></li>
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
</header>

<main class="site-main site-canvas" id="main">
