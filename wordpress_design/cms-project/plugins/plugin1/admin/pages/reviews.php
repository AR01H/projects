<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$model   = new AH_Reviews_Model();
$ct_model = new AH_Content_Taxonomy_Model();
$notice  = '';
$n_type  = 'success';
$action  = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id = (int) ( $_GET['id'] ?? 0 );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	if ( ! wp_verify_nonce( $_POST['ah_reviews_nonce'] ?? '', 'ah_save_review' ) ) wp_die( 'Security.' );

	$img_id = (int) ( $_POST['reviewer_image_id'] ?? 0 );

	$data = array(
		'reviewer_name'  => sanitize_text_field( $_POST['reviewer_name'] ?? '' ),
		'reviewer_title' => sanitize_text_field( $_POST['reviewer_title'] ?? '' ),
		'short_desc'     => sanitize_text_field( $_POST['short_desc'] ?? '' ),
		'review_text'    => wp_kses_post( $_POST['review_text'] ?? '' ),
		'rating'         => min( 5, max( 1, (int) ( $_POST['rating'] ?? 5 ) ) ),
		'source'         => sanitize_key( $_POST['source'] ?? 'manual' ),
		'is_featured'    => (int) ( $_POST['is_featured'] ?? 0 ),
		'sort_order'     => (int) ( $_POST['sort_order'] ?? 0 ),
		'status'         => sanitize_key( $_POST['status'] ?? 'active' ),
	);

	// Store WP attachment ID directly - FK to ah_media dropped in maybe_upgrade
	if ( $img_id ) {
		$data['reviewer_image_id'] = $img_id;
	}

	if ( $edit_id ) {
		$model->update( $edit_id, $data );
		$saved_id = $edit_id;
	} else {
		$saved_id = $model->create( $data );
	}

	// Save taxonomy terms
	$taxonomy_ids = array_map( 'absint', (array) ( $_POST['taxonomy_ids'] ?? [] ) );
	$ct_model->sync_terms( 'review', (int) $saved_id, $taxonomy_ids );

	// Save occasion images
	$raw_img_ids = array_map( 'absint', (array) ( $_POST['review_image_ids'] ?? [] ) );
	$model->save_images( (int) $saved_id, array_filter( $raw_img_ids ) );

	$notice = 'Review saved.';
	$action = 'list';
	$edit_id = 0;
}

