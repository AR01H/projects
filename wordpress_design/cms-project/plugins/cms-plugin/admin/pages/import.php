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
			AH_DB_Helper::log_action( 'create', 'import_' . $type, 0, array(
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
  <?php \Ah\Cms\Admin\Components\AdminComponents::pageHeader( 'upload', 'Data Import', 'Bulk import content from CSV files into the CMS.' ); ?>

  <?php if ( $notice_text ) : ?>
    <?php \Ah\Cms\Admin\Components\AdminComponents::notice( $notice_text, $notice_type === 'error' ? 'warning' : 'success' ); ?>
  <?php endif; ?>

  <?php if ( $results && ! empty( $results['errors'] ) ) : ?>
    <?php \Ah\Cms\Admin\Components\AdminComponents::notice( '<strong>Row errors:</strong> ' . esc_html( implode( ', ', $results['errors'] ) ), 'warning' ); ?>
  <?php endif; ?>

  <!-- Import type tabs -->
  <?php
  $import_tabs = array();
  foreach ( $config as $key => $cfg ) {
      $import_tabs[ $key ] = $cfg['label'];
  }
  \Ah\Cms\Admin\Components\AdminComponents::tabBarUrl( $import_tabs, $tab, 'ah-import' );
  ?>

  <?php
  $current = $config[ $tab ];
  ?>

  <div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start;">

    <!-- Left: column reference + results -->
    <div>

      <!-- Column reference card -->
      <?php ob_start(); ?>
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
          <h2 style="margin:0;">CSV Columns - <?php echo esc_html( $current['label'] ); ?></h2>
          <?php
            $sample_file = 'sample-' . $tab . '.csv';
            if ( file_exists( get_template_directory() . '/' . $sample_file ) ) :
              $sample_url = get_template_directory_uri() . '/' . $sample_file;
          ?>
            <a href="<?php echo esc_url( $sample_url ); ?>"
               download="<?php echo esc_attr( $sample_file ); ?>"
               class="ah-btn ah-btn-secondary ah-btn-sm"
               style="font-size:12px;">
              <span class="dashicons dashicons-download" style="font-size:14px;line-height:1.6;margin-right:3px;"></span>
              Download Sample CSV
            </a>
          <?php endif; ?>
        </div>
        <?php
        $_import_cols = array();
        foreach ( $current['columns'] as $_col => $_desc ) {
          $_import_cols[] = (object) array( 'id' => $_col, 'col' => $_col, 'desc' => $_desc, 'required' => in_array( $_col, $current['required'], true ) );
        }
        \Ah\Cms\Admin\Components\AdminComponents::dataTable( array(
          'columns' => array(
            array( 'label' => 'Column', 'style' => 'width:200px', 'render' => function ( $item ) {
              return '<code>' . esc_html( $item->col ) . '</code>';
            } ),
            array( 'label' => 'Description', 'render' => function ( $item ) {
              return '<small>' . esc_html( $item->desc ) . '</small>';
            } ),
            array( 'label' => 'Required', 'style' => 'width:80px', 'render' => function ( $item ) {
              return $item->required
                ? '<span class="ah-badge ah-badge-active">Yes</span>'
                : '<span style="color:var(--ah-muted);font-size:12px;">Optional</span>';
            } ),
          ),
          'items'         => $_import_cols,
          'empty_message' => '',
        ) ); ?>
        <p style="margin:12px 0 0;color:var(--ah-muted);font-size:12px;">
          &bull; First row must be the header row exactly as shown above.<br>
          &bull; Columns can be in any order as long as headers match.<br>
          &bull; Extra columns are ignored. All text values should be UTF-8.
        </p>
      <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'CSV Columns - ' . esc_html( $current['label'] ), ob_get_clean() ); ?>

      <!-- Import results panel -->
      <?php if ( $results && $notice_type === 'success' ) : ?>
        <?php ob_start(); ?>
          <div class="ah-stats-grid" style="grid-template-columns:repeat(3,1fr);">
            <div class="ah-stat-card">
              <div class="ah-stat-number" style="color:var(--ah-success);"><?php \Ah\Cms\Admin\Components\AdminComponents::statCard( (int) $results['imported'], 'Rows Imported', 'yes-alt' ); ?></div>
            </div>
            <div class="ah-stat-card">
              <div class="ah-stat-number" style="color:var(--ah-warning);"><?php \Ah\Cms\Admin\Components\AdminComponents::statCard( (int) $results['skipped'], 'Rows Skipped', 'warning' ); ?></div>
            </div>
            <div class="ah-stat-card">
              <?php \Ah\Cms\Admin\Components\AdminComponents::statCard( count( $results['errors'] ), 'Errors', 'dismiss' ); ?>
            </div>
          </div>
        <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Import Results', ob_get_clean() ); ?>
      <?php endif; ?>

    </div>

    <!-- Right: upload form -->
    <?php ob_start(); ?>
      <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field( 'ah_import_csv', 'ah_import_nonce' ); ?>
        <input type="hidden" name="import_type" value="<?php echo esc_attr( $tab ); ?>">

        <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Import Type', '<div style="padding:8px 0;font-weight:600;color:var(--ah-primary);">' . esc_html( $current['label'] ) . '</div>' ); ?>

        <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'CSV File <span style="color:var(--ah-danger);">*</span>',
          '<input type="file" name="csv_file" accept=".csv,text/csv" required style="display:block;width:100%;padding:6px 0;">'
          . '<p class="description" style="font-size:12px;margin-top:4px;">Max upload size: ' . esc_html( ini_get( 'upload_max_filesize' ) ) . '. Only .csv files accepted.</p>'
        ); ?>

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
    <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Upload CSV', ob_get_clean() ); ?>

  </div><!-- /grid -->


</div>
