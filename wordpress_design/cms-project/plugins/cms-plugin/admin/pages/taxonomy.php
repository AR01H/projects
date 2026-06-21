<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$model    = new AH_Taxonomy_Model();
$pt_model = new AH_Taxonomy_Parent_Model();
$notice   = '';
$action   = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id  = (int) ( $_GET['id'] ?? 0 );
$type_id  = (int) ( $_GET['type_id'] ?? 0 );

// ── POST: save parent term (separate table) ───────────────────────────────────
if ( isset( $_POST['save_parent_term'] ) && wp_verify_nonce( $_POST['ah_pt_nonce'] ?? '', 'ah_save_parent_term' ) ) {
	$pt_save_id = (int) ( $_GET['id'] ?? 0 );
	$pt_data    = array(
		'name'        => sanitize_text_field( $_POST['pt_name'] ?? '' ),
		'slug'        => AH_Slug_Helper::generate( $_POST['pt_slug'] ?: $_POST['pt_name'], AH_DB_Helper::table( 'taxonomy_parent_terms' ), 'slug', $pt_save_id ),
		'description' => sanitize_textarea_field( $_POST['pt_description'] ?? '' ),
		'color'       => sanitize_hex_color( $_POST['pt_color'] ?? '' ) ?: null,
		'icon_emoji'  => sanitize_text_field( $_POST['pt_icon_emoji'] ?? '' ) ?: null,
		'image_id'    => (int) ( $_POST['pt_image_id'] ?? 0 ) ?: null,
		'status'      => sanitize_key( $_POST['pt_status'] ?? 'active' ),
		'sort_order'  => (int) ( $_POST['pt_sort_order'] ?? 0 ),
	);
	$pt_save_id ? $pt_model->update( $pt_save_id, $pt_data ) : $pt_model->create( $pt_data );
	AH_Admin_Bootstrap::redirect( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'parent-terms', 'saved' => 1 ), admin_url( 'admin.php' ) ) );
}

// ── GET: delete parent term ───────────────────────────────────────────────────
if ( isset( $_GET['delete_pt_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_del_pt' ) ) {
	$pt_model->delete( (int) $_GET['delete_pt_id'] );
	AH_Admin_Bootstrap::redirect( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'parent-terms', 'deleted' => 1 ), admin_url( 'admin.php' ) ) );
}

if ( isset( $_GET['saved'] )   && sanitize_key( $_GET['tab'] ?? '' ) === 'parent-terms' ) $notice = 'Parent term saved.';
if ( isset( $_GET['deleted'] ) && sanitize_key( $_GET['tab'] ?? '' ) === 'parent-terms' ) $notice = 'Parent term deleted.';

// POST: save taxonomy type
if ( isset( $_POST['save_type'] ) && wp_verify_nonce( $_POST['ah_tax_nonce'] ?? '', 'ah_save_taxonomy' ) ) {
	$type_edit_id   = (int) ( $_POST['type_edit_id'] ?? 0 );
	$guarded_type   = $type_edit_id ? $model->get_type( $type_edit_id ) : null;
	if ( $guarded_type && $guarded_type->slug === 'data-protected' ) {
		$notice = 'This taxonomy type is protected and cannot be edited.';
	} else {
		$type_data = array(
			'name'        => sanitize_text_field( $_POST['type_name'] ?? '' ),
			'slug'        => sanitize_title( $_POST['type_slug'] ?? $_POST['type_name'] ?? '' ),
			'description' => sanitize_textarea_field( $_POST['type_description'] ?? '' ),
		);
		$type_edit_id ? $model->update_type( $type_edit_id, $type_data ) : $model->create_type( $type_data );
		$notice = 'Taxonomy type saved.';
	}
}

