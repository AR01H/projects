<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$notice   = '';
$n_type   = 'success';
$action   = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id  = (int) ( $_GET['id'] ?? 0 );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ah_pages_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_pages_nonce'], 'ah_wp_page_save' ) ) wp_die( 'Security check failed.' );

	if ( isset( $_POST['trash_page'] ) && $edit_id ) {
		wp_trash_post( $edit_id );
		$notice = 'Page moved to trash.'; $action = 'list'; $edit_id = 0;
	} else {
		$title    = sanitize_text_field( $_POST['page_title'] ?? '' );
		$slug     = sanitize_title( $_POST['page_slug'] ?: $title );
		$status   = in_array( $_POST['page_status'] ?? 'draft', array( 'publish','draft','private','pending' ), true ) ? $_POST['page_status'] : 'draft';
		$template = sanitize_text_field( $_POST['page_template'] ?? '' );
		$thumb_id = (int) ( $_POST['featured_image_id'] ?? 0 );
		$excerpt  = sanitize_textarea_field( $_POST['page_excerpt'] ?? '' );
		$page_content_raw = isset( $_POST['page_content'] ) ? wp_unslash( $_POST['page_content'] ) : '';
		$page_content = ( current_user_can( 'unfiltered_html' ) || current_user_can( 'manage_options' ) ) ? $page_content_raw : wp_kses_post( $page_content_raw );
		$page_data = array( 'post_type' => 'page', 'post_title' => $title, 'post_content' => $page_content, 'post_name' => $slug, 'post_status' => $status, 'post_excerpt' => $excerpt, 'page_template' => $template );
		if ( $edit_id ) { $page_data['ID'] = $edit_id; $result = wp_update_post( $page_data, true ); }
		else { $result = wp_insert_post( $page_data, true ); }
		if ( is_wp_error( $result ) ) { $notice = 'Error: ' . $result->get_error_message(); $n_type = 'error'; }
		else {
			$saved_id = (int) $result;
			if ( $thumb_id ) set_post_thumbnail( $saved_id, $thumb_id );
			else delete_post_thumbnail( $saved_id );
			if ( ! $edit_id ) {
				flush_rewrite_rules( false ); // refresh routing so new slug is immediately accessible
			}
			$notice = $edit_id ? 'Page updated.' : 'Page created.';
			$action = 'list'; $edit_id = 0;
		}
	}
}

