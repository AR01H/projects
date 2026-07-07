<?php
/**
 * Site header: logo/brand, primary nav (WP menu with JSON fallback),
 * live search box, mobile toggle. Rendered by header.php on every page.
 */

defined( 'ABSPATH' ) || exit;

$nt_nav_fallback = nt_data( 'nav' );
?>
<header class="nt-header">
	<div class="nt-container nt-header-inner">

		<div class="nt-brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="nt-brand-name" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( NT_BRAND_NAME ); ?></a>
			<?php endif; ?>
		</div>

		<button class="nt-nav-toggle" aria-expanded="false" aria-controls="nt-nav" data-nt-nav-toggle>
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', NT_TEXT_DOMAIN ); ?></span>
			<span class="nt-nav-toggle-bar"></span>
			<span class="nt-nav-toggle-bar"></span>
			<span class="nt-nav-toggle-bar"></span>
		</button>

		<nav id="nt-nav" class="nt-nav" aria-label="<?php esc_attr_e( 'Primary', NT_TEXT_DOMAIN ); ?>">
			<?php if ( has_nav_menu( 'primary' ) ) : ?>
				<?php
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'nt-menu',
					'depth'          => 2,
				) );
				?>
			<?php else : ?>
				<ul class="nt-menu">
					<?php foreach ( (array) ( $nt_nav_fallback['header'] ?? array() ) as $nt_item ) : ?>
						<li><a href="<?php echo esc_url( nt_link( $nt_item['url'] ?? '#' ) ); ?>"><?php echo esc_html( $nt_item['label'] ?? '' ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</nav>

		<div class="nt-header-search" data-nt-search>
			<input type="search" placeholder="<?php esc_attr_e( 'Search...', NT_TEXT_DOMAIN ); ?>" autocomplete="off" data-nt-search-input>
			<div class="nt-search-results" data-nt-search-results hidden></div>
		</div>

	</div>
</header>
