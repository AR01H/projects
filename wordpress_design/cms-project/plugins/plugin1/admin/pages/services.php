<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$model   = new AH_Services_Model();
$pages_m = new AH_Pages_Model();
$notice  = '';
$action  = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id = (int) ( $_GET['id'] ?? 0 );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	if ( ! wp_verify_nonce( $_POST['ah_services_nonce'] ?? '', 'ah_save_service' ) ) wp_die( 'Security.' );

	if ( isset( $_POST['save_service'] ) ) {
		$data = array(
			'title'            => sanitize_text_field( $_POST['title'] ?? '' ),
			'slug'             => AH_Slug_Helper::generate( $_POST['slug'] ?: $_POST['title'], AH_DB_Helper::table( 'services' ), 'slug', $edit_id ),
			'short_desc'       => sanitize_textarea_field( $_POST['short_desc'] ?? '' ),
			'full_desc'        => wp_kses_post( $_POST['full_desc'] ?? '' ),
			'image_id'         => (int) ( $_POST['image_id'] ?? 0 ) ?: null,
			'sort_order'       => (int) ( $_POST['sort_order'] ?? 0 ),
			'meta_title'       => sanitize_text_field( $_POST['meta_title'] ?? '' ),
			'meta_description' => sanitize_textarea_field( $_POST['meta_description'] ?? '' ),
			'status'           => sanitize_key( $_POST['status'] ?? 'active' ),
			'created_by'       => get_current_user_id() ?: null,
		);
		if ( $edit_id ) {
			$model->update( $edit_id, $data );
			$saved_id = $edit_id;
		} else {
			$saved_id = $model->create( $data );
		}
		// Save bullet points
		$points = array_filter( array_map( 'sanitize_text_field', $_POST['bullet_points'] ?? array() ) );
		$model->save_bullet_points( (int) $saved_id, $points );
		$model->sync_taxonomies( (int) $saved_id, $_POST['taxonomy_ids'] ?? array() );
		$notice = 'Service saved.';
		$action = 'list';
	}

	if ( isset( $_POST['save_page_header'] ) ) {
		$page_id = (int) ( $_POST['page_id'] ?? 0 );
		$model->save_page_header( $page_id, array(
			'heading'    => sanitize_text_field( $_POST['heading'] ?? '' ),
			'information'=> sanitize_textarea_field( $_POST['information'] ?? '' ),
			'is_visible' => (int) ( $_POST['is_visible'] ?? 1 ),
		) );
		$notice = 'Services page header saved.';
	}
}

