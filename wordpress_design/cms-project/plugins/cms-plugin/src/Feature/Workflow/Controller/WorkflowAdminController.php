<?php
namespace Ah\Cms\Feature\Workflow\Controller;

defined( 'ABSPATH' ) || exit;

class WorkflowAdminController {

	public static function render(): void {
		if ( ! \current_user_can( 'manage_options' ) ) {
			\wp_die( 'Access denied.' );
		}

		$repo = new \Ah\Cms\Feature\Workflow\Repository\RulesRepository();
		$rules = $repo->findAll();

		?>
		<div class="wrap">
			<h1>Workflow Manager</h1>
			<p>Create and manage automation rules for your CMS.</p>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th>ID</th>
						<th>Name</th>
						<th>Status</th>
						<th>Trigger</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $rules ) ): ?>
						<tr>
							<td colspan="5">No rules found. Create your first automation rule.</td>
						</tr>
					<?php else: ?>
						<?php foreach ( $rules as $rule ): ?>
							<tr>
								<td><?php echo esc_html( $rule->id ); ?></td>
								<td><?php echo esc_html( $rule->name ); ?></td>
								<td><?php echo esc_html( $rule->status ); ?></td>
								<td><?php echo esc_html( $rule->trigger_type ); ?></td>
								<td>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-workflow&edit=' . $rule->id ) ); ?>">Edit</a> |
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-workflow&delete=' . $rule->id ) ); ?>" onclick="return confirm('Are you sure?');">Delete</a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>

			<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-workflow&edit=new' ) ); ?>" class="button button-primary">Add New Rule</a></p>
		</div>
		<?php
	}
}
