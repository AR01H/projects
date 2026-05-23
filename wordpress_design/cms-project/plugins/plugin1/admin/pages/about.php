<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$model   = new AH_About_Model();
$team_m  = new AH_Team_Model();
$pages_m = new AH_Pages_Model();
$media_m = new AH_Media_Model();
$notice  = '';

$about_page = $pages_m->get_by_type( 'about' );
$page_id    = $about_page ? (int) $about_page->id : 0;
$tab        = sanitize_key( $_GET['tab'] ?? 'header' );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && wp_verify_nonce( $_POST['ah_about_nonce'] ?? '', 'ah_save_about' ) ) {

	if ( isset( $_POST['save_header'] ) ) {
		$model->save_page_header( $page_id, array(
			'heading'    => sanitize_text_field( $_POST['heading'] ?? '' ),
			'information'=> sanitize_textarea_field( $_POST['information'] ?? '' ),
			'is_visible' => (int) ( $_POST['is_visible'] ?? 1 ),
		) );
		$notice = 'Header saved.';
	}

	if ( isset( $_POST['save_story'] ) ) {
		$story_id = $model->save_story( $page_id, array(
			'image_id'   => (int) ( $_POST['image_id'] ?? 0 ) ?: null,
			'heading'    => sanitize_text_field( $_POST['heading'] ?? '' ),
			'subheading' => sanitize_text_field( $_POST['subheading'] ?? '' ),
			'is_visible' => (int) ( $_POST['is_visible'] ?? 1 ),
		) );
		$points = array_filter( array_map( 'sanitize_text_field', $_POST['story_points'] ?? array() ) );
		$model->save_story_points( $story_id, $points );
		$notice = 'Story saved.';
	}

	if ( isset( $_POST['save_value'] ) ) {
		$val_id  = (int) ( $_POST['value_id'] ?? 0 );
		$val_data = array(
			'page_id'    => $page_id,
			'image_id'   => (int) ( $_POST['image_id'] ?? 0 ) ?: null,
			'heading'    => sanitize_text_field( $_POST['heading'] ?? '' ),
			'information'=> sanitize_textarea_field( $_POST['information'] ?? '' ),
			'sort_order' => (int) ( $_POST['sort_order'] ?? 0 ),
			'status'     => sanitize_key( $_POST['status'] ?? 'active' ),
		);
		$val_id ? $model->save_value( $val_data, $val_id ) : $model->save_value( $val_data );
		$notice = 'Value card saved.';
	}
}

