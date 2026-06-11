<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="card" style="max-width:none;">
	<h2><?php esc_html_e( 'Clear Cache', ADN_TEXT_DOMAIN ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'Flushes the WordPress object cache, deletes this theme\'s transients, and resets OPcache (if available). Use this after deploying code or content changes that still look stale.', ADN_TEXT_DOMAIN ); ?>
	</p>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="adn_clear_cache" />
		<?php wp_nonce_field( 'adn_clear_cache' ); ?>
		<p>
			<button type="submit" class="button button-primary">
				<?php esc_html_e( 'Clear Cache Now', ADN_TEXT_DOMAIN ); ?>
			</button>
		</p>
	</form>
</div>
