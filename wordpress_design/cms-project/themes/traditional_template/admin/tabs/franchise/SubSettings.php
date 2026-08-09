<?php
defined( 'ABSPATH' ) || exit;
require_once dirname(__DIR__, 2) . '/Models/SettingsModel.php';
App_Admin_UI::enqueue_media_uploader();
?>
<div class="wrap">
<h2>Franchise Settings</h2>
<form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
	<?php wp_nonce_field( 'tt_save_settings' ); ?>
	<input type="hidden" name="action" value="tt_save_settings">
	<input type="hidden" name="return_page" value="<?php echo esc_attr($_GET['page']??''); ?>">
	<input type="hidden" name="return_subtab" value="<?php echo esc_attr($_GET['subtab']??''); ?>">
	<table class="form-table"><tbody>
	<?php echo App_Admin_UI::media_field('settings[franchise_hero_img]', 'Hero Image', (string)(TT_Settings_Model::get('franchise_hero_img')??'')); ?>
	<?php echo App_Admin_UI::text_field('settings[franchise_hero_title]', 'Hero Title', (string)(TT_Settings_Model::get('franchise_hero_title')??'')); ?>
	<?php echo App_Admin_UI::text_field('settings[franchise_hero_subtitle]', 'Hero Subtitle', (string)(TT_Settings_Model::get('franchise_hero_subtitle')??'')); ?>
	<?php echo App_Admin_UI::media_field('settings[franchise_cta_img]', 'CTA Image', (string)(TT_Settings_Model::get('franchise_cta_img')??'')); ?>
	<?php echo App_Admin_UI::text_field('settings[cta_franchise_tag]', 'CTA Tag', (string)(TT_Settings_Model::get('cta_franchise_tag')??'')); ?>
	<?php echo App_Admin_UI::text_field('settings[cta_franchise_title]', 'CTA Title', (string)(TT_Settings_Model::get('cta_franchise_title')??'')); ?>
	<?php echo App_Admin_UI::textarea_field('settings[cta_franchise_sub]', 'CTA Subtitle', (string)(TT_Settings_Model::get('cta_franchise_sub')??'')); ?>
	</tbody></table>
	<?php echo App_Admin_UI::submit_button('Save Franchise Settings'); ?>
</form>
</div>