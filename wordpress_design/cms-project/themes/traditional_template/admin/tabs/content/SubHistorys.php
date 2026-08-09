<?php
defined( 'ABSPATH' ) || exit;
require_once dirname(__DIR__, 2) . '/Models/HistoryModel.php';

$action = isset($_GET['action']) ? sanitize_key($_GET['action']) : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($action === 'edit' || $action === 'add'): 
	$item = $id > 0 ? TT_History_Model::get($id) : null;
?>
	<div class="tt-card" style="background:#fff;padding:20px;border:1px solid #ccc;">
		<h3><?php echo $id > 0 ? 'Edit History' : 'Add History'; ?></h3>
		<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
			<?php wp_nonce_field('tt_save_history'); ?>
			<input type="hidden" name="action" value="tt_save_history">
			<input type="hidden" name="id" value="<?php echo $id; ?>">
			<table class="form-table">
				<tr>
					<th>Year</th>
					<td><input type="text" name="year" class="regular-text" value="<?php echo esc_attr($item['year'] ?? ''); ?>"></td>
				</tr>
				<tr>
					<th>Title</th>
					<td><input type="text" name="title" class="regular-text" value="<?php echo esc_attr($item['title'] ?? ''); ?>"></td>
				</tr>
				<tr>
					<th>Description</th>
					<td><textarea name="description" rows="5" class="large-text"><?php echo esc_textarea($item['description'] ?? ''); ?></textarea></td>
				</tr>
				<tr>
					<th>Sort Order</th>
					<td><input type="number" name="sort_order" value="<?php echo esc_attr($item['sort_order'] ?? '0'); ?>"></td>
				</tr>

			</table>
			<p class="submit">
				<button type="submit" class="button button-primary">Save History</button>
				<a href="<?php echo esc_url(add_query_arg(['page'=>'tt-admin-content', 'subtab'=>'historys'], admin_url('admin.php'))); ?>" class="button button-secondary">Cancel</a>
			</p>
		</form>
	</div>
<?php else: 
	$items = TT_History_Model::get_all();
?>
	<div class="tt-toolbar" style="margin-bottom:15px;">
		<a href="<?php echo esc_url(add_query_arg(['action'=>'add'], $_SERVER['REQUEST_URI'])); ?>" class="button button-primary">Add New History</a>
	</div>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
<th>Year</th>
<th>Title</th>
<th>Description</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if (empty($items)): ?>
				<tr><td colspan="4">No records found.</td></tr>
			<?php else: foreach ($items as $item): ?>
				<tr>
<td><?php echo esc_html($item['year']); ?></td>
<td><?php echo esc_html($item['title']); ?></td>
<td><?php echo esc_html($item['description']); ?></td>
					<td>
						<a href="<?php echo esc_url(add_query_arg(['action'=>'edit', 'id'=>$item['id']], $_SERVER['REQUEST_URI'])); ?>">Edit</a> | 
						<form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;">
							<?php wp_nonce_field('tt_delete_history'); ?>
							<input type="hidden" name="action" value="tt_delete_history">
							<input type="hidden" name="id" value="<?php echo $item['id']; ?>">
							<button type="submit" class="button-link-delete" style="color:#b32d2e;text-decoration:none;border:none;background:none;cursor:pointer;" onclick="return confirm('Are you sure?');">Delete</button>
						</form>
					</td>
				</tr>
			<?php endforeach; endif; ?>
		</tbody>
	</table>
<?php endif; ?>
