<?php
defined( 'ABSPATH' ) || exit;
require_once dirname(__DIR__, 2) . '/Models/SettingsModel.php';
App_Admin_UI::enqueue_media_uploader();
?>
<div class="wrap">
<h2>Events & Catering</h2>
<form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
	<?php wp_nonce_field( 'tt_save_settings' ); ?>
	<input type="hidden" name="action" value="tt_save_settings">
	<input type="hidden" name="return_page" value="<?php echo esc_attr($_GET['page']??''); ?>">
	<input type="hidden" name="return_subtab" value="<?php echo esc_attr($_GET['subtab']??''); ?>">
	<table class="form-table"><tbody>
	<?php echo App_Admin_UI::text_field('settings[site_events_heading]', 'Heading', (string)(TT_Settings_Model::get('site_events_heading')??'')); ?>
	<?php echo App_Admin_UI::textarea_field('settings[site_events_subheading]', 'Subheading', (string)(TT_Settings_Model::get('site_events_subheading')??'')); ?>
	<?php echo App_Admin_UI::media_field('settings[site_events_image]', 'Section Image', (string)(TT_Settings_Model::get('site_events_image')??'')); ?>
	<?php echo App_Admin_UI::text_field('settings[site_events_sign_0]', 'Decorative Sign Line 1', (string)(TT_Settings_Model::get('site_events_sign_0')??'')); ?>
	<?php echo App_Admin_UI::text_field('settings[site_events_sign_1]', 'Decorative Sign Line 2', (string)(TT_Settings_Model::get('site_events_sign_1')??'')); ?>
	<?php echo App_Admin_UI::text_field('settings[site_events_sign_2]', 'Decorative Sign Line 3', (string)(TT_Settings_Model::get('site_events_sign_2')??'')); ?>
	<?php echo App_Admin_UI::text_field('settings[cta_events_tag]', 'CTA Tag', (string)(TT_Settings_Model::get('cta_events_tag')??'')); ?>
	<?php echo App_Admin_UI::text_field('settings[cta_events_title]', 'CTA Title', (string)(TT_Settings_Model::get('cta_events_title')??'')); ?>
	<?php echo App_Admin_UI::textarea_field('settings[cta_events_sub]', 'CTA Subtitle', (string)(TT_Settings_Model::get('cta_events_sub')??'')); ?>
	</tbody></table>
	<?php echo App_Admin_UI::submit_button('Save Events & Catering'); ?>
</form>
</div>