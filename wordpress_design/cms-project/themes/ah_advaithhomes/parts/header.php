<?php
defined( 'ABSPATH' ) || exit;

$settings  = ah_get_settings();
$phone     = $settings['phone'] ?? '';
$logo_url  = get_template_directory_uri() . '/assets/images/logo.png';
$has_logo  = file_exists( get_template_directory() . '/assets/images/logo.png' );
$theme_nav = array_values(
	array_filter(
		ah_get_theme_navigation(),
		static function ( $item ) {
			$item = is_object( $item ) ? (array) $item : (array) $item;
			return ! empty( $item['visible'] ) && ! empty( $item['label'] );
		}
	)
);
$nav_cta = ah_get_nav_cta();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="profile" href="https://gmpg.org/xfn/11">
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<nav class="nav" id="mainNav" role="navigation" aria-label="<?php esc_attr_e( 'Main Navigation', 'ah-theme' ); ?>">
	<div class="container">
		<div class="nav__inner">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nav__logo" aria-label="<?php bloginfo( 'name' ); ?> Home">
				<?php if ( $has_logo ) : ?>
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php bloginfo( 'name' ); ?>" style="height:40px">
					<span>Advaith <em style="font-style:italic;font-family:var(--font-accent)">Homes</em></span>
				<?php else : ?>
					<div class="nav__logo-mark">AH</div>
					<span>Advaith <em style="font-style:italic;font-family:var(--font-accent)">Homes</em></span>
				<?php endif; ?>
			</a>

			<ul class="nav__menu" role="list">
				<?php foreach ( $theme_nav as $item ) : ?>
					<?php
					$item       = is_object( $item ) ? (array) $item : (array) $item;
					$has_submenu = ( $item['type'] ?? 'link' ) === 'dropdown' && ! empty( $item['submenu'] );
					?>
					<?php if ( $has_submenu ) : ?>
						<li class="nav__dropdown">
							<button class="nav__link nav__dropdown-toggle" aria-haspopup="true" aria-expanded="false">
								<?php echo esc_html( $item['label'] ); ?>
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
							</button>
							<div class="nav__dropdown-menu" role="menu">
								<?php foreach ( (array) $item['submenu'] as $sub_item ) : ?>
									<?php $sub_item = is_object( $sub_item ) ? (array) $sub_item : (array) $sub_item; ?>
									<a href="<?php echo esc_url( $sub_item['url'] ?? '#' ); ?>" class="nav__dropdown-item" role="menuitem"
										<?php if ( ! empty( $sub_item['highlight'] ) ) echo 'style="background:var(--bg-alt);border-radius:8px"'; ?>>
										<div class="nav__dropdown-item-icon" <?php if ( ! empty( $sub_item['highlight'] ) ) echo 'style="background:var(--accent);color:white"'; ?>>
											<?php echo esc_html( $sub_item['icon'] ?? '' ); ?>
										</div>
										<div>
											<div style="font-weight:<?php echo ! empty( $sub_item['highlight'] ) ? 700 : 600; ?>;color:<?php echo ! empty( $sub_item['highlight'] ) ? 'var(--accent)' : 'var(--slate-800)'; ?>;font-size:.85rem">
												<?php echo esc_html( $sub_item['label'] ?? '' ); ?>
											</div>
											<?php if ( ! empty( $sub_item['description'] ) ) : ?>
												<div style="font-size:.78rem;color:var(--text-muted);margin-top:2px"><?php echo esc_html( $sub_item['description'] ); ?></div>
											<?php endif; ?>
										</div>
									</a>
								<?php endforeach; ?>
							</div>
						</li>
					<?php else : ?>
						<li>
							<a href="<?php echo esc_url( $item['url'] ?? home_url( '/' ) ); ?>" class="nav__link">
								<?php echo esc_html( $item['label'] ); ?>
							</a>
						</li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>

			<div class="nav__actions">
				<?php if ( $phone ) : ?>
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>"
						class="btn btn-sm btn-primary" aria-label="<?php esc_attr_e( 'Call us', 'ah-theme' ); ?>">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.8 19.79 19.79 0 01.02 1.18 2 2 0 012 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
						</svg>
						Contact Us
					</a>
				<?php endif; ?>
				<a href="<?php echo esc_url( $nav_cta['url'] ?? home_url( '/contact/' ) ); ?>" class="btn btn-sm btn-primary">
					<?php echo esc_html( $nav_cta['label'] ?? 'Get Help' ); ?>
				</a>
				<button class="nav__hamburger" id="ahHamburger"
					aria-label="<?php esc_attr_e( 'Open menu', 'ah-theme' ); ?>"
					aria-expanded="false" aria-controls="ahMobileNav">
					<span></span><span></span><span></span>
				</button>
			</div>
		</div>
	</div>
</nav>

<nav class="nav__mobile" id="ahMobileNav" aria-label="<?php esc_attr_e( 'Mobile Navigation', 'ah-theme' ); ?>">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nav__mobile-link">Home</a>

	<?php foreach ( $theme_nav as $item ) : ?>
		<?php
		$item        = is_object( $item ) ? (array) $item : (array) $item;
		$has_submenu = ( $item['type'] ?? 'link' ) === 'dropdown' && ! empty( $item['submenu'] );
		?>
		<?php if ( $has_submenu ) : ?>
			<details class="nav__mobile-details">
				<summary class="nav__mobile-summary">
					<?php echo esc_html( $item['label'] ); ?>
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
				</summary>
				<div class="nav__mobile-sub-menu">
					<?php foreach ( (array) $item['submenu'] as $sub_item ) : ?>
						<?php $sub_item = is_object( $sub_item ) ? (array) $sub_item : (array) $sub_item; ?>
						<a href="<?php echo esc_url( $sub_item['url'] ?? '#' ); ?>" class="nav__mobile-link"
							<?php if ( ! empty( $sub_item['highlight'] ) ) echo 'style="color:var(--accent);font-weight:700"'; ?>>
							<?php echo esc_html( trim( ( $sub_item['icon'] ?? '' ) . ' ' . ( $sub_item['label'] ?? '' ) ) ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</details>
		<?php else : ?>
			<a href="<?php echo esc_url( $item['url'] ?? home_url( '/' ) ); ?>" class="nav__mobile-link"><?php echo esc_html( $item['label'] ); ?></a>
		<?php endif; ?>
	<?php endforeach; ?>

	<div style="padding:16px">
		<a href="<?php echo esc_url( $nav_cta['url'] ?? home_url( '/contact/' ) ); ?>" class="btn btn-primary btn-block" style="justify-content:center">
			<?php echo esc_html( $nav_cta['label'] ?? 'Talk to an Expert' ); ?>
		</a>
	</div>
</nav>

<div id="page-content">

<?php if ( ah_section_visible( 'global_news_ticker' ) ) : ?>
	<?php get_template_part( 'components/news-ticker' ); ?>
<?php endif; ?>
