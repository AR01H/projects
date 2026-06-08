<footer class="pt-site-footer" id="site-footer">
	<div class="pt-site-footer__inner pt-container">

		<!-- Brand column -->
		<div class="pt-site-footer__brand">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="pt-site-footer__logo">
				<?php
				if ( has_custom_logo() ) {
					the_custom_logo();
				} else {
					echo '<span class="pt-site-footer__logo-text">' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
				}
				?>
			</a>
			<p class="pt-site-footer__tagline">
				<?php echo esc_html( get_bloginfo( 'description' ) ?: 'Building great things.' ); ?>
			</p>
		</div>

		<!-- Footer nav -->
		<?php
		wp_nav_menu( [
			'theme_location' => 'footer',
			'menu_class'     => 'pt-footer-nav__list',
			'container'      => 'nav',
			'container_class'=> 'pt-site-footer__nav',
			'container_id'   => 'footer-nav',
			'fallback_cb'    => false,
		] );
		?>

	</div>

	<div class="pt-site-footer__bar pt-container">
		<span>
			&copy; <?php echo esc_html( date( 'Y' ) ); ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php bloginfo( 'name' ); ?>
			</a>.
			All rights reserved.
		</span>
	</div>
</footer>

<style>
/* ── Site footer — starter styles ──────────────────────────── */
.pt-site-footer {
	background: var(--pt-color-1, #1a2c4e);
	color: rgba(255,255,255,.85);
	padding-top: 4rem;
}
.pt-site-footer__inner {
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
	gap: 2rem;
	padding-bottom: 3rem;
}
.pt-site-footer__logo { text-decoration: none; display: inline-block; margin-bottom: .75rem; }
.pt-site-footer__logo img { height: 36px; width: auto; filter: brightness(0) invert(1); }
.pt-site-footer__logo-text {
	font-size: 1.1rem;
	font-weight: 800;
	color: #fff;
}
.pt-site-footer__tagline {
	font-size: .85rem;
	line-height: 1.6;
	color: rgba(255,255,255,.6);
	max-width: 280px;
}
.pt-footer-nav__list {
	list-style: none;
	margin: 0; padding: 0;
	display: flex;
	flex-wrap: wrap;
	gap: 4px 6px;
}
.pt-footer-nav__list li a {
	font-size: .85rem;
	font-weight: 600;
	color: rgba(255,255,255,.7);
	text-decoration: none;
	padding: 5px 10px;
	border-radius: 5px;
	transition: color .15s, background .15s;
}
.pt-footer-nav__list li a:hover {
	color: #fff;
	background: rgba(255,255,255,.08);
}
.pt-site-footer__bar {
	display: flex;
	align-items: center;
	justify-content: center;
	padding-top: 1.25rem;
	padding-bottom: 1.25rem;
	border-top: 1px solid rgba(255,255,255,.1);
	font-size: .78rem;
	color: rgba(255,255,255,.45);
}
.pt-site-footer__bar a { color: rgba(255,255,255,.6); text-decoration: none; }
.pt-site-footer__bar a:hover { color: #fff; }
@media (max-width: 640px) {
	.pt-site-footer__inner { flex-direction: column; }
	.pt-footer-nav__list { flex-direction: column; }
}
</style>

<?php wp_footer(); ?>
</body>
</html>
