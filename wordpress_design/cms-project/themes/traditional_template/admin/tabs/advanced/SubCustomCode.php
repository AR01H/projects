<?php
/**
 * TT Admin: Custom Code Injection
 */
defined( 'ABSPATH' ) || exit;

?>

<div class="wrap">
    
    <?php echo App_Admin_UI::card_start('Existing Custom Code Injection'); ?>
    <?php $items = TT_CustomCode_Model::get_all(); ?>
    <?php if ( empty($items) ) : ?>
        <p>No items found.</p>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Internal Title</th>
                            <th>Code Snippet</th>
                            <th>Placement</th>
                            <th>Is Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $items as $row ) : ?>
                    <tr>
                        <td><?php echo esc_html( $row['title'] ); ?></td>
                                <td><?php echo esc_html( $row['code_snippet'] ); ?></td>
                                <td><?php echo esc_html( $row['placement'] ); ?></td>
                                <td><?php echo esc_html( $row['is_active'] ); ?></td>
                        
                        <td>
                            <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this?');">
                                <input type="hidden" name="action" value="tt_update_custom_code">
                                <input type="hidden" name="id" value="<?php echo esc_attr( $row['id'] ); ?>">
                                <input type="hidden" name="do_action" value="delete">
                                <?php wp_nonce_field( 'tt_update_custom_code' ); ?>
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
        <input type="hidden" name="action" value="tt_update_custom_code">
        <input type="hidden" name="do_action" value="create">
        <?php wp_nonce_field( 'tt_update_custom_code' ); ?>
        <table class="form-table">
            <?php echo App_Admin_UI::text_field('title', 'Internal Title', '', true); ?>
                <?php echo App_Admin_UI::textarea_field('code_snippet', 'Code Snippet', ''); ?>
                <?php echo App_Admin_UI::select_field('placement', 'Placement', array('head' => 'Header (<head>)', 'footer' => 'Footer (before </body>)'), 'head'); ?>
                <?php echo App_Admin_UI::select_field('is_active', 'Is Active', array('1' => 'Yes', '0' => 'No'), '1'); ?>
        </table>
        <?php echo App_Admin_UI::submit_button('Add New'); ?>
    </form>
    <?php echo App_Admin_UI::card_end(); ?>
    
</div>