if ( isset( $_GET['delete_value'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_del_value' ) ) {
	$model->delete_value( (int) $_GET['delete_value'] );
	$notice = 'Value card deleted.';
}

$header = $page_id ? $model->get_page_header( $page_id ) : null;
$story  = $page_id ? $model->get_story( $page_id ) : null;
$points = $story ? $model->get_story_points( (int) $story->id ) : array();
$values = $page_id ? $model->get_values( $page_id ) : array();
$edit_value_id = (int) ( $_GET['edit_value'] ?? 0 );
$edit_value    = $edit_value_id ? AH_DB_Helper::get_row( AH_DB_Helper::table( 'about_values' ), $edit_value_id ) : null;
$story_img_url = $story && $story->image_id ? ( wp_get_attachment_image_url( (int) $story->image_id, 'medium' ) ?: '' ) : '';
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-info"></span> <?php esc_html_e( 'About Page', 'ah-theme' ); ?></h1>
  <?php if ( $notice ) : ?><div class="ah-notice ah-notice-success"><?php echo esc_html( $notice ); ?></div><?php endif; ?>
  <?php if ( ! $page_id ) : ?><div class="ah-notice ah-notice-warning">About page not found. Create it in Pages Manager first.</div><?php return; endif; ?>

  <div class="ah-tabs">
    <?php foreach ( array( 'header' => 'Page Header', 'story' => 'Our Story', 'values' => 'Values' ) as $t => $label ) : ?>
      <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-about', 'tab' => $t ), admin_url( 'admin.php' ) ) ); ?>" class="ah-tab <?php echo $tab === $t ? 'active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
    <?php endforeach; ?>
  </div>

  <?php if ( $tab === 'header' ) : ?>
    <div class="ah-card">
      <div class="ah-card-header"><h2>About Page Header</h2></div>
      <form method="post">
        <?php wp_nonce_field( 'ah_save_about', 'ah_about_nonce' ); ?>
        <div class="ah-form-row"><label>Heading</label><input type="text" name="heading" value="<?php echo esc_attr( $header->heading ?? '' ); ?>"></div>
        <div class="ah-form-row"><label>Information</label><textarea name="information" rows="5"><?php echo esc_textarea( $header->information ?? '' ); ?></textarea></div>
        <div class="ah-form-row"><label>Visible</label><select name="is_visible"><option value="1" <?php selected( $header->is_visible ?? 1, 1 ); ?>>Yes</option><option value="0" <?php selected( $header->is_visible ?? 1, 0 ); ?>>No</option></select></div>
        <button type="submit" name="save_header" value="1" class="ah-btn ah-btn-primary">Save Header</button>
      </form>
    </div>

  <?php elseif ( $tab === 'story' ) : ?>
    <div class="ah-card">
      <div class="ah-card-header"><h2>Our Story Section</h2></div>
      <form method="post">
        <?php wp_nonce_field( 'ah_save_about', 'ah_about_nonce' ); ?>
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
          <div>
            <div class="ah-form-row"><label>Heading</label><input type="text" name="heading" value="<?php echo esc_attr( $story->heading ?? '' ); ?>"></div>
            <div class="ah-form-row"><label>Sub-heading</label><input type="text" name="subheading" value="<?php echo esc_attr( $story->subheading ?? '' ); ?>"></div>
            <div class="ah-form-row">
              <label>Story Points (bullet points)</label>
              <div class="ah-repeater-container">
                <?php
                $pts_to_show = $points ?: array( (object) array( 'point_text' => '' ) );
                foreach ( $pts_to_show as $pt ) : ?>
                  <div class="ah-repeater-item" style="display:flex;gap:8px;align-items:center;padding:8px;margin-bottom:6px;">
                    <span class="ah-sort-handle">&#9776;</span>
                    <input type="text" name="story_points[]" value="<?php echo esc_attr( $pt->point_text ); ?>" style="flex:1;" placeholder="Bullet point text">
                    <button type="button" class="ah-repeater-remove">✕</button>
                  </div>
                <?php endforeach; ?>
              </div>
              <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-add-repeater">+ Add Point</button>
            </div>
            <div class="ah-form-row"><label>Visible</label><select name="is_visible"><option value="1" <?php selected( $story->is_visible ?? 1, 1 ); ?>>Yes</option><option value="0">No</option></select></div>
          </div>
          <div>
            <div class="ah-form-row">
              <label>Story Image</label>
              <div class="ah-image-picker">
                <img src="<?php echo esc_url( $story_img_url ); ?>" class="ah-image-preview <?php echo $story_img_url ? 'visible' : ''; ?>" alt="" style="width:100%;aspect-ratio:16/9;height:auto;object-fit:cover;">
                <div class="ah-image-picker-btns">
                  <input type="hidden" class="ah-image-id" name="image_id" value="<?php echo esc_attr( $story->image_id ?? 0 ); ?>">
                  <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-pick-image">Choose Image</button>
                  <button type="button" class="ah-btn ah-btn-sm ah-remove-image" style="color:var(--ah-danger);">Remove</button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <button type="submit" name="save_story" value="1" class="ah-btn ah-btn-primary">Save Story</button>
      </form>
    </div>

  <?php elseif ( $tab === 'values' ) : ?>
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">
      <!-- Values list -->
      <div>
        <div class="ah-table-wrap">
          <table class="ah-table ah-sortable-list" data-model="about_values">
            <thead><tr><th></th><th>Heading</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ( $values as $val ) : ?>
                <tr data-id="<?php echo esc_attr( $val->id ); ?>">
                  <td class="ah-sort-handle">&#9776;</td>
                  <td><?php echo esc_html( $val->heading ); ?></td>
                  <td><span class="ah-badge ah-badge-<?php echo esc_attr( $val->status ); ?>"><?php echo esc_html( $val->status ); ?></span></td>
                  <td class="row-actions">
                    <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-about', 'tab' => 'values', 'edit_value' => $val->id ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>
                    <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-about', 'tab' => 'values', 'delete_value' => $val->id ), admin_url( 'admin.php' ) ), 'ah_del_value' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm" onclick="return confirm('Delete?');">Delete</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <!-- Add/Edit Value -->
      <div class="ah-card">
        <div class="ah-card-header"><h2><?php echo $edit_value ? 'Edit Value Card' : 'Add Value Card'; ?></h2></div>
        <form method="post">
          <?php wp_nonce_field( 'ah_save_about', 'ah_about_nonce' ); ?>
          <input type="hidden" name="value_id" value="<?php echo esc_attr( $edit_value_id ); ?>">
          <div class="ah-form-row">
            <label>Image</label>
            <?php $val_img = $edit_value && $edit_value->image_id ? ( wp_get_attachment_image_url( (int) $edit_value->image_id, 'medium' ) ?: '' ) : ''; ?>
            <div class="ah-image-picker">
              <img src="<?php echo esc_url( $val_img ); ?>" class="ah-image-preview <?php echo $val_img ? 'visible' : ''; ?>" alt="" style="width:80px;height:60px;">
              <div class="ah-image-picker-btns">
                <input type="hidden" class="ah-image-id" name="image_id" value="<?php echo esc_attr( $edit_value->image_id ?? 0 ); ?>">
                <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-pick-image">Choose</button>
                <button type="button" class="ah-btn ah-btn-sm ah-remove-image" style="color:var(--ah-danger);">Remove</button>
              </div>
            </div>
          </div>
          <div class="ah-form-row"><label>Heading</label><input type="text" name="heading" value="<?php echo esc_attr( $edit_value->heading ?? '' ); ?>"></div>
          <div class="ah-form-row"><label>Information</label><textarea name="information" rows="4"><?php echo esc_textarea( $edit_value->information ?? '' ); ?></textarea></div>
          <div class="ah-form-row"><label>Sort Order</label><input type="number" name="sort_order" value="<?php echo esc_attr( $edit_value->sort_order ?? 0 ); ?>"></div>
          <div class="ah-form-row"><label>Status</label><select name="status"><option value="active" <?php selected( $edit_value->status ?? 'active', 'active' ); ?>>Active</option><option value="inactive" <?php selected( $edit_value->status ?? '', 'inactive' ); ?>>Inactive</option></select></div>
          <button type="submit" name="save_value" value="1" class="ah-btn ah-btn-primary">Save Value</button>
          <?php if ( $edit_value ) : ?><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-about', 'tab' => 'values' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary" style="margin-left:8px;">Cancel</a><?php endif; ?>
        </form>
      </div>
    </div>
  <?php endif; ?>
</div>
