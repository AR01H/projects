<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$config  = AH_CSV_Importer::get_config();
$tab     = sanitize_key( $_GET['tab'] ?? array_key_first( $config ) );
if ( ! isset( $config[ $tab ] ) ) $tab = array_key_first( $config );

$notice  = '';
$results = null;

// ---- Handle upload ----
if (
	$_SERVER['REQUEST_METHOD'] === 'POST' &&
	wp_verify_nonce( $_POST['ah_import_nonce'] ?? '', 'ah_import_csv' ) &&
	isset( $_POST['import_type'] )
) {
	$type = sanitize_key( $_POST['import_type'] );

	if ( empty( $_FILES['csv_file']['tmp_name'] ) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK ) {
		$notice = 'error:Please select a valid CSV file to upload.';
	} elseif ( strtolower( pathinfo( $_FILES['csv_file']['name'], PATHINFO_EXTENSION ) ) !== 'csv' ) {
		$notice = 'error:Only .csv files are accepted.';
	} else {
		$rows = AH_CSV_Importer::parse_file( $_FILES['csv_file']['tmp_name'] );
		if ( empty( $rows ) ) {
			$notice = 'error:The CSV file is empty or could not be parsed. Check headers match the sample file.';
		} else {
			$results = AH_CSV_Importer::import( $type, $rows );
			AH_DB_Helper::log_action( 'create', 'import_' . $type, null, array(
				'file'     => sanitize_text_field( $_FILES['csv_file']['name'] ),
				'rows'     => count( $rows ),
				'imported' => $results['imported'],
				'skipped'  => $results['skipped'],
			) );
			$notice = "success:{$results['imported']} rows imported, {$results['skipped']} skipped.";
		}
	}
}

[ $notice_type, $notice_text ] = $notice ? explode( ':', $notice, 2 ) : [ '', '' ];
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Data Import', 'ah-theme' ); ?></h1>

  <?php if ( $notice_text ) : ?>
    <div class="ah-notice ah-notice-<?php echo $notice_type === 'error' ? 'warning' : 'success'; ?>">
      <?php echo esc_html( $notice_text ); ?>
    </div>
  <?php endif; ?>

  <?php if ( $results && ! empty( $results['errors'] ) ) : ?>
    <div class="ah-notice ah-notice-warning" style="margin-top:0;">
      <strong>Row errors:</strong>
      <ul style="margin:.4em 0 0 1.2em;padding:0;">
        <?php foreach ( $results['errors'] as $err ) : ?>
          <li><?php echo esc_html( $err ); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <!-- Import type tabs -->
  <div class="ah-tabs">
    <?php foreach ( $config as $key => $cfg ) : ?>
      <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-import', 'tab' => $key ), admin_url( 'admin.php' ) ) ); ?>"
         class="ah-tab <?php echo $tab === $key ? 'active' : ''; ?>">
        <?php echo esc_html( $cfg['label'] ); ?>
      </a>
    <?php endforeach; ?>
  </div>

  <?php
  $current = $config[ $tab ];
  ?>

  <div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start;">

    <!-- Left: column reference + results -->
    <div>

      <!-- Column reference card -->
      <div class="ah-card" style="margin-bottom:20px;">
        <div class="ah-card-header">
          <h2>CSV Columns - <?php echo esc_html( $current['label'] ); ?></h2>
        </div>
        <table class="ah-table" style="margin-top:4px;">
          <thead>
            <tr><th style="width:200px;">Column</th><th>Description</th><th style="width:80px;">Required</th></tr>
          </thead>
          <tbody>
            <?php foreach ( $current['columns'] as $col => $desc ) : ?>
              <tr>
                <td><code><?php echo esc_html( $col ); ?></code></td>
                <td><small><?php echo esc_html( $desc ); ?></small></td>
                <td>
                  <?php if ( in_array( $col, $current['required'], true ) ) : ?>
                    <span class="ah-badge ah-badge-active">Yes</span>
                  <?php else : ?>
                    <span style="color:var(--ah-muted);font-size:12px;">Optional</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <p style="margin:12px 0 0;color:var(--ah-muted);font-size:12px;">
          &bull; First row must be the header row exactly as shown above.<br>
          &bull; Columns can be in any order as long as headers match.<br>
          &bull; Extra columns are ignored. All text values should be UTF-8.
        </p>
      </div>

      <!-- Import results panel -->
      <?php if ( $results && $notice_type === 'success' ) : ?>
        <div class="ah-card">
          <div class="ah-card-header"><h2>Import Results</h2></div>
          <div class="ah-stats-grid" style="grid-template-columns:repeat(3,1fr);">
            <div class="ah-stat-card">
              <div class="ah-stat-number" style="color:var(--ah-success);"><?php echo (int) $results['imported']; ?></div>
              <div class="ah-stat-label">Rows Imported</div>
            </div>
            <div class="ah-stat-card">
              <div class="ah-stat-number" style="color:var(--ah-warning);"><?php echo (int) $results['skipped']; ?></div>
              <div class="ah-stat-label">Rows Skipped</div>
            </div>
            <div class="ah-stat-card">
              <div class="ah-stat-number"><?php echo count( $results['errors'] ); ?></div>
              <div class="ah-stat-label">Errors</div>
            </div>
          </div>
        </div>
      <?php endif; ?>

    </div>

    <!-- Right: upload form -->
    <div class="ah-card" style="position:sticky;top:32px;">
      <div class="ah-card-header"><h2>Upload CSV</h2></div>
      <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field( 'ah_import_csv', 'ah_import_nonce' ); ?>
        <input type="hidden" name="import_type" value="<?php echo esc_attr( $tab ); ?>">

        <div class="ah-form-row">
          <label>Import Type</label>
          <div style="padding:8px 0;font-weight:600;color:var(--ah-primary);">
            <?php echo esc_html( $current['label'] ); ?>
          </div>
        </div>

        <div class="ah-form-row">
          <label>CSV File <span style="color:var(--ah-danger);">*</span></label>
          <input type="file" name="csv_file" accept=".csv,text/csv" required
                 style="display:block;width:100%;padding:6px 0;">
          <p class="description" style="font-size:12px;margin-top:4px;">
            Max upload size: <?php echo esc_html( ini_get( 'upload_max_filesize' ) ); ?>.
            Only .csv files accepted.
          </p>
        </div>

        <div style="background:var(--ah-bg-light);border-radius:var(--ah-radius);padding:12px;margin-bottom:16px;font-size:12px;color:var(--ah-muted);">
          <strong>Bulk import:</strong> Add as many data rows as you need in a single CSV file.
          All rows are processed in one upload. Duplicate detection runs per-row - failed rows
          are skipped and reported without stopping the rest.
        </div>

        <button type="submit" class="ah-btn ah-btn-primary" style="width:100%;">
          <span class="dashicons dashicons-upload" style="font-size:14px;line-height:1.8;margin-right:4px;"></span>
          Import <?php echo esc_html( $current['label'] ); ?>
        </button>

      </form>
    </div>

  </div><!-- /grid -->


</div>
