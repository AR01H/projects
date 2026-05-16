<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$notice  = '';
$action  = sanitize_key( $_GET['action'] ?? 'list' );

// Trash via GET
if ( isset( $_GET['trash_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_trash_post' ) ) {
	wp_trash_post( (int) $_GET['trash_id'] );
	$notice = 'Post moved to trash.';
}

$paged    = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
$search   = sanitize_text_field( $_GET['s'] ?? '' );
$status_f = sanitize_key( $_GET['status'] ?? '' );
$type_f   = sanitize_key( $_GET['post_type_filter'] ?? '' );

$q_args = array(
	'post_type'      => 'post',
	'post_status'    => $status_f ?: array( 'publish', 'draft', 'private', 'pending' ),
	'posts_per_page' => 20,
	'paged'          => $paged,
	'orderby'        => 'modified',
	'order'          => 'DESC',
);
if ( $search ) $q_args['s'] = $search;

$q           = new WP_Query( $q_args );
$posts_list  = $q->posts;
$total       = $q->found_posts;
$pages_count = (int) ceil( $total / 20 );
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-edit"></span> Posts / Blog</h1>
  <?php if ( $notice ) : ?><div class="ah-notice ah-notice-success"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

  <div class="ah-table-top">
    <form class="ah-search-form" method="get">
      <input type="hidden" name="page" value="ah-posts">
      <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search posts…">
      <select name="status">
        <option value="">All Statuses</option>
        <?php foreach ( array( 'publish' => 'Published', 'draft' => 'Draft', 'private' => 'Private', 'pending' => 'Pending' ) as $sv => $sl ) : ?>
          <option value="<?php echo $sv; ?>" <?php selected( $status_f, $sv ); ?>><?php echo $sl; ?></option>
        <?php endforeach; ?>
      </select>
      <button class="ah-btn ah-btn-secondary">Filter</button>
    </form>
    <a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>" class="ah-btn ah-btn-primary">+ New Post</a>
  </div>

  <div class="ah-table-wrap">
    <table class="ah-table">
      <thead>
        <tr><th>Title</th><th>Categories</th><th>Status</th><th>Author</th><th>Modified</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if ( empty( $posts_list ) ) : ?>
          <tr><td colspan="6" style="text-align:center;color:var(--ah-muted);padding:32px;">No posts found.</td></tr>
        <?php endif; ?>
        <?php foreach ( $posts_list as $p ) :
          $cats   = get_the_category( $p->ID );
          $author = get_the_author_meta( 'display_name', $p->post_author );
          $badge  = array( 'publish' => 'active', 'draft' => 'draft', 'private' => 'inactive', 'pending' => 'draft' );
          $label  = array( 'publish' => 'Published', 'draft' => 'Draft', 'private' => 'Private', 'pending' => 'Pending' );
        ?>
          <tr>
            <td>
              <strong><?php echo esc_html( $p->post_title ?: '(no title)' ); ?></strong>
              <?php if ( $p->post_excerpt ) : ?>
                <small style="color:var(--ah-muted);display:block;"><?php echo esc_html( wp_trim_words( $p->post_excerpt, 10 ) ); ?></small>
              <?php endif; ?>
            </td>
            <td>
              <small><?php echo $cats ? esc_html( implode( ', ', wp_list_pluck( $cats, 'name' ) ) ) : '—'; ?></small>
            </td>
            <td><span class="ah-badge ah-badge-<?php echo esc_attr( $badge[ $p->post_status ] ?? 'draft' ); ?>"><?php echo esc_html( $label[ $p->post_status ] ?? $p->post_status ); ?></span></td>
            <td><small><?php echo esc_html( $author ); ?></small></td>
            <td><small><?php echo esc_html( wp_date( 'M j, Y', strtotime( $p->post_modified ) ) ); ?></small></td>
            <td class="row-actions">
              <a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>
              <?php if ( $p->post_status === 'publish' ) : ?>
                <a href="<?php echo esc_url( get_permalink( $p->ID ) ); ?>" target="_blank" class="ah-btn ah-btn-secondary ah-btn-sm">View</a>
              <?php endif; ?>
              <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-posts', 'trash_id' => $p->ID ), admin_url( 'admin.php' ) ), 'ah_trash_post' ) ); ?>"
                 class="ah-btn ah-btn-danger ah-btn-sm" onclick="return confirm('Move to trash?');">Trash</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ( $pages_count > 1 ) : ?>
    <div style="margin-top:16px;display:flex;gap:6px;">
      <?php for ( $pg = 1; $pg <= $pages_count; $pg++ ) : ?>
        <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-posts', 'paged' => $pg ), admin_url( 'admin.php' ) ) ); ?>"
           class="ah-btn ah-btn-sm <?php echo $pg === $paged ? 'ah-btn-primary' : 'ah-btn-secondary'; ?>"><?php echo $pg; ?></a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</div>
