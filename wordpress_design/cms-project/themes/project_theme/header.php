<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="pt-site-header" id="site-header">
	<div class="pt-site-header__inner pt-container">

		<!-- Logo -->
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="pt-site-header__logo" aria-label="<?php bloginfo( 'name' ); ?> — Home">
			<?php
			if ( has_custom_logo() ) {
				the_custom_logo();
			} else {
				echo '<span class="pt-site-header__logo-text">' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
			}
			?>
		</a>

		<!-- Primary nav -->
		<?php
		wp_nav_menu( [
			'theme_location' => 'primary',
			'menu_class'     => 'pt-nav__list',
			'container'      => 'nav',
			'container_class'=> 'pt-site-header__nav',
			'container_id'   => 'site-nav',
			'fallback_cb'    => false,
		] );
		?>

		<!-- Mobile toggle -->
		<button class="pt-site-header__toggle" id="pt-nav-toggle"
		        aria-controls="site-nav" aria-expanded="false" aria-label="Toggle navigation">
			<span></span><span></span><span></span>
		</button>

	</div>
</header>

<style>
/* ── Site header — starter styles ──────────────────────────── */
.pt-site-header {
	position: sticky;
	top: 0;
	z-index: 100;
	background: #fff;
	border-bottom: 1px solid rgba(110,154,217,.2);
	height: var(--pt-nav-height, 72px);
}
.pt-site-header__inner {
	display: flex;
	align-items: center;
	justify-content: space-between;
	height: 100%;
}
.pt-site-header__logo { text-decoration: none; }
.pt-site-header__logo-text {
	font-size: 1.15rem;
	font-weight: 800;
	color: var(--pt-color-1, #1a2c4e);
}
.pt-site-header__logo img { height: 40px; width: auto; display: block; }
.pt-site-header__nav { display: flex; align-items: center; }
.pt-nav__list {
	list-style: none;
	margin: 0; padding: 0;
	display: flex;
	gap: 4px;
	align-items: center;
}
.pt-nav__list li a {
	display: block;
	padding: 8px 14px;
	font-size: .88rem;
	font-weight: 600;
	color: var(--pt-text, #1a2c4e);
	text-decoration: none;
	border-radius: 6px;
	transition: background .15s, color .15s;
}
.pt-nav__list li a:hover,
.pt-nav__list li.current-menu-item > a {
	background: var(--pt-color-6, #e8f0fb);
	color: var(--pt-color-2, #2a4a82);
}
.pt-site-header__toggle {
	display: none;
	flex-direction: column;
	gap: 5px;
	background: none;
	border: none;
	cursor: pointer;
	padding: 8px;
}
.pt-site-header__toggle span {
	display: block;
	width: 22px;
	height: 2px;
	background: var(--pt-color-1, #1a2c4e);
	border-radius: 2px;
	transition: transform .2s;
}
@media (max-width: 768px) {
	.pt-site-header__toggle { display: flex; }
	.pt-site-header__nav {
		display: none;
		position: absolute;
		top: var(--pt-nav-height, 72px);
		left: 0; right: 0;
		background: #fff;
		border-bottom: 1px solid rgba(110,154,217,.2);
		padding: 16px 24px;
		box-shadow: 0 8px 24px rgba(26,44,78,.1);
	}
	.pt-site-header__nav.is-open { display: flex; }
	.pt-nav__list { flex-direction: column; align-items: flex-start; width: 100%; gap: 2px; }
	.pt-nav__list li a { padding: 10px 12px; width: 100%; }
}
</style>

<script>
(function(){
	var btn = document.getElementById('pt-nav-toggle');
	var nav = document.getElementById('site-nav');
	if ( ! btn || ! nav ) return;
	btn.addEventListener('click', function(){
		var open = nav.classList.toggle('is-open');
		btn.setAttribute('aria-expanded', open ? 'true' : 'false');
	});
}());
</script>
