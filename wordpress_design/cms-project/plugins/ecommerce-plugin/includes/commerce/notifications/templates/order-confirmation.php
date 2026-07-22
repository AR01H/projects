<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
<h2 style="color: #333;">Order Confirmed!</h2>
<p>Thank you for your order. Here are your order details:</p>

<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
<tr style="background: #f7f7f7;">
<td style="padding: 10px; font-weight: bold;">Order ID</td>
<td style="padding: 10px;">#<?php echo esc_html( $order['id'] ); ?></td>
</tr>
<tr>
<td style="padding: 10px; font-weight: bold;">Status</td>
<td style="padding: 10px;"><?php echo esc_html( ucfirst( $order['status'] ) ); ?></td>
</tr>
<tr style="background: #f7f7f7;">
<td style="padding: 10px; font-weight: bold;">Payment</td>
<td style="padding: 10px;"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $order['payment_method'] ) ) ); ?></td>
</tr>
</table>

<h3>Items</h3>
<table style="width: 100%; border-collapse: collapse;">
<tr style="background: #f7f7f7;"><th style="padding: 8px; text-align: left;">Product</th><th style="padding: 8px;">Qty</th><th style="padding: 8px;">Price</th></tr>
<?php foreach ( $cart_items as $item ) : ?>
<tr><td style="padding: 8px;"><?php echo esc_html( $item['name'] ?? 'Product #' . $item['id'] ); ?></td><td style="padding: 8px; text-align: center;"><?php echo (int) $item['qty']; ?></td><td style="padding: 8px; text-align: right;">$<?php echo number_format( (float) $item['price'] * (int) $item['qty'], 2 ); ?></td></tr>
<?php endforeach; ?>
</table>

<p style="font-size: 18px; font-weight: bold; text-align: right; margin-top: 20px;">Total: $<?php echo number_format( (float) $order['total'], 2 ); ?></p>

<hr style="margin: 30px 0;">
<p style="color: #888; font-size: 12px;"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
</body>
</html>
