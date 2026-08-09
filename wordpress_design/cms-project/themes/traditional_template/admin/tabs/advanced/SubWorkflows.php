<?php
/**
 * TT Admin: Workflow Management
 */
defined( 'ABSPATH' ) || exit;

?>

<div class="wrap">
    
    <?php echo App_Admin_UI::card_start('Existing Workflow Management'); ?>
    <?php $items = TT_Workflows_Model::get_all(); ?>
    <?php if ( empty($items) ) : ?>
        <p>No items found.</p>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Task Name</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $items as $row ) : ?>
                    <tr>
                        <td><?php echo esc_html( $row['task_name'] ); ?></td>
                                <td><?php echo esc_html( $row['status'] ); ?></td>
                                <td><?php echo esc_html( $row['assigned_to'] ); ?></td>
                                <td><?php echo esc_html( $row['notes'] ); ?></td>
                        
                        <td>
                            <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this?');">
                                <input type="hidden" name="action" value="tt_update_workflows">
                                <input type="hidden" name="id" value="<?php echo esc_attr( $row['id'] ); ?>">
                                <input type="hidden" name="do_action" value="delete">
                                <?php wp_nonce_field( 'tt_update_workflows' ); ?>
                                <button type="submit" class="button button-link-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <?php echo App_Admin_UI::card_end(); ?>
    
    
    <?php echo App_Admin_UI::card_start('Add New'); ?>
    <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
        <input type="hidden" name="action" value="tt_update_workflows">
        <input type="hidden" name="do_action" value="create">
        <?php wp_nonce_field( 'tt_update_workflows' ); ?>
        <table class="form-table">
            <?php echo App_Admin_UI::text_field('task_name', 'Task Name', '', true); ?>
                <?php echo App_Admin_UI::text_field('status', 'Status', '', false); ?>
                <?php echo App_Admin_UI::text_field('assigned_to', 'Assigned To', '', false); ?>
                <?php echo App_Admin_UI::textarea_field('notes', 'Notes', ''); ?>
        </table>
        <?php echo App_Admin_UI::submit_button('Add New'); ?>
    </form>
    <?php echo App_Admin_UI::card_end(); ?>
    
</div>
