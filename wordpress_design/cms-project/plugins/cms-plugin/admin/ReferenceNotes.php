<?php
/**
 * admin/ReferenceNotes.php - Reference Notes / Cheat Sheet.
 * List + Edit pattern with reusable components.
 */
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use Ah\Cms\Admin\Components\AdminComponents;

$opt_key = 'adn_help_notes';
$action  = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id = isset( $_GET['edit'] ) ? (int) $_GET['edit'] : -1;
$notice  = '';
$n_type  = 'success';

$allowed_tags = array(
	'p' => array(), 'br' => array(), 'strong' => array(), 'b' => array(),
	'em' => array(), 'i' => array(), 'u' => array(),
	'h1' => array(), 'h2' => array(), 'h3' => array(), 'h4' => array(),
	'ul' => array(), 'ol' => array(), 'li' => array(),
	'a' => array( 'href' => true, 'target' => true ),
	'code' => array(), 'pre' => array(), 'blockquote' => array(), 'hr' => array(),
	'table' => array(), 'thead' => array(), 'tbody' => array(),
	'tr' => array(), 'th' => array(), 'td' => array(),
	'span' => array( 'style' => true ), 'div' => array( 'style' => true ),
);

// ── Load data ──
$folders = get_option( $opt_key, array() );
if ( ! is_array( $folders ) ) $folders = array();

// ── POST: save folder ──
if ( isset( $_POST['save_folder'] ) && wp_verify_nonce( $_POST['ah_rn_nonce'] ?? '', 'ah_save_rn' ) ) {
	$fi           = (int) ( $_POST['folder_index'] ?? -1 );
	$folder_name  = sanitize_text_field( $_POST['folder_name'] ?? '' );
	$raw_notes    = $_POST['notes'] ?? array();

	if ( '' === trim( $folder_name ) ) {
		$notice = 'Folder name is required.';
		$n_type = 'error';
	} else {
		$notes = array();
		if ( is_array( $raw_notes ) ) {
			foreach ( $raw_notes as $note_raw ) {
				$title   = sanitize_text_field( wp_unslash( $note_raw['title'] ?? '' ) );
				$content = wp_kses( wp_unslash( $note_raw['content'] ?? '' ), $allowed_tags );
				if ( '' === trim( $title ) && '' === trim( $content ) ) continue;
				$notes[] = array( 'title' => $title, 'content' => $content );
			}
		}
		$data = array( 'folder' => $folder_name, 'notes' => $notes );

		if ( $fi >= 0 && isset( $folders[ $fi ] ) ) {
			$folders[ $fi ] = $data;
		} else {
			$folders[] = $data;
		}
		update_option( $opt_key, $folders, false );
		$notice = 'Folder saved.';
		$action = 'list';
	}
}

// ── POST: create new folder ──
if ( isset( $_POST['create_folder'] ) && wp_verify_nonce( $_POST['ah_rn_nonce'] ?? '', 'ah_save_rn' ) ) {
	$folder_name = sanitize_text_field( $_POST['new_folder_name'] ?? '' );
	if ( '' === trim( $folder_name ) ) {
		$notice = 'Folder name is required.';
		$n_type = 'error';
	} else {
		$folders[] = array( 'folder' => $folder_name, 'notes' => array() );
		update_option( $opt_key, $folders, false );
		$notice = 'Folder created.';
		$action = 'list';
	}
}

