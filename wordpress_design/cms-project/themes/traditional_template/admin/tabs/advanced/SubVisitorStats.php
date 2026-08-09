<?php
/**
 * TT Admin: Visitor Stats (Read Only)
 */
defined( 'ABSPATH' ) || exit;

?>

<div class="wrap">
    
    <?php echo App_Admin_UI::card_start('Existing Visitor Stats (Read Only)'); ?>
    <?php $items = TT_VisitorStats_Model::get_all(); ?>
    <?php if ( empty($items) ) : ?>
        <p>No items found.</p>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>URL Accessed</th>
                            <th>IP Hash</th>
                            <th>User Agent</th>
                            <th>Timestamp</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $items as $row ) : ?>
                    <tr>
                        <td><?php echo esc_html( $row['url'] ); ?></td>
                                <td><?php echo esc_html( $row['ip_hash'] ); ?></td>
                                <td><?php echo esc_html( $row['user_agent'] ); ?></td>
                                <td><?php echo esc_html( $row['created_at'] ); ?></td>
                        
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <?php echo App_Admin_UI::card_end(); ?>
    
    
</div>
