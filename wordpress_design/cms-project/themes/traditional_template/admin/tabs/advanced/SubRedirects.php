<?php
/**
 * TT Admin: Redirect Rules
 */
defined( 'ABSPATH' ) || exit;

?>

<div class="wrap">
    
    <?php echo App_Admin_UI::card_start('Existing Redirect Rules'); ?>
    <?php $items = TT_Redirects_Model::get_all(); ?>
    <?php if ( empty($items) ) : ?>
        <p>No items found.</p>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Source URL (Relative)</th>
                            <th>Target URL</th>
                            <th>Status Code</th>
                            <th>Is Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $items as $row ) : ?>
                    <tr>
                        <td><?php echo esc_html( $row['source_url'] ); ?></td>
                                <td><?php echo esc_html( $row['target_url'] ); ?></td>
                                <td><?php echo esc_html( $row['status_code'] ); ?></td>
                                <td><?php echo esc_html( $row['is_active'] ); ?></td>
                        
                        <td>
                            <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this?');">
                                <input type="hidden" name="action" value="tt_update_redirects">
                                <input type="hidden" name="id" value="<?php echo esc_attr( $row['id'] ); ?>">
                                <input type="hidden" name="do_action" value="delete">
                                <?php wp_nonce_field( 'tt_update_redirects' ); ?>
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
        <input type="hidden" name="action" value="tt_update_redirects">
        <input type="hidden" name="do_action" value="create">
        <?php wp_nonce_field( 'tt_update_redirects' ); ?>
        <table class="form-table">
            <?php echo App_Admin_UI::text_field('source_url', 'Source URL (Relative)', '', true); ?>
                <?php echo App_Admin_UI::text_field('target_url', 'Target URL', '', true); ?>
                <?php echo App_Admin_UI::number_field('status_code', 'Status Code', '0'); ?>
                <?php echo App_Admin_UI::select_field('is_active', 'Is Active', array('1' => 'Yes', '0' => 'No'), '1'); ?>
        </table>
        <?php echo App_Admin_UI::submit_button('Add New'); ?>
    </form>
    <?php echo App_Admin_UI::card_end(); ?>
    
</div>
