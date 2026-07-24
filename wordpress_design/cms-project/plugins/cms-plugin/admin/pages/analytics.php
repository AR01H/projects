<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$action    = sanitize_key( $_GET['action'] ?? 'list' );
$report_id = (int) ( $_GET['id'] ?? 0 );
$report    = ( $action === 'edit' && $report_id ) ? ( new AH_Analytics_Report_Model() )->find( $report_id ) : null;

if ( $action === 'edit' && $report_id && ! $report ) {
	$action = 'list';
}
?>
<div class="wrap ah-wrap">

<?php if ( $action === 'edit' ) : ?>
<!-- ══════════════════════════════════════════════════════════════
     EDIT / ADD VIEW
══════════════════════════════════════════════════════════════ -->

<?php \Ah\Cms\Admin\Components\AdminComponents::backLink( admin_url( 'admin.php?page=ah-analytics' ), '← Back to Reports' ); ?>
<h2 style="margin:0 0 6px;font-size:1.15rem;"><?php echo $report ? esc_html( $report->name ) : 'New Report'; ?></h2>
<p style="color:var(--ah-muted);margin-bottom:24px">
	Write a SELECT query, run it to preview results, then save + export.
</p>

<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start">

	<!-- Query editor -->
	<?php ob_start(); ?>
		<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
			<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Report Name *', '<input id="ar-name" type="text" class="regular-text" value="' . esc_attr( $report->name ?? '' ) . '" placeholder="Monthly Active Users" style="width:100%">' ); ?>
			<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Description', '<input id="ar-desc" type="text" class="regular-text" value="' . esc_attr( $report->description ?? '' ) . '" placeholder="Optional note" style="width:100%">' ); ?>
		</div>

		<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
			<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Report Type',
				'<select id="ar-type" style="width:100%;max-width:none">'
				. '<option value="sql"' . selected( $report->report_type ?? 'sql', 'sql', false ) . '>SQL Query</option>'
				. '<option value="php"' . selected( $report->report_type ?? 'sql', 'php', false ) . '>PHP Code</option>'
				. '</select>'
			); ?>
			<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'API Visibility',
				'<select id="ar-api-vis" style="width:100%;max-width:none">'
				. '<option value="private"' . selected( $report->api_visibility ?? 'private', 'private', false ) . '>Private (Admin Only)</option>'
				. '<option value="public"' . selected( $report->api_visibility ?? 'private', 'public', false ) . '>Public (Anyone)</option>'
				. '</select>'
			); ?>
		</div>

		<?php if ( $report && $report->id ) : ?>
		<div style="margin-bottom:14px;background:#f8fafc;padding:10px;border-radius:4px;font-size:12px;display:flex;align-items:center;gap:10px">
			<strong style="color:var(--ah-muted)">API Endpoint:</strong>
			<code style="background:transparent;padding:0;color:#0369a1"><?php echo esc_url( home_url( '/wp-json/ah-analytics/v1/report/' . $report->id ) ); ?></code>
			<button type="button" class="ah-btn ah-btn-secondary" style="padding:2px 6px;font-size:10px" onclick="navigator.clipboard.writeText('<?php echo esc_js( home_url( '/wp-json/ah-analytics/v1/report/' . $report->id ) ) ; ?>');alert('Copied!')">Copy</button>
		</div>
		<?php endif; ?>

		<div id="ar-sql-wrap" style="<?php echo ( $report->report_type ?? 'sql' ) === 'php' ? 'display:none' : ''; ?>">
			<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'SQL Query * <span style="font-weight:400;text-transform:none;letter-spacing:0">(SELECT, DESC, SHOW CREATE)</span>',
				'<textarea id="ar-sql" rows="12" style="width:100%;font-family:monospace;font-size:13px;padding:12px;border:1.5px solid #ddd;border-radius:6px;resize:vertical;background:#1e1e2e;color:#cdd6f4;line-height:1.6" placeholder="SELECT * FROM wp_posts WHERE post_status = \'publish\' LIMIT 20">'
				. esc_textarea( $report->query_sql ?? '' ) . '</textarea>'
			); ?>
		</div>

		<div id="ar-php-wrap" style="<?php echo ( $report->report_type ?? 'sql' ) === 'sql' ? 'display:none' : ''; ?>">
			<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'PHP Code * <span style="font-weight:400;text-transform:none;letter-spacing:0">(Must return an array of arrays)</span>',
				'<textarea id="ar-php" rows="12" style="width:100%;font-family:monospace;font-size:13px;padding:12px;border:1.5px solid #ddd;border-radius:6px;resize:vertical;background:#1e1e2e;color:#cdd6f4;line-height:1.6" placeholder="$data = [];&#10;foreach ( get_users() as $u ) {&#10;    $data[] = [ \'ID\' => $u->ID, \'Login\' => $u->user_login ];&#10;}&#10;return $data;">'
				. esc_textarea( $report->query_php ?? '' ) . '</textarea>'
				. '<p style="font-size:11px;color:var(--ah-muted);margin:6px 0 0">Security warning: This code is executed via `eval()`. Do not use untrusted input.</p>'
			); ?>
		</div>

		<div style="display:flex;gap:10px;margin-top:12px;align-items:center">
			<button id="ar-run-btn" class="ah-btn ah-btn-primary">
				&#9654; Run Query
			</button>
			<button id="ar-save-btn" class="ah-btn ah-btn-secondary">
				&#10003; Save Report
			</button>
			<span id="ar-save-status" style="font-size:13px;color:var(--ah-muted)"></span>
			<span style="margin-left:auto;font-size:12px;color:var(--ah-muted)" id="ar-exec-info"></span>
		</div>
	<?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Query Editor', ob_get_clean() ); ?>

	<!-- Sidebar -->
	<div style="display:flex;flex-direction:column;gap:16px">

		<!-- Table reference -->
		<?php
		global $wpdb;
		$tables = $wpdb->get_col( 'SHOW TABLES' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		sort( $tables );
		$_tbl_content = '<div style="max-height:220px;overflow-y:auto">';
		foreach ( $tables as $t ) {
			$_tbl_content .= '<div class="ar-tbl-row" data-table="' . esc_attr( $t ) . '" style="font-size:12px;font-family:monospace;padding:4px 8px;border-radius:4px;cursor:pointer;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="Click to insert table name">' . esc_html( $t ) . '</div>';
		}
		$_tbl_content .= '</div><p style="font-size:11px;color:var(--ah-muted);margin:8px 0 0">Click a table name to insert it at cursor position.</p>';
		?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Available Tables', $_tbl_content ); ?>

		<!-- Run history -->
		<?php if ( $report ) : ?>
		<?php ob_start(); ?>
			<button id="ar-load-history" class="ah-btn ah-btn-secondary ah-btn-sm" style="float:right" data-report-id="<?php echo esc_attr( $report->id ); ?>">Load</button>
			<div id="ar-history-list" style="font-size:12px;color:var(--ah-muted)">Click Load to show history.</div>
		<?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Run History', ob_get_clean() ); ?>
		<?php endif; ?>

	</div>

</div><!-- /grid -->

<!-- Results area - always visible -->
<div id="ar-results-wrap" style="margin-top:24px">
	<?php ob_start(); ?>
		<div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;flex-wrap:wrap">
			<h2 style="margin:0;font-size:15px">Results</h2>
			<span id="ar-results-meta" style="font-size:13px;color:var(--ah-muted)"></span>
			<div style="margin-left:auto;display:flex;gap:8px" id="ar-export-btns" class="hidden">
				<button class="ah-btn ah-btn-secondary ar-export-btn" data-format="csv" style="font-size:12px;padding:4px 12px">
					&#8681; CSV
				</button>
				<button class="ah-btn ah-btn-secondary ar-export-btn" data-format="json" style="font-size:12px;padding:4px 12px">
					&#8681; JSON
				</button>
			</div>
		</div>

		<!-- Idle placeholder -->
		<div id="ar-results-placeholder" style="text-align:center;padding:40px 20px;color:var(--ah-muted)">
			<span class="dashicons dashicons-database" style="font-size:2rem;height:auto;width:auto;opacity:.3;display:block;margin-bottom:10px"></span>
			<p style="margin:0;font-size:13px">Write a query above and press <strong>Run Query</strong> (or <kbd style="background:#f1f5f9;padding:1px 5px;border-radius:3px;font-size:11px">Ctrl+Enter</kbd>) to see results here.</p>
		</div>

		<!-- Loading skeleton -->
		<div id="ar-results-loading" style="display:none;padding:16px 0">
			<div class="ar-skeleton" style="height:32px;margin-bottom:8px;border-radius:4px;width:100%"></div>
			<div class="ar-skeleton" style="height:24px;margin-bottom:6px;border-radius:4px;width:100%"></div>
			<div class="ar-skeleton" style="height:24px;margin-bottom:6px;border-radius:4px;width:92%"></div>
			<div class="ar-skeleton" style="height:24px;margin-bottom:6px;border-radius:4px;width:97%"></div>
			<div class="ar-skeleton" style="height:24px;border-radius:4px;width:88%"></div>
		</div>

		<div id="ar-results-error" style="display:none;color:#dc2626;font-size:13px;padding:10px;background:#fef2f2;border-radius:6px;border:1px solid #fca5a5"></div>
		<div style="overflow-x:auto">
			<table class="ah-table" id="ar-results-table" style="display:none;font-size:12px"></table>
		</div>
		<div id="ar-export-link" style="margin-top:10px;font-size:13px"></div>
	<?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Results', ob_get_clean() ); ?>
</div>

<input type="hidden" id="ar-report-id" value="<?php echo esc_attr( $report->id ?? 0 ); ?>">
<input type="hidden" id="ar-result-id" value="0">

<?php else : ?>
<!-- ══════════════════════════════════════════════════════════════
     LIST VIEW
══════════════════════════════════════════════════════════════ -->

<?php \Ah\Cms\Admin\Components\AdminComponents::pageHeader( 'chart-bar', 'Analytics Reports', 'View traffic reports, page performance, and visitor insights.' ); ?>
<?php ob_start(); ?>
	<div style="display:flex;justify-content:flex-end;">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-analytics&action=edit' ) ); ?>"
		   class="ah-btn ah-btn-primary">
			+ New Report
		</a>
	</div>
<?php echo ob_get_clean(); ?>

<?php if ( isset( $_GET['deleted'] ) ) : ?>
	<?php \Ah\Cms\Admin\Components\AdminComponents::notice( 'Report deleted.', 'success' ); ?>
<?php endif; ?>

<?php
$reports = ( new AH_Analytics_Report_Model() )->all_with_last_result();
?>

<?php if ( empty( $reports ) ) : ?>
	<?php \Ah\Cms\Admin\Components\AdminComponents::emptyState( 'No reports yet. Create your first report to get started.', 'chart-bar' ); ?>
	<div style="text-align:center;margin-top:12px;">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-analytics&action=edit' ) ); ?>"
		   class="ah-btn ah-btn-primary">Create Your First Report</a>
	</div>
<?php else : ?>
<?php \Ah\Cms\Admin\Components\AdminComponents::dataTable( array(
	'columns' => array(
		array( 'label' => 'Name', 'render' => function ( $r ) {
			return '<a href="' . esc_url( admin_url( 'admin.php?page=ah-analytics&action=edit&id=' . $r->id ) ) . '" style="font-weight:600">'
				. esc_html( $r->name ) . '</a>';
		} ),
		array( 'label' => 'Description', 'render' => function ( $r ) {
			return '<span style="color:var(--ah-muted);max-width:280px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block">'
				. esc_html( $r->description ?: '-' ) . '</span>';
		} ),
		array( 'label' => 'Last Run', 'style' => 'width:130px', 'render' => function ( $r ) {
			return '<span style="font-size:12px;color:var(--ah-muted)">'
				. ( $r->last_run_at ? esc_html( wp_date( 'M j Y g:i a', strtotime( $r->last_run_at ) ) ) : '<em>Never</em>' ) . '</span>';
		} ),
		array( 'label' => 'Runs', 'style' => 'width:70px;text-align:center', 'render' => function ( $r ) {
			return (int) $r->run_count;
		} ),
		array( 'label' => 'Last Status', 'style' => 'width:90px;text-align:center', 'render' => function ( $r ) {
			if ( ! $r->last_status ) return '-';
			$status_colors = [ 'success' => '#dcfce7|#16a34a', 'error' => '#fee2e2|#dc2626' ];
			[ $sbg, $sfg ] = explode( '|', $status_colors[ $r->last_status ] ?? '#f1f5f9|#64748b' );
			return '<span style="background:' . esc_attr( $sbg ) . ';color:' . esc_attr( $sfg ) . ';padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700">'
				. esc_html( strtoupper( $r->last_status ) ) . '</span>';
		} ),
		array( 'label' => 'Rows', 'style' => 'width:70px;text-align:center', 'render' => function ( $r ) {
			return $r->last_row_count !== null ? (int) $r->last_row_count : '-';
		} ),
		array( 'label' => 'ms', 'style' => 'width:60px;text-align:center', 'render' => function ( $r ) {
			return '<span style="font-size:11px;color:var(--ah-muted)">' . ( $r->last_exec_ms !== null ? (int) $r->last_exec_ms : '-' ) . '</span>';
		} ),
	),
	'items'         => $reports,
	'empty_message' => 'No reports yet.',
	'actions'       => function ( $r ) {
		$html = '<div style="display:flex;gap:6px;flex-wrap:wrap">';
		$html .= '<a href="' . esc_url( admin_url( 'admin.php?page=ah-analytics&action=edit&id=' . $r->id ) ) . '" class="ah-btn ah-btn-secondary" style="font-size:11px;padding:3px 8px">Edit</a>';
		if ( $r->last_status === 'success' ) {
			$html .= '<button class="ah-btn ah-btn-secondary ar-quick-export" style="font-size:11px;padding:3px 8px" data-report-id="' . esc_attr( $r->id ) . '" data-format="csv" title="Export last result as CSV">CSV</button>';
			$html .= '<button class="ah-btn ah-btn-secondary ar-quick-export" style="font-size:11px;padding:3px 8px" data-report-id="' . esc_attr( $r->id ) . '" data-format="json" title="Export last result as JSON">JSON</button>';
		}
		$html .= '<button class="ah-btn ar-delete-btn" style="font-size:11px;padding:3px 8px;background:#fee2e2;color:#dc2626;border:1px solid #fca5a5" data-id="' . esc_attr( $r->id ) . '" data-name="' . esc_attr( $r->name ) . '">Delete</button>';
		$html .= '</div>';
		return $html;
	},
) ); ?>
<?php endif; ?>

<?php endif; /* end list/edit switch */ ?>
</div><!-- .wrap -->

<style>
.ar-tbl-row:hover { background:#f1f5f9; }
.hidden { display:none !important; }

/* Skeleton shimmer */
@keyframes ar-shimmer {
	0%   { background-position: -600px 0; }
	100% { background-position:  600px 0; }
}
.ar-skeleton {
	background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
	background-size: 600px 100%;
	animation: ar-shimmer 1.4s infinite linear;
}

/* Result state transitions */
#ar-results-wrap { transition: opacity .15s; }
</style>

<script>
jQuery(function($){
	var nonce   = ahAdmin.nonce;
	var ajaxUrl = ahAdmin.ajaxUrl;
	var listUrl = '<?php echo esc_js( admin_url( 'admin.php?page=ah-analytics' ) ); ?>';

	/* ── LIST: delete ─────────────────────────────────────────── */
	$('.ar-delete-btn').on('click', function(){
		var id   = $(this).data('id');
		var name = $(this).data('name');
		if ( ! confirm('Delete report "' + name + '" and all its results + files? Cannot be undone.') ) return;
		var $row = $(this).closest('tr');
		$.post( ajaxUrl, { action:'ah_analytics_delete', nonce:nonce, id:id }, function(res){
			if ( res.success ) {
				$row.fadeOut(200, function(){ $(this).remove(); });
			} else {
				alert( res.data.message || 'Delete failed.' );
			}
		});
	});

	/* ── LIST: quick export ───────────────────────────────────── */
	$('.ar-quick-export').on('click', function(){
		var $btn  = $(this);
		var rid   = $btn.data('report-id');
		var fmt   = $btn.data('format');
		$btn.text('…').prop('disabled', true);
		$.post( ajaxUrl, { action:'ah_analytics_export', nonce:nonce, report_id:rid, format:fmt }, function(res){
			$btn.text( fmt.toUpperCase() ).prop('disabled', false);
			if ( res.success ) {
				window.location.href = res.data.file_url;
			} else {
				alert( res.data.message || 'Export failed.' );
			}
		});
	});

	/* ══════════════════════════════════════════════════════════
	   EDIT VIEW
	══════════════════════════════════════════════════════════ */
	if ( ! $('#ar-run-btn').length ) return;

	/* ── Table name click → insert at cursor ─────────────────── */
	$('.ar-tbl-row').on('click', function(){
		var tbl = $(this).data('table');
		var el  = document.getElementById('ar-sql');
		if ( ! el ) return;
		var start = el.selectionStart;
		var end   = el.selectionEnd;
		var val   = el.value;
		el.value  = val.substring(0, start) + tbl + val.substring(end);
		el.selectionStart = el.selectionEnd = start + tbl.length;
		el.focus();
	});

	/* ── Result state helpers ────────────────────────────────── */
	function arShowPlaceholder( msg ) {
		$('#ar-results-loading').hide();
		$('#ar-results-error').hide();
		$('#ar-results-table').hide().empty();
		$('#ar-export-btns').addClass('hidden');
		$('#ar-results-meta').text('');
		$('#ar-export-link').empty();
		if ( msg ) {
			$('#ar-results-placeholder')
				.find('p').html( msg ).end()
				.show();
		} else {
			$('#ar-results-placeholder').show();
		}
	}

	function arShowLoading() {
		$('#ar-results-placeholder').hide();
		$('#ar-results-error').hide();
		$('#ar-results-table').hide().empty();
		$('#ar-export-btns').addClass('hidden');
		$('#ar-results-meta').text('Running…');
		$('#ar-results-loading').show();
	}

	function arShowResults( d, reportId ) {
		$('#ar-results-loading').hide();
		$('#ar-results-placeholder').hide();
		$('#ar-results-error').hide();
		$('#ar-result-id').val( d.result_id || 0 );
		$('#ar-exec-info').text( d.exec_ms + 'ms · ' + d.row_count + ' row' + (d.row_count !== 1 ? 's' : '') );

		var meta = d.row_count + ' row' + (d.row_count !== 1 ? 's' : '');
		if ( d.truncated ) meta += ' (preview limited to <?php echo esc_js( AH_Analytics_Ajax::PREVIEW_LIMIT ); ?>)';
		$('#ar-results-meta').text( meta );

		if ( d.columns && d.columns.length ) {
			var $table = $('#ar-results-table');
			var thead  = '<thead><tr>' + d.columns.map(function(c){ return '<th>' + $('<span>').text(c).html() + '</th>'; }).join('') + '</tr></thead>';
			var tbody  = '<tbody>' + d.rows.map(function(row){
				return '<tr>' + d.columns.map(function(c){
					var v = row[c];
					return '<td>' + ( v === null ? '<em style="color:#94a3b8">NULL</em>' : $('<span>').text(String(v)).html() ) + '</td>';
				}).join('') + '</tr>';
			}).join('') + '</tbody>';
			$table.html( thead + tbody ).show();
			if ( reportId > 0 ) {
				$('#ar-export-btns').removeClass('hidden');
			}
		} else {
			arShowPlaceholder('Query ran successfully but returned <strong>no rows</strong>.');
		}
	}

	/* ── Toggle Report Type ──────────────────────────────────── */
	$('#ar-type').on('change', function(){
		var t = $(this).val();
		if ( t === 'php' ) {
			$('#ar-sql-wrap').hide();
			$('#ar-php-wrap').show();
		} else {
			$('#ar-sql-wrap').show();
			$('#ar-php-wrap').hide();
		}
	});

	/* ── Run query ───────────────────────────────────────────── */
	$('#ar-run-btn').on('click', function(){
		var sql      = $('#ar-sql').val().trim();
		var php      = $('#ar-php').val().trim();
		var type     = $('#ar-type').val();
		var reportId = $('#ar-report-id').val();
		if ( type === 'sql' && ! sql ) { alert('Enter a query first.'); return; }
		if ( type === 'php' && ! php ) { alert('Enter PHP code first.'); return; }

		var $btn = $(this);
		$btn.text('Running…').prop('disabled', true);
		arShowLoading();

		$.post( ajaxUrl, {
			action      : 'ah_analytics_run',
			nonce       : nonce,
			report_type : type,
			query_sql   : sql,
			query_php   : php,
			report_id   : reportId,
		}, function(res){
			$btn.text('▶ Run Query').prop('disabled', false);
			if ( ! res.success ) {
				$('#ar-results-loading').hide();
				$('#ar-results-placeholder').hide();
				$('#ar-results-meta').text('');
				$('#ar-exec-info').text('');
				$('#ar-results-error').text( res.data.message || 'Query failed.' ).show();
				return;
			}
			arShowResults( res.data, parseInt( reportId, 10 ) );
		});
	});

	/* ── Auto-load last result when opening a saved report ───── */
	(function(){
		var reportId = parseInt( $('#ar-report-id').val(), 10 );
		if ( ! reportId ) return;

		arShowLoading();
		$.post( ajaxUrl, { action:'ah_analytics_history', nonce:nonce, report_id:reportId }, function(res){
			if ( ! res.success || ! res.data.history.length ) {
				arShowPlaceholder();
				return;
			}
			var latest = res.data.history[0];
			if ( latest.status !== 'success' ) {
				arShowPlaceholder( 'Last run on <strong>' + latest.run_at + '</strong> returned an error. Run the query to try again.' );
				return;
			}

			var type = $('#ar-type').val();
			var sql  = $('#ar-sql').val().trim();
			var php  = $('#ar-php').val().trim();
			if ( type === 'sql' && ! sql ) { arShowPlaceholder(); return; }
			if ( type === 'php' && ! php ) { arShowPlaceholder(); return; }

			$.post( ajaxUrl, {
				action      : 'ah_analytics_run',
				nonce       : nonce,
				report_type : type,
				query_sql   : sql,
				query_php   : php,
				report_id   : reportId,
			}, function(r){
				if ( r.success ) {
					arShowResults( r.data, reportId );
					$('#ar-results-meta').text(
						$('#ar-results-meta').text() + ' · auto-loaded from last run'
					);
				} else {
					arShowPlaceholder();
				}
			});
		});
	}());

	/* ── Save report ─────────────────────────────────────────── */
	$('#ar-save-btn').on('click', function(){
		var type = $('#ar-type').val();
		var vis  = $('#ar-api-vis').val();
		var sql  = $('#ar-sql').val().trim();
		var php  = $('#ar-php').val().trim();
		var name = $('#ar-name').val().trim();
		var desc = $('#ar-desc').val().trim();
		var id   = $('#ar-report-id').val();
		if ( ! name ) { alert('Report name is required.'); return; }
		if ( type === 'sql' && ! sql )  { alert('Query is required.'); return; }
		if ( type === 'php' && ! php )  { alert('PHP Code is required.'); return; }

		var $btn = $(this);
		$btn.text('Saving…').prop('disabled', true);
		$('#ar-save-status').text('');

		$.post( ajaxUrl, {
			action         : 'ah_analytics_save',
			nonce          : nonce,
			id             : id,
			name           : name,
			description    : desc,
			report_type    : type,
			api_visibility : vis,
			query_sql      : sql,
			query_php      : php,
		}, function(res){
			$btn.text('✓ Save Report').prop('disabled', false);
			if ( res.success ) {
				$('#ar-save-status').css('color','#16a34a').text('Saved!');
				if ( ! id || id === '0' ) {
					/* Redirect to edit page for the new report */
					window.location.href = listUrl + '&action=edit&id=' + res.data.id + '&saved=1';
				}
				setTimeout(function(){ $('#ar-save-status').text(''); }, 2500);
			} else {
				$('#ar-save-status').css('color','#dc2626').text( res.data.message || 'Save failed.' );
			}
		});
	});

	/* ── Export from editor ──────────────────────────────────── */
	$('.ar-export-btn').on('click', function(){
		var fmt      = $(this).data('format');
		var reportId = $('#ar-report-id').val();
		var resultId = $('#ar-result-id').val();
		if ( ! reportId || reportId === '0' ) { alert('Save the report first before exporting.'); return; }

		var $btn = $(this);
		$btn.text('…').prop('disabled', true);
		$.post( ajaxUrl, {
			action    : 'ah_analytics_export',
			nonce     : nonce,
			report_id : reportId,
			result_id : resultId,
			format    : fmt,
		}, function(res){
			$btn.text( '↓ ' + fmt.toUpperCase() ).prop('disabled', false);
			if ( res.success ) {
				$('#ar-export-link').html(
					'<a href="' + res.data.file_url + '" target="_blank" style="color:#2563eb;font-size:13px">&#8599; ' +
					$('<span>').text(res.data.filename).html() +
					' (' + res.data.row_count + ' rows)</a>'
				);
				window.open( res.data.file_url, '_blank' );
			} else {
				alert( res.data.message || 'Export failed.' );
			}
		});
	});

	/* ── Run history ─────────────────────────────────────────── */
	$('#ar-load-history').on('click', function(){
		var rid  = $(this).data('report-id');
		var $btn = $(this);
		$btn.text('…').prop('disabled', true);
		$.post( ajaxUrl, { action:'ah_analytics_history', nonce:nonce, report_id:rid }, function(res){
			$btn.text('Load').prop('disabled', false);
			if ( ! res.success ) return;
			var rows = res.data.history;
			if ( ! rows.length ) { $('#ar-history-list').text('No runs recorded yet.'); return; }
			var html = rows.map(function(r){
				var col = r.status === 'success' ? '#16a34a' : '#dc2626';
				var exp = r.export_file ? ' · <a href="javascript:void(0)" class="ar-hist-load" data-rid="'+rid+'" data-resid="'+r.id+'" style="color:#2563eb">re-export</a>' : '';
				return '<div style="padding:5px 0;border-bottom:1px solid #f1f5f9;font-size:12px">' +
					'<span style="color:' + col + ';font-weight:700">' + r.status.toUpperCase() + '</span> ' +
					r.run_at + ' · ' + r.row_count + ' rows · ' + r.exec_ms + 'ms' + exp +
					'</div>';
			}).join('');
			$('#ar-history-list').html(html);
		});
	});

	/* Ctrl+Enter to run */
	$('#ar-sql, #ar-php').on('keydown', function(e){
		if ( e.ctrlKey && e.key === 'Enter' ) $('#ar-run-btn').trigger('click');
	});

});
</script>
