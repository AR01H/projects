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

/* Handle delete via POST to avoid GET/nonce issues */
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['delete_item'] ) ) {
	$post_del = (int) $_POST['delete_item'];
	if ( ! wp_verify_nonce( $_POST['_wpnonce'] ?? '', 'ah_del_sp_item' ) ) {
		$notice = 'Security check failed. Item not deleted (id: ' . $post_del . ').';
		$n_type = 'error';
		error_log( sprintf( 'AH_SPOTLIGHTS: delete_item POST nonce failed - user=%d, delete_item=%d, referer=%s', get_current_user_id(), $post_del, $_SERVER['HTTP_REFERER'] ?? '' ) );
	} else {
		$items_model->delete( $post_del );
		error_log( sprintf( 'AH_SPOTLIGHTS: delete_item POST succeeded - user=%d, delete_item=%d', get_current_user_id(), $post_del ) );
		wp_safe_redirect( ah_sp_url( array( 'tab' => 'items', 'term_id' => $term_id, 'deleted' => 1, 'deleted_id' => $post_del ) ) ); exit;
	}
}

/* ── Deletes & Toggles ── */
if ( isset( $_GET['delete_term'] ) ) {
	if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_del_sp_term' ) ) {
		$notice = 'Security check failed. Term not deleted.';
		$n_type = 'error';
	} else {
		$terms_model->delete_with_items( (int) $_GET['delete_term'] );
		wp_safe_redirect( ah_sp_url( array( 'tab' => 'terms', 'deleted' => 1 ) ) ); exit;
	}
}

if ( isset( $_GET['delete_item'] ) ) {
	if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_del_sp_item' ) ) {
		$del_id = (int) ( $_GET['delete_item'] ?? 0 );
		$notice = 'Security check failed. Item not deleted (id: ' . $del_id . ').';
		$n_type = 'error';
		// Log for debugging (do not expose nonce in UI)
		error_log( sprintf( 'AH_SPOTLIGHTS: delete_item nonce failed - user=%d, delete_item=%d, _wpnonce=%s, referer=%s', get_current_user_id(), $del_id, $_GET['_wpnonce'] ?? '', $_SERVER['HTTP_REFERER'] ?? '' ) );
	} else {
		$del_id = (int) $_GET['delete_item'];
		$items_model->delete( $del_id );
		// Log successful delete for traceability
		error_log( sprintf( 'AH_SPOTLIGHTS: delete_item succeeded - user=%d, delete_item=%d', get_current_user_id(), $del_id ) );
		wp_safe_redirect( ah_sp_url( array( 'tab' => 'items', 'term_id' => $term_id, 'deleted' => 1, 'deleted_id' => $del_id ) ) ); exit;
	}
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
<?php \Ah\Cms\Admin\Components\AdminComponents::pageHeader( 'star-half', 'Spotlights', 'Create spotlight term groups and items for the homepage feature section.' ); ?>
<?php \Ah\Cms\Admin\Components\AdminComponents::notice( $notice, $n_type ); ?>

<?php \Ah\Cms\Admin\Components\AdminComponents::tabBarUrl( array(
	'terms' => 'Terms',
	'items' => 'Items',
), $tab ); ?>

<?php /* ============================================================  TERMS  ============================================================ */ ?>
<?php if ( $tab === 'terms' ) : ?>

<?php if ( $action === 'add' || $action === 'edit' ) :
	$term = ( $action === 'edit' && $id ) ? $terms_model->find( $id ) : null;
