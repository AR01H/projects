<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use AHEcommerce\Commerce\Reviews\Review_Service;

$tab     = sanitize_key( $_GET['tab'] ?? 'pending' );
$notice  = '';

// Handle actions.
if ( isset( $_GET['action'] ) && isset( $_GET['review_id'] ) ) {
	check_admin_referer( 'ah_review_action_' . $_GET['review_id'] );
	$sub_action = sanitize_text_field( $_GET['action'] );
	$review_id  = (int) $_GET['review_id'];

	switch ( $sub_action ) {
		case 'approve': Review_Service::approve( $review_id ); $notice = 'Review approved.'; break;
		case 'reject':  Review_Service::reject( $review_id );  $notice = 'Review rejected.';  break;
		case 'delete':  Review_Service::delete_review( $review_id ); $notice = 'Review deleted.';  break;
	}
}
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-star-filled"></span> <?php esc_html_e( 'Product Reviews', 'ah-ecommerce' ); ?></h1>
	<?php if ( $notice ) : ?><div class="ah-notice ah-notice-success" style="padding:10px; background:#d4edda; color:#155724; border-left:4px solid #28a745; margin-bottom:20px;"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

	<h2 class="nav-tab-wrapper">
		<a href="?page=ah-reviews&tab=pending" class="nav-tab <?php echo $tab === 'pending' ? 'nav-tab-active' : ''; ?>">Pending</a>
		<a href="?page=ah-reviews&tab=approved" class="nav-tab <?php echo $tab === 'approved' ? 'nav-tab-active' : ''; ?>">Approved</a>
		<a href="?page=ah-reviews&tab=all" class="nav-tab <?php echo $tab === 'all' ? 'nav-tab-active' : ''; ?>">All</a>
	</h2>

	<?php
	$page = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
	if ( $tab === 'pending' ) {
		$result = Review_Service::get_pending_reviews( $page );
	} else {
		// For approved/all, show all reviews (simplified).
		global $wpdb;
		$table  = $wpdb->prefix . 'ah_ecommerce_reviews';
		$where  = $tab === 'approved' ? "WHERE r.status = 'approved'" : '';
		$offset = ( $page - 1 ) * 20;
		$result = array(
			'items' => $wpdb->get_results( "SELECT r.*, p.title AS product_name FROM {$table} r LEFT JOIN {$wpdb->prefix}ah_ecommerce_products p ON r.product_id = p.id {$where} ORDER BY r.created_at DESC LIMIT 20 OFFSET {$offset}" ),
			'meta'  => array( 'current_page' => $page, 'per_page' => 20, 'total_items' => 0, 'total_pages' => 1 ),
		);
	}
	?>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th>Product</th>
				<th>Reviewer</th>
				<th>Rating</th>
				<th>Comment</th>
				<th>Date</th>
				<th>Status</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $result['items'] ) ) : ?>
				<tr><td colspan="7">No reviews found.</td></tr>
			<?php else : foreach ( $result['items'] as $review ) : ?>
				<tr>
					<td><strong><?php echo esc_html( $review->product_name ?? '#' . $review->product_id ); ?></strong></td>
					<td>
						<?php echo esc_html( $review->reviewer_name ); ?>
						<br><small style="color:#888;"><?php echo esc_html( $review->reviewer_email ); ?></small>
					</td>
					<td><?php echo str_repeat( '★', (int) $review->rating ) . str_repeat( '☆', 5 - (int) $review->rating ); ?></td>
					<td style="max-width:300px;"><?php echo esc_html( wp_trim_words( $review->comment, 20 ) ); ?></td>
					<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $review->created_at ) ) ); ?></td>
					<td>
						<?php
						$status_colors = array( 'pending' => '#fef3c7,#92400e', 'approved' => '#dcfce7,#166534', 'rejected' => '#fee2e2,#991b1b' );
						list( $bg, $fg ) = explode( ',', $status_colors[ $review->status ] ?? '#f3f4f6,#6b7280' );
						?>
						<span style="background:<?php echo $bg; ?>; color:<?php echo $fg; ?>; padding:3px 8px; border-radius:4px; font-size:12px; font-weight:600;"><?php echo ucfirst( $review->status ); ?></span>
					</td>
					<td>
						<?php if ( $review->status !== 'approved' ) : ?>
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( "admin.php?page=ah-reviews&action=approve&review_id={$review->id}" ), "ah_review_action_{$review->id}" ) ); ?>" class="button button-small">Approve</a>
						<?php endif; ?>
						<?php if ( $review->status !== 'rejected' ) : ?>
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( "admin.php?page=ah-reviews&action=reject&review_id={$review->id}" ), "ah_review_action_{$review->id}" ) ); ?>" class="button button-small">Reject</a>
						<?php endif; ?>
						<a href="<?php echo esc_url( wp_nonce_url( admin_url( "admin.php?page=ah-reviews&action=delete&review_id={$review->id}" ), "ah_review_action_{$review->id}" ) ); ?>" class="button button-small" style="color:#dc3232;" onclick="return confirm('Delete review?');">Delete</a>
					</td>
				</tr>
			<?php endforeach; endif; ?>
		</tbody>
	</table>
</div>
