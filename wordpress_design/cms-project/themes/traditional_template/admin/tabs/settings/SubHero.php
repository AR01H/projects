<?php
defined( 'ABSPATH' ) || exit;
require_once dirname(__DIR__, 2) . '/Models/SettingsModel.php';
?>
<div class="wrap">
	<h2>Hero Section</h2>
	<form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
		<?php wp_nonce_field( 'tt_save_settings' ); ?>
		<input type="hidden" name="action" value="tt_save_settings">
		<input type="hidden" name="return_page" value="<?php echo esc_attr($_GET['page']??''); ?>">
		<input type="hidden" name="return_subtab" value="<?php echo esc_attr($_GET['subtab']??''); ?>">
		<table class="form-table">
			<tbody>
				<?php echo App_Admin_UI::text_field('settings[site_hero_image]', 'Background Image URL', TT_Settings_Model::get('site_hero_image')); ?>
				<?php echo App_Admin_UI::text_field('settings[site_hero_badge]', 'Badge Text', TT_Settings_Model::get('site_hero_badge')); ?>
			</tbody>
		</table>
		<?php echo App_Admin_UI::submit_button('Save Settings'); ?>
	</form>
</div>