?>
<?php \Ah\Cms\Admin\Components\AdminComponents::backLink( ah_sp_url( array( 'tab' => 'terms' ) ), '← Back' ); ?>
<?php ob_start(); ?>
	<form method="post">
		<?php wp_nonce_field( 'ah_save_spotlight', 'ah_spotlights_nonce' ); ?>
		<input type="hidden" name="save_term" value="1">
		<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Name', '<input type="text" name="term_name" value="' . esc_attr( $term->name ?? '' ) . '" class="regular-text" required>' ); ?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Slug', '<input type="text" name="term_slug" value="' . esc_attr( $term->slug ?? '' ) . '" class="regular-text" placeholder="auto-generated">' ); ?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Description', '<textarea name="term_desc" rows="2" class="large-text">' . esc_textarea( $term->description ?? '' ) . '</textarea>' ); ?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::formGrid( array(
			array( 'Max display', '<input type="number" name="max_display" value="' . esc_attr( $term->max_display ?? 10 ) . '" min="1" max="50" style="width:80px"><p class="description">Widget shows up to this many active items.</p>' ),
			array( 'Sort Order', '<input type="number" name="sort_order" value="' . esc_attr( $term->sort_order ?? 0 ) . '" style="width:80px">' ),
		) ); ?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::field( 'checkbox', 'is_active', 'Enabled', isset( $term->is_active ) ? (int) $term->is_active : 1 ); ?>
		<div style="display:flex;gap:8px;align-items:center;margin-top:12px;">
			<button type="submit" class="ah-btn ah-btn-primary">Save Term</button>
			<a href="<?php echo ah_sp_url( array( 'tab' => 'terms' ) ); ?>" class="ah-btn ah-btn-secondary">Cancel</a>
		</div>
	</form>
<?php \Ah\Cms\Admin\Components\AdminComponents::card( $action === 'edit' ? 'Edit Term' : 'Add Term', ob_get_clean() ); ?>

<?php else :
	$all_terms = $terms_model->all( array( 'order_by' => 'sort_order', 'order' => 'ASC' ) );
	$term_search = sanitize_text_field( $_GET['s'] ?? '' );
	$term_status = sanitize_key( $_GET['status'] ?? '' );

	// Filter terms
	if ( $term_search || $term_status ) {
		$filtered = array();
		foreach ( $all_terms as $t ) {
			if ( $term_search && stripos( $t->name, $term_search ) === false && stripos( $t->slug, $term_search ) === false ) {
				continue;
			}
			if ( $term_status === 'active' && ! $t->is_active ) {
				continue;
			}
			if ( $term_status === 'inactive' && $t->is_active ) {
				continue;
			}
			$filtered[] = $t;
		}
		$all_terms = $filtered;
	}