if ( isset( $_GET['trash_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_trash_page' ) ) {
	$trash_id = (int) $_GET['trash_id'];
	wp_trash_post( $trash_id );
	$notice = 'Page moved to trash.';
}

$all_templates  = array( '' => 'Default Template' ) + get_page_templates();
?>
<?php if ( $action === 'list' ) :
    $paged    = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
    $search   = sanitize_text_field( $_GET['s'] ?? '' );
    $status_f = sanitize_key( $_GET['status'] ?? '' );
    $q_args   = array( 'post_type' => 'page', 'post_status' => $status_f ?: array( 'publish','draft','private','pending' ), 'posts_per_page' => 20, 'paged' => $paged, 'orderby' => 'title', 'order' => 'ASC' );
    if ( $search ) $q_args['s'] = $search;
    $q = new WP_Query( $q_args );
    $pages = $q->posts; $total = $q->found_posts; $pages_count = (int) ceil( $total / 20 );

    $all_templates_for_table = $all_templates;
    \Ah\Cms\Admin\Components\AdminComponents::listPage( array(
      'icon'        => 'admin-page',
      'title'       => 'Pages Manager',
      'description' => 'Create and manage static pages with templates and featured images.',
      'notice'      => $notice,
      'notice_type' => $n_type,
      'filter_bar'  => array(
        'page_slug'          => 'ah-pages',
        'search_placeholder' => 'Search pages…',
        'search_value'       => $search,
        'filters'            => array(
          array(
            'name'     => 'status',
            'options'  => array_merge( array( '' => 'All Statuses' ), array( 'publish' => 'Published', 'draft' => 'Draft', 'private' => 'Private', 'pending' => 'Pending' ) ),
            'selected' => $status_f,
          ),
        ),
        'add_url'   => add_query_arg( array( 'page' => 'ah-pages', 'action' => 'add' ), admin_url( 'admin.php' ) ),
        'add_label' => '+ New Page',
      ),
      'table' => array(
        'columns' => array(
          array( 'label' => 'Title', 'render' => function ( $pg ) {
            $html = '<strong>' . esc_html( $pg->post_title ?: '(no title)' ) . '</strong>';
            if ( $pg->post_parent ) $html .= '<small style="color:var(--ah-muted);display:block;">Child of: ' . esc_html( get_the_title( $pg->post_parent ) ) . '</small>';
            return $html;
          } ),
          array( 'label' => 'Slug', 'render' => function ( $pg ) {
            return '<code>' . esc_html( $pg->post_name ) . '</code>';
          } ),
          array( 'label' => 'Status', 'render' => function ( $pg ) {
            $badge = array( 'publish' => 'active', 'draft' => 'draft', 'private' => 'inactive', 'pending' => 'draft' );
            $label = array( 'publish' => 'Published', 'draft' => 'Draft', 'private' => 'Private', 'pending' => 'Pending' );
            return '<span class="ah-badge ah-badge-' . esc_attr( $badge[ $pg->post_status ] ?? 'draft' ) . '">' . esc_html( $label[ $pg->post_status ] ?? $pg->post_status ) . '</span>';
          } ),
          array( 'label' => 'Template', 'render' => function ( $pg ) use ( $all_templates_for_table ) {
            $tpl      = get_page_template_slug( $pg->ID );
            $tpl_name = $tpl ? ( $all_templates_for_table[ $tpl ] ?? basename( $tpl ) ) : 'Default';
            return '<small>' . esc_html( $tpl_name ) . '</small>';
          } ),
          array( 'label' => 'Modified', 'render' => function ( $pg ) {
            return '<small>' . esc_html( wp_date( 'M j, Y', strtotime( $pg->post_modified ) ) ) . '</small>';
          } ),
        ),
        'items'         => $pages,
        'empty_message' => 'No pages found.',
        'actions'       => function ( $pg ) {
          $edit_url = add_query_arg( array( 'page' => 'ah-pages', 'action' => 'edit', 'id' => $pg->ID ), admin_url( 'admin.php' ) );
          $trash_url = wp_nonce_url( add_query_arg( array( 'page' => 'ah-pages', 'trash_id' => $pg->ID ), admin_url( 'admin.php' ) ), 'ah_trash_page' );
          $html = '<a href="' . esc_url( $edit_url ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>';
          if ( $pg->post_status === 'publish' ) {
            $html .= '<a href="' . esc_url( get_permalink( $pg->ID ) ) . '" target="_blank" class="ah-btn ah-btn-secondary ah-btn-sm">View</a>';
          }
          ob_start();
          \Ah\Cms\Admin\Components\AdminComponents::confirmDelete( $trash_url );
          $html .= ob_get_clean();
          return $html;
        },
      ),
      'pagination' => array( 'total' => $total, 'total_pages' => $pages_count, 'current_page' => $paged ),
    ) );

  else :
    $wp_page   = $edit_id ? get_post( $edit_id ) : null;
    $thumb_id  = $wp_page ? (int) get_post_thumbnail_id( $wp_page->ID ) : 0;
    $thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'medium' ) : '';
    $cur_tpl   = $wp_page ? get_page_template_slug( $wp_page->ID ) : '';
  ?>
  <div class="wrap ah-wrap">
    <?php \Ah\Cms\Admin\Components\AdminComponents::pageHeader( 'admin-page', 'Pages Manager', 'Create and manage static pages with templates and featured images.' ); ?>
    <?php if ( $notice ) : ?><?php \Ah\Cms\Admin\Components\AdminComponents::notice( $notice, $n_type ); ?><?php endif; ?>
    <?php \Ah\Cms\Admin\Components\AdminComponents::backLink( admin_url( 'admin.php?page=ah-pages' ), '← Back to Pages' ); ?>

    <form method="post">
      <?php wp_nonce_field( 'ah_wp_page_save', 'ah_pages_nonce' ); ?>
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">
        <div>
          <?php ob_start(); ?>
            <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Page Title *', '<input type="text" name="page_title" value="' . esc_attr( $wp_page->post_title ?? '' ) . '" class="ah-generate-slug-source" data-slug-target="#ah-page-slug" required style="font-size:16px;font-weight:600;">' ); ?>
            <?php
            $slug_input = '<div style="display:flex;align-items:center;gap:8px;">'
              . '<span style="color:var(--ah-muted);font-size:12px;">' . esc_html( trailingslashit( home_url() ) ) . '</span>'
              . '<input type="text" name="page_slug" id="ah-page-slug" value="' . esc_attr( $wp_page->post_name ?? '' ) . '" class="ah-slug-field" style="flex:1;"'
              . ( ! empty( $wp_page->post_name ) ? ' data-manual="1"' : '' ) . '>';
            if ( ! empty( $wp_page->post_name ) ) {
              $slug_input .= '<small style="color:var(--ah-muted);font-size:11px;display:block;margin-top:4px;">'
                . 'Slug is locked - editing the title won\'t change it. '
                . '<a href="#" style="color:var(--ah-primary);" onclick="document.getElementById(\'ah-page-slug\').removeAttribute(\'data-manual\');jQuery(\'#ah-page-slug\').data(\'manual\',false);this.parentNode.remove();return false;">Unlock to regenerate</a>'
                . '</small>';
            }
            $slug_input .= '</div>';
            ?>
            <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Slug (URL)', $slug_input ); ?>
            <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Excerpt', '<textarea name="page_excerpt" rows="2">' . esc_textarea( $wp_page->post_excerpt ?? '' ) . '</textarea>' ); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Page Details', ob_get_clean() ); ?>
          <?php ob_start(); ?>
            <p style="margin:0 0 10px;color:var(--ah-muted);font-size:13px;">Paste raw HTML, inline styles, scripts, and custom markup here.</p>
            <textarea name="page_content" id="page_content" rows="28" style="width:100%;min-height:420px;font-family:Consolas,Monaco,monospace;font-size:13px;line-height:1.6;resize:vertical;"><?php echo esc_textarea( $wp_page->post_content ?? '' ); ?></textarea>
          <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Page Content', ob_get_clean() ); ?>
        </div>

        <div>
          <?php ob_start(); ?>
            <?php \Ah\Cms\Admin\Components\AdminComponents::mediaField( 'featured_image_id', 'Featured Image / Video', $thumb_id, array( 'type' => 'media' ) ); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Featured Image', ob_get_clean() ); ?>
          <?php ob_start(); ?>
            <?php
            $status_select = '<select name="page_status">'
              . '<option value="publish"' . selected( $wp_page->post_status ?? 'draft', 'publish', false ) . '>Published</option>'
              . '<option value="draft"' . selected( $wp_page->post_status ?? 'draft', 'draft', false ) . '>Draft</option>'
              . '<option value="private"' . selected( $wp_page->post_status ?? '', 'private', false ) . '>Private</option>'
              . '<option value="pending"' . selected( $wp_page->post_status ?? '', 'pending', false ) . '>Pending Review</option>'
              . '</select>';
            ?>
            <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Status', $status_select ); ?>
            <?php if ( $wp_page && $wp_page->post_status === 'publish' ) : ?>
              <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( '', '<a href="' . esc_url( get_permalink( $wp_page->ID ) ) . '" target="_blank" class="ah-btn ah-btn-secondary ah-btn-sm" style="width:100%;justify-content:center;">View Page</a>' ); ?>
            <?php endif; ?>
            <button type="submit" class="ah-btn ah-btn-primary" style="width:100%;justify-content:center;margin-top:8px;">
              <span class="dashicons dashicons-saved"></span> <?php echo $wp_page ? 'Update Page' : 'Publish Page'; ?>
            </button>
          <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Publish', ob_get_clean() ); ?>

          <?php
          $tpl_select = '<select name="page_template">';
          foreach ( $all_templates as $tf => $tl ) {
            $tpl_select .= '<option value="' . esc_attr( $tf ) . '"' . selected( $cur_tpl, $tf, false ) . '>' . esc_html( $tl ) . '</option>';
          }
          $tpl_select .= '</select>';
          ob_start();
          \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Template', $tpl_select );
          ?>
          <div class="ah-card ah-hidden">
            <div class="ah-card-header"><h2>Template</h2></div>
            <?php echo ob_get_clean(); ?>
          </div>
          <style>.ah-hidden{display:none;}</style>

          <?php if ( $wp_page ) : ?>
            <?php ob_start(); ?>
              <button type="submit" name="trash_page" value="1" class="ah-btn ah-btn-danger" style="width:100%;justify-content:center;">
                <span class="dashicons dashicons-trash"></span> Move to Trash
              </button>
            <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Danger Zone', ob_get_clean() ); ?>
            <style>.ah-card:last-child { border-color: var(--ah-danger); }</style>
          <?php endif; ?>
        </div>
      </div>
    </form>
  <?php endif; ?>
</div>
