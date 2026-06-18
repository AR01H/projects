<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$model         = new AH_Newsbar_Model();
$content_tax_m = new AH_Content_Taxonomy_Model();
$notice        = '';
$action        = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id       = (int) ( $_GET['id'] ?? 0 );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	if ( ! wp_verify_nonce( $_POST['ah_newsbar_nonce'] ?? '', 'ah_save_newsbar' ) ) wp_die( 'Security.' );
	$data = array(
		'label'      => sanitize_text_field( $_POST['label'] ?? '' ),
		'text'       => sanitize_text_field( $_POST['text'] ?? '' ),
		'excerpt'    => sanitize_text_field( $_POST['excerpt'] ?? '' ),
		'content'    => wp_kses_post( $_POST['content'] ?? '' ),
		'image_id'   => (int) ( $_POST['image_id'] ?? 0 ) ?: null,
		'link_url'   => esc_url_raw( $_POST['link_url'] ?? '' ),
		'link_target'=> in_array( $_POST['link_target'] ?? '_self', array( '_self', '_blank' ), true ) ? $_POST['link_target'] : '_self',
		'status'     => sanitize_key( $_POST['status'] ?? 'active' ),
		'sort_order' => (int) ( $_POST['sort_order'] ?? 0 ),
		'start_date' => sanitize_text_field( $_POST['start_date'] ?? '' ) ?: null,
		'end_date'   => sanitize_text_field( $_POST['end_date'] ?? '' ) ?: null,
		'created_by' => get_current_user_id() ?: null,
	);
	if ( $edit_id ) {
		$model->update( $edit_id, $data );
		$saved_id = $edit_id;
	} else {
		$saved_id = (int) $model->create( $data );
	}
	if ( $saved_id ) {
		$content_tax_m->sync_terms( 'news_bar_item', $saved_id, $_POST['taxonomy_ids'] ?? array() );
	}
	$notice = 'News bar item saved.';
	$action = 'list';
}