// ── GET: delete folder ──
if ( isset( $_GET['delete_fi'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_del_rn' ) ) {
	$del_fi = (int) $_GET['delete_fi'];
	if ( isset( $folders[ $del_fi ] ) ) {
		$name = $folders[ $del_fi ]['folder'] ?? '';
		unset( $folders[ $del_fi ] );
		$folders = array_values( $folders );
		update_option( $opt_key, $folders, false );
		$notice = "Folder \"{$name}\" deleted.";
	}
	$action = 'list';
}

// ── GET: delete note ──
if ( isset( $_GET['delete_note'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_del_rn_note' ) ) {
	$del_fi = (int) $_GET['delete_fi'];
	$del_ni = (int) $_GET['delete_note'];
	if ( isset( $folders[ $del_fi ]['notes'][ $del_ni ] ) ) {
		unset( $folders[ $del_fi ]['notes'][ $del_ni ] );
		$folders[ $del_fi ]['notes'] = array_values( $folders[ $del_fi ]['notes'] );
		update_option( $opt_key, $folders, false );
		$notice = 'Note deleted.';
	}
	$action = 'edit';
	$edit_id = $del_fi;
}
?>
<div class="wrap ah-wrap">
	<?php AdminComponents::pageHeader( 'admin-page', 'Reference Notes', 'Your personal cheat-sheet. Add folders and notes for steps, instructions, or anything you need at hand.' ); ?>
	<?php if ( $notice ) : ?><?php AdminComponents::notice( $notice, $n_type ); ?><?php endif; ?>

	<?php if ( $action === 'edit' && isset( $folders[ $edit_id ] ) ) :
		$folder = $folders[ $edit_id ];
		$notes  = $folder['notes'] ?? array();
	?>
		<?php AdminComponents::backLink( add_query_arg( array( 'page' => 'ah-ref-notes' ), admin_url( 'admin.php' ) ) ); ?>
		<?php ob_start(); ?>
			<form method="post">
				<?php wp_nonce_field( 'ah_save_rn', 'ah_rn_nonce' ); ?>
				<input type="hidden" name="folder_index" value="<?php echo esc_attr( $edit_id ); ?>">

				<div style="display:flex;justify-content:flex-end;gap:8px;margin-bottom:16px;">
					<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-ref-notes' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary">Cancel</a>
					<button type="submit" name="save_folder" value="1" class="ah-btn ah-btn-primary">Save Folder</button>
				</div>

				<div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;">
					<!-- Left: Notes -->
					<div>
						<?php AdminComponents::formRow( 'Folder Name', '<input type="text" name="folder_name" value="' . esc_attr( $folder['folder'] ?? '' ) . '" placeholder="e.g. How-to Steps" required>' ); ?>

						<div class="ah-card" style="padding:16px;">
							<div class="ah-card-header">
								<h2>Notes (<?php echo count( $notes ); ?>)</h2>
							</div>

							<style>
							.rn-toolbar{display:flex;flex-wrap:wrap;gap:2px;padding:6px 10px;background:#f1f5f9;border-bottom:1px solid var(--ah-border);}
							.rn-toolbar button{background:none;border:1px solid transparent;border-radius:4px;padding:4px 8px;cursor:pointer;font-size:13px;color:var(--ah-text);transition:all .12s;line-height:1;}
							.rn-toolbar button:hover{background:#e2e8f0;border-color:var(--ah-border);}
							.rn-toolbar .rn-sep{width:1px;background:var(--ah-border);margin:2px 4px;flex-shrink:0;}
							.rn-note-item{border:1px solid var(--ah-border);border-radius:var(--ah-radius);margin-bottom:8px;overflow:hidden;transition:box-shadow .15s;}
							.rn-note-item:hover{box-shadow:0 2px 8px rgba(0,0,0,.06);}
							.rn-note-header{display:flex;align-items:center;gap:8px;padding:10px 14px;background:var(--ah-bg-light);cursor:pointer;user-select:none;transition:background .15s;}
							.rn-note-header:hover{background:#e9ecef;}
							.rn-note-header .rn-toggle{font-size:12px;color:var(--ah-muted);transition:transform .2s;flex-shrink:0;width:16px;text-align:center;}
							.rn-note-item.open .rn-toggle{transform:rotate(90deg);}
							.rn-note-header .rn-note-title-static{flex:1;font-weight:600;font-size:13px;color:var(--ah-text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
							.rn-note-header .rn-note-badge{font-size:11px;color:var(--ah-muted);flex-shrink:0;}
							.rn-note-body{display:none;padding:0;}
							.rn-note-item.open .rn-note-body{display:block;}
							.rn-note-item.open .rn-note-header{background:#fff;border-bottom:1px solid var(--ah-border);}
							</style>

							<div id="rn-notes-list">
								<?php if ( empty( $notes ) ) : ?>
								<p style="color:var(--ah-muted);font-size:13px;text-align:center;padding:24px 0;">No notes yet. Click "+ Add Note" below.</p>
								<?php endif; ?>

								<?php foreach ( $notes as $ni => $note ) :
									$content_preview = wp_strip_all_tags( $note['content'] ?? '' );
									$content_preview = mb_strimwidth( $content_preview, 0, 80, '...' );
								?>
								<div class="rn-note-item">
									<div class="rn-note-header" onclick="var p=this.closest('.rn-note-item');p.classList.toggle('open');">
										<span class="rn-toggle">&#9654;</span>
										<span class="rn-note-title-static"><?php echo esc_html( $note['title'] ?? 'Untitled Note' ); ?></span>
										<?php if ( $content_preview ) : ?>
										<span class="rn-note-badge"><?php echo esc_html( $content_preview ); ?></span>
										<?php endif; ?>
									</div>
									<div class="rn-note-body">
										<input type="hidden" name="notes[<?php echo $ni; ?>][title]" value="<?php echo esc_attr( $note['title'] ?? '' ); ?>">
										<div class="rn-toolbar" data-editor="notes[<?php echo $ni; ?>][content]">
											<button type="button" title="Bold" onclick="event.stopPropagation();rnInsert('notes[<?php echo $ni; ?>][content]','<strong>','</strong>')"><strong>B</strong></button>
											<button type="button" title="Italic" onclick="event.stopPropagation();rnInsert('notes[<?php echo $ni; ?>][content]','<em>','</em>')"><em>I</em></button>
											<button type="button" title="Underline" onclick="event.stopPropagation();rnInsert('notes[<?php echo $ni; ?>][content]','<u>','</u>')"><u>U</u></button>
											<button type="button" title="Strikethrough" onclick="event.stopPropagation();rnInsert('notes[<?php echo $ni; ?>][content]','<s>','</s>')"><s>S</s></button>
											<div class="rn-sep"></div>
											<button type="button" title="Heading 2" onclick="event.stopPropagation();rnInsert('notes[<?php echo $ni; ?>][content]','<h2>','</h2>')">H2</button>
											<button type="button" title="Heading 3" onclick="event.stopPropagation();rnInsert('notes[<?php echo $ni; ?>][content]','<h3>','</h3>')">H3</button>
											<div class="rn-sep"></div>
											<button type="button" title="Bullet List" onclick="event.stopPropagation();rnInsertList('notes[<?php echo $ni; ?>][content]','ul')">&#8226; List</button>
											<button type="button" title="Numbered List" onclick="event.stopPropagation();rnInsertList('notes[<?php echo $ni; ?>][content]','ol')">1. List</button>
											<div class="rn-sep"></div>
											<button type="button" title="Link" onclick="event.stopPropagation();rnInsertLink('notes[<?php echo $ni; ?>][content]')">&#128279;</button>
											<button type="button" title="Code" onclick="event.stopPropagation();rnInsert('notes[<?php echo $ni; ?>][content]','<code>','</code>')">&lt;/&gt;</button>
											<button type="button" title="Code Block" onclick="event.stopPropagation();rnInsert('notes[<?php echo $ni; ?>][content]','<pre>','</pre>')">{ }</button>
											<button type="button" title="Blockquote" onclick="event.stopPropagation();rnInsert('notes[<?php echo $ni; ?>][content]','<blockquote>','</blockquote>')">&#8220;</button>
											<div class="rn-sep"></div>
											<button type="button" title="Horizontal Rule" onclick="event.stopPropagation();rnInsert('notes[<?php echo $ni; ?>][content]','<hr>','')">---</button>
											<button type="button" title="Table" onclick="event.stopPropagation();rnInsertTable('notes[<?php echo $ni; ?>][content]')">&#9638;</button>
											<div style="flex:1"></div>
											<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-ref-notes', 'action' => 'edit', 'edit' => $edit_id, 'delete_fi' => $edit_id, 'delete_note' => $ni ), admin_url( 'admin.php' ) ), 'ah_del_rn_note' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm ah-confirm-delete" data-title="Delete Note" data-confirm="Delete this note?" onclick="event.stopPropagation();">&#10005; Delete</a>
										</div>
										<textarea name="notes[<?php echo $ni; ?>][content]" rows="10" placeholder="Write steps, instructions or content. Use the toolbar above for formatting." style="width:100%;border:none;padding:12px 14px;font-family:monospace;font-size:13px;line-height:1.6;resize:vertical;box-sizing:border-box;background:#fdfdfd;"><?php echo esc_textarea( $note['content'] ?? '' ); ?></textarea>
									</div>
								</div>
								<?php endforeach; ?>
							</div>

							<button type="button" id="rn-add-note" class="ah-btn ah-btn-secondary" style="width:100%;justify-content:center;margin-top:8px;">+ Add Note</button>
						</div>
					</div>

					<!-- Right: Info -->
					<div>
						<div class="ah-card" style="padding:14px;font-size:12px;color:var(--ah-muted);line-height:1.7;">
							<strong style="color:var(--ah-text);">How it works</strong><br>
							1. Name your folder (e.g. "Deployment Steps").<br>
							2. Add notes with titles and content.<br>
							3. HTML is allowed in note content.<br>
							4. Save when done.<br><br>
							<strong style="color:var(--ah-text);">Allowed HTML</strong><br>
							Paragraphs, lists, headings, links, code, tables, blockquotes, and inline styles.
						</div>
						<div class="ah-card" style="padding:14px;margin-top:12px;font-size:12px;color:var(--ah-muted);line-height:1.7;">
							<strong style="color:var(--ah-text);">Stats</strong><br>
							Folder: <strong><?php echo esc_html( $folder['folder'] ?? '' ); ?></strong><br>
							Notes: <strong><?php echo count( $notes ); ?></strong><br>
							Total folders: <strong><?php echo count( $folders ); ?></strong>
						</div>
					</div>
				</div>
			</form>
		<?php AdminComponents::card( 'Edit: ' . esc_html( $folder['folder'] ?? '' ), ob_get_clean() ); ?>

	<?php else : ?>
		<!-- List page -->
		<?php AdminComponents::filterBar( array(
			'page_slug'          => 'ah-ref-notes',
			'search_placeholder' => 'Search folders...',
			'search_value'       => sanitize_text_field( $_GET['s'] ?? '' ),
			'add_url'            => '#',
			'add_label'          => '',
			'extra_fields'       => '<button type="button" class="ah-btn ah-btn-primary" id="rn-new-folder-btn">+ New Folder</button>',
		) ); ?>

		<!-- New folder dialog -->
		<div id="rn-new-dialog" style="display:none;background:#fff;border:1px solid var(--ah-border);border-radius:var(--ah-radius);padding:20px 24px;margin-bottom:20px;max-width:480px;box-shadow:0 4px 20px rgba(0,0,0,.1);">
			<h3 style="margin:0 0 14px;">Create New Folder</h3>
			<form method="post">
				<?php wp_nonce_field( 'ah_save_rn', 'ah_rn_nonce' ); ?>
				<?php AdminComponents::formRow( 'Folder Name', '<input type="text" name="new_folder_name" placeholder="e.g. Deployment Steps" autofocus required>' ); ?>
				<div style="display:flex;gap:10px;">
					<button type="submit" name="create_folder" value="1" class="ah-btn ah-btn-primary">Create Folder</button>
					<button type="button" class="ah-btn ah-btn-secondary" id="rn-cancel-new">Cancel</button>
				</div>
			</form>
		</div>

		<?php
		$search = sanitize_text_field( $_GET['s'] ?? '' );
		$filtered = $folders;
		if ( $search ) {
			$filtered = array_values( array_filter( $filtered, function ( $f ) use ( $search ) {
				return stripos( $f['folder'] ?? '', $search ) !== false;
			} ) );
		}

		$rows = array();
		foreach ( $filtered as $fi => $f ) {
			$notes = $f['notes'] ?? array();
			$real_fi = array_search( $f, $folders, true );
			$row = new \stdClass();
			$row->id         = $real_fi;
			$row->folder     = $f['folder'] ?? '';
			$row->note_count = count( $notes );
			$row->first_note = $notes[0]['title'] ?? '';
			$row->edit_url   = add_query_arg( array( 'page' => 'ah-ref-notes', 'action' => 'edit', 'edit' => $real_fi ), admin_url( 'admin.php' ) );
			$row->delete_url = wp_nonce_url( add_query_arg( array( 'page' => 'ah-ref-notes', 'delete_fi' => $real_fi ), admin_url( 'admin.php' ) ), 'ah_del_rn' );
			$rows[] = $row;
		}
		AdminComponents::dataTable( array(
			'columns' => array(
				array( 'label' => 'Folder', 'render' => function ( $r ) {
					return '<a href="' . esc_url( $r->edit_url ) . '" style="font-weight:600;text-decoration:none;color:var(--ah-text);">&#128193; ' . esc_html( $r->folder ) . '</a>';
				} ),
				array( 'label' => 'Notes', 'render' => function ( $r ) {
					return '<span class="ah-badge ah-badge-new">' . esc_html( $r->note_count ) . '</span>';
				} ),
				array( 'label' => 'First Note', 'render' => function ( $r ) {
					if ( ! $r->first_note ) return '<span style="color:var(--ah-muted);font-size:12px;">-</span>';
					return '<span style="font-size:13px;">' . esc_html( mb_strimwidth( $r->first_note, 0, 50, '...' ) ) . '</span>';
				} ),
			),
			'items'         => $rows,
			'empty_message' => $search ? 'No folders match your search.' : 'No folders yet. Click "+ New Folder" to create one.',
			'actions'       => function ( $r ) {
				$html = '<a href="' . esc_url( $r->edit_url ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>';
				$html .= ' <a href="' . esc_url( $r->delete_url ) . '" class="ah-btn ah-btn-danger ah-btn-sm ah-confirm-delete" data-title="Delete Folder" data-confirm="Delete folder \'' . esc_attr( $r->folder ) . '\' and all its notes?">Delete</a>';
				return $html;
			},
		) ); ?>
	<?php endif; ?>
</div>

<script>
function rnInsert(name, open, close) {
	var ta = document.querySelector('textarea[name="' + name + '"]');
	if (!ta) return;
	var s = ta.selectionStart, e = ta.selectionEnd;
	var sel = ta.value.substring(s, e) || 'text';
	ta.value = ta.value.substring(0, s) + open + sel + close + ta.value.substring(e);
	ta.selectionStart = s + open.length;
	ta.selectionEnd   = s + open.length + sel.length;
	ta.focus();
}

function rnInsertList(name, type) {
	var ta = document.querySelector('textarea[name="' + name + '"]');
	if (!ta) return;
	var s = ta.selectionStart, e = ta.selectionEnd;
	var sel = ta.value.substring(s, e);
	var lines = sel ? sel.split('\n') : ['Item 1', 'Item 2'];
	var html = '<' + type + '>\n';
	lines.forEach(function(l){ html += '  <li>' + l.trim() + '</li>\n'; });
	html += '</' + type + '>';
	ta.value = ta.value.substring(0, s) + html + ta.value.substring(e);
	ta.focus();
}

function rnInsertLink(name) {
	var url = prompt('Enter URL:', 'https://');
	if (!url) return;
	var ta = document.querySelector('textarea[name="' + name + '"]');
	if (!ta) return;
	var s = ta.selectionStart, e = ta.selectionEnd;
	var sel = ta.value.substring(s, e) || 'link text';
	ta.value = ta.value.substring(0, s) + '<a href="' + url + '" target="_blank">' + sel + '</a>' + ta.value.substring(e);
	ta.focus();
}

function rnInsertTable(name) {
	var ta = document.querySelector('textarea[name="' + name + '"]');
	if (!ta) return;
	var s = ta.selectionStart;
	var html = '<table>\n  <thead>\n    <tr>\n      <th>Column 1</th>\n      <th>Column 2</th>\n      <th>Column 3</th>\n    </tr>\n  </thead>\n  <tbody>\n    <tr>\n      <td>Data</td>\n      <td>Data</td>\n      <td>Data</td>\n    </tr>\n    <tr>\n      <td>Data</td>\n      <td>Data</td>\n      <td>Data</td>\n    </tr>\n  </tbody>\n</table>';
	ta.value = ta.value.substring(0, s) + html + ta.value.substring(s);
	ta.focus();
}

jQuery(function ($) {
	$('#rn-new-folder-btn').on('click', function () { $('#rn-new-dialog').slideToggle(180); });
	$('#rn-cancel-new').on('click', function () { $('#rn-new-dialog').slideUp(180); });

	$('#rn-add-note').on('click', function () {
		var list = $('#rn-notes-list');
		var emptyMsg = list.find('p');
		if (emptyMsg.length) emptyMsg.remove();
		var idx  = list.find('.rn-note-item').length;
		var name = 'notes[' + idx + '][content]';
		var titleName = 'notes[' + idx + '][title]';
		var html = '<div class="rn-note-item open">'
			+ '<div class="rn-note-header">'
			+ '<span class="rn-toggle">&#9654;</span>'
			+ '<span class="rn-note-title-static">New Note</span>'
			+ '</div>'
			+ '<div class="rn-note-body">'
			+ '<input type="hidden" name="' + titleName + '" value="">'
			+ '<div class="rn-toolbar" data-editor="' + name + '">'
			+ '<button type="button" title="Bold" onclick="event.stopPropagation();rnInsert(\'' + name + '\',\'<strong>\',\'</strong>\')"><strong>B</strong></button>'
			+ '<button type="button" title="Italic" onclick="event.stopPropagation();rnInsert(\'' + name + '\',\'<em>\',\'</em>\')"><em>I</em></button>'
			+ '<button type="button" title="Underline" onclick="event.stopPropagation();rnInsert(\'' + name + '\',\'<u>\',\'</u>\')"><u>U</u></button>'
			+ '<button type="button" title="Strikethrough" onclick="event.stopPropagation();rnInsert(\'' + name + '\',\'<s>\',\'</s>\')"><s>S</s></button>'
			+ '<div class="rn-sep"></div>'
			+ '<button type="button" title="Heading 2" onclick="event.stopPropagation();rnInsert(\'' + name + '\',\'<h2>\',\'</h2>\')">H2</button>'
			+ '<button type="button" title="Heading 3" onclick="event.stopPropagation();rnInsert(\'' + name + '\',\'<h3>\',\'</h3>\')">H3</button>'
			+ '<div class="rn-sep"></div>'
			+ '<button type="button" title="Bullet List" onclick="event.stopPropagation();rnInsertList(\'' + name + '\',\'ul\')">&#8226; List</button>'
			+ '<button type="button" title="Numbered List" onclick="event.stopPropagation();rnInsertList(\'' + name + '\',\'ol\')">1. List</button>'
			+ '<div class="rn-sep"></div>'
			+ '<button type="button" title="Link" onclick="event.stopPropagation();rnInsertLink(\'' + name + '\')">&#128279;</button>'
			+ '<button type="button" title="Code" onclick="event.stopPropagation();rnInsert(\'' + name + '\',\'<code>\',\'</code>\')">&lt;/&gt;</button>'
			+ '<button type="button" title="Code Block" onclick="event.stopPropagation();rnInsert(\'' + name + '\',\'<pre>\',\'</pre>\')">{ }</button>'
			+ '<button type="button" title="Blockquote" onclick="event.stopPropagation();rnInsert(\'' + name + '\',\'<blockquote>\',\'</blockquote>\')">&#8220;</button>'
			+ '<div class="rn-sep"></div>'
			+ '<button type="button" title="Horizontal Rule" onclick="event.stopPropagation();rnInsert(\'' + name + '\',\'<hr>\',\'\')">---</button>'
			+ '<button type="button" title="Table" onclick="event.stopPropagation();rnInsertTable(\'' + name + '\')">&#9638;</button>'
			+ '<div style="flex:1"></div>'
			+ '<button type="button" class="ah-btn ah-btn-danger ah-btn-sm rn-del-note">&#10005; Delete</button>'
			+ '</div>'
			+ '<textarea name="' + name + '" rows="10" placeholder="Write content here. Use the toolbar above for formatting." style="width:100%;border:none;padding:12px 14px;font-family:monospace;font-size:13px;line-height:1.6;resize:vertical;box-sizing:border-box;background:#fdfdfd;"></textarea>'
			+ '</div>'
			+ '</div>';
		list.append(html);
		list.find('.rn-note-item:last textarea').focus();
	});

	$(document).on('click', '.rn-del-note', function (e) {
		e.stopPropagation();
		var item = $(this).closest('.rn-note-item');
		item.fadeOut(150, function () { $(this).remove(); });
	});

	// Update header title when typing in the hidden title input
	$(document).on('input', '.rn-note-body input[type="hidden"]', function () {
		var title = $(this).val() || 'Untitled Note';
		$(this).closest('.rn-note-item').find('.rn-note-title-static').text(title);
	});
});
</script>
