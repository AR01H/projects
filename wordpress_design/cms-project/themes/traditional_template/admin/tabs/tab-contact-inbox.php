<?php
/**
 * Contact Submissions submenu - inbox table for the contact form.
 * Rows come from the nt_submissions table (config/database.php); the form
 * saves there via handlers/ajax/contact.php. Row actions (mark read/new,
 * delete) post to the generic tool endpoints - nonce + capability enforced.
 */

defined( 'ABSPATH' ) || exit;

global $wpdb;

if ( ! nt_db_table_exists( 'submissions' ) ) {
	echo '<p>' . esc_html__( 'The submissions table is not installed yet.', NT_TEXT_DOMAIN ) . ' ';
	echo '<a href="' . esc_url( nt_admin_url( 'dashboard_tools', 'admin_tools', 'database' ) ) . '">' . esc_html__( 'Install it under Admin Tools -> Database.', NT_TEXT_DOMAIN ) . '</a></p>';
	return;
}

$nt_table    = nt_db_table( 'submissions' );
$nt_per_page = 20;

// Status filter (?status=new|read) - whitelisted.
$nt_filter = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
if ( ! in_array( $nt_filter, array( 'new', 'read' ), true ) ) {
	$nt_filter = '';
}

$nt_paged  = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
$nt_offset = ( $nt_paged - 1 ) * $nt_per_page;

// Counts for the filter links.
$nt_count_all  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$nt_table}`" );
$nt_count_new  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$nt_table}` WHERE status = %s", 'new' ) );
$nt_count_read = $nt_count_all - $nt_count_new;

if ( '' === $nt_filter ) {
	$nt_total = $nt_count_all;
	$nt_rows  = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM `{$nt_table}` ORDER BY created_at DESC LIMIT %d OFFSET %d",
		$nt_per_page,
		$nt_offset
	), ARRAY_A );
} else {
	$nt_total = ( 'new' === $nt_filter ) ? $nt_count_new : $nt_count_read;
	$nt_rows  = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM `{$nt_table}` WHERE status = %s ORDER BY created_at DESC LIMIT %d OFFSET %d",
		$nt_filter,
		$nt_per_page,
		$nt_offset
	), ARRAY_A );
}

$nt_total_pages = max( 1, (int) ceil( $nt_total / $nt_per_page ) );
$nt_base_url    = nt_admin_url( 'contact_inbox' );

/** Small helper for the per-row action forms (status toggle / delete). */
$nt_row_form = static function ( $action, $id, $label, $extra = array() ) {
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline;margin:0 4px 0 0;">';
	echo '<input type="hidden" name="action" value="' . esc_attr( 'nt_tool_' . $action ) . '">';
	echo '<input type="hidden" name="submission_id" value="' . esc_attr( (string) $id ) . '">';
	nt_admin_location_fields();
	foreach ( $extra as $k => $v ) {
		echo '<input type="hidden" name="' . esc_attr( $k ) . '" value="' . esc_attr( $v ) . '">';
	}
	wp_nonce_field( 'nt_tool_' . $action );
	echo '<button type="submit" class="button-link">' . esc_html( $label ) . '</button>';
	echo '</form>';
};
?>

<ul class="subsubsub nt-subtabs">
	<li><a<?php echo '' === $nt_filter ? ' class="current"' : ''; ?> href="<?php echo esc_url( $nt_base_url ); ?>"><?php esc_html_e( 'All', NT_TEXT_DOMAIN ); ?> (<?php echo esc_html( (string) $nt_count_all ); ?>)</a> | </li>
	<li><a<?php echo 'new' === $nt_filter ? ' class="current"' : ''; ?> href="<?php echo esc_url( add_query_arg( 'status', 'new', $nt_base_url ) ); ?>"><?php esc_html_e( 'New', NT_TEXT_DOMAIN ); ?> (<?php echo esc_html( (string) $nt_count_new ); ?>)</a> | </li>
	<li><a<?php echo 'read' === $nt_filter ? ' class="current"' : ''; ?> href="<?php echo esc_url( add_query_arg( 'status', 'read', $nt_base_url ) ); ?>"><?php esc_html_e( 'Read', NT_TEXT_DOMAIN ); ?> (<?php echo esc_html( (string) $nt_count_read ); ?>)</a></li>
