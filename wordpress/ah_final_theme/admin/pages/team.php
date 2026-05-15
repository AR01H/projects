<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$model   = new AH_Team_Model();
$media_m = new AH_Media_Model();
$notice  = '';
$action  = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id = (int) ( $_GET['id'] ?? 0 );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	if ( ! wp_verify_nonce( $_POST['ah_team_nonce'] ?? '', 'ah_save_team' ) ) wp_die( 'Security.' );
	$data = array(
		'photo_id'    => (int) ( $_POST['photo_id'] ?? 0 ) ?: null,
		'name'        => sanitize_text_field( $_POST['name'] ?? '' ),
		'designation' => sanitize_text_field( $_POST['designation'] ?? '' ),
		'bio'         => sanitize_textarea_field( $_POST['bio'] ?? '' ),
		'email'       => sanitize_email( $_POST['email'] ?? '' ),
		'linkedin_url'=> esc_url_raw( $_POST['linkedin_url'] ?? '' ),
		'sort_order'  => (int) ( $_POST['sort_order'] ?? 0 ),
		'is_featured' => (int) ( $_POST['is_featured'] ?? 0 ),
		'status'      => sanitize_key( $_POST['status'] ?? 'active' ),
		'created_by'  => get_current_user_id() ?: null,
	);
	$edit_id ? $model->update( $edit_id, $data ) : $model->create( $data );
	$notice = 'Team member saved.';
	$action = 'list';
}

if ( isset( $_GET['delete_id'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'ah_del_team' ) ) {
	$model->delete( (int) $_GET['delete_id'] );
	$notice = 'Member deleted.';
}
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-groups"></span> <?php esc_html_e( 'Team Members', 'ah-theme' ); ?></h1>
  <?php if ( $notice ) : ?><div class="ah-notice ah-notice-success"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

  <?php if ( $action === 'list' ) :
    $search = sanitize_text_field( $_GET['s'] ?? '' );
    $paged  = AH_Pagination::current_page();
    $result = $model->get_paginated( $paged, $search );
    $items  = $result['items']; $meta = $result['meta'];
  ?>
    <div class="ah-table-top">
      <form class="ah-search-form" method="get">
        <input type="hidden" name="page" value="ah-team">
        <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search team…">
        <button class="ah-btn ah-btn-secondary">Search</button>
      </form>
      <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-team', 'action' => 'add' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-primary">+ Add Member</a>
    </div>
    <div class="ah-table-wrap">
      <table class="ah-table ah-sortable-list" data-model="team_members">
        <thead><tr><th></th><th>Photo</th><th>Name</th><th>Designation</th><th>Featured</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ( $items as $member ) :
            $photo = $member->photo_id ? $media_m->get_url( (int) $member->photo_id ) : '';
          ?>
            <tr data-id="<?php echo esc_attr( $member->id ); ?>">
              <td class="ah-sort-handle">&#9776;</td>
              <td><?php if ( $photo ) : ?><img src="<?php echo esc_url( $photo ); ?>" alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover;"><?php else : ?>—<?php endif; ?></td>
              <td><strong><?php echo esc_html( $member->name ); ?></strong></td>
              <td><?php echo esc_html( $member->designation ); ?></td>
              <td><?php echo $member->is_featured ? '<span class="ah-badge ah-badge-active">Yes</span>' : '-'; ?></td>
              <td><span class="ah-badge ah-badge-<?php echo esc_attr( $member->status ); ?>"><?php echo esc_html( $member->status ); ?></span></td>
              <td class="row-actions">
                <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-team', 'action' => 'edit', 'id' => $member->id ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>
                <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-team', 'delete_id' => $member->id ), admin_url( 'admin.php' ) ), 'ah_del_team' ) ); ?>" class="ah-btn ah-btn-danger ah-btn-sm" onclick="return confirm('Delete?');">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php echo AH_Pagination::render( $meta ); ?>

  <?php else :
    $item    = $edit_id ? $model->find( $edit_id ) : null;
    $img_url = $item && $item->photo_id ? $media_m->get_url( (int) $item->photo_id ) : '';
  ?>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-team' ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm" style="margin-bottom:14px;display:inline-flex;">&larr; Back</a>
    <div class="ah-card">
      <div class="ah-card-header"><h2><?php echo $item ? 'Edit Member' : 'Add Member'; ?></h2></div>
      <form method="post">
        <?php wp_nonce_field( 'ah_save_team', 'ah_team_nonce' ); ?>
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
          <div>
            <div class="ah-form-row"><label>Full Name *</label><input type="text" name="name" value="<?php echo esc_attr( $item->name ?? '' ); ?>" required></div>
            <div class="ah-form-row"><label>Designation</label><input type="text" name="designation" value="<?php echo esc_attr( $item->designation ?? '' ); ?>"></div>
            <div class="ah-form-row"><label>Bio / Passage</label><textarea name="bio" rows="5"><?php echo esc_textarea( $item->bio ?? '' ); ?></textarea></div>
            <div class="ah-form-row"><label>Email</label><input type="email" name="email" value="<?php echo esc_attr( $item->email ?? '' ); ?>"></div>
            <div class="ah-form-row"><label>LinkedIn URL</label><input type="url" name="linkedin_url" value="<?php echo esc_attr( $item->linkedin_url ?? '' ); ?>"></div>
          </div>
          <div>
            <div class="ah-form-row">
              <label>Photo</label>
              <div class="ah-image-picker">
                <img src="<?php echo esc_url( $img_url ); ?>" class="ah-image-preview <?php echo $img_url ? 'visible' : ''; ?>" alt="" style="width:100px;height:100px;border-radius:50%;object-fit:cover;">
                <div class="ah-image-picker-btns">
                  <input type="hidden" class="ah-image-id" name="photo_id" value="<?php echo esc_attr( $item->photo_id ?? 0 ); ?>">
                  <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-pick-image">Choose Photo</button>
                  <button type="button" class="ah-btn ah-btn-sm ah-remove-image" style="color:var(--ah-danger);">Remove</button>
                </div>
              </div>
            </div>
            <div class="ah-form-row"><label>Sort Order</label><input type="number" name="sort_order" value="<?php echo esc_attr( $item->sort_order ?? 0 ); ?>"></div>
            <div class="ah-form-row"><label>Featured</label><select name="is_featured"><option value="0" <?php selected( $item->is_featured ?? 0, 0 ); ?>>No</option><option value="1" <?php selected( $item->is_featured ?? 0, 1 ); ?>>Yes</option></select></div>
            <div class="ah-form-row"><label>Status</label><select name="status"><option value="active" <?php selected( $item->status ?? 'active', 'active' ); ?>>Active</option><option value="inactive" <?php selected( $item->status ?? '', 'inactive' ); ?>>Inactive</option></select></div>
          </div>
        </div>
        <button type="submit" class="ah-btn ah-btn-primary">Save Member</button>
      </form>
    </div>
  <?php endif; ?>
</div>
