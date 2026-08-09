<?php
defined( 'ABSPATH' ) || exit;
require_once dirname(__DIR__, 2) . '/Models/SettingsModel.php';
App_Admin_UI::enqueue_media_uploader();
?>
<div class="wrap">
<h2>Hero Section</h2>
<form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
	<?php wp_nonce_field( 'tt_save_settings' ); ?>
	<input type="hidden" name="action" value="tt_save_settings">
	<input type="hidden" name="return_page" value="<?php echo esc_attr($_GET['page']??''); ?>">
	<input type="hidden" name="return_subtab" value="<?php echo esc_attr($_GET['subtab']??''); ?>">

	<h3 style="border-bottom:1px solid #ddd;padding-bottom:6px;">🖼️ Background Image</h3>
	<table class="form-table"><tbody>
		<?php echo App_Admin_UI::media_field('settings[site_hero_image]', 'Background Image', (string)(TT_Settings_Model::get('site_hero_image')??'')); ?>
		<?php echo App_Admin_UI::text_field('settings[site_hero_badge]', 'Badge Text (e.g. 🌿 Freshly Pressed)', (string)(TT_Settings_Model::get('site_hero_badge')??'')); ?>
	</tbody></table>

	<h3 style="border-bottom:1px solid #ddd;padding-bottom:6px;">📝 Title Lines <small style="font-weight:normal;">(shown in animated sequence)</small></h3>
	<table class="form-table"><tbody>
		<?php
		$t = TT_Settings_Model::get('site_hero_title');
		if (is_string($t)) $t = json_decode($t, true);
		$t = (array)($t ?: ['','','']);
		echo App_Admin_UI::text_field('settings[site_hero_title_0]', 'Title Line 1', (string)(TT_Settings_Model::get('site_hero_title_0') ?: ($t[0]??'')));
		echo App_Admin_UI::text_field('settings[site_hero_title_1]', 'Title Line 2', (string)(TT_Settings_Model::get('site_hero_title_1') ?: ($t[1]??'')));
		echo App_Admin_UI::text_field('settings[site_hero_title_2]', 'Title Line 3', (string)(TT_Settings_Model::get('site_hero_title_2') ?: ($t[2]??'')));
		$s = TT_Settings_Model::get('site_hero_subtitle');
		if (is_string($s)) $s = json_decode($s, true);
		$s = (array)($s ?: ['','']);
		echo App_Admin_UI::text_field('settings[site_hero_subtitle_0]', 'Subtitle Line 1', (string)(TT_Settings_Model::get('site_hero_subtitle_0') ?: ($s[0]??'')));
		echo App_Admin_UI::text_field('settings[site_hero_subtitle_1]', 'Subtitle Line 2', (string)(TT_Settings_Model::get('site_hero_subtitle_1') ?: ($s[1]??'')));
		?>
	</tbody></table>

	<h3 style="border-bottom:1px solid #ddd;padding-bottom:6px;">🔘 CTA Buttons</h3>
	<table class="form-table"><tbody>
		<?php
		echo App_Admin_UI::text_field('settings[site_hero_btn1_label]', 'Button 1 Label', (string)(TT_Settings_Model::get('site_hero_btn1_label')??''));
		echo App_Admin_UI::text_field('settings[site_hero_btn1_href]',  'Button 1 Link',  (string)(TT_Settings_Model::get('site_hero_btn1_href')??''));
		echo App_Admin_UI::text_field('settings[site_hero_btn2_label]', 'Button 2 Label', (string)(TT_Settings_Model::get('site_hero_btn2_label')??''));
		echo App_Admin_UI::text_field('settings[site_hero_btn2_href]',  'Button 2 Link',  (string)(TT_Settings_Model::get('site_hero_btn2_href')??''));
		?>
	</tbody></table>

	<?php echo App_Admin_UI::submit_button('Save Hero Settings'); ?>
</form>
</div>