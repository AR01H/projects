<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$model  = new AH_Nav_Model();
$notice = '';
$action = sanitize_key( $_GET['action'] ?? 'list' );
$menu_id = (int) ( $_GET['menu_id'] ?? 0 );

// Handle POST
if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	if ( ! wp_verify_nonce( $_POST['ah_nav_nonce'] ?? '', 'ah_save_nav' ) ) wp_die( 'Security check failed.' );

	if ( isset( $_POST['save_item'] ) ) {
		$item_id = (int) ( $_POST['item_id'] ?? 0 );
		$data    = array(
			'menu_id'   => (int) $_POST['menu_id'],
			'parent_id' => (int) ( $_POST['parent_id'] ?? 0 ) ?: null,
			'label'     => sanitize_text_field( $_POST['label'] ?? '' ),
			'url'       => esc_url_raw( $_POST['url'] ?? '' ),
			'target'    => in_array( $_POST['target'] ?? '_self', array( '_self', '_blank' ), true ) ? $_POST['target'] : '_self',
			'icon_class'=> sanitize_text_field( $_POST['icon_class'] ?? '' ),
			'sort_order'=> (int) ( $_POST['sort_order'] ?? 0 ),
			'status'    => sanitize_key( $_POST['status'] ?? 'active' ),
		);
		$item_id ? $model->update_item( $item_id, $data ) : $model->add_item( $data );
		$notice = 'Menu item saved.';
		$action = 'items';
	}

	if ( isset( $_POST['delete_item'] ) ) {
		$model->delete_item( (int) $_POST['item_id'] );
		$notice = 'Menu item deleted.';
		$action = 'items';
	}
}

$menus = $model->get_all_menus();
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-menu-alt"></span> <?php esc_html_e( 'Navigation Menus', 'ah-theme' ); ?></h1>

  <?php if ( $notice ) : ?><div class="ah-notice ah-notice-success"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

  <?php if ( $action === 'list' || ! $menu_id ) : ?>

    <!-- List of menus -->
    <div class="ah-table-wrap">
      <table class="ah-table">
        <thead><tr><th>Menu Name</th><th>Slug</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ( $menus as $menu ) : ?>
            <tr>
              <td><strong><?php echo esc_html( $menu->name ); ?></strong></td>
              <td><code><?php echo esc_html( $menu->slug ); ?></code></td>
              <td><span class="ah-badge ah-badge-<?php echo esc_attr( $menu->status ); ?>"><?php echo esc_html( $menu->status ); ?></span></td>
              <td class="row-actions">
                <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-nav-menus', 'action' => 'items', 'menu_id' => $menu->id ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Manage Items</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  <?php elseif ( $action === 'items' || $action === 'add_item' || $action === 'edit_item' ) :
    $menu      = $model->find( $menu_id );
    $items     = $model->get_items( $menu_id );
    $edit_item = null;
    $edit_id   = (int) ( $_GET['item_id'] ?? 0 );
    if ( $edit_id ) $edit_item = $model->get_item( $edit_id );
    $top_items = array_filter( $items, fn( $i ) => ! $i->parent_id );
  ?>

    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-nav-menus' ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm" style="margin-bottom:14px;display:inline-flex;">&larr; Back to Menus</a>

    <h2 style="font-size:17px;font-weight:700;margin-bottom:16px;"><?php echo esc_html( $menu->name ?? 'Menu' ); ?> — Items</h2>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">

      <!-- Items List -->
      <div class="ah-table-wrap">
        <table class="ah-table ah-sortable-list" data-model="nav_menu_items">
          <thead><tr><th></th><th>Label</th><th>URL</th><th>Parent</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ( $items as $item ) : ?>
              <tr data-id="<?php echo esc_attr( $item->id ); ?>">
                <td class="ah-sort-handle" style="cursor:grab;color:var(--ah-muted);">&#9776;</td>
                <td><?php echo esc_html( $item->label ); ?><?php echo $item->target === '_blank' ? ' <small style="color:var(--ah-muted);">[new tab]</small>' : ''; ?></td>
                <td><small><?php echo esc_html( $item->url ?: $item->page_slug ); ?></small></td>
                <td><?php if ( $item->parent_id ) { $p = $model->get_item( $item->parent_id ); echo $p ? esc_html( $p->label ) : '-'; } else { echo '-'; } ?></td>
                <td><span class="ah-badge ah-badge-<?php echo esc_attr( $item->status ); ?>"><?php echo esc_html( $item->status ); ?></span></td>
                <td class="row-actions">
                  <a href="<?php echo esc_url( add_query_arg( array( 'action' => 'items', 'menu_id' => $menu_id, 'item_id' => $item->id ), admin_url( 'admin.php?page=ah-nav-menus' ) ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>
                  <form method="post" style="display:inline;" class="ah-confirm-form">
                    <?php wp_nonce_field( 'ah_save_nav', 'ah_nav_nonce' ); ?>
                    <input type="hidden" name="item_id" value="<?php echo esc_attr( $item->id ); ?>">
                    <button type="submit" name="delete_item" value="1" class="ah-btn ah-btn-danger ah-btn-sm">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Add / Edit Item Form -->
      <div class="ah-card">
        <div class="ah-card-header">
          <h2><?php echo $edit_item ? 'Edit Item' : 'Add Item'; ?></h2>
        </div>
        <form method="post">
          <?php wp_nonce_field( 'ah_save_nav', 'ah_nav_nonce' ); ?>
          <input type="hidden" name="item_id" value="<?php echo esc_attr( $edit_id ); ?>">
          <input type="hidden" name="menu_id" value="<?php echo esc_attr( $menu_id ); ?>">

          <div class="ah-form-row">
            <label>Label *</label>
            <input type="text" name="label" value="<?php echo esc_attr( $edit_item->label ?? '' ); ?>" required>
          </div>
          <div class="ah-form-row">
            <label>URL</label>
            <input type="text" name="url" value="<?php echo esc_attr( $edit_item->url ?? '' ); ?>" placeholder="https://... or /page-slug">
          </div>
          <div class="ah-form-row">
            <label>Parent Item</label>
            <select name="parent_id">
              <option value="">— None (top-level) —</option>
              <?php foreach ( $top_items as $ti ) : ?>
                <option value="<?php echo esc_attr( $ti->id ); ?>" <?php selected( $edit_item->parent_id ?? 0, $ti->id ); ?>><?php echo esc_html( $ti->label ); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="ah-form-row">
            <label>Open In</label>
            <select name="target">
              <option value="_self" <?php selected( $edit_item->target ?? '_self', '_self' ); ?>>Same Tab</option>
              <option value="_blank" <?php selected( $edit_item->target ?? '', '_blank' ); ?>>New Tab</option>
            </select>
          </div>
          <div class="ah-form-row">
            <label>Sort Order</label>
            <input type="number" name="sort_order" value="<?php echo esc_attr( $edit_item->sort_order ?? 0 ); ?>">
          </div>
          <div class="ah-form-row">
            <label>Status</label>
            <select name="status">
              <option value="active" <?php selected( $edit_item->status ?? 'active', 'active' ); ?>>Active</option>
              <option value="inactive" <?php selected( $edit_item->status ?? '', 'inactive' ); ?>>Inactive</option>
            </select>
          </div>
          <button type="submit" name="save_item" value="1" class="ah-btn ah-btn-primary">Save Item</button>
        </form>
      </div>

    </div><!-- /grid -->
  <?php endif; ?>
</div>
