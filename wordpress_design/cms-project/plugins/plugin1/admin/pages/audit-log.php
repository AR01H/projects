<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$model   = new AH_Audit_Model();
$action  = sanitize_key( $_GET['action_filter'] ?? '' );
$table   = sanitize_text_field( $_GET['table_filter'] ?? '' );
$paged   = AH_Pagination::current_page();

$result  = $model->get_paginated( $paged, array_filter( array( 'action' => $action, 'table_name' => $table ) ) );
$items   = $result['items'];
$meta    = $result['meta'];

$actions_list = array( 'create', 'update', 'delete', 'login' );
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-list-view"></span> <?php esc_html_e( 'Audit Log', 'ah-theme' ); ?></h1>

  <div class="ah-table-top">
    <form class="ah-filters" method="get">
      <input type="hidden" name="page" value="ah-audit">
      <select name="action_filter">
        <option value="">All Actions</option>
        <?php foreach ( $actions_list as $a ) : ?><option value="<?php echo $a; ?>" <?php selected( $action, $a ); ?>><?php echo ucfirst( $a ); ?></option><?php endforeach; ?>
      </select>
      <input type="text" name="table_filter" value="<?php echo esc_attr( $table ); ?>" placeholder="Filter by table…" style="max-width:200px;">
      <button class="ah-btn ah-btn-secondary">Filter</button>
      <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-audit' ) ); ?>" class="ah-btn ah-btn-secondary">Clear</a>
    </form>
    <span style="color:var(--ah-muted);font-size:13px;"><?php echo number_format_i18n( $meta['total'] ); ?> entries</span>
  </div>

  <div class="ah-table-wrap">
    <table class="ah-table">
      <thead>
        <tr><th>Time</th><th>Action</th><th>Table</th><th>Record ID</th><th>User</th><th>IP</th><th>Details</th></tr>
      </thead>
      <tbody>
        <?php foreach ( $items as $log ) :
          $colors = array( 'create' => '#dcfce7', 'update' => '#fef9c3', 'delete' => '#fee2e2', 'login' => '#dbeafe' );
          $color  = $colors[ $log->action ] ?? '#f1f5f9';
        ?>
          <tr>
            <td><small><?php echo esc_html( wp_date( 'M j Y g:i a', strtotime( $log->created_at ) ) ); ?></small></td>
            <td><span style="background:<?php echo esc_attr( $color ); ?>;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;"><?php echo esc_html( strtoupper( $log->action ) ); ?></span></td>
            <td><code style="font-size:11px;"><?php echo esc_html( $log->table_name ?: '—' ); ?></code></td>
            <td><?php echo $log->record_id ? '#' . esc_html( $log->record_id ) : '—'; ?></td>
            <td><?php echo $log->user_id ? '#' . esc_html( $log->user_id ) : '<em style="color:var(--ah-muted);">system</em>'; ?></td>
            <td><small><?php echo esc_html( $log->ip_address ?: '—' ); ?></small></td>
            <td>
              <?php if ( $log->old_values || $log->new_values ) : ?>
                <details><summary style="cursor:pointer;font-size:12px;color:var(--ah-primary);">View</summary>
                  <?php if ( $log->old_values ) : ?><pre style="font-size:10px;max-height:120px;overflow:auto;background:#f8fafc;padding:6px;border-radius:4px;">Before: <?php echo esc_html( $log->old_values ); ?></pre><?php endif; ?>
                  <?php if ( $log->new_values ) : ?><pre style="font-size:10px;max-height:120px;overflow:auto;background:#f8fafc;padding:6px;border-radius:4px;">After: <?php echo esc_html( $log->new_values ); ?></pre><?php endif; ?>
                </details>
              <?php else : ?>—<?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if ( ! $items ) : ?><tr><td colspan="7" style="text-align:center;color:var(--ah-muted);padding:30px;">No audit entries found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php echo AH_Pagination::render( $meta ); ?>
</div>
