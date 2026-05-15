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
		$upload_dir = wp_upload_dir();
		@unlink( $upload_dir['basedir'] . $row->file_path );
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
  <h1><span class="dashicons dashicons-images-alt2"></span> <?php esc_html_e( 'Media Library', 'ah-theme' ); ?></h1>

  <?php if ( $notice ) : ?><div class="ah-notice ah-notice-success"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

  <!-- Upload Form -->
  <div class="ah-card" style="margin-bottom:20px;">
    <div class="ah-card-header"><h2>Upload New File</h2></div>
    <form method="post" enctype="multipart/form-data" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
      <?php wp_nonce_field( 'ah_upload_media', 'ah_media_nonce' ); ?>
      <div class="ah-form-row" style="margin:0;flex:1;min-width:260px;">
        <label>Select File</label>
        <input type="file" name="media_file" accept="image/*,application/pdf,video/mp4" required>
      </div>
      <button type="submit" class="ah-btn ah-btn-primary" style="margin-bottom:0;">Upload</button>
    </form>
  </div>

  <!-- Filters -->
  <div class="ah-table-top">
    <form class="ah-search-form" method="get">
      <input type="hidden" name="page" value="ah-media">
      <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search files…">
      <select name="mime">
        <option value="">All Types</option>
        <option value="image" <?php selected( $mime, 'image' ); ?>>Images</option>
        <option value="application/pdf" <?php selected( $mime, 'application/pdf' ); ?>>PDFs</option>
        <option value="video" <?php selected( $mime, 'video' ); ?>>Videos</option>
      </select>
      <button class="ah-btn ah-btn-secondary">Filter</button>
    </form>
    <span style="color:var(--ah-muted);font-size:13px;"><?php echo number_format_i18n( $meta['total'] ); ?> files</span>
  </div>

  <!-- Grid -->
  <div class="ah-media-grid">
    <?php foreach ( $items as $item ) :
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
          <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-media', 'delete_id' => $item->id ), admin_url( 'admin.php' ) ), 'ah_delete_media' ) ); ?>"
             class="ah-btn ah-btn-danger ah-btn-icon"
             title="Delete"
             onclick="return confirm('Delete this file?');">
            <span class="dashicons dashicons-trash"></span>
          </a>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if ( ! $items ) : ?><p style="color:var(--ah-muted);">No files found.</p><?php endif; ?>
  </div>

  <?php echo AH_Pagination::render( $meta ); ?>
</div>
