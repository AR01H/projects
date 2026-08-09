<?php
defined( 'ABSPATH' ) || exit;
require_once dirname(__DIR__, 2) . '/Models/SettingsModel.php';
App_Admin_UI::enqueue_media_uploader();
?>
<div class="wrap">
<h2>Our Story</h2>
<form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
	<?php wp_nonce_field( 'tt_save_settings' ); ?>
	<input type="hidden" name="action" value="tt_save_settings">
	<input type="hidden" name="return_page" value="<?php echo esc_attr($_GET['page']??''); ?>">
	<input type="hidden" name="return_subtab" value="<?php echo esc_attr($_GET['subtab']??''); ?>">

	<h3 style="border-bottom:1px solid #ddd;padding-bottom:6px;">📋 Section Header</h3>
	<table class="form-table"><tbody>
		<?php echo App_Admin_UI::text_field('settings[site_story_heading]', 'Heading', (string)(TT_Settings_Model::get('site_story_heading')??'')); ?>
		<?php echo App_Admin_UI::textarea_field('settings[site_story_subheading]', 'Subheading', (string)(TT_Settings_Model::get('site_story_subheading')??'')); ?>
	</tbody></table>

	<h3 style="border-bottom:1px solid #ddd;padding-bottom:6px;">📝 Story Paragraphs</h3>
	<table class="form-table"><tbody>
		<?php
		$p = TT_Settings_Model::get('site_story_paragraphs');
		if (is_string($p)) $p = json_decode($p, true);
		$p = (array)($p ?: ['','','']);
		echo App_Admin_UI::textarea_field('settings[site_story_para_1]', 'Paragraph 1', (string)(TT_Settings_Model::get('site_story_para_1') ?: ($p[0]??'')));
		echo App_Admin_UI::textarea_field('settings[site_story_para_2]', 'Paragraph 2', (string)(TT_Settings_Model::get('site_story_para_2') ?: ($p[1]??'')));
		echo App_Admin_UI::textarea_field('settings[site_story_para_3]', 'Paragraph 3', (string)(TT_Settings_Model::get('site_story_para_3') ?: ($p[2]??'')));
		?>
	</tbody></table>

	<h3 style="border-bottom:1px solid #ddd;padding-bottom:6px;">🖼️ Photos</h3>
	<table class="form-table"><tbody>
		<?php echo App_Admin_UI::media_field('settings[site_story_photo]', 'Main Story Photo', (string)(TT_Settings_Model::get('site_story_photo')??'')); ?>
		<?php echo App_Admin_UI::text_field('settings[site_story_photoBadge]', 'Photo Badge Text', (string)(TT_Settings_Model::get('site_story_photoBadge')??'')); ?>
		<?php echo App_Admin_UI::media_field('settings[site_story_sketch]', 'Sketch / Secondary Image', (string)(TT_Settings_Model::get('site_story_sketch')??'')); ?>
	</tbody></table>

	<?php echo App_Admin_UI::submit_button('Save Story'); ?>
</form>
</div>