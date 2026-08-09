<?php
/**
 * TT Admin: Admin Shortcuts
 */
defined( 'ABSPATH' ) || exit;

?>

<div class="wrap">
    
    <?php echo App_Admin_UI::card_start('Existing Admin Shortcuts'); ?>
    <?php $items = TT_Admin_Shortcuts_Model::get_all(); ?>
    <?php if ( empty($items) ) : ?>
        <p>No items found.</p>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Title</th>
                            <th>Target URL</th>
                            <th>Dashicon Class</th>
                            <th>Sort Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $items as $row ) : ?>
                    <tr>
                        <td><?php echo esc_html( $row['title'] ); ?></td>
                                <td><?php echo esc_html( $row['url'] ); ?></td>
                                <td><?php echo esc_html( $row['icon'] ); ?></td>
                                <td><?php echo esc_html( $row['sort_order'] ); ?></td>
                        
                        <td>
                            <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this?');">
                                <input type="hidden" name="action" value="tt_update_shortcuts">
                                <input type="hidden" name="id" value="<?php echo esc_attr( $row['id'] ); ?>">
                                <input type="hidden" name="do_action" value="delete">
                                <?php wp_nonce_field( 'tt_update_shortcuts' ); ?>
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
        <input type="hidden" name="action" value="tt_update_shortcuts">
        <input type="hidden" name="do_action" value="create">
        <?php wp_nonce_field( 'tt_update_shortcuts' ); ?>
        <table class="form-table">
            <?php echo App_Admin_UI::text_field('title', 'Title', '', true); ?>
                <?php echo App_Admin_UI::text_field('url', 'Target URL', '', true); ?>
                <?php echo App_Admin_UI::text_field('icon', 'Dashicon Class', '', false); ?>
                <?php echo App_Admin_UI::number_field('sort_order', 'Sort Order', '0'); ?>
        </table>
        <?php echo App_Admin_UI::submit_button('Add New'); ?>
    </form>
    <?php echo App_Admin_UI::card_end(); ?>
    
</div>
