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

// GET: delete taxonomy type
if ( isset( $_GET['delete_type_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_del_type' ) ) {
	$del_type = $model->get_type( (int) $_GET['delete_type_id'] );
	if ( $del_type && $del_type->slug === 'data-protected' ) {
		$notice = 'This taxonomy type is protected and cannot be deleted.';
	} else {
		$model->delete_type( (int) $_GET['delete_type_id'] );
		AH_Admin_Bootstrap::redirect( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'types', 'type_deleted' => 1 ), admin_url( 'admin.php' ) ) );
	}
}
if ( isset( $_GET['type_deleted'] ) ) $notice = 'Taxonomy type deleted.';

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
		AH_Admin_Bootstrap::redirect( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'types', 'type_saved' => 1 ), admin_url( 'admin.php' ) ) );
	}
}
if ( isset( $_GET['type_saved'] ) ) $notice = 'Taxonomy type saved.';

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
  <?php \Ah\Cms\Admin\Components\AdminComponents::pageHeader( 'tag', 'Taxonomies', 'Organise content with custom taxonomy types and terms.' ); ?>
  <?php if ( $notice ) : ?><?php \Ah\Cms\Admin\Components\AdminComponents::notice( $notice, 'success' ); ?><?php endif; ?>

  <?php \Ah\Cms\Admin\Components\AdminComponents::tabBarUrl( array(
	'terms'       => 'Terms',
	'parent-terms'=> 'Parent Terms',
	'types'       => 'Taxonomy Types',
  ), $tab ); ?>

  <?php if ( $tab === 'parent-terms' ) :
    $pt_search = sanitize_text_field( $_GET['s'] ?? '' );
    $pt_items = $pt_model->get_all();

    // Filter by search
    if ( $pt_search ) {
      $filtered = array();
      foreach ( $pt_items as $pt ) {
        if ( stripos( $pt->name, $pt_search ) !== false || stripos( $pt->slug, $pt_search ) !== false || stripos( $pt->description ?? '', $pt_search ) !== false ) {
          $filtered[] = $pt;
        }
      }
      $pt_items = $filtered;
    }

    $pt_item  = $edit_id ? $pt_model->find( $edit_id ) : null;
  ?>

    <?php if ( $action === 'add' || $action === 'edit' ) : ?>
      <?php \Ah\Cms\Admin\Components\AdminComponents::backLink( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'parent-terms' ), admin_url( 'admin.php' ) ) ); ?>
      <?php ob_start(); ?>
        <?php
          $pt_form_args = array( 'page' => 'ah-taxonomy', 'tab' => 'parent-terms' );
          if ( $pt_item ) $pt_form_args['id'] = $edit_id;
          $pt_img_id  = $pt_item ? (int) ( $pt_item->image_id ?? 0 ) : 0;
        ?>
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-bottom:16px;">
          <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'parent-terms' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary">Cancel</a>
          <button type="submit" name="save_parent_term" value="1" class="ah-btn ah-btn-primary">Save</button>
        </div>
        <form method="post" action="<?php echo esc_url( add_query_arg( $pt_form_args, admin_url( 'admin.php' ) ) ); ?>">
          <?php wp_nonce_field( 'ah_save_parent_term', 'ah_pt_nonce' ); ?>
          <div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;">
          <div>
          <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Name *', '<input type="text" name="pt_name" value="' . esc_attr( $pt_item->name ?? '' ) . '" class="ah-generate-slug-source" data-slug-target="#pt-slug" required>' ); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Slug', '<input type="text" name="pt_slug" id="pt-slug" value="' . esc_attr( $pt_item->slug ?? '' ) . '" class="ah-slug-field">' ); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Description', '<textarea name="pt_description" rows="3">' . esc_textarea( $pt_item->description ?? '' ) . '</textarea>' ); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Colour',
            '<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">'
            . '<input type="color" name="pt_color" value="' . esc_attr( $pt_item->color ?? '#1e40af' ) . '" style="width:44px;height:36px;padding:2px;border-radius:6px;border:1px solid #ddd;cursor:pointer;">'
            . '<div style="display:flex;flex-wrap:wrap;gap:5px;">'
            . implode( '', array_map( function ( $c ) {
                return '<span onclick="this.closest(\'form\').querySelector(\'[name=pt_color]\').value=\'' . esc_js( $c ) . '\'" style="width:22px;height:22px;border-radius:4px;background:' . esc_attr( $c ) . ';cursor:pointer;border:2px solid transparent;" title="' . esc_attr( $c ) . '"></span>';
            }, [ '#1e40af','#15803d','#b45309','#9333ea','#dc2626','#0891b2','#be185d','#374151' ] ) )
            . '</div></div>'
          ); ?>
          </div>
          <div>
          <?php \Ah\Cms\Admin\Components\AdminComponents::mediaField( 'pt_image_id', 'Image / Video', $pt_img_id, array( 'type' => 'media' ) ); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::formGrid( array(
            array( 'Icon (emoji)', '<div style="display:flex;align-items:center;gap:8px;"><input type="text" name="pt_icon_emoji" value="' . esc_attr( $pt_item->icon_emoji ?? '' ) . '" placeholder="🏠" style="width:70px;font-size:1.4rem;text-align:center;"><span id="pt-emoji-preview" style="font-size:2rem;line-height:1;">' . esc_html( $pt_item->icon_emoji ?? '' ) . '</span></div>' ),
            array( 'Sort Order', '<input type="number" name="pt_sort_order" value="' . esc_attr( $pt_item->sort_order ?? 0 ) . '">' ),
          ) ); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Status', '<select name="pt_status"><option value="active"' . selected( $pt_item->status ?? 'active', 'active', false ) . '>Active</option><option value="inactive"' . selected( $pt_item->status ?? '', 'inactive', false ) . '>Inactive</option></select>' ); ?>
          </div>
          </div>
        </form>
      <?php \Ah\Cms\Admin\Components\AdminComponents::card( $pt_item ? 'Edit Parent Term' : 'Add Parent Term', ob_get_clean() ); ?>
      <script>
      document.addEventListener('DOMContentLoaded', function(){
        var ptInput = document.querySelector('[name="pt_icon_emoji"]');
        var ptPreview = document.getElementById('pt-emoji-preview');
        if (ptInput && ptPreview) {
          ptInput.addEventListener('input', function(){ ptPreview.textContent = this.value; });
        }
      });
      </script>

    <?php else : ?>
    <?php \Ah\Cms\Admin\Components\AdminComponents::filterBar( array(
      'page_slug'          => 'ah-taxonomy',
      'search_placeholder' => 'Search parent terms…',
      'search_value'       => $pt_search,
      'hidden_inputs'      => array( 'tab' => 'parent-terms' ),
      'add_url'            => add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'parent-terms', 'action' => 'add' ), admin_url( 'admin.php' ) ),
      'add_label'          => '+ Add Parent Term',
    ) ); ?>
    <?php \Ah\Cms\Admin\Components\AdminComponents::dataTable( array(
          'columns' => array(
            array( 'label' => '', 'render' => function ( $pt ) {
              $dot_color = ! empty( $pt->color ) ? $pt->color : '#94a3b8';
              return '<span style="display:inline-block;width:18px;height:18px;border-radius:50%;background:' . esc_attr( $dot_color ) . ';vertical-align:middle;" title="' . esc_attr( $pt->color ?? 'no colour' ) . '"></span>';
            } ),
            array( 'label' => 'Name', 'render' => function ( $pt ) {
              return '<strong>' . esc_html( ( $pt->icon_emoji ? $pt->icon_emoji . ' ' : '' ) . $pt->name ) . '</strong>'
                   . '<small style="color:var(--ah-muted);display:block;"><code>' . esc_html( $pt->slug ) . '</code></small>';
            } ),
            array( 'label' => 'Description', 'render' => function ( $pt ) {
              return '<small style="color:var(--ah-muted);">' . esc_html( wp_trim_words( $pt->description ?? '', 10 ) ) . '</small>';
            } ),
            array( 'label' => 'Children', 'render' => function ( $pt ) use ( $pt_model ) {
              $child_count = $pt_model->count_children( (int) $pt->id );
              if ( $child_count > 0 ) {
                return '<a href="' . esc_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'terms' ), admin_url( 'admin.php' ) ) ) . '" style="font-size:.82rem;">' . $child_count . ' term' . ( $child_count !== 1 ? 's' : '' ) . ' →</a>';
              }
              return '<small style="opacity:.4;">-</small>';
            } ),
            array( 'label' => 'Status', 'render' => function ( $pt ) {
              return \Ah\Cms\Admin\Components\AdminComponents::statusBadge( $pt->status );
            } ),
          ),
          'items' => $pt_items,
          'empty_message' => 'No parent terms yet - use the form to add one.',
          'actions' => function ( $pt ) {
            $edit_url = add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'parent-terms', 'action' => 'edit', 'id' => $pt->id ), admin_url( 'admin.php' ) );
            $del_url  = add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'parent-terms', 'delete_pt_id' => $pt->id ), admin_url( 'admin.php' ) );
            $html = '<a href="' . esc_url( $edit_url ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>';
            ob_start();
            \Ah\Cms\Admin\Components\AdminComponents::confirmDelete( $del_url, 'ah_del_pt' );
            $html .= ob_get_clean();
            return $html;
          },
        ) ); ?>
  <?php endif; ?>

  <?php elseif ( $tab === 'types' ) :
    $type_search = sanitize_text_field( $_GET['s'] ?? '' );
    if ( $type_search ) {
      $types = array_values( array_filter( $types, function ( $t ) use ( $type_search ) {
        return stripos( $t->name, $type_search ) !== false || stripos( $t->slug, $type_search ) !== false;
      } ) );
    }
    $edit_type_id = isset( $_GET['edit_type'] ) ? (int) $_GET['edit_type'] : -1;
    $edit_type    = $edit_type_id > 0 ? $model->get_type( $edit_type_id ) : null;
  ?>

  <?php if ( $edit_type_id >= 0 ) : ?>
    <?php if ( $edit_type_id > 0 && ! $edit_type ) : ?>
      <?php \Ah\Cms\Admin\Components\AdminComponents::notice( 'Type not found.', 'error' ); ?>
      <?php \Ah\Cms\Admin\Components\AdminComponents::backLink( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'types' ), admin_url( 'admin.php' ) ) ); ?>
    <?php else : ?>
    <?php \Ah\Cms\Admin\Components\AdminComponents::backLink( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'types' ), admin_url( 'admin.php' ) ) ); ?>
    <?php ob_start(); ?>
      <form method="post">
        <?php wp_nonce_field( 'ah_save_taxonomy', 'ah_tax_nonce' ); ?>
        <input type="hidden" name="type_edit_id" value="<?php echo esc_attr( $edit_type_id ); ?>">
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-bottom:16px;">
          <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'types' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary">Cancel</a>
          <button type="submit" name="save_type" value="1" class="ah-btn ah-btn-primary">Save</button>
        </div>
        <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Name *', '<input type="text" name="type_name" value="' . esc_attr( $edit_type->name ?? '' ) . '" required>' ); ?>
        <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Slug', '<input type="text" name="type_slug" value="' . esc_attr( $edit_type->slug ?? '' ) . '">' ); ?>
        <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Description', '<textarea name="type_description" rows="3">' . esc_textarea( $edit_type->description ?? '' ) . '</textarea>' ); ?>
      </form>
    <?php \Ah\Cms\Admin\Components\AdminComponents::card( $edit_type_id > 0 ? 'Edit Type' : 'Add Type', ob_get_clean() ); ?>
    <?php endif; ?>

  <?php else : ?>
    <?php \Ah\Cms\Admin\Components\AdminComponents::filterBar( array(
      'page_slug'          => 'ah-taxonomy',
      'search_placeholder' => 'Search taxonomy types…',
      'search_value'       => $type_search,
      'hidden_inputs'      => array( 'tab' => 'types' ),
      'add_url'            => add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'types', 'edit_type' => '0' ), admin_url( 'admin.php' ) ),
      'add_label'          => '+ Add Type',
    ) ); ?>
    <?php \Ah\Cms\Admin\Components\AdminComponents::dataTable( array(
      'columns' => array(
        array( 'label' => 'Name', 'render' => function ( $t ) {
          $html = esc_html( $t->name );
          if ( $t->slug === 'data-protected' ) $html .= ' &#128274;';
          return $html;
        } ),
        array( 'label' => 'Slug', 'render' => function ( $t ) {
          return '<code>' . esc_html( $t->slug ) . '</code>';
        } ),
      ),
      'items' => $types,
      'empty_message' => 'No taxonomy types yet.',
      'actions' => function ( $t ) {
        if ( $t->slug === 'data-protected' ) return '';
        $edit_url = add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'types', 'edit_type' => $t->id ), admin_url( 'admin.php' ) );
        $del_url  = wp_nonce_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'types', 'delete_type_id' => $t->id ), admin_url( 'admin.php' ) ), 'ah_del_type' );
        $html = '<a href="' . esc_url( $edit_url ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>';
        ob_start();
        \Ah\Cms\Admin\Components\AdminComponents::confirmDelete( $del_url, 'ah_del_type' );
        $html .= ob_get_clean();
        return $html;
      },
    ) ); ?>
  <?php endif; ?>

  <?php else : /* Terms tab */
    $paged      = AH_Pagination::current_page();
    $search     = sanitize_text_field( $_GET['s'] ?? '' );
    $status_f   = sanitize_key( $_GET['term_status'] ?? '' );
    $parent_f   = (int) ( $_GET['parent_id'] ?? 0 );
    $result     = $model->get_paginated( $paged, $search, $type_id ?: null, $parent_f, $status_f );
    $items   = $result['items']; $meta = $result['meta'];
    $item    = $edit_id ? $model->find( $edit_id ) : null;
    $parents    = $pt_model->get_all_active();
    $parent_map = array();
    foreach ( $parents as $par ) { $parent_map[ (int) $par->id ] = $par; }
  ?>

  <?php if ( $action === 'add' || $action === 'edit' ) : ?>
    <?php \Ah\Cms\Admin\Components\AdminComponents::backLink( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'terms' ), admin_url( 'admin.php' ) ) ); ?>
    <?php ob_start(); ?>
      <div style="display:flex;justify-content:flex-end;gap:8px;margin-bottom:16px;">
        <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'terms' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary">Cancel</a>
        <button type="submit" name="save_term" value="1" class="ah-btn ah-btn-primary">Save</button>
      </div>
      <form method="post">
        <?php wp_nonce_field( 'ah_save_taxonomy', 'ah_tax_nonce' ); ?>
        <div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;">
        <!-- Left: Text fields -->
        <div>
        <?php
        $type_select = '<select name="type_id" id="term-type" required><option value="">- Select Type -</option>';
        foreach ( $types as $t ) { $type_select .= '<option value="' . esc_attr( $t->id ) . '"' . selected( $item->type_id ?? $type_id, $t->id, false ) . '>' . esc_html( $t->name ) . '</option>'; }
        $type_select .= '</select>';
        ?>
        <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Type *', $type_select ); ?>
        <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Name *', '<input type="text" name="name" value="' . esc_attr( $item->name ?? '' ) . '" class="ah-generate-slug-source" data-slug-target="#term-slug" required>' ); ?>
        <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Slug', '<input type="text" name="slug" id="term-slug" value="' . esc_attr( $item->slug ?? '' ) . '" class="ah-slug-field">' ); ?>
        <?php
        $parent_select = '<select name="parent_term_id"><option value="">- No group -</option>';
        foreach ( $parents as $par ) { $parent_select .= '<option value="' . esc_attr( $par->id ) . '"' . selected( $item->parent_term_id ?? 0, $par->id, false ) . '>' . esc_html( ( $par->icon_emoji ? $par->icon_emoji . ' ' : '' ) . $par->name ) . '</option>'; }
        $parent_select .= '</select>';
        \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Parent Group', $parent_select );
        ?>
        <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Description', '<textarea name="description" rows="3">' . esc_textarea( $item->description ?? '' ) . '</textarea>' ); ?>
        </div>
        <!-- Right: Image + Emoji + Sort + Status -->
        <div>
          <?php $tax_img_id = (int) ( $item->image_id ?? 0 ); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::mediaField( 'image_id', 'Card Background Image / Video', $tax_img_id, array( 'type' => 'media' ) ); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::formGrid( array(
            array( 'Icon (emoji)', '<input type="text" name="icon_emoji" value="' . esc_attr( $item->icon_emoji ?? '' ) . '" placeholder="e.g. 📖" style="width:80px;font-size:1.4rem;text-align:center;">' ),
            array( 'Sort Order', '<input type="number" name="sort_order" value="' . esc_attr( $item->sort_order ?? 0 ) . '">' ),
          ) ); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Status', '<select name="status"><option value="active"' . selected( $item->status ?? 'active', 'active', false ) . '>Active</option><option value="inactive"' . selected( $item->status ?? '', 'inactive', false ) . '>Inactive</option></select>' ); ?>
        </div>
      </form>
    <?php \Ah\Cms\Admin\Components\AdminComponents::card( $item ? 'Edit Term' : 'Add Term', ob_get_clean() ); ?>

  <?php else :
    $paged      = AH_Pagination::current_page();
    $search     = sanitize_text_field( $_GET['s'] ?? '' );
    $status_f   = sanitize_key( $_GET['term_status'] ?? '' );
    $parent_f   = (int) ( $_GET['parent_id'] ?? 0 );
    $result     = $model->get_paginated( $paged, $search, $type_id ?: null, $parent_f, $status_f );
    $items   = $result['items']; $meta = $result['meta'];
    $parents    = $pt_model->get_all_active();
    $parent_map = array();
    foreach ( $parents as $par ) { $parent_map[ (int) $par->id ] = $par; }

    $type_filter_opts = array( '' => 'All Types' );
    foreach ( $types as $t ) { $type_filter_opts[ $t->id ] = $t->name; }
    $parent_filter_opts = array( '' => 'All Parent Terms' );
    foreach ( $parents as $par ) { $parent_filter_opts[ $par->id ] = $par->name; }
    \Ah\Cms\Admin\Components\AdminComponents::filterBar( array(
      'page_slug'          => 'ah-taxonomy',
      'search_placeholder' => 'Search terms…',
      'search_value'       => $search,
      'hidden_inputs'      => array( 'tab' => 'terms' ),
      'filters'            => array(
        array( 'name' => 'type_id', 'options' => $type_filter_opts, 'selected' => $type_id ),
        array( 'name' => 'parent_id', 'options' => $parent_filter_opts, 'selected' => $parent_f ),
        array( 'name' => 'term_status', 'options' => array( '' => 'All Statuses', 'active' => 'Active', 'inactive' => 'Inactive' ), 'selected' => $status_f ),
      ),
      'add_url'   => add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'terms', 'action' => 'add' ), admin_url( 'admin.php' ) ),
      'add_label' => '+ Add Term',
    ) ); ?>
    <?php \Ah\Cms\Admin\Components\AdminComponents::dataTable( array(
      'columns' => array(
        array( 'label' => 'Name', 'render' => function ( $term ) {
          $html = esc_html( $term->name );
          if ( ! empty( $term->is_protected ) ) $html .= ' &#128274;';
          return $html;
        } ),
        array( 'label' => 'Slug', 'render' => function ( $term ) { return '<code>' . esc_html( $term->slug ) . '</code>'; } ),
        array( 'label' => 'Type', 'render' => function ( $term ) use ( $types ) {
          $t_type = null;
          foreach ( $types as $tt ) { if ( $tt->id == $term->type_id ) { $t_type = $tt; break; } }
          return esc_html( $t_type->name ?? '-' );
        } ),
        array( 'label' => 'Group', 'render' => function ( $term ) use ( $parent_map ) {
          $pt_obj = isset( $term->parent_term_id ) ? ( $parent_map[ (int) $term->parent_term_id ] ?? null ) : null;
          if ( $pt_obj ) {
            $dot = ! empty( $pt_obj->color ) ? $pt_obj->color : '#94a3b8';
            return '<span style="display:inline-flex;align-items:center;gap:6px;white-space:nowrap;"><span style="flex-shrink:0;width:10px;height:10px;border-radius:50%;background:' . esc_attr( $dot ) . ';"></span>' . esc_html( ( $pt_obj->icon_emoji ? $pt_obj->icon_emoji . ' ' : '' ) . $pt_obj->name ) . '</span>';
          }
          return '<small style="opacity:.4;">-</small>';
        } ),
        array( 'label' => 'Status', 'render' => function ( $term ) {
          return \Ah\Cms\Admin\Components\AdminComponents::statusBadge( $term->status );
        } ),
      ),
      'items' => $items,
      'empty_message' => 'No terms found.',
      'actions' => function ( $term ) {
        if ( ! empty( $term->is_protected ) ) return '';
        $edit_url = add_query_arg( array( 'page' => 'ah-taxonomy', 'tab' => 'terms', 'action' => 'edit', 'id' => $term->id ), admin_url( 'admin.php' ) );
        $del_url  = add_query_arg( array( 'page' => 'ah-taxonomy', 'delete_id' => $term->id ), admin_url( 'admin.php' ) );
        $html = '<a href="' . esc_url( $edit_url ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>';
        ob_start();
        \Ah\Cms\Admin\Components\AdminComponents::confirmDelete( $del_url, 'ah_del_tax' );
        $html .= ob_get_clean();
        return $html;
      },
    ) ); ?>
    <?php echo AH_Pagination::render( $meta ); ?>
  <?php endif; ?>
  <?php endif; ?>
</div>
