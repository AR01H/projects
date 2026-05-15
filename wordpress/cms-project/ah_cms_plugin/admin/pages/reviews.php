<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$model   = new AH_Reviews_Model();
$media_m = new AH_Media_Model();
$notice  = '';
$action  = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id = (int) ( $_GET['id'] ?? 0 );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	if ( ! wp_verify_nonce( $_POST['ah_reviews_nonce'] ?? '', 'ah_save_review' ) ) wp_die( 'Security.' );
	$data = array(
		'reviewer_name'     => sanitize_text_field( $_POST['reviewer_name'] ?? '' ),
		'reviewer_title'    => sanitize_text_field( $_POST['reviewer_title'] ?? '' ),
		'reviewer_image_id' => (int) ( $_POST['reviewer_image_id'] ?? 0 ) ?: null,
		'review_text'       => sanitize_textarea_field( $_POST['review_text'] ?? '' ),
		'rating'            => min( 5, max( 1, (int) ( $_POST['rating'] ?? 5 ) ) ),
		'source'            => sanitize_key( $_POST['source'] ?? 'manual' ),
		'is_featured'       => (int) ( $_POST['is_featured'] ?? 0 ),
		'sort_order'        => (int) ( $_POST['sort_order'] ?? 0 ),
		'status'            => sanitize_key( $_POST['status'] ?? 'active' ),
		'created_by'        => get_current_user_id() ?: null,
	);
	$edit_id ? $model->update( $edit_id, $data ) : $model->create( $data );
	$notice = 'Review saved.';
	$action = 'list';
}

