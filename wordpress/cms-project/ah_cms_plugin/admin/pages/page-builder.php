<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

global $wpdb;
$table   = $wpdb->prefix . 'ah_builder_pages';
$notice  = '';
$action  = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id = (int) ( $_GET['id'] ?? 0 );

// ── POST handlers ─────────────────────────────────────────────────────────────
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ah_builder_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_builder_nonce'], 'ah_builder_save' ) ) wp_die( 'Security check failed.' );

	if ( isset( $_POST['delete_page'] ) && $edit_id ) {
		$wpdb->delete( $table, array( 'id' => $edit_id ) );
		$notice = 'Page deleted.'; $action = 'list'; $edit_id = 0;

	} else {
		$title  = sanitize_text_field( $_POST['page_title'] ?? 'Untitled Page' );
		$slug   = sanitize_title( $_POST['page_slug'] ?: $title );
		$status = in_array( $_POST['page_status'] ?? 'draft', array( 'active', 'draft' ), true ) ? $_POST['page_status'] : 'draft';
		$meta_t = sanitize_text_field( $_POST['meta_title'] ?? '' );
		$meta_d = sanitize_textarea_field( $_POST['meta_desc'] ?? '' );

		// Sanitize blocks JSON
		$raw_blocks = wp_unslash( $_POST['blocks_json'] ?? '[]' );
		$decoded    = json_decode( $raw_blocks, true );
		$blocks_json = is_array( $decoded ) ? wp_json_encode( $decoded ) : '[]';

		$data = array(
			'title'            => $title,
			'slug'             => $slug,
			'blocks'           => $blocks_json,
			'status'           => $status,
			'meta_title'       => $meta_t,
			'meta_description' => $meta_d,
		);

		if ( $edit_id ) {
			$wpdb->update( $table, $data, array( 'id' => $edit_id ) );
			$notice = 'Page saved.';
		} else {
			$wpdb->insert( $table, $data );
			$edit_id = $wpdb->insert_id;
			$notice  = 'Page created.';
			$action  = 'builder';
		}
	}
}

// ── DATA ─────────────────────────────────────────────────────────────────────
$current_page  = $edit_id ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE id = %d", $edit_id ) ) : null;
$existing_blocks = $current_page ? ( $current_page->blocks ?: '[]' ) : '[]';
?>
<div class="wrap ah-wrap">

<?php if ( $notice ) : ?>
  <div class="ah-notice ah-notice-success"><?php echo esc_html( $notice ); ?></div>
<?php endif; ?>

<?php /* ══════════════ LIST VIEW ══════════════ */ ?>
<?php if ( $action === 'list' ) :
  $pages = $wpdb->get_results( "SELECT * FROM `{$table}` ORDER BY updated_at DESC" );
?>
  <div class="ah-table-top" style="margin-bottom:0">
    <h1 style="margin:0"><span class="dashicons dashicons-layout"></span> Page Builder</h1>
    <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-page-builder', 'action' => 'builder' ), admin_url( 'admin.php' ) ) ); ?>"
       class="ah-btn ah-btn-primary">+ New Page</a>
  </div>
  <p style="color:var(--ah-text-muted);margin:6px 0 20px">Build custom pages with drag-and-drop blocks — hero banners, card grids, CTAs, FAQs and more.</p>

  <?php if ( empty( $pages ) ) : ?>
    <div class="ah-card" style="text-align:center;padding:48px">
      <div style="font-size:3rem;margin-bottom:12px">🧱</div>
      <h3>No pages yet</h3>
      <p style="color:var(--ah-text-muted)">Click "+ New Page" to create your first drag-and-drop page.</p>
    </div>
  <?php else : ?>
    <div class="ah-table-wrap">
      <table class="ah-table">
        <thead>
          <tr><th>Title</th><th>Slug</th><th>Blocks</th><th>Status</th><th>Updated</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ( $pages as $pg ) :
            $b_count = is_string( $pg->blocks ) ? count( json_decode( $pg->blocks, true ) ?: array() ) : 0;
          ?>
            <tr>
              <td><strong><?php echo esc_html( $pg->title ); ?></strong></td>
              <td><code>/<?php echo esc_html( $pg->slug ); ?>/</code></td>
              <td><?php echo esc_html( $b_count ); ?> block<?php echo $b_count !== 1 ? 's' : ''; ?></td>
              <td><span class="ah-badge ah-badge-<?php echo esc_attr( $pg->status ); ?>"><?php echo esc_html( $pg->status ); ?></span></td>
              <td style="color:var(--ah-text-muted);font-size:.82rem"><?php echo esc_html( date_i18n( 'j M Y', strtotime( $pg->updated_at ) ) ); ?></td>
              <td class="row-actions">
                <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-page-builder', 'action' => 'builder', 'id' => $pg->id ), admin_url( 'admin.php' ) ) ); ?>"
                   class="ah-btn ah-btn-secondary ah-btn-sm">✏️ Edit</a>
                <a href="<?php echo esc_url( home_url( '/' . $pg->slug . '/' ) ); ?>"
                   target="_blank" class="ah-btn ah-btn-secondary ah-btn-sm">👁 Preview</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

