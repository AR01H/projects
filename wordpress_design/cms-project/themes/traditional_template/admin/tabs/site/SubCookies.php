<?php defined('ABSPATH')||exit; require_once dirname(__DIR__, 2) . '/Models/SettingsModel.php'; ?>
<div class="wrap"><h2>Cookie Settings</h2><form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
<?php wp_nonce_field('tt_save_settings'); ?>
<input type="hidden" name="action" value="tt_save_settings">
<input type="hidden" name="return_page" value="<?php echo esc_attr($_GET['page']??''); ?>">
<input type="hidden" name="return_subtab" value="<?php echo esc_attr($_GET['subtab']??''); ?>">
<table class="form-table"><tbody>
<?php echo App_Admin_UI::text_field('settings[cookies_enabled]', 'Enabled (1/0)', TT_Settings_Model::get('cookies_enabled')); ?>
<?php echo App_Admin_UI::text_field('settings[cookies_cookie_name]', 'Cookie Name', TT_Settings_Model::get('cookies_cookie_name')); ?>
<?php echo App_Admin_UI::text_field('settings[cookies_accept_days]', 'Accept Days', TT_Settings_Model::get('cookies_accept_days')); ?>
</tbody></table>
<?php echo App_Admin_UI::submit_button('Save Cookie Settings'); ?></form></div>