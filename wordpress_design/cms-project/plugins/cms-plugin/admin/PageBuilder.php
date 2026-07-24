<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

global $wpdb;
$table   = $wpdb->prefix . 'ah_builder_pages';
$notice  = '';
$action  = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id = (int) ( $_GET['id'] ?? 0 );
$content_tax_m = new AH_Content_Taxonomy_Model();

// ── Template presets ──────────────────────────────────────────────────────────
function ah_builder_templates(): array {
	return array(
		'faq' => array(
			'label' => 'FAQ Page', 'icon' => '❓', 'desc' => 'Hero + accordion + links + CTA',
			'blocks' => array(
				array( 'type' => 'hero', 'data' => array( 'heading' => 'Frequently Asked Questions', 'subheading' => 'Find answers to the most common property questions.', 'bg' => 'light' ) ),
				array( 'type' => 'faq', 'data' => array( 'heading' => 'Buying a Property', 'items' => array( array( 'q' => 'How long does buying a property take?', 'a' => 'The average property purchase takes 8–12 weeks from offer to completion, though this varies based on the chain and legal complexity.' ), array( 'q' => 'Do I need a solicitor?', 'a' => 'Yes, a conveyancing solicitor is required to handle the legal transfer of ownership.' ), array( 'q' => 'What is stamp duty?', 'a' => 'Stamp Duty Land Tax (SDLT) is a tax payable on property purchases above £250,000 (£425,000 for first-time buyers).' ) ) ) ),
				array( 'type' => 'faq', 'data' => array( 'heading' => 'Selling a Property', 'items' => array( array( 'q' => 'How do I value my property?', 'a' => 'We offer free, accurate market valuations based on comparable sales and current market conditions.' ), array( 'q' => 'What fees are involved in selling?', 'a' => 'Typical costs include estate agent fees (1–3%), conveyancing, and any early mortgage repayment charges.' ) ) ) ),
				array( 'type' => 'cta_banner', 'data' => array( 'heading' => 'Still Have Questions?', 'text' => 'Speak to one of our experts for personalised advice.', 'btn1_text' => 'Book Free Call', 'btn1_url' => '/free-consultation/', 'theme' => 'dark' ) ),
			),
		),
		'guide' => array(
			'label' => 'Guide / Article', 'icon' => '📖', 'desc' => 'Hero + rich text + links + CTA',
			'blocks' => array(
				array( 'type' => 'hero', 'data' => array( 'heading' => 'First-Time Buyers Guide', 'subheading' => 'Everything you need to know about buying your first home in the UK.', 'bg' => 'light' ) ),
				array( 'type' => 'text_block', 'data' => array( 'content' => '<p>Buying your first home is one of the biggest financial decisions you\'ll make. This guide walks you through every stage - from saving your deposit to getting the keys.</p><h2>Step 1: Get Your Finances in Order</h2><p>Before you start viewing properties, understand your budget. Most lenders require at least a 5% deposit, though 10% gives you access to better mortgage rates.</p><h2>Step 2: Get a Mortgage in Principle</h2><p>A mortgage in principle (MIP) shows sellers you\'re a serious buyer and helps you understand your maximum borrowing.</p>' ) ),
				array( 'type' => 'links_list', 'data' => array( 'heading' => 'Related Guides', 'cols' => '2', 'links' => array( array( 'label' => 'Understanding Stamp Duty', 'url' => '/guides/stamp-duty/', 'icon' => '💷', 'desc' => 'How much will you pay?' ), array( 'label' => 'Help to Buy Explained', 'url' => '/guides/help-to-buy/', 'icon' => '🏛️', 'desc' => 'Government schemes for first-time buyers' ), array( 'label' => 'Mortgage Guide', 'url' => '/guides/mortgages/', 'icon' => '🏦', 'desc' => 'Types, rates and how to apply' ), array( 'label' => 'Conveyancing Process', 'url' => '/guides/conveyancing/', 'icon' => '📋', 'desc' => 'Legal steps explained simply' ) ) ) ),
				array( 'type' => 'cta_banner', 'data' => array( 'heading' => 'Need Personal Guidance?', 'text' => 'Our experts are happy to answer your questions for free.', 'btn1_text' => 'Book Free Consultation', 'btn1_url' => '/free-consultation/', 'theme' => 'gold' ) ),
			),
		),
	);
}

// ── POST handlers ─────────────────────────────────────────────────────────────
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ah_builder_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_builder_nonce'], 'ah_builder_save' ) ) wp_die( 'Security check failed.' );

	if ( isset( $_POST['delete_page'] ) && $edit_id ) {
		$wpdb->delete( $table, array( 'id' => $edit_id ) );
		$content_tax_m->sync_terms( 'builder_page', $edit_id, array() );
		$notice = 'Page deleted.'; $action = 'list'; $edit_id = 0;

	} elseif ( isset( $_POST['create_from_template'] ) ) {
		// Create page from selected template then open builder
		$tpl_key = sanitize_key( $_POST['template_key'] ?? 'landing' );
		$tpls    = ah_builder_templates();
		$tpl     = $tpls[ $tpl_key ] ?? reset( $tpls );
		$title   = sanitize_text_field( $_POST['page_title'] ?? $tpl['label'] );
		$slug    = sanitize_title( $_POST['page_slug'] ?: $title );
		$wpdb->insert( $table, array( 'title' => $title, 'slug' => $slug, 'blocks' => wp_json_encode( $tpl['blocks'] ), 'status' => 'draft' ) );
		$edit_id = $wpdb->insert_id;
		$content_tax_m->sync_terms( 'builder_page', (int) $edit_id, $_POST['taxonomy_ids'] ?? array() );
		$action  = 'builder';
		$notice  = 'Page created from "' . esc_html( $tpl['label'] ) . '" template.';

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
			$content_tax_m->sync_terms( 'builder_page', $edit_id, $_POST['taxonomy_ids'] ?? array() );
			$notice = 'Page saved.';
		} else {
			$wpdb->insert( $table, $data );
			$edit_id = $wpdb->insert_id;
			$content_tax_m->sync_terms( 'builder_page', (int) $edit_id, $_POST['taxonomy_ids'] ?? array() );
			$notice  = 'Page created.';
			$action  = 'builder';
		}

		// Save layout / CTA opts
		$_cta_theme = $_POST['cta_theme'] ?? 'dark';
		update_option( 'ah_bp_' . $edit_id . '_opts', array(
			'show_header'   => ! empty( $_POST['show_header'] )   ? 1 : 0,
			'show_footer'   => ! empty( $_POST['show_footer'] )   ? 1 : 0,
			'cta_enabled'   => ! empty( $_POST['cta_enabled'] )   ? 1 : 0,
			'cta_heading'   => sanitize_text_field( $_POST['cta_heading']   ?? '' ),
			'cta_text'      => sanitize_textarea_field( $_POST['cta_text']  ?? '' ),
			'cta_btn1_text' => sanitize_text_field( $_POST['cta_btn1_text'] ?? '' ),
			'cta_btn1_url'  => esc_url_raw( $_POST['cta_btn1_url']          ?? '' ),
			'cta_btn2_text' => sanitize_text_field( $_POST['cta_btn2_text'] ?? '' ),
			'cta_btn2_url'  => esc_url_raw( $_POST['cta_btn2_url']          ?? '' ),
			'cta_theme'     => in_array( $_cta_theme, array( 'dark', 'gold', 'light', 'blue' ), true ) ? $_cta_theme : 'dark',
		), false );
		$page_opts = (array) get_option( 'ah_bp_' . $edit_id . '_opts', array() );
	}
}

