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
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-editor-help"></span> <?php esc_html_e( 'FAQs', 'ah-theme' ); ?></h1>
  <?php if ( $notice ) : ?><div class="ah-notice ah-notice-success"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

  <?php if ( $action === 'list' ) :
    $search   = sanitize_text_field( $_GET['s'] ?? '' );
    $page_id  = (int) ( $_GET['page_id'] ?? 0 ) ?: null;
    $section  = sanitize_text_field( $_GET['section'] ?? '' );
    $paged    = AH_Pagination::current_page();
    $result   = $model->get_paginated( $paged, $search, $page_id, null, $section );
    $items    = $result['items']; $meta = $result['meta'];
    $sections = $model->get_distinct_sections();
  ?>
    <div class="ah-table-top">
      <form class="ah-search-form" method="get">
        <input type="hidden" name="page" value="ah-faqs">
        <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search FAQs…">
        <select name="page_id">
          <option value="">All Pages</option>
          <option value="0" <?php selected( $_GET['page_id'] ?? '', '0' ); ?>>Global</option>
          <?php foreach ( $all_pages as $pg ) : ?><option value="<?php echo esc_attr( $pg->id ); ?>" <?php selected( $page_id, $pg->id ); ?>><?php echo esc_html( $pg->title ); ?></option><?php endforeach; ?>
        </select>
        <?php if ( ! empty( $sections ) ) : ?>
        <select name="section">
          <option value="">All Sections</option>
          <?php foreach ( $sections as $s ) : ?>
            <option value="<?php echo esc_attr( $s ); ?>" <?php selected( $section, $s ); ?>><?php echo esc_html( $s ); ?></option>
          <?php endforeach; ?>
        </select>
        <?php endif; ?>
        <button class="ah-btn ah-btn-secondary">Filter</button>
      </form>
      <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-faqs', 'action' => 'add' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-primary">+ Add FAQ</a>
    </div>
    <div class="ah-table-wrap">
      <table class="ah-table ah-sortable-list" data-model="faqs">
        <thead><tr><th></th><th>Question</th><th>Page</th><th>Section</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ( $items as $faq ) : ?>
            <tr data-id="<?php echo esc_attr( $faq->id ); ?>">
              <td class="ah-sort-handle">&#9776;</td>
              <td><?php echo esc_html( wp_trim_words( $faq->question, 12 ) ); ?></td>
              <td><?php
                if ( $faq->page_id ) {
                  $pg = $pages_m->find( (int) $faq->page_id );
                  echo $pg ? esc_html( $pg->title ) : '-';
                } else { echo '<em>Global</em>'; }
              ?></td>
              <td><?php echo $faq->section ? '<span class="ah-badge">' . esc_html( $faq->section ) . '</span>' : '<span style="color:var(--ah-muted);font-size:12px;">-</span>'; ?></td>
              <td><span class="ah-badge ah-badge-<?php echo esc_attr( $faq->status ); ?>"><?php echo esc_html( $faq->status ); ?></span></td>
              <td class="row-actions">
                <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-faqs', 'action' => 'edit', 'id' => $faq->id ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>
                <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-faqs', 'delete_id' => $faq->id ), admin_url( 'admin.php' ) ), 'ah_del_faq' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm" onclick="return confirm('Delete?');">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php echo AH_Pagination::render( $meta ); ?>

  <?php else :
    $item = $edit_id ? $model->find( $edit_id ) : null;
  ?>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-faqs' ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm" style="margin-bottom:14px;display:inline-flex;">&larr; Back</a>
    <div class="ah-card">
      <div class="ah-card-header"><h2><?php echo $item ? 'Edit FAQ' : 'Add FAQ'; ?></h2></div>
      <form method="post">
        <?php wp_nonce_field( 'ah_save_faq', 'ah_faqs_nonce' ); ?>
        <div class="ah-form-row"><label>Question *</label><textarea name="question" rows="3" required><?php echo esc_textarea( $item->question ?? '' ); ?></textarea></div>
        <div class="ah-form-row"><label>Answer *</label><?php wp_editor( $item->answer ?? '', 'answer', array( 'textarea_name' => 'answer', 'media_buttons' => false, 'teeny' => true, 'editor_height' => 200 ) ); ?></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="ah-form-row"><label>Link Text</label><input type="text" name="link_text" value="<?php echo esc_attr( $item->link_text ?? '' ); ?>"></div>
          <div class="ah-form-row"><label>Link URL</label><input type="text" name="link_url" value="<?php echo esc_attr( $item->link_url ?? '' ); ?>" placeholder="https://… or /slug/ or #section"></div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="ah-form-row">
            <label>Section <small>(e.g. Common Questions, Buying Questions)</small></label>
            <input type="text" name="section" value="<?php echo esc_attr( $item->section ?? '' ); ?>" placeholder="Leave empty for no section">
          </div>
          <div class="ah-form-row">
            <label>Attached To <small>(leave empty = global)</small></label>
            <?php
            $current_attach = '';
            if ( ! empty( $item->page_id ) ) {
              $current_attach = 'pid:' . (int) $item->page_id;
            } elseif ( ! empty( $item->attached_slug ) ) {
              $current_attach = 'slug';
            }
            ?>
            <select name="page_attach" id="ah-faq-attach">
              <option value="">- Global -</option>
              <optgroup label="Registered Pages">
                <?php foreach ( $all_pages as $pg ) : ?><option value="pid:<?php echo esc_attr( $pg->id ); ?>" <?php selected( $current_attach, 'pid:' . $pg->id ); ?>><?php echo esc_html( $pg->title ); ?></option><?php endforeach; ?>
              </optgroup>
              <option value="slug" <?php selected( $current_attach, 'slug' ); ?>>Slug Based (enter below)</option>
            </select>
          </div>
        </div>
        <div class="ah-form-row" id="ah-faq-slug-row">
          <label>Slug <small>(any page/category/topic URL slug, e.g. "ask-an-expert" or "buying")</small></label>
          <input type="text" name="attached_slug_value" value="<?php echo esc_attr( $item->attached_slug ?? '' ); ?>" placeholder="e.g. ask-an-expert">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="ah-form-row"><label>Sort Order</label><input type="number" name="sort_order" value="<?php echo esc_attr( $item->sort_order ?? 0 ); ?>"></div>
          <div class="ah-form-row"><label>Status</label><select name="status"><option value="active" <?php selected( $item->status ?? 'active', 'active' ); ?>>Active</option><option value="inactive" <?php selected( $item->status ?? '', 'inactive' ); ?>>Inactive</option></select></div>
        </div>
        <button type="submit" class="ah-btn ah-btn-primary">Save FAQ</button>
      </form>
    </div>
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