if ( isset( $_GET['delete_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_del_newsbar' ) ) {
	$delete_id = (int) $_GET['delete_id'];
	$model->delete( $delete_id );
	$content_tax_m->sync_terms( 'news_bar_item', $delete_id, array() );
	$notice = 'Item deleted.';
}
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-megaphone"></span> <?php esc_html_e( 'News Bar', 'ah-theme' ); ?></h1>
  <?php if ( $notice ) : ?><div class="ah-notice ah-notice-success"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

  <?php if ( $action === 'list' ) :
    $paged  = AH_Pagination::current_page();
    $result = $model->get_paginated( $paged );
    $items  = $result['items']; $meta = $result['meta'];
  ?>
    <div class="ah-table-top">
      <span></span>
      <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-news-bar', 'action' => 'add' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-primary">+ Add Item</a>
    </div>
    <div class="ah-table-wrap">
      <table class="ah-table ah-sortable-list" data-model="news_bar_items">
        <thead><tr><th></th><th>Label</th><th>Title</th><th>Link</th><th>Dates</th><th>CMS Terms</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ( $items as $item ) : ?>
            <tr data-id="<?php echo esc_attr( $item->id ); ?>">
              <td class="ah-sort-handle">&#9776;</td>
              <td><small><?php echo esc_html( $item->label ?? '' ); ?></small></td>
              <td><?php echo esc_html( $item->text ); ?></td>
              <td><small><?php echo esc_html( $item->link_url ); ?></small></td>
              <td><small><?php echo esc_html( ( $item->start_date ?: '∞' ) . ' → ' . ( $item->end_date ?: '∞' ) ); ?></small></td>
              <td><?php $content_tax_m->render_badges( 'news_bar_item', (int) $item->id ); ?></td>
              <td><span class="ah-badge ah-badge-<?php echo esc_attr( $item->status ); ?>"><?php echo esc_html( $item->status ); ?></span></td>
              <td class="row-actions">
                <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-news-bar', 'action' => 'edit', 'id' => $item->id ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>
                <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-news-bar', 'delete_id' => $item->id ), admin_url( 'admin.php' ) ), 'ah_del_newsbar' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm" onclick="return confirm('Delete?');">Delete</a>
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
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-news-bar' ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm" style="margin-bottom:14px;display:inline-flex;">&larr; Back</a>
    <div class="ah-card">
      <div class="ah-card-header"><h2><?php echo $item ? 'Edit Item' : 'Add Item'; ?></h2></div>
      <form method="post">
        <?php wp_nonce_field( 'ah_save_newsbar', 'ah_newsbar_nonce' ); ?>

        <div class="ah-form-row" style="display:grid;grid-template-columns:1fr 2fr;gap:12px;">
          <div>
            <label>Label <small style="color:var(--ah-muted);">(e.g. "Market Update")</small></label>
            <input type="text" name="label" value="<?php echo esc_attr( $item->label ?? '' ); ?>" placeholder="Category label">
          </div>
          <div>
            <label>Title *</label>
            <input type="text" name="text" value="<?php echo esc_attr( $item->text ?? '' ); ?>" required>
          </div>
        </div>

        <div class="ah-form-row">
          <label>Short Description <small style="color:var(--ah-muted);">(excerpt shown on listing cards)</small></label>
          <textarea name="excerpt" rows="2" style="width:100%;resize:vertical;"><?php echo esc_textarea( $item->excerpt ?? '' ); ?></textarea>
        </div>

        <div class="ah-form-row">
          <label>Full Content</label>
          <?php
          wp_editor( $item->content ?? '', 'newsbar_content', array(
	          'textarea_name' => 'content',
	          'media_buttons' => true,
	          'teeny'         => false,
	          'textarea_rows' => 8,
	          'quicktags'     => true,
          ) );
          ?>
        </div>

        <div class="ah-form-row">
          <label>Thumbnail Image</label>
          <?php
            $nb_img_id  = (int) ( $item->image_id ?? 0 );
            $nb_img_url = $nb_img_id ? ( wp_get_attachment_image_url( $nb_img_id, 'medium' ) ?: '' ) : '';
          ?>
          <div style="max-width:240px;">
            <div class="ah-image-picker<?php echo $nb_img_url ? ' has-image' : ''; ?>">
              <img src="<?php echo esc_url( $nb_img_url ); ?>" class="ah-image-preview<?php echo $nb_img_url ? ' visible' : ''; ?>" alt="">
              <div class="ah-image-picker-btns">
                <input type="hidden" class="ah-image-id" name="image_id" value="<?php echo esc_attr( $nb_img_id ); ?>">
                <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-pick-image">Set Image</button>
                <button type="button" class="ah-btn ah-btn-sm ah-remove-image" style="color:var(--ah-danger);">Remove</button>
              </div>
            </div>
          </div>
        </div>

        <div class="ah-form-row"><label>Link URL</label><input type="text" name="link_url" value="<?php echo esc_attr( $item->link_url ?? '' ); ?>"></div>
        <div class="ah-form-row">
          <label>Open In</label>
          <select name="link_target">
            <option value="_self" <?php selected( $item->link_target ?? '_self', '_self' ); ?>>Same Tab</option>
            <option value="_blank" <?php selected( $item->link_target ?? '', '_blank' ); ?>>New Tab</option>
          </select>
        </div>
        <div class="ah-form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div><label>Start Date</label><input type="date" name="start_date" value="<?php echo esc_attr( $item->start_date ?? '' ); ?>"></div>
          <div><label>End Date</label><input type="date" name="end_date" value="<?php echo esc_attr( $item->end_date ?? '' ); ?>"></div>
        </div>
        <div class="ah-form-row"><label>Sort Order</label><input type="number" name="sort_order" value="<?php echo esc_attr( $item->sort_order ?? 0 ); ?>"></div>
        <div class="ah-form-row">
          <label>Status</label>
          <select name="status">
            <option value="active" <?php selected( $item->status ?? 'active', 'active' ); ?>>Active</option>
            <option value="inactive" <?php selected( $item->status ?? '', 'inactive' ); ?>>Inactive</option>
          </select>
        </div>
        <div class="ah-form-row">
          <label>Taxonomy Terms</label>
          <?php $content_tax_m->render_picker( 'news_bar_item', $edit_id ); ?>
        </div>
        <button type="submit" class="ah-btn ah-btn-primary">Save Item</button>
      </form>
    </div>
  <?php endif; ?>
</div>
