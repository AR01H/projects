<?php
defined( 'ABSPATH' ) || exit;
require_once dirname(__DIR__, 2) . '/Models/SettingsModel.php';
App_Admin_UI::enqueue_media_uploader();
global $wpdb;
$slides = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tt_gallery WHERE section = 'carousel' ORDER BY sort_order ASC", ARRAY_A);
?>
<div class="wrap">
<h2>Home Media / Carousel</h2>
<form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
	<?php wp_nonce_field( 'tt_save_settings' ); ?>
	<input type="hidden" name="action" value="tt_save_settings">
	<input type="hidden" name="return_page" value="<?php echo esc_attr($_GET['page']??''); ?>">
	<input type="hidden" name="return_subtab" value="<?php echo esc_attr($_GET['subtab']??''); ?>">
	<h3 style="border-bottom:1px solid #ddd;padding-bottom:6px;">⚙️ Carousel Settings</h3>
	<table class="form-table"><tbody>
		<?php echo App_Admin_UI::select_field('settings[home_media_autoplay]', 'Autoplay', ['true'=>'Yes — Autoplay','false'=>'No — Manual'], (string)(TT_Settings_Model::get('home_media_autoplay')??'true')); ?>
		<?php echo App_Admin_UI::number_field('settings[home_media_interval]', 'Slide Interval (ms)', (string)(TT_Settings_Model::get('home_media_interval')??'4000')); ?>
		<?php echo App_Admin_UI::text_field('settings[home_media_tag]', 'Section Tag Label', (string)(TT_Settings_Model::get('home_media_tag')??'')); ?>
	</tbody></table>
	<?php echo App_Admin_UI::submit_button('Save Carousel Settings'); ?>
</form>
<hr>
<h3>Existing Slides <span style="font-size:13px;font-weight:normal;">(<?php echo count($slides); ?> slides — add via Gallery → Carousel section)</span></h3>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;margin-top:12px;">
<?php foreach ($slides as $slide): ?>
<div style="border:1px solid #ddd;border-radius:6px;overflow:hidden;background:#fafafa;">
	<?php if ($slide['image_url']): ?><img src="<?php echo esc_attr($slide['image_url']); ?>" style="width:100%;height:100px;object-fit:cover;"><?php endif; ?>
	<div style="padding:8px;font-size:12px;color:#444;"><?php echo esc_html($slide['alt'] ?: 'Slide '.($slide['sort_order']+1)); ?></div>
</div>
<?php endforeach; ?>
<?php if (empty($slides)): ?><p>No slides yet. Go to <strong>Global Content → Gallery</strong> and add images with section = "carousel".</p><?php endif; ?>
</div>
</div>