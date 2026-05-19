<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );
?>
<div class="wrap ch-admin-wrap">
	<h1>🗑️ Cleanup Data</h1>

	<?php if ( isset( $_GET['cleaned'] ) ) : ?>
		<div class="ch-notice ch-notice--success">
			✅ <?php echo esc_html( urldecode( $_GET['msg'] ?? 'Cleanup complete.' ) ); ?>
		</div>
	<?php endif; ?>

	<div class="ch-card" style="border-color:#dc3545;">
		<h2 style="color:#dc3545;">⚠️ Destructive Action</h2>
		<p>This will remove all seeded reviews, FAQs, and marquee items from the shared plugin tables, and delete all Cane House WordPress options.</p>
		<p style="margin-top:.5rem;font-size:.85rem;color:#666;">This does <strong>not</strong> delete the database tables themselves - only the rows seeded by this theme.</p>
	</div>

	<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
		<?php wp_nonce_field( 'ch_theme_cleanup' ); ?>
		<input type="hidden" name="action" value="ch_theme_cleanup">
		<?php submit_button( '🗑️ Run Cleanup', 'delete', 'submit', false, [ 'onclick' => 'return confirm("This will delete all seeded Cane House data. Are you sure?");' ] ); ?>
	</form>
</div>
