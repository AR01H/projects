<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$model   = new AH_Faqs_Model();
$pages_m = new AH_Pages_Model();
$notice  = '';
$action  = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id = (int) ( $_GET['id'] ?? 0 );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	if ( ! wp_verify_nonce( $_POST['ah_faqs_nonce'] ?? '', 'ah_save_faq' ) ) wp_die( 'Security.' );

	// One combined dropdown ("page_attach") drives both columns: "pid:{id}" =
	// a registered AH_Pages_Model page, "slug" = the free-text slug field below,
	// empty = Global. Only one of page_id/attached_slug is ever set.
	$attach     = sanitize_text_field( wp_unslash( $_POST['page_attach'] ?? '' ) );
	$attach_pid = null;
	$attach_slug = null;
	if ( 0 === strpos( $attach, 'pid:' ) ) {
		$attach_pid = (int) substr( $attach, 4 ) ?: null;
	} elseif ( 'slug' === $attach ) {
		$attach_slug = sanitize_title( wp_unslash( $_POST['attached_slug_value'] ?? '' ) ) ?: null;
	}

	$data = array(
		'question'      => sanitize_textarea_field( $_POST['question'] ?? '' ),
		'answer'        => wp_kses_post( $_POST['answer'] ?? '' ),
		'link_text'     => sanitize_text_field( $_POST['link_text'] ?? '' ),
		'link_url'      => esc_url_raw( $_POST['link_url'] ?? '' ),
		'page_id'       => $attach_pid,
		'attached_slug' => $attach_slug,
		'section'       => sanitize_text_field( $_POST['section'] ?? '' ) ?: null,
		'sort_order'    => (int) ( $_POST['sort_order'] ?? 0 ),
		'status'        => sanitize_key( $_POST['status'] ?? 'active' ),
		'created_by'    => get_current_user_id() ?: null,
	);
	if ( $edit_id ) {
		$model->update( $edit_id, $data );
	} else {
		$model->create( $data );
	}

	$notice = 'FAQ saved.';
	$action = 'list';
	do_action( 'ah_faqs_changed' );
}

