<?php
/**
 * TT Admin: Spotlights Settings
 */
defined( 'ABSPATH' ) || exit;
App_Admin_UI::enqueue_media_uploader();
?>

<div class="wrap">
    
    <?php echo App_Admin_UI::card_start('Existing Spotlights Settings'); ?>
    <?php $items = TT_Spotlights_Model::get_all(); ?>
    <?php if ( empty($items) ) : ?>
        <p>No items found.</p>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Title</th>
                            <th>Description</th>
                            <th>Image</th>
                            <th>Link URL</th>
                            <th>Link Text</th>
                            <th>Sort Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $items as $row ) : ?>
                    <tr>
                        <td><?php echo esc_html( $row['title'] ); ?></td>
                                <td><?php echo esc_html( $row['description'] ); ?></td>
                                <td><?php echo esc_html( $row['image_url'] ); ?></td>
                                <td><?php echo esc_html( $row['link_url'] ); ?></td>
                                <td><?php echo esc_html( $row['link_text'] ); ?></td>
                                <td><?php echo esc_html( $row['sort_order'] ); ?></td>
                        
                        <td>
                            <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this?');">
                                <input type="hidden" name="action" value="tt_update_spotlights">
                                <input type="hidden" name="id" value="<?php echo esc_attr( $row['id'] ); ?>">
                                <input type="hidden" name="do_action" value="delete">
                                <?php wp_nonce_field( 'tt_update_spotlights' ); ?>
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
        <input type="hidden" name="action" value="tt_update_spotlights">
        <input type="hidden" name="do_action" value="create">
        <?php wp_nonce_field( 'tt_update_spotlights' ); ?>
        <table class="form-table">
            <?php echo App_Admin_UI::text_field('title', 'Title', '', true); ?>
                <?php echo App_Admin_UI::textarea_field('description', 'Description', ''); ?>
                <?php echo App_Admin_UI::media_field('image_url', 'Image', ''); ?>
                <?php echo App_Admin_UI::text_field('link_url', 'Link URL', '', false); ?>
                <?php echo App_Admin_UI::text_field('link_text', 'Link Text', '', false); ?>
                <?php echo App_Admin_UI::number_field('sort_order', 'Sort Order', '0'); ?>
        </table>
        <?php echo App_Admin_UI::submit_button('Add New'); ?>
    </form>
    <?php echo App_Admin_UI::card_end(); ?>
    
</div>
