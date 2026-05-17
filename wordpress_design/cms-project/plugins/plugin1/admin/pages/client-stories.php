<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$pages_m = new AH_Pages_Model();
$media_m = new AH_Media_Model();
$notice  = '';

$cs_page = $pages_m->get_by_type( 'client_stories' );
$page_id = $cs_page ? (int) $cs_page->id : 0;
$tab     = sanitize_key( $_GET['tab'] ?? 'header' );

global $wpdb;
$cs_header_t = AH_DB_Helper::table( 'client_stories_header' );
$cs_images_t = AH_DB_Helper::table( 'client_story_images' );
$cs_journey_t= AH_DB_Helper::table( 'client_users_journey' );
$cs_gallery_t= AH_DB_Helper::table( 'client_gallery' );
$cs_video_t  = AH_DB_Helper::table( 'client_video_links' );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && wp_verify_nonce( $_POST['ah_cs_nonce'] ?? '', 'ah_save_client_stories' ) ) {

	if ( isset( $_POST['save_header'] ) ) {
		$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$cs_header_t}` WHERE page_id = %d", $page_id ) );
		$data = array( 'page_id' => $page_id, 'heading' => sanitize_text_field( $_POST['heading'] ?? '' ), 'information' => sanitize_textarea_field( $_POST['information'] ?? '' ), 'is_visible' => (int) ( $_POST['is_visible'] ?? 1 ), 'updated_by' => get_current_user_id() ?: null );
		$existing ? $wpdb->update( $cs_header_t, $data, array( 'id' => (int) $existing ) ) : $wpdb->insert( $cs_header_t, $data );
		$notice = 'Header saved.';
	}

	if ( isset( $_POST['save_gallery_item'] ) ) {
		$image_id = (int) ( $_POST['gallery_image_id'] ?? 0 );
		if ( $image_id ) {
			$wpdb->insert( $cs_gallery_t, array(
				'page_id'    => $page_id,
				'image_id'   => $image_id,
				'width_class'=> sanitize_key( $_POST['width_class'] ?? 'medium' ),
				'sort_order' => (int) ( $_POST['sort_order'] ?? 0 ),
				'status'     => 'active',
			) );
			$notice = 'Gallery image added.';
		}
	}

	if ( isset( $_POST['save_video'] ) ) {
		$vid_id = (int) ( $_POST['vid_edit_id'] ?? 0 );
		$vdata  = array(
			'page_id'      => $page_id,
			'heading'      => sanitize_text_field( $_POST['vid_heading'] ?? '' ),
			'video_url'    => esc_url_raw( $_POST['video_url'] ?? '' ),
			'thumbnail_id' => (int) ( $_POST['thumbnail_id'] ?? 0 ) ?: null,
			'sort_order'   => (int) ( $_POST['vid_sort'] ?? 0 ),
			'status'       => 'active',
		);
		$vid_id ? $wpdb->update( $cs_video_t, $vdata, array( 'id' => $vid_id ) ) : $wpdb->insert( $cs_video_t, $vdata );
		$notice = 'Video saved.';
	}
}

// Deletes
if ( isset( $_GET['delete_gallery'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'ah_del_cs_gallery' ) ) {
	$wpdb->delete( $cs_gallery_t, array( 'id' => (int) $_GET['delete_gallery'] ) );
	$notice = 'Gallery image removed.';
}
if ( isset( $_GET['delete_video'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'ah_del_cs_video' ) ) {
	$wpdb->delete( $cs_video_t, array( 'id' => (int) $_GET['delete_video'] ) );
	$notice = 'Video removed.';
}

// Fetch data
$header  = $page_id ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$cs_header_t}` WHERE page_id = %d", $page_id ) ) : null;
$gallery = $page_id ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$cs_gallery_t}` WHERE page_id = %d ORDER BY sort_order ASC", $page_id ) ) : array();
$videos  = $page_id ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$cs_video_t}` WHERE page_id = %d ORDER BY sort_order ASC", $page_id ) ) : array();
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-format-gallery"></span> <?php esc_html_e( 'Client Stories', 'ah-theme' ); ?></h1>
  <?php if ( $notice ) : ?><div class="ah-notice ah-notice-success"><?php echo esc_html( $notice ); ?></div><?php endif; ?>
  <?php if ( ! $page_id ) : ?><div class="ah-notice ah-notice-warning">Client Stories page not found. Create it in Pages Manager first.</div><?php return; endif; ?>

  <div class="ah-tabs">
    <?php foreach ( array( 'header' => 'Page Header', 'gallery' => 'Gallery', 'videos' => 'Video Links' ) as $t => $lbl ) : ?>
      <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-client-stories', 'tab' => $t ), admin_url( 'admin.php' ) ) ); ?>" class="ah-tab <?php echo $tab === $t ? 'active' : ''; ?>"><?php echo esc_html( $lbl ); ?></a>
    <?php endforeach; ?>
  </div>

  <?php if ( $tab === 'header' ) : ?>
    <div class="ah-card">
      <div class="ah-card-header"><h2>Client Stories Page Header</h2></div>
      <form method="post">
        <?php wp_nonce_field( 'ah_save_client_stories', 'ah_cs_nonce' ); ?>
        <div class="ah-form-row"><label>Heading</label><input type="text" name="heading" value="<?php echo esc_attr( $header->heading ?? '' ); ?>"></div>
        <div class="ah-form-row"><label>Information</label><textarea name="information" rows="5"><?php echo esc_textarea( $header->information ?? '' ); ?></textarea></div>
        <div class="ah-form-row"><label>Visible</label><select name="is_visible"><option value="1" <?php selected( $header->is_visible ?? 1, 1 ); ?>>Yes</option><option value="0">No</option></select></div>
        <button type="submit" name="save_header" value="1" class="ah-btn ah-btn-primary">Save Header</button>
      </form>
    </div>

  <?php elseif ( $tab === 'gallery' ) : ?>
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">
      <!-- Gallery list -->
      <div>
        <div class="ah-media-grid">
          <?php foreach ( $gallery as $gi ) :
            $img_url = $media_m->get_url( (int) $gi->image_id );
          ?>
            <div class="ah-media-item">
              <img src="<?php echo esc_url( $img_url ); ?>" alt="" loading="lazy">
              <div class="media-name"><?php echo esc_html( $gi->width_class ); ?></div>
              <div class="media-actions">
                <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-client-stories', 'tab' => 'gallery', 'delete_gallery' => $gi->id ), admin_url( 'admin.php' ) ), 'ah_del_cs_gallery' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-icon" onclick="return confirm('Remove?');"><span class="dashicons dashicons-trash"></span></a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <!-- Add gallery image -->
      <div class="ah-card">
        <div class="ah-card-header"><h2>Add Gallery Image</h2></div>
        <form method="post">
          <?php wp_nonce_field( 'ah_save_client_stories', 'ah_cs_nonce' ); ?>
          <div class="ah-form-row">
            <label>Image *</label>
            <div class="ah-image-picker">
              <img src="" class="ah-image-preview" alt="" style="width:100%;height:100px;">
              <div class="ah-image-picker-btns">
                <input type="hidden" class="ah-image-id" name="gallery_image_id" value="0">
                <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-pick-image">Choose Image</button>
                <button type="button" class="ah-btn ah-btn-sm ah-remove-image" style="color:var(--ah-danger);">Remove</button>
              </div>
            </div>
          </div>
          <div class="ah-form-row">
            <label>Size</label>
            <select name="width_class">
              <?php foreach ( array( 'small', 'medium', 'large', 'full' ) as $wc ) : ?><option value="<?php echo $wc; ?>"><?php echo ucfirst( $wc ); ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="ah-form-row"><label>Sort Order</label><input type="number" name="sort_order" value="0"></div>
          <button type="submit" name="save_gallery_item" value="1" class="ah-btn ah-btn-primary">Add Image</button>
        </form>
      </div>
    </div>

  <?php elseif ( $tab === 'videos' ) : ?>
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">
      <div class="ah-table-wrap">
        <table class="ah-table">
          <thead><tr><th>Heading</th><th>URL</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ( $videos as $vid ) : ?>
              <tr>
                <td><?php echo esc_html( $vid->heading ); ?></td>
                <td><small><?php echo esc_html( $vid->video_url ); ?></small></td>
                <td class="row-actions">
                  <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-client-stories', 'tab' => 'videos', 'delete_video' => $vid->id ), admin_url( 'admin.php' ) ), 'ah_del_cs_video' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm" onclick="return confirm('Delete?');">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="ah-card">
        <div class="ah-card-header"><h2>Add Video Link</h2></div>
        <form method="post">
          <?php wp_nonce_field( 'ah_save_client_stories', 'ah_cs_nonce' ); ?>
          <input type="hidden" name="vid_edit_id" value="0">
          <div class="ah-form-row"><label>Heading</label><input type="text" name="vid_heading" value=""></div>
          <div class="ah-form-row"><label>Video URL *</label><input type="url" name="video_url" required placeholder="https://youtube.com/watch?v=…"></div>
          <div class="ah-form-row"><label>Sort Order</label><input type="number" name="vid_sort" value="0"></div>
          <button type="submit" name="save_video" value="1" class="ah-btn ah-btn-primary">Add Video</button>
        </form>
      </div>
    </div>
  <?php endif; ?>
</div>
