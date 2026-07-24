<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use Ah\Cms\Admin\Components\AdminComponents;

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
		$timezone_offset = sanitize_text_field( wp_unslash( $_POST['gmt_offset'] ?? '0' ) );
		$disable_opt = isset( $_POST['ah_disable_optimized_images'] ) ? '1' : '0';
		$cache_enabled = isset( $_POST['ah_cache_enabled'] ) ? '1' : '0';
		$cache_expiry = absint( $_POST['ah_cache_expiry'] ?? 3600 );

		update_option( 'gmt_offset', (float) $timezone_offset );
		update_option( 'timezone_string', '' );
		update_option( 'ah_disable_optimized_images', $disable_opt );
		update_option( 'ah_cache_enabled', $cache_enabled );
		update_option( 'ah_cache_expiry', $cache_expiry );

		$notice = 'Global settings saved successfully.';
	}
}

$current_timezone = (float) get_option( 'gmt_offset', 0 );
$disable_opt      = get_option( 'ah_disable_optimized_images', '0' );
$cache_enabled    = get_option( 'ah_cache_enabled', '0' );
$cache_expiry     = get_option( 'ah_cache_expiry', 3600 );
$timezone_choices = array();

$format_timezone_offset_label = static function ( float $offset ) : string {
	$sign     = $offset >= 0 ? '+' : '-';
	$absolute = abs( $offset );
	$hours    = (int) floor( $absolute );
	$minutes  = (int) round( ( $absolute - $hours ) * 60 );

	if ( 0 === $hours && 0 === $minutes ) {
		return 'UTC+00';
	}

	$label = sprintf( 'UTC%s%02d', $sign, $hours );

	if ( 0 !== $minutes ) {
		$label .= sprintf( ':%02d', $minutes );
	}

	return $label;
};

for ( $offset = -12; $offset <= 14; $offset += 0.5 ) {
	$timezone_choices[] = array(
		'value' => (string) $offset,
		'label' => $format_timezone_offset_label( (float) $offset ),
	);
}
?>
<div class="wrap ah-wrap">
	<?php AdminComponents::pageHeader( 'admin-generic', 'Global Settings', 'Configure timezone, caching, and image optimization for the entire site.' ); ?>

	<?php if ( $notice ) : ?>
		<?php AdminComponents::notice( $notice, $n_type ); ?>
	<?php endif; ?>

	<?php ob_start(); ?>
		<form method="post">
			<?php wp_nonce_field( 'ah_save_global_settings', 'ah_global_settings_nonce' ); ?>

			<?php
			$tz_select = '<select name="gmt_offset" style="width:100%;max-width:400px;">';
			foreach ( $timezone_choices as $timezone_choice ) {
				$tz_select .= '<option value="' . esc_attr( $timezone_choice['value'] ) . '"' . selected( (string) $current_timezone, (string) $timezone_choice['value'], false ) . '>' . esc_html( $timezone_choice['label'] ) . '</option>';
			}
			$tz_select .= '</select><p class="description">Choose an offset like UTC+00, UTC+01, or UTC+05:30. City/country timezones are not used here.</p>';
			AdminComponents::formRow( 'Timezone Offset', $tz_select );
			?>

			<?php AdminComponents::formRow( 'Disable Optimized Images',
				'<label style="display:flex;align-items:center;gap:8px;cursor:pointer;"><input type="checkbox" name="ah_disable_optimized_images" value="1"' . checked( $disable_opt, '1', false ) . '> Skip WebP conversion for uploaded images</label>'
			); ?>

			<?php AdminComponents::formRow( 'Enable Page Cache',
				'<label style="display:flex;align-items:center;gap:8px;cursor:pointer;"><input type="checkbox" name="ah_cache_enabled" value="1"' . checked( $cache_enabled, '1', false ) . '> Cache front-end HTML output</label>'
			); ?>

			<?php AdminComponents::formRow( 'Cache Expiry (seconds)',
				'<input type="number" name="ah_cache_expiry" value="' . esc_attr( $cache_expiry ) . '" min="60" max="86400" style="width:120px;"><p class="description">How long cached pages stay fresh. Default: 3600 (1 hour).</p>'
			); ?>

			<div style="display:flex;gap:10px;margin-top:16px;">
				<button type="submit" class="ah-btn ah-btn-primary">Save Settings</button>
				<button type="submit" name="clear_cache" value="1" class="ah-btn ah-btn-secondary ah-confirm-delete" data-title="Clear Cache" data-confirm="All cached data will be flushed.">Clear Cache Now</button>
			</div>
		</form>
	<?php AdminComponents::card( 'Site Performance', ob_get_clean() ); ?>
</div>
