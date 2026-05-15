<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$model  = new AH_Pages_Model();
$notice = '';
$action = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id = (int) ( $_GET['id'] ?? 0 );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	if ( ! wp_verify_nonce( $_POST['ah_pages_nonce'] ?? '', 'ah_save_page' ) ) wp_die( 'Security.' );

	$data = array(
		'title'            => sanitize_text_field( $_POST['title'] ?? '' ),
		'slug'             => AH_Slug_Helper::generate( $_POST['slug'] ?: $_POST['title'], AH_DB_Helper::table( 'pages' ), 'slug', $edit_id ),
		'page_type'        => sanitize_key( $_POST['page_type'] ?? 'custom' ),
		'meta_title'       => sanitize_text_field( $_POST['meta_title'] ?? '' ),
		'meta_description' => sanitize_textarea_field( $_POST['meta_description'] ?? '' ),
		'meta_keywords'    => sanitize_text_field( $_POST['meta_keywords'] ?? '' ),
		'status'           => sanitize_key( $_POST['status'] ?? 'active' ),
		'updated_by'       => get_current_user_id() ?: null,
	);

	if ( $edit_id ) {
		$model->update( $edit_id, $data );
	} else {
		$data['created_by'] = get_current_user_id() ?: null;
		$model->create( $data );
	}
	$notice = 'Page saved.';
	$action = 'list';
}

$page_types = array( 'home','about','services','contact','client_stories','blog_listing','news_listing','custom' );
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-admin-page"></span> <?php esc_html_e( 'Pages Manager', 'ah-theme' ); ?></h1>

  <?php if ( $notice ) : ?><div class="ah-notice ah-notice-success"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

  <?php if ( $action === 'list' ) :
    $search  = sanitize_text_field( $_GET['s'] ?? '' );
    $paged   = AH_Pagination::current_page();
    $result  = $model->get_paginated( $paged, $search );
    $items   = $result['items'];
    $meta    = $result['meta'];
  ?>
    <div class="ah-table-top">
      <form class="ah-search-form" method="get">
        <input type="hidden" name="page" value="ah-pages">
        <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search pages…">
        <button class="ah-btn ah-btn-secondary">Search</button>
      </form>
      <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-pages', 'action' => 'add' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-primary">+ Add Page</a>
    </div>

    <div class="ah-table-wrap">
      <table class="ah-table">
        <thead><tr><th>Title</th><th>Slug</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ( $items as $pg ) : ?>
            <tr>
              <td><strong><?php echo esc_html( $pg->title ); ?></strong></td>
              <td><code><?php echo esc_html( $pg->slug ); ?></code></td>
              <td><?php echo esc_html( str_replace( '_', ' ', $pg->page_type ) ); ?></td>
              <td><span class="ah-badge ah-badge-<?php echo esc_attr( $pg->status ); ?>"><?php echo esc_html( $pg->status ); ?></span></td>
              <td class="row-actions">
                <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-pages', 'action' => 'edit', 'id' => $pg->id ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>
                <button class="ah-btn ah-btn-secondary ah-btn-sm ah-toggle-status" data-id="<?php echo esc_attr( $pg->id ); ?>" data-table="pages" data-action="<?php echo $pg->status === 'active' ? 'inactive' : 'active'; ?>">
                  <?php echo $pg->status === 'active' ? 'Deactivate' : 'Activate'; ?>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php echo AH_Pagination::render( $meta ); ?>

  <?php else :
    $item = $edit_id ? $model->find( $edit_id ) : null;
  ?>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-pages' ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm" style="margin-bottom:14px;display:inline-flex;">&larr; Back</a>
    <div class="ah-card">
      <div class="ah-card-header">
        <h2><?php echo $item ? 'Edit Page' : 'Add New Page'; ?></h2>
      </div>
      <form method="post">
        <?php wp_nonce_field( 'ah_save_page', 'ah_pages_nonce' ); ?>
        <div class="ah-form-row">
          <label>Title *</label>
          <input type="text" name="title" value="<?php echo esc_attr( $item->title ?? '' ); ?>" class="ah-generate-slug-source" data-slug-target="#ah-slug" required>
        </div>
        <div class="ah-form-row">
          <label>Slug</label>
          <input type="text" name="slug" id="ah-slug" value="<?php echo esc_attr( $item->slug ?? '' ); ?>" class="ah-slug-field">
        </div>
        <div class="ah-form-row">
          <label>Page Type</label>
          <select name="page_type">
            <?php foreach ( $page_types as $pt ) : ?>
              <option value="<?php echo esc_attr( $pt ); ?>" <?php selected( $item->page_type ?? 'custom', $pt ); ?>><?php echo esc_html( str_replace( '_', ' ', ucfirst( $pt ) ) ); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="ah-form-row">
          <label>Meta Title</label>
          <input type="text" name="meta_title" value="<?php echo esc_attr( $item->meta_title ?? '' ); ?>">
        </div>
        <div class="ah-form-row">
          <label>Meta Description</label>
          <textarea name="meta_description" rows="3"><?php echo esc_textarea( $item->meta_description ?? '' ); ?></textarea>
        </div>
        <div class="ah-form-row">
          <label>Meta Keywords</label>
          <input type="text" name="meta_keywords" value="<?php echo esc_attr( $item->meta_keywords ?? '' ); ?>">
        </div>
        <div class="ah-form-row">
          <label>Status</label>
          <select name="status">
            <option value="active" <?php selected( $item->status ?? 'active', 'active' ); ?>>Active</option>
            <option value="inactive" <?php selected( $item->status ?? '', 'inactive' ); ?>>Inactive</option>
            <option value="draft" <?php selected( $item->status ?? '', 'draft' ); ?>>Draft</option>
          </select>
        </div>
        <button type="submit" class="ah-btn ah-btn-primary">Save Page</button>
      </form>
    </div>
  <?php endif; ?>
</div>
