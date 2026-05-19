<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

global $wpdb;
$table = $wpdb->prefix . 'ch_contact_submissions';
$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
$submissions  = $table_exists
	? $wpdb->get_results( "SELECT * FROM `{$table}` ORDER BY created_at DESC LIMIT 100" )
	: [];
?>
<div class="wrap ch-admin-wrap">
	<h1>📥 Enquiry Submissions</h1>

	<?php if ( ! $table_exists ) : ?>
		<div class="ch-notice ch-notice--warning">
			⚠️ The submissions table doesn't exist yet. <a href="<?php echo admin_url( 'admin.php?page=ch-theme-mock' ); ?>">Run the seeder</a> to create it.
		</div>
	<?php elseif ( empty( $submissions ) ) : ?>
		<div class="ch-card">
			<p>No enquiries received yet. Once visitors submit the contact form, their messages will appear here.</p>
		</div>
	<?php else : ?>
		<div class="ch-card">
			<p style="margin-bottom:1rem;"><strong><?php echo count( $submissions ); ?></strong> enquiries received.</p>
			<table class="widefat striped">
				<thead>
					<tr>
						<th>Date</th>
						<th>Name</th>
						<th>Email</th>
						<th>Phone</th>
						<th>Enquiry Type</th>
						<th>Message</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $submissions as $sub ) : ?>
						<tr>
							<td><?php echo esc_html( $sub->created_at ?? '-' ); ?></td>
							<td><?php echo esc_html( $sub->name     ?? '-' ); ?></td>
							<td><a href="mailto:<?php echo esc_attr( $sub->email ?? '' ); ?>"><?php echo esc_html( $sub->email ?? '-' ); ?></a></td>
							<td><?php echo esc_html( $sub->phone    ?? '-' ); ?></td>
							<td><span class="ch-badge ch-badge--green"><?php echo esc_html( $sub->enquiry_type ?? 'General' ); ?></span></td>
							<td style="max-width:300px;word-wrap:break-word;"><?php echo esc_html( $sub->message ?? '-' ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</div>
