<?php
/**
 * templates/admin/partials/notice.php - reusable dismissible admin notice.
 *
 * @var string $message
 * @var string $type 'success' | 'error' | 'warning' | 'info'
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
	<p><?php echo esc_html( $message ); ?></p>
</div>
