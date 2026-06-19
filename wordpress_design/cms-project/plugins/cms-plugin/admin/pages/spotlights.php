<?php
/**
 * admin/pages/spotlights.php
 * Manage Spotlight Terms and Spotlight Items via model classes.
 *
 * Tabs:  terms | items
 * Actions: list | add | edit
 */
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$terms_model = new AH_Spotlight_Terms_Model();
$items_model = new AH_Spotlights_Model();

$tab     = sanitize_key( $_GET['tab']    ?? 'terms' );
$action  = sanitize_key( $_GET['action'] ?? 'list' );
$id      = (int) ( $_GET['id']      ?? 0 );
$term_id = (int) ( $_GET['term_id'] ?? 0 );
$notice  = '';
$n_type  = 'success';

function ah_sp_url( array $args ): string {
	return esc_url( add_query_arg( array_merge( array( 'page' => 'ah-spotlights' ), $args ), admin_url( 'admin.php' ) ) );
}

/* ================================================================
   POST HANDLERS
   ================================================================ */
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ah_spotlights_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_spotlights_nonce'], 'ah_save_spotlight' ) ) {
		wp_die( 'Security check failed.' );
	}

	if ( isset( $_POST['save_term'] ) ) {
		$t_name = sanitize_text_field( $_POST['term_name'] ?? '' );
		$t_slug = sanitize_key( $_POST['term_slug'] ?? '' ) ?: sanitize_title( $t_name );
		$data   = array(
			'name'        => $t_name,
			'slug'        => $t_slug,
			'description' => sanitize_textarea_field( $_POST['term_desc'] ?? '' ),
			'max_display' => max( 1, min( 50, (int) ( $_POST['max_display'] ?? 10 ) ) ),
			'sort_order'  => (int) ( $_POST['sort_order'] ?? 0 ),
			'is_active'   => isset( $_POST['is_active'] ) ? 1 : 0,
		);
		$id ? $terms_model->update( $id, $data ) : $terms_model->create( $data );
		$notice = $id ? 'Term updated.' : 'Term created.';
		$tab = 'terms'; $action = 'list'; $id = 0;
	}

	if ( isset( $_POST['save_item'] ) ) {
		$data = array(
			'term_id'     => (int) ( $_POST['term_id'] ?? 0 ),
			'icon'        => sanitize_text_field( $_POST['icon'] ?? '' ),
			'title'       => sanitize_text_field( $_POST['title'] ?? '' ),
			'description' => sanitize_textarea_field( $_POST['description'] ?? '' ),
			'point_value' => sanitize_text_field( $_POST['point_value'] ?? '' ),
			'point_label' => sanitize_text_field( $_POST['point_label'] ?? '' ),
			'link_url'    => esc_url_raw( $_POST['link_url'] ?? '' ),
			'link_label'  => sanitize_text_field( $_POST['link_label'] ?? '' ),
			'show_link'   => isset( $_POST['show_link'] ) ? 1 : 0,
			'sort_order'  => (int) ( $_POST['sort_order'] ?? 0 ),
			'is_active'   => isset( $_POST['is_active'] ) ? 1 : 0,
		);
		$id ? $items_model->update( $id, $data ) : $items_model->create( $data );
		$notice = $id ? 'Item updated.' : 'Item created.';
		$tab = 'items'; $action = 'list'; $id = 0;
	}
}

