<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$model  = new AH_Taxonomy_Model();
$notice = '';
$action = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id = (int) ( $_GET['id'] ?? 0 );
$type_id = (int) ( $_GET['type_id'] ?? 0 );

// POST: save taxonomy type
if ( isset( $_POST['save_type'] ) && wp_verify_nonce( $_POST['ah_tax_nonce'] ?? '', 'ah_save_taxonomy' ) ) {
	$type_data = array(
		'name'        => sanitize_text_field( $_POST['type_name'] ?? '' ),
		'slug'        => sanitize_title( $_POST['type_slug'] ?? $_POST['type_name'] ?? '' ),
		'description' => sanitize_textarea_field( $_POST['type_description'] ?? '' ),
	);
	$type_edit_id = (int) ( $_POST['type_edit_id'] ?? 0 );
	$type_edit_id ? $model->update_type( $type_edit_id, $type_data ) : $model->create_type( $type_data );
	$notice = 'Taxonomy type saved.';
}

// POST: save taxonomy term
if ( isset( $_POST['save_term'] ) && wp_verify_nonce( $_POST['ah_tax_nonce'] ?? '', 'ah_save_taxonomy' ) ) {
	$data = array(
		'type_id'         => (int) ( $_POST['type_id'] ?? 0 ),
		'parent_id'       => (int) ( $_POST['parent_id'] ?? 0 ) ?: null,
		'name'            => sanitize_text_field( $_POST['name'] ?? '' ),
		'slug'            => AH_Slug_Helper::generate( $_POST['slug'] ?: $_POST['name'], AH_DB_Helper::table( 'taxonomies' ), 'slug', $edit_id ),
		'description'     => sanitize_textarea_field( $_POST['description'] ?? '' ),
		'meta_title'      => sanitize_text_field( $_POST['meta_title'] ?? '' ),
		'meta_description'=> sanitize_textarea_field( $_POST['meta_description'] ?? '' ),
		'status'          => sanitize_key( $_POST['status'] ?? 'active' ),
		'sort_order'      => (int) ( $_POST['sort_order'] ?? 0 ),
	);
	$edit_id ? $model->update( $edit_id, $data ) : $model->create( $data );
	$notice = 'Taxonomy term saved.';
	$action = 'list';
}

