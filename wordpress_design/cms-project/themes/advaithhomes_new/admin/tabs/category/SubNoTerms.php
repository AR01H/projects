<?php
/**
 * admin/tabs/category/sub-no-terms.php - Shown when CMS plugin has no active parent terms.
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="notice notice-warning inline" style="margin:0;">
	<p><?php esc_html_e( 'No active parent terms found. Add terms at:', ADN_TEXT_DOMAIN ); ?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-taxonomy' ) ); ?>">
			<?php esc_html_e( 'CMS → Taxonomy', ADN_TEXT_DOMAIN ); ?>
		</a>
	</p>
</div>