// Delete a single occasion image row
if ( isset( $_GET['delete_rv_img'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_del_rv_img' ) ) {
	$model->delete_image( (int) $_GET['delete_rv_img'] );
	wp_safe_redirect( add_query_arg( [ 'page' => 'ah-reviews', 'action' => 'edit', 'id' => (int) ( $_GET['review_id'] ?? 0 ), 'img_deleted' => 1 ], admin_url( 'admin.php' ) ) );
	exit;
}

if ( isset( $_GET['delete_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_del_review' ) ) {
	$model->delete( (int) $_GET['delete_id'] );
	$notice = 'Review deleted.';
}
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-star-filled"></span> Reviews &amp; Testimonials</h1>
  <?php if ( $notice ) : ?><div class="ah-notice ah-notice-<?php echo esc_attr( $n_type ); ?>"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

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
        <select name="status">
          <option value="">All Status</option>
          <option value="active" <?php selected( $status, 'active' ); ?>>Active</option>
          <option value="inactive" <?php selected( $status, 'inactive' ); ?>>Inactive</option>
        </select>
        <button class="ah-btn ah-btn-secondary">Filter</button>
      </form>
      <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-reviews', 'action' => 'add' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-primary">+ Add Review</a>
    </div>

    <div class="ah-table-wrap">
      <table class="ah-table ah-sortable-list" data-model="reviews">
        <thead>
          <tr><th></th><th>Reviewer</th><th>Review</th><th>Rating</th><th>Type</th><th>Source</th><th>Featured</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if ( empty( $items ) ) : ?>
            <tr><td colspan="9" style="text-align:center;color:var(--ah-muted);padding:32px;">No reviews yet. Click "+ Add Review" to add one.</td></tr>
          <?php endif; ?>
          <?php foreach ( $items as $rv ) :
            $img_url = $rv->reviewer_image_id ? wp_get_attachment_image_url( (int) $rv->reviewer_image_id, 'thumbnail' ) : '';
          ?>
            <tr data-id="<?php echo esc_attr( $rv->id ); ?>">
              <td class="ah-sort-handle">&#9776;</td>
              <td>
                <?php if ( $img_url ) : ?>
                  <img src="<?php echo esc_url( $img_url ); ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;vertical-align:middle;margin-right:8px;">
                <?php endif; ?>
                <strong><?php echo esc_html( $rv->reviewer_name ); ?></strong>
                <?php if ( $rv->reviewer_title ) : ?>
                  <br><small style="color:var(--ah-muted);"><?php echo esc_html( $rv->reviewer_title ); ?></small>
                <?php endif; ?>
              </td>
              <td style="max-width:260px;"><small style="color:var(--ah-muted);"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $rv->review_text ), 12 ) ); ?></small></td>
              <td class="ah-stars"><?php echo str_repeat( '★', (int) $rv->rating ) . str_repeat( '☆', 5 - (int) $rv->rating ); ?></td>
              <td><?php $ct_model->render_badges( 'review', (int) $rv->id ); ?></td>
              <td><?php echo esc_html( $rv->source ); ?></td>
              <td><?php echo $rv->is_featured ? '<span class="ah-badge ah-badge-active">Yes</span>' : '-'; ?></td>
              <td><span class="ah-badge ah-badge-<?php echo esc_attr( $rv->status ); ?>"><?php echo esc_html( $rv->status ); ?></span></td>
              <td class="row-actions">
                <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-reviews', 'action' => 'edit', 'id' => $rv->id ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>
                <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-reviews', 'delete_id' => $rv->id ), admin_url( 'admin.php' ) ), 'ah_del_review' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm" onclick="return confirm('Delete this review?');">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php echo AH_Pagination::render( $meta ); ?>

  <?php
    if ( isset( $_GET['img_deleted'] ) ) {
      echo '<div class="ah-notice ah-notice-success">Image removed.</div>';
    }
  ?>
  <?php else :
    $item        = $edit_id ? $model->find( $edit_id ) : null;
    $img_id      = $item ? (int) $item->reviewer_image_id : 0;
    $img_url     = $img_id ? wp_get_attachment_image_url( $img_id, 'medium' ) : '';
    $occ_images  = $edit_id ? $model->get_images( $edit_id ) : [];
  ?>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-reviews' ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm" style="margin-bottom:16px;display:inline-flex;">&larr; Back to Reviews</a>

    <form method="post">
      <?php wp_nonce_field( 'ah_save_review', 'ah_reviews_nonce' ); ?>
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">

        <!-- Left column: main content -->
        <div>
          <div class="ah-card">
            <div class="ah-card-header"><h2>Reviewer Details</h2></div>
            <div class="ah-form-row">
              <label>Reviewer Name *</label>
              <input type="text" name="reviewer_name" value="<?php echo esc_attr( $item->reviewer_name ?? '' ); ?>" required>
            </div>
            <div class="ah-form-row">
              <label>Title / Company <small style="font-weight:400;color:var(--ah-muted);">(e.g. "First-Time Buyer" or "Google Review")</small></label>
              <input type="text" name="reviewer_title" value="<?php echo esc_attr( $item->reviewer_title ?? '' ); ?>">
            </div>
            <div class="ah-form-row">
              <label>Mini Description <small style="font-weight:400;color:var(--ah-muted);">(short bio or context - shown below name)</small></label>
              <input type="text" name="short_desc" value="<?php echo esc_attr( $item->short_desc ?? '' ); ?>" placeholder="e.g. Bought first home in London, 2024">
            </div>
          </div>

          <div class="ah-card">
            <div class="ah-card-header"><h2>Review Text</h2></div>
            <?php
            wp_editor(
              $item->review_text ?? '',
              'review_text',
              array(
                'textarea_name' => 'review_text',
                'editor_height' => 300,
                'media_buttons' => false,
                'teeny'         => false,
                'tinymce'       => array(
                  'toolbar1' => 'bold,italic,underline,blockquote,bullist,numlist,link,unlink,undo,redo',
                  'toolbar2' => '',
                ),
                'quicktags'     => true,
              )
            );
            ?>
          </div>

          <!-- ── Occasion / Gallery Images ──────────────────────────────── -->
          <div class="ah-card">
            <div class="ah-card-header" style="display:flex;align-items:center;justify-content:space-between;">
              <h2 style="margin:0;">Occasion Images</h2>
              <button type="button" id="rv-add-images" class="ah-btn ah-btn-secondary ah-btn-sm">
                <span class="dashicons dashicons-plus-alt2" style="font-size:15px;line-height:1.4;"></span> Add Images
              </button>
            </div>
            <p style="font-size:12px;color:var(--ah-muted);margin:0 0 14px;">
              Add photos from the occasion — wedding, event, party etc. Drag to reorder.
            </p>

            <div id="rv-gallery" style="display:flex;flex-wrap:wrap;gap:12px;min-height:60px;">
              <?php foreach ( $occ_images as $occ ) :
                $occ_url = wp_get_attachment_image_url( (int) $occ->image_id, 'thumbnail' );
                if ( ! $occ_url ) continue;
                $del_url = wp_nonce_url(
                  add_query_arg( [ 'page' => 'ah-reviews', 'action' => 'edit', 'id' => $edit_id, 'delete_rv_img' => $occ->id, 'review_id' => $edit_id ], admin_url( 'admin.php' ) ),
                  'ah_del_rv_img'
                );
              ?>
                <div class="rv-img-tile" style="position:relative;width:90px;cursor:move;">
                  <input type="hidden" name="review_image_ids[]" value="<?php echo esc_attr( $occ->image_id ); ?>">
                  <img src="<?php echo esc_url( $occ_url ); ?>" style="width:90px;height:90px;object-fit:cover;border-radius:6px;border:2px solid var(--ah-border,#e0e0e0);display:block;">
                  <a href="<?php echo esc_url( $del_url ); ?>"
                     onclick="return confirm('Remove this image?')"
                     style="position:absolute;top:-6px;right:-6px;background:#e53e3e;color:#fff;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:12px;line-height:1;text-decoration:none;"
                     title="Remove">✕</a>
                </div>
              <?php endforeach; ?>
            </div>

            <?php if ( ! $edit_id ) : ?>
              <p style="font-size:12px;color:var(--ah-muted);margin-top:10px;font-style:italic;">Save the review first, then add occasion images.</p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Right column: settings -->
        <div>
          <div class="ah-card">
            <div class="ah-card-header"><h2>Reviewer Photo</h2></div>
            <div class="ah-image-picker">
              <img src="<?php echo esc_url( $img_url ); ?>"
                   class="ah-image-preview <?php echo $img_url ? 'visible' : ''; ?>"
                   alt=""
                   style="width:100px;height:100px;border-radius:50%;object-fit:cover;display:block;margin-bottom:12px;">
              <div class="ah-image-picker-btns">
                <input type="hidden" class="ah-image-id" name="reviewer_image_id" value="<?php echo esc_attr( $img_id ); ?>">
                <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-pick-image">Choose Photo</button>
                <button type="button" class="ah-btn ah-btn-sm ah-remove-image" style="color:var(--ah-danger);">Remove</button>
              </div>
            </div>
          </div>

          <div class="ah-card">
            <div class="ah-card-header"><h2>Review Type</h2></div>
            <p style="font-size:12px;color:var(--ah-muted);margin:0 0 12px;">Tag this review as Customer, Partner, or Event so it appears in the right section on the website.</p>
            <?php $ct_model->render_picker( 'review', $edit_id ); ?>
          </div>

          <div class="ah-card">
            <div class="ah-card-header"><h2>Review Settings</h2></div>
            <div class="ah-form-row">
              <label>Rating</label>
              <select name="rating">
                <?php for ( $r = 5; $r >= 1; $r-- ) : ?>
                  <option value="<?php echo $r; ?>" <?php selected( (int) ( $item->rating ?? 5 ), $r ); ?>><?php echo str_repeat( '★', $r ) . str_repeat( '☆', 5 - $r ); ?> (<?php echo $r; ?>/5)</option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="ah-form-row">
              <label>Source</label>
              <select name="source">
                <?php foreach ( array( 'manual' => 'Manual / Direct', 'google' => 'Google', 'facebook' => 'Facebook', 'other' => 'Other' ) as $src => $lbl ) : ?>
                  <option value="<?php echo esc_attr( $src ); ?>" <?php selected( $item->source ?? 'manual', $src ); ?>><?php echo esc_html( $lbl ); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="ah-form-row">
              <label>Featured</label>
              <select name="is_featured">
                <option value="0" <?php selected( (int) ( $item->is_featured ?? 0 ), 0 ); ?>>No</option>
                <option value="1" <?php selected( (int) ( $item->is_featured ?? 0 ), 1 ); ?>>Yes - show on homepage</option>
              </select>
            </div>
            <div class="ah-form-row">
              <label>Sort Order</label>
              <input type="number" name="sort_order" value="<?php echo esc_attr( $item->sort_order ?? 0 ); ?>" min="0">
            </div>
            <div class="ah-form-row">
              <label>Status</label>
              <select name="status">
                <option value="active" <?php selected( $item->status ?? 'active', 'active' ); ?>>Active</option>
                <option value="inactive" <?php selected( $item->status ?? '', 'inactive' ); ?>>Inactive</option>
              </select>
            </div>
            <button type="submit" class="ah-btn ah-btn-primary" style="width:100%;justify-content:center;margin-top:8px;">
              <span class="dashicons dashicons-saved"></span> <?php echo $item ? 'Update Review' : 'Save Review'; ?>
            </button>
          </div>
        </div>

      </div>
    </form>

    <?php if ( $edit_id ) : ?>
    <script>
    jQuery(function($){
      var gallery   = document.getElementById('rv-gallery');
      var addBtn    = document.getElementById('rv-add-images');
      if ( ! addBtn || ! gallery ) return;

      // Sortable
      $(gallery).sortable({ items: '.rv-img-tile', tolerance: 'pointer' });

      // WP Media frame (multi-select)
      var frame;
      addBtn.addEventListener('click', function(){
        if ( frame ) { frame.open(); return; }
        frame = wp.media({
          title    : 'Select Occasion Images',
          button   : { text: 'Add Selected' },
          multiple : true,
          library  : { type: 'image' }
        });
        frame.on('select', function(){
          var attachments = frame.state().get('selection').toJSON();
          attachments.forEach(function(att){
            // Skip if already in the gallery
            if ( gallery.querySelector('input[value="'+att.id+'"]') ) return;

            var thumb = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
            var delConfirm = 'Remove this image?';

            var tile = document.createElement('div');
            tile.className = 'rv-img-tile';
            tile.style.cssText = 'position:relative;width:90px;cursor:move;';
            tile.innerHTML =
              '<input type="hidden" name="review_image_ids[]" value="' + att.id + '">' +
              '<img src="' + thumb + '" style="width:90px;height:90px;object-fit:cover;border-radius:6px;border:2px solid var(--ah-border,#e0e0e0);display:block;">' +
              '<button type="button" class="rv-remove-new" title="Remove" style="position:absolute;top:-6px;right:-6px;background:#e53e3e;color:#fff;border-radius:50%;width:20px;height:20px;border:none;cursor:pointer;font-size:12px;line-height:1;padding:0;display:flex;align-items:center;justify-content:center;">✕</button>';

            tile.querySelector('.rv-remove-new').addEventListener('click', function(){
              tile.parentNode.removeChild(tile);
            });

            gallery.appendChild(tile);
          });
        });
        frame.open();
      });
    });
    </script>
    <?php endif; ?>

  <?php endif; ?>
</div>
