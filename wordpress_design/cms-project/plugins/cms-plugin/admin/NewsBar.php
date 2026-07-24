<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use Ah\Cms\Admin\Components\AdminComponents;

$model         = new AH_Newsbar_Model();
$notice        = '';
$action        = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id       = (int) ( $_GET['id'] ?? 0 );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	if ( ! wp_verify_nonce( $_POST['ah_newsbar_nonce'] ?? '', 'ah_save_newsbar' ) ) wp_die( 'Security.' );
	$_title       = sanitize_text_field( $_POST['text'] ?? '' );
	$_slug_input  = sanitize_title( wp_unslash( $_POST['slug'] ?? '' ) );
	$data = array(
		'label'      => sanitize_text_field( $_POST['label'] ?? '' ),
		'text'       => $_title,
		'slug'       => $model->unique_slug_from_title( $_slug_input ?: $_title, $edit_id ),
		'excerpt'    => sanitize_text_field( $_POST['excerpt'] ?? '' ),
		'content'    => wp_kses_post( $_POST['content'] ?? '' ),
		'image_id'   => (int) ( $_POST['image_id'] ?? 0 ) ?: null,
		'link_url'   => esc_url_raw( $_POST['link_url'] ?? '' ),
		'link_target'=> in_array( $_POST['link_target'] ?? '_self', array( '_self', '_blank' ), true ) ? $_POST['link_target'] : '_self',
		'status'     => sanitize_key( $_POST['status'] ?? 'active' ),
		'sort_order' => (int) ( $_POST['sort_order'] ?? 0 ),
		'start_date' => sanitize_text_field( $_POST['start_date'] ?? '' ) ?: null,
		'end_date'   => sanitize_text_field( $_POST['end_date'] ?? '' ) ?: null,
		'created_by' => get_current_user_id() ?: null,
	);
	if ( $edit_id ) {
		$model->update( $edit_id, $data );
		$saved_id = $edit_id;
	} else {
		$saved_id = (int) $model->create( $data );
	}
	if ( $saved_id ) {
		// taxonomy sync removed — not using taxonomy terms
	}
	$notice = 'News bar item saved.';
	$action = 'list';
}

