<?php defined('ABSPATH')||exit; require_once dirname(__DIR__, 2) . '/Models/SettingsModel.php'; ?>
<div class="wrap"><h2>Social Media</h2><form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
<?php wp_nonce_field('tt_save_settings'); ?>
<input type="hidden" name="action" value="tt_save_settings">
<input type="hidden" name="return_page" value="<?php echo esc_attr($_GET['page']??''); ?>">
<input type="hidden" name="return_subtab" value="<?php echo esc_attr($_GET['subtab']??''); ?>">
<table class="form-table"><tbody>
<?php echo App_Admin_UI::text_field('settings[site_footer_socials_facebook]', 'Facebook URL', TT_Settings_Model::get('site_footer_socials_facebook')); ?>
<?php echo App_Admin_UI::text_field('settings[site_footer_socials_instagram]', 'Instagram URL', TT_Settings_Model::get('site_footer_socials_instagram')); ?>
<?php echo App_Admin_UI::text_field('settings[site_footer_socials_whatsapp]', 'WhatsApp URL', TT_Settings_Model::get('site_footer_socials_whatsapp')); ?>
<?php echo App_Admin_UI::text_field('settings[site_footer_socials_tiktok]', 'TikTok URL', TT_Settings_Model::get('site_footer_socials_tiktok')); ?>
</tbody></table>
<?php echo App_Admin_UI::submit_button('Save Social Links'); ?></form></div>