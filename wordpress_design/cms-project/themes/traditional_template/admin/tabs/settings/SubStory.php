<?php
defined( 'ABSPATH' ) || exit;
require_once dirname(__DIR__, 2) . '/Models/SettingsModel.php';
?>
<div class="wrap">
	<h2>Our Story</h2>
	<form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
		<?php wp_nonce_field( 'tt_save_settings' ); ?>
		<input type="hidden" name="action" value="tt_save_settings">
		<input type="hidden" name="return_page" value="<?php echo esc_attr($_GET['page']??''); ?>">
		<input type="hidden" name="return_subtab" value="<?php echo esc_attr($_GET['subtab']??''); ?>">
		<table class="form-table">
			<tbody>
				<?php echo App_Admin_UI::text_field('settings[site_story_heading]', 'Heading', TT_Settings_Model::get('site_story_heading')); ?>
				<?php echo App_Admin_UI::textarea_field('settings[site_story_subheading]', 'Subheading', TT_Settings_Model::get('site_story_subheading')); ?>
				<?php echo App_Admin_UI::text_field('settings[site_story_photo]', 'Photo URL', TT_Settings_Model::get('site_story_photo')); ?>
				<?php echo App_Admin_UI::text_field('settings[site_story_photoBadge]', 'Photo Badge Text', TT_Settings_Model::get('site_story_photoBadge')); ?>
				<?php
				$paras = TT_Settings_Model::get('site_story_paragraphs');
				if (is_string($paras)) $paras = json_decode($paras, true);
				$paras = (array)($paras ?: ['','','']);
				echo App_Admin_UI::textarea_field('settings[site_story_para_1]', 'Paragraph 1', TT_Settings_Model::get('site_story_para_1') ?: ($paras[0]??''));
				echo App_Admin_UI::textarea_field('settings[site_story_para_2]', 'Paragraph 2', TT_Settings_Model::get('site_story_para_2') ?: ($paras[1]??''));
				echo App_Admin_UI::textarea_field('settings[site_story_para_3]', 'Paragraph 3', TT_Settings_Model::get('site_story_para_3') ?: ($paras[2]??''));
				?>
			</tbody>
		</table>
		<?php echo App_Admin_UI::submit_button('Save Story'); ?>
	</form>
</div>
