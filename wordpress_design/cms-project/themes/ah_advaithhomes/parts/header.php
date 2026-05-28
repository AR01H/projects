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

<nav class="nav" id="mainNav" role="navigation" aria-label="<?php echo esc_attr( TXT_MAIN_NAVIGATION ); ?>">
	<div class="container">
		<div class="nav__inner">
			<a href="<?php echo esc_url( home_url( AH_LINK_HOME ) ); ?>" class="nav__logo" aria-label="<?php bloginfo( 'name' ); ?> Home">
				<?php if ( $has_logo ) : ?>
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php bloginfo( 'name' ); ?>" style="height:40px">
					<span><?php echo esc_html( CLIENT_PRIMARY_TITLE ); ?> <em style="font-style:italic;font-family:var(--font-accent)"><?php echo esc_html( CLIENT_SECONDARY_TITLE ); ?></em></span>
				<?php else : ?>
					<div class="nav__logo-mark"><?php echo esc_html( CLIENT_SHORT_TITLE ); ?></div>
					<span><?php echo esc_html( CLIENT_PRIMARY_TITLE ); ?> <em style="font-style:italic;font-family:var(--font-accent)"><?php echo esc_html( CLIENT_SECONDARY_TITLE ); ?></em></span>
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
									<?php $is_hl = ! empty( $sub_item['highlight'] ); ?>
									<a href="<?php echo esc_url( $sub_item['url'] ?? '#' ); ?>" class="nav__dropdown-item" role="menuitem">
										<?php if ( ! empty( $sub_item['icon'] ) ) : ?>
										<div class="nav__dropdown-item-icon"<?php if ( $is_hl ) echo ( 'style="background:var(--accent);color:#fff"' ); ?>>
											<?php echo esc_html( $sub_item['icon'] ); ?>
										</div>
										<?php endif; ?>
										<div>
											<div class="nav__ddi-label<?php echo $is_hl ? ' nav__ddi-label--accent' : ''; ?>">
												<?php echo esc_html( $sub_item['label'] ?? '' ); ?>
											</div>
											<?php if ( ! empty( $sub_item['description'] ) ) : ?>
												<div class="nav__ddi-desc"><?php echo esc_html( $sub_item['description'] ); ?></div>
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
				<button class="nav__search-btn" id="ahSearchToggle"
					aria-label="<?php echo esc_attr( TXT_SEARCH ); ?>>"
					aria-expanded="false" aria-controls="ahSearchPanel">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
					</svg>
				</button>
				<a href="<?php echo esc_url( $nav_cta['url'] ?? home_url( AH_LINK_CONTACT ) ); ?>" class="btn btn-sm btn-primary">
					<?php echo esc_html( $nav_cta['label'] ?? AH_LABEL_GET_HELP ); ?>
				</a>
				<button class="nav__hamburger" id="ahHamburger"
					aria-label="<?php echo esc_attr( TXT_OPEN_MENU ); ?>"
					aria-expanded="false" aria-controls="ahMobileNav">
					<span></span><span></span><span></span>
				</button>
			</div>
		</div>
	</div>
</nav>

<!-- Search overlay — backdrop click closes, ESC closes -->
<div class="nav__search-panel" id="ahSearchPanel" role="search" aria-hidden="true">
	<div class="container">
		<div class="nav__search-inner">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="nav__search-icon">
				<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
			</svg>
			<input type="search" id="ahSearchInput" class="nav__search-input"
				placeholder="<?php printf( 'Search %s, guides &amp; news…', AH_TERM_LOWER_PLURAL ); ?>"
				autocomplete="off" aria-label="<?php echo esc_attr( TXT_SEARCH ); ?>">
			<button class="nav__search-close" id="ahSearchClose"
				aria-label="<?php echo esc_attr( TXT_CLOSE_SEARCH ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
			</button>
		</div>
		<div class="nav__search-results" id="ahSearchResults" aria-live="polite"></div>
	</div>
</div>

<nav class="nav__mobile" id="ahMobileNav" aria-label="<?php echo esc_attr( TXT_MOBILE_NAVIGATION ); ?>">
	<a href="<?php echo esc_url( home_url( AH_LINK_HOME ) ); ?>" class="nav__mobile-link"><?php echo esc_html( AH_LABEL_HOME ); ?></a>

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
		<a href="<?php echo esc_url( $nav_cta['url'] ?? home_url( AH_LINK_CONTACT ) ); ?>" class="btn btn-primary btn-block" style="justify-content:center">
			<?php echo esc_html( $nav_cta['label'] ?? AH_LABEL_TALK_TO_EXPERT ); ?>
		</a>
	</div>
</nav>

<div id="page-content">

<?php if ( ah_section_visible( 'global_news_ticker' ) ) : ?>
	<!-- <?php get_template_part( 'components/news-ticker' ); ?> -->
<?php endif; ?>
