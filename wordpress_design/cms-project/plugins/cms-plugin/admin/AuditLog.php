<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use Ah\Cms\Admin\Components\AdminComponents;

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
  <?php AdminComponents::pageHeader( 'list-view', 'Audit Log', 'Review all create, update, and delete events across the CMS.' ); ?>

  <?php AdminComponents::filterBar( array(
    'page_slug'      => 'ah-audit',
    'search_name'    => 'table_filter',
    'search_placeholder' => 'Filter by table…',
    'search_value'   => $table,
    'hidden_inputs'  => array(),
    'filters'        => array(
      array(
        'name'     => 'action_filter',
        'options'  => array_merge( array( '' => 'All Actions' ), array_combine( $actions_list, array_map( 'ucfirst', $actions_list ) ) ),
        'selected' => $action,
      ),
    ),
  ) ); ?>

  <?php
  $audit_rows = array();
  foreach ( $items as $log ) {
    $row = new \stdClass();
    $row->id = $log->id;
    $row->time = wp_date( 'M j Y g:i a', strtotime( $log->created_at ) );
    $row->action = strtoupper( $log->action );
    $row->action_color = array( 'create' => '#dcfce7', 'update' => '#fef9c3', 'delete' => '#fee2e2', 'login' => '#dbeafe' )[ $log->action ] ?? '#f1f5f9';
    $row->table_name = $log->table_name ?: '-';
    $row->record_id = $log->record_id;
    $row->user_id = $log->user_id;
    $row->ip_address = $log->ip_address ?: '-';
    $row->old_values = $log->old_values;
    $row->new_values = $log->new_values;
    $audit_rows[] = $row;
  }
  AdminComponents::dataTable( array(
    'columns' => array(
      array( 'label' => 'Time', 'render' => function ( $r ) {
        return '<small>' . esc_html( $r->time ) . '</small>';
      } ),
      array( 'label' => 'Action', 'render' => function ( $r ) {
        return '<span style="background:' . esc_attr( $r->action_color ) . ';padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;">' . esc_html( $r->action ) . '</span>';
      } ),
      array( 'label' => 'Table', 'render' => function ( $r ) {
        return '<code style="font-size:11px;">' . esc_html( $r->table_name ) . '</code>';
      } ),
      array( 'label' => 'Record ID', 'render' => function ( $r ) {
        return $r->record_id ? '#' . esc_html( $r->record_id ) : '-';
      } ),
      array( 'label' => 'User', 'render' => function ( $r ) {
        return $r->user_id ? '#' . esc_html( $r->user_id ) : '<em style="color:var(--ah-muted);">system</em>';
      } ),
      array( 'label' => 'IP', 'render' => function ( $r ) {
        return '<small>' . esc_html( $r->ip_address ) . '</small>';
      } ),
      array( 'label' => 'Details', 'render' => function ( $r ) {
        if ( ! $r->old_values && ! $r->new_values ) return '-';
        $html = '<details><summary style="cursor:pointer;font-size:12px;color:var(--ah-primary);">View</summary>';
        if ( $r->old_values ) $html .= '<pre style="font-size:10px;max-height:120px;overflow:auto;background:#f8fafc;padding:6px;border-radius:4px;">Before: ' . esc_html( $r->old_values ) . '</pre>';
        if ( $r->new_values ) $html .= '<pre style="font-size:10px;max-height:120px;overflow:auto;background:#f8fafc;padding:6px;border-radius:4px;">After: ' . esc_html( $r->new_values ) . '</pre>';
        $html .= '</details>';
        return $html;
      } ),
    ),
    'items'         => $audit_rows,
    'empty_message' => 'No audit entries found.',
  ) ); ?>
  <?php echo \AH_Pagination::render( $meta ); ?>
</div>
