<?php
defined( 'ABSPATH' ) || exit;
require_once dirname(__DIR__, 2) . '/Models/FaqModel.php';

$action = isset($_GET['action']) ? sanitize_key($_GET['action']) : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($action === 'edit' || $action === 'add'): 
	$item = $id > 0 ? TT_Faq_Model::get($id) : null;
?>
	<div class="tt-card" style="background:#fff;padding:20px;border:1px solid #ccc;">
		<h3><?php echo $id > 0 ? 'Edit FAQ' : 'Add FAQ'; ?></h3>
		<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
			<?php wp_nonce_field('tt_save_faq'); ?>
			<input type="hidden" name="action" value="tt_save_faq">
			<input type="hidden" name="id" value="<?php echo $id; ?>">
			<table class="form-table">
				<tr>
					<th>Question</th>
					<td><input type="text" name="question" class="regular-text" value="<?php echo esc_attr($item['question'] ?? ''); ?>" required></td>
				</tr>
				<tr>
					<th>Answer</th>
					<td><textarea name="answer" rows="5" class="large-text" required><?php echo esc_textarea($item['answer'] ?? ''); ?></textarea></td>
				</tr>
				<tr>
					<th>Sort Order</th>
					<td><input type="number" name="sort_order" value="<?php echo esc_attr($item['sort_order'] ?? '0'); ?>"></td>
				</tr>
				<tr>
					<th>Status</th>
					<td>
						<select name="status">
							<option value="active" <?php selected($item['status'] ?? 'active', 'active'); ?>>Active</option>
							<option value="inactive" <?php selected($item['status'] ?? '', 'inactive'); ?>>Inactive</option>
						</select>
					</td>
				</tr>
			</table>
			<p class="submit">
				<button type="submit" class="button button-primary">Save FAQ</button>
				<a href="<?php echo esc_url(add_query_arg(['page'=>'tt-admin-content', 'subtab'=>'faqs'], admin_url('admin.php'))); ?>" class="button button-secondary">Cancel</a>
			</p>
		</form>
	</div>
<?php else: 
	$items = TT_Faq_Model::get_all();
?>
	<div class="tt-toolbar" style="margin-bottom:15px;">
		<a href="<?php echo esc_url(add_query_arg(['action'=>'add'], $_SERVER['REQUEST_URI'])); ?>" class="button button-primary">Add New FAQ</a>
	</div>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th>Question</th>
				<th>Order</th>
				<th>Status</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if (empty($items)): ?>
				<tr><td colspan="4">No FAQs found.</td></tr>
			<?php else: foreach ($items as $item): ?>
				<tr>
					<td><?php echo esc_html($item['question']); ?></td>
					<td><?php echo intval($item['sort_order']); ?></td>
					<td><?php echo esc_html(ucfirst($item['status'])); ?></td>
					<td>
						<a href="<?php echo esc_url(add_query_arg(['action'=>'edit', 'id'=>$item['id']], $_SERVER['REQUEST_URI'])); ?>">Edit</a> | 
						<form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;">
							<?php wp_nonce_field('tt_delete_faq'); ?>
							<input type="hidden" name="action" value="tt_delete_faq">
							<input type="hidden" name="id" value="<?php echo $item['id']; ?>">
							<button type="submit" class="button-link-delete" style="color:#b32d2e;text-decoration:none;border:none;background:none;cursor:pointer;" onclick="return confirm('Are you sure?');">Delete</button>
						</form>
					</td>
				</tr>
			<?php endforeach; endif; ?>
		</tbody>
	</table>
<?php endif; ?>