if ( isset( $_GET['delete_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_del_newsbar' ) ) {
	$delete_id = (int) $_GET['delete_id'];
	$model->delete( $delete_id );
	$notice = 'Item deleted.';
}
?>
<div class="wrap ah-wrap">
  <?php AdminComponents::pageHeader( 'megaphone', 'News Bar', 'Create scrolling news items displayed in the site news ticker.' ); ?>
  <?php if ( $notice ) : ?><?php AdminComponents::notice( $notice, 'success' ); ?><?php endif; ?>

  <?php if ( $action === 'list' ) :
    $search = sanitize_text_field( $_GET['s'] ?? '' );
    $status = sanitize_key( $_GET['status'] ?? '' );
    $label_search = sanitize_text_field( $_GET['label'] ?? '' );
    $paged  = AH_Pagination::current_page();
    $result = $model->get_paginated( $paged, $search, $status, $label_search );
    $items  = $result['items']; $meta = $result['meta'];
  ?>
    <?php AdminComponents::filterBar( array(
      'page_slug'          => 'ah-news-bar',
      'search_placeholder' => 'Search by title or content…',
      'search_value'       => $search,
      'filters'            => array(
        array(
          'name'     => 'status',
          'options'  => array( '' => 'All Status', 'active' => 'Active', 'inactive' => 'Inactive' ),
          'selected' => $status,
        ),
      ),
      'extra_fields'  => '<input type="text" name="label" value="' . esc_attr( $label_search ) . '" placeholder="Filter by label…" style="max-width:160px;">',
      'active_values' => array( $label_search ),
      'add_url'   => add_query_arg( array( 'page' => 'ah-news-bar', 'action' => 'add' ), admin_url( 'admin.php' ) ),
      'add_label' => '+ Add Item',
    ) ); ?>

    <?php
    $nb_rows = array();
    foreach ( $items as $item ) {
      $row = new \stdClass();
      $row->id = $item->id;
      $row->label = $item->label ?? '';
      $row->text = $item->text;
      $row->link_url = $item->link_url;
      $row->dates = ( $item->start_date ?: '∞' ) . ' → ' . ( $item->end_date ?: '∞' );
      $row->status = $item->status;
      $row->edit_url = add_query_arg( array( 'page' => 'ah-news-bar', 'action' => 'edit', 'id' => $item->id ), admin_url( 'admin.php' ) );
      $row->delete_url = wp_nonce_url( add_query_arg( array( 'page' => 'ah-news-bar', 'delete_id' => $item->id ), admin_url( 'admin.php' ) ), 'ah_del_newsbar' );
      $nb_rows[] = $row;
    }
    AdminComponents::dataTable( array(
      'columns' => array(
        array( 'label' => 'Label', 'render' => function ( $r ) {
          return '<small>' . esc_html( $r->label ) . '</small>';
        } ),
        array( 'label' => 'Title', 'render' => function ( $r ) {
          return esc_html( $r->text );
        } ),
        array( 'label' => 'Link', 'render' => function ( $r ) {
          return '<small>' . esc_html( $r->link_url ) . '</small>';
        } ),
        array( 'label' => 'Dates', 'render' => function ( $r ) {
          return '<small>' . esc_html( $r->dates ) . '</small>';
        } ),
        array( 'label' => 'Status', 'render' => function ( $r ) {
          return '<span class="ah-badge ah-badge-' . esc_attr( $r->status ) . '">' . esc_html( $r->status ) . '</span>';
        } ),
      ),
      'items'         => $nb_rows,
      'empty_message' => 'No news bar items yet.',
      'actions'       => function ( $r ) {
        $html = '<a href="' . esc_url( $r->edit_url ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>';
        $html .= '<a href="' . esc_url( $r->delete_url ) . '" class="ah-btn ah-btn-danger ah-btn-sm ah-confirm-delete" data-title="Delete &quot;' . esc_attr( $r->text ) . '&quot;" data-confirm="This news item will be permanently removed.">Delete</a>';
        return $html;
      },
    ) ); ?>
    <?php echo AH_Pagination::render( $meta ); ?>

  <?php else :
    $item = $edit_id ? $model->find( $edit_id ) : null;
  ?>
    <?php AdminComponents::backLink( admin_url( 'admin.php?page=ah-news-bar' ) ); ?>

    <?php ob_start(); ?>
      <form method="post">
        <?php wp_nonce_field( 'ah_save_newsbar', 'ah_newsbar_nonce' ); ?>

        <?php AdminComponents::formGrid( array(
          array( 'Label <small>(e.g. "Market Update")</small>', '<input type="text" name="label" value="' . esc_attr( $item->label ?? '' ) . '" placeholder="Category label">' ),
          array( 'Title *', '<input type="text" name="text" value="' . esc_attr( $item->text ?? '' ) . '" required>' ),
        ) ); ?>

        <?php
        $slug_help = '';
        if ( ! empty( $item->slug ) ) {
          $slug_help = '<p class="description"><a href="' . esc_url( home_url( SITE_NEWS_URL . '?ah_news=' . rawurlencode( $item->slug ) ) ) . '" target="_blank">View item &#8599;</a></p>';
        }
        AdminComponents::formRow( 'Slug <small>(leave blank to auto-generate)</small>',
          '<input type="text" name="slug" class="regular-text" pattern="[a-z0-9\\-_]*" value="' . esc_attr( $item->slug ?? '' ) . '" placeholder="e.g. stamp_duty_changes_2026">',
          $slug_help
        );
        ?>

        <?php AdminComponents::formRow( 'Short Description <small>(excerpt shown on listing cards)</small>',
          '<textarea name="excerpt" rows="2" class="large-text">' . esc_textarea( $item->excerpt ?? '' ) . '</textarea>'
        ); ?>

        <?php
        ob_start();
        wp_editor( $item->content ?? '', 'newsbar_content', array(
            'textarea_name' => 'content',
            'media_buttons' => true,
            'teeny'         => false,
            'textarea_rows' => 8,
            'quicktags'     => true,
        ) );
        $editor_html = ob_get_clean();
        AdminComponents::formRow( 'Full Content', $editor_html );
        ?>

        <?php
        $nb_img_id  = (int) ( $item->image_id ?? 0 );
        AdminComponents::mediaField( 'image_id', 'Thumbnail Image / Video', $nb_img_id, array( 'type' => 'media' ) );
        ?>

        <?php AdminComponents::formRow( 'Link URL', '<input type="text" name="link_url" value="' . esc_attr( $item->link_url ?? '' ) . '" class="regular-text">' ); ?>

        <?php
        $target_select = '<select name="link_target"><option value="_self"' . selected( $item->link_target ?? '_self', '_self', false ) . '>Same Tab</option><option value="_blank"' . selected( $item->link_target ?? '', '_blank', false ) . '>New Tab</option></select>';
        AdminComponents::formRow( 'Open In', $target_select );
        ?>

        <?php AdminComponents::formGrid( array(
          array( 'Start Date', '<input type="date" name="start_date" value="' . esc_attr( $item->start_date ?? '' ) . '">' ),
          array( 'End Date', '<input type="date" name="end_date" value="' . esc_attr( $item->end_date ?? '' ) . '">' ),
        ) ); ?>

        <?php AdminComponents::formRow( 'Sort Order', '<input type="number" name="sort_order" value="' . esc_attr( $item->sort_order ?? 0 ) . '">' ); ?>

        <?php
        $status_select = '<select name="status"><option value="active"' . selected( $item->status ?? 'active', 'active', false ) . '>Active</option><option value="inactive"' . selected( $item->status ?? '', 'inactive', false ) . '>Inactive</option></select>';
        AdminComponents::formRow( 'Status', $status_select );
        ?>

        <button type="submit" class="ah-btn ah-btn-primary">Save Item</button>
      </form>
    <?php AdminComponents::card( $item ? 'Edit Item' : 'Add Item', ob_get_clean() ); ?>
  <?php endif; ?>
</div>
