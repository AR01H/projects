<?php
/**
 * VintageSoulTheme - "Theme Settings" admin page (admin.php?page=vst-theme-settings).
 * Entirely theme-owned - registered by Services/Plugins/AdminBridgeService,
 * not an override of any plugin admin page.
 */

defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Access denied.' );
}

use VintageSoul\Services\Plugins\CookieConsentBridgeService;
use VintageSoul\Services\Plugins\GlobalSettingsBridgeService;
use VintageSoul\Services\Plugins\GoogleServicesBridgeService;

$has_admin_components = class_exists( '\Ah\Cms\Admin\Components\AdminComponents' );

$notice = '';
$n_type = 'success';
$tab    = sanitize_key( $_GET['tab'] ?? 'google' );

// ── POST: save Google service IDs ───────────────────────────────────────────
if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['vst_save_google_nonce'] ) ) {
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vst_save_google_nonce'] ) ), 'vst_save_google' ) ) {
		$notice = 'Security check failed.';
		$n_type = 'error';
	} else {
		update_option( GoogleServicesBridgeService::OPT_ANALYTICS_IDS, sanitize_text_field( wp_unslash( $_POST['ga_ids'] ?? '' ) ) );
		update_option( GoogleServicesBridgeService::OPT_TAG_MANAGER_ID, sanitize_text_field( wp_unslash( $_POST['gtm_id'] ?? '' ) ) );
		$notice = 'Google services settings saved.';
	}
	$tab = 'google';
}

// ── POST: clear theme cache ─────────────────────────────────────────────────
if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['vst_clear_cache_nonce'] ) ) {
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vst_clear_cache_nonce'] ) ), 'vst_clear_cache' ) ) {
		$notice = 'Security check failed.';
		$n_type = 'error';
	} else {
		GlobalSettingsBridgeService::clear_all();
		$notice = 'Theme cache cleared.';
	}
	$tab = 'cache';
}

// ── POST: bump cookie consent version (re-ask everyone) ────────────────────
if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['vst_reask_cookie_nonce'] ) ) {
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vst_reask_cookie_nonce'] ) ), 'vst_reask_cookie' ) ) {
		$notice = 'Security check failed.';
		$n_type = 'error';
	} else {
		CookieConsentBridgeService::bump_version();
		$notice = 'Cookie consent will be re-asked for every visitor on their next page load.';
	}
	$tab = 'cache';
}

$ga_ids_value = (string) get_option( GoogleServicesBridgeService::OPT_ANALYTICS_IDS, '' );
$gtm_id_value = (string) get_option( GoogleServicesBridgeService::OPT_TAG_MANAGER_ID, '' );
$cache_version = CookieConsentBridgeService::get_version();
?>
<div class="wrap ah-wrap">
	<?php if ( $has_admin_components ) : ?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::pageHeader( 'admin-generic', 'Theme Settings', 'Theme-level settings for VintageSoulTheme: Google services, cache, and cookie consent.' ); ?>
		<?php if ( $notice ) : ?>
			<?php \Ah\Cms\Admin\Components\AdminComponents::notice( $notice, $n_type ); ?>
		<?php endif; ?>
	<?php else : ?>
		<h1>Theme Settings</h1>
		<p class="description">Theme-level settings for VintageSoulTheme: Google services, cache, and cookie consent.</p>
		<?php if ( $notice ) : ?>
			<div class="notice notice-<?php echo 'error' === $n_type ? 'error' : 'success'; ?>"><p><?php echo esc_html( $notice ); ?></p></div>
		<?php endif; ?>
	<?php endif; ?>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'vst-theme-settings', 'tab' => 'google' ), admin_url( 'admin.php' ) ) ); ?>" class="nav-tab<?php echo 'google' === $tab ? ' nav-tab-active' : ''; ?>">Google Services</a>
		<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'vst-theme-settings', 'tab' => 'cache' ), admin_url( 'admin.php' ) ) ); ?>" class="nav-tab<?php echo 'cache' === $tab ? ' nav-tab-active' : ''; ?>">Cache &amp; Cookies</a>
	</h2>

	<?php if ( 'google' === $tab ) : ?>
		<div class="card" style="max-width:none;margin-top:16px;">
			<h2>Google Analytics &amp; Tag Manager</h2>
			<p class="description">
				Calls into the CMS Plugin's own <code>google-services.js</code> library (always loaded on the frontend) with
				the IDs below - see <code>initGoogleAnalytics()</code> / <code>initGoogleTagManager()</code>. Leave a field
				empty to skip that service.
			</p>
			<form method="post">
				<?php wp_nonce_field( 'vst_save_google', 'vst_save_google_nonce' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="ga_ids">Google Analytics Measurement ID(s)</label></th>
						<td>
							<input type="text" id="ga_ids" name="ga_ids" value="<?php echo esc_attr( $ga_ids_value ); ?>" class="regular-text" placeholder="G-XXXXXXXXXX, G-YYYYYYYYYY">
							<p class="description">Comma-separate more than one (e.g. tracking under two GA4 properties).</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="gtm_id">Google Tag Manager Container ID</label></th>
						<td>
							<input type="text" id="gtm_id" name="gtm_id" value="<?php echo esc_attr( $gtm_id_value ); ?>" class="regular-text" placeholder="GTM-XXXXXXX">
						</td>
					</tr>
				</table>
				<p><button type="submit" class="button button-primary">Save Settings</button></p>
			</form>
		</div>
	<?php else : ?>
		<div class="card" style="max-width:none;margin-top:16px;">
			<h2>Clear Theme Cache</h2>
			<p class="description">
				Flushes this theme's own filesystem cache (<code>GlobalSettingsBridgeService</code>) and the CMS Plugin's
				transient/cache registry. The plugin's own "Clear Cache Now" (Global Settings) already triggers this too via
				<code>includes/ADN_Cache.php</code> - this button is here for convenience.
			</p>
			<form method="post">
				<?php wp_nonce_field( 'vst_clear_cache', 'vst_clear_cache_nonce' ); ?>
				<button type="submit" class="button button-primary">Clear Theme Cache Now</button>
			</form>
		</div>

		<div class="card" style="max-width:none;margin-top:16px;">
			<h2>Cookie Consent</h2>
			<p class="description">
				The consent banner's decision is stored only in each visitor's own browser - there is no per-visitor record
				on the server. This bumps a version number so the banner shows again for everyone on their next page load,
				without affecting anyone currently mid-session.
			</p>
			<p class="description">Current consent version: <strong><?php echo esc_html( (string) $cache_version ); ?></strong></p>
			<form method="post">
				<?php wp_nonce_field( 'vst_reask_cookie', 'vst_reask_cookie_nonce' ); ?>
				<button type="submit" class="button button-secondary" onclick="return confirm('Re-ask cookie consent for every visitor?');">Re-ask Cookie Consent for All</button>
			</form>
		</div>
	<?php endif; ?>
</div>
