<?php
defined( 'ABSPATH' ) || exit;
require_once dirname(__DIR__, 2) . '/Models/TeamModel.php';

$action = isset($_GET['action']) ? sanitize_key($_GET['action']) : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($action === 'edit' || $action === 'add'): 
	$item = $id > 0 ? TT_Team_Model::get($id) : null;
?>
	<div class="tt-card" style="background:#fff;padding:20px;border:1px solid #ccc;">
		<h3><?php echo $id > 0 ? 'Edit Team' : 'Add Team'; ?></h3>
		<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
			<?php wp_nonce_field('tt_save_team'); ?>
			<input type="hidden" name="action" value="tt_save_team">
			<input type="hidden" name="id" value="<?php echo $id; ?>">
			<table class="form-table">
				<tr>
					<th>Name</th>
					<td><input type="text" name="name" class="regular-text" value="<?php echo esc_attr($item['name'] ?? ''); ?>"></td>
				</tr>
				<tr>
					<th>Role</th>
					<td><input type="text" name="role" class="regular-text" value="<?php echo esc_attr($item['role'] ?? ''); ?>"></td>
				</tr>
				<tr>
					<th>Bio</th>
					<td><textarea name="bio" rows="5" class="large-text"><?php echo esc_textarea($item['bio'] ?? ''); ?></textarea></td>
				</tr>
				<tr>
					<th>Image Url</th>
					<td><input type="text" name="image_url" class="regular-text" value="<?php echo esc_attr($item['image_url'] ?? ''); ?>"></td>
				</tr>
				<tr>
					<th>Sort Order</th>
					<td><input type="number" name="sort_order" value="<?php echo esc_attr($item['sort_order'] ?? '0'); ?>"></td>
				</tr>

			</table>
			<p class="submit">
				<button type="submit" class="button button-primary">Save Team</button>
				<a href="<?php echo esc_url(add_query_arg(['page'=>'tt-admin-content', 'subtab'=>'teams'], admin_url('admin.php'))); ?>" class="button button-secondary">Cancel</a>
			</p>
		</form>
	</div>
<?php else: 
	$items = TT_Team_Model::get_all();
?>
	<div class="tt-toolbar" style="margin-bottom:15px;">
		<a href="<?php echo esc_url(add_query_arg(['action'=>'add'], $_SERVER['REQUEST_URI'])); ?>" class="button button-primary">Add New Team</a>
	</div>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
<th>Name</th>
<th>Role</th>
<th>Bio</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if (empty($items)): ?>
				<tr><td colspan="4">No records found.</td></tr>
			<?php else: foreach ($items as $item): ?>
				<tr>
<td><?php echo esc_html($item['name']); ?></td>
<td><?php echo esc_html($item['role']); ?></td>
<td><?php echo esc_html($item['bio']); ?></td>
					<td>
						<a href="<?php echo esc_url(add_query_arg(['action'=>'edit', 'id'=>$item['id']], $_SERVER['REQUEST_URI'])); ?>">Edit</a> | 
						<form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;">
							<?php wp_nonce_field('tt_delete_team'); ?>
							<input type="hidden" name="action" value="tt_delete_team">
							<input type="hidden" name="id" value="<?php echo $item['id']; ?>">
							<button type="submit" class="button-link-delete" style="color:#b32d2e;text-decoration:none;border:none;background:none;cursor:pointer;" onclick="return confirm('Are you sure?');">Delete</button>
						</form>
					</td>
				</tr>
			<?php endforeach; endif; ?>
		</tbody>
	</table>
<?php endif; ?>
