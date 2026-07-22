<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
<h2 style="color: #d97706;">Low Stock Alert</h2>
<p>The following product is running low on stock:</p>

<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
<tr style="background: #fef3c7;"><td style="padding: 10px; font-weight: bold;">Product</td><td style="padding: 10px;"><?php echo esc_html( $product->title ); ?></td></tr>
<tr><td style="padding: 10px; font-weight: bold;">SKU</td><td style="padding: 10px;"><?php echo esc_html( $product->sku ?? '-' ); ?></td></tr>
<tr style="background: #fef3c7;"><td style="padding: 10px; font-weight: bold;">Stock Remaining</td><td style="padding: 10px; color: #dc2626; font-weight: bold;"><?php echo esc_html( $product->stock_quantity ); ?></td></tr>
</table>

<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-products&action=edit&id=' . $product->id ) ); ?>">Edit Product</a></p>

<hr style="margin: 30px 0;">
<p style="color: #888; font-size: 12px;"><?php echo esc_html( get_bloginfo( 'name' ) ); ?> — Admin Notification</p>
</body>
</html>
