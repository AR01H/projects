<?php
defined( 'ABSPATH' ) || exit;
require_once dirname(__DIR__, 2) . '/Models/NavModel.php';
$items = TT_Nav_Model::get_all();
$edit_id = intval($_GET['edit'] ?? 0);
$edit_item = $edit_id ? TT_Nav_Model::get_by_id($edit_id) : null;
?>
<div class="wrap">
	<h2>Navigation Links</h2>
	<form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
		<?php wp_nonce_field( 'tt_save_nav' ); ?>
		<input type="hidden" name="action" value="tt_save_nav">
		<input type="hidden" name="id" value="<?php echo $edit_id; ?>">
		<table class="form-table"><tbody>
			<?php echo App_Admin_UI::text_field('label', 'Label', $edit_item['label']??'', true); ?>
			<?php echo App_Admin_UI::text_field('url', 'URL', $edit_item['url']??''); ?>
			<?php echo App_Admin_UI::number_field('sort_order', 'Sort Order', $edit_item['sort_order']??'0'); ?>
		</tbody></table>
		<?php echo App_Admin_UI::submit_button('Save Link'); ?>
	</form>
	<hr>
	<table class="wp-list-table widefat striped">
		<thead><tr><th>Label</th><th>URL</th><th>Sort</th><th>Actions</th></tr></thead>
		<tbody>
			<?php foreach ($items as $item): ?>
			<tr>
				<td><?php echo esc_html($item['label']); ?></td>
				<td><?php echo esc_html($item['url']); ?></td>
				<td><?php echo esc_html($item['sort_order']); ?></td>
				<td>
					<a href="?page=tt-admin-settings&subtab=nav&edit=<?php echo $item['id']; ?>">Edit</a> | 
					<a href="<?php echo wp_nonce_url( admin_url('admin-post.php?action=tt_delete_nav&id='.$item['id']), 'tt_delete_nav' ); ?>" style="color:red;">Delete</a>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
