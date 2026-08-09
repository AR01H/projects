<?php
defined( 'ABSPATH' ) || exit;
require_once dirname(__DIR__, 2) . '/Models/SettingsModel.php';
App_Admin_UI::enqueue_media_uploader();
?>
<div class="wrap">
<h2>Contact Information</h2>
<form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
	<?php wp_nonce_field( 'tt_save_settings' ); ?>
	<input type="hidden" name="action" value="tt_save_settings">
	<input type="hidden" name="return_page" value="<?php echo esc_attr($_GET['page']??''); ?>">
	<input type="hidden" name="return_subtab" value="<?php echo esc_attr($_GET['subtab']??''); ?>">

	<h3 style="border-bottom:1px solid #ddd;padding-bottom:6px;">📞 Business Details</h3>
	<table class="form-table"><tbody>
		<?php echo App_Admin_UI::text_field('settings[site_contact_cafeName]', 'Cafe / Business Name', (string)(TT_Settings_Model::get('site_contact_cafeName')??'')); ?>
		<?php echo App_Admin_UI::text_field('settings[site_contact_phone]', 'Phone Number', (string)(TT_Settings_Model::get('site_contact_phone')??'')); ?>
		<?php echo App_Admin_UI::text_field('settings[site_contact_email]', 'Email Address', (string)(TT_Settings_Model::get('site_contact_email')??'')); ?>
		<?php echo App_Admin_UI::text_field('settings[site_contact_heading]', 'Section Heading', (string)(TT_Settings_Model::get('site_contact_heading')??'')); ?>
	</tbody></table>

	<h3 style="border-bottom:1px solid #ddd;padding-bottom:6px;">📍 Address</h3>
	<table class="form-table"><tbody>
		<?php
		$a = TT_Settings_Model::get('site_contact_address');
		if (is_string($a)) $a = json_decode($a, true);
		$a = (array)($a ?: ['','']);
		echo App_Admin_UI::text_field('settings[site_contact_address_1]', 'Address Line 1', (string)(TT_Settings_Model::get('site_contact_address_1') ?: ($a[0]??'')));
		echo App_Admin_UI::text_field('settings[site_contact_address_2]', 'Address Line 2', (string)(TT_Settings_Model::get('site_contact_address_2') ?: ($a[1]??'')));
		echo App_Admin_UI::text_field('settings[site_contact_address_3]', 'Address Line 3 (optional)', (string)(TT_Settings_Model::get('site_contact_address_3') ?: ($a[2]??'')));
		?>
	</tbody></table>

	<h3 style="border-bottom:1px solid #ddd;padding-bottom:6px;">🕐 Opening Hours</h3>
	<table class="form-table"><tbody>
		<?php
		$h = TT_Settings_Model::get('site_contact_hours');
		if (is_string($h)) $h = json_decode($h, true);
		$h = (array)($h ?: ['','']);
		echo App_Admin_UI::text_field('settings[site_contact_hours_1]', 'Hours Line 1 (e.g. Mon–Sun)', (string)(TT_Settings_Model::get('site_contact_hours_1') ?: ($h[0]??'')));
		echo App_Admin_UI::text_field('settings[site_contact_hours_2]', 'Hours Line 2 (e.g. 9:00 AM – 9:00 PM)', (string)(TT_Settings_Model::get('site_contact_hours_2') ?: ($h[1]??'')));
		?>
	</tbody></table>

	<h3 style="border-bottom:1px solid #ddd;padding-bottom:6px;">🗺️ Map</h3>
	<table class="form-table"><tbody>
		<?php echo App_Admin_UI::media_field('settings[site_contact_mapImage]', 'Map Image', (string)(TT_Settings_Model::get('site_contact_mapImage')??'')); ?>
		<?php echo App_Admin_UI::text_field('settings[site_contact_mapPinLabel]', 'Map Pin Label', (string)(TT_Settings_Model::get('site_contact_mapPinLabel')??'')); ?>
	</tbody></table>

	<?php echo App_Admin_UI::submit_button('Save Contact Info'); ?>
</form>
</div>