if ( isset( $_GET['delete_id'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'ah_del_review' ) ) {
	$model->delete( (int) $_GET['delete_id'] );
	$notice = 'Review deleted.';
}
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-star-filled"></span> <?php esc_html_e( 'Reviews & Testimonials', 'ah-theme' ); ?></h1>
  <?php if ( $notice ) : ?><div class="ah-notice ah-notice-success"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

  <?php if ( $action === 'list' ) :
    $search = sanitize_text_field( $_GET['s'] ?? '' );
    $status = sanitize_key( $_GET['status'] ?? '' );
    $paged  = AH_Pagination::current_page();
    $result = $model->get_paginated( $paged, $search, $status );
    $items  = $result['items']; $meta = $result['meta'];
  ?>
    <div class="ah-table-top">
      <form class="ah-search-form" method="get">
        <input type="hidden" name="page" value="ah-reviews">
        <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search reviews…">
        <select name="status"><option value="">All Status</option><option value="active" <?php selected( $status, 'active' ); ?>>Active</option><option value="inactive" <?php selected( $status, 'inactive' ); ?>>Inactive</option></select>
        <button class="ah-btn ah-btn-secondary">Filter</button>
      </form>
      <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-reviews', 'action' => 'add' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-primary">+ Add Review</a>
    </div>
    <div class="ah-table-wrap">
      <table class="ah-table ah-sortable-list" data-model="reviews">
        <thead><tr><th></th><th>Reviewer</th><th>Rating</th><th>Source</th><th>Featured</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ( $items as $rv ) : ?>
            <tr data-id="<?php echo esc_attr( $rv->id ); ?>">
              <td class="ah-sort-handle">&#9776;</td>
              <td>
                <strong><?php echo esc_html( $rv->reviewer_name ); ?></strong>
                <?php if ( $rv->reviewer_title ) : ?><br><small style="color:var(--ah-muted);"><?php echo esc_html( $rv->reviewer_title ); ?></small><?php endif; ?>
              </td>
              <td class="ah-stars"><?php echo str_repeat( '★', (int) $rv->rating ) . str_repeat( '☆', 5 - (int) $rv->rating ); ?></td>
              <td><?php echo esc_html( $rv->source ); ?></td>
              <td><?php echo $rv->is_featured ? '<span class="ah-badge ah-badge-active">Yes</span>' : '-'; ?></td>
              <td><span class="ah-badge ah-badge-<?php echo esc_attr( $rv->status ); ?>"><?php echo esc_html( $rv->status ); ?></span></td>
              <td class="row-actions">
                <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-reviews', 'action' => 'edit', 'id' => $rv->id ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>
                <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-reviews', 'delete_id' => $rv->id ), admin_url( 'admin.php' ) ), 'ah_del_review' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm" onclick="return confirm('Delete?');">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php echo AH_Pagination::render( $meta ); ?>

  <?php else :
    $item    = $edit_id ? $model->find( $edit_id ) : null;
    $img_url = $item && $item->reviewer_image_id ? $media_m->get_url( (int) $item->reviewer_image_id ) : '';
  ?>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-reviews' ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm" style="margin-bottom:14px;display:inline-flex;">&larr; Back</a>
    <div class="ah-card">
      <div class="ah-card-header"><h2><?php echo $item ? 'Edit Review' : 'Add Review'; ?></h2></div>
      <form method="post">
        <?php wp_nonce_field( 'ah_save_review', 'ah_reviews_nonce' ); ?>
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
          <div>
            <div class="ah-form-row"><label>Reviewer Name *</label><input type="text" name="reviewer_name" value="<?php echo esc_attr( $item->reviewer_name ?? '' ); ?>" required></div>
            <div class="ah-form-row"><label>Title / Company</label><input type="text" name="reviewer_title" value="<?php echo esc_attr( $item->reviewer_title ?? '' ); ?>"></div>
            <div class="ah-form-row"><label>Review Text *</label><textarea name="review_text" rows="6" required><?php echo esc_textarea( $item->review_text ?? '' ); ?></textarea></div>
            <div class="ah-form-row">
              <label>Rating</label>
              <select name="rating"><?php for ( $r = 5; $r >= 1; $r-- ) : ?><option value="<?php echo $r; ?>" <?php selected( $item->rating ?? 5, $r ); ?>><?php echo str_repeat( '★', $r ); ?></option><?php endfor; ?></select>
            </div>
            <div class="ah-form-row">
              <label>Source</label>
              <select name="source">
                <?php foreach ( array( 'manual', 'google', 'facebook', 'other' ) as $src ) : ?>
                  <option value="<?php echo $src; ?>" <?php selected( $item->source ?? 'manual', $src ); ?>><?php echo ucfirst( $src ); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div>
            <div class="ah-form-row">
              <label>Photo</label>
              <div class="ah-image-picker">
                <img src="<?php echo esc_url( $img_url ); ?>" class="ah-image-preview <?php echo $img_url ? 'visible' : ''; ?>" alt="" style="width:80px;height:80px;border-radius:50%;object-fit:cover;">
                <div class="ah-image-picker-btns">
                  <input type="hidden" class="ah-image-id" name="reviewer_image_id" value="<?php echo esc_attr( $item->reviewer_image_id ?? 0 ); ?>">
                  <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-pick-image">Choose Photo</button>
                  <button type="button" class="ah-btn ah-btn-sm ah-remove-image" style="color:var(--ah-danger);">Remove</button>
                </div>
              </div>
            </div>
            <div class="ah-form-row">
              <label>Featured</label>
              <select name="is_featured"><option value="0" <?php selected( $item->is_featured ?? 0, 0 ); ?>>No</option><option value="1" <?php selected( $item->is_featured ?? 0, 1 ); ?>>Yes</option></select>
            </div>
            <div class="ah-form-row"><label>Sort Order</label><input type="number" name="sort_order" value="<?php echo esc_attr( $item->sort_order ?? 0 ); ?>"></div>
            <div class="ah-form-row">
              <label>Status</label>
              <select name="status"><option value="active" <?php selected( $item->status ?? 'active', 'active' ); ?>>Active</option><option value="inactive" <?php selected( $item->status ?? '', 'inactive' ); ?>>Inactive</option></select>
            </div>
          </div>
        </div>
        <button type="submit" class="ah-btn ah-btn-primary">Save Review</button>
      </form>
    </div>
  <?php endif; ?>
</div>
