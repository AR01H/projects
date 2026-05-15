<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$model   = new AH_Posts_Model();
$media_m = new AH_Media_Model();
$tax_m   = new AH_Taxonomy_Model();
$notice  = '';
$action  = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id = (int) ( $_GET['id'] ?? 0 );
$post_types = array( 'blog', 'article', 'news', 'newsletter', 'guide' );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	if ( ! wp_verify_nonce( $_POST['ah_posts_nonce'] ?? '', 'ah_save_post' ) ) wp_die( 'Security.' );

	$post_type = sanitize_key( $_POST['post_type'] ?? 'blog' );
	$data = array(
		'post_type'         => $post_type,
		'title'             => sanitize_text_field( $_POST['title'] ?? '' ),
		'slug'              => AH_Slug_Helper::generate_post( $_POST['slug'] ?: $_POST['title'], $post_type, $edit_id ),
		'excerpt'           => sanitize_textarea_field( $_POST['excerpt'] ?? '' ),
		'content'           => wp_kses_post( $_POST['content'] ?? '' ),
		'featured_image_id' => (int) ( $_POST['featured_image_id'] ?? 0 ) ?: null,
		'banner_image_id'   => (int) ( $_POST['banner_image_id'] ?? 0 ) ?: null,
		'author_id'         => get_current_user_id() ?: null,
		'status'            => sanitize_key( $_POST['status'] ?? 'draft' ),
		'is_featured'       => (int) ( $_POST['is_featured'] ?? 0 ),
		'published_at'      => $_POST['status'] === 'active' ? current_time( 'mysql' ) : null,
		'meta_title'        => sanitize_text_field( $_POST['meta_title'] ?? '' ),
		'meta_description'  => sanitize_textarea_field( $_POST['meta_description'] ?? '' ),
		'meta_keywords'     => sanitize_text_field( $_POST['meta_keywords'] ?? '' ),
	);

	if ( $edit_id ) {
		$model->update( $edit_id, $data );
		$saved_id = $edit_id;
	} else {
		$saved_id = $model->create( $data );
	}

	// Sync taxonomies
	$tax_ids = array_map( 'intval', $_POST['taxonomy_ids'] ?? array() );
	$model->sync_taxonomies( (int) $saved_id, $tax_ids );

	// Save links
	$links = $_POST['links'] ?? array();
	$model->save_links( (int) $saved_id, $links );

	$notice = 'Post saved.';
	$action = 'list';
}

