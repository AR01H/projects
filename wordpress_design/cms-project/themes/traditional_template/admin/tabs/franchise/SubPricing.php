<?php
defined( 'ABSPATH' ) || exit;
global $wpdb;
$tiers = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tt_pricing_tiers ORDER BY sort_order ASC", ARRAY_A);
?>
<div class="wrap">
<h2>Pricing Tiers</h2>
<table class="wp-list-table widefat striped">
<thead><tr><th>Name</th><th>Price</th><th>Featured?</th></tr></thead>
<tbody>
<?php foreach ($tiers as $t): ?>
<tr><td><?php echo esc_html($t['name']); ?></td><td><?php echo esc_html($t['price']); ?></td><td><?php echo $t['is_featured'] ? '✅' : ''; ?></td></tr>
<?php endforeach; if (empty($tiers)): ?><tr><td colspan="3">No tiers found. Run the migrator.</td></tr><?php endif; ?>
</tbody>
</table>
</div>