</ul>
<br class="clear">

<?php if ( ! $nt_rows ) : ?>
	<p><?php esc_html_e( 'No submissions yet.', NT_TEXT_DOMAIN ); ?></p>
	<?php return; ?>
<?php endif; ?>

<table class="widefat striped nt-admin-table">
	<thead>
		<tr>
			<th style="width:60px;"><?php esc_html_e( 'ID', NT_TEXT_DOMAIN ); ?></th>
			<th><?php esc_html_e( 'Name', NT_TEXT_DOMAIN ); ?></th>
			<th><?php esc_html_e( 'Contact', NT_TEXT_DOMAIN ); ?></th>
			<th style="width:36%;"><?php esc_html_e( 'Message', NT_TEXT_DOMAIN ); ?></th>
			<th><?php esc_html_e( 'Date', NT_TEXT_DOMAIN ); ?></th>
			<th><?php esc_html_e( 'Status', NT_TEXT_DOMAIN ); ?></th>
			<th><?php esc_html_e( 'Actions', NT_TEXT_DOMAIN ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $nt_rows as $nt_sub ) : ?>
			<?php $nt_is_new = ( 'new' === $nt_sub['status'] ); ?>
			<tr<?php echo $nt_is_new ? ' style="font-weight:600;"' : ''; ?>>
				<td>#<?php echo esc_html( (string) $nt_sub['id'] ); ?></td>
				<td><?php echo esc_html( $nt_sub['name'] ); ?></td>
				<td>
					<a href="<?php echo esc_url( 'mailto:' . $nt_sub['email'] ); ?>"><?php echo esc_html( $nt_sub['email'] ); ?></a>
					<?php if ( '' !== $nt_sub['phone'] ) : ?>
						<br><span class="nt-muted"><?php echo esc_html( $nt_sub['phone'] ); ?></span>
					<?php endif; ?>
				</td>
				<td><?php echo esc_html( wp_html_excerpt( $nt_sub['message'], 160, '…' ) ); ?></td>
				<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' H:i', $nt_sub['created_at'] ) ); ?></td>
				<td><?php echo $nt_is_new ? '<span class="nt-badge">' . esc_html__( 'new', NT_TEXT_DOMAIN ) . '</span>' : esc_html__( 'read', NT_TEXT_DOMAIN ); ?></td>
				<td>
					<?php
					$nt_row_form(
						'submission_status',
						(int) $nt_sub['id'],
						$nt_is_new ? __( 'Mark read', NT_TEXT_DOMAIN ) : __( 'Mark new', NT_TEXT_DOMAIN ),
						array( 'new_status' => $nt_is_new ? 'read' : 'new' )
					);
					$nt_row_form( 'submission_delete', (int) $nt_sub['id'], __( 'Delete', NT_TEXT_DOMAIN ) );
					?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php if ( $nt_total_pages > 1 ) : ?>
	<p class="nt-inbox-pages" style="margin-top:12px;">
		<?php for ( $nt_p = 1; $nt_p <= $nt_total_pages; $nt_p++ ) : ?>
			<?php $nt_url = add_query_arg( array_filter( array( 'status' => $nt_filter, 'paged' => $nt_p ) ), $nt_base_url ); ?>
			<?php if ( $nt_p === $nt_paged ) : ?>
				<strong style="padding:0 6px;"><?php echo esc_html( (string) $nt_p ); ?></strong>
			<?php else : ?>
				<a style="padding:0 6px;" href="<?php echo esc_url( $nt_url ); ?>"><?php echo esc_html( (string) $nt_p ); ?></a>
			<?php endif; ?>
		<?php endfor; ?>
	</p>
<?php endif; ?>