/* ── Deletes & Toggles ── */
if ( isset( $_GET['delete_term'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_del_sp_term' ) ) {
	$terms_model->delete_with_items( (int) $_GET['delete_term'] );
	wp_safe_redirect( ah_sp_url( array( 'tab' => 'terms', 'deleted' => 1 ) ) ); exit;
}
if ( isset( $_GET['delete_item'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_del_sp_item' ) ) {
	$items_model->delete( (int) $_GET['delete_item'] );
	wp_safe_redirect( ah_sp_url( array( 'tab' => 'items', 'term_id' => $term_id, 'deleted' => 1 ) ) ); exit;
}
if ( isset( $_GET['toggle_term'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_tog_sp_term' ) ) {
	$terms_model->toggle_active( (int) $_GET['toggle_term'] );
	wp_safe_redirect( ah_sp_url( array( 'tab' => 'terms' ) ) ); exit;
}
if ( isset( $_GET['toggle_item'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_tog_sp_item' ) ) {
	$items_model->toggle_active( (int) $_GET['toggle_item'] );
	wp_safe_redirect( ah_sp_url( array( 'tab' => 'items', 'term_id' => $term_id ) ) ); exit;
}

if ( isset( $_GET['deleted'] ) ) { $notice = 'Deleted successfully.'; }
?>
<div class="wrap ah-wrap">
<h1><span class="dashicons dashicons-star-half"></span> Spotlights</h1>
<?php if ( $notice ) : ?>
<div class="ah-notice ah-notice-<?php echo esc_attr( $n_type ); ?>"><?php echo esc_html( $notice ); ?></div>
<?php endif; ?>

<nav class="ah-tabs" style="margin:12px 0 20px">
	<a href="<?php echo ah_sp_url( array( 'tab' => 'terms' ) ); ?>" class="ah-tab<?php echo $tab === 'terms' ? ' ah-tab--active' : ''; ?>">Terms</a>
	<a href="<?php echo ah_sp_url( array( 'tab' => 'items' ) ); ?>" class="ah-tab<?php echo $tab === 'items' ? ' ah-tab--active' : ''; ?>">Items</a>
</nav>

<?php /* ============================================================  TERMS  ============================================================ */ ?>
<?php if ( $tab === 'terms' ) : ?>

<?php if ( $action === 'add' || $action === 'edit' ) :
	$term = ( $action === 'edit' && $id ) ? $terms_model->find( $id ) : null;
?>
<div class="ah-form-card" style="max-width:640px">
	<h2><?php echo $action === 'edit' ? 'Edit Term' : 'Add Term'; ?></h2>
	<form method="post">
		<?php wp_nonce_field( 'ah_save_spotlight', 'ah_spotlights_nonce' ); ?>
		<input type="hidden" name="save_term" value="1">
		<table class="form-table">
			<tr><th><label>Name</label></th>
				<td><input type="text" name="term_name" value="<?php echo esc_attr( $term->name ?? '' ); ?>" class="regular-text" required></td></tr>
			<tr><th><label>Slug</label></th>
				<td><input type="text" name="term_slug" value="<?php echo esc_attr( $term->slug ?? '' ); ?>" class="regular-text" placeholder="auto-generated"></td></tr>
			<tr><th><label>Description</label></th>
				<td><textarea name="term_desc" rows="2" class="large-text"><?php echo esc_textarea( $term->description ?? '' ); ?></textarea></td></tr>
			<tr><th><label>Max display</label></th>
				<td><input type="number" name="max_display" value="<?php echo esc_attr( $term->max_display ?? 10 ); ?>" min="1" max="50" style="width:80px">
					<p class="description">Widget shows up to this many active items.</p></td></tr>
			<tr><th><label>Sort Order</label></th>
				<td><input type="number" name="sort_order" value="<?php echo esc_attr( $term->sort_order ?? 0 ); ?>" style="width:80px"></td></tr>
			<tr><th>Active</th>
				<td><label><input type="checkbox" name="is_active" value="1" <?php checked( isset( $term->is_active ) ? (int) $term->is_active : 1 ); ?>> Enabled</label></td></tr>
		</table>
		<p>
			<button type="submit" class="button button-primary">Save Term</button>
			<a href="<?php echo ah_sp_url( array( 'tab' => 'terms' ) ); ?>" class="button">Cancel</a>
		</p>
	</form>
</div>

<?php else :
	$all_terms = $terms_model->all( array( 'order_by' => 'sort_order', 'order' => 'ASC' ) );
?>
<p><a href="<?php echo ah_sp_url( array( 'tab' => 'terms', 'action' => 'add' ) ); ?>" class="button button-primary">+ Add Term</a></p>
<?php if ( $all_terms ) : ?>
<table class="wp-list-table widefat fixed striped">
	<thead><tr><th>Name</th><th>Slug</th><th>Max</th><th>Items</th><th>Active</th><th>Actions</th></tr></thead>
	<tbody>
	<?php foreach ( $all_terms as $t ) : ?>
	<tr>
		<td><strong><?php echo esc_html( $t->name ); ?></strong></td>
		<td><code><?php echo esc_html( $t->slug ); ?></code></td>
		<td><?php echo (int) $t->max_display; ?></td>
		<td><a href="<?php echo ah_sp_url( array( 'tab' => 'items', 'term_id' => $t->id ) ); ?>"><?php echo $terms_model->item_count( (int) $t->id ); ?></a></td>
		<td>
			<a href="<?php echo esc_url( wp_nonce_url( ah_sp_url( array( 'tab' => 'terms', 'toggle_term' => $t->id ) ), 'ah_tog_sp_term' ) ); ?>">
				<?php echo $t->is_active ? '<span style="color:#16a34a">● Active</span>' : '<span style="color:#9ca3af">● Inactive</span>'; ?>
			</a>
		</td>
		<td>
			<a href="<?php echo ah_sp_url( array( 'tab' => 'terms', 'action' => 'edit', 'id' => $t->id ) ); ?>">Edit</a> &nbsp;|&nbsp;
			<a href="<?php echo esc_url( wp_nonce_url( ah_sp_url( array( 'tab' => 'terms', 'delete_term' => $t->id ) ), 'ah_del_sp_term' ) ); ?>"
			   onclick="return confirm('Delete term and all its items?')" style="color:#b91c1c">Delete</a>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<?php else : ?>
<p>No terms yet. <a href="<?php echo ah_sp_url( array( 'tab' => 'terms', 'action' => 'add' ) ); ?>">Add the first one.</a></p>
<?php endif; ?>
<?php endif; ?>

<?php /* ============================================================  ITEMS  ============================================================ */ ?>
<?php elseif ( $tab === 'items' ) :
	$active_terms = $terms_model->get_all_active();

	if ( $action === 'add' || $action === 'edit' ) :
		$item = ( $action === 'edit' && $id ) ? $items_model->find( $id ) : null;
?>
<div class="ah-form-card" style="max-width:680px">
	<h2><?php echo $action === 'edit' ? 'Edit Item' : 'Add Item'; ?></h2>
	<form method="post">
		<?php wp_nonce_field( 'ah_save_spotlight', 'ah_spotlights_nonce' ); ?>
		<input type="hidden" name="save_item" value="1">
		<table class="form-table">
			<tr><th><label>Term</label></th>
				<td>
					<select name="term_id" required>
						<option value="">— select —</option>
						<?php foreach ( $active_terms as $t ) : ?>
						<option value="<?php echo (int) $t->id; ?>" <?php selected( isset( $item->term_id ) ? (int) $item->term_id : $term_id, (int) $t->id ); ?>>
							<?php echo esc_html( $t->name ); ?> (<?php echo esc_html( $t->slug ); ?>)
						</option>
						<?php endforeach; ?>
					</select>
				</td></tr>
			<tr><th><label>Icon</label></th>
				<td><input type="text" name="icon" value="<?php echo esc_attr( $item->icon ?? '' ); ?>" class="regular-text" placeholder="fa-solid fa-heart  or emoji 🌿">
					<p class="description">Font Awesome class string or a single emoji.</p></td></tr>
			<tr><th><label>Title</label></th>
				<td><input type="text" name="title" value="<?php echo esc_attr( $item->title ?? '' ); ?>" class="large-text" required></td></tr>
			<tr><th><label>Description</label></th>
				<td><textarea name="description" rows="2" class="large-text"><?php echo esc_textarea( $item->description ?? '' ); ?></textarea></td></tr>
			<tr><th><label>Point Value</label></th>
				<td><input type="text" name="point_value" value="<?php echo esc_attr( $item->point_value ?? '' ); ?>" style="width:120px" placeholder="100+"></td></tr>
			<tr><th><label>Point Label</label></th>
				<td><input type="text" name="point_label" value="<?php echo esc_attr( $item->point_label ?? '' ); ?>" class="regular-text" placeholder="Properties Listed"></td></tr>
			<tr><th><label>Link URL</label></th>
				<td><input type="url" name="link_url" value="<?php echo esc_attr( $item->link_url ?? '' ); ?>" class="large-text"></td></tr>
			<tr><th><label>Link Label</label></th>
				<td><input type="text" name="link_label" value="<?php echo esc_attr( $item->link_label ?? '' ); ?>" class="regular-text" placeholder="Learn more"></td></tr>
			<tr><th>Show Link</th>
				<td><label><input type="checkbox" name="show_link" value="1" <?php checked( (int) ( $item->show_link ?? 0 ) ); ?>> Display link on card</label></td></tr>
			<tr><th><label>Sort Order</label></th>
				<td><input type="number" name="sort_order" value="<?php echo esc_attr( $item->sort_order ?? 0 ); ?>" style="width:80px"></td></tr>
			<tr><th>Active</th>
				<td><label><input type="checkbox" name="is_active" value="1" <?php checked( isset( $item->is_active ) ? (int) $item->is_active : 1 ); ?>> Enabled</label></td></tr>
		</table>
		<p>
			<button type="submit" class="button button-primary">Save Item</button>
			<a href="<?php echo ah_sp_url( array( 'tab' => 'items', 'term_id' => $term_id ) ); ?>" class="button">Cancel</a>
		</p>
	</form>
</div>

<?php else :
	$result = $items_model->get_paginated_for_admin( AH_Pagination::current_page(), $term_id );
	$items  = $result['items'];
?>
<div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;flex-wrap:wrap">
	<a href="<?php echo ah_sp_url( array( 'tab' => 'items', 'action' => 'add', 'term_id' => $term_id ) ); ?>" class="button button-primary">+ Add Item</a>
	<form method="get" style="display:flex;gap:6px;align-items:center">
		<input type="hidden" name="page" value="ah-spotlights">
		<input type="hidden" name="tab" value="items">
		<select name="term_id" onchange="this.form.submit()" style="height:30px">
			<option value="">All terms</option>
			<?php foreach ( $active_terms as $t ) : ?>
			<option value="<?php echo (int) $t->id; ?>" <?php selected( $term_id, (int) $t->id ); ?>>
				<?php echo esc_html( $t->name ); ?>
			</option>
			<?php endforeach; ?>
		</select>
	</form>
</div>

<?php if ( $items ) : ?>
<table class="wp-list-table widefat fixed striped">
	<thead><tr>
		<th style="width:36px">Icon</th><th>Title</th><th>Point</th><th>Term</th><th>Link</th><th>Active</th><th>Actions</th>
	</tr></thead>
	<tbody>
	<?php foreach ( $items as $item ) : ?>
	<tr>
		<td style="font-size:1.2rem;line-height:1"><?php echo esc_html( $item->icon ); ?></td>
		<td>
			<strong><?php echo esc_html( $item->title ); ?></strong>
			<?php if ( $item->description ) : ?><br><small style="color:#6b7280"><?php echo esc_html( wp_trim_words( $item->description, 8 ) ); ?></small><?php endif; ?>
		</td>
		<td><?php if ( $item->point_value ) : ?><strong><?php echo esc_html( $item->point_value ); ?></strong><br><small><?php echo esc_html( $item->point_label ); ?></small><?php endif; ?></td>
		<td><?php $t_row = $terms_model->find( (int) $item->term_id ); echo $t_row ? '<code>' . esc_html( $t_row->slug ) . '</code>' : '—'; ?></td>
		<td><?php echo $item->show_link && $item->link_url ? '<span style="color:#16a34a">✓</span>' : '—'; ?></td>
		<td>
			<a href="<?php echo esc_url( wp_nonce_url( ah_sp_url( array( 'tab' => 'items', 'toggle_item' => $item->id, 'term_id' => $term_id ) ), 'ah_tog_sp_item' ) ); ?>">
				<?php echo $item->is_active ? '<span style="color:#16a34a">●</span>' : '<span style="color:#9ca3af">●</span>'; ?>
			</a>
		</td>
		<td>
			<a href="<?php echo ah_sp_url( array( 'tab' => 'items', 'action' => 'edit', 'id' => $item->id, 'term_id' => $term_id ) ); ?>">Edit</a> &nbsp;|&nbsp;
			<a href="<?php echo esc_url( wp_nonce_url( ah_sp_url( array( 'tab' => 'items', 'delete_item' => $item->id, 'term_id' => $term_id ) ), 'ah_del_sp_item' ) ); ?>"
			   onclick="return confirm('Delete this item?')" style="color:#b91c1c">Delete</a>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<?php else : ?>
<p>No items found. <a href="<?php echo ah_sp_url( array( 'tab' => 'items', 'action' => 'add' ) ); ?>">Add the first spotlight.</a></p>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
</div>
