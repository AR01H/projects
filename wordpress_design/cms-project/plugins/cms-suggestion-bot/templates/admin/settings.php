<?php
/**
 * templates/admin/settings.php
 *
 * @var bool   $delete_on_uninstall
 * @var string $notice
 */

use CmsSuggestionBot\Admin\View;

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Settings', 'cms-suggestion-bot' ); ?></h1>

	<?php View::notice( $notice ); ?>

	<form method="post">
		<?php wp_nonce_field( 'csb_settings' ); ?>
		<table class="form-table" role="presentation">
			<tr>
				<th><?php esc_html_e( 'On Uninstall', 'cms-suggestion-bot' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="delete_on_uninstall" value="1" <?php checked( $delete_on_uninstall ); ?>>
						<?php esc_html_e( 'Delete all plugin data (tables, cache, logs, API keys, options) when this plugin is uninstalled.', 'cms-suggestion-bot' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Off by default - deactivating or uninstalling the plugin normally leaves your Knowledge Base, logs, and settings in place in case you reinstall it.', 'cms-suggestion-bot' ); ?></p>
				</td>
			</tr>
		</table>
		<?php submit_button( __( 'Save Settings', 'cms-suggestion-bot' ), 'primary', 'csb_settings_save' ); ?>
	</form>
</div>
