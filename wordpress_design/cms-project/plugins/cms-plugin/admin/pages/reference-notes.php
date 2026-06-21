<?php
/**
 * admin/pages/reference-notes.php — Reference Notes / Cheat Sheet.
 *
 * Folders → Notes (passage / HTML content).
 * Stored in wp_options 'adn_help_notes'.
 */
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Access denied.' ); }

$opt_key = 'adn_help_notes';

// ── Save ──────────────────────────────────────────────────────────────────────
if ( isset( $_POST['adn_help_nonce'] )
	&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['adn_help_nonce'] ) ), 'adn_help_save' )
	&& current_user_can( 'manage_options' )
) {
	$raw   = isset( $_POST['help_notes'] ) && is_array( $_POST['help_notes'] ) ? $_POST['help_notes'] : array();
	$saved = array();

	$allowed_tags = array(
		'p'          => array(),
		'br'         => array(),
		'strong'     => array(),
		'b'          => array(),
		'em'         => array(),
		'i'          => array(),
		'u'          => array(),
		'h1'         => array(),
		'h2'         => array(),
		'h3'         => array(),
		'h4'         => array(),
		'ul'         => array(),
		'ol'         => array(),
		'li'         => array(),
		'a'          => array( 'href' => true, 'target' => true ),
		'code'       => array(),
		'pre'        => array(),
		'blockquote' => array(),
		'hr'         => array(),
		'table'      => array(),
		'thead'      => array(),
		'tbody'      => array(),
		'tr'         => array(),
		'th'         => array(),
		'td'         => array(),
		'span'       => array( 'style' => true ),
		'div'        => array( 'style' => true ),
	);

	foreach ( $raw as $folder_raw ) {
		$folder_name = sanitize_text_field( wp_unslash( isset( $folder_raw['folder'] ) ? $folder_raw['folder'] : '' ) );
		if ( '' === trim( $folder_name ) ) {
			continue;
		}
		$notes = array();
		if ( ! empty( $folder_raw['notes'] ) && is_array( $folder_raw['notes'] ) ) {
			foreach ( $folder_raw['notes'] as $note_raw ) {
				$title   = sanitize_text_field( wp_unslash( isset( $note_raw['title'] ) ? $note_raw['title'] : '' ) );
				$content = wp_kses( wp_unslash( isset( $note_raw['content'] ) ? $note_raw['content'] : '' ), $allowed_tags );
				if ( '' === trim( $title ) && '' === trim( $content ) ) {
					continue;
				}
				$notes[] = array( 'title' => $title, 'content' => $content );
			}
		}
		$saved[] = array( 'folder' => $folder_name, 'notes' => $notes );
	}

	update_option( $opt_key, $saved, false );
	echo '<div class="notice notice-success is-dismissible"><p><strong>Notes saved.</strong></p></div>';
}

// ── Load ──────────────────────────────────────────────────────────────────────
$folders = get_option( $opt_key, array() );
if ( ! is_array( $folders ) || empty( $folders ) ) {
	$folders = array(
		array(
			'folder' => 'How-to Steps',
			'notes'  => array(
				array( 'title' => 'Example Note', 'content' => "<p>Write your steps or instructions here.</p>\n<ol>\n  <li>Step one</li>\n  <li>Step two</li>\n</ol>" ),
			),
		),
	);
}
?>
<div class="wrap ah-wrap">
<h1 style="margin-bottom:16px;">📋 Reference Notes</h1>
<p class="description" style="margin-bottom:20px;">Your personal cheat-sheet. Add folders and notes for steps, instructions, proper names — anything you need at hand.</p>

