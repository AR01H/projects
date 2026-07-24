<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$model  = new AH_Media_Model();
$notice = '';

// Handle upload
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ah_media_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_media_nonce'], 'ah_upload_media' ) ) wp_die( 'Security.' );

	if ( ! empty( $_FILES['media_file']['name'] ) ) {
		$result = AH_Uploader::upload( 'media_file' );
		$notice = is_wp_error( $result ) ? 'Error: ' . $result->get_error_message() : 'File uploaded successfully.';
	}
}

// Handle delete
if ( isset( $_GET['delete_id'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'ah_delete_media' ) ) {
	$del_id = (int) $_GET['delete_id'];
	$row    = $model->find( $del_id );
	if ( $row ) {
		$upload_dir    = wp_upload_dir();
		$allowed_base  = realpath( $upload_dir['basedir'] );
		$resolved_path = realpath( $upload_dir['basedir'] . $row->file_path );
		if ( $allowed_base && $resolved_path && str_starts_with( $resolved_path, $allowed_base ) ) {
			@unlink( $resolved_path );
		}
		$model->delete( $del_id );
		$notice = 'File deleted.';
	}
}

$search = sanitize_text_field( $_GET['s'] ?? '' );
$mime   = sanitize_text_field( $_GET['mime'] ?? '' );
$paged  = AH_Pagination::current_page();
$result = $model->get_paginated( $paged, $search, $mime );
$items  = $result['items'];
$meta   = $result['meta'];
?>
<div class="wrap ah-wrap">
  <?php \Ah\Cms\Admin\Components\AdminComponents::pageHeader( 'images-alt2', 'Media Library', 'Browse, upload, and manage images and media files.' ); ?>

  <?php if ( $notice ) : ?><?php \Ah\Cms\Admin\Components\AdminComponents::notice( $notice, 'success' ); ?><?php endif; ?>

  <!-- Upload Form -->
  <?php ob_start(); ?>
    <form method="post" enctype="multipart/form-data" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
      <?php wp_nonce_field( 'ah_upload_media', 'ah_media_nonce' ); ?>
      <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Select File', '<input type="file" name="media_file" accept="image/*,application/pdf,video/*" required style="display:block;width:100%;">' ); ?>
      <button type="submit" class="ah-btn ah-btn-primary" style="margin-bottom:0;">Upload</button>
    </form>
  <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Upload New File', ob_get_clean() ); ?>

  <!-- Filters -->
  <?php
  \Ah\Cms\Admin\Components\AdminComponents::filterBar( array(
    'page_slug'          => 'ah-media',
    'search_placeholder' => 'Search files…',
    'search_value'       => $search,
    'hidden_inputs'      => array(),
    'filters'            => array(
      array(
        'name'     => 'mime',
        'options'  => array(
          ''                => 'All Types',
          'image'           => 'Images',
          'application/pdf' => 'PDFs',
          'video'           => 'Videos',
        ),
        'selected' => $mime,
      ),
    ),
  ) );
  ?>

  <!-- Grid -->
  <?php
  ob_start();
  foreach ( $items as $item ) :
    $is_img = str_starts_with( $item->mime_type, 'image/' );
    ?>
    <div class="ah-media-item">
      <?php if ( $is_img ) : ?>
        <img src="<?php echo esc_url( $item->file_url ); ?>" alt="<?php echo esc_attr( $item->alt_text ); ?>" loading="lazy">
      <?php else : ?>
        <div style="height:100px;display:flex;align-items:center;justify-content:center;background:var(--ah-bg-light);">
          <span class="dashicons dashicons-media-default" style="font-size:36px;color:var(--ah-muted);"></span>
        </div>
      <?php endif; ?>
      <div class="media-name" title="<?php echo esc_attr( $item->file_name ); ?>"><?php echo esc_html( $item->file_name ); ?></div>
      <div class="media-actions">
        <a href="<?php echo esc_url( $item->file_url ); ?>" target="_blank" class="ah-btn ah-btn-secondary ah-btn-icon" title="View"><span class="dashicons dashicons-visibility"></span></a>
        <?php echo \Ah\Cms\Admin\Components\AdminComponents::confirmDelete(
          wp_nonce_url( add_query_arg( array( 'page' => 'ah-media', 'delete_id' => $item->id ), admin_url( 'admin.php' ) ), 'ah_delete_media' ),
          'ah_delete_media'
        ); ?>
      </div>
    </div>
    <?php
  endforeach;
  if ( ! $items ) :
    \Ah\Cms\Admin\Components\AdminComponents::emptyState( 'No files found.' );
  endif;
  $grid_content = ob_get_clean();
  echo '<div class="ah-media-grid">' . $grid_content . '</div>';
  ?>

  <?php echo AH_Pagination::render( $meta ); ?>
</div>