// Delete term
if ( isset( $_GET['delete_id'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'ah_del_tax' ) ) {
	$model->delete( (int) $_GET['delete_id'] );
	$notice = 'Term deleted.';
}

$types  = $model->get_types();
$tab    = sanitize_key( $_GET['tab'] ?? 'terms' );
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-tag"></span> <?php esc_html_e( 'Categories & Tags', 'ah-theme' ); ?></h1>
  <?php if ( $notice ) : ?><div class="ah-notice ah-notice-success"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

  <div class="ah-tabs" style="margin-bottom:20px;">
    <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'terms' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-tab <?php echo $tab === 'terms' ? 'active' : ''; ?>">Terms</a>
    <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'types' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-tab <?php echo $tab === 'types' ? 'active' : ''; ?>">Taxonomy Types</a>
  </div>

  <?php if ( $tab === 'types' ) : ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
      <!-- List -->
      <div class="ah-table-wrap">
        <table class="ah-table">
          <thead><tr><th>Name</th><th>Slug</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ( $types as $t ) : ?>
              <tr>
                <td><?php echo esc_html( $t->name ); ?></td>
                <td><code><?php echo esc_html( $t->slug ); ?></code></td>
                <td>
                  <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'types', 'edit_type' => $t->id ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <!-- Form -->
      <?php
      $edit_type_id = (int) ( $_GET['edit_type'] ?? 0 );
      $edit_type    = $edit_type_id ? $model->get_type( $edit_type_id ) : null;
      ?>
      <div class="ah-card">
        <div class="ah-card-header"><h2><?php echo $edit_type ? 'Edit Type' : 'Add Type'; ?></h2></div>
        <form method="post">
          <?php wp_nonce_field( 'ah_save_taxonomy', 'ah_tax_nonce' ); ?>
          <input type="hidden" name="type_edit_id" value="<?php echo esc_attr( $edit_type_id ); ?>">
          <div class="ah-form-row"><label>Name *</label><input type="text" name="type_name" value="<?php echo esc_attr( $edit_type->name ?? '' ); ?>" required></div>
          <div class="ah-form-row"><label>Slug</label><input type="text" name="type_slug" value="<?php echo esc_attr( $edit_type->slug ?? '' ); ?>"></div>
          <div class="ah-form-row"><label>Description</label><textarea name="type_description" rows="3"><?php echo esc_textarea( $edit_type->description ?? '' ); ?></textarea></div>
          <button type="submit" name="save_type" value="1" class="ah-btn ah-btn-primary">Save Type</button>
        </form>
      </div>
    </div>

  <?php else : /* Terms tab */
    $paged   = AH_Pagination::current_page();
    $search  = sanitize_text_field( $_GET['s'] ?? '' );
    $result  = $model->get_paginated( $paged, $search, $type_id ?: null );
    $items   = $result['items']; $meta = $result['meta'];
    $item    = $edit_id ? $model->find( $edit_id ) : null;
    $parents = $type_id ? $model->get_by_type( $type_id ) : array();
  ?>
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">
      <!-- Terms list -->
      <div>
        <div class="ah-table-top">
          <form class="ah-search-form" method="get">
            <input type="hidden" name="page" value="ah-taxonomy">
            <input type="hidden" name="tab" value="terms">
            <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search terms…">
            <select name="type_id">
              <option value="">All Types</option>
              <?php foreach ( $types as $t ) : ?><option value="<?php echo esc_attr( $t->id ); ?>" <?php selected( $type_id, $t->id ); ?>><?php echo esc_html( $t->name ); ?></option><?php endforeach; ?>
            </select>
            <button class="ah-btn ah-btn-secondary">Filter</button>
          </form>
        </div>
        <div class="ah-table-wrap">
          <table class="ah-table">
            <thead><tr><th>Name</th><th>Slug</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ( $items as $term ) :
                $t_type = null;
                foreach ( $types as $tt ) { if ( $tt->id == $term->type_id ) { $t_type = $tt; break; } }
              ?>
                <tr>
                  <td><?php echo esc_html( $term->name ); ?><?php if ( $term->parent_id ) echo ' <small style="color:var(--ah-muted);">↳ child</small>'; ?></td>
                  <td><code><?php echo esc_html( $term->slug ); ?></code></td>
                  <td><?php echo esc_html( $t_type->name ?? '—' ); ?></td>
                  <td><span class="ah-badge ah-badge-<?php echo esc_attr( $term->status ); ?>"><?php echo esc_html( $term->status ); ?></span></td>
                  <td class="row-actions">
                    <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'terms', 'action' => 'edit', 'id' => $term->id ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>
                    <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'delete_id' => $term->id ), admin_url( 'admin.php' ) ), 'ah_del_tax' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm" onclick="return confirm('Delete?');">Delete</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php echo AH_Pagination::render( $meta ); ?>
      </div>

      <!-- Add/Edit form -->
      <div class="ah-card">
        <div class="ah-card-header"><h2><?php echo $item ? 'Edit Term' : 'Add Term'; ?></h2></div>
        <form method="post">
          <?php wp_nonce_field( 'ah_save_taxonomy', 'ah_tax_nonce' ); ?>
          <div class="ah-form-row">
            <label>Type *</label>
            <select name="type_id" id="term-type" required>
              <option value="">— Select Type —</option>
              <?php foreach ( $types as $t ) : ?><option value="<?php echo esc_attr( $t->id ); ?>" <?php selected( $item->type_id ?? $type_id, $t->id ); ?>><?php echo esc_html( $t->name ); ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="ah-form-row"><label>Name *</label><input type="text" name="name" value="<?php echo esc_attr( $item->name ?? '' ); ?>" class="ah-generate-slug-source" data-slug-target="#term-slug" required></div>
          <div class="ah-form-row"><label>Slug</label><input type="text" name="slug" id="term-slug" value="<?php echo esc_attr( $item->slug ?? '' ); ?>" class="ah-slug-field"></div>
          <div class="ah-form-row"><label>Parent (for sub-categories)</label>
            <select name="parent_id">
              <option value="">— None —</option>
              <?php foreach ( $parents as $par ) : ?><option value="<?php echo esc_attr( $par->id ); ?>" <?php selected( $item->parent_id ?? 0, $par->id ); ?>><?php echo esc_html( $par->name ); ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="ah-form-row"><label>Description</label><textarea name="description" rows="3"><?php echo esc_textarea( $item->description ?? '' ); ?></textarea></div>
          <div class="ah-form-row"><label>Sort Order</label><input type="number" name="sort_order" value="<?php echo esc_attr( $item->sort_order ?? 0 ); ?>"></div>
          <div class="ah-form-row"><label>Status</label><select name="status"><option value="active" <?php selected( $item->status ?? 'active', 'active' ); ?>>Active</option><option value="inactive" <?php selected( $item->status ?? '', 'inactive' ); ?>>Inactive</option></select></div>
          <button type="submit" name="save_term" value="1" class="ah-btn ah-btn-primary">Save Term</button>
          <?php if ( $item ) : ?><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'terms' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary" style="margin-left:8px;">Cancel</a><?php endif; ?>
        </form>
      </div>
    </div>
  <?php endif; ?>
</div>
