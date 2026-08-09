<?php
defined( 'ABSPATH' ) || exit;
require_once dirname(__DIR__, 2) . '/Models/BaseModel.php';
global $wpdb;
$items = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tt_hire_packages ORDER BY sort_order ASC", ARRAY_A);
?>
<div class="wrap">
<h2>Hire Packages</h2>
<table class="wp-list-table widefat striped">
<thead><tr><th>Name</th><th>Price</th><th>Description</th></tr></thead>
<tbody>
<?php foreach ($items as $item): ?>
<tr><td><?php echo esc_html($item['name']); ?></td><td><?php echo esc_html($item['price']); ?></td><td><?php echo esc_html($item['description']); ?></td></tr>
<?php endforeach; if (empty($items)): ?><tr><td colspan="3">No packages found. Run the migrator.</td></tr><?php endif; ?>
</tbody>
</table>
</div>