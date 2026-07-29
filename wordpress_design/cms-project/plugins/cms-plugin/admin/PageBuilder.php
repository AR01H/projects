<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

global $wpdb;
$table   = $wpdb->prefix . 'ah_builder_pages';
$notice  = '';
$action  = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id = (int) ( $_GET['id'] ?? 0 );
$content_tax_m = new AH_Content_Taxonomy_Model();

// ── Template presets ──
function ah_builder_templates(): array {
	return AH_Page_Builder_Helper::templates();
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
        <div style="background:var(--ah-primary,var(--ah-primary));color:#fff;padding:20px 24px;">
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


<form id="ah-builder-form" method="post">
  <?php wp_nonce_field( 'ah_builder_save', 'ah_builder_nonce' ); ?>
  <input type="hidden" name="blocks_json" id="blocks-json" value="">

  <!-- Top Bar -->
  <div class="ah-builder-topbar">
    <div class="ah-builder-topbar__group">
      <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-page-builder' ) ); ?>"
         class="ah-builder-topbar__back" title="Back to pages">←</a>
      <input type="text" name="page_title" id="page-title"
             value="<?php echo esc_attr( $current_page->title ?? '' ); ?>"
             placeholder="Page Title…" required>
    </div>
    <div class="ah-builder-topbar__group">
      <?php if ( $current_page ) : ?>
        <a href="<?php echo esc_url( home_url( '/' . esc_attr( $current_page->slug ) . '/' ) ); ?>"
           target="_blank" class="ah-btn ah-btn-secondary">👁 Preview</a>
      <?php endif; ?>
      <select name="page_status">
        <option value="draft" <?php selected( $current_page->status ?? 'draft', 'draft' ); ?>>Draft</option>
        <option value="active" <?php selected( $current_page->status ?? '', 'active' ); ?>>Published</option>
      </select>
      <button type="submit" class="ah-btn ah-btn-primary">💾 Save Page</button>
    </div>
  </div>

  <!-- Builder Grid -->
  <div class="ah-builder-wrap">

    <!-- LEFT: Block Palette -->
    <div class="ah-palette" id="ah-palette">

      <?php /* No name attr: this filter box must never be submitted with the form. */ ?>
      <div class="ah-palette__search">
        <input type="search" id="ah-block-search" placeholder="Search blocks…" autocomplete="off">
      </div>

      <div class="ah-palette__list">
        <div class="ah-palette__group">
          <button type="button" class="ah-palette__cat"><span>Layout</span><span class="ah-palette__cat-chev" aria-hidden="true">▾</span></button>
          <div class="ah-palette-block" data-cat="layout" data-type="hero">          <span class="icon">🎯</span> Hero Banner</div>
          <div class="ah-palette-block" data-cat="layout" data-type="section_heading"><span class="icon">📌</span> Section Heading</div>
          <div class="ah-palette-block" data-cat="layout" data-type="text_block">    <span class="icon">📝</span> Rich Text</div>
          <div class="ah-palette-block" data-cat="layout" data-type="columns">       <span class="icon">⬛</span> 2-Col Text</div>
          <div class="ah-palette-block" data-cat="layout" data-type="tabs">          <span class="icon">🗂️</span> Tabs</div>
          <div class="ah-palette-block" data-cat="layout" data-type="divider">       <span class="icon">➖</span> Divider</div>
          <div class="ah-palette-block" data-cat="layout" data-type="spacer">        <span class="icon">↕️</span> Spacer</div>
        </div>

        <div class="ah-palette__group">
          <button type="button" class="ah-palette__cat"><span>Media</span><span class="ah-palette__cat-chev" aria-hidden="true">▾</span></button>
          <div class="ah-palette-block" data-cat="media" data-type="gallery">       <span class="icon">🖼️</span> Gallery</div>
          <div class="ah-palette-block" data-cat="media" data-type="video">         <span class="icon">▶️</span> Video Embed</div>
          <div class="ah-palette-block" data-cat="media" data-type="map_embed">     <span class="icon">📍</span> Map Embed</div>
          <div class="ah-palette-block" data-cat="media" data-type="logo_strip">    <span class="icon">🏷️</span> Logo Strip</div>
        </div>

        <div class="ah-palette__group">
          <button type="button" class="ah-palette__cat"><span>Content</span><span class="ah-palette__cat-chev" aria-hidden="true">▾</span></button>
          <div class="ah-palette-block" data-cat="content" data-type="cards">         <span class="icon">🃏</span> Card Grid</div>
          <div class="ah-palette-block" data-cat="content" data-type="image_text">    <span class="icon">🖼️</span> Image + Text</div>
          <div class="ah-palette-block" data-cat="content" data-type="testimonial">   <span class="icon">💬</span> Testimonial</div>
          <div class="ah-palette-block" data-cat="content" data-type="steps">         <span class="icon">🔢</span> Steps / Process</div>
          <div class="ah-palette-block" data-cat="content" data-type="timeline">      <span class="icon">📅</span> Timeline</div>
          <div class="ah-palette-block" data-cat="content" data-type="icon_list">     <span class="icon">✅</span> Icon List</div>
          <div class="ah-palette-block" data-cat="content" data-type="pull_quote">    <span class="icon">❝</span> Pull Quote</div>
          <div class="ah-palette-block" data-cat="content" data-type="comparison">    <span class="icon">⚖️</span> Comparison Table</div>
          <div class="ah-palette-block" data-cat="content" data-type="pricing">       <span class="icon">💰</span> Pricing Card</div>
        </div>

        <div class="ah-palette__group">
          <button type="button" class="ah-palette__cat"><span>Action</span><span class="ah-palette__cat-chev" aria-hidden="true">▾</span></button>
          <div class="ah-palette-block" data-cat="action" data-type="cta_banner">    <span class="icon">📣</span> CTA Banner</div>
          <div class="ah-palette-block" data-cat="action" data-type="stats_row">     <span class="icon">📊</span> Stats Row</div>
          <div class="ah-palette-block" data-cat="action" data-type="faq">           <span class="icon">❓</span> FAQ Accordion</div>
          <div class="ah-palette-block" data-cat="action" data-type="alert">         <span class="icon">📢</span> Alert / Notice</div>
          <div class="ah-palette-block" data-cat="action" data-type="notice_bar">    <span class="icon">📯</span> Notice Bar</div>
          <div class="ah-palette-block" data-cat="action" data-type="download">      <span class="icon">⬇️</span> Download Button</div>
        </div>

        <div class="ah-palette__group">
          <button type="button" class="ah-palette__cat"><span>People &amp; Contact</span><span class="ah-palette__cat-chev" aria-hidden="true">▾</span></button>
          <div class="ah-palette-block" data-cat="people" data-type="contact_card">  <span class="icon">📇</span> Contact Card</div>
        </div>

        <div class="ah-palette__group">
          <button type="button" class="ah-palette__cat"><span>Navigation</span><span class="ah-palette__cat-chev" aria-hidden="true">▾</span></button>
          <div class="ah-palette-block" data-cat="nav" data-type="button_row">    <span class="icon">🔘</span> Button Row</div>
          <div class="ah-palette-block" data-cat="nav" data-type="links_list">    <span class="icon">🔗</span> Links List</div>
        </div>

        <div class="ah-palette__empty">No blocks match that search.</div>
      </div>
    </div>

    <!-- MIDDLE: Canvas -->
    <div class="ah-canvas-wrap">
      <?php /* Block count + collapse-all: with a dozen expanded blocks the canvas
               becomes a very long scroll with no way to get an overview. */ ?>
      <div class="ah-canvas-toolbar" id="ah-canvas-toolbar">
        <span class="ah-canvas-count" id="ah-canvas-count">0 blocks</span>
        <button type="button" class="ah-canvas-collapse" id="ah-canvas-collapse">Collapse all</button>
      </div>
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

      <div class="ah-settings-section is-open">
        <button type="button" class="ah-settings-section__head">
          <span class="ah-settings-section__title">Page Settings</span>
          <span class="ah-settings-section__chevron">▾</span>
        </button>
        <div class="ah-settings-section__body">

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

        </div>
      </div>

      <div class="ah-settings-section is-open">
        <button type="button" class="ah-settings-section__head">
          <span class="ah-settings-section__title">Layout</span>
          <span class="ah-settings-section__chevron">▾</span>
        </button>
        <div class="ah-settings-section__body">

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

        </div>
      </div>

      <?php /* Collapsed by default: 8 fields behind its own enable toggle, and
               leaving it open pushed Page Settings and Layout off-screen. */ ?>
      <div class="ah-settings-section">
        <button type="button" class="ah-settings-section__head">
          <span class="ah-settings-section__title">Bottom CTA</span>
          <span class="ah-settings-section__chevron">▾</span>
        </button>
        <div class="ah-settings-section__body">

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

        </div>
      </div>

      <?php if ( $current_page ) : ?>
        <div class="ah-settings-section ah-settings-section--danger">
          <button type="button" class="ah-settings-section__head">
            <span class="ah-settings-section__title">Danger Zone</span>
            <span class="ah-settings-section__chevron">▾</span>
          </button>
          <div class="ah-settings-section__body">
            <button type="submit" form="ah-builder-delete-form" name="delete_page" value="1" class="ah-confirm-delete" data-title="Delete Page" data-confirm="This page and all its content will be permanently deleted."
                    style="width:100%;background:var(--ah-bg-light);color:var(--ah-danger);border:1px solid var(--ah-border);border-radius:6px;padding:8px;cursor:pointer;font-size:.82rem;font-weight:600">
              🗑 Delete Page
            </button>
          </div>
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
/* Block type -> palette category. Emitted as data-cat on each canvas block so
   the stylesheet can colour-code blocks by family. Colour lives in CSS, never
   here: the theme design layer owns appearance. */
var BLOCK_CATS = {
  hero:'layout', section_heading:'layout', text_block:'layout', columns:'layout',
  tabs:'layout', divider:'layout', spacer:'layout',
  gallery:'media', video:'media', map_embed:'media', logo_strip:'media',
  cards:'content', image_text:'content', testimonial:'content', steps:'content',
  timeline:'content', icon_list:'content', pull_quote:'content', comparison:'content', pricing:'content',
  cta_banner:'action', stats_row:'action', faq:'action', alert:'action',
  notice_bar:'action', download:'action',
  contact_card:'people',
  button_row:'nav', links_list:'nav'
};

var BLOCK_DEFS = {
  hero: {
    label: 'Hero Banner', icon: '🎯', color: 'var(--ah-primary)',
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
    label: 'Section Heading', icon: '📌', color: 'var(--ah-primary)',
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
    label: 'Spacer', icon: '↕️', color: 'var(--ah-muted)',
    fields: [
      { key:'height', label:'Height (px)', type:'text', ph:'40' },
    ]
  },
  cards: {
    label: 'Card Grid', icon: '🃏', color: 'var(--ah-primary)',
    fields: [
      { key:'heading',    label:'Section Heading', type:'text',   ph:'Our Features' },
      { key:'cols',       label:'Columns',         type:'select', options:['2','3','4'], def:'3' },
      { key:'bg',         label:'Background',      type:'select', options:['white','alt'], def:'white' },
      { key:'card_style', label:'Card Style',      type:'select', options:['feat','value','plain'], def:'feat' },
      { key:'source',       label:'Data Source', type:'select', options:['manual','latest_news','latest_posts'], def:'manual' },
      { key:'source_limit', label:'Items to Show (when not Manual)', type:'text', ph:'4' },
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
    label: 'CTA Banner', icon: '📣', color: 'var(--ah-warning)',
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
    label: 'Stats Row', icon: '📊', color: 'var(--ah-primary)',
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
    label: 'FAQ Accordion', icon: '❓', color: 'var(--ah-primary)',
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
    label: 'Button Row', icon: '🔘', color: 'var(--ah-primary)',
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
    label: 'Links List', icon: '🔗', color: 'var(--ah-primary)',
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
    label: 'Testimonial', icon: '💬', color: 'var(--ah-primary)',
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
    label: 'Steps / Process', icon: '🔢', color: 'var(--ah-primary)',
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
    label: 'Divider', icon: '➖', color: 'var(--ah-muted)',
    fields: [
      { key:'style', label:'Style',         type:'select', options:['line','ornament','dots'], def:'line' },
      { key:'label', label:'Optional Label',type:'text',   ph:'- or -' },
    ]
  },
  alert: {
    label: 'Alert / Notice', icon: '📢', color: 'var(--ah-warning)',
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
    label: 'Gallery', icon: '🖼️', color: 'var(--ah-primary)',
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
    label: 'Video Embed', icon: '▶️', color: 'var(--ah-danger)',
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
    label: 'Logo Strip', icon: '🏷️', color: 'var(--ah-muted)',
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
    label: 'Timeline', icon: '📅', color: 'var(--ah-primary)',
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
    label: 'Pricing Card', icon: '💰', color: 'var(--ah-warning)',
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
    label: 'Pull Quote', icon: '❝', color: 'var(--ah-warning)',
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
    label: 'Download Button', icon: '⬇️', color: 'var(--ah-primary)',
    fields: [
      { key:'label',    label:'Label',         type:'text',   ph:'First-Time Buyer Guide'         },
      { key:'url',      label:'File URL',      type:'text',   ph:'/wp-content/uploads/guide.pdf'  },
      { key:'filetype', label:'File Type',     type:'text',   ph:'PDF'                            },
      { key:'filesize', label:'File Size',     type:'text',   ph:'2.4 MB'                         },
      { key:'desc',     label:'Description',   type:'text',   ph:'Everything you need to know'    },
    ]
  },
  tabs: {
    label: 'Tabs', icon: '🗂️', color: 'var(--ah-primary)',
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
    label: 'Comparison Table', icon: '⚖️', color: 'var(--ah-primary)',
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
    label: 'Notice Bar', icon: '📯', color: 'var(--ah-warning)',
    fields: [
      { key:'text',  label:'Message',    type:'text',   ph:'Limited spaces available - book your free consultation today' },
      { key:'cta',   label:'CTA Text',   type:'text',   ph:'Book Now'    },
      { key:'url',   label:'CTA URL',    type:'text',   ph:'/contact/'   },
      { key:'style', label:'Style',      type:'select', options:['gold','dark','info'], def:'gold' },
    ]
  },
  contact_card: {
    label: 'Contact Card', icon: '📇', color: 'var(--ah-primary)',
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

  // Toolbar reflects the current canvas; hidden entirely when there's nothing yet.
  $('#ah-canvas-count').text(blocks.length + (blocks.length === 1 ? ' block' : ' blocks'));
  $('#ah-canvas-toolbar').toggleClass('is-hidden', blocks.length === 0);
}

function buildBlockHTML(block) {
  var def = BLOCK_DEFS[block.type];
  if (!def) return '';
  var id = block._id;
  var data = block.data || {};

  var html = '<div class="ah-canvas-block" data-block-id="'+id+'" data-type="'+block.type+'" data-cat="'+(BLOCK_CATS[block.type]||'')+'">';
  html += '<div class="ah-block-header">';
  html += '<span class="ah-block-handle" title="Drag to reorder">☰</span>';
  html += '<span style="font-size:.9rem;margin-right:4px">'+def.icon+'</span>';
  html += '<span class="ah-block-title">'+(data.heading||data.title||data.content||def.label).substring(0,40)+'</span>';
  html += '<span class="ah-block-type-badge">'+def.label+'</span>';
  html += '<div class="ah-block-actions">';
  html += '<button type="button" class="ah-toggle-block" title="Edit block">▼</button>';
  html += '<button type="button" class="ah-duplicate-block" title="Duplicate block">⧉</button>';
  html += '<button type="button" class="ah-delete-block" title="Delete block">✕</button>';
  html += '</div>';
  html += '</div>';

  html += '<div class="ah-block-body">';

  // Regular fields
  def.fields.forEach(function(f){
    var val = data[f.key] !== undefined ? data[f.key] : (f.def||'');
    // Type modifier lets CSS lay short controls out two-per-row while textareas
    // (and repeaters below) still span the full width.
    html += '<div class="ah-form-row ah-form-row--'+(f.type||'text')+'"><label>'+esc(f.label)+'</label>';
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
    html += '<div class="ah-form-row ah-form-row--'+(f.type||'text')+'"><label>'+esc(f.label)+'</label>';
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
    html += '<div class="ah-form-row ah-form-row--repeater"><label>'+esc(rep.label)+'</label>';
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
    html += '<div><label style="font-size:.72rem;color:var(--ah-muted)">'+esc(f.label)+'</label>';
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
/* ── Palette search ──────────────────────────────────────────────────────
   Filters by visible label AND data-type, so "cta" finds "CTA Banner" and
   "notice_bar" finds "Notice Bar". Category headings hide when every block
   inside them is filtered out. CSS owns the actual hiding via .is-filtered-out. */
var $palette      = $('#ah-palette');
var $paletteGroup = $palette.find('.ah-palette__group');
var $searchInput  = $('#ah-block-search');

/* Collapse-all / expand-all every block on the canvas. TinyMCE instances are
   re-initialised on expand because they only attach to active blocks. */
$(document).on('click', '#ah-canvas-collapse', function(){
  var $open = $('#ah-canvas .ah-canvas-block.ah-block-active');
  if ($open.length) {
    destroyRichEditors();
    $('#ah-canvas .ah-canvas-block').removeClass('ah-block-active');
    $(this).text('Expand all');
  } else {
    $('#ah-canvas .ah-canvas-block').addClass('ah-block-active');
    initRichEditors();
    $(this).text('Collapse all');
  }
});

/* Collapse a category. Purely visual - collapsed groups are still searchable,
   because searching adds .is-searching which overrides the collapsed state. */
$(document).on('click', '.ah-palette__cat', function(){
  $(this).closest('.ah-palette__group').toggleClass('is-collapsed');
});

$searchInput.on('input', function(){
  var q = $.trim(this.value).toLowerCase();
  $palette.toggleClass('is-searching', q !== '');
  if (!q) {
    $palette.removeClass('is-empty');
    $paletteGroup.removeClass('is-filtered-out').find('.ah-palette-block').removeClass('is-filtered-out');
    return;
  }
  var anyVisible = false;
  $paletteGroup.each(function(){
    var $group = $(this), groupHas = false;
    $group.find('.ah-palette-block').each(function(){
      var $b    = $(this);
      var hay   = ($b.text() + ' ' + ($b.data('type') || '')).toLowerCase();
      var match = hay.indexOf(q) !== -1;
      $b.toggleClass('is-filtered-out', !match);
      if (match) { groupHas = true; }
    });
    $group.toggleClass('is-filtered-out', !groupHas);
    if (groupHas) { anyVisible = true; }
  });
  $palette.toggleClass('is-empty', !anyVisible);
});

/* Enter inside the search box must filter, never submit/save the page. */
$searchInput.on('keydown', function(e){
  if (e.key === 'Enter' || e.which === 13) { e.preventDefault(); }
});

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
  $new.addClass('ah-block-active ah-block-just-added');
  setTimeout(function(){ $new.removeClass('ah-block-just-added'); }, 400);
  initRichEditors();
  $new[0].scrollIntoView({behavior:'smooth', block:'center'});
});

// Toggle block expand/collapse
$(document).on('click', '.ah-block-header', function(e){
  if ($(e.target).is('.ah-block-handle, .ah-delete-block, .ah-duplicate-block')) return;
  var $block = $(this).closest('.ah-canvas-block');
  $block.toggleClass('ah-block-active');
  if ($block.hasClass('ah-block-active')) {
    initRichEditors();
  }
});

// Duplicate block - deep-copies the block's data (repeater rows included) and
// inserts the copy directly below the original, so a configured block with many
// fields can be reused without re-entering everything.
$(document).on('click', '.ah-duplicate-block', function(e){
  e.stopPropagation();
  var bid = parseInt($(this).closest('.ah-canvas-block').data('block-id'));
  var idx = -1;
  for (var i = 0; i < blocks.length; i++) { if (blocks[i]._id === bid) { idx = i; break; } }
  if (idx === -1) return;

  var copy = JSON.parse(JSON.stringify(blocks[idx]));
  copy._id = ++blockIdCounter;
  blocks.splice(idx + 1, 0, copy);
  renderCanvas();

  var $new = $('#ah-canvas .ah-canvas-block[data-block-id="' + copy._id + '"]');
  $new.addClass('ah-block-just-added');
  setTimeout(function(){ $new.removeClass('ah-block-just-added'); }, 400);
  if ($new[0]) { $new[0].scrollIntoView({ behavior: 'smooth', block: 'center' }); }
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
/* hexToLight() removed: it only parsed 6-digit hex, but 24 of 29 block defs pass
   CSS custom-property strings like 'var(--ah-primary)', so they all silently fell
   back to the same flat grey badge. Badge colour is now data-cat driven in CSS. */

// ── Collapsible settings-panel sections ───────────────────────────────────────
$(document).on('click', '.ah-settings-section__head', function(){
  $(this).closest('.ah-settings-section').toggleClass('is-open');
});

// ── Init ─────────────────────────────────────────────────────────────────────
// Deferred to document-ready: jquery-ui-sortable (a dependency of the
// footer-loaded ah-admin-script) isn't available yet at the point this inline
// script block itself executes, so calling renderCanvas() (-> makeSortable())
// synchronously here would throw "$(...).sortable is not a function" whenever
// the page loads with existing blocks. By document-ready, footer scripts have
// already run.
$(function(){
  renderCanvas();
});

// ── Layout / CTA toggles ──────────────────────────────────────────────────────
$('#ahb_cta_enabled').on('change', function(){
  $('#ahb-cta-fields').toggle( this.checked );
});

})(jQuery);
</script>

<?php endif; // builder vs list ?>
</div><!-- /wrap -->
