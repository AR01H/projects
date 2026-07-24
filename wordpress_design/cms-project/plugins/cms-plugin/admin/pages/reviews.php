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
<?php if ( $action === 'list' ) :
    $search = sanitize_text_field( $_GET['s'] ?? '' );
    $status = sanitize_key( $_GET['status'] ?? '' );
    $paged  = AH_Pagination::current_page();
    $result = $model->get_paginated( $paged, $search, $status );
    $items  = $result['items']; $meta = $result['meta'];

    \Ah\Cms\Admin\Components\AdminComponents::listPage( array(
      'icon'        => 'star-filled',
      'title'       => 'Reviews & Testimonials',
      'description' => 'Collect and display client reviews and testimonials.',
      'notice'      => $notice,
      'notice_type' => $n_type,
      'filter_bar'  => array(
        'page_slug'          => 'ah-reviews',
        'search_placeholder' => 'Search reviews…',
        'search_value'       => $search,
        'filters'            => array(
          array(
            'name'     => 'status',
            'options'  => array(
              ''        => 'All Status',
              'active'  => 'Active',
              'inactive' => 'Inactive',
            ),
            'selected' => $status,
          ),
        ),
        'add_url'   => add_query_arg( array( 'page' => 'ah-reviews', 'action' => 'add' ), admin_url( 'admin.php' ) ),
        'add_label' => '+ Add Review',
      ),
      'table' => array(
        'columns' => array(
          array( 'label' => 'Reviewer', 'render' => function ( $rv ) {
            $img_url = $rv->reviewer_image_id ? wp_get_attachment_image_url( (int) $rv->reviewer_image_id, 'thumbnail' ) : '';
            $html = '';
            if ( $img_url ) {
              $html .= '<img src="' . esc_url( $img_url ) . '" style="width:36px;height:36px;border-radius:50%;object-fit:cover;vertical-align:middle;margin-right:8px;">';
            }
            $html .= '<strong>' . esc_html( $rv->reviewer_name ) . '</strong>';
            if ( $rv->reviewer_title ) {
              $html .= '<br><small style="color:var(--ah-muted);">' . esc_html( $rv->reviewer_title ) . '</small>';
            }
            return $html;
          } ),
          array( 'label' => 'Review', 'style' => 'max-width:260px;', 'render' => function ( $rv ) {
            return '<small style="color:var(--ah-muted);">' . esc_html( wp_trim_words( wp_strip_all_tags( $rv->review_text ), 12 ) ) . '</small>';
          } ),
          array( 'label' => 'Rating', 'render' => function ( $rv ) {
            return '<span class="ah-stars">' . str_repeat( '★', (int) $rv->rating ) . str_repeat( '☆', 5 - (int) $rv->rating ) . '</span>';
          } ),
          array( 'label' => 'Type', 'render' => function ( $rv ) use ( $ct_model ) {
            ob_start();
            $ct_model->render_badges( 'review', (int) $rv->id );
            return ob_get_clean();
          } ),
          array( 'key' => 'source', 'label' => 'Source' ),
          array( 'label' => 'Featured', 'render' => function ( $rv ) {
            return $rv->is_featured ? '<span class="ah-badge ah-badge-active">Yes</span>' : '-';
          } ),
          array( 'label' => 'Status', 'render' => function ( $rv ) {
            return \Ah\Cms\Admin\Components\AdminComponents::statusBadge( $rv->status );
          } ),
        ),
        'items'         => $items,
        'sortable'      => true,
        'model'         => 'reviews',
        'empty_message' => 'No reviews yet. Click "+ Add Review" to add one.',
        'actions'       => function ( $rv ) {
          $edit_url = add_query_arg( array( 'page' => 'ah-reviews', 'action' => 'edit', 'id' => $rv->id ), admin_url( 'admin.php' ) );
          $del_url  = add_query_arg( array( 'page' => 'ah-reviews', 'delete_id' => $rv->id ), admin_url( 'admin.php' ) );
          $html = '<a href="' . esc_url( $edit_url ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>';
          ob_start();
          \Ah\Cms\Admin\Components\AdminComponents::confirmDelete( $del_url, 'ah_del_review' );
          $html .= ob_get_clean();
          return $html;
        },
      ),
      'pagination' => $meta,
    ) );

    if ( isset( $_GET['img_deleted'] ) ) {
      \Ah\Cms\Admin\Components\AdminComponents::notice( 'Image removed.', 'success' );
    }
  endif;
  if ( $action !== 'list' ) :
    $item        = $edit_id ? $model->find( $edit_id ) : null;
    $img_id      = $item ? (int) $item->reviewer_image_id : 0;
    $img_url     = $img_id ? wp_get_attachment_image_url( $img_id, 'medium' ) : '';
    $occ_images  = $edit_id ? $model->get_images( $edit_id ) : [];
  ?>
  <div class="wrap ah-wrap">
    <?php \Ah\Cms\Admin\Components\AdminComponents::pageHeader( 'star-filled', 'Reviews & Testimonials', 'Collect and display client reviews and testimonials.' ); ?>
    <?php \Ah\Cms\Admin\Components\AdminComponents::notice( $notice, $n_type ); ?>
    <?php \Ah\Cms\Admin\Components\AdminComponents::backLink( admin_url( 'admin.php?page=ah-reviews' ), '← Back to Reviews' ); ?>

    <form method="post">
      <?php wp_nonce_field( 'ah_save_review', 'ah_reviews_nonce' ); ?>
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">

        <!-- Left column: main content -->
        <div>
          <?php ob_start(); ?>
            <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Reviewer Name *', '<input type="text" name="reviewer_name" value="' . esc_attr( $item->reviewer_name ?? '' ) . '" required>' ); ?>
            <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Title / Company <small style="font-weight:400;color:var(--ah-muted);">(e.g. "First-Time Buyer" or "Google Review")</small>', '<input type="text" name="reviewer_title" value="' . esc_attr( $item->reviewer_title ?? '' ) . '">' ); ?>
            <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Mini Description <small style="font-weight:400;color:var(--ah-muted);">(short bio or context - shown below name)</small>', '<input type="text" name="short_desc" value="' . esc_attr( $item->short_desc ?? '' ) . '" placeholder="e.g. Bought first home in London, 2024">' ); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Reviewer Details', ob_get_clean() ); ?>

          <?php ob_start(); ?>
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
          <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Review Text', ob_get_clean() ); ?>

          <!-- ── Occasion / Gallery Images ──────────────────────────────── -->
          <?php ob_start(); ?>
            <p style="font-size:12px;color:var(--ah-muted);margin:0 0 14px;">
              Add photos from the occasion - wedding, event, party etc. Drag to reorder.
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
                     class="ah-confirm-delete"
                     data-confirm="Remove this image?"
                     style="position:absolute;top:-6px;right:-6px;background:#e53e3e;color:#fff;border-radius:50%;width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:12px;line-height:1;text-decoration:none;"
                     title="Remove">✕</a>
                </div>
              <?php endforeach; ?>
            </div>

            <?php if ( ! $edit_id ) : ?>
              <p style="font-size:12px;color:var(--ah-muted);margin-top:10px;font-style:italic;">Save the review first, then add occasion images.</p>
            <?php endif; ?>
          <?php
          ob_start();
          ?>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
              <div></div>
              <button type="button" id="rv-add-images" class="ah-btn ah-btn-secondary ah-btn-sm">
                <span class="dashicons dashicons-plus-alt2" style="font-size:15px;line-height:1.4;"></span> Add Images
              </button>
            </div>
            <?php echo ob_get_clean(); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Occasion Images', ob_get_clean() ); ?>
        </div>

        <!-- Right column: settings -->
        <div>
          <?php ob_start(); ?>
            <?php \Ah\Cms\Admin\Components\AdminComponents::mediaField( 'reviewer_image_id', 'Reviewer Photo', $img_id, array( 'type' => 'media' ) ); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Reviewer Photo', ob_get_clean() ); ?>

          <?php ob_start(); ?>
            <p style="font-size:12px;color:var(--ah-muted);margin:0 0 12px;">Tag this review as Customer, Partner, or Event so it appears in the right section on the website.</p>
            <?php $ct_model->render_picker( 'review', $edit_id ); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Review Type', ob_get_clean() ); ?>

          <?php ob_start(); ?>
            <?php
            $rating_select = '<select name="rating">';
            for ( $r = 5; $r >= 1; $r-- ) {
              $rating_select .= '<option value="' . $r . '"' . selected( (int) ( $item->rating ?? 5 ), $r, false ) . '>' . str_repeat( '★', $r ) . str_repeat( '☆', 5 - $r ) . ' (' . $r . '/5)</option>';
            }
            $rating_select .= '</select>';
            ?>
            <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Rating', $rating_select ); ?>
            <?php
            $source_select = '<select name="source">';
            foreach ( array( 'manual' => 'Manual / Direct', 'google' => 'Google', 'facebook' => 'Facebook', 'other' => 'Other' ) as $src => $lbl ) {
              $source_select .= '<option value="' . esc_attr( $src ) . '"' . selected( $item->source ?? 'manual', $src, false ) . '>' . esc_html( $lbl ) . '</option>';
            }
            $source_select .= '</select>';
            ?>
            <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Source', $source_select ); ?>
            <?php
            $featured_select = '<select name="is_featured">';
            $featured_select .= '<option value="0"' . selected( (int) ( $item->is_featured ?? 0 ), 0, false ) . '>No</option>';
            $featured_select .= '<option value="1"' . selected( (int) ( $item->is_featured ?? 0 ), 1, false ) . '>Yes - show on homepage</option>';
            $featured_select .= '</select>';
            ?>
            <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Featured', $featured_select ); ?>
            <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Sort Order', '<input type="number" name="sort_order" value="' . esc_attr( $item->sort_order ?? 0 ) . '" min="0">' ); ?>
            <?php
            $status_select = '<select name="status">';
            $status_select .= '<option value="active"' . selected( $item->status ?? 'active', 'active', false ) . '>Active</option>';
            $status_select .= '<option value="inactive"' . selected( $item->status ?? '', 'inactive', false ) . '>Inactive</option>';
            $status_select .= '</select>';
            ?>
            <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Status', $status_select ); ?>
            <button type="submit" class="ah-btn ah-btn-primary" style="width:100%;justify-content:center;margin-top:8px;">
              <span class="dashicons dashicons-saved"></span> <?php echo $item ? 'Update Review' : 'Save Review'; ?>
            </button>
          <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Review Settings', ob_get_clean() ); ?>
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
