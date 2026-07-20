<?php
/**
 * Admin Tools -> Import / Export. Export is a normal registry tool card;
 * Import needs a file upload, so it gets its own form here - posting to the
 * SAME generic nt_tool_* endpoint with the same nonce convention.
 */

defined( 'ABSPATH' ) || exit;

nt_admin_tools_render( 'import-export' );
?>

<div class="nt-admin-tools">
	<div class="nt-admin-card nt-admin-tool">
		<h3><?php esc_html_e( 'Import Settings', NT_TEXT_DOMAIN ); ?></h3>
		<p><?php esc_html_e( 'Upload a JSON file exported above. Only option groups declared in config/admin.php are accepted; every value is re-sanitized on import.', NT_TEXT_DOMAIN ); ?></p>
		<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="nt_tool_import_settings">
			<?php nt_admin_location_fields(); ?>
			<?php wp_nonce_field( 'nt_tool_import_settings' ); ?>
			<p><input type="file" name="nt_import_file" accept=".json,application/json" required></p>
			<?php submit_button( __( 'Import', NT_TEXT_DOMAIN ), 'secondary', 'submit', false ); ?>
		</form>
	</div>
</div>
