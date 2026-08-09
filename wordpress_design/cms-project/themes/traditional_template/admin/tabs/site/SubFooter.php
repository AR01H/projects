<?php
defined( 'ABSPATH' ) || exit;
require_once dirname(__DIR__, 2) . '/Models/SettingsModel.php';
?>
<div class="wrap"><h2>Footer Settings</h2>
<form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
<?php wp_nonce_field( 'tt_save_settings' ); ?>
<input type="hidden" name="action" value="tt_save_settings">
<input type="hidden" name="return_page" value="<?php echo esc_attr($_GET['page']??''); ?>">
<input type="hidden" name="return_subtab" value="<?php echo esc_attr($_GET['subtab']??''); ?>">
<table class="form-table"><tbody>
<?php echo App_Admin_UI::text_field('settings[site_footer_tagline1]', 'Tagline Line 1', TT_Settings_Model::get('site_footer_tagline1')); ?>
<?php echo App_Admin_UI::text_field('settings[site_footer_tagline2]', 'Tagline Line 2', TT_Settings_Model::get('site_footer_tagline2')); ?>
<?php echo App_Admin_UI::text_field('settings[site_footer_copyright]', 'Copyright Text', TT_Settings_Model::get('site_footer_copyright')); ?>
<?php echo App_Admin_UI::text_field('settings[site_footer_logoSub]', 'Logo Sub Text', TT_Settings_Model::get('site_footer_logoSub')); ?>
</tbody></table>
<?php echo App_Admin_UI::submit_button('Save Footer'); ?>
</form></div>