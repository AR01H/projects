<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

global $wpdb;

// ── Ensure table exists (auto-creates on first visit) ──────────────────────
$fl_table = $wpdb->prefix . 'ah_file_links';
$wpdb->query( "
	CREATE TABLE IF NOT EXISTS `{$fl_table}` (
		`id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
		`original_name` VARCHAR(255)     NOT NULL DEFAULT '',
		`stored_name`   VARCHAR(255)     NOT NULL DEFAULT '',
		`file_path`     VARCHAR(500)     NOT NULL DEFAULT '',
		`mime_type`     VARCHAR(150)     NOT NULL DEFAULT '',
		`file_size`     BIGINT UNSIGNED  NOT NULL DEFAULT 0,
		`uploaded_by`   INT UNSIGNED     DEFAULT NULL,
		`created_at`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (`id`),
		KEY `idx_fl_created` (`created_at`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
" );

// ── Helpers ────────────────────────────────────────────────────────────────
function ah_fl_human_size( int $bytes ): string {
	if ( $bytes >= 1073741824 ) return round( $bytes / 1073741824, 1 ) . ' GB';
	if ( $bytes >= 1048576 )    return round( $bytes / 1048576,    1 ) . ' MB';
	if ( $bytes >= 1024 )       return round( $bytes / 1024,       1 ) . ' KB';
	return $bytes . ' B';
}

function ah_fl_type_meta( string $mime ): array {
	if ( str_starts_with( $mime, 'image/' ) )        return [ 'label' => 'Image',    'icon' => 'format-image',      'color' => '#7c3aed' ];
	if ( $mime === 'application/pdf' )               return [ 'label' => 'PDF',      'icon' => 'media-document',    'color' => '#dc2626' ];
	if ( str_starts_with( $mime, 'video/' ) )        return [ 'label' => 'Video',    'icon' => 'video-alt3',        'color' => '#2563eb' ];
	if ( str_starts_with( $mime, 'audio/' ) )        return [ 'label' => 'Audio',    'icon' => 'format-audio',      'color' => '#d97706' ];
	if ( str_starts_with( $mime, 'text/' ) )         return [ 'label' => 'Text',     'icon' => 'text',              'color' => '#16a34a' ];
	if ( in_array( $mime, [ 'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed' ], true ) )
	                                                 return [ 'label' => 'Archive',  'icon' => 'media-archive',     'color' => '#64748b' ];
	if ( str_contains( $mime, 'spreadsheet' ) || str_contains( $mime, 'excel' ) || str_contains( $mime, 'csv' ) )
	                                                 return [ 'label' => 'Sheet',    'icon' => 'media-spreadsheet', 'color' => '#16a34a' ];
	if ( str_contains( $mime, 'word' ) || str_contains( $mime, 'document' ) )
	                                                 return [ 'label' => 'Doc',      'icon' => 'media-text',        'color' => '#1d4ed8' ];
	if ( str_contains( $mime, 'presentation' ) || str_contains( $mime, 'powerpoint' ) )
	                                                 return [ 'label' => 'Slides',   'icon' => 'slides',            'color' => '#ea580c' ];
	                                                 return [ 'label' => 'File',     'icon' => 'media-default',     'color' => '#64748b' ];
}

function ah_fl_get_url( string $file_path ): string {
	$upload = wp_upload_dir();
	return trailingslashit( $upload['baseurl'] ) . 'ah-files/' . ltrim( $file_path, '/' );
}

function ah_fl_get_disk_path( string $file_path ): string {
	$upload = wp_upload_dir();
	return trailingslashit( $upload['basedir'] ) . 'ah-files/' . ltrim( $file_path, '/' );
}

$notice       = '';
$notice_type  = 'success';
$upload_dir   = wp_upload_dir();
$base_dir     = trailingslashit( $upload_dir['basedir'] ) . 'ah-files';

// Ensure upload directory + index.php guard exist
if ( ! is_dir( $base_dir ) ) {
	wp_mkdir_p( $base_dir );
	file_put_contents( $base_dir . '/index.php', '<?php // Silence is golden.' );
}

// ── Handle upload ──────────────────────────────────────────────────────────
if (
	$_SERVER['REQUEST_METHOD'] === 'POST' &&
	wp_verify_nonce( $_POST['ah_fl_nonce'] ?? '', 'ah_upload_file_link' )
) {
	$uploaded = $_FILES['fl_upload'] ?? null;

	if ( ! $uploaded || $uploaded['error'] !== UPLOAD_ERR_OK ) {
		$notice      = 'No file received or upload error (code ' . ( $uploaded['error'] ?? '?' ) . ').';
		$notice_type = 'warning';
	} else {
		$original_name = sanitize_file_name( $uploaded['name'] );
		$year_month    = date( 'Y/m' );
		$target_dir    = $base_dir . '/' . $year_month;

		if ( ! is_dir( $target_dir ) ) {
			wp_mkdir_p( $target_dir );
		}

		// Unique filename — avoid collisions
		$stored_name = wp_unique_filename( $target_dir, $original_name );
		$target_path = $target_dir . '/' . $stored_name;
		$file_path   = $year_month . '/' . $stored_name;

		// Detect MIME
		$wp_filetype = wp_check_filetype( $original_name );
		$mime        = $wp_filetype['type'] ?: ( $uploaded['type'] ?: 'application/octet-stream' );

		if ( move_uploaded_file( $uploaded['tmp_name'], $target_path ) ) {
			$wpdb->insert( $fl_table, array(
				'original_name' => $original_name,
				'stored_name'   => $stored_name,
				'file_path'     => $file_path,
				'mime_type'     => $mime,
				'file_size'     => (int) $uploaded['size'],
				'uploaded_by'   => get_current_user_id() ?: null,
			) );
			AH_DB_Helper::log_action( 'create', 'file_links', $wpdb->insert_id, array( 'file' => $original_name ) );
			$notice = "'{$original_name}' uploaded successfully. Copy the link below.";
		} else {
			$notice      = 'Failed to move uploaded file. Check directory permissions.';
			$notice_type = 'warning';
		}
	}
}

// ── Handle delete ──────────────────────────────────────────────────────────
if ( isset( $_GET['delete_fl'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_del_file_link' ) ) {
	$del_id  = (int) $_GET['delete_fl'];
	$del_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$fl_table}` WHERE id = %d", $del_id ) );
	if ( $del_row ) {
		$disk_path = ah_fl_get_disk_path( $del_row->file_path );
		if ( file_exists( $disk_path ) ) @unlink( $disk_path );
		$wpdb->delete( $fl_table, array( 'id' => $del_id ), array( '%d' ) );
		AH_DB_Helper::log_action( 'delete', 'file_links', $del_id );
		$notice = "'{$del_row->original_name}' deleted.";
	}
}

// ── Fetch files ────────────────────────────────────────────────────────────
$search   = sanitize_text_field( $_GET['s'] ?? '' );
$paged    = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
$per_page = 20;

$where_sql = $search
	? $wpdb->prepare( ' WHERE original_name LIKE %s OR mime_type LIKE %s', '%' . $wpdb->esc_like( $search ) . '%', '%' . $wpdb->esc_like( $search ) . '%' )
	: '';

$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$fl_table}`" . $where_sql );
$offset = ( $paged - 1 ) * $per_page;
$files  = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT * FROM `{$fl_table}`{$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d",
		$per_page, $offset
	)
) ?: array();

$meta = AH_DB_Helper::paginate_meta( $total, $per_page, $paged );
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-admin-links"></span> <?php esc_html_e( 'File Links', 'ah-theme' ); ?></h1>

  <?php if ( $notice ) : ?>
    <div class="ah-notice ah-notice-<?php echo esc_attr( $notice_type ); ?>">
      <?php echo esc_html( $notice ); ?>
    </div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">

    <!-- ── Left: file table ─────────────────────────────────── -->
    <div>

      <!-- Search bar -->
      <div class="ah-table-top" style="margin-bottom:12px;">
        <form method="get" class="ah-filters">
          <input type="hidden" name="page" value="ah-file-links">
          <input type="text" name="s" value="<?php echo esc_attr( $search ); ?>"
                 placeholder="Search files…" style="max-width:240px;">
          <button class="ah-btn ah-btn-secondary">Search</button>
          <?php if ( $search ) : ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-file-links' ) ); ?>"
               class="ah-btn ah-btn-secondary">Clear</a>
          <?php endif; ?>
        </form>
        <span style="color:var(--ah-muted);font-size:13px;">
          <?php echo number_format_i18n( $total ); ?> file<?php echo $total !== 1 ? 's' : ''; ?>
        </span>
      </div>

      <?php if ( $files ) : ?>
        <div class="ah-table-wrap">
          <table class="ah-table" style="table-layout:fixed;">
            <colgroup>
              <col style="width:36px;">
              <col>
              <col style="width:70px;">
              <col style="width:75px;">
              <col style="width:240px;">
              <col style="width:95px;">
              <col style="width:80px;">
            </colgroup>
            <thead>
              <tr>
                <th></th>
                <th>File Name</th>
                <th>Type</th>
                <th>Size</th>
                <th>Link</th>
                <th>Uploaded</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ( $files as $f ) :
                $meta_type = ah_fl_type_meta( $f->mime_type );
                $file_url  = ah_fl_get_url( $f->file_path );
                $disk_ok   = file_exists( ah_fl_get_disk_path( $f->file_path ) );
              ?>
                <tr <?php echo ! $disk_ok ? 'style="opacity:.5;"' : ''; ?>>
                  <!-- Type icon -->
                  <td style="text-align:center;">
                    <span class="dashicons dashicons-<?php echo esc_attr( $meta_type['icon'] ); ?>"
                          style="color:<?php echo esc_attr( $meta_type['color'] ); ?>;font-size:20px;width:20px;"></span>
                  </td>

                  <!-- Name -->
                  <td style="word-break:break-all;">
                    <a href="<?php echo esc_url( $file_url ); ?>" target="_blank"
                       style="font-weight:500;text-decoration:none;color:var(--ah-text);">
                      <?php echo esc_html( $f->original_name ); ?>
                    </a>
                    <?php if ( ! $disk_ok ) : ?>
                      <br><small style="color:var(--ah-danger);">⚠ File missing on disk</small>
                    <?php endif; ?>
                  </td>

                  <!-- MIME badge -->
                  <td>
                    <span style="background:<?php echo esc_attr( $meta_type['color'] ); ?>22;
                                 color:<?php echo esc_attr( $meta_type['color'] ); ?>;
                                 padding:2px 7px;border-radius:10px;font-size:11px;font-weight:600;">
                      <?php echo esc_html( $meta_type['label'] ); ?>
                    </span>
                  </td>

                  <!-- Size -->
                  <td style="font-size:12px;color:var(--ah-muted);">
                    <?php echo esc_html( ah_fl_human_size( (int) $f->file_size ) ); ?>
                  </td>

                  <!-- Link + copy -->
                  <td>
                    <div style="display:flex;gap:4px;align-items:center;">
                      <input type="text" value="<?php echo esc_url( $file_url ); ?>"
                             readonly class="ah-link-input"
                             style="flex:1;font-size:11px;padding:4px 6px;border:1px solid var(--ah-border);
                                    border-radius:4px;background:var(--ah-bg-light);color:var(--ah-text);
                                    min-width:0;cursor:text;">
                      <button type="button"
                              class="ah-btn ah-btn-secondary ah-btn-sm ah-copy-link"
                              data-url="<?php echo esc_url( $file_url ); ?>"
                              title="Copy link"
                              style="flex-shrink:0;padding:4px 7px;">
                        <span class="dashicons dashicons-clipboard"
                              style="font-size:14px;width:14px;line-height:1.6;"></span>
                      </button>
                      <a href="<?php echo esc_url( $file_url ); ?>" target="_blank"
                         class="ah-btn ah-btn-secondary ah-btn-sm"
                         title="Open in new tab"
                         style="flex-shrink:0;padding:4px 7px;">
                        <span class="dashicons dashicons-external"
                              style="font-size:14px;width:14px;line-height:1.6;"></span>
                      </a>
                    </div>
                  </td>

                  <!-- Date -->
                  <td style="font-size:12px;color:var(--ah-muted);">
                    <?php echo esc_html( wp_date( 'M j, Y', strtotime( $f->created_at ) ) ); ?><br>
                    <span style="font-size:11px;"><?php echo esc_html( wp_date( 'g:i a', strtotime( $f->created_at ) ) ); ?></span>
                  </td>

                  <!-- Delete -->
                  <td class="row-actions">
                    <a href="<?php echo esc_url( wp_nonce_url(
                        add_query_arg( array( 'page' => 'ah-file-links', 'delete_fl' => $f->id ), admin_url( 'admin.php' ) ),
                        'ah_del_file_link'
                    ) ); ?>"
                       class="ah-btn ah-btn-danger ah-btn-sm ah-btn-icon"
                       onclick="return confirm('Delete this file permanently?');">
                      <span class="dashicons dashicons-trash" style="font-size:14px;line-height:1.6;"></span>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php echo AH_Pagination::render( $meta ); ?>

      <?php else : ?>
        <div style="text-align:center;padding:48px 20px;color:var(--ah-muted);background:#fff;border:1px solid var(--ah-border);border-radius:var(--ah-radius);">
          <span class="dashicons dashicons-admin-links" style="font-size:40px;width:40px;color:var(--ah-border);display:block;margin:0 auto 12px;"></span>
          <?php echo $search ? 'No files match your search.' : 'No files uploaded yet. Upload your first file using the form.'; ?>
        </div>
      <?php endif; ?>

    </div>

    <!-- ── Right: upload form ────────────────────────────────── -->
    <div style="position:sticky;top:32px;">
      <div class="ah-card">
        <div class="ah-card-header"><h2>Upload File</h2></div>

        <form method="post" enctype="multipart/form-data" id="ah-fl-form">
          <?php wp_nonce_field( 'ah_upload_file_link', 'ah_fl_nonce' ); ?>

          <!-- Drop zone -->
          <div id="ah-fl-dropzone"
               style="border:2px dashed var(--ah-border);border-radius:var(--ah-radius);
                      padding:28px 16px;text-align:center;cursor:pointer;
                      background:var(--ah-bg-light);transition:border-color .2s,background .2s;
                      margin-bottom:14px;">
            <span class="dashicons dashicons-upload"
                  style="font-size:32px;width:32px;color:var(--ah-muted);display:block;margin:0 auto 8px;"></span>
            <p style="margin:0 0 8px;font-weight:500;">Drag & drop a file here</p>
            <p style="margin:0 0 12px;font-size:12px;color:var(--ah-muted);">or</p>
            <label class="ah-btn ah-btn-secondary ah-btn-sm"
                   for="fl_upload" style="cursor:pointer;display:inline-block;">
              Browse Files
            </label>
            <input type="file" name="fl_upload" id="fl_upload" style="display:none;" required>
            <p id="ah-fl-filename"
               style="margin:10px 0 0;font-size:12px;color:var(--ah-primary);font-weight:500;min-height:16px;"></p>
          </div>

          <div style="background:var(--ah-bg-light);border-radius:var(--ah-radius);padding:10px 12px;
                      font-size:12px;color:var(--ah-muted);margin-bottom:14px;">
            <strong>Accepted:</strong> Any file type<br>
            <strong>Max size:</strong> <?php echo esc_html( ini_get( 'upload_max_filesize' ) ); ?>
            &nbsp;·&nbsp;
            <strong>Post limit:</strong> <?php echo esc_html( ini_get( 'post_max_size' ) ); ?>
          </div>

          <button type="submit" class="ah-btn ah-btn-primary" style="width:100%;" id="ah-fl-submit">
            <span class="dashicons dashicons-upload"
                  style="font-size:14px;width:14px;line-height:1.8;margin-right:4px;"></span>
            Upload &amp; Get Link
          </button>
        </form>
      </div>

      <!-- Quick tips -->
      <div class="ah-card" style="margin-top:16px;">
        <div class="ah-card-header"><h2>How to Use</h2></div>
        <ol style="margin:0;padding-left:18px;font-size:13px;color:var(--ah-muted);line-height:1.8;">
          <li>Upload any file using the form above.</li>
          <li>Copy the generated link from the table.</li>
          <li>Paste the link anywhere — emails, pages, content, navigation menus.</li>
          <li>Files are stored in your WordPress uploads folder and are publicly accessible via the link.</li>
        </ol>
      </div>
    </div>

  </div><!-- /grid -->
</div>

<script>
(function(){
  const dropzone  = document.getElementById('ah-fl-dropzone');
  const fileInput = document.getElementById('fl_upload');
  const label     = document.getElementById('ah-fl-filename');
  const form      = document.getElementById('ah-fl-form');
  const submitBtn = document.getElementById('ah-fl-submit');

  // Show selected filename
  function showName(name) {
    label.textContent = name ? '📄 ' + name : '';
  }

  fileInput.addEventListener('change', function(){
    showName(this.files[0]?.name || '');
  });

  // Click dropzone → trigger file input
  dropzone.addEventListener('click', function(e){
    if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'LABEL') fileInput.click();
  });

  // Drag & drop
  dropzone.addEventListener('dragover', function(e){
    e.preventDefault();
    this.style.borderColor = 'var(--ah-primary)';
    this.style.background  = '#eff6ff';
  });
  dropzone.addEventListener('dragleave', function(){
    this.style.borderColor = 'var(--ah-border)';
    this.style.background  = 'var(--ah-bg-light)';
  });
  dropzone.addEventListener('drop', function(e){
    e.preventDefault();
    this.style.borderColor = 'var(--ah-border)';
    this.style.background  = 'var(--ah-bg-light)';
    const dt = e.dataTransfer;
    if (dt.files.length) {
      fileInput.files = dt.files;
      showName(dt.files[0].name);
    }
  });

  // Disable submit while uploading
  form.addEventListener('submit', function(){
    if (!fileInput.files.length) { alert('Please select a file first.'); return false; }
    submitBtn.disabled    = true;
    submitBtn.textContent = 'Uploading…';
  });

  // Copy link buttons
  document.querySelectorAll('.ah-copy-link').forEach(function(btn){
    btn.addEventListener('click', function(){
      const url  = this.dataset.url;
      const icon = this.querySelector('.dashicons');

      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(function(){
          icon.className = 'dashicons dashicons-yes';
          setTimeout(function(){ icon.className = 'dashicons dashicons-clipboard'; }, 2000);
        });
      } else {
        // Fallback for non-HTTPS / older browsers
        const tmp = document.createElement('input');
        tmp.value = url;
        document.body.appendChild(tmp);
        tmp.select();
        document.execCommand('copy');
        document.body.removeChild(tmp);
        icon.className = 'dashicons dashicons-yes';
        setTimeout(function(){ icon.className = 'dashicons dashicons-clipboard'; }, 2000);
      }
    });
  });

  // Select-all on link input click
  document.querySelectorAll('.ah-link-input').forEach(function(inp){
    inp.addEventListener('click', function(){ this.select(); });
  });
})();
</script>
