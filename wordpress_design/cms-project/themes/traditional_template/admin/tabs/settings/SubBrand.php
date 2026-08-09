<?php
defined( 'ABSPATH' ) || exit;
require_once dirname(__DIR__, 2) . '/Models/SettingsModel.php';
?>
<div class="wrap">
	<h2>Brand Identity</h2>
	<form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
		<?php wp_nonce_field( 'tt_save_settings' ); ?>
		<input type="hidden" name="action" value="tt_save_settings">
		<input type="hidden" name="return_page" value="<?php echo esc_attr($_GET['page']??''); ?>">
		<input type="hidden" name="return_subtab" value="<?php echo esc_attr($_GET['subtab']??''); ?>">
		<table class="form-table">
			<tbody>
				<?php echo App_Admin_UI::text_field('settings[site_brand_name]', 'Brand Name', TT_Settings_Model::get('site_brand_name')); ?>
				<?php echo App_Admin_UI::text_field('settings[site_brand_tagline]', 'Tagline', TT_Settings_Model::get('site_brand_tagline')); ?>
				<?php echo App_Admin_UI::text_field('settings[site_brand_logoImage]', 'Logo Image URL', TT_Settings_Model::get('site_brand_logoImage')); ?>
				<?php echo App_Admin_UI::text_field('settings[site_brand_logoLine1]', 'Logo Line 1', TT_Settings_Model::get('site_brand_logoLine1')); ?>
				<?php echo App_Admin_UI::text_field('settings[site_brand_logoLine2]', 'Logo Line 2', TT_Settings_Model::get('site_brand_logoLine2')); ?>
				<?php echo App_Admin_UI::text_field('settings[site_brand_logoLine3]', 'Logo Line 3', TT_Settings_Model::get('site_brand_logoLine3')); ?>
			</tbody>
		</table>
		<?php echo App_Admin_UI::submit_button('Save Settings'); ?>
	</form>
</div>