?>
<?php \Ah\Cms\Admin\Components\AdminComponents::filterBar( array(
	'page_slug'          => 'ah-spotlights',
	'search_placeholder' => 'Search terms…',
	'search_value'       => $term_search,
	'hidden_inputs'      => array( 'tab' => 'terms' ),
	'filters'            => array(
		array(
			'name'     => 'status',
			'options'  => array( '' => 'All Status', 'active' => 'Active', 'inactive' => 'Inactive' ),
			'selected' => $term_status,
		),
	),
	'add_url'   => ah_sp_url( array( 'tab' => 'terms', 'action' => 'add' ) ),
	'add_label' => '+ Add Term',
) ); ?>
<?php if ( $all_terms ) : ?>
<?php \Ah\Cms\Admin\Components\AdminComponents::dataTable( array(
	'columns' => array(
		array( 'label' => 'Name', 'render' => function ( $t ) {
			return '<strong>' . esc_html( $t->name ) . '</strong>';
		} ),
		array( 'label' => 'Slug', 'render' => function ( $t ) {
			return '<code>' . esc_html( $t->slug ) . '</code>';
		} ),
		array( 'label' => 'Max', 'render' => function ( $t ) {
			return (int) $t->max_display;
		} ),
		array( 'label' => 'Items', 'render' => function ( $t ) use ( $terms_model ) {
			return '<a href="' . esc_url( ah_sp_url( array( 'tab' => 'items', 'term_id' => $t->id ) ) ) . '">' . $terms_model->item_count( (int) $t->id ) . '</a>';
		} ),
		array( 'label' => 'Active', 'render' => function ( $t ) {
			$url = esc_url( wp_nonce_url( ah_sp_url( array( 'tab' => 'terms', 'toggle_term' => $t->id ) ), 'ah_tog_sp_term' ) );
			return $t->is_active
				? '<a href="' . $url . '" style="color:#16a34a">● Active</a>'
				: '<a href="' . $url . '" style="color:#9ca3af">● Inactive</a>';
		} ),
	),
	'items'         => $all_terms,
	'empty_message' => 'No terms yet.',
	'actions'       => function ( $t ) {
		$html = '<a href="' . esc_url( ah_sp_url( array( 'tab' => 'terms', 'action' => 'edit', 'id' => $t->id ) ) ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>';
		$html .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline;margin:0;padding:0">';
		$html .= '<input type="hidden" name="action" value="ah_delete_spotlight_term">';
		$html .= wp_nonce_field( 'ah_del_sp_term', '_wpnonce', false );
		$html .= '<input type="hidden" name="delete_term" value="' . (int) $t->id . '">';
		$html .= '<button type="submit" class="ah-btn ah-btn-danger ah-btn-sm ah-confirm-delete" data-title="Delete &quot;' . esc_attr( $t->name ) . '&quot;" data-confirm="This term and all its items will be deleted.">Delete</button>';
		$html .= '</form>';
		return $html;
	},
) ); ?>
<?php else : ?>
	<?php \Ah\Cms\Admin\Components\AdminComponents::emptyState( 'No terms yet.', 'star-half' ); ?>
<?php endif; ?>
<?php endif; ?>

<?php /* ============================================================  ITEMS  ============================================================ */ ?>
<?php elseif ( $tab === 'items' ) :
	$active_terms = $terms_model->get_all_active();

	if ( $action === 'add' || $action === 'edit' ) :
		$item = ( $action === 'edit' && $id ) ? $items_model->find( $id ) : null;
?>
<?php \Ah\Cms\Admin\Components\AdminComponents::backLink( ah_sp_url( array( 'tab' => 'items', 'term_id' => $term_id ) ), '← Back' ); ?>
<?php ob_start(); ?>
	<form method="post">
		<?php wp_nonce_field( 'ah_save_spotlight', 'ah_spotlights_nonce' ); ?>
		<input type="hidden" name="save_item" value="1">
		<?php
		$term_select = '<select name="term_id" required><option value="">- select -</option>';
		foreach ( $active_terms as $t ) {
			$term_select .= '<option value="' . (int) $t->id . '"' . selected( isset( $item->term_id ) ? (int) $item->term_id : $term_id, (int) $t->id, false ) . '>'
				. esc_html( $t->name ) . ' (' . esc_html( $t->slug ) . ')</option>';
		}
		$term_select .= '</select>';
		?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Term', $term_select ); ?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Icon', '<input type="text" name="icon" value="' . esc_attr( $item->icon ?? '' ) . '" class="regular-text" placeholder="fa-solid fa-heart  or emoji 🌿"><p class="description">Font Awesome class string or a single emoji.</p>' ); ?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Title', '<input type="text" name="title" value="' . esc_attr( $item->title ?? '' ) . '" class="large-text" required>' ); ?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Description', '<textarea name="description" rows="2" class="large-text">' . esc_textarea( $item->description ?? '' ) . '</textarea>' ); ?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::formGrid( array(
			array( 'Point Value', '<input type="text" name="point_value" value="' . esc_attr( $item->point_value ?? '' ) . '" style="width:120px" placeholder="100+">' ),
			array( 'Point Label', '<input type="text" name="point_label" value="' . esc_attr( $item->point_label ?? '' ) . '" class="regular-text" placeholder="Properties Listed">' ),
		) ); ?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::formGrid( array(
			array( 'Link URL', '<input type="text" name="link_url" value="' . esc_attr( $item->link_url ?? '' ) . '" class="large-text" placeholder="https://… or /slug/ or #section">' ),
			array( 'Link Label', '<input type="text" name="link_label" value="' . esc_attr( $item->link_label ?? '' ) . '" class="regular-text" placeholder="Learn more">' ),
		) ); ?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::field( 'checkbox', 'show_link', 'Display link on card', (int) ( $item->show_link ?? 0 ) ); ?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::formGrid( array(
			array( 'Sort Order', '<input type="number" name="sort_order" value="' . esc_attr( $item->sort_order ?? 0 ) . '" style="width:80px">' ),
			array( '', \Ah\Cms\Admin\Components\AdminComponents::field( 'checkbox', 'is_active', 'Enabled', isset( $item->is_active ) ? (int) $item->is_active : 1 ) ),
		) ); ?>
		<div style="display:flex;gap:8px;align-items:center;margin-top:12px;">
			<button type="submit" class="ah-btn ah-btn-primary">Save Item</button>
			<a href="<?php echo ah_sp_url( array( 'tab' => 'items', 'term_id' => $term_id ) ); ?>" class="ah-btn ah-btn-secondary">Cancel</a>
		</div>
	</form>
