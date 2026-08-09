<?php
defined( 'ABSPATH' ) || exit;
global $wpdb;
$forms = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tt_forms ORDER BY id ASC", ARRAY_A);
$edit_id = sanitize_text_field($_GET['edit'] ?? '');
$edit_item = null;
if ($edit_id) {
	$edit_item = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tt_forms WHERE id=%s", $edit_id), ARRAY_A);
}
?>
<div class="wrap">
<?php if ($edit_item): ?>
	<h2>Edit Form: <?php echo esc_html($edit_item['id']); ?></h2>
	<a href="?page=tt-forms&subtab=list" class="button mb-4">&larr; Back to Forms</a>
	
	<form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" style="margin-top:20px; background:#fff; padding:20px; border:1px solid #ddd;">
		<?php wp_nonce_field( 'tt_save_form' ); ?>
		<input type="hidden" name="action" value="tt_save_form">
		<input type="hidden" name="form_id" value="<?php echo esc_attr($edit_item['id']); ?>">
		<table class="form-table"><tbody>
			<?php echo App_Admin_UI::text_field('form_label', 'Form Label / Title', $edit_item['form_label']); ?>
			<?php echo App_Admin_UI::text_field('submit_text', 'Submit Button Text', $edit_item['submit_text']); ?>
		</tbody></table>
		<?php echo App_Admin_UI::submit_button('Save Form Settings'); ?>
	</form>

	<hr style="margin:40px 0;">
	<h2>Form Fields</h2>
	<?php
	$fields = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tt_form_fields WHERE form_id=%s ORDER BY step_id ASC, sort_order ASC", $edit_id), ARRAY_A);
	$edit_field_id = intval($_GET['edit_field'] ?? 0);
	$edit_field = null;
	if ($edit_field_id) {
		$edit_field = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tt_form_fields WHERE id=%d", $edit_field_id), ARRAY_A);
	}
	?>
	<form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" style="margin-top:20px; background:#f9f9f9; padding:20px; border:1px solid #ccc;">
		<h3><?php echo $edit_field ? 'Edit Field' : 'Add New Field'; ?></h3>
		<?php wp_nonce_field( 'tt_save_form_field' ); ?>
		<input type="hidden" name="action" value="tt_save_form_field">
		<input type="hidden" name="id" value="<?php echo $edit_field_id; ?>">
		<input type="hidden" name="form_id" value="<?php echo esc_attr($edit_item['id']); ?>">
		<table class="form-table"><tbody>
			<?php echo App_Admin_UI::text_field('name', 'Field Name (DB key)', $edit_field ? $edit_field['name'] : '', true); ?>
			<?php echo App_Admin_UI::text_field('field_id', 'HTML ID', $edit_field ? $edit_field['field_id'] : '', true); ?>
			<?php echo App_Admin_UI::select_field('type', 'Field Type', [
				'text' => 'Text', 'email' => 'Email', 'tel' => 'Phone', 'textarea' => 'Textarea', 'select' => 'Select / Dropdown', 'checkbox' => 'Checkbox'
			], $edit_field ? $edit_field['type'] : 'text'); ?>
			<?php echo App_Admin_UI::text_field('label', 'Label', $edit_field ? $edit_field['label'] : ''); ?>
			<?php echo App_Admin_UI::text_field('placeholder', 'Placeholder', $edit_field ? $edit_field['placeholder'] : ''); ?>
			<?php echo App_Admin_UI::textarea_field('options', 'Options (JSON array of {value, label})', $edit_field ? $edit_field['options'] : ''); ?>
			<tr><th>Settings</th><td>
				<label><input type="checkbox" name="is_required" value="1" <?php checked(1, $edit_field ? $edit_field['is_required'] : 0); ?>> Required Field</label><br>
				<label><input type="checkbox" name="is_multi_select" value="1" <?php checked(1, $edit_field ? $edit_field['is_multi_select'] : 0); ?>> Allow Multiple Selections (for select/checkboxes)</label>
			</td></tr>
			<?php echo App_Admin_UI::select_field('width', 'Width', ['full' => 'Full Width', 'half' => 'Half Width'], $edit_field ? $edit_field['width'] : 'full'); ?>
			<?php echo App_Admin_UI::number_field('step_id', 'Step ID (0 if no steps)', $edit_field ? $edit_field['step_id'] : 0); ?>
			<?php echo App_Admin_UI::number_field('sort_order', 'Sort Order', $edit_field ? $edit_field['sort_order'] : 0); ?>
		</tbody></table>
		<?php echo App_Admin_UI::submit_button('Save Field'); ?>
		<?php if ($edit_field_id): ?><a href="?page=tt-forms&subtab=list&edit=<?php echo urlencode($edit_item['id']); ?>" class="button">Cancel</a><?php endif; ?>
	</form>

	<table class="wp-list-table widefat fixed striped" style="margin-top:20px;">
		<thead><tr><th>Step</th><th>Name</th><th>Label</th><th>Type</th><th>Multi-Select</th><th>Sort</th><th>Actions</th></tr></thead>
		<tbody>
		<?php foreach ($fields as $field): ?>
		<tr>
			<td><?php echo $field['step_id']; ?></td>
			<td><strong><?php echo esc_html($field['name']); ?></strong></td>
			<td><?php echo esc_html($field['label']); ?></td>
			<td><?php echo esc_html($field['type']); ?></td>
			<td><?php echo $field['is_multi_select'] ? '✅ Yes' : '❌ No'; ?></td>
			<td><?php echo $field['sort_order']; ?></td>
			<td>
				<a href="?page=tt-forms&subtab=list&edit=<?php echo urlencode($edit_item['id']); ?>&edit_field=<?php echo $field['id']; ?>">Edit</a> | 
				<a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=tt_delete_form_field&id=' . $field['id'] . '&form_id=' . urlencode($edit_item['id'])), 'tt_delete_form_field'); ?>" style="color:#c00;" onclick="return confirm('Delete this field?');">Delete</a>
			</td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

<?php else: ?>
	<h2>Forms</h2>
	<table class="wp-list-table widefat fixed striped">
		<thead><tr><th>Form ID</th><th>Label</th><th>Submit Text</th><th>Actions</th></tr></thead>
		<tbody>
		<?php foreach ($forms as $form): ?>
		<tr>
			<td><strong><?php echo esc_html($form['id']); ?></strong></td>
			<td><?php echo esc_html($form['form_label']); ?></td>
			<td><?php echo esc_html($form['submit_text']); ?></td>
			<td><a href="?page=tt-forms&subtab=list&edit=<?php echo urlencode($form['id']); ?>" class="button button-small">Manage Fields</a></td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>
</div>
