<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use Ah\Cms\Admin\Components\AdminComponents;

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
  <?php AdminComponents::pageHeader( 'format-gallery', 'Client Stories', 'Manage the client stories page header, gallery, and video links.' ); ?>
  <?php if ( $notice ) : ?><?php AdminComponents::notice( $notice, 'success' ); ?><?php endif; ?>
  <?php if ( ! $page_id ) : ?><?php AdminComponents::notice( 'Client Stories page not found. Create it in Pages Manager first.', 'warning' ); ?><?php return; endif; ?>

  <?php AdminComponents::tabBarUrl( array(
    'header'  => 'Page Header',
    'gallery' => 'Gallery',
    'videos'  => 'Video Links',
  ), $tab ); ?>

  <?php if ( $tab === 'header' ) : ?>
    <?php ob_start(); ?>
      <form method="post">
        <?php wp_nonce_field( 'ah_save_client_stories', 'ah_cs_nonce' ); ?>
        <?php AdminComponents::formRow( 'Heading', '<input type="text" name="heading" value="' . esc_attr( $header->heading ?? '' ) . '" class="regular-text">' ); ?>
        <?php AdminComponents::formRow( 'Information', '<textarea name="information" rows="5" class="large-text">' . esc_textarea( $header->information ?? '' ) . '</textarea>' ); ?>
        <?php
        $vis_select = '<select name="is_visible"><option value="1"' . selected( $header->is_visible ?? 1, 1, false ) . '>Yes</option><option value="0"' . selected( $header->is_visible ?? 0, 0, false ) . '>No</option></select>';
        AdminComponents::formRow( 'Visible', $vis_select );
        ?>
        <button type="submit" name="save_header" value="1" class="ah-btn ah-btn-primary">Save Header</button>
      </form>
    <?php AdminComponents::card( 'Client Stories Page Header', ob_get_clean() ); ?>

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
                <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-client-stories', 'tab' => 'gallery', 'delete_gallery' => $gi->id ), admin_url( 'admin.php' ) ), 'ah_del_cs_gallery' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-icon ah-confirm-delete" data-title="Remove Image" data-confirm="Remove this gallery image?"><span class="dashicons dashicons-trash"></span></a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <?php if ( empty( $gallery ) ) : ?>
          <?php AdminComponents::emptyState( 'No gallery images yet.', 'format-gallery' ); ?>
        <?php endif; ?>
      </div>
      <!-- Add gallery image -->
      <?php ob_start(); ?>
        <form method="post">
          <?php wp_nonce_field( 'ah_save_client_stories', 'ah_cs_nonce' ); ?>
          <?php AdminComponents::mediaField( 'gallery_image_id', 'Image / Video *', '', array( 'id' => 'gallery_image_id', 'type' => 'media' ) ); ?>
          <?php
          $size_select = '<select name="width_class">';
          foreach ( array( 'small', 'medium', 'large', 'full' ) as $wc ) {
            $size_select .= '<option value="' . esc_attr( $wc ) . '">' . esc_html( ucfirst( $wc ) ) . '</option>';
          }
          $size_select .= '</select>';
          AdminComponents::formRow( 'Size', $size_select );
          ?>
          <?php AdminComponents::formRow( 'Sort Order', '<input type="number" name="sort_order" value="0">' ); ?>
          <button type="submit" name="save_gallery_item" value="1" class="ah-btn ah-btn-primary">Add Image</button>
        </form>
      <?php AdminComponents::card( 'Add Gallery Image', ob_get_clean() ); ?>
    </div>

  <?php elseif ( $tab === 'videos' ) : ?>
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">
      <?php
      $vid_rows = array();
      foreach ( $videos as $vid ) {
        $row = new \stdClass();
        $row->id = $vid->id;
        $row->heading = $vid->heading;
        $row->video_url = $vid->video_url;
        $row->delete_url = wp_nonce_url( add_query_arg( array( 'page' => 'ah-client-stories', 'tab' => 'videos', 'delete_video' => $vid->id ), admin_url( 'admin.php' ) ), 'ah_del_cs_video' );
        $vid_rows[] = $row;
      }
      AdminComponents::dataTable( array(
        'columns' => array(
          array( 'label' => 'Heading', 'render' => function ( $r ) {
            return '<strong>' . esc_html( $r->heading ) . '</strong>';
          } ),
          array( 'label' => 'URL', 'render' => function ( $r ) {
            return '<small>' . esc_html( $r->video_url ) . '</small>';
          } ),
        ),
        'items'         => $vid_rows,
        'empty_message' => 'No video links yet.',
        'actions'       => function ( $r ) {
          return '<a href="' . esc_url( $r->delete_url ) . '" class="ah-btn ah-btn-danger ah-btn-sm ah-confirm-delete" data-title="Delete Video" data-confirm="This video link will be removed.">Delete</a>';
        },
      ) ); ?>
      <?php ob_start(); ?>
        <form method="post">
          <?php wp_nonce_field( 'ah_save_client_stories', 'ah_cs_nonce' ); ?>
          <input type="hidden" name="vid_edit_id" value="0">
          <?php AdminComponents::formRow( 'Heading', '<input type="text" name="vid_heading" value="" class="regular-text">' ); ?>
          <?php AdminComponents::formRow( 'Video URL *', '<input type="url" name="video_url" required placeholder="https://youtube.com/watch?v=…" class="regular-text">' ); ?>
          <?php AdminComponents::formRow( 'Sort Order', '<input type="number" name="vid_sort" value="0">' ); ?>
          <button type="submit" name="save_video" value="1" class="ah-btn ah-btn-primary">Add Video</button>
        </form>
      <?php AdminComponents::card( 'Add Video Link', ob_get_clean() ); ?>
    </div>
  <?php endif; ?>
</div>
