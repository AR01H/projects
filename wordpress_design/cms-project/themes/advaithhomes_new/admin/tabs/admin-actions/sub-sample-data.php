<?php
/**
 * admin/tabs/admin-actions/sub-sample-data.php - seed sample Guide content.
 */

defined( 'ABSPATH' ) || exit;

$cms_ready = function_exists( 'adn_cms_available' ) && adn_cms_available();
?>
<div class="card" style="max-width:none;">
	<h2><?php esc_html_e( 'Seed Sample Content', ADN_TEXT_DOMAIN ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'Creates a starter Guide structure in the CMS plugin so the home page shows real data: the type "Guide" → parent terms Buying / Selling / House Movers (the "Where are you in your journey?" cards) → topics → a few articles, plus some news posts.', ADN_TEXT_DOMAIN ); ?>
	</p>
	<p class="description">
		<?php esc_html_e( 'Safe to run more than once - existing items (matched by slug) are left untouched, never duplicated. You can edit or delete everything afterwards in the CMS plugin (Taxonomies / Posts).', ADN_TEXT_DOMAIN ); ?>
	</p>

	<?php if ( ! $cms_ready ) : ?>
		<div class="notice notice-warning inline" style="margin:12px 0;">
			<p><?php esc_html_e( 'The CMS plugin tables were not found. Activate the CMS plugin first, then seed.', ADN_TEXT_DOMAIN ); ?></p>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="adn_seed_content">
		<?php wp_nonce_field( 'adn_seed_content' ); ?>
		<p>
			<button type="submit" class="button button-primary" <?php disabled( ! $cms_ready ); ?>>
				<?php esc_html_e( 'Seed sample content', ADN_TEXT_DOMAIN ); ?>
			</button>
		</p>
	</form>
</div>
