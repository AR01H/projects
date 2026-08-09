<?php
defined( 'ABSPATH' ) || exit;
global $wpdb;
$items = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tt_opening_hours ORDER BY sort_order ASC", ARRAY_A);
?>
<div class="wrap"><h2>Opening Hours</h2>
<table class="wp-list-table widefat striped">
<thead><tr><th>Day</th><th>Open</th><th>Close</th><th>Closed?</th></tr></thead>
<tbody>
<?php foreach ($items as $i): ?><tr><td><?php echo esc_html($i['day_label']); ?></td><td><?php echo esc_html($i['open_time']); ?></td><td><?php echo esc_html($i['close_time']); ?></td><td><?php echo $i['is_closed']?'Closed':''; ?></td></tr><?php endforeach; ?>
<?php if(empty($items)): ?><tr><td colspan="4">No hours. Run the migrator.</td></tr><?php endif; ?>
</tbody></table></div>