// POST: save taxonomy term
if ( isset( $_POST['save_term'] ) && wp_verify_nonce( $_POST['ah_tax_nonce'] ?? '', 'ah_save_taxonomy' ) ) {
	$guarded_term = $edit_id ? $model->find( $edit_id ) : null;
	if ( $guarded_term && ! empty( $guarded_term->is_protected ) ) {
		$notice = 'This term is protected and cannot be edited.';
	} else {
		$data = array(
			'type_id'         => (int) ( $_POST['type_id'] ?? 0 ),
			'parent_id'       => null,
			'parent_term_id'  => (int) ( $_POST['parent_term_id'] ?? 0 ) ?: null,
			'name'            => sanitize_text_field( $_POST['name'] ?? '' ),
			'slug'            => AH_Slug_Helper::generate( $_POST['slug'] ?: $_POST['name'], AH_DB_Helper::table( 'taxonomies' ), 'slug', $edit_id ),
			'description'     => sanitize_textarea_field( $_POST['description'] ?? '' ),
			'meta_title'      => sanitize_text_field( $_POST['meta_title'] ?? '' ),
			'meta_description'=> sanitize_textarea_field( $_POST['meta_description'] ?? '' ),
			'status'          => sanitize_key( $_POST['status'] ?? 'active' ),
			'sort_order'      => (int) ( $_POST['sort_order'] ?? 0 ),
			'image_id'        => (int) ( $_POST['image_id'] ?? 0 ) ?: null,
			'icon_emoji'      => sanitize_text_field( $_POST['icon_emoji'] ?? '' ) ?: null,
		);
		$edit_id ? $model->update( $edit_id, $data ) : $model->create( $data );
		$notice = 'Taxonomy term saved.';
		$action = 'list';
	}
}