if ( isset( $_GET['delete_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_del_faq' ) ) {
	$model->delete( (int) $_GET['delete_id'] );
	$notice = 'FAQ deleted.';
	do_action( 'ah_faqs_changed' );
}

$all_pages = $pages_m->get_active();
?>
<?php if ( $action === 'list' ) :
    $search   = sanitize_text_field( $_GET['s'] ?? '' );
    $page_id  = (int) ( $_GET['page_id'] ?? 0 ) ?: null;
    $section  = sanitize_text_field( $_GET['section'] ?? '' );
    $paged    = AH_Pagination::current_page();
    $result   = $model->get_paginated( $paged, $search, $page_id, null, $section );
    $items    = $result['items']; $meta = $result['meta'];
    $sections = $model->get_distinct_sections();

    $page_options = array( '' => 'All Pages', '0' => 'Global' );
    foreach ( $all_pages as $pg ) {
      $page_options[ $pg->id ] = $pg->title;
    }
    $section_options = array( '' => 'All Sections' );
    foreach ( $sections as $s ) {
      $section_options[ $s ] = $s;
    }

    \Ah\Cms\Admin\Components\AdminComponents::listPage( array(
      'icon'        => 'editor-help',
      'title'       => 'FAQs',
      'description' => 'Build FAQ entries organised by page, section, and status.',
      'notice'      => $notice,
      'notice_type' => 'success',
      'filter_bar'  => array(
        'page_slug'          => 'ah-faqs',
        'search_placeholder' => 'Search FAQs…',
        'search_value'       => $search,
        'filters'            => array(
          array(
            'name'     => 'page_id',
            'options'  => $page_options,
            'selected' => sanitize_text_field( $_GET['page_id'] ?? '' ),
          ),
          array(
            'name'     => 'section',
            'options'  => $section_options,
            'selected' => $section,
            'show_if'  => ! empty( $sections ),
          ),
        ),
        'add_url'   => add_query_arg( array( 'page' => 'ah-faqs', 'action' => 'add' ), admin_url( 'admin.php' ) ),
        'add_label' => '+ Add FAQ',
      ),
      'table' => array(
        'columns' => array(
          array( 'label' => 'Question', 'render' => function ( $faq ) {
            return esc_html( wp_trim_words( $faq->question, 12 ) );
          } ),
          array( 'label' => 'Page', 'render' => function ( $faq ) use ( $pages_m ) {
            if ( $faq->page_id ) {
              $pg = $pages_m->find( (int) $faq->page_id );
              return $pg ? esc_html( $pg->title ) : '-';
            } elseif ( ! empty( $faq->attached_slug ) ) {
              return '<em>Slug: ' . esc_html( $faq->attached_slug ) . '</em>';
            }
            return '<em>Global</em>';
          } ),
          array( 'label' => 'Section', 'render' => function ( $faq ) {
            return $faq->section
              ? '<span class="ah-badge">' . esc_html( $faq->section ) . '</span>'
              : '<span style="color:var(--ah-muted);font-size:12px;">-</span>';
          } ),
          array( 'label' => 'Status', 'render' => function ( $faq ) {
            return \Ah\Cms\Admin\Components\AdminComponents::statusBadge( $faq->status );
          } ),
        ),
        'items'    => $items,
        'sortable' => true,
        'model'    => 'faqs',
        'actions'  => function ( $faq ) {
          $edit_url = add_query_arg( array( 'page' => 'ah-faqs', 'action' => 'edit', 'id' => $faq->id ), admin_url( 'admin.php' ) );
          $del_url  = wp_nonce_url( add_query_arg( array( 'page' => 'ah-faqs', 'delete_id' => $faq->id ), admin_url( 'admin.php' ) ), 'ah_del_faq' );
          return '<a href="' . esc_url( $edit_url ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>'
               . '<a href="' . esc_url( $del_url ) . '" class="ah-btn ah-btn-danger ah-btn-sm ah-confirm-delete" data-title="Delete FAQ" data-confirm="This FAQ will be permanently removed.">Delete</a>';
        },
      ),
      'pagination' => $meta,
    ) ); ?>

  <?php else :
    $item = $edit_id ? $model->find( $edit_id ) : null;

    $current_attach = '';
    if ( ! empty( $item->page_id ) ) {
      $current_attach = 'pid:' . (int) $item->page_id;
    } elseif ( ! empty( $item->attached_slug ) ) {
      $current_attach = 'slug';
    }
  ?>
  <div class="wrap ah-wrap">
    <?php \Ah\Cms\Admin\Components\AdminComponents::pageHeader( 'editor-help', 'FAQs', 'Build FAQ entries organised by page, section, and status.' ); ?>
    <?php \Ah\Cms\Admin\Components\AdminComponents::notice( $notice, 'success' ); ?>
    <?php \Ah\Cms\Admin\Components\AdminComponents::backLink( admin_url( 'admin.php?page=ah-faqs' ), '← Back' ); ?>
    <?php ob_start(); ?>
      <form method="post">
        <?php wp_nonce_field( 'ah_save_faq', 'ah_faqs_nonce' ); ?>
        <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Question *', '<textarea name="question" rows="3" required>' . esc_textarea( $item->question ?? '' ) . '</textarea>' ); ?>
        <?php
        ob_start();
        wp_editor( $item->answer ?? '', 'answer', array( 'textarea_name' => 'answer', 'media_buttons' => false, 'teeny' => true, 'editor_height' => 200 ) );
        \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Answer *', ob_get_clean() );
        ?>
        <?php \Ah\Cms\Admin\Components\AdminComponents::formGrid( array(
          array( 'Link Text', '<input type="text" name="link_text" value="' . esc_attr( $item->link_text ?? '' ) . '">' ),
          array( 'Link URL', '<input type="text" name="link_url" value="' . esc_attr( $item->link_url ?? '' ) . '" placeholder="https://… or /slug/ or #section">' ),
        ) ); ?>
        <?php
        $attach_select = '<select name="page_attach" id="ah-faq-attach">';
        $attach_select .= '<option value="">- Global -</option>';
        $attach_select .= '<optgroup label="Registered Pages">';
        foreach ( $all_pages as $pg ) {
          $attach_select .= '<option value="pid:' . esc_attr( $pg->id ) . '"' . selected( $current_attach, 'pid:' . $pg->id, false ) . '>' . esc_html( $pg->title ) . '</option>';
        }
        $attach_select .= '</optgroup>';
        $attach_select .= '<option value="slug"' . selected( $current_attach, 'slug', false ) . '>Slug Based (enter below)</option>';
        $attach_select .= '</select>';
        \Ah\Cms\Admin\Components\AdminComponents::formGrid( array(
          array(
            'Section <small>(e.g. Common Questions, Buying Questions)</small>',
            '<input type="text" name="section" value="' . esc_attr( $item->section ?? '' ) . '" placeholder="Leave empty for no section">',
          ),
          array(
            'Attached To <small>(leave empty = global)</small>',
            $attach_select,
          ),
        ) );
        ?>
        <?php \Ah\Cms\Admin\Components\AdminComponents::formRow(
          'Slug <small>(any page/category/topic URL slug, e.g. "ask-an-expert" or "buying")</small>',
          '<input type="text" name="attached_slug_value" value="' . esc_attr( $item->attached_slug ?? '' ) . '" placeholder="e.g. ask-an-expert">',
          '',
          'ah-faq-slug-row'
        ); ?>
        <?php \Ah\Cms\Admin\Components\AdminComponents::formGrid( array(
          array( 'Sort Order', '<input type="number" name="sort_order" value="' . esc_attr( $item->sort_order ?? 0 ) . '">' ),
          array( 'Status', '<select name="status"><option value="active"' . selected( $item->status ?? 'active', 'active', false ) . '>Active</option><option value="inactive"' . selected( $item->status ?? '', 'inactive', false ) . '>Inactive</option></select>' ),
        ) ); ?>
        <button type="submit" class="ah-btn ah-btn-primary">Save FAQ</button>
      </form>
    <?php \Ah\Cms\Admin\Components\AdminComponents::card( $item ? 'Edit FAQ' : 'Add FAQ', ob_get_clean() ); ?>
    <script>
    jQuery(function ($) {
      function syncFaqSlugRow() {
        $('#ah-faq-slug-row').toggle( $('#ah-faq-attach').val() === 'slug' );
      }
      $('#ah-faq-attach').on('change', syncFaqSlugRow);
      syncFaqSlugRow();
    });
    </script>
  <?php endif; ?>
</div>
