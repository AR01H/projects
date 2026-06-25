<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$static_model  = new AH_Static_Pages_Model();
$static_pages  = $static_model->all();   // DB rows (HTML stored in the database)
$content_tax_m = new AH_Content_Taxonomy_Model();

$edit_slug    = isset( $_GET['edit'] ) ? sanitize_file_name( wp_unslash( $_GET['edit'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$edit_content = '';
$edit_title   = '';
$edit_page_id = 0;
if ( $edit_slug ) {
	$edit_row = $static_model->get_by_slug( $edit_slug );
	if ( $edit_row ) {
		$edit_content = (string) $edit_row->html;
		$edit_title   = (string) ( $edit_row->title ?? '' );
		$edit_page    = get_page_by_path( $edit_slug );
		$edit_page_id = $edit_page ? (int) $edit_page->ID : (int) ( $edit_row->page_id ?? 0 );
	} else {
		$edit_slug = ''; // Not in the DB - treat as new with that slug pre-filled
	}
}
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-editor-code"></span> Static HTML Pages</h1>
	<p style="color:var(--ah-muted);margin-top:4px;">Upload raw HTML pages. Each page displays inside your site layout with full style isolation - theme CSS won't interfere with the HTML you write.</p>

	<div style="display:grid;grid-template-columns:260px 1fr;gap:20px;margin-top:24px;align-items:start;">

		<!-- ── Sidebar ───────────────────────────────────────────────── -->
		<div>
			<div class="ah-card" style="padding:16px;">
				<h3 style="margin:0 0 12px;font-size:13px;text-transform:uppercase;letter-spacing:.05em;color:var(--ah-muted);">Pages</h3>

				<div id="ah-page-list">
					<?php foreach ( $static_pages as $sp ) :
						$s    = $sp->slug;
						$page = get_page_by_path( $s );
						$url  = $page ? get_permalink( $page->ID ) : null;
					?>
					<div class="ah-sl-item<?php echo ( $edit_slug === $s ) ? ' active' : ''; ?>">
						<span class="ah-sl-name">
							<?php echo esc_html( $s ); ?>
							<?php if ( $page ) : ?>
								<span style="display:block;margin-top:4px;"><?php $content_tax_m->render_badges( 'static_page', (int) $page->ID ); ?></span>
							<?php endif; ?>
							<span class="ah-sl-sc" data-shortcode="[ah_static_page slug=&quot;<?php echo esc_attr( $s ); ?>&quot;]"
							      title="Click to copy shortcode">[ah_static_page slug=&quot;<?php echo esc_attr( $s ); ?>&quot;]</span>
						</span>
						<div class="ah-sl-actions">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-static-pages&edit=' . rawurlencode( $s ) ) ); ?>" class="ah-sl-btn">Edit</a>
							<?php if ( $url ) : ?>
							<a href="<?php echo esc_url( $url ); ?>" target="_blank" class="ah-sl-btn">View</a>
							<?php endif; ?>
						</div>
					</div>
					<?php endforeach; ?>
					<?php if ( empty( $static_pages ) ) : ?>
					<p style="color:var(--ah-muted);font-size:13px;margin:0 0 8px;">No pages yet. Create your first one →</p>
					<?php endif; ?>
				</div>

				<hr style="margin:12px 0 10px;">
				<button id="ah-new-btn" class="ah-btn ah-btn-primary" style="width:100%;">+ New Page</button>
			</div>

			<div class="ah-card" style="padding:14px;margin-top:12px;font-size:12px;color:var(--ah-muted);line-height:1.6;">
				<strong style="color:var(--ah-text);">How it works</strong><br>
				1. Create a page (or pick an existing one).<br>
				2. Paste raw HTML in the editor and save.<br>
				3. A WordPress page is created automatically at <code>/your-slug/</code>.<br>
				4. Add it to your nav menu via <em>WP Admin → Appearance → Menus</em>.<br><br>
				<strong style="color:var(--ah-text);">Style isolation</strong><br>
				Content renders inside a same-origin <code>&lt;iframe&gt;</code> - your HTML is completely isolated from the theme's CSS.
			</div>
		</div>

		<!-- ── Editor ────────────────────────────────────────────────── -->
		<div>
			<div class="ah-card" style="padding:20px;">

				<div id="ah-slug-row" style="margin-bottom:16px;">
					<label style="font-weight:600;font-size:13px;">Page Slug</label>
					<input
						type="text"
						id="ah-slug-input"
						value="<?php echo esc_attr( isset( $_GET['edit'] ) ? wp_unslash( $_GET['edit'] ) : '' ); // phpcs:ignore ?>"
						placeholder="e.g. privacy-policy"
						class="regular-text"
						style="display:block;margin-top:6px;"
					>
					<p style="color:var(--ah-muted);font-size:12px;margin:4px 0 0;">Lowercase letters, numbers, hyphens only. Creates <code>/slug/</code> in WordPress.</p>
				</div>

				<?php if ( $edit_slug ) : ?>
				<div style="margin-bottom:12px;display:flex;align-items:center;justify-content:space-between;">
					<span style="font-size:13px;color:var(--ah-muted);">Editing: <strong style="color:var(--ah-text);"><?php echo esc_html( $edit_slug ); ?>.html</strong></span>
					<div style="display:flex;gap:8px;align-items:center;">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-static-pages&edit=' . rawurlencode( $edit_slug ) . '&raw=1' ) ); ?>" target="_blank" class="ah-btn" style="font-size:12px;">Raw / Print ↗</a>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-static-pages&edit=' . rawurlencode( $edit_slug ) . '&themed=1' ) ); ?>" target="_blank" class="ah-btn" style="font-size:12px;background:#eff6ff;border-color:#93c5fd;color:#1d4ed8;">Match Theme ↗</a>
						<?php
						$page = get_page_by_path( $edit_slug );
						if ( $page ) :
						?>
						<a href="<?php echo esc_url( get_permalink( $page->ID ) ); ?>" target="_blank" class="ah-btn" style="font-size:12px;">View Page ↗</a>
						<?php endif; ?>
					</div>
				</div>
				<div style="background:#f0f4ff;border:1px solid #c5d0e6;border-radius:6px;padding:10px 14px;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between;gap:12px;">
					<div>
						<span style="font-size:11px;font-weight:600;color:#3b5bdb;text-transform:uppercase;letter-spacing:.04em;">Shortcode</span>
						<code id="ah-editor-sc" style="display:block;margin-top:3px;font-size:12px;color:#1e3a5f;">[ah_static_page slug="<?php echo esc_attr( $edit_slug ); ?>"]</code>
					</div>
					<button id="ah-copy-sc-btn" type="button" class="ah-btn" style="font-size:12px;flex-shrink:0;">Copy</button>
				</div>
				<?php endif; ?>

				<label style="font-weight:600;font-size:13px;display:block;margin-bottom:4px;">Page Title</label>
				<input
					type="text"
					id="ah-title-input"
					value="<?php echo esc_attr( $edit_title ); ?>"
					placeholder="e.g. Privacy Policy"
					class="regular-text"
					style="width:100%;margin-bottom:16px;"
				>

				<label style="font-weight:600;font-size:13px;">HTML Content</label>
				<textarea
					id="ah-html-editor"
					rows="32"
					style="width:100%;font-family:monospace;font-size:12.5px;line-height:1.5;margin-top:8px;resize:vertical;"
					placeholder="Paste your full HTML here - include &lt;!DOCTYPE html&gt;, &lt;head&gt;, &lt;style&gt;, &lt;body&gt;, etc."
				><?php echo esc_textarea( $edit_content ); ?></textarea>

				<div class="ah-card" style="padding:16px;margin-top:12px;">
					<div class="ah-card-header" style="margin-bottom:12px;"><h2>Taxonomy Terms</h2></div>
					<?php $content_tax_m->render_picker( 'static_page', $edit_page_id ); ?>
				</div>

				<div style="margin-top:12px;display:flex;gap:12px;align-items:center;">
					<button id="ah-save-btn" class="ah-btn ah-btn-primary">Save Page</button>
					<span id="ah-save-msg" style="font-size:13px;"></span>
				</div>

			</div>
		</div>

	</div>
</div>

<style>
.ah-sl-sc {
	display: inline-block;
	margin-top: 5px;
	font-size: 10.5px;
	font-family: monospace;
	background: #f0f4ff;
	color: #3b5bdb;
	border: 1px solid #c5d0e6;
	border-radius: 3px;
	padding: 2px 6px;
	cursor: pointer;
	user-select: all;
	max-width: 180px;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
	vertical-align: middle;
	transition: background .15s;
}
.ah-sl-sc:hover { background: #dbe4ff; }
.ah-sl-sc.copied { background: #d1fae5; color: #065f46; border-color: #6ee7b7; }
.ah-sl-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 8px 6px;
	border-radius: 4px;
	font-size: 13px;
	gap: 8px;
}
.ah-sl-item + .ah-sl-item { border-top: 1px solid #f0f0f0; }
.ah-sl-item.active { background: #eff6ff; }
.ah-sl-name { overflow: hidden; text-overflow: ellipsis; flex: 1; min-width: 0; }
.ah-sl-actions { display: flex; gap: 4px; flex-shrink: 0; }
.ah-sl-btn {
	font-size: 11px;
	padding: 2px 8px;
	border: 1px solid #d0d5dd;
	border-radius: 4px;
	background: #fff;
	color: var(--ah-text);
	text-decoration: none;
	cursor: pointer;
}
.ah-sl-btn:hover { background: #f9fafb; }
</style>

<script>
jQuery(function ($) {
	var editing = <?php echo wp_json_encode( $edit_slug ); ?>;

	// Default content for new pages
	var defaultHtml = [
		'<!DOCTYPE html>',
		'<html lang="en">',
		'<head>',
		'<meta charset="UTF-8">',
		'<meta name="viewport" content="width=device-width, initial-scale=1.0">',
		'<style>',
		'  body { margin: 0; padding: 24px; font-family: sans-serif; color: #1a1a1a; }',
		'</style>',
		'</head>',
		'<body>',
		'',
		'<h1>Page Title</h1>',
		'<p>Your content here.</p>',
		'',
		'</body>',
		'</html>',
	].join('\n');

	$('#ah-new-btn').on('click', function () {
		editing = '';
		$('#ah-slug-row').show();
		$('#ah-slug-input').val('').focus();
		$('#ah-title-input').val('');
		$('#ah-html-editor').val(defaultHtml);
		$('.ah-taxonomy-picker input[name="taxonomy_ids[]"]').prop('checked', false);
		$('#ah-save-msg').text('');
		$('#ah-save-btn').text('Create Page');
	});

	// Editor shortcode copy button
	$('#ah-copy-sc-btn').on('click', function () {
		var sc = $('#ah-editor-sc').text();
		if ( navigator.clipboard ) {
			navigator.clipboard.writeText( sc ).then(function () {
				$('#ah-copy-sc-btn').text('✓ Copied!');
				setTimeout(function(){ $('#ah-copy-sc-btn').text('Copy'); }, 1800);
			});
		}
	});

	// Shortcode copy-to-clipboard
	$(document).on('click', '.ah-sl-sc', function () {
		var $el = $(this);
		var sc  = $el.data('shortcode');
		if ( navigator.clipboard ) {
			navigator.clipboard.writeText( sc ).then(function () {
				$el.addClass('copied').text('✓ Copied!');
				setTimeout(function(){ $el.removeClass('copied').text(sc); }, 1800);
			});
		} else {
			var ta = document.createElement('textarea');
			ta.value = sc; document.body.appendChild(ta); ta.select();
			document.execCommand('copy');
			document.body.removeChild(ta);
			$el.addClass('copied').text('✓ Copied!');
			setTimeout(function(){ $el.removeClass('copied').text(sc); }, 1800);
		}
	});

	$('#ah-save-btn').on('click', function () {
		var $btn    = $(this);
		var oldSlug = editing;
		var slug    = $('#ah-slug-input').val().trim().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
		var html    = $('#ah-html-editor').val();
		var taxonomyIds = $('.ah-taxonomy-picker input[name="taxonomy_ids[]"]:checked').map(function () {
			return this.value;
		}).get();

		if (!slug) { alert('Enter a page slug first.'); $('#ah-slug-input').focus(); return; }
		if (!html.trim()) { alert('HTML content is empty.'); return; }

		$btn.prop('disabled', true).text('Saving…');
		$('#ah-save-msg').css('color', '#6b7280').text('');

		$.post(ajaxurl, {
			action   : 'ah_save_static_page',
			nonce    : ahAdmin.nonce,
			old_slug : oldSlug,
			slug     : slug,
			title    : $('#ah-title-input').val().trim(),
			html     : html,
			taxonomy_ids: taxonomyIds,
		}, function (res) {
			$btn.prop('disabled', false).text(editing ? 'Save Page' : 'Create Page');
			if (res.success) {
				$('#ah-save-msg').css('color', '#15803d').text('✓ ' + res.data.message);
				if (res.data.redirect) {
					setTimeout(function () {
						location.href = res.data.redirect;
					}, 700);
				}
			} else {
				$('#ah-save-msg').css('color', '#b91c1c').text('✗ ' + (res.data ? res.data.message : 'Request error.'));
			}
		}).fail(function () {
			$btn.prop('disabled', false).text(editing ? 'Save Page' : 'Create Page');
			$('#ah-save-msg').css('color', '#b91c1c').text('✗ Request failed.');
		});
	});
});
</script>