// ── DATA ─────────────────────────────────────────────────────────────────────
$current_page  = $edit_id ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE id = %d", $edit_id ) ) : null;
$existing_blocks = $current_page ? ( $current_page->blocks ?: '[]' ) : '[]';
$page_opts       = $edit_id ? (array) get_option( 'ah_bp_' . $edit_id . '_opts', array() ) : array();
?>
<div class="wrap ah-wrap">

<?php if ( $notice ) : ?>
  <div class="ah-notice ah-notice-success"><?php echo esc_html( $notice ); ?></div>
<?php endif; ?>

<?php /* ══════════════ LIST VIEW ══════════════ */ ?>
<?php if ( $action === 'list' ) :
  $pb_search  = sanitize_text_field( $_GET['s'] ?? '' );
  $pb_status  = sanitize_key( $_GET['pb_status'] ?? '' );
  $_pb_where  = array();
  $_pb_in     = array();
  if ( $pb_search ) {
    $_pb_where[] = 'title LIKE %s';
    $_pb_in[]    = '%' . $wpdb->esc_like( $pb_search ) . '%';
  }
  if ( in_array( $pb_status, array( 'active', 'draft' ), true ) ) {
    $_pb_where[] = 'status = %s';
    $_pb_in[]    = $pb_status;
  }
  $_pb_sql = "SELECT * FROM `{$table}`" . ( $_pb_where ? ' WHERE ' . implode( ' AND ', $_pb_where ) : '' ) . ' ORDER BY updated_at DESC';
  $pages = $_pb_in
    ? $wpdb->get_results( $wpdb->prepare( $_pb_sql, $_pb_in ) )
    : $wpdb->get_results( $_pb_sql );
?>
  <div class="ah-table-top" style="margin-bottom:0">
    <?php \Ah\Cms\Admin\Components\AdminComponents::pageHeader( 'layout', 'Page Builder', 'Create pages with the drag-drop builder.' ); ?>
    <div style="display:flex;gap:8px;">
      <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-page-builder', 'action' => 'builder' ), admin_url( 'admin.php' ) ) ); ?>"
         class="ah-btn ah-btn-secondary">+ Blank Page</a>
      <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-page-builder', 'action' => 'templates' ), admin_url( 'admin.php' ) ) ); ?>"
         class="ah-btn ah-btn-primary">📋 From Template</a>
    </div>
  </div>
  <form class="ah-search-form" method="get" style="margin:12px 0 0">
    <input type="hidden" name="page" value="ah-page-builder">
    <input type="search" name="s" value="<?php echo esc_attr( $pb_search ); ?>" placeholder="Search pages…">
    <select name="pb_status">
      <option value="">All Statuses</option>
      <option value="active" <?php selected( $pb_status, 'active' ); ?>>Active</option>
      <option value="draft"  <?php selected( $pb_status, 'draft' );  ?>>Draft</option>
    </select>
    <button class="ah-btn ah-btn-secondary">Filter</button>
    <?php if ( $pb_search || $pb_status ) : ?>
      <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-page-builder' ) ); ?>" class="ah-btn ah-btn-secondary" style="opacity:.7;">✕ Clear</a>
    <?php endif; ?>
  </form>
  <p style="color:var(--ah-text-muted);margin:6px 0 20px">Build custom pages with drag-and-drop blocks - hero banners, card grids, CTAs, FAQs and more.</p>

  <?php if ( empty( $pages ) ) : ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:8px;">
      <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-page-builder', 'action' => 'builder' ), admin_url( 'admin.php' ) ) ); ?>"
         class="ah-card" style="text-decoration:none;color:inherit;text-align:center;padding:36px 24px;transition:box-shadow .15s;" onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,.1)'" onmouseout="this.style.boxShadow=''">
        <div style="font-size:2.5rem;margin-bottom:12px;">🧱</div>
        <h3 style="margin:0 0 8px;">Blank Page</h3>
        <p style="color:var(--ah-muted);margin:0;font-size:.85rem;">Start from scratch - drag and drop blocks to build your page.</p>
      </a>
      <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-page-builder', 'action' => 'templates' ), admin_url( 'admin.php' ) ) ); ?>"
         class="ah-card" style="text-decoration:none;color:inherit;text-align:center;padding:36px 24px;transition:box-shadow .15s;border-top:3px solid var(--ah-primary);" onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,.1)'" onmouseout="this.style.boxShadow=''">
        <div style="font-size:2.5rem;margin-bottom:12px;">📋</div>
        <h3 style="margin:0 0 8px;">From Template</h3>
        <p style="color:var(--ah-muted);margin:0;font-size:.85rem;">Pick a pre-built layout - FAQ, and more.</p>
      </a>
    </div>
  <?php else : ?>
    <div class="ah-table-wrap">
      <table class="ah-table">
        <thead>
          <tr><th>Title</th><th>Slug</th><th>Blocks</th><th>CMS Terms</th><th>Status</th><th>Updated</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ( $pages as $pg ) :
            $b_count = is_string( $pg->blocks ) ? count( json_decode( $pg->blocks, true ) ?: array() ) : 0;
          ?>
            <tr>
              <td><strong><?php echo esc_html( $pg->title ); ?></strong></td>
              <td><code>/<?php echo esc_html( $pg->slug ); ?>/</code></td>
              <td><?php echo esc_html( $b_count ); ?> block<?php echo $b_count !== 1 ? 's' : ''; ?></td>
              <td><?php $content_tax_m->render_badges( 'builder_page', (int) $pg->id ); ?></td>
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

<?php /* ══════════════ TEMPLATES VIEW ══════════════ */ ?>
<?php elseif ( $action === 'templates' ) :
  $tpls = ah_builder_templates();
