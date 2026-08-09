<?php
/**
 * TT Admin: Utilities / Admin Actions
 */
defined( 'ABSPATH' ) || exit;
?>

<div class="wrap">
    <h2><?php echo esc_html( 'Admin Actions' ); ?></h2>
    <p>Quick utilities for maintenance, testing, and diagnostics.</p>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:20px;margin-top:20px;">
        
        <?php
        $actions = array(
            array(
                'id' => 'flush_rewrite',
                'icon' => 'dashicons-update',
                'title' => 'Flush Rewrite Rules',
                'desc' => 'Regenerate WordPress permalink rules. Run this after adding new page slugs or custom post types.',
                'btn' => 'Run',
                'color' => 'primary',
                'danger' => false
            ),
            array(
                'id' => 'clear_cache',
                'icon' => 'dashicons-trash',
                'title' => 'Clear Cache',
                'desc' => 'Delete all WordPress transient cache entries from the database. Useful when stale cached data causes issues.',
                'btn' => 'Run',
                'color' => 'primary',
                'danger' => false
            ),
            array(
                'id' => 'db_health',
                'icon' => 'dashicons-database',
                'title' => 'DB Health Check',
                'desc' => 'Verify that all required `tt_` tables exist in the database and report any that are missing.',
                'btn' => 'Run',
                'color' => 'primary',
                'danger' => false
            ),
            array(
                'id' => 'clear_stats',
                'icon' => 'dashicons-chart-area',
                'title' => 'Clear Visitor Stats',
                'desc' => 'Truncate the visitor stats table. All recorded entries will be permanently removed.',
                'btn' => 'Run',
                'color' => 'error',
                'danger' => true
            ),
            array(
                'id' => 'clear_workflows',
                'icon' => 'dashicons-clipboard',
                'title' => 'Clear Workflows',
                'desc' => 'Truncate the workflows table. All recorded tasks will be permanently removed.',
                'btn' => 'Run',
                'color' => 'error',
                'danger' => true
            )
        );

        foreach ( $actions as $a ) :
            $btnClass = $a['danger'] ? 'button-link-delete' : 'button-primary';
            $border = $a['danger'] ? 'border-top: 3px solid #d63638;' : 'border-top: 3px solid #2271b1;';
        ?>
            <div class="postbox" style="padding:20px;margin:0;<?php echo $border; ?>border-radius:4px;">
                <h3 style="margin:0 0 10px;padding:0;border:none;"><span class="dashicons <?php echo esc_attr($a['icon']); ?>" style="color:<?php echo $a['danger'] ? '#d63638' : '#2271b1'; ?>"></span> <?php echo esc_html($a['title']); ?></h3>
                <p style="color:#50575e;min-height:60px;margin-bottom:20px;"><?php echo esc_html($a['desc']); ?></p>
                <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" <?php if($a['danger']) echo 'onsubmit="return confirm(\'Are you sure you want to perform this dangerous action?\');"'; ?>>
                    <input type="hidden" name="action" value="tt_run_utility">
                    <input type="hidden" name="utility_id" value="<?php echo esc_attr($a['id']); ?>">
                    <?php wp_nonce_field( 'tt_run_utility' ); ?>
                    <button type="submit" class="button <?php echo esc_attr($btnClass); ?>" style="width:100%;text-align:center;justify-content:center;"><?php echo esc_html($a['btn']); ?></button>
                </form>
            </div>
        <?php endforeach; ?>

    </div>
</div>
