<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

global $wpdb;
$table = $wpdb->prefix . 'ah_custom_code';

$entries  = $wpdb->get_results( "SELECT * FROM `{$table}` ORDER BY created_at DESC" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$edit_id  = isset( $_GET['edit'] ) ? (int) $_GET['edit'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$edit_row = null;
if ( $edit_id ) {
	foreach ( $entries as $e ) {
		if ( (int) $e->id === $edit_id ) { $edit_row = $e; break; }
	}
}
?>
<?php
$active_tab = sanitize_key( $_GET['tab'] ?? 'per-page' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$gs_css     = (string) get_option( 'ah_global_styles_css', '' );
$gs_active  = (int) get_option( 'ah_global_styles_active', 0 );
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-editor-code"></span> Custom Code</h1>

	<!-- ── Top tabs ── -->
	<div style="display:flex;gap:0;border-bottom:2px solid #e5e7eb;margin:16px 0 24px;">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-custom-code&tab=per-page' ) ); ?>"
		   style="padding:9px 20px;font-size:13px;font-weight:600;text-decoration:none;border-bottom:<?php echo $active_tab !== 'global-styles' ? '2px solid var(--ah-primary,#1d4ed8);color:var(--ah-primary,#1d4ed8)' : '2px solid transparent;color:var(--ah-muted)'; ?>;margin-bottom:-2px;">
			Per-Page Rules
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-custom-code&tab=global-styles' ) ); ?>"
		   style="padding:9px 20px;font-size:13px;font-weight:600;text-decoration:none;border-bottom:<?php echo $active_tab === 'global-styles' ? '2px solid var(--ah-primary,#1d4ed8);color:var(--ah-primary,#1d4ed8)' : '2px solid transparent;color:var(--ah-muted)'; ?>;margin-bottom:-2px;">
			🎨 Global Styles
		</a>
	</div>

<?php if ( $active_tab === 'global-styles' ) : ?>
	<!-- ══════════════════ GLOBAL STYLES TAB ══════════════════ -->
	<p style="color:var(--ah-muted);margin:0 0 20px;">Global CSS that loads on every page sitewide - perfect for celebration themes, seasonal tweaks, or campaign overrides.</p>

	<div style="display:grid;grid-template-columns:1fr 280px;gap:20px;align-items:start;">
		<div class="ah-card" style="padding:20px;">
			<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
				<label style="font-weight:600;font-size:13px;">Global CSS</label>
				<label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
					<span style="color:var(--ah-muted);">Inject on site</span>
					<input type="checkbox" id="ah-gs-active" <?php checked( $gs_active, 1 ); ?>>
				</label>
			</div>
			<p style="color:var(--ah-muted);font-size:12px;margin:0 0 8px;">No <code>&lt;style&gt;</code> tags needed. Loads in <code>&lt;head&gt;</code> on every page when enabled.</p>
			<textarea id="ah-gs-css" rows="30"
				style="width:100%;font-family:monospace;font-size:12.5px;line-height:1.6;resize:vertical;background:#1e1e2e;color:#cdd6f4;padding:14px;border-radius:6px;border:1px solid #313244;"
				placeholder="/* Example: Christmas theme */
body { --color-primary: #c0392b; }
.site-header { background: linear-gradient(135deg,#1a472a,#2d6a4f); }
.confetti { display: block; }"
			><?php echo esc_textarea( $gs_css ); ?></textarea>

			<div style="margin-top:14px;display:flex;gap:12px;align-items:center;">
				<button id="ah-gs-save-btn" class="ah-btn ah-btn-primary">Save Global Styles</button>
				<span id="ah-gs-msg" style="font-size:13px;"></span>
			</div>
		</div>

		<div>
			<div class="ah-card" style="padding:16px;font-size:13px;line-height:1.7;">
				<strong>Status</strong><br>
				<span id="ah-gs-status-label" style="color:<?php echo $gs_active ? '#15803d' : '#b91c1c'; ?>;font-weight:600;">
					<?php echo $gs_active ? '● Active - injecting on all pages' : '○ Disabled - not injecting'; ?>
				</span>
			</div>
			<div class="ah-card" style="padding:16px;margin-top:12px;font-size:12px;color:var(--ah-muted);line-height:1.7;">
				<strong style="color:var(--ah-text);">Use cases</strong><br>
				• Christmas / seasonal theme<br>
				• Sitewide font override<br>
				• Campaign accent colour<br>
				• Celebratory banner CSS<br>
				• A/B test styles<br><br>
				<strong style="color:var(--ah-text);">Tip</strong><br>
				Uncheck "Inject on site" to draft styles without them going live.
			</div>
		</div>
	</div>

<?php else : ?>
	<!-- ══════════════════ PER-PAGE RULES TAB ══════════════════ -->
	<p style="color:var(--ah-muted);margin:0 0 20px;">Write custom CSS or JS that only loads on a specific page slug - useful for per-page typography fixes, dynamic content tweaks, and layout overrides.</p>

	<div style="display:grid;grid-template-columns:260px 1fr;gap:20px;align-items:start;">

		<!-- ── Sidebar list ── -->
		<div>
			<div class="ah-card" style="padding:16px;">
				<h3 style="margin:0 0 12px;font-size:13px;text-transform:uppercase;letter-spacing:.05em;color:var(--ah-muted);">Page Rules</h3>

				<div id="ah-cc-list">
					<?php foreach ( $entries as $e ) : ?>
					<div class="ah-cc-item<?php echo ( $edit_id === (int) $e->id ) ? ' active' : ''; ?>">
						<span class="ah-cc-slug">
							/<?php echo esc_html( $e->slug ); ?>/
							<span class="ah-cc-badges">
								<?php if ( '' !== trim( $e->css ?? '' ) ) echo '<span class="ah-cc-badge" style="background:#dbeafe;color:#1d4ed8;">CSS</span>'; ?>
								<?php if ( '' !== trim( $e->js  ?? '' ) ) echo '<span class="ah-cc-badge" style="background:#dcfce7;color:#15803d;">JS</span>'; ?>
							</span>
						</span>
						<div class="ah-cc-actions">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-custom-code&edit=' . (int) $e->id ) ); ?>" class="ah-sl-btn">Edit</a>
							<button class="ah-sl-btn ah-cc-toggle"
								data-id="<?php echo (int) $e->id; ?>"
								title="<?php echo empty( $e->is_active ) ? 'Enable' : 'Pause'; ?>">
								<?php echo empty( $e->is_active ) ? '▶' : '⏸'; ?>
							</button>
						</div>
					</div>
					<?php endforeach; ?>
					<?php if ( empty( $entries ) ) : ?>
					<p style="color:var(--ah-muted);font-size:13px;margin:0 0 8px;">No rules yet.</p>
					<?php endif; ?>
				</div>

				<hr style="margin:12px 0 10px;">
				<button id="ah-cc-new-btn" class="ah-btn ah-btn-primary" style="width:100%;">+ New Rule</button>
			</div>

			<div class="ah-card" style="padding:14px;margin-top:12px;font-size:12px;color:var(--ah-muted);line-height:1.6;">
				<strong style="color:var(--ah-text);">How it works</strong><br>
				1. Enter the page slug (e.g. <code>buying</code>).<br>
				2. Write CSS and/or JS in the editor tabs.<br>
				3. Save - code injects into <code>&lt;head&gt;</code> (CSS) and <code>&lt;footer&gt;</code> (JS) only on that slug.<br><br>
				<strong style="color:var(--ah-text);">Scope</strong><br>
				Works on WP pages, virtual routes (<code>/buying/</code>), and static pages.
			</div>
		</div>

		<!-- ── Editor ── -->
		<div>
			<div class="ah-card" style="padding:20px;">

				<div style="margin-bottom:16px;">
					<label style="font-weight:600;font-size:13px;">Page Slug</label>
					<div style="display:flex;align-items:center;gap:8px;margin-top:6px;">
						<span style="font-size:14px;color:var(--ah-muted);">/</span>
						<input
							type="text"
							id="ah-cc-slug"
							value="<?php echo esc_attr( $edit_row ? $edit_row->slug : '' ); ?>"
							placeholder="e.g. buying"
							class="regular-text"
							style="flex:1;"
						>
						<span style="font-size:14px;color:var(--ah-muted);">/</span>
					</div>
					<p style="color:var(--ah-muted);font-size:12px;margin:4px 0 0;">Lowercase, no slashes. Matches <code>/buying/</code>, <code>/selling/</code> etc.</p>
				</div>

				<!-- ── Code tabs ── -->
				<div class="ah-cc-tabs">
					<div class="ah-cc-tab-btns" style="display:flex;gap:0;border-bottom:2px solid #e5e7eb;margin-bottom:0;">
						<button class="ah-cc-tab-btn active" data-tab="css"
							style="padding:8px 20px;font-size:13px;font-weight:600;border:none;background:none;border-bottom:2px solid #1d4ed8;margin-bottom:-2px;cursor:pointer;color:#1d4ed8;">
							CSS
						</button>
						<button class="ah-cc-tab-btn" data-tab="js"
							style="padding:8px 20px;font-size:13px;font-weight:600;border:none;background:none;border-bottom:2px solid transparent;margin-bottom:-2px;cursor:pointer;color:var(--ah-muted);">
							JavaScript
						</button>
					</div>

					<div id="ah-cc-panel-css" class="ah-cc-panel" style="padding-top:12px;">
						<p style="color:var(--ah-muted);font-size:12px;margin:0 0 6px;">Plain CSS rules - no <code>&lt;style&gt;</code> tags needed.</p>
						<textarea id="ah-cc-css" rows="24"
							style="width:100%;font-family:monospace;font-size:12.5px;line-height:1.6;resize:vertical;background:#1e1e2e;color:#cdd6f4;padding:14px;border-radius:6px;border:1px solid #313244;"
							placeholder="/* Example */
.some-class {
  word-break: break-word;
  font-size: 1rem;
}"
						><?php echo esc_textarea( $edit_row ? ( $edit_row->css ?? '' ) : '' ); ?></textarea>
					</div>

					<div id="ah-cc-panel-js" class="ah-cc-panel" style="padding-top:12px;display:none;">
						<p style="color:var(--ah-muted);font-size:12px;margin:0 0 6px;">Plain JavaScript - no <code>&lt;script&gt;</code> tags needed. Runs at footer (after DOM ready).</p>
						<textarea id="ah-cc-js" rows="24"
							style="width:100%;font-family:monospace;font-size:12.5px;line-height:1.6;resize:vertical;background:#1e1e2e;color:#a6e3a1;padding:14px;border-radius:6px;border:1px solid #313244;"
							placeholder="// Example
document.querySelectorAll('.dynamic-text').forEach(function(el) {
  el.style.wordBreak = 'break-word';
});"
						><?php echo esc_textarea( $edit_row ? ( $edit_row->js ?? '' ) : '' ); ?></textarea>
					</div>
				</div>

				<div style="margin-top:16px;display:flex;gap:12px;align-items:center;">
					<button id="ah-cc-save-btn" class="ah-btn ah-btn-primary">Save Rule</button>
					<?php if ( $edit_row ) : ?>
					<button id="ah-cc-delete-btn" class="ah-btn" style="color:#b91c1c;border-color:#fca5a5;"
						data-id="<?php echo (int) $edit_row->id; ?>">Delete</button>
					<?php endif; ?>
					<span id="ah-cc-msg" style="font-size:13px;"></span>
				</div>

			</div>

			<?php if ( $edit_row ) : ?>
			<div style="margin-top:12px;background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:12px 16px;font-size:13px;color:#15803d;">
				✓ Rule is <strong><?php echo empty( $edit_row->is_active ) ? 'paused' : 'active'; ?></strong>
				- code <?php echo empty( $edit_row->is_active ) ? 'will <em>not</em> inject' : 'injects'; ?> on
				<a href="<?php echo esc_url( home_url( '/' . $edit_row->slug . '/' ) ); ?>" target="_blank" style="color:#15803d;">
					/<?php echo esc_html( $edit_row->slug ); ?>/
				</a>
			</div>
			<?php endif; ?>
		</div>

	</div>
</div>

<style>
.ah-cc-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 9px 6px;
	border-radius: 4px;
	font-size: 13px;
	gap: 8px;
}
.ah-cc-item + .ah-cc-item { border-top: 1px solid #f0f0f0; }
.ah-cc-item.active { background: #eff6ff; }
.ah-cc-slug { flex: 1; font-family: monospace; font-size: 12px; font-weight: 600; min-width: 0; overflow: hidden; text-overflow: ellipsis; }
.ah-cc-badges { display: inline-flex; gap: 4px; margin-left: 6px; vertical-align: middle; }
.ah-cc-badge { font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 4px; }
.ah-cc-actions { display: flex; gap: 4px; flex-shrink: 0; }
</style>

<script>
jQuery(function ($) {
	var editing = <?php echo wp_json_encode( $edit_row ? (int) $edit_row->id : 0 ); ?>;
	var nonce   = <?php echo wp_json_encode( wp_create_nonce( 'ah_custom_code' ) ); ?>;

	/* ── Tabs ── */
	$('.ah-cc-tab-btn').on('click', function () {
		var tab = $(this).data('tab');
		$('.ah-cc-tab-btn').css({ 'border-bottom-color': 'transparent', 'color': '#6b7280' });
		$(this).css({ 'border-bottom-color': '#1d4ed8', 'color': '#1d4ed8' });
		$('.ah-cc-panel').hide();
		$('#ah-cc-panel-' + tab).show();
	});

	/* ── New rule ── */
	$('#ah-cc-new-btn').on('click', function () {
		editing = 0;
		$('#ah-cc-slug').val('').focus();
		$('#ah-cc-css').val('');
		$('#ah-cc-js').val('');
		$('#ah-cc-msg').text('');
		$('.ah-cc-item').removeClass('active');
	});

	/* ── Save ── */
	$('#ah-cc-save-btn').on('click', function () {
		var $btn = $(this);
		var slug = $.trim( $('#ah-cc-slug').val() ).toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/^-+|-+$/g, '');
		var css  = $('#ah-cc-css').val();
		var js   = $('#ah-cc-js').val();

		if ( ! slug ) { alert('Enter a page slug first.'); $('#ah-cc-slug').focus(); return; }
		if ( ! css.trim() && ! js.trim() ) { alert('Write some CSS or JS before saving.'); return; }

		$btn.prop('disabled', true).text('Saving…');
		$('#ah-cc-msg').text('');

		$.post(ajaxurl, {
			action   : 'ah_save_custom_code',
			nonce    : nonce,
			entry_id : editing,
			slug     : slug,
			css      : css,
			js       : js,
		}, function (res) {
			$btn.prop('disabled', false).text('Save Rule');
			if ( res.success ) {
				$('#ah-cc-msg').css('color', '#15803d').text('✓ ' + res.data.message);
				if ( res.data.redirect ) {
					setTimeout(function () { location.href = res.data.redirect; }, 600);
				}
			} else {
				$('#ah-cc-msg').css('color', '#b91c1c').text('✗ ' + (res.data ? res.data.message : 'Error.'));
			}
		}).fail(function () {
			$btn.prop('disabled', false).text('Save Rule');
			$('#ah-cc-msg').css('color', '#b91c1c').text('✗ Request failed.');
		});
	});

	/* ── Delete ── */
	$('#ah-cc-delete-btn').on('click', function () {
		if ( ! confirm('Delete this rule? The code will stop injecting immediately.') ) return;
		var $btn = $(this);
		$btn.prop('disabled', true).text('Deleting…');
		$.post(ajaxurl, { action: 'ah_delete_custom_code', nonce: nonce, entry_id: $btn.data('id') }, function (res) {
			if ( res.success ) { location.href = '<?php echo esc_js( admin_url( 'admin.php?page=ah-custom-code' ) ); ?>'; }
			else { $btn.prop('disabled', false).text('Delete'); alert(res.data ? res.data.message : 'Error.'); }
		});
	});

	/* ── Toggle active / paused ── */
	$(document).on('click', '.ah-cc-toggle', function () {
		var $btn = $(this);
		var id   = $btn.data('id');
		$.post(ajaxurl, { action: 'ah_toggle_custom_code', nonce: nonce, entry_id: id }, function (res) {
			if ( res.success ) {
				$btn.text( res.data.active ? '⏸' : '▶' ).attr('title', res.data.active ? 'Pause' : 'Enable');
			}
		});
	});
});
</script>

	</div><!-- /per-page grid -->
<?php endif; ?>
</div><!-- /wrap -->

<script>
jQuery(function ($) {
	var gsNonce = <?php echo wp_json_encode( wp_create_nonce( 'ah_custom_code' ) ); ?>;

	$('#ah-gs-save-btn').on('click', function () {
		var $btn = $(this);
		$btn.prop('disabled', true).text('Saving…');
		$('#ah-gs-msg').text('');
		$.post(ajaxurl, {
			action  : 'ah_save_global_styles',
			nonce   : gsNonce,
			css     : $('#ah-gs-css').val(),
			active  : $('#ah-gs-active').is(':checked') ? 1 : 0,
		}, function (res) {
			$btn.prop('disabled', false).text('Save Global Styles');
			if ( res.success ) {
				$('#ah-gs-msg').css('color','#15803d').text('✓ ' + res.data.message);
				var on = $('#ah-gs-active').is(':checked');
				$('#ah-gs-status-label')
					.css('color', on ? '#15803d' : '#b91c1c')
					.text( on ? '● Active - injecting on all pages' : '○ Disabled - not injecting' );
			} else {
				$('#ah-gs-msg').css('color','#b91c1c').text('✗ ' + (res.data ? res.data.message : 'Error.'));
			}
		}).fail(function () {
			$btn.prop('disabled', false).text('Save Global Styles');
			$('#ah-gs-msg').css('color','#b91c1c').text('✗ Request failed.');
		});
	});
});
</script>
