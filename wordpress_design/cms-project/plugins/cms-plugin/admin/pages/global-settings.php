<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$notice = '';
$n_type = 'success';

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ah_global_settings_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_global_settings_nonce'], 'ah_save_global_settings' ) ) {
		wp_die( 'Security check failed.' );
	}

	if ( isset( $_POST['clear_cache'] ) ) {
		if ( function_exists( 'cache_clear_all' ) ) {
			cache_clear_all();
			$notice = 'All caches have been cleared.';
		} else {
			$notice = 'Cache engine not loaded.';
			$n_type = 'error';
		}
	} else {
		$timezone = sanitize_text_field( wp_unslash( $_POST['timezone_string'] ?? '' ) );
		$disable_opt = isset( $_POST['ah_disable_optimized_images'] ) ? '1' : '0';
		$cache_enabled = isset( $_POST['ah_cache_enabled'] ) ? '1' : '0';
		$cache_expiry = absint( $_POST['ah_cache_expiry'] ?? 3600 );

		if ( $timezone ) {
			update_option( 'timezone_string', $timezone );
		}
		update_option( 'ah_disable_optimized_images', $disable_opt );
		update_option( 'ah_cache_enabled', $cache_enabled );
		update_option( 'ah_cache_expiry', $cache_expiry );

		$notice = 'Global settings saved successfully.';
	}
}

$current_timezone = get_option( 'timezone_string' );
$disable_opt      = get_option( 'ah_disable_optimized_images', '0' );
$cache_enabled    = get_option( 'ah_cache_enabled', '0' );
$cache_expiry     = get_option( 'ah_cache_expiry', 3600 );
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e( 'Global Settings', 'ah-theme' ); ?></h1>

	<?php if ( $notice ) : ?>
		<div class="notice notice-<?php echo esc_attr( $n_type ); ?> is-dismissible">
			<p><?php echo esc_html( $notice ); ?></p>
		</div>
	<?php endif; ?>

	<div class="ah-card" style="padding: 24px; max-width: 800px;">
		<form method="post">
			<?php wp_nonce_field( 'ah_save_global_settings', 'ah_global_settings_nonce' ); ?>

			<table class="form-table" role="presentation">
				<tbody>
					<!-- Timezone -->
					<tr>
						<th scope="row">
							<label for="timezone_string">Timezone</label>
						</th>
						<td>
							<select id="timezone_string" name="timezone_string" style="width:100%; max-width:400px;">
								<?php echo wp_timezone_choice( $current_timezone ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</select>
							<p class="description">Choose a city in the same timezone as you.</p>
						</td>
					</tr>

					<!-- Disable Optimized Images -->
					<tr>
						<th scope="row">
							<label for="ah_disable_optimized_images">Disable Optimized Images</label>
						</th>
						<td>
							<label>
								<input type="checkbox" id="ah_disable_optimized_images" name="ah_disable_optimized_images" value="1" <?php checked( $disable_opt, '1' ); ?>>
								Do not scale down large images on upload
							</label>
							<p class="description">
								If checked, WordPress will not resize huge images (e.g., 10MB+) down to 2560px.
								The original image will be served to users.
							</p>
						</td>
					</tr>

					<!-- Cache Enabled -->
					<tr>
						<th scope="row">
							<label for="ah_cache_enabled">Enable Front-End Cache</label>
						</th>
						<td>
							<label>
								<input type="checkbox" id="ah_cache_enabled" name="ah_cache_enabled" value="1" <?php checked( $cache_enabled, '1' ); ?>>
								Enable caching for heavy queries on the front end.
							</label>
						</td>
					</tr>

					<!-- Cache Expiry -->
					<tr>
						<th scope="row">
							<label for="ah_cache_expiry">Cache Expiry (Seconds)</label>
						</th>
						<td>
							<input type="number" id="ah_cache_expiry" name="ah_cache_expiry" value="<?php echo esc_attr( $cache_expiry ); ?>" style="width:100px;">
							<p class="description">Default time (in seconds) to keep data cached. 3600 = 1 hour, 86400 = 1 day.</p>
						</td>
					</tr>

				</tbody>
			</table>

			<p class="submit" style="display:flex;gap:15px;align-items:center;">
				<button type="submit" class="ah-btn ah-btn-primary">Save Settings</button>
				<button type="submit" name="clear_cache" value="1" class="ah-btn ah-btn-secondary" style="color:#b32d2e;border-color:#b32d2e;" onclick="return confirm('Are you sure you want to clear all caches?');">Clear All Caches</button>
			</p>
		</form>
	</div>
</div>