?>
  <div class="ah-table-top" style="margin-bottom:0">
    <?php \Ah\Cms\Admin\Components\AdminComponents::pageHeader( 'layout', 'Choose a Template', 'Pick a pre-built layout to start with.' ); ?>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-page-builder' ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">&larr; Back</a>
  </div>
  <p style="color:var(--ah-muted);margin:6px 0 24px;">Pick a pre-built layout. You can edit every block after creation.</p>

  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;">
    <?php foreach ( $tpls as $tpl_key => $tpl ) : ?>
      <div class="ah-card" style="padding:0;overflow:hidden;">
        <div style="background:var(--ah-primary,#1e40af);color:#fff;padding:20px 24px;">
          <div style="font-size:2rem;margin-bottom:8px;"><?php echo $tpl['icon']; ?></div>
          <h3 style="margin:0 0 4px;color:#fff;"><?php echo esc_html( $tpl['label'] ); ?></h3>
          <p style="margin:0;opacity:.8;font-size:.82rem;"><?php echo esc_html( $tpl['desc'] ); ?></p>
          <p style="margin:4px 0 0;opacity:.6;font-size:.75rem;"><?php echo count( $tpl['blocks'] ); ?> blocks</p>
        </div>
        <div style="padding:20px 24px;">
          <form method="post">
            <?php wp_nonce_field( 'ah_builder_save', 'ah_builder_nonce' ); ?>
            <input type="hidden" name="create_from_template" value="1">
            <input type="hidden" name="template_key" value="<?php echo esc_attr( $tpl_key ); ?>">
            <div class="ah-form-row" style="margin-bottom:12px;">
              <label style="font-size:.8rem;margin-bottom:4px;display:block;font-weight:600;">Page Title</label>
              <input type="text" name="page_title" value="<?php echo esc_attr( $tpl['label'] ); ?>" required style="width:100%;box-sizing:border-box;">
            </div>
            <div class="ah-form-row" style="margin-bottom:16px;">
              <label style="font-size:.8rem;margin-bottom:4px;display:block;font-weight:600;">Slug (URL)</label>
              <input type="text" name="page_slug" placeholder="auto-generated-from-title" style="width:100%;box-sizing:border-box;">
            </div>
            <button type="submit" class="ah-btn ah-btn-primary" style="width:100%;justify-content:center;">
              Use This Template &rarr;
            </button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

<?php /* ══════════════ BUILDER VIEW ══════════════ */ ?>
<?php else : ?>

<style>
/* ── Page Builder Styles ───────────────────────────────────────────────── */

/* Top bar */
.ah-builder-topbar {
  display:flex; align-items:center; justify-content:space-between; gap:12px;
  padding:10px 20px; background:#fff;
  border-bottom:1px solid #e5e7eb; box-shadow:0 1px 6px rgba(0,0,0,.05);
  margin:0 -20px;  z-index:100;
}
.ah-builder-topbar input[type=text] {
  border:1px solid #d1d5db; border-radius:7px; padding:7px 12px;
  font-size:.9rem; font-weight:600; max-width:300px;
  transition:border-color .15s,box-shadow .15s;
}
.ah-builder-topbar input[type=text]:focus {
  outline:none; border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.12);
}
.ah-builder-topbar .ah-btn { padding:7px 16px; font-size:.8rem; }

/* 3-column grid */
.ah-builder-wrap {
  display:grid; grid-template-columns:232px 1fr 236px;
  gap:0; height:calc(100vh - 108px); overflow:hidden; margin:0 -20px;
  border:1px solid #e5e7eb;
}