<?php \Ah\Cms\Admin\Components\AdminComponents::card( $action === 'edit' ? 'Edit Item' : 'Add Item', ob_get_clean() ); ?>

<?php else :
	$result = $items_model->get_paginated_for_admin( AH_Pagination::current_page(), $term_id );
	$items  = $result['items'];
?>
<?php \Ah\Cms\Admin\Components\AdminComponents::filterBar( array(
	'page_slug'          => 'ah-spotlights',
	'search_placeholder' => 'Search items…',
	'hidden_inputs'      => array( 'tab' => 'items', 'term_id' => $term_id ),
	'add_url'            => ah_sp_url( array( 'tab' => 'items', 'action' => 'add', 'term_id' => $term_id ) ),
	'add_label'          => '+ Add Item',
	'filters'            => array(
		array(
			'name'     => 'term_id',
			'options'  => array_merge( array( '' => 'All terms' ), array_combine( array_column( $active_terms, 'id' ), array_map( fn( $t ) => $t->name, $active_terms ) ) ),
			'selected' => $term_id,
		),
	),
) ); ?>

<?php if ( $items ) : ?>
<?php \Ah\Cms\Admin\Components\AdminComponents::dataTable( array(
	'columns' => array(
		array( 'label' => 'Icon', 'style' => 'width:36px', 'render' => function ( $item ) {
			return '<span style="font-size:1.2rem;line-height:1">' . esc_html( $item->icon ) . '</span>';
		} ),
		array( 'label' => 'Title', 'render' => function ( $item ) {
			$html = '<strong>' . esc_html( $item->title ) . '</strong>';
			if ( $item->description ) $html .= '<br><small style="color:#6b7280">' . esc_html( wp_trim_words( $item->description, 8 ) ) . '</small>';
			return $html;
		} ),
		array( 'label' => 'Point', 'render' => function ( $item ) {
			if ( ! $item->point_value ) return '-';
			return '<strong>' . esc_html( $item->point_value ) . '</strong><br><small>' . esc_html( $item->point_label ) . '</small>';
		} ),
		array( 'label' => 'Term', 'render' => function ( $item ) use ( $items_model ) {
			$t_row = $items_model instanceof AH_Spotlight_Terms_Model ? $items_model->find( (int) $item->term_id ) : null;
			return $t_row ? '<code>' . esc_html( $t_row->slug ) . '</code>' : '-';
		} ),
		array( 'label' => 'Link', 'render' => function ( $item ) {
			return $item->show_link && $item->link_url ? '<span style="color:#16a34a">✓</span>' : '-';
		} ),
	),
	'items'         => $items,
	'empty_message' => 'No items found.',
	'actions'       => function ( $item ) use ( $term_id ) {
		$html = '<a href="' . esc_url( ah_sp_url( array( 'tab' => 'items', 'action' => 'edit', 'id' => $item->id, 'term_id' => $term_id ) ) ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>';
		$html .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline;margin:0;padding:0">';
		$html .= '<input type="hidden" name="action" value="ah_delete_spotlight_item">';
		$html .= '<input type="hidden" name="term_id" value="' . (int) $term_id . '">';
		$html .= wp_nonce_field( 'ah_del_sp_item', '_wpnonce', false );
		$html .= '<input type="hidden" name="delete_item" value="' . (int) $item->id . '">';
		$html .= '<button type="submit" class="ah-btn ah-btn-danger ah-btn-sm ah-confirm-delete" data-title="Delete &quot;' . esc_attr( $item->title ) . '&quot;" data-confirm="This spotlight item will be permanently removed.">Delete</button>';
		$html .= '</form>';
		return $html;
	},
) ); ?>
<?php else : ?>
	<?php \Ah\Cms\Admin\Components\AdminComponents::emptyState( 'No items found. Add the first spotlight.', 'star-half' ); ?>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
</div>