// Delete term
if ( isset( $_GET['delete_id'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'ah_del_tax' ) ) {
	$del_row = $model->find( (int) $_GET['delete_id'] );
	if ( $del_row && ! empty( $del_row->is_protected ) ) {
		$notice = 'This term is protected and cannot be deleted.';
	} else {
		$model->delete( (int) $_GET['delete_id'] );
		$notice = 'Term deleted.';
	}
}

$types  = $model->get_types();
$tab    = sanitize_key( $_GET['tab'] ?? 'terms' );
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-tag"></span> <?php esc_html_e( 'Taxonomies', 'ah-theme' ); ?></h1>
  <?php if ( $notice ) : ?><div class="ah-notice ah-notice-success"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

  <div class="ah-tabs" style="margin-bottom:20px;">
    <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'terms' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-tab <?php echo $tab === 'terms' ? 'active' : ''; ?>">Terms</a>
    <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'parent-terms' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-tab <?php echo $tab === 'parent-terms' ? 'active' : ''; ?>">Parent Terms</a>
    <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'types' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-tab <?php echo $tab === 'types' ? 'active' : ''; ?>">Taxonomy Types</a>
  </div>

  <?php if ( $tab === 'parent-terms' ) :
    $pt_items = $pt_model->get_all();
    $pt_item  = $edit_id ? $pt_model->find( $edit_id ) : null;
  ?>
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">

      <!-- Parent Terms list -->
      <div>
        <div class="ah-table-wrap">
          <table class="ah-table">
            <thead>
              <tr><th style="width:36px;"></th><th>Name</th><th>Description</th><th>Children</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php if ( empty( $pt_items ) ) : ?>
                <tr><td colspan="6" style="text-align:center;opacity:.5;padding:24px;">No parent terms yet - use the form to add one.</td></tr>
              <?php endif; ?>
              <?php foreach ( $pt_items as $pt ) :
                $child_count = $pt_model->count_children( (int) $pt->id );
                $dot_color   = ! empty( $pt->color ) ? $pt->color : '#94a3b8';
              ?>
                <tr>
                  <td style="text-align:center;">
                    <span style="display:inline-block;width:18px;height:18px;border-radius:50%;background:<?php echo esc_attr( $dot_color ); ?>;vertical-align:middle;"
                          title="<?php echo esc_attr( $pt->color ?? 'no colour' ); ?>"></span>
                  </td>
                  <td>
                    <strong><?php echo esc_html( ( $pt->icon_emoji ? $pt->icon_emoji . ' ' : '' ) . $pt->name ); ?></strong>
                    <small style="color:var(--ah-muted);display:block;"><code><?php echo esc_html( $pt->slug ); ?></code></small>
                  </td>
                  <td><small style="color:var(--ah-muted);"><?php echo esc_html( wp_trim_words( $pt->description ?? '', 10 ) ); ?></small></td>
                  <td>
                    <?php if ( $child_count > 0 ) : ?>
                      <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'terms' ), admin_url( 'admin.php' ) ) ); ?>"
                         style="font-size:.82rem;"><?php echo $child_count; ?> term<?php echo $child_count !== 1 ? 's' : ''; ?> →</a>
                    <?php else : ?>
                      <small style="opacity:.4;">-</small>
                    <?php endif; ?>
                  </td>
                  <td><span class="ah-badge ah-badge-<?php echo esc_attr( $pt->status ); ?>"><?php echo esc_html( $pt->status ); ?></span></td>
                  <td class="row-actions">
                    <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'parent-terms', 'action' => 'edit', 'id' => $pt->id ), admin_url( 'admin.php' ) ) ); ?>"
                       class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>
                    <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'parent-terms', 'delete_pt_id' => $pt->id ), admin_url( 'admin.php' ) ), 'ah_del_pt' ) ); ?>"
                       class="ah-btn ah-btn-danger ah-btn-sm"
                       onclick="return confirm('Delete this parent term? Its child terms will lose their group assignment.');">Delete</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Add / Edit form -->
      <div class="ah-card">
        <div class="ah-card-header">
          <h2><?php echo $pt_item ? 'Edit Parent Term' : 'Add Parent Term'; ?></h2>
        </div>
        <?php
          $pt_form_args = array( 'page' => 'ah-taxonomy', 'tab' => 'parent-terms' );
          if ( $pt_item ) $pt_form_args['id'] = $edit_id;
        ?>
        <form method="post" action="<?php echo esc_url( add_query_arg( $pt_form_args, admin_url( 'admin.php' ) ) ); ?>">
          <?php wp_nonce_field( 'ah_save_parent_term', 'ah_pt_nonce' ); ?>

          <div class="ah-form-row">
            <label>Name *</label>
            <input type="text" name="pt_name" value="<?php echo esc_attr( $pt_item->name ?? '' ); ?>"
                   class="ah-generate-slug-source" data-slug-target="#pt-slug" required>
          </div>
          <div class="ah-form-row">
            <label>Slug</label>
            <input type="text" name="pt_slug" id="pt-slug" value="<?php echo esc_attr( $pt_item->slug ?? '' ); ?>" class="ah-slug-field">
          </div>
          <div class="ah-form-row">
            <label>Description</label>
            <textarea name="pt_description" rows="3"><?php echo esc_textarea( $pt_item->description ?? '' ); ?></textarea>
          </div>

          <div class="ah-form-row">
            <label>Colour</label>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
              <input type="color" name="pt_color" value="<?php echo esc_attr( $pt_item->color ?? '#1e40af' ); ?>"
                     style="width:44px;height:36px;padding:2px;border-radius:6px;border:1px solid #ddd;cursor:pointer;">
              <div style="display:flex;flex-wrap:wrap;gap:5px;">
                <?php foreach ( [ '#1e40af','#15803d','#b45309','#9333ea','#dc2626','#0891b2','#be185d','#374151' ] as $c ) : ?>
                  <span onclick="this.closest('form').querySelector('[name=pt_color]').value='<?php echo esc_js( $c ); ?>'"
                        style="width:22px;height:22px;border-radius:4px;background:<?php echo esc_attr( $c ); ?>;cursor:pointer;border:2px solid transparent;"
                        title="<?php echo esc_attr( $c ); ?>"></span>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <div class="ah-form-row">
            <label>Icon (emoji)</label>
            <input type="text" name="pt_icon_emoji" value="<?php echo esc_attr( $pt_item->icon_emoji ?? '' ); ?>"
                   placeholder="🏠" style="width:70px;font-size:1.4rem;text-align:center;">
          </div>

          <div class="ah-form-row">
            <label>Image</label>
            <?php
              $pt_img_id  = (int) ( $pt_item->image_id ?? 0 );
              $pt_img_url = $pt_img_id ? ( wp_get_attachment_image_url( $pt_img_id, 'medium' ) ?: '' ) : '';
            ?>
            <div class="ah-image-picker">
              <img src="<?php echo esc_url( $pt_img_url ); ?>"
                   class="ah-image-preview <?php echo $pt_img_url ? 'visible' : ''; ?>"
                   alt="" style="aspect-ratio:16/9;height:auto;object-fit:cover;width:100%;border-radius:6px;">
              <div class="ah-image-picker-btns" style="margin-top:8px;">
                <input type="hidden" class="ah-image-id" name="pt_image_id" value="<?php echo esc_attr( $pt_img_id ); ?>">
                <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-pick-image">Set Image</button>
                <button type="button" class="ah-btn ah-btn-sm ah-remove-image" style="color:var(--ah-danger);">Remove</button>
              </div>
            </div>
          </div>

          <div class="ah-form-row">
            <label>Sort Order</label>
            <input type="number" name="pt_sort_order" value="<?php echo esc_attr( $pt_item->sort_order ?? 0 ); ?>">
          </div>
          <div class="ah-form-row">
            <label>Status</label>
            <select name="pt_status">
              <option value="active"   <?php selected( $pt_item->status ?? 'active', 'active' ); ?>>Active</option>
              <option value="inactive" <?php selected( $pt_item->status ?? '', 'inactive' ); ?>>Inactive</option>
            </select>
          </div>

          <button type="submit" name="save_parent_term" value="1" class="ah-btn ah-btn-primary">
            <?php echo $pt_item ? 'Update Parent Term' : 'Add Parent Term'; ?>
          </button>
          <?php if ( $pt_item ) : ?>
            <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'parent-terms' ), admin_url( 'admin.php' ) ) ); ?>"
               class="ah-btn ah-btn-secondary" style="margin-left:8px;">Cancel</a>
          <?php endif; ?>
        </form>
      </div>

    </div>

  <?php elseif ( $tab === 'types' ) : ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
      <!-- List -->
      <div class="ah-table-wrap">
        <table class="ah-table">
          <thead><tr><th>Name</th><th>Slug</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ( $types as $t ) : ?>
              <tr>
                <td><?php echo esc_html( $t->name ); ?><?php if ( $t->slug === 'data-protected' ) echo ' <span title="System protected" style="cursor:default;">&#128274;</span>'; ?></td>
                <td><code><?php echo esc_html( $t->slug ); ?></code></td>
                <td>
                  <?php if ( $t->slug !== 'data-protected' ) : ?>
                    <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'types', 'edit_type' => $t->id ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>
                  <?php endif; ?>
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
    $paged      = AH_Pagination::current_page();
    $search     = sanitize_text_field( $_GET['s'] ?? '' );
    $status_f   = sanitize_key( $_GET['term_status'] ?? '' );
    $result     = $model->get_paginated( $paged, $search, $type_id ?: null );
    if ( $status_f && in_array( $status_f, array( 'active', 'inactive' ), true ) ) {
      $result['items'] = array_values( array_filter( $result['items'], static function( $t ) use ( $status_f ) {
        return isset( $t->status ) && $t->status === $status_f;
      } ) );
    }
    $items   = $result['items']; $meta = $result['meta'];
    $item    = $edit_id ? $model->find( $edit_id ) : null;
    $parents    = $pt_model->get_all_active();
    $parent_map = array(); // id => parent term object
    foreach ( $parents as $par ) { $parent_map[ (int) $par->id ] = $par; }
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
            <select name="term_status">
              <option value="">All Statuses</option>
              <option value="active"   <?php selected( $status_f, 'active' ); ?>>Active</option>
              <option value="inactive" <?php selected( $status_f, 'inactive' ); ?>>Inactive</option>
            </select>
            <button class="ah-btn ah-btn-secondary">Filter</button>
            <?php if ( $search || $type_id || $status_f ) : ?>
              <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'terms' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary" style="opacity:.7;">✕ Clear</a>
            <?php endif; ?>
          </form>
        </div>
        <div class="ah-table-wrap">
          <table class="ah-table">
            <thead><tr><th>Name</th><th>Slug</th><th>Type</th><th>Group</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ( $items as $term ) :
                $t_type    = null;
                foreach ( $types as $tt ) { if ( $tt->id == $term->type_id ) { $t_type = $tt; break; } }
                $pt_obj    = isset( $term->parent_term_id ) ? ( $parent_map[ (int) $term->parent_term_id ] ?? null ) : null;
              ?>
                <tr>
                  <td><?php echo esc_html( $term->name ); ?><?php if ( ! empty( $term->is_protected ) ) echo ' <span title="System protected" style="cursor:default;">&#128274;</span>'; ?></td>
                  <td><code><?php echo esc_html( $term->slug ); ?></code></td>
                  <td><?php echo esc_html( $t_type->name ?? '-' ); ?></td>
                  <td>
                    <?php if ( $pt_obj ) :
                      $dot = ! empty( $pt_obj->color ) ? $pt_obj->color : '#94a3b8';
                    ?>
                      <span style="display:inline-flex;align-items:center;gap:6px;white-space:nowrap;">
                        <span style="flex-shrink:0;width:10px;height:10px;border-radius:50%;background:<?php echo esc_attr( $dot ); ?>;"></span>
                        <?php echo esc_html( ( $pt_obj->icon_emoji ? $pt_obj->icon_emoji . ' ' : '' ) . $pt_obj->name ); ?>
                      </span>
                    <?php else : ?>
                      <small style="opacity:.4;">-</small>
                    <?php endif; ?>
                  </td>
                  <td><span class="ah-badge ah-badge-<?php echo esc_attr( $term->status ); ?>"><?php echo esc_html( $term->status ); ?></span></td>
                  <td class="row-actions">
                    <?php if ( empty( $term->is_protected ) ) : ?>
                      <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'terms', 'action' => 'edit', 'id' => $term->id ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>
                      <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'delete_id' => $term->id ), admin_url( 'admin.php' ) ), 'ah_del_tax' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm" onclick="return confirm('Delete?');">Delete</a>
                    <?php endif; ?>
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
              <option value="">- Select Type -</option>
              <?php foreach ( $types as $t ) : ?><option value="<?php echo esc_attr( $t->id ); ?>" <?php selected( $item->type_id ?? $type_id, $t->id ); ?>><?php echo esc_html( $t->name ); ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="ah-form-row"><label>Name *</label><input type="text" name="name" value="<?php echo esc_attr( $item->name ?? '' ); ?>" class="ah-generate-slug-source" data-slug-target="#term-slug" required></div>
          <div class="ah-form-row"><label>Slug</label><input type="text" name="slug" id="term-slug" value="<?php echo esc_attr( $item->slug ?? '' ); ?>" class="ah-slug-field"></div>
          <div class="ah-form-row">
            <label>Parent Group
              <small style="font-weight:400;opacity:.6;">(from Parent Terms tab)</small>
            </label>
            <select name="parent_term_id">
              <option value="">- No group -</option>
              <?php foreach ( $parents as $par ) : ?>
                <option value="<?php echo esc_attr( $par->id ); ?>" <?php selected( $item->parent_term_id ?? 0, $par->id ); ?>>
                  <?php echo esc_html( ( $par->icon_emoji ? $par->icon_emoji . ' ' : '' ) . $par->name ); ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php if ( empty( $parents ) ) : ?>
              <small style="color:var(--ah-muted);display:block;margin-top:4px;">
                No parent terms yet -
                <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'parent-terms' ), admin_url( 'admin.php' ) ) ); ?>">create some first →</a>
              </small>
            <?php endif; ?>
          </div>
          <div class="ah-form-row"><label>Description</label><textarea name="description" rows="3"><?php echo esc_textarea( $item->description ?? '' ); ?></textarea></div>
          <div class="ah-form-row"><label>Icon (emoji)</label><input type="text" name="icon_emoji" value="<?php echo esc_attr( $item->icon_emoji ?? '' ); ?>" placeholder="e.g. 📖" style="width:80px;font-size:1.4rem;text-align:center;"></div>
          <div class="ah-form-row">
            <label>Card Background Image</label>
            <?php
              $tax_img_id  = (int) ( $item->image_id ?? 0 );
              $tax_img_url = $tax_img_id ? ( wp_get_attachment_image_url( $tax_img_id, 'medium' ) ?: '' ) : '';
            ?>
            <div class="ah-image-picker">
              <img src="<?php echo esc_url( $tax_img_url ); ?>" class="ah-image-preview <?php echo $tax_img_url ? 'visible' : ''; ?>" alt="" style="aspect-ratio:16/9;height:auto;object-fit:cover;width:100%;border-radius:6px;">
              <div class="ah-image-picker-btns" style="margin-top:8px;">
                <input type="hidden" class="ah-image-id" name="image_id" value="<?php echo esc_attr( $tax_img_id ); ?>">
                <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-pick-image">Set Image</button>
                <button type="button" class="ah-btn ah-btn-sm ah-remove-image" style="color:var(--ah-danger);">Remove</button>
              </div>
            </div>
          </div>
          <div class="ah-form-row"><label>Sort Order</label><input type="number" name="sort_order" value="<?php echo esc_attr( $item->sort_order ?? 0 ); ?>"></div>
          <div class="ah-form-row"><label>Status</label><select name="status"><option value="active" <?php selected( $item->status ?? 'active', 'active' ); ?>>Active</option><option value="inactive" <?php selected( $item->status ?? '', 'inactive' ); ?>>Inactive</option></select></div>
          <button type="submit" name="save_term" value="1" class="ah-btn ah-btn-primary">Save Term</button>
          <?php if ( $item ) : ?><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'terms' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary" style="margin-left:8px;">Cancel</a><?php endif; ?>
        </form>
      </div>
    </div>
  <?php endif; ?>
</div>
