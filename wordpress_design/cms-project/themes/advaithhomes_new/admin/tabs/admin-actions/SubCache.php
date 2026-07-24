<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="card" style="max-width:none;">
	<h2><?php esc_html_e( 'Clear Cache', ADN_TEXT_DOMAIN ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'Flushes the WordPress object cache, deletes this theme\'s transients, and resets OPcache (if available). Use this after deploying code or content changes that still look stale.', ADN_TEXT_DOMAIN ); ?>
	</p>

	<?php if ( class_exists( 'ADN_Cache' ) ) : ?>
		<div style="margin: 15px 0; padding: 12px; background: #f6f7f7; border-left: 4px solid #2271b1; border-radius: 0 4px 4px 0; font-size: 13px;">
			<strong><?php esc_html_e( 'Theme Filesystem Cache Directory:', ADN_TEXT_DOMAIN ); ?></strong>
			<code style="display: block; margin-top: 6px; word-break: break-all;"><?php echo esc_html( ADN_Cache::get_cache_dir() ); ?></code>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="adn_clear_cache" />
		<?php wp_nonce_field( 'adn_clear_cache' ); ?>

		<div style="margin: 20px 0; padding: 15px; border: 1px solid #c3c4c7; background: #fff; border-radius: 4px;">
			<h3 style="margin-top:0;"><?php esc_html_e( 'Cache Settings', ADN_TEXT_DOMAIN ); ?></h3>
			<label for="ah_cache_enabled_checkbox" style="display: inline-flex; align-items: center; gap: 10px; font-weight: 600; cursor: pointer; user-select: none;">
				<input type="checkbox" id="ah_cache_enabled_checkbox" name="ah_cache_enabled" value="1" <?php checked( get_option( 'ah_cache_enabled', '1' ), '1' ); ?> />
				<?php esc_html_e( 'Enable Front-End Cache', ADN_TEXT_DOMAIN ); ?>
			</label>
			<p class="description" style="margin-left: 24px; margin-top: 5px;">
				<?php esc_html_e( 'When enabled, frontend page contexts (home, listing, widgets, single posts) are cached to local JSON files for faster performance. Uncheck to disable caching.', ADN_TEXT_DOMAIN ); ?>
			</p>
		</div>

		<p>
			<button type="submit" class="button button-primary">
				<?php esc_html_e( 'Save Settings & Clear Cache', ADN_TEXT_DOMAIN ); ?>
			</button>
		</p>
	</form>
</div>

<div class="card" style="max-width:none;">
	<h2><?php esc_html_e( 'Cookie Consent', ADN_TEXT_DOMAIN ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'The consent banner is stored client-side in each visitor\'s browser - there\'s no per-visitor record on the server. These actions bump a version number so the banner shows again on a visitor\'s next page load, without affecting anyone still mid-session.', ADN_TEXT_DOMAIN ); ?>
	</p>

	<p style="display:flex; gap:10px; flex-wrap:wrap;">
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="adn_reask_cookie_rejected" />
			<?php wp_nonce_field( 'adn_reask_cookie_rejected' ); ?>
			<button type="submit" class="button" onclick="return confirm('<?php echo esc_js( __( 'Re-ask cookie consent for everyone who previously rejected it?', ADN_TEXT_DOMAIN ) ); ?>');">
				<?php esc_html_e( 'Re-ask Cookie Consent for Rejected', ADN_TEXT_DOMAIN ); ?>
			</button>
		</form>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="adn_reask_cookie_all" />
			<?php wp_nonce_field( 'adn_reask_cookie_all' ); ?>
			<button type="submit" class="button button-secondary" onclick="return confirm('<?php echo esc_js( __( 'Re-ask cookie consent for EVERY visitor, including those who already accepted?', ADN_TEXT_DOMAIN ) ); ?>');">
				<?php esc_html_e( 'Re-ask Cookie Consent for All', ADN_TEXT_DOMAIN ); ?>
			</button>
		</form>
	</p>

	<p class="description" style="margin-top:10px;">
		<?php
		printf(
			/* translators: 1: accept version number, 2: reject version number */
			esc_html__( 'Current versions - accepted: %1$d, rejected: %2$d', ADN_TEXT_DOMAIN ),
			(int) get_option( 'adn_cookie_consent_accept_version', 1 ),
			(int) get_option( 'adn_cookie_consent_reject_version', 1 )
		);
		?>
	</p>
</div>
