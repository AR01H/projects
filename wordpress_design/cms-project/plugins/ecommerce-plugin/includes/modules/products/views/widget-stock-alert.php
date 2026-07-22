<?php
/**
 * Widget: Stock Alert — "Notify me when back in stock"
 * Include this in product detail pages when stock is 0.
 * Usage: include plugin_dir_path(__FILE__) . '../views/widget-stock-alert.php';
 *
 * Expected variables: $product_id (int)
 */
defined( 'ABSPATH' ) || exit;
$product_id = (int) ( $product_id ?? 0 );
if ( ! $product_id ) return;
?>
<div id="ah-stock-alert-wrap" style="margin-top:15px; padding:15px; background:#fef3c7; border:1px solid #fbbf24; border-radius:8px;">
	<p style="margin:0 0 8px; font-weight:600; color:#92400e;">Out of Stock — Get notified when it's back!</p>
	<div style="display:flex; gap:8px;">
		<input type="email" id="ah-stock-alert-email" placeholder="Your email address" style="flex:1; padding:8px 12px; border:1px solid #fbbf24; border-radius:4px;">
		<button id="ah-stock-alert-btn" style="padding:8px 16px; background:#d97706; color:#fff; border:none; border-radius:4px; cursor:pointer; font-weight:600;">Notify Me</button>
	</div>
	<p id="ah-stock-alert-msg" style="margin:8px 0 0; font-size:13px; display:none;"></p>
</div>
<script>
jQuery(document).ready(function($) {
	$('#ah-stock-alert-btn').on('click', function() {
		var email = $('#ah-stock-alert-email').val().trim();
		if (!email) return;
		$.post('<?php echo admin_url('admin-ajax.php'); ?>', {
			action: 'ah_stock_alert_subscribe',
			product_id: <?php echo $product_id; ?>,
			email: email,
			nonce: '<?php echo wp_create_nonce("ah_cart_nonce"); ?>'
		}, function(r) {
			var $msg = $('#ah-stock-alert-msg').show();
			$msg.text(r.message).css('color', r.success ? '#166534' : '#991b1b');
			if (r.success) { $('#ah-stock-alert-email').val(''); }
		});
	});
});
</script>
