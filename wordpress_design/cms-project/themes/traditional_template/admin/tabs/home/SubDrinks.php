<?php
defined( 'ABSPATH' ) || exit;
global $wpdb;
App_Admin_UI::enqueue_media_uploader();
$table = $wpdb->prefix . 'tt_drinks';
$items = $wpdb->get_results("SELECT * FROM {$table} ORDER BY sort_order ASC", ARRAY_A);
$edit_id   = intval($_GET['edit'] ?? 0);
$edit_item = $edit_id ? $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id=%d",$edit_id), ARRAY_A) : null;
?>
<div class="wrap">
<h2><?php echo $edit_id ? 'Edit Drink' : 'Add New Drink'; ?></h2>
<form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
	<?php wp_nonce_field( 'tt_save_drink' ); ?>
	<input type="hidden" name="action" value="tt_save_drink">
	<input type="hidden" name="id" value="<?php echo $edit_id; ?>">
	<input type="hidden" name="return_page" value="<?php echo esc_attr($_GET['page']??''); ?>">
	<input type="hidden" name="return_subtab" value="<?php echo esc_attr($_GET['subtab']??''); ?>">
	<table class="form-table"><tbody>
		<?php echo App_Admin_UI::text_field('name', 'Drink Name', $edit_item ? (string)($edit_item['name']??'') : '', true); ?>
		<?php echo App_Admin_UI::textarea_field('description', 'Description', $edit_item ? (string)($edit_item['description']??'') : ''); ?>
		<?php echo App_Admin_UI::media_field('image_url', 'Image', $edit_item ? (string)($edit_item['image_url']??'') : ''); ?>
		<?php echo App_Admin_UI::number_field('sort_order', 'Sort Order', $edit_item ? (string)($edit_item['sort_order']??'0') : '0'); ?>
	</tbody></table>
	<?php echo App_Admin_UI::submit_button('Save Drink'); ?>
	<?php if ($edit_id): ?><a href="?page=<?php echo esc_attr($_GET['page']??''); ?>&subtab=<?php echo esc_attr($_GET['subtab']??''); ?>" class="button" style="margin-left:8px;">Cancel</a><?php endif; ?>
</form>
<hr>
<h2>All Drinks</h2>
<table class="wp-list-table widefat fixed striped">
	<thead><tr><th>Name</th><th>Description</th><th>Image</th><th>Actions</th></tr></thead>
	<tbody>
	<?php foreach ($items as $item): ?>
	<tr>
		<td><?php echo esc_html($item['name']); ?></td><td><?php echo esc_html($item['description']); ?></td><td><?php echo esc_html($item['image_url']); ?></td>
		<td>
			<a href="?page=<?php echo esc_attr($_GET['page']??''); ?>&subtab=<?php echo esc_attr($_GET['subtab']??''); ?>&edit=<?php echo $item['id']; ?>">✏️ Edit</a> |
			<a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=tt_delete_drink&id='.$item['id']), 'tt_delete_drink'); ?>" style="color:#c00;" onclick="return confirm('Delete this Drink?');">🗑️ Delete</a>
		</td>
	</tr>
	<?php endforeach; ?>
	<?php if (empty($items)): ?><tr><td colspan="4" style="text-align:center;padding:20px;">No items yet. Add one above.</td></tr><?php endif; ?>
	</tbody>
</table>
</div>