if ( isset( $_GET['delete_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_del_service' ) ) {
	$model->delete( (int) $_GET['delete_id'] );
	$notice = 'Service deleted.';
}
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-hammer"></span> <?php esc_html_e( 'Services', 'ah-theme' ); ?></h1>
  <?php if ( $notice ) : ?><div class="ah-notice ah-notice-success"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

  <?php if ( $action === 'list' ) :
    $search = sanitize_text_field( $_GET['s'] ?? '' );
    $paged  = AH_Pagination::current_page();
    $result = $model->get_paginated( $paged, $search );
    $items  = $result['items']; $meta = $result['meta'];
    $srv_page = $pages_m->get_by_type( 'services' );
    $header   = $srv_page ? $model->get_page_header( (int) $srv_page->id ) : null;
  ?>

    <!-- Services Page Header -->
    <?php if ( $srv_page ) : ?>
    <div class="ah-card" style="margin-bottom:20px;">
      <div class="ah-card-header"><h2>Services Page Header</h2></div>
      <form method="post">
        <?php wp_nonce_field( 'ah_save_service', 'ah_services_nonce' ); ?>
        <input type="hidden" name="page_id" value="<?php echo esc_attr( $srv_page->id ); ?>">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="ah-form-row"><label>Heading</label><input type="text" name="heading" value="<?php echo esc_attr( $header->heading ?? '' ); ?>"></div>
          <div class="ah-form-row">
            <label>Visible</label>
            <select name="is_visible"><option value="1" <?php selected( $header->is_visible ?? 1, 1 ); ?>>Yes</option><option value="0" <?php selected( $header->is_visible ?? 1, 0 ); ?>>No</option></select>
          </div>
        </div>
        <div class="ah-form-row"><label>Information</label><textarea name="information" rows="3"><?php echo esc_textarea( $header->information ?? '' ); ?></textarea></div>
        <button type="submit" name="save_page_header" value="1" class="ah-btn ah-btn-primary ah-btn-sm">Save Header</button>
      </form>
    </div>
    <?php endif; ?>

    <!-- Services List -->
    <div class="ah-table-top">
      <form class="ah-search-form" method="get">
        <input type="hidden" name="page" value="ah-services">
        <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search services…">
        <button class="ah-btn ah-btn-secondary">Search</button>
      </form>
      <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-services', 'action' => 'add' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-primary">+ Add Service</a>
    </div>
    <div class="ah-table-wrap">
      <table class="ah-table ah-sortable-list" data-model="services">
        <thead><tr><th></th><th>Title</th><th>Slug</th><th>CMS Terms</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ( $items as $svc ) : ?>
            <tr data-id="<?php echo esc_attr( $svc->id ); ?>">
              <td class="ah-sort-handle">&#9776;</td>
              <td><strong><?php echo esc_html( $svc->title ); ?></strong></td>
              <td><code><?php echo esc_html( $svc->slug ); ?></code></td>
              <td><?php ( new AH_Content_Taxonomy_Model() )->render_badges( 'service', (int) $svc->id ); ?></td>
              <td><span class="ah-badge ah-badge-<?php echo esc_attr( $svc->status ); ?>"><?php echo esc_html( $svc->status ); ?></span></td>
              <td class="row-actions">
                <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-services', 'action' => 'edit', 'id' => $svc->id ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>
                <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-services', 'delete_id' => $svc->id ), admin_url( 'admin.php' ) ), 'ah_del_service' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm" onclick="return confirm('Delete?');">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php echo AH_Pagination::render( $meta ); ?>

  <?php else :
    $item   = $edit_id ? $model->find( $edit_id ) : null;
    $bullets = $edit_id ? $model->get_bullet_points( $edit_id ) : array();
    $img_url = $item && $item->image_id ? ( wp_get_attachment_image_url( (int) $item->image_id, 'medium' ) ?: '' ) : '';
  ?>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-services' ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm" style="margin-bottom:14px;display:inline-flex;">&larr; Back</a>
    <div class="ah-card">
      <div class="ah-card-header"><h2><?php echo $item ? 'Edit Service' : 'Add Service'; ?></h2></div>
      <form method="post">
        <?php wp_nonce_field( 'ah_save_service', 'ah_services_nonce' ); ?>
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
          <div>
            <div class="ah-form-row"><label>Title *</label><input type="text" name="title" value="<?php echo esc_attr( $item->title ?? '' ); ?>" class="ah-generate-slug-source" data-slug-target="#svc-slug" required></div>
            <div class="ah-form-row"><label>Slug</label><input type="text" name="slug" id="svc-slug" value="<?php echo esc_attr( $item->slug ?? '' ); ?>" class="ah-slug-field"></div>
            <div class="ah-form-row"><label>Short Description</label><textarea name="short_desc" rows="3"><?php echo esc_textarea( $item->short_desc ?? '' ); ?></textarea></div>
            <div class="ah-form-row"><label>Full Description</label><?php
              wp_editor( $item->full_desc ?? '', 'full_desc', array( 'textarea_name' => 'full_desc', 'media_buttons' => false, 'teeny' => true, 'editor_height' => 200 ) );
            ?></div>
            <div class="ah-form-row">
              <label>Bullet Points</label>
              <div class="ah-repeater-container" id="bullet-points-container">
                <?php if ( $bullets ) : foreach ( $bullets as $i => $bp ) : ?>
                  <div class="ah-repeater-item" style="display:flex;gap:8px;align-items:center;padding:8px 12px;margin-bottom:6px;">
                    <span class="ah-repeater-handle ah-sort-handle">&#9776;</span>
                    <input type="text" name="bullet_points[]" value="<?php echo esc_attr( $bp->point_text ); ?>" style="flex:1;" placeholder="Bullet point text">
                    <button type="button" class="ah-repeater-remove">✕</button>
                  </div>
                <?php endforeach; else : ?>
                  <div class="ah-repeater-item" style="display:flex;gap:8px;align-items:center;padding:8px 12px;margin-bottom:6px;">
                    <span class="ah-repeater-handle ah-sort-handle">&#9776;</span>
                    <input type="text" name="bullet_points[]" value="" style="flex:1;" placeholder="Bullet point text">
                    <button type="button" class="ah-repeater-remove">✕</button>
                  </div>
                <?php endif; ?>
              </div>
              <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-add-repeater">+ Add Bullet Point</button>
            </div>
          </div>
          <div>
            <div class="ah-form-row">
              <label>Featured Image</label>
              <div class="ah-image-picker">
                <img src="<?php echo esc_url( $img_url ); ?>" class="ah-image-preview <?php echo $img_url ? 'visible' : ''; ?>" alt="">
                <div class="ah-image-picker-btns">
                  <input type="hidden" class="ah-image-id" name="image_id" value="<?php echo esc_attr( $item->image_id ?? 0 ); ?>">
                  <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-pick-image">Choose Image</button>
                  <button type="button" class="ah-btn ah-btn-sm ah-remove-image" style="color:var(--ah-danger);">Remove</button>
                </div>
              </div>
            </div>
            <div class="ah-form-row">
              <label>Taxonomy Terms</label>
              <?php ( new AH_Content_Taxonomy_Model() )->render_picker( 'service', $edit_id ); ?>
            </div>
            <div class="ah-form-row"><label>Sort Order</label><input type="number" name="sort_order" value="<?php echo esc_attr( $item->sort_order ?? 0 ); ?>"></div>
            <div class="ah-form-row">
              <label>Status</label>
              <select name="status">
                <option value="active" <?php selected( $item->status ?? 'active', 'active' ); ?>>Active</option>
                <option value="inactive" <?php selected( $item->status ?? '', 'inactive' ); ?>>Inactive</option>
              </select>
            </div>
            <div class="ah-form-row"><label>Meta Title</label><input type="text" name="meta_title" value="<?php echo esc_attr( $item->meta_title ?? '' ); ?>"></div>
            <div class="ah-form-row"><label>Meta Description</label><textarea name="meta_description" rows="3"><?php echo esc_textarea( $item->meta_description ?? '' ); ?></textarea></div>
          </div>
        </div>
        <button type="submit" name="save_service" value="1" class="ah-btn ah-btn-primary">Save Service</button>
      </form>
    </div>
  <?php endif; ?>
</div>
