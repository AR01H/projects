<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$notice   = '';
$n_type   = 'success';
$action   = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id  = (int) ( $_GET['id'] ?? 0 );
$content_tax_m = new AH_Content_Taxonomy_Model();

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ah_pages_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_pages_nonce'], 'ah_wp_page_save' ) ) wp_die( 'Security check failed.' );

	if ( isset( $_POST['trash_page'] ) && $edit_id ) {
		wp_trash_post( $edit_id );
		$content_tax_m->sync_terms( 'wp_page', $edit_id, array() );
		$notice = 'Page moved to trash.'; $action = 'list'; $edit_id = 0;
	} else {
		$title    = sanitize_text_field( $_POST['page_title'] ?? '' );
		$slug     = sanitize_title( $_POST['page_slug'] ?: $title );
		$status   = in_array( $_POST['page_status'] ?? 'draft', array( 'publish','draft','private','pending' ), true ) ? $_POST['page_status'] : 'draft';
		$parent   = (int) ( $_POST['page_parent'] ?? 0 );
		$template = sanitize_text_field( $_POST['page_template'] ?? '' );
		$thumb_id = (int) ( $_POST['featured_image_id'] ?? 0 );
		$excerpt  = sanitize_textarea_field( $_POST['page_excerpt'] ?? '' );
		$page_content_raw = isset( $_POST['page_content'] ) ? wp_unslash( $_POST['page_content'] ) : '';
		$page_content = ( current_user_can( 'unfiltered_html' ) || current_user_can( 'manage_options' ) ) ? $page_content_raw : wp_kses_post( $page_content_raw );
		$page_data = array( 'post_type' => 'page', 'post_title' => $title, 'post_content' => $page_content, 'post_name' => $slug, 'post_status' => $status, 'post_parent' => $parent, 'post_excerpt' => $excerpt, 'page_template' => $template );
		if ( $edit_id ) { $page_data['ID'] = $edit_id; $result = wp_update_post( $page_data, true ); }
		else { $result = wp_insert_post( $page_data, true ); }
		if ( is_wp_error( $result ) ) { $notice = 'Error: ' . $result->get_error_message(); $n_type = 'error'; }
		else {
			$saved_id = (int) $result;
			if ( $thumb_id ) set_post_thumbnail( $saved_id, $thumb_id );
			else delete_post_thumbnail( $saved_id );
			$content_tax_m->sync_terms( 'wp_page', $saved_id, $_POST['taxonomy_ids'] ?? array() );
			if ( ! $edit_id ) {
				flush_rewrite_rules( false ); // refresh routing so new slug is immediately accessible
			}
			$notice = $edit_id ? 'Page updated.' : 'Page created.';
			$action = 'list'; $edit_id = 0;
		}
	}
}

