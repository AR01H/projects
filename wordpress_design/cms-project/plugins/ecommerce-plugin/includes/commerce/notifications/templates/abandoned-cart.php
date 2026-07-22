<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
<h2 style="color: #333;">You left items in your cart!</h2>
<p>We noticed you didn't complete your order. Here's what you left behind:</p>

<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
<tr style="background: #f7f7f7;"><th style="padding: 8px; text-align: left;">Product</th><th style="padding: 8px;">Qty</th><th style="padding: 8px;">Price</th></tr>
<?php foreach ( $cart_items as $item ) : ?>
<tr>
<td style="padding: 8px;"><?php echo esc_html( $item['name'] ?? 'Product #' . $item['id'] ); ?></td>
<td style="padding: 8px; text-align: center;"><?php echo (int) $item['qty']; ?></td>
<td style="padding: 8px; text-align: right;">$<?php echo number_format( (float) $item['price'] * (int) $item['qty'], 2 ); ?></td>
</tr>
<?php endforeach; ?>
</table>

<p style="font-size: 16px; font-weight: bold; text-align: right;">Cart Total: $<?php echo number_format( (float) $cart_total, 2 ); ?></p>

<p style="text-align: center; margin: 30px 0;">
<a href="<?php echo esc_url( home_url( '/checkout/' ) ); ?>" style="display: inline-block; padding: 14px 40px; background: #000; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold;">Complete Your Order</a>
</p>

<hr style="margin: 30px 0;">
<p style="color: #888; font-size: 12px;"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
</body>
</html>
