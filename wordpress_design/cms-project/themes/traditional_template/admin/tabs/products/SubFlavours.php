<?php
defined( 'ABSPATH' ) || exit;
global $wpdb;
$items = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tt_flavours ORDER BY category, sort_order ASC", ARRAY_A);
$grouped = [];
foreach ($items as $i) $grouped[$i['category']][] = $i;
?>
<div class="wrap">
<h2>Flavours</h2>
<?php foreach ($grouped as $cat => $flavours): ?>
<h3><?php echo esc_html(ucfirst($cat)); ?></h3>
<table class="wp-list-table widefat striped">
<thead><tr><th>Name</th></tr></thead>
<tbody>
<?php foreach ($flavours as $f): ?><tr><td><?php echo esc_html($f['name']); ?></td></tr><?php endforeach; ?>
</tbody>
</table>
<?php endforeach; if (empty($grouped)): ?><p>No flavours. Run the migrator.</p><?php endif; ?>
</div>