<style>
.hn-wrap{display:flex;gap:0;max-width:1200px;min-height:600px;border:1px solid #dcdcde;border-radius:4px;overflow:hidden;background:#fff;}
.hn-sidebar{width:220px;min-width:220px;background:#f6f7f7;border-right:1px solid #dcdcde;display:flex;flex-direction:column;}
.hn-sidebar-head{padding:12px 14px 8px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#787c82;border-bottom:1px solid #dcdcde;}
.hn-folder-list{flex:1;overflow-y:auto;padding:6px 0;}
.hn-folder-item{display:flex;align-items:center;}
.hn-folder-btn{flex:1;text-align:left;background:none;border:none;padding:8px 14px;cursor:pointer;font-size:13px;color:#2c3338;display:flex;align-items:center;gap:7px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.hn-folder-btn:hover{background:#e9e9e9;}
.hn-folder-btn.active{background:#fff;color:#2271b1;font-weight:600;border-right:3px solid #2271b1;}
.hn-folder-icon{opacity:.55;font-size:14px;flex-shrink:0;}
.hn-sidebar-add{padding:10px 14px;border-top:1px solid #dcdcde;}
.hn-btn-add-folder{width:100%;background:none;border:1px dashed #a7aaad;color:#50575e;border-radius:3px;padding:6px;cursor:pointer;font-size:12px;}
.hn-btn-add-folder:hover{border-color:#2271b1;color:#2271b1;}
.hn-main{flex:1;display:flex;flex-direction:column;min-width:0;}
.hn-main-head{padding:12px 18px;border-bottom:1px solid #dcdcde;display:flex;align-items:center;gap:10px;background:#fafafa;}
.hn-folder-name-input{font-size:15px;font-weight:600;border:none;background:transparent;color:#1d2327;flex:1;padding:2px 4px;border-radius:3px;}
.hn-folder-name-input:focus{outline:1px solid #2271b1;background:#fff;}
.hn-btn-del-folder{background:none;border:1px solid #c3c4c7;color:#d63638;border-radius:3px;padding:3px 10px;cursor:pointer;font-size:12px;}
.hn-btn-del-folder:hover{background:#d63638;color:#fff;border-color:#d63638;}
.hn-notes-body{flex:1;padding:16px 18px;overflow-y:auto;}
.hn-empty{color:#787c82;text-align:center;padding:48px 0;font-size:14px;}
.hn-note{background:#fff;border:1px solid #e2e4e7;border-radius:4px;margin-bottom:16px;overflow:hidden;}
.hn-note-head{display:flex;align-items:center;gap:8px;padding:10px 14px;background:#f6f7f7;border-bottom:1px solid #e2e4e7;}
.hn-note-title{flex:1;font-size:14px;font-weight:600;border:1px solid transparent;border-radius:3px;padding:4px 7px;background:transparent;}
.hn-note-title:focus{border-color:#2271b1;background:#fff;outline:none;}
.hn-note-actions{display:flex;gap:6px;}
.hn-btn-toggle{background:none;border:1px solid #c3c4c7;color:#2c3338;border-radius:3px;padding:3px 10px;cursor:pointer;font-size:11px;}
.hn-btn-toggle:hover,.hn-btn-toggle.active{background:#2271b1;color:#fff;border-color:#2271b1;}
.hn-btn-print{background:none;border:1px solid #c3c4c7;color:#2c3338;border-radius:3px;padding:3px 10px;cursor:pointer;font-size:11px;}
.hn-btn-print:hover{background:#f0f0f1;}
.hn-btn-del-note{background:none;border:none;color:#b2b9c4;cursor:pointer;font-size:18px;padding:0 4px;line-height:1;border-radius:3px;}
.hn-btn-del-note:hover{color:#d63638;background:#fce8e8;}
.hn-note-editor{padding:0;}
.hn-note-editor textarea{width:100%;box-sizing:border-box;border:none;border-top:1px solid #e2e4e7;resize:vertical;font-family:'SFMono-Regular',Consolas,monospace;font-size:13px;line-height:1.6;padding:12px 14px;min-height:160px;color:#2c3338;background:#fdfdfd;}
.hn-note-editor textarea:focus{outline:none;background:#fff;}
.hn-note-preview{display:none;padding:14px 18px;border-top:1px solid #e2e4e7;font-size:14px;line-height:1.7;color:#1d2327;}
.hn-note-preview h1,.hn-note-preview h2,.hn-note-preview h3{margin-top:.75em;margin-bottom:.4em;}
.hn-note-preview ol,.hn-note-preview ul{padding-left:20px;}
.hn-note-preview li{margin-bottom:4px;}
.hn-note-preview code{background:#f0f0f1;padding:1px 5px;border-radius:3px;font-family:monospace;font-size:12px;}
.hn-note-preview pre{background:#f0f0f1;padding:10px 14px;border-radius:4px;overflow-x:auto;}
.hn-note-preview blockquote{border-left:3px solid #2271b1;margin:0;padding:4px 12px;color:#50575e;}
.hn-note-preview table{border-collapse:collapse;width:100%;}
.hn-note-preview td,.hn-note-preview th{border:1px solid #dcdcde;padding:6px 10px;font-size:13px;}
.hn-note-preview th{background:#f6f7f7;font-weight:600;}
.hn-btn-add-note{margin-top:8px;background:none;border:1px dashed #a7aaad;color:#50575e;border-radius:3px;padding:7px 16px;cursor:pointer;font-size:13px;}
.hn-btn-add-note:hover{border-color:#2271b1;color:#2271b1;}
.hn-save-bar{padding:12px 18px;border-top:1px solid #dcdcde;background:#f6f7f7;display:flex;align-items:center;gap:12px;}
.hn-no-sel{display:flex;align-items:center;justify-content:center;flex:1;color:#787c82;font-size:14px;}
</style>

<form method="post" id="adn-help-form">
<?php wp_nonce_field( 'adn_help_save', 'adn_help_nonce' ); ?>

<div class="hn-wrap">

	<!-- ── Folder sidebar ── -->
	<div class="hn-sidebar">
		<div class="hn-sidebar-head">Folders</div>
		<div class="hn-folder-list" id="hn-folder-list">
			<?php foreach ( $folders as $fi => $folder ) : ?>
			<div class="hn-folder-item" data-fi="<?php echo $fi; ?>">
				<button type="button" class="hn-folder-btn<?php echo 0 === $fi ? ' active' : ''; ?>" data-fi="<?php echo $fi; ?>">
					<span class="hn-folder-icon">📁</span>
					<span class="hn-folder-label"><?php echo esc_html( $folder['folder'] ); ?></span>
				</button>
			</div>
			<?php endforeach; ?>
		</div>
		<div class="hn-sidebar-add">
			<button type="button" id="hn-btn-add-folder" class="hn-btn-add-folder">+ New folder</button>
		</div>
	</div>

	<!-- ── Main panel ── -->
	<div class="hn-main" id="hn-main">
		<?php foreach ( $folders as $fi => $folder ) :
			$notes = ! empty( $folder['notes'] ) && is_array( $folder['notes'] ) ? $folder['notes'] : array();
		?>
		<div class="hn-folder-panel" data-fi="<?php echo $fi; ?>" style="<?php echo 0 === $fi ? 'display:flex;flex-direction:column;flex:1;' : 'display:none;'; ?>">

			<div class="hn-main-head">
				<input
					type="text"
					class="hn-folder-name-input folder-name"
					placeholder="Folder name…"
					value="<?php echo esc_attr( $folder['folder'] ); ?>"
				>
				<button type="button" class="hn-btn-del-folder">Delete folder</button>
			</div>

			<div class="hn-notes-body">
				<?php if ( empty( $notes ) ) : ?>
				<div class="hn-empty">No notes yet. Click "+ Add note" below.</div>
				<?php endif; ?>

				<?php foreach ( $notes as $ni => $note ) : ?>
				<div class="hn-note">
					<div class="hn-note-head">
						<input
							type="text"
							class="hn-note-title note-title"
							placeholder="Note title…"
							value="<?php echo esc_attr( isset( $note['title'] ) ? $note['title'] : '' ); ?>"
						>
						<div class="hn-note-actions">
							<button type="button" class="hn-btn-toggle">Preview</button>
							<button type="button" class="hn-btn-print">Print</button>
							<button type="button" class="hn-btn-del-note">×</button>
						</div>
					</div>
					<div class="hn-note-editor">
						<textarea
							class="note-content"
							rows="10"
							placeholder="Write steps, instructions or any content. You can use HTML like &lt;ol&gt;&lt;li&gt;…"
						><?php echo esc_textarea( isset( $note['content'] ) ? $note['content'] : '' ); ?></textarea>
					</div>
					<div class="hn-note-preview"></div>
				</div>
				<?php endforeach; ?>

				<button type="button" class="hn-btn-add-note">+ Add note</button>
			</div>

		</div>
		<?php endforeach; ?>

		<?php if ( empty( $folders ) ) : ?>
		<div class="hn-no-sel">Create a folder to get started.</div>
		<?php endif; ?>

		<div class="hn-save-bar">
			<button type="submit" class="button button-primary">Save all notes</button>
			<span class="description">All folders and notes are saved together.</span>
		</div>
	</div>

</div><!-- .hn-wrap -->
</form>

<!-- Templates -->
<template id="hn-folder-item-tpl">
	<div class="hn-folder-item">
		<button type="button" class="hn-folder-btn active">
			<span class="hn-folder-icon">📁</span>
			<span class="hn-folder-label">New folder</span>
		</button>
	</div>
</template>

<template id="hn-folder-panel-tpl">
	<div class="hn-folder-panel" style="display:flex;flex-direction:column;flex:1;">
		<div class="hn-main-head">
			<input type="text" class="hn-folder-name-input folder-name" placeholder="Folder name…" value="New folder">
			<button type="button" class="hn-btn-del-folder">Delete folder</button>
		</div>
		<div class="hn-notes-body">
			<div class="hn-empty">No notes yet. Click "+ Add note" below.</div>
			<button type="button" class="hn-btn-add-note">+ Add note</button>
		</div>
	</div>
</template>

<template id="hn-note-tpl">
	<div class="hn-note">
		<div class="hn-note-head">
			<input type="text" class="hn-note-title note-title" placeholder="Note title…" value="">
			<div class="hn-note-actions">
				<button type="button" class="hn-btn-toggle">Preview</button>
				<button type="button" class="hn-btn-print">Print</button>
				<button type="button" class="hn-btn-del-note">×</button>
			</div>
		</div>
		<div class="hn-note-editor">
			<textarea class="note-content" rows="10" placeholder="Write steps, instructions or any content. You can use HTML like &lt;ol&gt;&lt;li&gt;…"></textarea>
		</div>
		<div class="hn-note-preview"></div>
	</div>
</template>

<script>
(function () {
	var form       = document.getElementById('adn-help-form');
	var folderList = document.getElementById('hn-folder-list');
	var mainPanel  = document.getElementById('hn-main');
	var saveBar    = mainPanel.querySelector('.hn-save-bar');

	function setActiveFolder(fi) {
		folderList.querySelectorAll('.hn-folder-btn').forEach(function (b) {
			b.classList.toggle('active', parseInt(b.dataset.fi) === fi);
		});
		mainPanel.querySelectorAll('.hn-folder-panel').forEach(function (p) {
			var show = parseInt(p.dataset.fi) === fi;
			p.style.display = show ? 'flex' : 'none';
			if (show) { p.style.flexDirection = 'column'; p.style.flex = '1'; }
		});
	}

	function updateFolderLabel(fi) {
		var panel = mainPanel.querySelector('.hn-folder-panel[data-fi="' + fi + '"]');
		var btn   = folderList.querySelector('.hn-folder-btn[data-fi="' + fi + '"] .hn-folder-label');
		if (!panel || !btn) return;
		btn.textContent = panel.querySelector('.folder-name').value || 'Untitled';
	}

	folderList.addEventListener('click', function (e) {
		var btn = e.target.closest('.hn-folder-btn');
		if (!btn) return;
		setActiveFolder(parseInt(btn.dataset.fi));
	});

	mainPanel.addEventListener('input', function (e) {
		if (e.target.classList.contains('folder-name')) {
			var panel = e.target.closest('.hn-folder-panel');
			if (panel) updateFolderLabel(parseInt(panel.dataset.fi));
		}
	});

	mainPanel.addEventListener('click', function (e) {
		if (!e.target.classList.contains('hn-btn-del-folder')) return;
		var panel = e.target.closest('.hn-folder-panel');
		var fi    = parseInt(panel.dataset.fi);
		if (mainPanel.querySelectorAll('.hn-folder-panel').length <= 1) {
			panel.querySelector('.folder-name').value = '';
			panel.querySelector('.hn-notes-body').innerHTML = '<div class="hn-empty">No notes yet. Click &quot;+ Add note&quot; below.</div><button type="button" class="hn-btn-add-note">+ Add note</button>';
			return;
		}
		var sideItem = folderList.querySelector('.hn-folder-item[data-fi="' + fi + '"]');
		if (sideItem) sideItem.remove();
		panel.remove();
		var first = folderList.querySelector('.hn-folder-btn');
		if (first) setActiveFolder(parseInt(first.dataset.fi));
	});

	document.getElementById('hn-btn-add-folder').addEventListener('click', function () {
		var all = mainPanel.querySelectorAll('.hn-folder-panel');
		var fi  = all.length;

		var itemTpl  = document.getElementById('hn-folder-item-tpl').content.cloneNode(true);
		var itemNode = itemTpl.querySelector('.hn-folder-item');
		itemNode.dataset.fi = fi;
		itemNode.querySelector('.hn-folder-btn').dataset.fi = fi;
		folderList.appendChild(itemTpl);

		var panelTpl  = document.getElementById('hn-folder-panel-tpl').content.cloneNode(true);
		panelTpl.querySelector('.hn-folder-panel').dataset.fi = fi;
		mainPanel.insertBefore(panelTpl, saveBar);

		setActiveFolder(fi);
		mainPanel.querySelector('.hn-folder-panel[data-fi="' + fi + '"] .folder-name').select();
	});

	mainPanel.addEventListener('click', function (e) {
		if (e.target.classList.contains('hn-btn-add-note')) {
			var body    = e.target.closest('.hn-notes-body');
			var noteTpl = document.getElementById('hn-note-tpl').content.cloneNode(true);
			var empty   = body.querySelector('.hn-empty');
			if (empty) empty.remove();
			body.insertBefore(noteTpl, e.target);
		}
		if (e.target.classList.contains('hn-btn-del-note')) {
			var note = e.target.closest('.hn-note');
			var body = note.closest('.hn-notes-body');
			note.remove();
			if (!body.querySelector('.hn-note')) {
				body.insertAdjacentHTML('afterbegin', '<div class="hn-empty">No notes yet. Click &quot;+ Add note&quot; below.</div>');
			}
		}
		if (e.target.classList.contains('hn-btn-toggle')) {
			var note    = e.target.closest('.hn-note');
			var editor  = note.querySelector('.hn-note-editor');
			var preview = note.querySelector('.hn-note-preview');
			var ta      = note.querySelector('.note-content');
			var on      = !e.target.classList.contains('active');
			e.target.classList.toggle('active', on);
			e.target.textContent  = on ? 'Edit' : 'Preview';
			editor.style.display  = on ? 'none' : '';
			preview.style.display = on ? 'block' : 'none';
			if (on) preview.innerHTML = ta.value;
		}
		if (e.target.classList.contains('hn-btn-print')) {
			var note    = e.target.closest('.hn-note');
			var title   = note.querySelector('.note-title').value || 'Note';
			var content = note.querySelector('.note-content').value;
			var win     = window.open('', '_blank');
			win.document.write(
				'<!DOCTYPE html><html><head><title>' + title + '</title>' +
				'<style>body{font-family:Georgia,serif;max-width:720px;margin:40px auto;font-size:15px;line-height:1.7;color:#1d2327;}' +
				'h1,h2,h3{margin-top:.9em;margin-bottom:.4em;}ol,ul{padding-left:22px;}li{margin-bottom:5px;}' +
				'code{background:#f0f0f1;padding:1px 5px;border-radius:3px;font-family:monospace;font-size:13px;}' +
				'pre{background:#f0f0f1;padding:12px 16px;border-radius:4px;overflow-x:auto;}' +
				'blockquote{border-left:3px solid #2271b1;margin:0;padding:4px 14px;color:#50575e;}' +
				'table{border-collapse:collapse;width:100%;}td,th{border:1px solid #dcdcde;padding:6px 10px;}th{background:#f6f7f7;font-weight:600;}' +
				'@media print{body{margin:20px;}}</style>' +
				'</head><body><h1>' + title + '</h1>' + content + '</body></html>'
			);
			win.document.close();
			win.focus();
			win.print();
		}
	});

	form.addEventListener('submit', function () {
		mainPanel.querySelectorAll('.hn-folder-panel').forEach(function (panel, fi) {
			panel.querySelector('.folder-name').name = 'help_notes[' + fi + '][folder]';
			panel.querySelectorAll('.hn-note').forEach(function (note, ni) {
				note.querySelector('.note-title').name   = 'help_notes[' + fi + '][notes][' + ni + '][title]';
				note.querySelector('.note-content').name = 'help_notes[' + fi + '][notes][' + ni + '][content]';
			});
		});
	});
}());
</script>

</div><!-- .wrap -->