if ( isset( $_GET['trash_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_trash_page' ) ) {
	$trash_id = (int) $_GET['trash_id'];
	wp_trash_post( $trash_id );
	$content_tax_m->sync_terms( 'wp_page', $trash_id, array() );
	$notice = 'Page moved to trash.';
}

$all_templates  = array( '' => 'Default Template' ) + get_page_templates();
$parent_pages   = get_pages( array( 'sort_column' => 'post_title', 'post_status' => array( 'publish','draft','private' ) ) );
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-admin-page"></span> Pages Manager</h1>
  <?php if ( $notice ) : ?><div class="ah-notice ah-notice-<?php echo esc_attr( $n_type ); ?>"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

  <?php if ( $action === 'list' ) :
    $paged    = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
    $search   = sanitize_text_field( $_GET['s'] ?? '' );
    $status_f = sanitize_key( $_GET['status'] ?? '' );
    $q_args   = array( 'post_type' => 'page', 'post_status' => $status_f ?: array( 'publish','draft','private','pending' ), 'posts_per_page' => 20, 'paged' => $paged, 'orderby' => 'title', 'order' => 'ASC' );
    if ( $search ) $q_args['s'] = $search;
    $q = new WP_Query( $q_args );
    $pages = $q->posts; $total = $q->found_posts; $pages_count = (int) ceil( $total / 20 );
  ?>
    <div class="ah-table-top">
      <form class="ah-search-form" method="get">
        <input type="hidden" name="page" value="ah-pages">
        <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search pages…">
        <select name="status">
          <option value="">All Statuses</option>
          <?php foreach ( array( 'publish' => 'Published', 'draft' => 'Draft', 'private' => 'Private', 'pending' => 'Pending' ) as $sv => $sl ) : ?>
            <option value="<?php echo $sv; ?>" <?php selected( $status_f, $sv ); ?>><?php echo $sl; ?></option>
          <?php endforeach; ?>
        </select>
        <button class="ah-btn ah-btn-secondary">Filter</button>
      </form>
      <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-pages', 'action' => 'add' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-primary">+ New Page</a>
    </div>

    <div class="ah-table-wrap">
      <table class="ah-table">
        <thead><tr><th>Title</th><th>Slug</th><th>Taxonomies</th><th>Status</th><th>Template</th><th>Modified</th><th>Actions</th></tr></thead>
        <tbody>
          <?php if ( empty( $pages ) ) : ?><tr><td colspan="7" style="text-align:center;color:var(--ah-muted);padding:32px;">No pages found.</td></tr><?php endif; ?>
          <?php foreach ( $pages as $pg ) :
            $tpl      = get_page_template_slug( $pg->ID );
            $tpl_name = $tpl ? ( $all_templates[ $tpl ] ?? basename( $tpl ) ) : 'Default';
            $badge    = array( 'publish' => 'active', 'draft' => 'draft', 'private' => 'inactive', 'pending' => 'draft' );
            $label    = array( 'publish' => 'Published', 'draft' => 'Draft', 'private' => 'Private', 'pending' => 'Pending' );
          ?>
            <tr>
              <td><strong><?php echo esc_html( $pg->post_title ?: '(no title)' ); ?></strong>
                <?php if ( $pg->post_parent ) : ?><small style="color:var(--ah-muted);display:block;">Child of: <?php echo esc_html( get_the_title( $pg->post_parent ) ); ?></small><?php endif; ?>
              </td>
              <td><code><?php echo esc_html( $pg->post_name ); ?></code></td>
              <td><?php $content_tax_m->render_badges( 'wp_page', (int) $pg->ID ); ?></td>
              <td><span class="ah-badge ah-badge-<?php echo esc_attr( $badge[ $pg->post_status ] ?? 'draft' ); ?>"><?php echo esc_html( $label[ $pg->post_status ] ?? $pg->post_status ); ?></span></td>
              <td><small><?php echo esc_html( $tpl_name ); ?></small></td>
              <td><small><?php echo esc_html( wp_date( 'M j, Y', strtotime( $pg->post_modified ) ) ); ?></small></td>
              <td class="row-actions">
                <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-pages', 'action' => 'edit', 'id' => $pg->ID ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>
                <?php if ( $pg->post_status === 'publish' ) : ?><a href="<?php echo esc_url( get_permalink( $pg->ID ) ); ?>" target="_blank" class="ah-btn ah-btn-secondary ah-btn-sm">View</a><?php endif; ?>
                <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-pages', 'trash_id' => $pg->ID ), admin_url( 'admin.php' ) ), 'ah_trash_page' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm" onclick="return confirm('Move to trash?');">Trash</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php echo AH_Pagination::render( array( 'total' => $total, 'total_pages' => $pages_count, 'current_page' => $paged ) ); ?>

  <?php else :
    $wp_page   = $edit_id ? get_post( $edit_id ) : null;
    $thumb_id  = $wp_page ? (int) get_post_thumbnail_id( $wp_page->ID ) : 0;
    $thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'medium' ) : '';
    $cur_tpl   = $wp_page ? get_page_template_slug( $wp_page->ID ) : '';
    $cur_par   = $wp_page ? (int) $wp_page->post_parent : 0;
  ?>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-pages' ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm" style="margin-bottom:16px;display:inline-flex;">&larr; Back to Pages</a>

    <form method="post">
      <?php wp_nonce_field( 'ah_wp_page_save', 'ah_pages_nonce' ); ?>
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">
        <div>
          <div class="ah-card">
            <div class="ah-form-row">
              <label>Page Title *</label>
              <input type="text" name="page_title" value="<?php echo esc_attr( $wp_page->post_title ?? '' ); ?>" class="ah-generate-slug-source" data-slug-target="#ah-page-slug" required style="font-size:16px;font-weight:600;">
            </div>
            <div class="ah-form-row">
              <label>Slug (URL)</label>
              <div style="display:flex;align-items:center;gap:8px;">
                <span style="color:var(--ah-muted);font-size:12px;"><?php echo esc_html( trailingslashit( home_url() ) ); ?></span>
                <input type="text" name="page_slug" id="ah-page-slug" value="<?php echo esc_attr( $wp_page->post_name ?? '' ); ?>" class="ah-slug-field" style="flex:1;"
                     <?php if ( ! empty( $wp_page->post_name ) ) echo 'data-manual="1"'; ?>>
              <?php if ( ! empty( $wp_page->post_name ) ) : ?>
                <small style="color:var(--ah-muted);font-size:11px;display:block;margin-top:4px;">
                  Slug is locked - editing the title won't change it.
                  <a href="#" style="color:var(--ah-primary);" onclick="document.getElementById('ah-page-slug').removeAttribute('data-manual');jQuery('#ah-page-slug').data('manual',false);this.parentNode.remove();return false;">Unlock to regenerate</a>
                </small>
              <?php endif; ?>
              </div>
            </div>
            <div class="ah-form-row"><label>Excerpt</label><textarea name="page_excerpt" rows="2"><?php echo esc_textarea( $wp_page->post_excerpt ?? '' ); ?></textarea></div>
          </div>
          <div class="ah-card">
            <div class="ah-card-header"><h2>Page Content</h2></div>
            <p style="margin:0 0 10px;color:var(--ah-muted);font-size:13px;">Paste raw HTML, inline styles, scripts, and custom markup here.</p>
            <textarea name="page_content" id="page_content" rows="28" style="width:100%;min-height:420px;font-family:Consolas,Monaco,monospace;font-size:13px;line-height:1.6;resize:vertical;"><?php echo esc_textarea( $wp_page->post_content ?? '' ); ?></textarea>
          </div>
        </div>

        <div>
          <div class="ah-card">
            <div class="ah-card-header"><h2>Publish</h2></div>
            <div class="ah-form-row"><label>Status</label>
              <select name="page_status">
                <option value="publish" <?php selected( $wp_page->post_status ?? 'draft', 'publish' ); ?>>Published</option>
                <option value="draft"   <?php selected( $wp_page->post_status ?? 'draft', 'draft' ); ?>>Draft</option>
                <option value="private" <?php selected( $wp_page->post_status ?? '', 'private' ); ?>>Private</option>
                <option value="pending" <?php selected( $wp_page->post_status ?? '', 'pending' ); ?>>Pending Review</option>
              </select>
            </div>
            <?php if ( $wp_page && $wp_page->post_status === 'publish' ) : ?>
              <div class="ah-form-row"><a href="<?php echo esc_url( get_permalink( $wp_page->ID ) ); ?>" target="_blank" class="ah-btn ah-btn-secondary ah-btn-sm" style="width:100%;justify-content:center;">View Page</a></div>
            <?php endif; ?>
            <button type="submit" class="ah-btn ah-btn-primary" style="width:100%;justify-content:center;margin-top:8px;">
              <span class="dashicons dashicons-saved"></span> <?php echo $wp_page ? 'Update Page' : 'Publish Page'; ?>
            </button>
          </div>

          <div class="ah-card">
            <div class="ah-card-header"><h2>Page Attributes</h2></div>
            <div class="ah-form-row"><label>Parent Page</label>
              <select name="page_parent">
                <option value="0">(No Parent)</option>
                <?php foreach ( $parent_pages as $pp ) : if ( $pp->ID === $edit_id ) continue; ?>
                  <option value="<?php echo esc_attr( $pp->ID ); ?>" <?php selected( $cur_par, $pp->ID ); ?>><?php echo esc_html( $pp->post_title ); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="ah-form-row"><label>Template</label>
              <select name="page_template">
                <?php foreach ( $all_templates as $tf => $tl ) : ?>
                  <option value="<?php echo esc_attr( $tf ); ?>" <?php selected( $cur_tpl, $tf ); ?>><?php echo esc_html( $tl ); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="ah-card">
            <div class="ah-card-header"><h2>Featured Image</h2></div>
            <div class="ah-image-picker">
              <img src="<?php echo esc_url( $thumb_url ); ?>" class="ah-image-preview <?php echo $thumb_url ? 'visible' : ''; ?>" alt="" style="width:100%;aspect-ratio:16/9;height:auto;object-fit:cover;border-radius:6px;">
              <div class="ah-image-picker-btns" style="margin-top:8px;">
                <input type="hidden" class="ah-image-id" name="featured_image_id" value="<?php echo esc_attr( $thumb_id ); ?>">
                <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-pick-image">Set Image</button>
                <button type="button" class="ah-btn ah-btn-sm ah-remove-image" style="color:var(--ah-danger);">Remove</button>
              </div>
            </div>
          </div>

          <div class="ah-card">
            <div class="ah-card-header"><h2>Taxonomies</h2></div>
            <?php $content_tax_m->render_picker( 'wp_page', $edit_id ); ?>
          </div>

          <?php if ( $wp_page ) : ?>
            <div class="ah-card" style="border-color:var(--ah-danger);">
              <div class="ah-card-header"><h2 style="color:var(--ah-danger);">Danger Zone</h2></div>
              <button type="submit" name="trash_page" value="1" class="ah-btn ah-btn-danger" style="width:100%;justify-content:center;" onclick="return confirm('Move to trash?');">
                <span class="dashicons dashicons-trash"></span> Move to Trash
              </button>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </form>
  <?php endif; ?>
</div>