if ( isset( $_GET['delete_id'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'ah_del_post' ) ) {
	$model->delete( (int) $_GET['delete_id'] );
	$notice = 'Post deleted.';
}
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-edit"></span> <?php esc_html_e( 'Posts / Blog', 'ah-theme' ); ?></h1>
  <?php if ( $notice ) : ?><div class="ah-notice ah-notice-success"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

  <?php if ( $action === 'list' ) :
    $search    = sanitize_text_field( $_GET['s'] ?? '' );
    $type_f    = sanitize_key( $_GET['post_type'] ?? '' );
    $status_f  = sanitize_key( $_GET['status'] ?? '' );
    $paged     = AH_Pagination::current_page();
    $result    = $model->get_paginated( $paged, array( 'search' => $search, 'post_type' => $type_f, 'status' => $status_f ) );
    $items     = $result['items']; $meta = $result['meta'];
  ?>
    <div class="ah-table-top">
      <form class="ah-search-form" method="get">
        <input type="hidden" name="page" value="ah-posts">
        <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search posts…">
        <select name="post_type">
          <option value="">All Types</option>
          <?php foreach ( $post_types as $pt ) : ?><option value="<?php echo $pt; ?>" <?php selected( $type_f, $pt ); ?>><?php echo ucfirst( $pt ); ?></option><?php endforeach; ?>
        </select>
        <select name="status">
          <option value="">All Status</option>
          <?php foreach ( array( 'active', 'draft', 'inactive', 'scheduled' ) as $s ) : ?><option value="<?php echo $s; ?>" <?php selected( $status_f, $s ); ?>><?php echo ucfirst( $s ); ?></option><?php endforeach; ?>
        </select>
        <button class="ah-btn ah-btn-secondary">Filter</button>
      </form>
      <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-posts', 'action' => 'add' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-primary">+ Add Post</a>
    </div>
    <div class="ah-table-wrap">
      <table class="ah-table">
        <thead><tr><th>Title</th><th>Type</th><th>Status</th><th>Views</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ( $items as $post ) : ?>
            <tr>
              <td><strong><?php echo esc_html( $post->title ); ?></strong><?php echo $post->is_featured ? ' <span class="ah-badge ah-badge-active" style="font-size:10px;">Featured</span>' : ''; ?></td>
              <td><?php echo esc_html( ucfirst( $post->post_type ) ); ?></td>
              <td><span class="ah-badge ah-badge-<?php echo esc_attr( $post->status ); ?>"><?php echo esc_html( $post->status ); ?></span></td>
              <td><?php echo number_format_i18n( $post->view_count ); ?></td>
              <td><small><?php echo esc_html( $post->created_at ? wp_date( 'M j, Y', strtotime( $post->created_at ) ) : '—' ); ?></small></td>
              <td class="row-actions">
                <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-posts', 'action' => 'edit', 'id' => $post->id ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>
                <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-posts', 'delete_id' => $post->id ), admin_url( 'admin.php' ) ), 'ah_del_post' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm" onclick="return confirm('Delete?');">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php echo AH_Pagination::render( $meta ); ?>

  <?php else :
    $item      = $edit_id ? $model->find( $edit_id ) : null;
    $item_tax  = $edit_id ? array_map( fn( $t ) => $t->id, $model->get_taxonomies( $edit_id ) ) : array();
    $item_links = $edit_id ? $model->get_links( $edit_id ) : array();
    $all_tax   = $tax_m->all( array( 'order_by' => 'name', 'order' => 'ASC' ) );
    $feat_url  = $item && $item->featured_image_id ? $media_m->get_url( (int) $item->featured_image_id ) : '';
  ?>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-posts' ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm" style="margin-bottom:14px;display:inline-flex;">&larr; Back</a>
    <form method="post">
      <?php wp_nonce_field( 'ah_save_post', 'ah_posts_nonce' ); ?>
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">

        <!-- Main content -->
        <div>
          <div class="ah-card">
            <div class="ah-form-row">
              <label>Title *</label>
              <input type="text" name="title" value="<?php echo esc_attr( $item->title ?? '' ); ?>" class="ah-generate-slug-source" data-slug-target="#post-slug" required style="font-size:16px;font-weight:600;">
            </div>
            <div class="ah-form-row"><label>Slug</label><input type="text" name="slug" id="post-slug" value="<?php echo esc_attr( $item->slug ?? '' ); ?>" class="ah-slug-field"></div>
            <div class="ah-form-row"><label>Excerpt</label><textarea name="excerpt" rows="3"><?php echo esc_textarea( $item->excerpt ?? '' ); ?></textarea></div>
            <div class="ah-form-row"><label>Content</label><?php wp_editor( $item->content ?? '', 'content', array( 'textarea_name' => 'content', 'editor_height' => 400 ) ); ?></div>
          </div>

          <!-- Links repeater -->
          <div class="ah-card">
            <div class="ah-card-header"><h2>Reference Links</h2></div>
            <div class="ah-repeater-container" id="links-container">
              <?php
              $links_to_show = $item_links ?: array( (object) array( 'id' => 0, 'label' => '', 'url' => '', 'link_type' => 'reference' ) );
              foreach ( $links_to_show as $i => $lnk ) : ?>
                <div class="ah-repeater-item" style="padding:12px;">
                  <span class="ah-repeater-handle ah-sort-handle">&#9776;</span>
                  <button type="button" class="ah-repeater-remove">✕</button>
                  <div style="display:grid;grid-template-columns:2fr 3fr 1fr;gap:8px;margin-top:8px;">
                    <input type="text" name="links[<?php echo $i; ?>][label]" value="<?php echo esc_attr( $lnk->label ?? '' ); ?>" placeholder="Label">
                    <input type="url" name="links[<?php echo $i; ?>][url]" value="<?php echo esc_attr( $lnk->url ?? '' ); ?>" placeholder="https://…">
                    <select name="links[<?php echo $i; ?>][link_type]">
                      <?php foreach ( array( 'official', 'reference', 'related', 'cta' ) as $lt ) : ?>
                        <option value="<?php echo $lt; ?>" <?php selected( $lnk->link_type ?? 'reference', $lt ); ?>><?php echo ucfirst( $lt ); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-add-repeater">+ Add Link</button>
          </div>

          <!-- SEO -->
          <div class="ah-card">
            <div class="ah-card-header"><h2>SEO</h2></div>
            <div class="ah-form-row"><label>Meta Title</label><input type="text" name="meta_title" value="<?php echo esc_attr( $item->meta_title ?? '' ); ?>"></div>
            <div class="ah-form-row"><label>Meta Description</label><textarea name="meta_description" rows="3"><?php echo esc_textarea( $item->meta_description ?? '' ); ?></textarea></div>
            <div class="ah-form-row"><label>Meta Keywords</label><input type="text" name="meta_keywords" value="<?php echo esc_attr( $item->meta_keywords ?? '' ); ?>"></div>
          </div>
        </div>

        <!-- Sidebar -->
        <div>
          <div class="ah-card">
            <div class="ah-card-header"><h2>Publish</h2></div>
            <div class="ah-form-row">
              <label>Status</label>
              <select name="status">
                <?php foreach ( array( 'draft', 'active', 'inactive', 'scheduled' ) as $s ) : ?><option value="<?php echo $s; ?>" <?php selected( $item->status ?? 'draft', $s ); ?>><?php echo ucfirst( $s ); ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="ah-form-row">
              <label>Post Type</label>
              <select name="post_type">
                <?php foreach ( $post_types as $pt ) : ?><option value="<?php echo $pt; ?>" <?php selected( $item->post_type ?? 'blog', $pt ); ?>><?php echo ucfirst( $pt ); ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="ah-form-row">
              <label>Featured</label>
              <select name="is_featured"><option value="0" <?php selected( $item->is_featured ?? 0, 0 ); ?>>No</option><option value="1" <?php selected( $item->is_featured ?? 0, 1 ); ?>>Yes</option></select>
            </div>
            <button type="submit" class="ah-btn ah-btn-primary" style="width:100%;justify-content:center;">Save Post</button>
          </div>

          <div class="ah-card">
            <div class="ah-card-header"><h2>Featured Image</h2></div>
            <div class="ah-image-picker">
              <img src="<?php echo esc_url( $feat_url ); ?>" class="ah-image-preview <?php echo $feat_url ? 'visible' : ''; ?>" alt="" style="width:100%;height:160px;">
              <div class="ah-image-picker-btns">
                <input type="hidden" class="ah-image-id" name="featured_image_id" value="<?php echo esc_attr( $item->featured_image_id ?? 0 ); ?>">
                <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-pick-image">Set Featured Image</button>
                <button type="button" class="ah-btn ah-btn-sm ah-remove-image" style="color:var(--ah-danger);">Remove</button>
              </div>
            </div>
          </div>

          <div class="ah-card">
            <div class="ah-card-header"><h2>Categories & Tags</h2></div>
            <div style="max-height:200px;overflow-y:auto;border:1px solid var(--ah-border);border-radius:6px;padding:8px;">
              <?php foreach ( $all_tax as $tx ) : ?>
                <label style="display:flex;align-items:center;gap:6px;padding:3px 0;font-weight:400;cursor:pointer;">
                  <input type="checkbox" name="taxonomy_ids[]" value="<?php echo esc_attr( $tx->id ); ?>" <?php checked( in_array( $tx->id, $item_tax, false ) ); ?>>
                  <?php echo esc_html( $tx->name ); ?> <small style="color:var(--ah-muted);">(<?php echo esc_html( $tx->slug ); ?>)</small>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </form>
  <?php endif; ?>
</div>
