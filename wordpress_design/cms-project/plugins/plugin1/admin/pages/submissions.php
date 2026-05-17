<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$model  = new AH_Contact_Model();
$notice = '';
$action = sanitize_key( $_GET['action'] ?? 'list' );
$view_id = (int) ( $_GET['id'] ?? 0 );

// Mark status / read
if ( isset( $_GET['mark_read'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'ah_mark_read' ) ) {
	$model->mark_read( (int) $_GET['mark_read'] );
	$notice = 'Marked as read.';
}
if ( isset( $_GET['mark_status'] ) && isset( $_GET['status'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'ah_mark_status' ) ) {
	$model->mark_status( (int) $_GET['mark_status'], sanitize_key( $_GET['status'] ) );
	$notice = 'Status updated.';
}
if ( isset( $_GET['delete_id'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'ah_del_sub' ) ) {
	$model->delete( (int) $_GET['delete_id'] );
	$notice = 'Submission deleted.';
}

// View single submission
if ( $view_id ) {
	$sub = $model->find( $view_id );
	if ( $sub && ! $sub->is_read ) $model->mark_read( $view_id );
}
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-email-alt"></span> <?php esc_html_e( 'Contact Submissions', 'ah-theme' ); ?>
    <?php $unread = $model->unread_count(); if ( $unread ) : ?><span class="update-plugins count-<?php echo $unread; ?>"><span class="plugin-count"><?php echo $unread; ?></span></span><?php endif; ?>
  </h1>
  <?php if ( $notice ) : ?><div class="ah-notice ah-notice-success"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

  <?php if ( $view_id && isset( $sub ) && $sub ) :
    $status_options = array( 'new', 'in_progress', 'resolved', 'spam' );
  ?>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-submissions' ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm" style="margin-bottom:14px;display:inline-flex;">&larr; Back</a>
    <div class="ah-card">
      <div class="ah-card-header">
        <h2>Message from <?php echo esc_html( $sub->full_name ); ?></h2>
        <div style="display:flex;gap:8px;align-items:center;">
          <?php foreach ( $status_options as $st ) : ?>
            <?php if ( $st !== $sub->status ) : ?>
              <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-submissions', 'mark_status' => $sub->id, 'id' => $sub->id, 'status' => $st ), admin_url( 'admin.php' ) ), 'ah_mark_status' ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Mark <?php echo ucfirst( $st ); ?></a>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
      <table style="width:100%;max-width:600px;">
        <tr><td style="padding:6px 0;color:var(--ah-muted);width:140px;">Name</td><td><?php echo esc_html( $sub->full_name ); ?></td></tr>
        <tr><td style="padding:6px 0;color:var(--ah-muted);">Email</td><td><a href="mailto:<?php echo esc_attr( $sub->email ); ?>"><?php echo esc_html( $sub->email ); ?></a></td></tr>
        <tr><td style="padding:6px 0;color:var(--ah-muted);">Phone</td><td><?php echo esc_html( $sub->phone ?: '—' ); ?></td></tr>
        <tr><td style="padding:6px 0;color:var(--ah-muted);">Subject</td><td><?php echo esc_html( $sub->subject ?: '—' ); ?></td></tr>
        <tr><td style="padding:6px 0;color:var(--ah-muted);">Status</td><td><span class="ah-badge ah-badge-<?php echo esc_attr( str_replace( '_', '-', $sub->status ) ); ?>"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $sub->status ) ) ); ?></span></td></tr>
        <tr><td style="padding:6px 0;color:var(--ah-muted);">Submitted</td><td><?php echo esc_html( wp_date( 'M j, Y g:i a', strtotime( $sub->submitted_at ) ) ); ?></td></tr>
        <tr><td style="padding:6px 0;color:var(--ah-muted);">IP</td><td><?php echo esc_html( $sub->ip_address ); ?></td></tr>
      </table>
      <div style="margin-top:16px;padding:16px;background:var(--ah-bg-light);border-radius:6px;white-space:pre-wrap;font-size:14px;line-height:1.6;"><?php echo esc_html( $sub->message ); ?></div>
    </div>

  <?php else :
    $status_f = sanitize_key( $_GET['status'] ?? '' );
    $search   = sanitize_text_field( $_GET['s'] ?? '' );
    $paged    = AH_Pagination::current_page();
    $result   = $model->get_paginated( $paged, $status_f, $search );
    $items    = $result['items']; $meta = $result['meta'];
  ?>
    <div class="ah-table-top">
      <form class="ah-search-form" method="get">
        <input type="hidden" name="page" value="ah-submissions">
        <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search submissions…">
        <select name="status">
          <option value="">All Status</option>
          <?php foreach ( array( 'new', 'in_progress', 'resolved', 'spam' ) as $st ) : ?><option value="<?php echo $st; ?>" <?php selected( $status_f, $st ); ?>><?php echo ucfirst( str_replace( '_', ' ', $st ) ); ?></option><?php endforeach; ?>
        </select>
        <button class="ah-btn ah-btn-secondary">Filter</button>
      </form>
    </div>
    <div class="ah-table-wrap">
      <table class="ah-table">
        <thead><tr><th>Name</th><th>Email</th><th>Subject</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ( $items as $sub ) : ?>
            <tr style="<?php echo ! $sub->is_read ? 'font-weight:600;' : ''; ?>">
              <td><?php echo esc_html( $sub->full_name ); ?><?php echo ! $sub->is_read ? ' <span class="ah-badge ah-badge-new" style="font-size:10px;">New</span>' : ''; ?></td>
              <td><?php echo esc_html( $sub->email ); ?></td>
              <td><?php echo esc_html( wp_trim_words( $sub->subject ?: $sub->message, 8 ) ); ?></td>
              <td><span class="ah-badge ah-badge-<?php echo esc_attr( str_replace( '_', '-', $sub->status ) ); ?>"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $sub->status ) ) ); ?></span></td>
              <td><small><?php echo esc_html( wp_date( 'M j, Y', strtotime( $sub->submitted_at ) ) ); ?></small></td>
              <td class="row-actions">
                <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-submissions', 'id' => $sub->id ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">View</a>
                <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-submissions', 'delete_id' => $sub->id ), admin_url( 'admin.php' ) ), 'ah_del_sub' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm" onclick="return confirm('Delete?');">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php echo AH_Pagination::render( $meta ); ?>
  <?php endif; ?>
</div>
