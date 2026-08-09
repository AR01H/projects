<?php
defined( 'ABSPATH' ) || exit;
global $wpdb;
$items = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tt_stats", ARRAY_A);
?>
<div class="wrap"><h2>Site Stats</h2>
<table class="wp-list-table widefat striped">
<thead><tr><th>Key</th><th>Value</th><th>Label</th></tr></thead>
<tbody>
<?php foreach ($items as $i): ?><tr><td><?php echo esc_html($i['stat_key']); ?></td><td><strong><?php echo esc_html($i['stat_value']); ?></strong></td><td><?php echo esc_html($i['label']); ?></td></tr><?php endforeach; ?>
<?php if(empty($items)): ?><tr><td colspan="3">No stats. Run the migrator.</td></tr><?php endif; ?>
</tbody></table></div>