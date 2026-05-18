<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

$plugin_nav_url = admin_url( 'admin.php?page=ah-navigation' );
$plugin_active  = class_exists( 'AH_Admin_Bootstrap' );
?>
<div class="wrap ch-admin-wrap">
	<h1>Navigation &amp; Footer</h1>

	<?php if ( $plugin_active ) : ?>
		<div class="ch-notice ch-notice--success" style="margin-bottom:1.5rem;">
			Navigation and footer are managed by the <strong>CMS Plugin</strong>.
			The plugin's editor supports full multi-page links, dropdown menus, submenu items, and footer columns — all shared across themes.
		</div>
		<p>
			<a href="<?php echo esc_url( $plugin_nav_url ); ?>" class="button button-primary button-hero">
				Open Navigation &amp; Footer Editor →
			</a>
		</p>
		<p style="color:#666;font-size:.85rem;margin-top:1rem;">
			Changes saved there are immediately reflected in this theme's header and footer.
		</p>
	<?php else : ?>
		<div class="ch-notice ch-notice--warning">
			The CMS Plugin is not active. Please activate <strong>plugin1</strong> from the Plugins screen to access the full navigation editor.
		</div>
	<?php endif; ?>
</div>
