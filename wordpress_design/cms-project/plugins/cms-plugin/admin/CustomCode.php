<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use Ah\Cms\Admin\Components\AdminComponents;

$cc_model  = new AH_Custom_Code_Model();
$entries   = $cc_model->get_all();
$active_tab = sanitize_key( $_GET['tab'] ?? 'per-page' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$action     = sanitize_key( $_GET['action'] ?? 'list' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$edit_id    = isset( $_GET['edit'] ) ? (int) $_GET['edit'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$notice     = '';
$n_type     = 'success';

$edit_row = null;
if ( $edit_id ) {
	$edit_row = $cc_model->find( $edit_id );
}

// Global styles
$gs_css    = (string) get_option( 'ah_global_styles_css', '' );
$gs_js     = (string) get_option( 'ah_global_styles_js', '' );
$gs_active = (int) get_option( 'ah_global_styles_active', 0 );
$gs_nonce  = wp_create_nonce( 'ah_custom_code' );
?>
<div class="wrap ah-wrap">
	<?php AdminComponents::pageHeader( 'editor-code', 'Custom Code', 'Inject per-page or global CSS and JavaScript snippets.' ); ?>
	<?php if ( $notice ) : ?><?php AdminComponents::notice( $notice, $n_type ); ?><?php endif; ?>

	<?php AdminComponents::tabBarUrl( array(
		'per-page'      => 'Per-Page Rules',
		'global-styles' => 'Global CSS / JS',
	), $active_tab, 'ah-custom-code' ); ?>

<?php if ( $active_tab === 'global-styles' ) : ?>
	<!-- ══════════════════ GLOBAL STYLES TAB ══════════════════ -->
	<p style="color:var(--ah-muted);margin:0 0 20px;">Global CSS and JavaScript that loads on every page sitewide - perfect for celebration themes, seasonal tweaks, analytics scripts, or campaign overrides.</p>

	<div style="display:grid;grid-template-columns:1fr 280px;gap:20px;align-items:start;">
		<div class="ah-card" style="padding:20px;">
			<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
				<label style="font-weight:600;font-size:13px;">Global CSS &amp; JS</label>
				<label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
					<span style="color:var(--ah-muted);">Inject on site</span>
					<input type="checkbox" id="ah-gs-active" <?php checked( $gs_active, 1 ); ?>>
				</label>
			</div>

			<div style="margin-bottom:20px;">
				<p style="color:var(--ah-text);font-size:13px;font-weight:600;margin:0 0 4px;">CSS</p>
				<p style="color:var(--ah-muted);font-size:12px;margin:0 0 8px;">No <code>&lt;style&gt;</code> tags needed. Loads in <code>&lt;head&gt;</code>.</p>
				<textarea id="ah-gs-css" rows="15"
					style="width:100%;font-family:monospace;font-size:12.5px;line-height:1.6;resize:vertical;background:#1e1e2e;color:#cdd6f4;padding:14px;border-radius:6px;border:1px solid #313244;"
					placeholder="/* Example: Christmas theme */&#10;body { --color-primary: #c0392b; }"
				><?php echo esc_textarea( $gs_css ); ?></textarea>
			</div>

			<div>
				<p style="color:var(--ah-text);font-size:13px;font-weight:600;margin:0 0 4px;">JavaScript</p>
				<p style="color:var(--ah-muted);font-size:12px;margin:0 0 8px;">No <code>&lt;script&gt;</code> tags needed. Loads in <code>&lt;footer&gt;</code>.</p>
				<textarea id="ah-gs-js" rows="15"
					style="width:100%;font-family:monospace;font-size:12.5px;line-height:1.6;resize:vertical;background:#1e1e2e;color:#a6e3a1;padding:14px;border-radius:6px;border:1px solid #313244;"
					placeholder="// Example: Sitewide tracking&#10;console.log('Global JS loaded!');"
				><?php echo esc_textarea( $gs_js ); ?></textarea>
			</div>

			<div style="margin-top:16px;display:flex;gap:12px;align-items:center;">
				<button id="ah-gs-save-btn" class="ah-btn ah-btn-primary">Save Global Code</button>
				<span id="ah-gs-msg" style="font-size:13px;"></span>
			</div>
		</div>

		<div>
			<div class="ah-card" style="padding:16px;font-size:13px;line-height:1.7;">
				<strong>Status</strong><br>
				<span id="ah-gs-status-label" style="color:<?php echo $gs_active ? 'var(--ah-success)' : 'var(--ah-danger)'; ?>;font-weight:600;">
					<?php echo $gs_active ? 'Active - injecting on all pages' : 'Disabled - not injecting'; ?>
				</span>
			</div>
			<div class="ah-card" style="padding:16px;margin-top:12px;font-size:12px;color:var(--ah-muted);line-height:1.7;">
				<strong style="color:var(--ah-text);">Use cases</strong><br>
				Christmas / seasonal theme<br>
				Sitewide font override<br>
				Sitewide tracking scripts<br>
				Campaign accent colour<br>
				A/B test styles & scripts<br><br>
				<strong style="color:var(--ah-text);">Tip</strong><br>
				Uncheck "Inject on site" to draft code without it going live.
			</div>
		</div>
	</div>

<?php else : ?>
	<!-- ══════════════════ PER-PAGE RULES TAB ══════════════════ -->
	<?php if ( $action === 'edit' || $action === 'add' ) : ?>
		<?php AdminComponents::backLink( add_query_arg( array( 'page' => 'ah-custom-code', 'tab' => 'per-page' ), admin_url( 'admin.php' ) ) ); ?>
		<?php ob_start(); ?>
			<form method="post" id="ah-cc-form">
				<?php wp_nonce_field( 'ah_custom_code', 'nonce' ); ?>
				<input type="hidden" name="entry_id" value="<?php echo esc_attr( $edit_id ); ?>">

				<div style="display:flex;justify-content:flex-end;gap:8px;margin-bottom:16px;">
					<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-custom-code', 'tab' => 'per-page' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary">Cancel</a>
					<?php if ( $edit_row ) : ?>
					<button type="button" id="ah-cc-delete-btn" class="ah-btn ah-btn-danger ah-btn-sm ah-confirm-delete" data-title="Delete Rule" data-confirm="This rule will be deleted and stop injecting immediately.">Delete</button>
					<?php endif; ?>
					<button type="submit" id="ah-cc-save-btn" class="ah-btn ah-btn-primary">Save Rule</button>
				</div>

				<div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;">
					<!-- Left: Code editor -->
					<div>
						<?php AdminComponents::formRow( 'Apply to page slug',
							'<div style="display:flex;align-items:center;gap:8px;">'
							. '<span style="font-size:14px;color:var(--ah-muted);">/</span>'
							. '<input type="text" id="ah-cc-slug" name="slug" value="' . esc_attr( $edit_row ? $edit_row->slug : '' ) . '" placeholder="e.g. buying" required style="flex:1;">'
							. '<span style="font-size:14px;color:var(--ah-muted);">/</span>'
							. '</div>'
							. '<p style="color:var(--ah-muted);font-size:12px;margin:4px 0 0;">Lowercase, no slashes. Matches <code>/buying/</code>, <code>/selling/</code> etc.</p>'
						); ?>

						<!-- Code tabs -->
						<div style="border-bottom:2px solid var(--ah-border);margin-bottom:0;">
							<button type="button" class="ah-cc-tab-btn ah-tab ah-tab-active" data-tab="css" style="border-bottom:2px solid var(--ah-primary);margin-bottom:-2px;">CSS</button>
							<button type="button" class="ah-cc-tab-btn ah-tab" data-tab="js">JavaScript</button>
						</div>

						<div id="ah-cc-panel-css" class="ah-cc-panel" style="padding-top:12px;">
							<p style="color:var(--ah-muted);font-size:12px;margin:0 0 6px;">Plain CSS rules - no <code>&lt;style&gt;</code> tags needed.</p>
							<textarea id="ah-cc-css" name="css" rows="24"
								style="width:100%;font-family:monospace;font-size:12.5px;line-height:1.6;resize:vertical;background:#1e1e2e;color:#cdd6f4;padding:14px;border-radius:6px;border:1px solid #313244;box-sizing:border-box;"
								placeholder="/* Example */&#10;.some-class {&#10;  word-break: break-word;&#10;}"
							><?php echo esc_textarea( $edit_row ? ( $edit_row->css ?? '' ) : '' ); ?></textarea>
						</div>

						<div id="ah-cc-panel-js" class="ah-cc-panel" style="padding-top:12px;display:none;">
							<p style="color:var(--ah-muted);font-size:12px;margin:0 0 6px;">Plain JavaScript - no <code>&lt;script&gt;</code> tags needed. Runs at footer.</p>
							<textarea id="ah-cc-js" name="js" rows="24"
								style="width:100%;font-family:monospace;font-size:12.5px;line-height:1.6;resize:vertical;background:#1e1e2e;color:#a6e3a1;padding:14px;border-radius:6px;border:1px solid #313244;box-sizing:border-box;"
								placeholder="// Example&#10;document.querySelectorAll('.dynamic-text').forEach(function(el) {&#10;  el.style.wordBreak = 'break-word';&#10;});"
							><?php echo esc_textarea( $edit_row ? ( $edit_row->js ?? '' ) : '' ); ?></textarea>
						</div>
					</div>

					<!-- Right: Info -->
					<div>
						<?php if ( $edit_row ) : ?>
						<div class="ah-card" style="padding:14px;font-size:13px;line-height:1.7;">
							<strong>Status</strong><br>
							<span style="color:<?php echo empty( $edit_row->is_active ) ? 'var(--ah-danger)' : 'var(--ah-success)'; ?>;font-weight:600;">
								<?php echo empty( $edit_row->is_active ) ? 'Paused' : 'Active'; ?>
							</span>
							- code <?php echo empty( $edit_row->is_active ) ? 'will not inject' : 'injects'; ?> on
							<a href="<?php echo esc_url( home_url( '/' . $edit_row->slug . '/' ) ); ?>" target="_blank" style="color:var(--ah-primary);">/<?php echo esc_html( $edit_row->slug ); ?>/</a>
						</div>
						<?php endif; ?>

						<div class="ah-card" style="padding:14px;margin-top:12px;font-size:12px;color:var(--ah-muted);line-height:1.7;">
							<strong style="color:var(--ah-text);">How it works</strong><br>
							1. Enter the page slug (e.g. <code>buying</code>).<br>
							2. Write CSS and/or JS in the editor tabs.<br>
							3. Save - code injects into <code>&lt;head&gt;</code> (CSS) and <code>&lt;footer&gt;</code> (JS) only on that slug.<br><br>
							<strong style="color:var(--ah-text);">Scope</strong><br>
							Works on WP pages, virtual routes (<code>/buying/</code>), and static pages.
						</div>
					</div>
				</div>
			</form>
		<?php AdminComponents::card( $edit_row ? 'Edit: /' . esc_html( $edit_row->slug ) . '/' : 'New Rule', ob_get_clean() ); ?>

	<?php else : ?>
		<!-- List page -->
		<?php
		$search = sanitize_text_field( $_GET['s'] ?? '' );
		$filtered = $entries;
		if ( $search ) {
			$filtered = array_values( array_filter( $filtered, function ( $e ) use ( $search ) {
				return stripos( $e->slug, $search ) !== false;
			} ) );
		}
		?>

		<?php AdminComponents::filterBar( array(
			'page_slug'          => 'ah-custom-code',
			'search_placeholder' => 'Search rules...',
			'search_value'       => $search,
			'hidden_inputs'      => array( 'tab' => 'per-page' ),
			'add_url'            => add_query_arg( array( 'page' => 'ah-custom-code', 'tab' => 'per-page', 'action' => 'add' ), admin_url( 'admin.php' ) ),
			'add_label'          => '+ New Rule',
		) ); ?>

		<?php
		$rows = array();
		foreach ( $filtered as $e ) {
			$row = new \stdClass();
			$row->id        = (int) $e->id;
			$row->slug      = $e->slug;
			$row->is_active = ! empty( $e->is_active );
			$row->has_css   = '' !== trim( $e->css ?? '' );
			$row->has_js    = '' !== trim( $e->js ?? '' );
			$row->edit_url  = add_query_arg( array( 'page' => 'ah-custom-code', 'tab' => 'per-page', 'action' => 'edit', 'edit' => $e->id ), admin_url( 'admin.php' ) );
			$rows[] = $row;
		}
		AdminComponents::dataTable( array(
			'columns' => array(
				array( 'label' => 'Page Slug', 'render' => function ( $r ) {
					return '<a href="' . esc_url( $r->edit_url ) . '" style="font-family:monospace;font-weight:600;font-size:13px;text-decoration:none;color:var(--ah-text);">/' . esc_html( $r->slug ) . '/</a>';
				} ),
				array( 'label' => 'Code', 'render' => function ( $r ) {
					$html = '';
					if ( $r->has_css ) $html .= '<span class="ah-badge" style="background:#dbeafe;color:#1d4ed8;">CSS</span> ';
					if ( $r->has_js )  $html .= '<span class="ah-badge" style="background:#dcfce7;color:#15803d;">JS</span>';
					return $html ?: '<span style="color:var(--ah-muted);font-size:12px;">Empty</span>';
				} ),
				array( 'label' => 'Status', 'render' => function ( $r ) {
					return $r->is_active
						? '<span class="ah-badge ah-badge-active">Active</span>'
						: '<span class="ah-badge ah-badge-inactive">Paused</span>';
				} ),
				array( 'label' => 'Preview', 'render' => function ( $r ) {
					return '<a href="' . esc_url( home_url( '/' . $r->slug . '/' ) ) . '" target="_blank" style="font-size:12px;color:var(--ah-primary);">/' . esc_html( $r->slug ) . '/ &rarr;</a>';
				} ),
			),
			'items'         => $rows,
			'empty_message' => $search ? 'No rules match your search.' : 'No rules yet. Click "+ New Rule" to create one.',
			'actions'       => function ( $r ) {
				$html = '<a href="' . esc_url( $r->edit_url ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>';
				$html .= ' <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-cc-toggle" data-id="' . esc_attr( $r->id ) . '" title="' . ( $r->is_active ? 'Pause' : 'Enable' ) . '">' . ( $r->is_active ? 'Pause' : 'Enable' ) . '</button>';
				return $html;
			},
		) ); ?>
	<?php endif; ?>
<?php endif; ?>
</div>

<style>
.ah-cc-tab-btn { padding:10px 20px; font-size:13px; font-weight:600; border:none; background:none; border-bottom:2px solid transparent; margin-bottom:-2px; cursor:pointer; color:var(--ah-muted); border-radius:var(--ah-radius) var(--ah-radius) 0 0; }
.ah-cc-tab-btn:hover { color:var(--ah-text); }
.ah-cc-tab-btn.active { color:var(--ah-primary); border-bottom-color:var(--ah-primary); }
</style>

<script>
jQuery(function ($) {
	var editing = <?php echo wp_json_encode( $edit_id ); ?>;
	var nonce   = <?php echo wp_json_encode( $gs_nonce ); ?>;

	/* ── Code tabs ── */
	$('.ah-cc-tab-btn').on('click', function () {
		var tab = $(this).data('tab');
		$('.ah-cc-tab-btn').removeClass('active').css('border-bottom-color', 'transparent');
		$(this).addClass('active').css('border-bottom-color', 'var(--ah-primary)');
		$('.ah-cc-panel').hide();
		$('#ah-cc-panel-' + tab).show();
	});

	/* ── Save per-page rule ── */
	$('#ah-cc-form').on('submit', function (e) {
		e.preventDefault();
		var $btn = $('#ah-cc-save-btn');
		var slug = $.trim($('#ah-cc-slug').val()).toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/^-+|-+$/g, '');
		var css  = $('#ah-cc-css').val();
		var js   = $('#ah-cc-js').val();

		if (!slug) { alert('Enter a page slug first.'); $('#ah-cc-slug').focus(); return; }
		if (!css.trim() && !js.trim()) { alert('Write some CSS or JS before saving.'); return; }

		$btn.prop('disabled', true).text('Saving...');

		$.post(ajaxurl, {
			action: 'ah_save_custom_code', nonce: nonce, entry_id: editing, slug: slug, css: css, js: js,
		}, function (res) {
			$btn.prop('disabled', false).text('Save Rule');
			if (res.success && res.data.redirect) {
				location.href = res.data.redirect;
			} else if (!res.success) {
				alert(res.data ? res.data.message : 'Error.');
			}
		}).fail(function () {
			$btn.prop('disabled', false).text('Save Rule');
			alert('Request failed.');
		});
	});

	/* ── Delete per-page rule ── */
	$('#ah-cc-delete-btn').on('click', function () {
		var $btn = $(this);
		$btn.prop('disabled', true).text('Deleting...');
		$.post(ajaxurl, { action: 'ah_delete_custom_code', nonce: nonce, entry_id: $btn.data('id') }, function (res) {
			if (res.success) {
				location.href = '<?php echo esc_js( add_query_arg( array( 'page' => 'ah-custom-code', 'tab' => 'per-page' ), admin_url( 'admin.php' ) ) ); ?>';
			} else {
				$btn.prop('disabled', false).text('Delete');
				alert(res.data ? res.data.message : 'Error.');
			}
		});
	});

	/* ── Toggle active / paused ── */
	$(document).on('click', '.ah-cc-toggle', function () {
		var $btn = $(this);
		var id   = $btn.data('id');
		$btn.prop('disabled', true);
		$.post(ajaxurl, { action: 'ah_toggle_custom_code', nonce: nonce, entry_id: id }, function (res) {
			if (res.success) {
				$btn.text(res.data.active ? 'Pause' : 'Enable').prop('disabled', false);
				// Refresh to update status badge
				location.reload();
			} else {
				$btn.prop('disabled', false);
			}
		});
	});

	/* ── Global styles save ── */
	$('#ah-gs-save-btn').on('click', function () {
		var $btn = $(this);
		$btn.prop('disabled', true).text('Saving...');
		$('#ah-gs-msg').text('');
		$.post(ajaxurl, {
			action: 'ah_save_global_styles', nonce: nonce,
			css: $('#ah-gs-css').val(), js: $('#ah-gs-js').val(),
			active: $('#ah-gs-active').is(':checked') ? 1 : 0,
		}, function (res) {
			$btn.prop('disabled', false).text('Save Global Code');
			if (res.success) {
				$('#ah-gs-msg').css('color', 'var(--ah-success)').text(res.data.message);
				var on = $('#ah-gs-active').is(':checked');
				$('#ah-gs-status-label')
					.css('color', on ? 'var(--ah-success)' : 'var(--ah-danger)')
					.text(on ? 'Active - injecting on all pages' : 'Disabled - not injecting');
			} else {
				$('#ah-gs-msg').css('color', 'var(--ah-danger)').text(res.data ? res.data.message : 'Error.');
			}
		}).fail(function () {
			$btn.prop('disabled', false).text('Save Global Code');
			$('#ah-gs-msg').css('color', 'var(--ah-danger)').text('Request failed.');
		});
	});
});
</script>
