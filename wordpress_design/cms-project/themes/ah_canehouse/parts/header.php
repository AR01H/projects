<?php
defined( 'ABSPATH' ) || exit;

$settings  = ch_get_settings();
$phone     = $settings['phone']    ?? '';
$logo_url  = get_template_directory_uri() . '/assets/images/logo.png';
$favicon_url  = get_template_directory_uri() . '/assets/images/favicon.ico';
$only_logo_url  = get_template_directory_uri() . '/assets/images/only_logo.png';
$logotext_url = get_template_directory_uri() . '/assets/images/thecanehousetextlogo.png';
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
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="profile" href="https://gmpg.org/xfn/11">
<link rel="shortcut icon" href="<?php echo esc_url( $favicon_url ); ?>" type="image/x-icon">
<link rel="icon" href="<?php echo esc_url( $favicon_url ); ?>" type="image/x-icon">
<?php wp_head(); ?><?php
$_ch_sc = ch_get_schema_settings();
if ( ( $_ch_sc['enabled'] ?? '1' ) === '1' ) {
	// Buffer any WP DB errors so they don't break the <head> structure
	ob_start();
	$_ch_json = ch_build_schema_json( false ); // false = skip live DB review query
	ob_end_clean();
	if ( ! empty( $_ch_json ) ) {
		echo '<script type="application/ld+json">' . wp_json_encode( $_ch_json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
	}
	unset( $_ch_json );
}
unset( $_ch_sc );
?></head>

<body <?php body_class( 'ch-body' ); ?>><?php wp_body_open(); ?>
<!-- ── Main Navigation ────────────────────────────────────────────────────── -->
<nav id="ch-nav" class="ch-nav" role="navigation" aria-label="Main Navigation">
	<div class="container">
		<div class="ch-nav__inner">

			<!-- Logo -->
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ch-nav__logo" aria-label="The Cane House Home">
				<?php if ( $has_logo ) : ?>
					<!-- <img src="<?php echo esc_url( $only_logo_url ); ?>" alt="The Cane House" class="ch-nav__logo-img"> -->
					<img src="<?php echo esc_url( $logotext_url ); ?>" alt="The Cane House" class="ch-nav__logo-img" style="width: 100px;">
				<?php else : ?>
					<div class="ch-nav__logo-mark">🌿</div>
					<span class="ch-nav__logo-text">THE CANE <em>HOUSE</em></span>
				<?php endif; ?>
			</a>

			<!-- Desktop menu -->
			<ul class="ch-nav__links" id="ch-nav-links" role="list">
				<?php foreach ( $theme_nav as $item ) :
					$item        = (array) $item;
					$has_submenu = ( $item['type'] ?? 'link' ) === 'dropdown' && ! empty( $item['submenu'] );
				?>
					<?php if ( $has_submenu ) : ?>
						<li class="ch-nav__dropdown">
							<button class="ch-nav__link ch-nav__dropdown-toggle"
								aria-haspopup="true" aria-expanded="false">
								<?php echo esc_html( $item['label'] ); ?>
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" width="12" height="12"><path d="M6 9l6 6 6-6"/></svg>
							</button>
							<div class="ch-nav__dropdown-menu" role="menu">
								<?php foreach ( (array) $item['submenu'] as $sub ) :
									$sub   = (array) $sub;
									$is_hl = ! empty( $sub['highlight'] );
								?>
									<a href="<?php echo esc_url( ch_normalize_theme_url( $sub['url'] ?? '#' ) ); ?>"
										class="ch-nav__dropdown-item<?php echo $is_hl ? ' ch-nav__dropdown-item--highlight' : ''; ?>"
										role="menuitem">
										<?php if ( ! empty( $sub['icon'] ) ) : ?>
											<div class="ch-nav__dropdown-icon"
												<?php echo $is_hl ? 'style="background:var(--ch-lime);color:var(--ch-green-deep)"' : ''; ?>>
												<?php echo esc_html( $sub['icon'] ); ?>
											</div>
										<?php endif; ?>
										<div>
											<div class="ch-nav__ddi-label<?php echo $is_hl ? ' ch-nav__ddi-label--accent' : ''; ?>">
												<?php echo esc_html( $sub['label'] ?? '' ); ?>
											</div>
											<?php if ( ! empty( $sub['description'] ) ) : ?>
												<div class="ch-nav__ddi-desc"><?php echo esc_html( $sub['description'] ); ?></div>
											<?php endif; ?>
										</div>
									</a>
								<?php endforeach; ?>
							</div>
						</li>
					<?php else : ?>
						<li>
							<a href="<?php echo esc_url( ch_normalize_theme_url( $item['url'] ?? home_url( '/' ) ) ); ?>"
								class="ch-nav__link">
								<?php echo esc_html( $item['label'] ); ?>
							</a>
						</li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
			<!-- Actions: CTA + hamburger -->
			 <?php if (!empty($nav_cta['url'])) : ?>
			<div class="ch-nav__actions">
				<a href="<?php echo esc_url( ch_normalize_theme_url( $nav_cta['url'] ?? '/contact' ) ); ?>"
					class="ch-nav__cta-btn">
					<?php echo esc_html( $nav_cta['label'] ?? 'Hire Us' ); ?>
				</a>
				<button class="ch-nav__hamburger" id="ch-hamburger"
					aria-label="Open menu" aria-expanded="false" aria-controls="ch-mobile-nav">
					<span></span><span></span><span></span>
				</button>
			</div>
			 <?php endif; ?>


		</div><!-- .ch-nav__inner -->
	</div><!-- .container -->
</nav>

<!-- ── Mobile nav ────────────────────────────────────────────────────────── -->
<nav class="ch-mobile-nav" id="ch-mobile-nav" aria-label="Mobile Navigation">
	<?php foreach ( $theme_nav as $item ) :
		$item        = (array) $item;
		$has_submenu = ( $item['type'] ?? 'link' ) === 'dropdown' && ! empty( $item['submenu'] );
	?>
		<?php if ( $has_submenu ) : ?>
			<details class="ch-mobile-nav__details">
				<summary class="ch-mobile-nav__summary">
					<?php echo esc_html( $item['label'] ); ?>
					<svg viewBox="0 0 24 24" width="14" height="14" fill="none"
						stroke="currentColor" stroke-width="2.5" aria-hidden="true">
						<path d="M6 9l6 6 6-6"/>
					</svg>
				</summary>
				<div class="ch-mobile-nav__sub">
					<?php foreach ( (array) $item['submenu'] as $sub ) :
						$sub   = (array) $sub;
						$is_hl = ! empty( $sub['highlight'] );
					?>
						<a href="<?php echo esc_url( ch_normalize_theme_url( $sub['url'] ?? '#' ) ); ?>"
							class="ch-mobile-nav__sub-link<?php echo $is_hl ? ' ch-mobile-nav__sub-link--highlight' : ''; ?>">
							<?php echo esc_html( trim( ( $sub['icon'] ?? '' ) . ' ' . ( $sub['label'] ?? '' ) ) ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</details>
		<?php else : ?>
			<a href="<?php echo esc_url( ch_normalize_theme_url( $item['url'] ?? home_url( '/' ) ) ); ?>"
				class="ch-mobile-nav__link">
				<?php echo esc_html( $item['label'] ); ?>
			</a>
		<?php endif; ?>
	<?php endforeach; ?>

	<?php if ( ! empty( $nav_cta['url'] ) ) : ?>
		<a href="<?php echo esc_url( ch_normalize_theme_url( $nav_cta['url'] ?? '/contact' ) ); ?>"
			class="ch-mobile-nav__cta">
			<?php echo esc_html( $nav_cta['label'] ?? 'Hire Us' ); ?>
		</a>
	<?php endif; ?>
</nav>

<div id="ch-page-content">