<?php /* ══════════════ BUILDER VIEW ══════════════ */ ?>
<?php else : ?>

<style>
/* ── Page Builder Styles ────────────────────────── */
.ah-builder-wrap { display: grid; grid-template-columns: 260px 1fr 240px; gap: 0; height: calc(100vh - 120px); overflow: hidden; margin: 0 -20px; }
.ah-builder-topbar { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 20px; background:var(--ah-card-bg,#fff); border-bottom:1px solid var(--ah-border); margin:0 -20px 0; position:sticky; top:32px; z-index:100; }
.ah-builder-topbar input[type=text] { border:1px solid var(--ah-border); border-radius:6px; padding:6px 12px; font-size:.9rem; max-width:260px; }
.ah-palette { background:#1e2330; color:#c9d1e0; overflow-y:auto; padding:16px 12px; border-right:1px solid rgba(255,255,255,.07); }
.ah-palette h4 { font-size:.65rem; text-transform:uppercase; letter-spacing:.1em; color:#6b7280; margin:16px 0 8px; padding:0 4px; }
.ah-palette h4:first-child { margin-top:0; }
.ah-palette-block { display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:8px; cursor:pointer; transition:background .15s; font-size:.82rem; font-weight:500; color:#c9d1e0; }
.ah-palette-block:hover { background:rgba(255,255,255,.08); }
.ah-palette-block .icon { font-size:1.1rem; width:24px; text-align:center; }
.ah-canvas-wrap { overflow-y:auto; background:#f0f2f5; padding:24px 20px; }
.ah-canvas { min-height:400px; }
.ah-canvas-empty { text-align:center; padding:60px 20px; color:#9ca3af; border:2px dashed #d1d5db; border-radius:12px; background:#fff; }
.ah-canvas-empty .icon { font-size:3rem; margin-bottom:12px; }
.ah-canvas-block { background:#fff; border-radius:10px; border:1.5px solid #e5e7eb; margin-bottom:12px; overflow:hidden; transition:box-shadow .15s; }
.ah-canvas-block:hover { box-shadow:0 4px 20px rgba(0,0,0,.09); }
.ah-canvas-block.ah-block-active { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.12); }
.ah-block-header { display:flex; align-items:center; gap:8px; padding:11px 16px; background:#f9fafb; border-bottom:1px solid #f0f0f0; cursor:pointer; }
.ah-block-handle { cursor:grab; color:#9ca3af; padding:4px; font-size:1rem; }
.ah-block-handle:active { cursor:grabbing; }
.ah-block-type-badge { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; background:#eef2ff; color:#4f46e5; padding:2px 8px; border-radius:20px; }
.ah-block-title { flex:1; font-size:.85rem; font-weight:600; color:#374151; }
.ah-block-actions { display:flex; align-items:center; gap:4px; }
.ah-block-actions button { background:none; border:none; cursor:pointer; padding:4px 6px; color:#9ca3af; border-radius:4px; font-size:.85rem; transition:all .15s; }
.ah-block-actions button:hover { background:#f3f4f6; color:#374151; }
.ah-block-actions .ah-delete-block:hover { color:#ef4444; background:#fef2f2; }
.ah-block-body { padding:16px; display:none; }
.ah-canvas-block.ah-block-active .ah-block-body { display:block; }
.ah-block-body .ah-form-row { margin-bottom:12px; }
.ah-block-body label { font-size:.78rem; font-weight:600; color:#6b7280; display:block; margin-bottom:4px; }
.ah-block-body input, .ah-block-body textarea, .ah-block-body select { width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:7px 10px; font-size:.85rem; }
.ah-block-body textarea { resize:vertical; }
.ah-block-preview { padding:14px 16px; font-size:.82rem; color:#6b7280; border-top:1px dashed #e5e7eb; background:#fafafa; }
.ah-repeater { border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; margin-top:8px; }
.ah-repeater-row { display:grid; gap:8px; padding:10px 12px; border-bottom:1px solid #f0f0f0; position:relative; }
.ah-repeater-row:last-child { border-bottom:none; }
.ah-repeater-row .ah-remove-row { position:absolute; top:8px; right:8px; background:none; border:none; cursor:pointer; color:#ef4444; font-size:.85rem; }
.ah-add-row { display:flex; align-items:center; gap:6px; padding:8px 12px; color:#3b82f6; font-size:.82rem; font-weight:600; cursor:pointer; background:none; border:none; border-top:1px solid #f0f0f0; width:100%; }
.ah-add-row:hover { background:#f0f7ff; }
.ah-settings-panel { background:#fff; border-left:1px solid #e5e7eb; padding:16px; overflow-y:auto; }
.ah-settings-panel h4 { font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#6b7280; margin:0 0 14px; }
.ah-settings-panel .ah-form-row { margin-bottom:12px; }
.ah-settings-panel label { font-size:.78rem; font-weight:600; color:#6b7280; display:block; margin-bottom:4px; }
.ah-settings-panel input, .ah-settings-panel select, .ah-settings-panel textarea { width:100%; border:1px solid #e5e7eb; border-radius:6px; padding:7px 10px; font-size:.82rem; }
.ah-builder-topbar .ah-btn { padding:8px 18px; font-size:.82rem; }
.ui-sortable-helper { box-shadow:0 8px 32px rgba(0,0,0,.15); }
.ui-sortable-placeholder { background:#f0f7ff; border:2px dashed #93c5fd; border-radius:10px; margin-bottom:12px; }
</style>

<form id="ah-builder-form" method="post">
  <?php wp_nonce_field( 'ah_builder_save', 'ah_builder_nonce' ); ?>
  <input type="hidden" name="blocks_json" id="blocks-json" value="">

  <!-- Top Bar -->
  <div class="ah-builder-topbar">
    <div style="display:flex;align-items:center;gap:12px">
      <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-page-builder' ) ); ?>"
         style="color:var(--ah-text-muted);text-decoration:none;font-size:1.2rem" title="Back to pages">←</a>
      <input type="text" name="page_title" id="page-title"
             value="<?php echo esc_attr( $current_page->title ?? '' ); ?>"
             placeholder="Page Title…" required style="font-weight:600;">
    </div>
    <div style="display:flex;align-items:center;gap:10px">
      <?php if ( $current_page ) : ?>
        <a href="<?php echo esc_url( home_url( '/' . esc_attr( $current_page->slug ) . '/' ) ); ?>"
           target="_blank" class="ah-btn ah-btn-secondary" style="padding:7px 14px;font-size:.8rem">👁 Preview</a>
      <?php endif; ?>
      <select name="page_status" style="border:1px solid var(--ah-border);border-radius:6px;padding:7px 10px;font-size:.82rem">
        <option value="draft" <?php selected( $current_page->status ?? 'draft', 'draft' ); ?>>Draft</option>
        <option value="active" <?php selected( $current_page->status ?? '', 'active' ); ?>>Published</option>
      </select>
      <button type="submit" class="ah-btn ah-btn-primary">💾 Save Page</button>
    </div>
  </div>

  <!-- Builder Grid -->
  <div class="ah-builder-wrap">

    <!-- LEFT: Block Palette -->
    <div class="ah-palette">
      <h4>Layout</h4>
      <div class="ah-palette-block" data-type="hero">         <span class="icon">🎯</span> Hero Banner</div>
      <div class="ah-palette-block" data-type="section_heading"><span class="icon">📌</span> Section Heading</div>
      <div class="ah-palette-block" data-type="text_block">  <span class="icon">📝</span> Rich Text</div>
      <div class="ah-palette-block" data-type="spacer">       <span class="icon">↕️</span> Spacer</div>

      <h4>Content</h4>
      <div class="ah-palette-block" data-type="cards">        <span class="icon">🃏</span> Card Grid</div>
      <div class="ah-palette-block" data-type="cta_banner">   <span class="icon">📣</span> CTA Banner</div>
      <div class="ah-palette-block" data-type="stats_row">    <span class="icon">📊</span> Stats Row</div>
      <div class="ah-palette-block" data-type="faq">          <span class="icon">❓</span> FAQ Accordion</div>

      <h4>Navigation</h4>
      <div class="ah-palette-block" data-type="button_row">   <span class="icon">🔘</span> Button Row</div>
      <div class="ah-palette-block" data-type="links_list">   <span class="icon">🔗</span> Links List</div>
      <div class="ah-palette-block" data-type="image_text">   <span class="icon">🖼️</span> Image + Text</div>
    </div>

    <!-- MIDDLE: Canvas -->
    <div class="ah-canvas-wrap">
      <div class="ah-canvas" id="ah-canvas">
        <div class="ah-canvas-empty" id="ah-canvas-empty">
          <div class="icon">🧱</div>
          <p>Click a block in the left panel to add it here.</p>
          <p style="font-size:.8rem">Drag blocks to reorder them.</p>
        </div>
      </div>
    </div>

    <!-- RIGHT: Page Settings -->
    <div class="ah-settings-panel">
      <h4>Page Settings</h4>

      <div class="ah-form-row">
        <label>URL Slug</label>
        <input type="text" name="page_slug" id="page-slug"
               value="<?php echo esc_attr( $current_page->slug ?? '' ); ?>"
               placeholder="my-custom-page">
        <?php if ( $current_page ) : ?>
          <small style="color:var(--ah-text-muted);font-size:.75rem;display:block;margin-top:4px">
            <?php echo esc_html( home_url( '/' . $current_page->slug . '/' ) ); ?>
          </small>
        <?php endif; ?>
      </div>

      <div class="ah-form-row">
        <label>Meta Title</label>
        <input type="text" name="meta_title"
               value="<?php echo esc_attr( $current_page->meta_title ?? '' ); ?>"
               placeholder="SEO title">
      </div>

      <div class="ah-form-row">
        <label>Meta Description</label>
        <textarea name="meta_desc" rows="3"
                  placeholder="SEO description"><?php echo esc_textarea( $current_page->meta_description ?? '' ); ?></textarea>
      </div>

      <?php if ( $current_page ) : ?>
        <hr style="border:none;border-top:1px solid #e5e7eb;margin:16px 0">
        <h4>Danger Zone</h4>
        <form method="post" onsubmit="return confirm('Delete this page permanently?')">
          <?php wp_nonce_field( 'ah_builder_save', 'ah_builder_nonce' ); ?>
          <input type="hidden" name="page_title" value="<?php echo esc_attr( $current_page->title ); ?>">
          <button type="submit" name="delete_page" value="1"
                  style="width:100%;background:#fef2f2;color:#ef4444;border:1px solid #fecaca;border-radius:6px;padding:8px;cursor:pointer;font-size:.82rem;font-weight:600">
            🗑 Delete Page
          </button>
        </form>
      <?php endif; ?>
    </div>

  </div><!-- /builder-wrap -->
</form>

<script>
(function($){
'use strict';

// ── Block definitions ────────────────────────────────────────────────────────
var BLOCK_DEFS = {
  hero: {
    label: 'Hero Banner', icon: '🎯', color: '#4f46e5',
    fields: [
      { key:'heading',     label:'Heading',         type:'text',     ph:'Welcome to our service'     },
      { key:'subheading',  label:'Subheading',      type:'textarea', ph:'A brief description…'       },
      { key:'cta1_text',   label:'Button 1 Text',   type:'text',     ph:'Book Free Call'             },
      { key:'cta1_url',    label:'Button 1 URL',    type:'text',     ph:'/free-consultation/'        },
      { key:'cta2_text',   label:'Button 2 Text',   type:'text',     ph:'Learn More'                 },
      { key:'cta2_url',    label:'Button 2 URL',    type:'text',     ph:'/about/'                    },
      { key:'bg',          label:'Background',      type:'select',   options:['white','light','dark','gold'], def:'white' },
    ]
  },
  section_heading: {
    label: 'Section Heading', icon: '📌', color: '#0891b2',
    fields: [
      { key:'title',    label:'Title',    type:'text',   ph:'Section Title'   },
      { key:'subtitle', label:'Subtitle', type:'text',   ph:'Optional subtitle' },
      { key:'align',    label:'Align',    type:'select', options:['center','left','right'], def:'center' },
    ]
  },
  text_block: {
    label: 'Rich Text', icon: '📝', color: '#059669',
    fields: [
      { key:'content', label:'Content (HTML allowed)', type:'textarea', ph:'<p>Your content here…</p>' },
    ]
  },
  spacer: {
    label: 'Spacer', icon: '↕️', color: '#9ca3af',
    fields: [
      { key:'height', label:'Height (px)', type:'text', ph:'40' },
    ]
  },
  cards: {
    label: 'Card Grid', icon: '🃏', color: '#7c3aed',
    fields: [
      { key:'heading', label:'Section Heading', type:'text', ph:'Our Features' },
      { key:'cols',    label:'Columns',         type:'select', options:['2','3','4'], def:'3' },
    ],
    repeater: {
      key: 'cards', label: 'Cards', addLabel: '+ Add Card',
      fields: [
        { key:'icon',     label:'Icon/Emoji', type:'text',     ph:'🏠' },
        { key:'title',    label:'Title',      type:'text',     ph:'Card Title' },
        { key:'text',     label:'Text',       type:'textarea', ph:'Description…' },
        { key:'link_url', label:'Link URL',   type:'text',     ph:'/guides/…' },
      ]
    }
  },
  cta_banner: {
    label: 'CTA Banner', icon: '📣', color: '#b45309',
    fields: [
      { key:'heading',   label:'Heading',      type:'text',     ph:'Ready to get started?' },
      { key:'text',      label:'Subtext',      type:'textarea', ph:'Supporting message…'   },
      { key:'btn1_text', label:'Button 1 Text',type:'text',     ph:'Book Free Call'        },
      { key:'btn1_url',  label:'Button 1 URL', type:'text',     ph:'/free-consultation/'   },
      { key:'btn2_text', label:'Button 2 Text',type:'text',     ph:'Learn More'            },
      { key:'btn2_url',  label:'Button 2 URL', type:'text',     ph:'/about/'               },
      { key:'theme',     label:'Theme',        type:'select',   options:['gold','dark','light','blue'], def:'gold' },
    ]
  },
  stats_row: {
    label: 'Stats Row', icon: '📊', color: '#0369a1',
    fields: [],
    repeater: {
      key: 'stats', label: 'Stats', addLabel: '+ Add Stat',
      fields: [
        { key:'prefix', label:'Prefix', type:'text', ph:'£' },
        { key:'number', label:'Number', type:'text', ph:'18' },
        { key:'suffix', label:'Suffix', type:'text', ph:'k+' },
        { key:'label',  label:'Label',  type:'text', ph:'Average Saving' },
      ]
    }
  },
  faq: {
    label: 'FAQ Accordion', icon: '❓', color: '#7c3aed',
    fields: [
      { key:'heading', label:'Section Heading', type:'text', ph:'Common Questions' },
    ],
    repeater: {
      key: 'items', label: 'Questions', addLabel: '+ Add Question',
      fields: [
        { key:'q', label:'Question', type:'text',     ph:'What is your process?' },
        { key:'a', label:'Answer',   type:'textarea', ph:'We start with…'        },
      ]
    }
  },
  button_row: {
    label: 'Button Row', icon: '🔘', color: '#be185d',
    fields: [
      { key:'align', label:'Alignment', type:'select', options:['center','left','right'], def:'center' },
    ],
    repeater: {
      key: 'buttons', label: 'Buttons', addLabel: '+ Add Button',
      fields: [
        { key:'text',  label:'Button Text', type:'text',   ph:'Click Here' },
        { key:'url',   label:'URL',         type:'text',   ph:'/page/'     },
        { key:'style', label:'Style',       type:'select', options:['primary','secondary','outline','gold'] },
      ]
    }
  },
  links_list: {
    label: 'Links List', icon: '🔗', color: '#0891b2',
    fields: [
      { key:'heading', label:'Heading',   type:'text',   ph:'Useful Links' },
      { key:'cols',    label:'Columns',   type:'select', options:['1','2','3'], def:'2' },
    ],
    repeater: {
      key: 'links', label: 'Links', addLabel: '+ Add Link',
      fields: [
        { key:'label',  label:'Label',       type:'text', ph:'First-Time Buyers Guide' },
        { key:'url',    label:'URL',         type:'text', ph:'/guides/first-time-buyers/' },
        { key:'icon',   label:'Icon/Emoji',  type:'text', ph:'🏠' },
        { key:'desc',   label:'Description', type:'text', ph:'Short description' },
      ]
    }
  },
  image_text: {
    label: 'Image + Text', icon: '🖼️', color: '#059669',
    fields: [
      { key:'image_url',  label:'Image URL',   type:'text',     ph:'/wp-content/uploads/…'       },
      { key:'image_alt',  label:'Image Alt',   type:'text',     ph:'Descriptive alt text'        },
      { key:'heading',    label:'Heading',     type:'text',     ph:'Section Heading'             },
      { key:'text',       label:'Body Text',   type:'textarea', ph:'Description…'                },
      { key:'btn_text',   label:'Button Text', type:'text',     ph:'Learn More'                  },
      { key:'btn_url',    label:'Button URL',  type:'text',     ph:'/page/'                      },
      { key:'layout',     label:'Layout',      type:'select',   options:['image-left','image-right'], def:'image-left' },
    ]
  },
};

// ── State ────────────────────────────────────────────────────────────────────
var blocks = <?php echo wp_json_encode( json_decode( $existing_blocks, true ) ?: array() ); ?>;
var blockIdCounter = 0;

// Assign IDs to loaded blocks
blocks.forEach(function(b){ b._id = ++blockIdCounter; });

// ── Render ───────────────────────────────────────────────────────────────────
function renderCanvas() {
  var $canvas  = $('#ah-canvas');
  var $empty   = $('#ah-canvas-empty');
  var $blocks  = $canvas.find('.ah-canvas-block');

  // Remove existing blocks but keep empty state div
  $blocks.remove();

  if ( blocks.length === 0 ) {
    $empty.show();
  } else {
    $empty.hide();
    blocks.forEach(function(block){ $canvas.append(buildBlockHTML(block)); });
    makeSortable();
  }
}

function buildBlockHTML(block) {
  var def = BLOCK_DEFS[block.type];
  if (!def) return '';
  var id = block._id;
  var data = block.data || {};

  var html = '<div class="ah-canvas-block" data-block-id="'+id+'" data-type="'+block.type+'">';
  html += '<div class="ah-block-header">';
  html += '<span class="ah-block-handle" title="Drag to reorder">☰</span>';
  html += '<span style="font-size:.9rem;margin-right:4px">'+def.icon+'</span>';
  html += '<span class="ah-block-title">'+(data.heading||data.title||data.content||def.label).substring(0,40)+'</span>';
  html += '<span class="ah-block-type-badge" style="background:'+hexToLight(def.color)+';color:'+def.color+'">'+def.label+'</span>';
  html += '<div class="ah-block-actions">';
  html += '<button type="button" class="ah-toggle-block" title="Edit block">▼</button>';
  html += '<button type="button" class="ah-delete-block" title="Delete block">✕</button>';
  html += '</div>';
  html += '</div>';

  html += '<div class="ah-block-body">';

  // Regular fields
  def.fields.forEach(function(f){
    var val = data[f.key] !== undefined ? data[f.key] : (f.def||'');
    html += '<div class="ah-form-row"><label>'+esc(f.label)+'</label>';
    if (f.type === 'textarea') {
      html += '<textarea data-block-id="'+id+'" data-field="'+f.key+'" placeholder="'+esc(f.ph||'')+'" rows="3">'+esc(val)+'</textarea>';
    } else if (f.type === 'select') {
      html += '<select data-block-id="'+id+'" data-field="'+f.key+'">';
      (f.options||[]).forEach(function(o){ html += '<option value="'+o+'"'+(val===o?' selected':'')+'>'+o+'</option>'; });
      html += '</select>';
    } else {
      html += '<input type="text" data-block-id="'+id+'" data-field="'+f.key+'" value="'+esc(val)+'" placeholder="'+esc(f.ph||'')+'">';
    }
    html += '</div>';
  });

  // Repeater
  if (def.repeater) {
    var rep = def.repeater;
    var items = data[rep.key] || [];
    html += '<div class="ah-form-row"><label>'+esc(rep.label)+'</label>';
    html += '<div class="ah-repeater" data-block-id="'+id+'" data-repeater="'+rep.key+'">';
    if (items.length === 0) items = [{}]; // always show at least one row
    items.forEach(function(item, ri){
      html += buildRepeaterRow(rep.fields, item, id, rep.key, ri);
    });
    html += '<button type="button" class="ah-add-row" data-block-id="'+id+'" data-repeater="'+rep.key+'">+ '+esc(rep.addLabel)+'</button>';
    html += '</div></div>';
  }

  html += '</div>'; // /block-body
  html += '</div>'; // /canvas-block
  return html;
}

function buildRepeaterRow(fields, data, blockId, repKey, ri) {
  var html = '<div class="ah-repeater-row" style="grid-template-columns: repeat('+Math.min(fields.length,2)+',1fr)">';
  fields.forEach(function(f){
    var val = data[f.key]||'';
    html += '<div><label style="font-size:.72rem;color:#9ca3af">'+esc(f.label)+'</label>';
    if (f.type==='textarea') {
      html += '<textarea data-block-id="'+blockId+'" data-repeater="'+repKey+'" data-rep-index="'+ri+'" data-field="'+f.key+'" rows="2">'+esc(val)+'</textarea>';
    } else if (f.type==='select') {
      html += '<select data-block-id="'+blockId+'" data-repeater="'+repKey+'" data-rep-index="'+ri+'" data-field="'+f.key+'">';
      (f.options||[]).forEach(function(o){ html += '<option value="'+o+'"'+(val===o?' selected':'')+'>'+o+'</option>'; });
      html += '</select>';
    } else {
      html += '<input type="text" data-block-id="'+blockId+'" data-repeater="'+repKey+'" data-rep-index="'+ri+'" data-field="'+f.key+'" value="'+esc(val)+'" placeholder="'+esc(f.placeholder||f.ph||'')+'">';
    }
    html += '</div>';
  });
  html += '<button type="button" class="ah-remove-row">✕</button>';
  html += '</div>';
  return html;
}

// ── Sortable ─────────────────────────────────────────────────────────────────
function makeSortable() {
  $('#ah-canvas').sortable({
    handle:      '.ah-block-handle',
    placeholder: 'ui-sortable-placeholder',
    axis:        'y',
    tolerance:   'pointer',
    stop: function() {
      var newOrder = [];
      $('#ah-canvas .ah-canvas-block').each(function(){
        var bid = parseInt($(this).data('block-id'));
        var found = blocks.find(function(b){ return b._id === bid; });
        if (found) newOrder.push(found);
      });
      blocks = newOrder;
    }
  });
}

// ── Block state helpers ───────────────────────────────────────────────────────
function getBlock(bid) {
  return blocks.find(function(b){ return b._id === bid; });
}

function syncField($el) {
  var bid  = parseInt($el.data('block-id'));
  var b    = getBlock(bid);
  if (!b) return;
  if (!b.data) b.data = {};
  var repKey = $el.data('repeater');
  if (repKey) {
    var ri = parseInt($el.data('rep-index'));
    if (!b.data[repKey]) b.data[repKey] = [];
    if (!b.data[repKey][ri]) b.data[repKey][ri] = {};
    b.data[repKey][ri][$el.data('field')] = $el.val();
  } else {
    b.data[$el.data('field')] = $el.val();
  }
  // Update block title in header
  var def = BLOCK_DEFS[b.type];
  if (def) {
    var displayVal = (b.data.heading||b.data.title||b.data.content||def.label).substring(0,40);
    $('#ah-canvas .ah-canvas-block[data-block-id="'+bid+'"] .ah-block-title').text(displayVal);
  }
}

// ── Event Handlers ────────────────────────────────────────────────────────────
// Add block from palette
$('.ah-palette-block').on('click', function(){
  var type = $(this).data('type');
  var def  = BLOCK_DEFS[type];
  if (!def) return;
  var block = { _id: ++blockIdCounter, type: type, data: {} };
  // Set defaults
  def.fields.forEach(function(f){ if(f.def) block.data[f.key] = f.def; });
  blocks.push(block);
  renderCanvas();
  // Auto-expand the new block
  var $new = $('#ah-canvas .ah-canvas-block:last');
  $new.addClass('ah-block-active');
  $new[0].scrollIntoView({behavior:'smooth', block:'center'});
});

// Toggle block expand/collapse
$(document).on('click', '.ah-block-header', function(e){
  if ($(e.target).is('.ah-block-handle, .ah-delete-block')) return;
  $(this).closest('.ah-canvas-block').toggleClass('ah-block-active');
});

// Delete block
$(document).on('click', '.ah-delete-block', function(e){
  e.stopPropagation();
  var bid = parseInt($(this).closest('.ah-canvas-block').data('block-id'));
  blocks = blocks.filter(function(b){ return b._id !== bid; });
  renderCanvas();
});

// Sync field changes to state
$(document).on('input change', '.ah-block-body input, .ah-block-body textarea, .ah-block-body select', function(){
  syncField($(this));
});

// Add repeater row
$(document).on('click', '.ah-add-row', function(){
  var bid    = parseInt($(this).data('block-id'));
  var repKey = $(this).data('repeater');
  var b      = getBlock(bid);
  if (!b) return;
  if (!b.data[repKey]) b.data[repKey] = [];
  b.data[repKey].push({});
  var def    = BLOCK_DEFS[b.type];
  var rep    = def.repeater;
  var ri     = b.data[repKey].length - 1;
  var newRow = buildRepeaterRow(rep.fields, {}, bid, repKey, ri);
  $(this).before(newRow);
});

// Remove repeater row
$(document).on('click', '.ah-remove-row', function(){
  var $row   = $(this).closest('.ah-repeater-row');
  var $rep   = $row.closest('.ah-repeater');
  var bid    = parseInt($rep.data('block-id'));
  var repKey = $rep.data('repeater');
  var b      = getBlock(bid);
  // Remove from state
  var idx    = $row.index();
  if (b && b.data[repKey]) b.data[repKey].splice(idx, 1);
  $row.remove();
  // Renumber remaining rows
  $rep.find('.ah-repeater-row').each(function(i){
    $(this).find('[data-rep-index]').attr('data-rep-index', i);
  });
});

// Auto-generate slug from title
$('#page-title').on('input', function(){
  var $slug = $('#page-slug');
  if (!$slug.data('manually-edited')) {
    $slug.val($(this).val().toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,''));
  }
});
$('#page-slug').on('input', function(){ $(this).data('manually-edited', true); });

// Save: serialize state to JSON
$('#ah-builder-form').on('submit', function(){
  // Sync any remaining repeater state
  $('#ah-canvas [data-repeater]').each(function(){ syncField($(this)); });
  $('#blocks-json').val(JSON.stringify(blocks.map(function(b){
    return { type: b.type, data: b.data || {} };
  })));
});

// ── Utilities ─────────────────────────────────────────────────────────────────
function esc(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function hexToLight(hex) {
  return hex.replace(/^#/, '').length === 6
    ? 'rgba('+parseInt(hex.slice(1,3),16)+','+parseInt(hex.slice(3,5),16)+','+parseInt(hex.slice(5,7),16)+',.1)'
    : '#f3f4f6';
}

// ── Init ─────────────────────────────────────────────────────────────────────
renderCanvas();

})(jQuery);
</script>

<?php endif; // builder vs list ?>
</div><!-- /wrap -->
