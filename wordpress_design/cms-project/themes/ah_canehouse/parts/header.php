<?php
defined( 'ABSPATH' ) || exit;

$settings  = ch_get_settings();
$phone     = $settings['phone'] ?? '';
$logo_url  = get_template_directory_uri() . '/assets/images/logo.png';
$has_logo  = file_exists( get_template_directory() . '/assets/images/logo.png' );
$theme_nav = array_values(
	array_filter(
		ch_get_theme_navigation(),
		static function ( $item ) {
			$item = (array) $item;
			return ! empty( $item['visible'] ) && ! empty( $item['label'] );
		}
	)
);
$nav_cta = ch_get_nav_cta();
?>
<?php $global_banner = ch_get_html_block( 'global_banner' ); ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="profile" href="https://gmpg.org/xfn/11">
<?php wp_head(); ?>
</head>

<body <?php body_class( 'ch-body' ); ?>>
<?php wp_body_open(); ?>
<?php if ( $global_banner ) echo $global_banner; ?>

<nav id="ch-nav" class="ch-nav" role="navigation" aria-label="<?php esc_attr_e( 'Main Navigation', 'ch-theme' ); ?>">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ch-nav__logo" aria-label="<?php bloginfo( 'name' ); ?> Home">
		<?php if ( $has_logo ) : ?>
			<img src="<?php echo esc_url( $logo_url ); ?>" alt="The Cane House" width="50" height="50" class="ch-nav__logo-img">
		<?php else : ?>
			<div class="ch-nav__logo-icon">🌿</div>
		<?php endif; ?>
		<span class="ch-nav__logo-text">THE CANE <em>HOUSE</em></span>
	</a>

	<ul class="ch-nav__links" id="ch-nav-links" role="list">
		<?php foreach ( $theme_nav as $item ) : ?>
			<?php
			$item        = (array) $item;
			$has_submenu = ( $item['type'] ?? 'link' ) === 'dropdown' && ! empty( $item['submenu'] );
			?>
			<?php if ( $has_submenu ) : ?>
				<li class="ch-nav__dropdown">
					<button class="ch-nav__link ch-nav__dropdown-toggle" aria-haspopup="true" aria-expanded="false">
						<?php echo esc_html( $item['label'] ); ?>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" width="12" height="12"><path d="M6 9l6 6 6-6"/></svg>
					</button>
					<div class="ch-nav__dropdown-menu" role="menu">
						<?php foreach ( (array) $item['submenu'] as $sub ) : ?>
							<?php $sub = (array) $sub; ?>
							<a href="<?php echo esc_url( $sub['url'] ?? '#' ); ?>"
								class="ch-nav__dropdown-item<?php echo ! empty( $sub['highlight'] ) ? ' ch-nav__dropdown-item--highlight' : ''; ?>"
								role="menuitem">
								<?php if ( ! empty( $sub['icon'] ) ) : ?>
									<div class="ch-nav__dropdown-icon"><?php echo esc_html( $sub['icon'] ); ?></div>
								<?php endif; ?>
								<div>
									<div class="ch-nav__dropdown-label"><?php echo esc_html( $sub['label'] ?? '' ); ?></div>
									<?php if ( ! empty( $sub['description'] ) ) : ?>
										<div class="ch-nav__dropdown-desc"><?php echo esc_html( $sub['description'] ); ?></div>
									<?php endif; ?>
								</div>
							</a>
						<?php endforeach; ?>
					</div>
				</li>
			<?php else : ?>
				<li>
					<a href="<?php echo esc_url( $item['url'] ?? home_url( '/' ) ); ?>" class="ch-nav__link">
						<?php echo esc_html( $item['label'] ); ?>
					</a>
				</li>
			<?php endif; ?>
		<?php endforeach; ?>
		<li>
			<a href="<?php echo esc_url( ch_normalize_theme_url( $nav_cta['url'] ?? '#contact' ) ); ?>"
				class="ch-nav__cta-btn">
				<?php echo esc_html( $nav_cta['label'] ?? 'Contact' ); ?>
			</a>
		</li>
	</ul>

	<button class="ch-nav__hamburger" id="ch-hamburger"
		aria-label="<?php esc_attr_e( 'Open menu', 'ch-theme' ); ?>"
		aria-expanded="false" aria-controls="ch-mobile-nav">
		<span></span><span></span><span></span>
	</button>
</nav>

<!-- Mobile navigation (PHP-rendered, toggled via JS) -->
<nav class="ch-mobile-nav" id="ch-mobile-nav" aria-label="<?php esc_attr_e( 'Mobile Navigation', 'ch-theme' ); ?>">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ch-mobile-nav__link">Home</a>

	<?php foreach ( $theme_nav as $item ) : ?>
		<?php
		$item        = (array) $item;
		$has_submenu = ( $item['type'] ?? 'link' ) === 'dropdown' && ! empty( $item['submenu'] );
		?>
		<?php if ( $has_submenu ) : ?>
			<details class="ch-mobile-nav__details">
				<summary class="ch-mobile-nav__summary">
					<?php echo esc_html( $item['label'] ); ?>
					<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
				</summary>
				<div class="ch-mobile-nav__sub">
					<?php foreach ( (array) $item['submenu'] as $sub ) : ?>
						<?php $sub = (array) $sub; ?>
						<a href="<?php echo esc_url( $sub['url'] ?? '#' ); ?>"
							class="ch-mobile-nav__sub-link<?php echo ! empty( $sub['highlight'] ) ? ' ch-mobile-nav__sub-link--highlight' : ''; ?>">
							<?php echo esc_html( trim( ( $sub['icon'] ?? '' ) . ' ' . ( $sub['label'] ?? '' ) ) ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</details>
		<?php else : ?>
			<a href="<?php echo esc_url( $item['url'] ?? home_url( '/' ) ); ?>" class="ch-mobile-nav__link">
				<?php echo esc_html( $item['label'] ); ?>
			</a>
		<?php endif; ?>
	<?php endforeach; ?>

	<div style="padding:1rem 0 .5rem;">
		<a href="<?php echo esc_url( ch_normalize_theme_url( $nav_cta['url'] ?? '#contact' ) ); ?>"
			class="ch-nav__cta-btn" style="display:block;text-align:center;">
			<?php echo esc_html( $nav_cta['label'] ?? 'Contact' ); ?>
		</a>
	</div>
</nav>

<div id="ch-page-content">