/* ── Left palette ──────────────────────────────────── */
.ah-palette {
  background:#181e2e; color:#c9d1e0; overflow-y:auto;
  padding:10px 8px; border-right:1px solid rgba(255,255,255,.05);
}
.ah-palette h4 {
  font-size:.58rem; text-transform:uppercase; letter-spacing:.13em; color:#4b5563;
  margin:14px 0 5px; padding:0 8px;
}
.ah-palette h4:first-child { margin-top:4px; }
.ah-palette-block {
  display:flex; align-items:center; gap:9px; padding:8px 10px; border-radius:7px;
  cursor:pointer; font-size:.79rem; font-weight:500; color:#9ca3af;
  transition:background .12s,color .12s,transform .1s; margin-bottom:2px;
}
.ah-palette-block:hover { background:rgba(255,255,255,.1); color:#fff; transform:translateX(2px); }
.ah-palette-block:active { transform:scale(.96); }
.ah-palette-block .icon { font-size:.95rem; width:20px; text-align:center; flex-shrink:0; }

/* ── Canvas ──────────────────────────────────────── */
.ah-canvas-wrap { overflow-y:auto; background:#f1f3f6; padding:18px 16px; }
.ah-canvas { min-height:calc(100vh - 180px); }
.ah-canvas-empty {
  text-align:center; padding:56px 20px; color:#9ca3af;
  border:2px dashed #d1d5db; border-radius:12px; background:#fff; margin-top:4px;
}
.ah-canvas-empty .icon { font-size:2.8rem; margin-bottom:10px; }

/* Canvas blocks */
.ah-canvas-block {
  background:#fff; border-radius:10px; border:1.5px solid #e5e7eb;
  margin-bottom:10px; overflow:hidden; transition:box-shadow .15s,border-color .15s;
}
.ah-canvas-block:hover { box-shadow:0 3px 14px rgba(0,0,0,.07); border-color:#d1d5db; }
.ah-canvas-block.ah-block-active { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.1); }

.ah-block-header {
  display:flex; align-items:center; gap:8px; padding:10px 14px;
  background:#f9fafb; cursor:pointer; user-select:none;
}
.ah-canvas-block.ah-block-active .ah-block-header { background:#eff6ff; }

.ah-block-handle { cursor:grab; color:#d1d5db; font-size:.9rem; transition:color .15s; flex-shrink:0; }
.ah-block-handle:hover { color:#6b7280; }
.ah-block-handle:active { cursor:grabbing; }

.ah-block-title {
  flex:1; font-size:.81rem; font-weight:600; color:#374151;
  white-space:nowrap; overflow:hidden; text-overflow:ellipsis; min-width:0;
}
.ah-block-type-badge {
  font-size:.62rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em;
  padding:2px 7px; border-radius:20px; white-space:nowrap; flex-shrink:0;
}
.ah-block-actions { display:flex; align-items:center; gap:2px; flex-shrink:0; }
.ah-block-actions button {
  background:none; border:none; cursor:pointer; padding:4px 6px;
  color:#9ca3af; border-radius:5px; font-size:.8rem; line-height:1;
  transition:background .12s,color .12s;
}
.ah-block-actions button:hover { background:#f3f4f6; color:#374151; }
.ah-block-actions .ah-delete-block:hover { color:#ef4444; background:#fef2f2; }
.ah-block-actions .ah-toggle-block { font-size:.7rem; transition:background .12s,color .12s,transform .2s; }
.ah-canvas-block.ah-block-active .ah-toggle-block { transform:rotate(180deg); }

/* Block body */
.ah-block-body { padding:14px 16px; display:none; border-top:1px solid #f0f2f5; }
.ah-canvas-block.ah-block-active .ah-block-body { display:block; }
.ah-block-body .ah-form-row { margin-bottom:10px; }
.ah-block-body label {
  font-size:.72rem; font-weight:700; color:#6b7280; display:block;
  margin-bottom:3px; text-transform:uppercase; letter-spacing:.04em;
}
.ah-block-body input, .ah-block-body textarea, .ah-block-body select {
  width:100%; border:1px solid #e5e7eb; border-radius:6px;
  padding:6px 9px; font-size:.82rem; box-sizing:border-box;
  transition:border-color .15s,box-shadow .15s;
}
.ah-block-body input:focus, .ah-block-body textarea:focus, .ah-block-body select:focus {
  outline:none; border-color:#3b82f6; box-shadow:0 0 0 2px rgba(59,130,246,.1);
}
.ah-block-body textarea { resize:vertical; min-height:76px; }
.ah-block-body .wp-editor-wrap { max-width:none; }
.ah-block-body .wp-editor-wrap textarea { border-radius:0; }
.ah-block-body .mce-container, .ah-block-body .quicktags-toolbar { box-sizing:border-box; }

/* Repeater */
.ah-repeater { border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; margin-top:6px; background:#fafafa; }
.ah-repeater-row {
  display:grid; gap:6px; padding:10px 32px 10px 10px;
  border-bottom:1px solid #f0f0f0; position:relative; background:#fff;
}
.ah-repeater-row:last-of-type { border-bottom:none; }
.ah-repeater-row label { font-size:.68rem; color:#9ca3af; font-weight:600; text-transform:uppercase; letter-spacing:.04em; }
.ah-repeater-row .ah-remove-row {
  position:absolute; top:8px; right:8px; background:none; border:none;
  cursor:pointer; color:#d1d5db; font-size:.75rem; line-height:1;
  padding:3px 5px; border-radius:4px; transition:color .12s,background .12s;
}
.ah-repeater-row .ah-remove-row:hover { color:#ef4444; background:#fef2f2; }
.ah-add-row {
  display:flex; align-items:center; justify-content:center; gap:4px;
  padding:7px 12px; color:#3b82f6; font-size:.78rem; font-weight:600;
  cursor:pointer; background:#f8fbff; border:none; width:100%;
  transition:background .12s;
}
.ah-add-row:hover { background:#dbeafe; }

/* ── Right settings panel ────────────────────────── */
.ah-settings-panel {
  background:#fff; border-left:1px solid #e5e7eb;
  padding:14px 12px; overflow-y:auto;
}
.ah-settings-panel h4 {
  font-size:.62rem; font-weight:700; text-transform:uppercase; letter-spacing:.1em;
  color:#9ca3af; margin:0 0 12px; padding-bottom:8px; border-bottom:1px solid #f3f4f6;
}
.ah-settings-panel .ah-form-row { margin-bottom:10px; }
.ah-settings-panel label {
  font-size:.7rem; font-weight:700; color:#6b7280; display:block;
  margin-bottom:3px; text-transform:uppercase; letter-spacing:.04em;
}
/* .ah-settings-panel input, */
 .ah-settings-panel select, .ah-settings-panel textarea {
  width:100%; border:1px solid #e5e7eb; border-radius:6px;
  padding:6px 8px; font-size:.8rem; box-sizing:border-box;
  transition:border-color .15s,box-shadow .15s;
}
.ah-settings-panel input:focus, .ah-settings-panel select:focus, .ah-settings-panel textarea:focus {
  outline:none; border-color:#3b82f6; box-shadow:0 0 0 2px rgba(59,130,246,.1);
}
.ah-settings-panel small { display:block; font-size:.68rem; color:#9ca3af; margin-top:3px; word-break:break-all; line-height:1.4; }

/* Drag helpers */
.ui-sortable-helper { box-shadow:0 12px 36px rgba(0,0,0,.18) !important; border-color:#3b82f6 !important; }
.ui-sortable-placeholder { background:#eff6ff; border:2px dashed #93c5fd; border-radius:10px; margin-bottom:10px; }
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
      <select name="page_status" style="border:1px solid var(--ah-border);border-radius:4px;font-size:.82rem">
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
      <div class="ah-palette-block" data-type="hero">          <span class="icon">🎯</span> Hero Banner</div>
      <div class="ah-palette-block" data-type="section_heading"><span class="icon">📌</span> Section Heading</div>
      <div class="ah-palette-block" data-type="text_block">    <span class="icon">📝</span> Rich Text</div>
      <div class="ah-palette-block" data-type="columns">       <span class="icon">⬛</span> 2-Col Text</div>
      <div class="ah-palette-block" data-type="tabs">          <span class="icon">🗂️</span> Tabs</div>
      <div class="ah-palette-block" data-type="divider">       <span class="icon">➖</span> Divider</div>
      <div class="ah-palette-block" data-type="spacer">        <span class="icon">↕️</span> Spacer</div>

      <h4>Media</h4>
      <div class="ah-palette-block" data-type="gallery">       <span class="icon">🖼️</span> Gallery</div>
      <div class="ah-palette-block" data-type="video">         <span class="icon">▶️</span> Video Embed</div>
      <div class="ah-palette-block" data-type="map_embed">     <span class="icon">📍</span> Map Embed</div>
      <div class="ah-palette-block" data-type="logo_strip">    <span class="icon">🏷️</span> Logo Strip</div>

      <h4>Content</h4>
      <div class="ah-palette-block" data-type="cards">         <span class="icon">🃏</span> Card Grid</div>
      <div class="ah-palette-block" data-type="image_text">    <span class="icon">🖼️</span> Image + Text</div>
      <div class="ah-palette-block" data-type="testimonial">   <span class="icon">💬</span> Testimonial</div>
      <div class="ah-palette-block" data-type="steps">         <span class="icon">🔢</span> Steps / Process</div>
      <div class="ah-palette-block" data-type="timeline">      <span class="icon">📅</span> Timeline</div>
      <div class="ah-palette-block" data-type="icon_list">     <span class="icon">✅</span> Icon List</div>
      <div class="ah-palette-block" data-type="pull_quote">    <span class="icon">❝</span> Pull Quote</div>
      <div class="ah-palette-block" data-type="comparison">    <span class="icon">⚖️</span> Comparison Table</div>
      <div class="ah-palette-block" data-type="pricing">       <span class="icon">💰</span> Pricing Card</div>

      <h4>Action</h4>
      <div class="ah-palette-block" data-type="cta_banner">    <span class="icon">📣</span> CTA Banner</div>
      <div class="ah-palette-block" data-type="stats_row">     <span class="icon">📊</span> Stats Row</div>
      <div class="ah-palette-block" data-type="faq">           <span class="icon">❓</span> FAQ Accordion</div>
      <div class="ah-palette-block" data-type="alert">         <span class="icon">📢</span> Alert / Notice</div>
      <div class="ah-palette-block" data-type="notice_bar">    <span class="icon">📯</span> Notice Bar</div>
      <div class="ah-palette-block" data-type="download">      <span class="icon">⬇️</span> Download Button</div>

      <h4>People & Contact</h4>
      <div class="ah-palette-block" data-type="contact_card">  <span class="icon">📇</span> Contact Card</div>

      <h4>Navigation</h4>
      <div class="ah-palette-block" data-type="button_row">    <span class="icon">🔘</span> Button Row</div>
      <div class="ah-palette-block" data-type="links_list">    <span class="icon">🔗</span> Links List</div>
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

      <div class="ah-form-row">
        <label>Taxonomy Terms</label>
        <?php $content_tax_m->render_picker( 'builder_page', $edit_id ); ?>
      </div>

      <hr style="border:none;border-top:1px solid #e5e7eb;margin:16px 0">
      <h4>Layout</h4>

      <div class="ah-form-row" style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
        <input type="checkbox" name="show_header" id="ahb_show_header" value="1"
               <?php checked( (int) ( $page_opts['show_header'] ?? 1 ), 1 ); ?>
               style="width:auto;margin:0;">
        <label for="ahb_show_header" style="margin:0;text-transform:none;font-size:.82rem;font-weight:500;">Show site header</label>
      </div>
      <div class="ah-form-row" style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
        <input type="checkbox" name="show_footer" id="ahb_show_footer" value="1"
               <?php checked( (int) ( $page_opts['show_footer'] ?? 1 ), 1 ); ?>
               style="width:auto;margin:0;">
        <label for="ahb_show_footer" style="margin:0;text-transform:none;font-size:.82rem;font-weight:500;">Show site footer</label>
      </div>

      <hr style="border:none;border-top:1px solid #e5e7eb;margin:16px 0">
      <h4>Bottom CTA</h4>

      <div class="ah-form-row" style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
        <input type="checkbox" name="cta_enabled" id="ahb_cta_enabled" value="1"
               <?php checked( (int) ( $page_opts['cta_enabled'] ?? 0 ), 1 ); ?>
               style="width:auto;margin:0;">
        <label for="ahb_cta_enabled" style="margin:0;text-transform:none;font-size:.82rem;font-weight:500;">Show bottom CTA section</label>
      </div>
      <div id="ahb-cta-fields" style="<?php echo empty( $page_opts['cta_enabled'] ) ? 'display:none;' : ''; ?>">
        <div class="ah-form-row">
          <label>Heading</label>
          <input type="text" name="cta_heading" value="<?php echo esc_attr( $page_opts['cta_heading'] ?? '' ); ?>" placeholder="Still have questions?">
        </div>
        <div class="ah-form-row">
          <label>Description</label>
          <textarea name="cta_text" rows="2" placeholder="Speak to one of our experts."><?php echo esc_textarea( $page_opts['cta_text'] ?? '' ); ?></textarea>
        </div>
        <div class="ah-form-row">
          <label>Button 1 Text</label>
          <input type="text" name="cta_btn1_text" value="<?php echo esc_attr( $page_opts['cta_btn1_text'] ?? '' ); ?>" placeholder="Get in Touch">
        </div>
        <div class="ah-form-row">
          <label>Button 1 URL</label>
          <input type="text" name="cta_btn1_url" value="<?php echo esc_attr( $page_opts['cta_btn1_url'] ?? '' ); ?>" placeholder="/contact/">
        </div>
        <div class="ah-form-row">
          <label>Button 2 Text <span style="font-weight:400;color:var(--ah-muted)">(optional)</span></label>
          <input type="text" name="cta_btn2_text" value="<?php echo esc_attr( $page_opts['cta_btn2_text'] ?? '' ); ?>" placeholder="Learn More">
        </div>
        <div class="ah-form-row">
          <label>Button 2 URL</label>
          <input type="text" name="cta_btn2_url" value="<?php echo esc_attr( $page_opts['cta_btn2_url'] ?? '' ); ?>" placeholder="/about/">
        </div>
        <div class="ah-form-row">
          <label>Theme</label>
          <select name="cta_theme">
            <option value="dark"  <?php selected( $page_opts['cta_theme'] ?? 'dark', 'dark' ); ?>>Dark</option>
            <option value="gold"  <?php selected( $page_opts['cta_theme'] ?? '',      'gold' ); ?>>Gold</option>
            <option value="light" <?php selected( $page_opts['cta_theme'] ?? '',      'light' ); ?>>Light</option>
            <option value="blue"  <?php selected( $page_opts['cta_theme'] ?? '',      'blue' ); ?>>Blue</option>
          </select>
        </div>
      </div>

      <?php if ( $current_page ) : ?>
        <hr style="border:none;border-top:1px solid #e5e7eb;margin:16px 0">
        <h4>Danger Zone</h4>
        <div>
          <button type="submit" form="ah-builder-delete-form" name="delete_page" value="1" class="ah-confirm-delete" data-title="Delete Page" data-confirm="This page and all its content will be permanently deleted."
                  style="width:100%;background:#fef2f2;color:#ef4444;border:1px solid #fecaca;border-radius:6px;padding:8px;cursor:pointer;font-size:.82rem;font-weight:600">
            🗑 Delete Page
          </button>
        </div>
      <?php endif; ?>
    </div>

  </div><!-- /builder-wrap -->
</form>
<?php if ( $current_page ) : ?>
  <form id="ah-builder-delete-form" method="post" style="display:none;">
    <?php wp_nonce_field( 'ah_builder_save', 'ah_builder_nonce' ); ?>
    <input type="hidden" name="page_title" value="<?php echo esc_attr( $current_page->title ); ?>">
  </form>
<?php endif; ?>

<script>
(function($){
'use strict';

// ── Block definitions ────────────────────────────────────────────────────────
var BLOCK_DEFS = {
  hero: {
    label: 'Hero Banner', icon: '🎯', color: '#4f46e5',
    fields: [
      { key:'eyebrow',     label:'Eyebrow Text',         type:'text',     ph:'Trusted Buyer\'s Agents'    },
      { key:'heading',     label:'Heading',              type:'text',     ph:'Welcome to our service'     },
      { key:'subheading',  label:'Subheading',           type:'textarea', ph:'A brief description…'       },
      { key:'cta1_text',   label:'Button 1 Text',        type:'text',     ph:'Book Free Call'             },
      { key:'cta1_url',    label:'Button 1 URL',         type:'text',     ph:'/free-consultation/'        },
      { key:'cta2_text',   label:'Button 2 Text',        type:'text',     ph:'Learn More'                 },
      { key:'cta2_url',    label:'Button 2 URL',         type:'text',     ph:'/about/'                    },
      { key:'bg',          label:'Background',           type:'select',   options:['white','light','dark','gold','client-color-light','client-color-medium','client-color-dark'], def:'white' },
      { key:'bg_image',    label:'Background Image URL', type:'text',     ph:'https://…/image.jpg'        },
      { key:'overlay',     label:'Image Overlay',        type:'select',   options:['light','medium','dark','none'], def:'medium' },
      { key:'min_height',  label:'Min Height (px)',      type:'text',     ph:'480'                        },
      { key:'text_align',  label:'Text Align',           type:'select',   options:['center','left'], def:'center' },
      { key:'full_height', label:'Full Height (100vh)',  type:'select',   options:['no','yes'], def:'no'   },
    ]
  },
  section_heading: {
    label: 'Section Heading', icon: '📌', color: '#0891b2',
    fields: [
      { key:'eyebrow',    label:'Eyebrow',    type:'text',   ph:'Our Approach'    },
      { key:'title',      label:'Title',      type:'text',   ph:'Section Title'   },
      { key:'subtitle',   label:'Subtitle',   type:'text',   ph:'Optional subtitle' },
      { key:'align',      label:'Align',      type:'select', options:['center','left','right'], def:'center' },
      { key:'accent_bar', label:'Accent Bar', type:'select', options:['yes','no'], def:'yes' },
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
      { key:'heading',    label:'Section Heading', type:'text',   ph:'Our Features' },
      { key:'cols',       label:'Columns',         type:'select', options:['2','3','4'], def:'3' },
      { key:'bg',         label:'Background',      type:'select', options:['white','alt'], def:'white' },
      { key:'card_style', label:'Card Style',      type:'select', options:['feat','value','plain'], def:'feat' },
    ],
    repeater: {
      key: 'cards', label: 'Cards', addLabel: '+ Add Card',
      fields: [
        { key:'icon',      label:'Icon/Emoji', type:'text',     ph:'🏠'         },
        { key:'title',     label:'Title',      type:'text',     ph:'Card Title' },
        { key:'text',      label:'Text',       type:'textarea', ph:'Description…' },
        { key:'link_url',  label:'Link URL',   type:'text',     ph:'/guides/…'  },
        { key:'link_text', label:'Link Label', type:'text',     ph:'Learn more' },
      ]
    }
  },
  cta_banner: {
    label: 'CTA Banner', icon: '📣', color: '#b45309',
    fields: [
      { key:'eyebrow',   label:'Eyebrow',      type:'text',     ph:'Ready to start?'       },
      { key:'heading',   label:'Heading',      type:'text',     ph:'Ready to get started?' },
      { key:'text',      label:'Subtext',      type:'textarea', ph:'Supporting message…'   },
      { key:'btn1_text', label:'Button 1 Text',type:'text',     ph:'Book Free Call'        },
      { key:'btn1_url',  label:'Button 1 URL', type:'text',     ph:'/free-consultation/'   },
      { key:'btn2_text', label:'Button 2 Text',type:'text',     ph:'Learn More'            },
      { key:'btn2_url',  label:'Button 2 URL', type:'text',     ph:'/about/'               },
      { key:'theme',     label:'Theme',        type:'select',   options:['gold','dark','light','blue'], def:'gold' },
      { key:'layout',    label:'Layout',       type:'select',   options:['centered','split'], def:'centered' },
    ]
  },
  stats_row: {
    label: 'Stats Row', icon: '📊', color: '#0369a1',
    fields: [
      { key:'heading', label:'Section Heading', type:'text', ph:'By the Numbers' },
    ],
    repeater: {
      key: 'stats', label: 'Stats', addLabel: '+ Add Stat',
      fields: [
        { key:'icon',   label:'Icon',   type:'text', ph:'🏠'         },
        { key:'prefix', label:'Prefix', type:'text', ph:'£'          },
        { key:'number', label:'Number', type:'text', ph:'18'         },
        { key:'suffix', label:'Suffix', type:'text', ph:'k+'         },
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
      { key:'style',   label:'Style',     type:'select', options:['card','plain','numbered'], def:'card' },
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
      { key:'image_url',  label:'Image URL',    type:'text',     ph:'/wp-content/uploads/…'       },
      { key:'image_alt',  label:'Image Alt',    type:'text',     ph:'Descriptive alt text'        },
      { key:'eyebrow',    label:'Eyebrow',      type:'text',     ph:'Our Story'                   },
      { key:'heading',    label:'Heading',      type:'text',     ph:'Section Heading'             },
      { key:'text',       label:'Body Text',    type:'textarea', ph:'Description…'                },
      { key:'btn_text',   label:'Button 1 Text',type:'text',     ph:'Learn More'                  },
      { key:'btn_url',    label:'Button 1 URL', type:'text',     ph:'/page/'                      },
      { key:'btn2_text',  label:'Button 2 Text',type:'text',     ph:'See Examples'                },
      { key:'btn2_url',   label:'Button 2 URL', type:'text',     ph:'/case-studies/'              },
      { key:'layout',     label:'Layout',       type:'select',   options:['image-left','image-right'], def:'image-left' },
    ],
    repeater: {
      key: 'points', label: 'Bullet Points', addLabel: '+ Add Point',
      fields: [
        { key:'icon', label:'Icon', type:'text', ph:'✅'           },
        { key:'text', label:'Text', type:'text', ph:'Key benefit…' },
      ]
    }
  },
  testimonial: {
    label: 'Testimonial', icon: '💬', color: '#0891b2',
    fields: [
      { key:'quote',   label:'Quote',       type:'textarea', ph:'Working with them transformed our property search…' },
      { key:'name',    label:'Author Name', type:'text',     ph:'Sarah & James T.'    },
      { key:'role',    label:'Role / Note', type:'text',     ph:'First-Time Buyers'   },
      { key:'company', label:'Company',     type:'text',     ph:'London, UK'          },
      { key:'stars',   label:'Stars (1–5)', type:'select',   options:['5','4','3','2','1'], def:'5' },
      { key:'avatar',  label:'Avatar URL',  type:'text',     ph:'/wp-content/uploads/avatar.jpg' },
      { key:'bg',      label:'Background',  type:'select',   options:['white','alt','gold'], def:'alt' },
      { key:'layout',  label:'Layout',      type:'select',   options:['centered','card'], def:'centered' },
    ]
  },
  steps: {
    label: 'Steps / Process', icon: '🔢', color: '#7c3aed',
    fields: [
      { key:'heading',   label:'Section Heading', type:'text',   ph:'How It Works' },
      { key:'layout',    label:'Layout',          type:'select', options:['vertical','horizontal'], def:'vertical' },
      { key:'bg',        label:'Background',      type:'select', options:['white','alt'], def:'white' },
      { key:'connector', label:'Show Connector',  type:'select', options:['yes','no'], def:'no' },
    ],
    repeater: {
      key: 'items', label: 'Steps', addLabel: '+ Add Step',
      fields: [
        { key:'title', label:'Title', type:'text',     ph:'Initial Consultation'  },
        { key:'text',  label:'Text',  type:'textarea', ph:'We begin by…'         },
        { key:'icon',  label:'Icon',  type:'text',     ph:'🏠'                   },
      ]
    }
  },
  divider: {
    label: 'Divider', icon: '➖', color: '#9ca3af',
    fields: [
      { key:'style', label:'Style',         type:'select', options:['line','ornament','dots'], def:'line' },
      { key:'label', label:'Optional Label',type:'text',   ph:'- or -' },
    ]
  },
  alert: {
    label: 'Alert / Notice', icon: '📢', color: '#b45309',
    fields: [
      { key:'type',        label:'Type',        type:'select', options:['info','success','warning','tip'], def:'info' },
      { key:'title',       label:'Title',       type:'text',   ph:'Did you know?'       },
      { key:'text',        label:'Text',        type:'textarea',ph:'Important message…' },
      { key:'dismissible', label:'Dismissible', type:'select', options:['no','yes'], def:'no' },
    ]
  },
  columns: {
    label: '2-Col Text', icon: '⬛', color: '#059669',
    fields: [
      { key:'heading', label:'Section Heading', type:'text',   ph:'' },
      { key:'cols',    label:'Columns',         type:'select', options:['2','3'], def:'2' },
      { key:'bg',      label:'Background',      type:'select', options:['white','alt'], def:'white' },
    ],
    repeater: {
      key: 'items', label: 'Columns', addLabel: '+ Add Column',
      fields: [
        { key:'heading', label:'Heading', type:'text',     ph:'Column Title'  },
        { key:'text',    label:'Text',    type:'textarea', ph:'Column body…'  },
        { key:'icon',    label:'Icon',    type:'text',     ph:'✅'            },
      ]
    }
  },
  gallery: {
    label: 'Gallery', icon: '🖼️', color: '#7c3aed',
    fields: [
      { key:'heading', label:'Section Heading', type:'text',   ph:'Our Properties'          },
      { key:'cols',    label:'Columns',         type:'select', options:['2','3','4'], def:'3' },
      { key:'gap',     label:'Gap',             type:'select', options:['sm','md','lg'], def:'md' },
    ],
    repeater: {
      key: 'images', label: 'Images', addLabel: '+ Add Image',
      fields: [
        { key:'url', label:'Image URL', type:'text', ph:'/wp-content/uploads/…' },
        { key:'alt', label:'Alt Text',  type:'text', ph:'Description'           },
        { key:'caption', label:'Caption', type:'text', ph:'Optional caption'    },
      ]
    }
  },
  video: {
    label: 'Video Embed', icon: '▶️', color: '#dc2626',
    fields: [
      { key:'url',     label:'YouTube / Vimeo URL', type:'text', ph:'https://www.youtube.com/watch?v=…' },
      { key:'caption', label:'Caption',             type:'text', ph:'Optional caption below video'       },
      { key:'ratio',   label:'Aspect Ratio',        type:'select', options:['16:9','4:3','1:1'], def:'16:9' },
    ]
  },
  map_embed: {
    label: 'Map Embed', icon: '📍', color: '#059669',
    fields: [
      { key:'url',    label:'Google Maps Embed URL', type:'text', ph:'https://www.google.com/maps/embed?…' },
      { key:'height', label:'Height (px)',           type:'text', ph:'400' },
      { key:'label',  label:'Label above map',       type:'text', ph:'Find Us'    },
    ]
  },
  logo_strip: {
    label: 'Logo Strip', icon: '🏷️', color: '#6b7280',
    fields: [
      { key:'heading', label:'Label / Heading', type:'text', ph:'Trusted by leading firms' },
      { key:'bg',      label:'Background',      type:'select', options:['white','alt'], def:'white' },
    ],
    repeater: {
      key: 'logos', label: 'Logos', addLabel: '+ Add Logo',
      fields: [
        { key:'url',  label:'Image URL', type:'text', ph:'/wp-content/uploads/logo.png' },
        { key:'alt',  label:'Alt Text',  type:'text', ph:'Company Name'                 },
        { key:'link', label:'Link URL',  type:'text', ph:'https://…'                    },
      ]
    }
  },
  timeline: {
    label: 'Timeline', icon: '📅', color: '#0891b2',
    fields: [
      { key:'heading', label:'Section Heading', type:'text', ph:'Our Journey' },
      { key:'bg',      label:'Background',      type:'select', options:['white','alt'], def:'white' },
    ],
    repeater: {
      key: 'items', label: 'Events', addLabel: '+ Add Event',
      fields: [
        { key:'date',  label:'Date / Year', type:'text',     ph:'2019'             },
        { key:'title', label:'Title',       type:'text',     ph:'Company Founded'  },
        { key:'text',  label:'Description', type:'textarea', ph:'We started with…' },
        { key:'icon',  label:'Icon',        type:'text',     ph:'🏠'               },
      ]
    }
  },
  pricing: {
    label: 'Pricing Card', icon: '💰', color: '#b45309',
    fields: [
      { key:'heading',    label:'Section Heading', type:'text',     ph:'Our Fees'                 },
      { key:'subtitle',   label:'Section Subtitle',type:'text',     ph:'Simple, transparent pricing' },
    ],
    repeater: {
      key: 'plans', label: 'Plans', addLabel: '+ Add Plan',
      fields: [
        { key:'name',       label:'Plan Name',    type:'text',     ph:'Standard'             },
        { key:'price',      label:'Price',        type:'text',     ph:'1% of purchase price' },
        { key:'period',     label:'Period/Note',  type:'text',     ph:'inc. VAT'             },
        { key:'desc',       label:'Description',  type:'text',     ph:'For buyers up to £500k' },
        { key:'features',   label:'Features (one per line)', type:'textarea', ph:'Property search\nNegotiation\nSolicitor liaison' },
        { key:'cta_text',   label:'Button Text',  type:'text',     ph:'Get Started'          },
        { key:'cta_url',    label:'Button URL',   type:'text',     ph:'/contact/'            },
        { key:'highlight',  label:'Highlighted',  type:'select',   options:['no','yes'], def:'no' },
      ]
    }
  },
  pull_quote: {
    label: 'Pull Quote', icon: '❝', color: '#b45309',
    fields: [
      { key:'quote', label:'Quote Text', type:'textarea', ph:'The most important thing is to find the right property at the right price.' },
      { key:'size',  label:'Size',       type:'select',   options:['md','lg'], def:'md'     },
      { key:'align', label:'Align',      type:'select',   options:['center','left'], def:'center' },
      { key:'color', label:'Accent',     type:'select',   options:['gold','dark','muted'], def:'gold' },
    ]
  },
  icon_list: {
    label: 'Icon List', icon: '✅', color: '#059669',
    fields: [
      { key:'heading', label:'Section Heading', type:'text',   ph:''         },
      { key:'cols',    label:'Columns',         type:'select', options:['1','2'], def:'1' },
      { key:'bg',      label:'Background',      type:'select', options:['white','alt'], def:'white' },
    ],
    repeater: {
      key: 'items', label: 'Items', addLabel: '+ Add Item',
      fields: [
        { key:'icon', label:'Icon',  type:'text', ph:'✅'          },
        { key:'text', label:'Text',  type:'text', ph:'Key benefit' },
        { key:'sub',  label:'Sub',   type:'text', ph:'Optional detail' },
      ]
    }
  },
  download: {
    label: 'Download Button', icon: '⬇️', color: '#0369a1',
    fields: [
      { key:'label',    label:'Label',         type:'text',   ph:'First-Time Buyer Guide'         },
      { key:'url',      label:'File URL',      type:'text',   ph:'/wp-content/uploads/guide.pdf'  },
      { key:'filetype', label:'File Type',     type:'text',   ph:'PDF'                            },
      { key:'filesize', label:'File Size',     type:'text',   ph:'2.4 MB'                         },
      { key:'desc',     label:'Description',   type:'text',   ph:'Everything you need to know'    },
    ]
  },
  tabs: {
    label: 'Tabs', icon: '🗂️', color: '#4f46e5',
    fields: [
      { key:'heading', label:'Section Heading', type:'text', ph:'' },
    ],
    repeater: {
      key: 'tabs', label: 'Tabs', addLabel: '+ Add Tab',
      fields: [
        { key:'label',   label:'Tab Label', type:'text',     ph:'Buying'        },
        { key:'content', label:'Content',   type:'textarea', ph:'Tab body text…' },
        { key:'icon',    label:'Icon',      type:'text',     ph:'🏠'            },
      ]
    }
  },
  comparison: {
    label: 'Comparison Table', icon: '⚖️', color: '#7c3aed',
    fields: [
      { key:'heading', label:'Section Heading', type:'text', ph:'Why use a Buyer\'s Agent?' },
      { key:'col1',    label:'Column 1 Label',  type:'text', ph:'With Us'                   },
      { key:'col2',    label:'Column 2 Label',  type:'text', ph:'Without Us'                },
    ],
    repeater: {
      key: 'rows', label: 'Rows', addLabel: '+ Add Row',
      fields: [
        { key:'feature', label:'Feature',     type:'text',   ph:'Property search'   },
        { key:'col1',    label:'Col 1 Value', type:'text',   ph:'✅ Handled for you' },
        { key:'col2',    label:'Col 2 Value', type:'text',   ph:'❌ You do it alone' },
      ]
    }
  },
  notice_bar: {
    label: 'Notice Bar', icon: '📯', color: '#b45309',
    fields: [
      { key:'text',  label:'Message',    type:'text',   ph:'Limited spaces available - book your free consultation today' },
      { key:'cta',   label:'CTA Text',   type:'text',   ph:'Book Now'    },
      { key:'url',   label:'CTA URL',    type:'text',   ph:'/contact/'   },
      { key:'style', label:'Style',      type:'select', options:['gold','dark','info'], def:'gold' },
    ]
  },
  contact_card: {
    label: 'Contact Card', icon: '📇', color: '#0891b2',
    fields: [
      { key:'photo',   label:'Photo URL',  type:'text',     ph:'/wp-content/uploads/agent.jpg' },
      { key:'name',    label:'Name',       type:'text',     ph:'James Whitmore'                },
      { key:'role',    label:'Role',       type:'text',     ph:'Senior Buyer\'s Agent'         },
      { key:'phone',   label:'Phone',      type:'text',     ph:'+44 20 1234 5678'              },
      { key:'email',   label:'Email',      type:'text',     ph:'james@agency.com'              },
      { key:'bio',     label:'Bio',        type:'textarea', ph:'James has 15 years experience…' },
      { key:'cta_text',label:'CTA Text',   type:'text',     ph:'Book a Call'                   },
      { key:'cta_url', label:'CTA URL',    type:'text',     ph:'/book/'                        },
      { key:'layout',  label:'Layout',     type:'select',   options:['horizontal','vertical'], def:'horizontal' },
    ]
  },
};

// Common fields appended to every block
var COMMON_FIELDS = [
  { key:'section_id', label:'Anchor ID', type:'text',   ph:'why-us'                      },
  { key:'padding',    label:'Padding',   type:'select', options:['md','sm','lg','none'], def:'md' },
];

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
  destroyRichEditors();
  $blocks.remove();

  if ( blocks.length === 0 ) {
    $empty.show();
  } else {
    $empty.hide();
    blocks.forEach(function(block){ $canvas.append(buildBlockHTML(block)); });
    makeSortable();
    initRichEditors();
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
      var richClass = block.type === 'text_block' && f.key === 'content' ? ' class="ah-rich-editor"' : '';
      var richId = block.type === 'text_block' && f.key === 'content' ? ' id="ah-rich-editor-'+id+'"' : '';
      html += '<textarea'+richId+richClass+' data-block-id="'+id+'" data-field="'+f.key+'" placeholder="'+esc(f.ph||'')+'" rows="6">'+esc(val)+'</textarea>';
    } else if (f.type === 'select') {
      html += '<select data-block-id="'+id+'" data-field="'+f.key+'">';
      (f.options||[]).forEach(function(o){ html += '<option value="'+o+'"'+(val===o?' selected':'')+'>'+o+'</option>'; });
      html += '</select>';
    } else {
      html += '<input type="text" data-block-id="'+id+'" data-field="'+f.key+'" value="'+esc(val)+'" placeholder="'+esc(f.ph||'')+'">';
    }
    html += '</div>';
  });

  // Common fields (anchor + padding) - appended to every block
  COMMON_FIELDS.forEach(function(f){
    var val = data[f.key] !== undefined ? data[f.key] : (f.def||'');
    html += '<div class="ah-form-row"><label>'+esc(f.label)+'</label>';
    if (f.type === 'select') {
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

function initRichEditors() {
  if (!window.wp || !wp.editor) return;
  $('.ah-canvas-block.ah-block-active .ah-rich-editor').each(function(){
    var el = this;
    if (el.dataset.editorReady) return;
    el.dataset.editorReady = '1';
    wp.editor.initialize(el.id, {
      tinymce: {
        wpautop: true,
        toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,undo,redo',
        toolbar2: '',
        setup: function(editor) {
          editor.on('change keyup undo redo', function() {
            $('#' + editor.id).val(editor.getContent());
            syncField($('#' + editor.id));
          });
        }
      },
      quicktags: true,
      mediaButtons: false
    });
  });
}

function destroyRichEditors() {
  $('.ah-rich-editor').each(function(){
    if (!this.id) return;
    if (window.tinymce && tinymce.get(this.id)) {
      tinymce.get(this.id).save();
      tinymce.get(this.id).remove();
    }
    if (window.QTags && QTags.instances && QTags.instances[this.id]) {
      delete QTags.instances[this.id];
    }
  });
}

function syncRichEditors() {
  $('.ah-rich-editor').each(function(){
    if (window.tinymce && tinymce.get(this.id)) {
      tinymce.get(this.id).save();
    }
    syncField($(this));
  });
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
  initRichEditors();
  $new[0].scrollIntoView({behavior:'smooth', block:'center'});
});

// Toggle block expand/collapse
$(document).on('click', '.ah-block-header', function(e){
  if ($(e.target).is('.ah-block-handle, .ah-delete-block')) return;
  var $block = $(this).closest('.ah-canvas-block');
  $block.toggleClass('ah-block-active');
  if ($block.hasClass('ah-block-active')) {
    initRichEditors();
  }
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
  syncRichEditors();
  // Sync any remaining regular/repeater field state
  $('#ah-canvas .ah-block-body input, #ah-canvas .ah-block-body textarea, #ah-canvas .ah-block-body select').each(function(){ syncField($(this)); });
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

// ── Layout / CTA toggles ──────────────────────────────────────────────────────
$('#ahb_cta_enabled').on('change', function(){
  $('#ahb-cta-fields').toggle( this.checked );
});

})(jQuery);
</script>

<?php endif; // builder vs list ?>
</div><!-- /wrap -->
