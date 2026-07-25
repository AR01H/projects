<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$notice = '';

// ── Sections array → Gutenberg block HTML ──
function ah_sections_to_blocks( array $sections ): string {
	return AH_Posts_Helper::sections_to_blocks( $sections );
}

// ── Default sections per template type ──
function ah_template_default_sections( string $tpl_key, array $overrides = [] ): array {
	return AH_Posts_Helper::template_default_sections( $tpl_key, $overrides );
}

// ── Render a section card (PHP - for both new and loaded sections) ──
function ah_render_section_card( array $s, bool $first = false, bool $last = false ): void {
	$type   = $s['type'] ?? '';
	$labels = [
		'heading'   => '📝 Heading',
		'paragraph' => '¶ Paragraph',
		'list'      => '• List',
		'table'     => '⊞ Table',
		'quote'     => '" Quote',
		'cta'       => '⬡ CTA Button',
	];
	$label  = $labels[ $type ] ?? $type;
	$cell   = 'padding:4px;border:1px solid var(--ah-border);';
	?>
	<div class="ah-card ah-section-card" data-type="<?php echo esc_attr( $type ); ?>" style="margin-bottom:12px;padding:16px;">
	  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
	    <strong style="font-size:.85rem;"><?php echo esc_html( $label ); ?></strong>
	    <div>
	      <button type="button" class="ah-sec-up ah-btn ah-btn-secondary ah-btn-sm"<?php echo $first ? ' disabled' : ''; ?>>↑</button>
	      <button type="button" class="ah-sec-dn ah-btn ah-btn-secondary ah-btn-sm"<?php echo $last ? ' disabled' : ''; ?>>↓</button>
	      <button type="button" class="ah-sec-rm ah-btn ah-btn-danger ah-btn-sm" style="margin-left:4px;">✕ Remove</button>
	    </div>
	  </div>
	  <div class="ah-section-fields">
	    <?php
	    switch ( $type ) {
	        case 'heading': ?>
	            <div style="display:flex;gap:8px;">
	              <select class="ah-sec-level" style="width:90px;">
	                <?php foreach ( [ 2, 3, 4 ] as $l ) : ?>
	                  <option value="<?php echo $l; ?>" <?php selected( (int) ( $s['level'] ?? 2 ), $l ); ?>>H<?php echo $l; ?></option>
	                <?php endforeach; ?>
	              </select>
	              <input type="text" class="ah-sec-text" value="<?php echo esc_attr( $s['text'] ?? '' ); ?>" placeholder="Section heading…" style="flex:1;box-sizing:border-box;">
	            </div>
	        <?php break;

	        case 'paragraph': ?>
	            <textarea class="ah-sec-text" rows="4" placeholder="Write your paragraph here…" style="width:100%;box-sizing:border-box;"><?php echo esc_textarea( $s['text'] ?? '' ); ?></textarea>
	        <?php break;

	        case 'list':
	            $ordered = ! empty( $s['ordered'] );
	            $items   = (array) ( $s['items'] ?? [ '' ] );
	            ?>
	            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
	              <label style="font-size:.8rem;font-weight:600;">Type:</label>
	              <select class="ah-sec-list-type">
	                <option value="0" <?php echo ! $ordered ? 'selected' : ''; ?>>• Bullet</option>
	                <option value="1" <?php echo $ordered ? 'selected' : ''; ?>>1. Numbered</option>
	              </select>
	            </div>
	            <div class="ah-list-items">
	              <?php foreach ( $items as $item ) : ?>
	                <div class="ah-list-item" style="display:flex;gap:6px;margin-bottom:6px;">
	                  <input type="text" class="ah-list-item-text" value="<?php echo esc_attr( $item ); ?>" placeholder="List item…" style="flex:1;box-sizing:border-box;">
	                  <button type="button" class="ah-list-rm ah-btn ah-btn-danger ah-btn-sm">✕</button>
	                </div>
	              <?php endforeach; ?>
	            </div>
	            <button type="button" class="ah-list-add ah-btn ah-btn-secondary ah-btn-sm" style="margin-top:4px;">+ Add Item</button>
	        <?php break;

	        case 'table':
	            $headers = (array) ( $s['headers'] ?? [ 'Column 1', 'Column 2' ] );
	            $rows    = (array) ( $s['rows'] ?? [ [ '', '' ] ] );
	            ?>
	            <div style="margin-bottom:8px;">
	              <button type="button" class="ah-table-add-row ah-btn ah-btn-secondary ah-btn-sm">+ Row</button>
	              <button type="button" class="ah-table-add-col ah-btn ah-btn-secondary ah-btn-sm" style="margin-left:6px;">+ Column</button>
	            </div>
	            <div style="overflow-x:auto;">
	              <table class="ah-table-editor" style="border-collapse:collapse;width:100%;">
	                <thead>
	                  <tr>
	                    <?php foreach ( $headers as $h ) : ?>
	                      <th style="<?php echo $cell; ?>background:var(--ah-bg-light);"><input type="text" value="<?php echo esc_attr( $h ); ?>" placeholder="Header" style="width:100%;min-width:80px;box-sizing:border-box;"></th>
	                    <?php endforeach; ?>
	                  </tr>
	                </thead>
	                <tbody>
	                  <?php foreach ( $rows as $row ) : ?>
	                    <tr>
	                      <?php foreach ( (array) $row as $cell_val ) : ?>
	                        <td style="<?php echo $cell; ?>"><input type="text" value="<?php echo esc_attr( $cell_val ); ?>" placeholder="Cell" style="width:100%;min-width:80px;box-sizing:border-box;"></td>
	                      <?php endforeach; ?>
	                    </tr>
	                  <?php endforeach; ?>
	                </tbody>
	              </table>
	            </div>
	        <?php break;

	        case 'quote': ?>
	            <textarea class="ah-sec-text" rows="3" placeholder="Quote text…" style="width:100%;box-sizing:border-box;margin-bottom:8px;font-style:italic;"><?php echo esc_textarea( $s['text'] ?? '' ); ?></textarea>
	            <input type="text" class="ah-sec-cite" value="<?php echo esc_attr( $s['cite'] ?? '' ); ?>" placeholder="Attribution / Author (optional)" style="width:100%;box-sizing:border-box;">
	        <?php break;

	        case 'cta': ?>
	            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
	              <div>
	                <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;">Button Text</label>
	                <input type="text" class="ah-sec-cta-text" value="<?php echo esc_attr( $s['text'] ?? '' ); ?>" placeholder="Book a Free Consultation" style="width:100%;box-sizing:border-box;">
	              </div>
	              <div>
	                <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;">URL</label>
	                <input type="text" class="ah-sec-cta-url" value="<?php echo esc_attr( $s['url'] ?? '' ); ?>" placeholder="/contact/" style="width:100%;box-sizing:border-box;">
	              </div>
	            </div>
	        <?php break;
	    }
	    ?>
	  </div>
	</div>
	<?php
}

// ── Helpers used by template POST ─────────────────────────────────────────────
function ah_generate_guide_content( int $count ): string {
	return AH_Posts_Helper::generate_guide_content( $count );
}
function ah_generate_faq_content( string $topic, int $count ): string {
	return AH_Posts_Helper::generate_faq_content( $topic, $count );
}

// ── Post templates ──
function ah_post_templates(): array {
	return AH_Posts_Helper::post_templates();
}

function ah_render_template_field( array $f ): void {
	$style = 'width:100%;box-sizing:border-box;';
	$input = '';
	if ( $f['type'] === 'text' ) {
		$input = '<input type="text" name="' . esc_attr( $f['name'] ) . '" placeholder="' . esc_attr( $f['placeholder'] ?? '' ) . '" style="' . $style . '"' . ( ! empty( $f['required'] ) ? ' required' : '' ) . '>';
	} elseif ( $f['type'] === 'textarea' ) {
		$input = '<textarea name="' . esc_attr( $f['name'] ) . '" rows="' . esc_attr( $f['rows'] ?? 2 ) . '" placeholder="' . esc_attr( $f['placeholder'] ?? '' ) . '" style="' . $style . '"></textarea>';
	} elseif ( $f['type'] === 'number' ) {
		$input = '<input type="number" name="' . esc_attr( $f['name'] ) . '" min="' . esc_attr( $f['min'] ?? 1 ) . '" max="' . esc_attr( $f['max'] ?? 10 ) . '" value="' . esc_attr( $f['default'] ?? 3 ) . '" style="' . $style . '">';
	} elseif ( $f['type'] === 'category' ) {
		ob_start();
		wp_dropdown_categories( array( 'name' => $f['name'], 'show_option_none' => '- No Category -', 'option_none_value' => 0, 'hide_empty' => false, 'style' => $style ) );
		$input = ob_get_clean();
	}
	$label = esc_html( $f['label'] );
	if ( ! empty( $f['hint'] ) ) {
		$label .= ' <small style="font-weight:400;opacity:.65;">(' . esc_html( $f['hint'] ) . ')</small>';
	}
	\Ah\Cms\Admin\Components\AdminComponents::formRow( $label, $input );
}

// ── POST: save from custom editor ─────────────────────────────────────────────
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ah_custom_editor_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_custom_editor_nonce'], 'ah_save_custom_post' ) ) wp_die( 'Security check failed.' );

	$post_id     = (int) ( $_POST['post_id'] ?? 0 );
	$title       = sanitize_text_field( $_POST['post_title'] ?? '' );
	$excerpt     = sanitize_textarea_field( $_POST['post_excerpt'] ?? '' );
	$raw_status  = sanitize_key( $_POST['post_status'] ?? 'draft' );
	$status      = in_array( $raw_status, [ 'draft', 'publish', 'private', 'pending' ], true ) ? $raw_status : 'draft';
	$pub_date    = sanitize_text_field( $_POST['post_date'] ?? '' );
	$feat_img_id = (int) ( $_POST['featured_image_id'] ?? 0 );
	$cats        = array_map( 'intval', (array) ( $_POST['post_categories'] ?? [] ) );
	$tags_raw    = sanitize_text_field( $_POST['post_tags'] ?? '' );
	$sec_raw     = wp_unslash( $_POST['sections_json'] ?? '[]' );
	$sections    = json_decode( $sec_raw, true );
	if ( ! is_array( $sections ) ) $sections = [];
	$content     = ah_sections_to_blocks( $sections );

	$args = array(
		'ID'           => $post_id,
		'post_title'   => $title,
		'post_content' => $content,
		'post_excerpt' => $excerpt,
		'post_status'  => $status,
	);
	if ( $pub_date ) {
		$args['post_date']     = $pub_date;
		$args['post_date_gmt'] = get_gmt_from_date( $pub_date );
	}
	wp_update_post( $args );
	wp_set_post_categories( $post_id, $cats );
	if ( $tags_raw ) wp_set_post_tags( $post_id, $tags_raw );
	( new AH_Content_Taxonomy_Model() )->sync_terms( 'wp_post', $post_id, $_POST['taxonomy_ids'] ?? array() );
	if ( $feat_img_id ) set_post_thumbnail( $post_id, $feat_img_id );
	else delete_post_thumbnail( $post_id );
	update_post_meta( $post_id, '_ah_sections', wp_slash( wp_json_encode( $sections ) ) );
	update_post_meta( $post_id, '_ah_editor_mode', 'custom' );
	update_post_meta( $post_id, '_ah_is_featured',  ! empty( $_POST['is_featured'] )  ? '1' : '0' );
	update_post_meta( $post_id, '_ah_is_popular',   ! empty( $_POST['is_popular'] )   ? '1' : '0' );
	update_post_meta( $post_id, '_ah_is_suggested', ! empty( $_POST['is_suggested'] ) ? '1' : '0' );

	AH_Admin_Bootstrap::redirect( add_query_arg( [ 'page' => 'ah-posts', 'action' => 'edit-custom', 'id' => $post_id, 'saved' => 1 ], admin_url( 'admin.php' ) ) );
}

// ── POST: create from template ────────────────────────────────────────────────
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ah_posts_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_posts_nonce'], 'ah_create_post' ) ) wp_die( 'Security check failed.' );

	$tpl_key     = sanitize_key( $_POST['template_key'] ?? 'blog' );
	$editor_mode = sanitize_key( $_POST['editor_mode'] ?? 'gutenberg' );
	$tpls        = ah_post_templates();
	$tpl         = $tpls[ $tpl_key ] ?? reset( $tpls );
	$title       = sanitize_text_field( $_POST['post_title'] ?? '' ) ?: $tpl['label'];
	$excerpt     = sanitize_textarea_field( $_POST['post_excerpt'] ?? '' ) ?: $tpl['excerpt'];
	$category    = (int) ( $_POST['post_category'] ?? 0 );

	$overrides = [];
	switch ( $tpl_key ) {
		case 'news':
			$overrides['lead_paragraph'] = sanitize_textarea_field( $_POST['lead_paragraph'] ?? '' );
			if ( $overrides['lead_paragraph'] && ! ( $_POST['post_excerpt'] ?? '' ) ) $excerpt = $overrides['lead_paragraph'];
			break;
		case 'guide':
			$overrides['step_count'] = max( 2, min( 10, (int) ( $_POST['step_count'] ?? 3 ) ) );
			break;
		case 'casestudy':
			$overrides['client_name']  = sanitize_text_field( $_POST['client_name'] ?? '' );
			$overrides['client_quote'] = sanitize_textarea_field( $_POST['client_quote'] ?? '' );
			break;
		case 'faq':
			$overrides['faq_topic'] = sanitize_text_field( $_POST['faq_topic'] ?? '' );
			$overrides['faq_count'] = max( 2, min( 10, (int) ( $_POST['faq_count'] ?? 3 ) ) );
			break;
	}

	$sections = ah_template_default_sections( $tpl_key, $overrides );
	$content  = ah_sections_to_blocks( $sections );

	$insert_args = array(
		'post_type'    => 'post',
		'post_status'  => 'draft',
		'post_title'   => $title,
		'post_content' => $content,
		'post_excerpt' => $excerpt,
	);
	if ( $category ) $insert_args['post_category'] = [ $category ];

	$new_id = wp_insert_post( $insert_args );
	if ( $new_id && ! is_wp_error( $new_id ) ) {
		update_post_meta( $new_id, '_ah_editor_mode', $editor_mode );
		if ( $editor_mode === 'custom' ) {
			update_post_meta( $new_id, '_ah_sections', wp_slash( wp_json_encode( $sections ) ) );
			AH_Admin_Bootstrap::redirect( add_query_arg( [ 'page' => 'ah-posts', 'action' => 'edit-custom', 'id' => $new_id ], admin_url( 'admin.php' ) ) );
		} else {
			AH_Admin_Bootstrap::redirect( get_edit_post_link( $new_id, 'redirect' ) );
		}
	}
	$notice = 'Could not create post. Please try again.';
}

// ── GET: trash ────────────────────────────────────────────────────────────────
if ( isset( $_GET['trash_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_trash_post' ) ) {
	wp_trash_post( (int) $_GET['trash_id'] );
	AH_Admin_Bootstrap::redirect( add_query_arg( [ 'page' => 'ah-posts', 'trashed' => 1 ], admin_url( 'admin.php' ) ) );
}
if ( isset( $_GET['trashed'] ) ) $notice = 'Post moved to trash.';
if ( isset( $_GET['saved'] ) )   $notice = 'Post saved successfully.';

// ── Setup ─────────────────────────────────────────────────────────────────────
$action      = sanitize_key( $_GET['action'] ?? 'list' );
$paged       = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
$search      = sanitize_text_field( $_GET['s'] ?? '' );
$status_f    = sanitize_key( $_GET['status'] ?? '' );
$cat_f       = absint( $_GET['cat'] ?? 0 );
$author_f    = absint( $_GET['author_id'] ?? 0 );
$flag_f      = sanitize_key( $_GET['flag'] ?? '' );
$q_args      = array(
	'post_type'      => 'post',
	'post_status'    => $status_f ?: array( 'publish', 'draft', 'private', 'pending' ),
	'posts_per_page' => 20,
	'paged'          => $paged,
	'orderby'        => 'modified',
	'order'          => 'DESC',
);
if ( $search )   $q_args['s']      = $search;
if ( $cat_f )    $q_args['cat']    = $cat_f;
if ( $author_f ) $q_args['author'] = $author_f;
if ( in_array( $flag_f, array( 'featured', 'popular', 'suggested' ), true ) ) {
	$q_args['meta_query'] = array( array(
		'key'   => '_ah_is_' . $flag_f,
		'value' => '1',
	) );
}
$q           = new WP_Query( $q_args );
$posts_list  = $q->posts;
$total       = $q->found_posts;
$pages_count = (int) ceil( $total / 20 );

if ( $action === 'edit-custom' ) {
	wp_enqueue_editor();
}
?>
<div class="wrap ah-wrap">
<?php \Ah\Cms\Admin\Components\AdminComponents::pageHeader( 'edit', 'Posts / Blog', 'Create, edit, and manage blog posts with the rich-text editor.' ); ?>
<?php if ( $notice ) : ?><?php \Ah\Cms\Admin\Components\AdminComponents::notice( $notice, 'success' ); ?><?php endif; ?>

<?php /* ══════════════ TEMPLATES VIEW ══════════════ */ ?>
<?php if ( $action === 'templates' ) :
  $tpls = ah_post_templates();
?>
  <div class="ah-table-top" style="margin-bottom:0;">
    <p style="color:var(--ah-muted);margin:0;">Fill in the fields below, then choose how you'd like to edit your post.</p>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-posts' ) ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">&larr; Back</a>
  </div>
  <p style="margin:8px 0 24px;"></p>

  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;">
    <?php foreach ( $tpls as $tpl_key => $tpl ) : ?>
      <?php ob_start(); ?>
        <div style="background:var(--ah-primary,var(--ah-primary));color:#fff;padding:20px 24px;">
          <div style="font-size:2rem;margin-bottom:8px;"><?php echo $tpl['icon']; ?></div>
          <h3 style="margin:0 0 4px;color:#fff;"><?php echo esc_html( $tpl['label'] ); ?></h3>
          <p style="margin:0;opacity:.8;font-size:.82rem;"><?php echo esc_html( $tpl['desc'] ); ?></p>
        </div>
        <div style="padding:20px 24px;">
          <form method="post">
            <?php wp_nonce_field( 'ah_create_post', 'ah_posts_nonce' ); ?>
            <input type="hidden" name="template_key" value="<?php echo esc_attr( $tpl_key ); ?>">
            <?php foreach ( $tpl['fields'] as $field ) : ?>
              <?php ah_render_template_field( $field ); ?>
            <?php endforeach; ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px;">
              <button type="submit" name="editor_mode" value="custom"
                      class="ah-btn ah-btn-secondary" style="justify-content:center;font-size:.82rem;">
                📝 Form Editor
              </button>
              <button type="submit" name="editor_mode" value="gutenberg"
                      class="ah-btn ah-btn-primary" style="justify-content:center;font-size:.82rem;">
                🖊 WP Editor
              </button>
            </div>
          </form>
        </div>
      <?php \Ah\Cms\Admin\Components\AdminComponents::card( '', ob_get_clean() ); ?>
    <?php endforeach; ?>
  </div>

<?php /* ══════════════ CUSTOM FORM EDITOR ══════════════ */ ?>
<?php elseif ( $action === 'edit-custom' ) :
  $edit_id     = (int) ( $_GET['id'] ?? 0 );
  if ( ! $edit_id ) { AH_Admin_Bootstrap::redirect( admin_url( 'admin.php?page=ah-posts&action=templates' ) ); }
  $post        = get_post( $edit_id );
  if ( ! $post ) { AH_Admin_Bootstrap::redirect( admin_url( 'admin.php?page=ah-posts' ) ); }

  $saved_sections_raw = get_post_meta( $edit_id, '_ah_sections', true );
  $saved_sections     = $saved_sections_raw ? json_decode( $saved_sections_raw, true ) : [];
  if ( ! is_array( $saved_sections ) ) $saved_sections = [];

  $post_cats      = wp_get_post_categories( $edit_id );
  $post_tags_obj  = wp_get_post_tags( $edit_id, [ 'fields' => 'names' ] );
  $tags_str       = implode( ', ', $post_tags_obj );
  $feat_img_id    = (int) get_post_thumbnail_id( $edit_id );
  $feat_img_url   = $feat_img_id ? wp_get_attachment_image_url( $feat_img_id, 'medium' ) : '';
  $all_cats       = get_categories( [ 'hide_empty' => false ] );
  $pub_date_raw   = $post->post_status === 'future' ? $post->post_date : '';
  $is_featured    = (bool) get_post_meta( $edit_id, '_ah_is_featured', true );
  $is_popular     = (bool) get_post_meta( $edit_id, '_ah_is_popular', true );
  $is_suggested   = (bool) get_post_meta( $edit_id, '_ah_is_suggested', true );

  $wp_edit_url    = get_edit_post_link( $edit_id );
  $section_count  = count( $saved_sections );
?>
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
    <?php \Ah\Cms\Admin\Components\AdminComponents::backLink( admin_url( 'admin.php?page=ah-posts' ), '← All Posts' ); ?>
    <div style="display:flex;align-items:center;gap:8px;">
      <span class="ah-badge ah-badge-<?php echo esc_attr( $post->post_status === 'publish' ? 'active' : 'draft' ); ?>"><?php echo esc_html( ucfirst( $post->post_status ) ); ?></span>
      <a href="<?php echo esc_url( $wp_edit_url ); ?>" class="ah-btn ah-btn-secondary ah-btn-sm">Switch to WP Editor</a>
      <?php if ( $post->post_status === 'publish' ) : ?>
        <a href="<?php echo esc_url( get_permalink( $edit_id ) ); ?>" target="_blank" class="ah-btn ah-btn-secondary ah-btn-sm">View Post ↗</a>
      <?php endif; ?>
    </div>
  </div>

  <form id="ah-custom-editor-form" method="post">
    <?php wp_nonce_field( 'ah_save_custom_post', 'ah_custom_editor_nonce' ); ?>
    <input type="hidden" name="post_id" value="<?php echo esc_attr( $edit_id ); ?>">
    <textarea id="ah-sections-json" name="sections_json" style="display:none;"></textarea>

    <div style="display:grid;grid-template-columns:1fr 290px;gap:20px;align-items:start;">

      <!-- ── Main editor column ── -->
      <div>
        <!-- Title & Excerpt -->
        <?php ob_start(); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Post Title', '<input type="text" name="post_title" value="' . esc_attr( $post->post_title ) . '" placeholder="Post title…" style="width:100%;box-sizing:border-box;font-size:1.25rem;font-weight:600;">' ); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Short Summary <small style="font-weight:400;opacity:.65;">(excerpt shown in listings)</small>', '<textarea name="post_excerpt" rows="2" placeholder="A short summary of what this post covers…" style="width:100%;box-sizing:border-box;">' . esc_textarea( $post->post_excerpt ) . '</textarea>' ); ?>
        <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Post Details', ob_get_clean() ); ?>

        <!-- Sections builder -->
        <div id="ah-sections-builder">
          <?php
          foreach ( $saved_sections as $idx => $sec ) {
              ah_render_section_card( $sec, $idx === 0, $idx === $section_count - 1 );
          }
          ?>
        </div>

        <!-- Add section toolbar -->
        <?php ob_start(); ?>
          <div style="display:flex;flex-wrap:wrap;gap:8px;">
            <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-add-sec" data-type="heading">+ Heading</button>
            <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-add-sec" data-type="paragraph">+ Paragraph</button>
            <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-add-sec" data-type="list">+ List</button>
            <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-add-sec" data-type="table">+ Table</button>
            <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-add-sec" data-type="quote">+ Quote</button>
            <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-add-sec" data-type="cta">+ CTA Button</button>
          </div>
        <?php \Ah\Cms\Admin\Components\AdminComponents::formSection( 'Add a section', ob_get_clean() ); ?>
      </div>

      <!-- ── Sidebar ── -->
      <div>
        <!-- Save actions -->
        <?php ob_start(); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::formGrid( array(
            array( '', '<button type="submit" name="post_status_override" value="draft" onclick="document.querySelector(\'[name=post_status]\').value=\'draft\';" class="ah-btn ah-btn-secondary" style="justify-content:center;">Save Draft</button>' ),
            array( '', '<button type="submit" name="post_status_override" value="publish" onclick="document.querySelector(\'[name=post_status]\').value=\'publish\';" class="ah-btn ah-btn-primary" style="justify-content:center;">Publish</button>' ),
          ) ); ?>
          <?php
          $status_select = '<select name="post_status" style="width:100%;box-sizing:border-box;">';
          foreach ( [ 'draft' => 'Draft', 'publish' => 'Published', 'private' => 'Private', 'pending' => 'Pending Review' ] as $sv => $sl ) {
            $status_select .= '<option value="' . esc_attr( $sv ) . '"' . selected( $post->post_status, $sv, false ) . '>' . esc_html( $sl ) . '</option>';
          }
          $status_select .= '</select>';
          ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Status', $status_select ); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Publish Date <small style="font-weight:400;opacity:.65;">(leave blank for now)</small>', '<input type="datetime-local" name="post_date" value="' . esc_attr( $pub_date_raw ) . '" style="width:100%;box-sizing:border-box;">' ); ?>
        <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Save Actions', ob_get_clean() ); ?>

        <!-- Post Settings -->
        <?php ob_start(); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::field( 'checkbox', 'is_featured', '⭐ Featured Post', $is_featured ); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::field( 'checkbox', 'is_popular', '🔥 Popular Post', $is_popular ); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::field( 'checkbox', 'is_suggested', '💡 Suggested Post', $is_suggested ); ?>
        <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Post Settings', ob_get_clean() ); ?>

        <!-- Categories -->
        <?php ob_start(); ?>
          <div style="max-height:150px;overflow-y:auto;">
            <?php if ( $all_cats ) : ?>
              <?php foreach ( $all_cats as $cat ) : ?>
                <label style="display:flex;align-items:center;gap:6px;margin-bottom:6px;cursor:pointer;font-size:.85rem;">
                  <input type="checkbox" name="post_categories[]" value="<?php echo esc_attr( $cat->term_id ); ?>"
                         <?php checked( in_array( $cat->term_id, $post_cats, true ) ); ?>>
                  <?php echo esc_html( $cat->name ); ?>
                  <small style="opacity:.6;">(<?php echo (int) $cat->count; ?>)</small>
                </label>
              <?php endforeach; ?>
            <?php else : ?>
              <p style="font-size:.82rem;opacity:.6;margin:0;">No categories yet - <a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=category' ) ); ?>">add one</a>.</p>
            <?php endif; ?>
          </div>
        <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Categories', ob_get_clean() ); ?>

        <!-- Tags -->
        <?php ob_start(); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Tags', '<input type="text" name="post_tags" value="' . esc_attr( $tags_str ) . '" placeholder="tag1, tag2, tag3" style="width:100%;box-sizing:border-box;">', 'Separate with commas.' ); ?>
        <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Tags', ob_get_clean() ); ?>

        <!-- Global Terms -->
        <?php ob_start(); ?>
          <?php ( new AH_Content_Taxonomy_Model() )->render_picker( 'wp_post', $edit_id ); ?>
        <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'CMS Taxonomy Terms', ob_get_clean() ); ?>

        <!-- Featured Image -->
        <?php ob_start(); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::mediaField( 'featured_image_id', 'Featured Image / Video', $feat_img_id, array( 'type' => 'media' ) ); ?>
        <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Featured Image', ob_get_clean() ); ?>
      </div><!-- /sidebar -->
    </div><!-- /grid -->
  </form>

<style>
.ah-section-card .wp-editor-wrap { max-width: none; }
.ah-section-card .wp-editor-container textarea.wp-editor-area { border-radius: 0; }
</style>

<script>
(function($){
  var richEditorCounter = 0;
  var richTextSelector = '.ah-section-card[data-type="paragraph"] .ah-sec-text';

  function assignRichEditorId($el) {
    if ($el.attr('id')) return $el.attr('id');
    richEditorCounter += 1;
    $el.attr('id', 'ah-post-section-editor-' + richEditorCounter);
    return $el.attr('id');
  }

  function initRichEditors($scope) {
    if (!window.wp || !wp.editor) {
      window.setTimeout(function() {
        initRichEditors($scope);
      }, 250);
      return;
    }
    $scope = $scope && $scope.length ? $scope : $(document);
    $scope.find(richTextSelector).each(function() {
      var $el = $(this);
      var id = assignRichEditorId($el);
      if ($el.data('editorReady')) return;
      $el.data('editorReady', 1);
      wp.editor.initialize(id, {
        tinymce: {
          wpautop: true,
          toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,image,undo,redo',
          toolbar2: '',
          setup: function(editor) {
            editor.on('change keyup undo redo SetContent', function() {
              editor.save();
            });
          }
        },
        quicktags: true,
        mediaButtons: true
      });
    });
  }

  function destroyRichEditors($scope) {
    $scope = $scope && $scope.length ? $scope : $(document);
    $scope.find(richTextSelector).each(function() {
      var id = this.id;
      if (!id) return;
      if (window.tinymce && tinymce.get(id)) {
        tinymce.get(id).save();
        tinymce.get(id).remove();
      }
      if (window.QTags && QTags.instances && QTags.instances[id]) {
        delete QTags.instances[id];
      }
      $(this).removeData('editorReady');
    });
  }

  function syncRichEditors() {
    $(richTextSelector).each(function() {
      if (this.id && window.tinymce && tinymce.get(this.id)) {
        tinymce.get(this.id).save();
      }
    });
  }
  // ── Section card templates ──────────────────────────────────────────────────
  var sectionTemplates = {
    heading: function() {
      return buildCard('heading', '📝 Heading',
        '<div style="display:flex;gap:8px;">' +
        '<select class="ah-sec-level" style="width:90px;"><option value="2">H2</option><option value="3">H3</option><option value="4">H4</option></select>' +
        '<input type="text" class="ah-sec-text" placeholder="Section heading…" style="flex:1;box-sizing:border-box;">' +
        '</div>'
      );
    },
    paragraph: function() {
      return buildCard('paragraph', '¶ Paragraph',
        '<textarea class="ah-sec-text" rows="4" placeholder="Write your paragraph here…" style="width:100%;box-sizing:border-box;"></textarea>'
      );
    },
    list: function() {
      return buildCard('list', '• List',
        '<div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">' +
        '<label style="font-size:.8rem;font-weight:600;">Type:</label>' +
        '<select class="ah-sec-list-type"><option value="0">• Bullet</option><option value="1">1. Numbered</option></select>' +
        '</div>' +
        '<div class="ah-list-items">' +
        '<div class="ah-list-item" style="display:flex;gap:6px;margin-bottom:6px;">' +
        '<input type="text" class="ah-list-item-text" placeholder="List item…" style="flex:1;box-sizing:border-box;">' +
        '<button type="button" class="ah-list-rm ah-btn ah-btn-danger ah-btn-sm">✕</button>' +
        '</div></div>' +
        '<button type="button" class="ah-list-add ah-btn ah-btn-secondary ah-btn-sm" style="margin-top:4px;">+ Add Item</button>'
      );
    },
    table: function() {
      var cell = 'padding:4px;border:1px solid var(--ah-border);';
      return buildCard('table', '⊞ Table',
        '<div style="margin-bottom:8px;">' +
        '<button type="button" class="ah-table-add-row ah-btn ah-btn-secondary ah-btn-sm">+ Row</button>' +
        '<button type="button" class="ah-table-add-col ah-btn ah-btn-secondary ah-btn-sm" style="margin-left:6px;">+ Column</button>' +
        '</div>' +
        '<div style="overflow-x:auto;"><table class="ah-table-editor" style="border-collapse:collapse;width:100%;">' +
        '<thead><tr>' +
        '<th style="' + cell + 'background:var(--ah-bg-light);"><input type="text" placeholder="Header 1" style="width:100%;min-width:80px;box-sizing:border-box;"></th>' +
        '<th style="' + cell + 'background:var(--ah-bg-light);"><input type="text" placeholder="Header 2" style="width:100%;min-width:80px;box-sizing:border-box;"></th>' +
        '</tr></thead>' +
        '<tbody><tr>' +
        '<td style="' + cell + '"><input type="text" placeholder="Cell" style="width:100%;min-width:80px;box-sizing:border-box;"></td>' +
        '<td style="' + cell + '"><input type="text" placeholder="Cell" style="width:100%;min-width:80px;box-sizing:border-box;"></td>' +
        '</tr></tbody></table></div>'
      );
    },
    quote: function() {
      return buildCard('quote', '" Quote',
        '<textarea class="ah-sec-text" rows="3" placeholder="Quote text…" style="width:100%;box-sizing:border-box;margin-bottom:8px;font-style:italic;"></textarea>' +
        '<input type="text" class="ah-sec-cite" placeholder="Attribution / Author (optional)" style="width:100%;box-sizing:border-box;">'
      );
    },
    cta: function() {
      return buildCard('cta', '⬡ CTA Button',
        '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">' +
        '<div><label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;">Button Text</label>' +
        '<input type="text" class="ah-sec-cta-text" placeholder="Book a Free Consultation" style="width:100%;box-sizing:border-box;"></div>' +
        '<div><label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;">URL</label>' +
        '<input type="text" class="ah-sec-cta-url" placeholder="/contact/" style="width:100%;box-sizing:border-box;"></div>' +
        '</div>'
      );
    }
  };

  function buildCard(type, label, fieldsHtml) {
    return $(
      '<div class="ah-card ah-section-card" data-type="' + type + '" style="margin-bottom:12px;padding:16px;">' +
      '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">' +
      '<strong style="font-size:.85rem;">' + label + '</strong>' +
      '<div>' +
      '<button type="button" class="ah-sec-up ah-btn ah-btn-secondary ah-btn-sm">↑</button> ' +
      '<button type="button" class="ah-sec-dn ah-btn ah-btn-secondary ah-btn-sm">↓</button> ' +
      '<button type="button" class="ah-sec-rm ah-btn ah-btn-danger ah-btn-sm" style="margin-left:4px;">✕ Remove</button>' +
      '</div></div>' +
      '<div class="ah-section-fields">' + fieldsHtml + '</div>' +
      '</div>'
    );
  }

  // ── Add section ─────────────────────────────────────────────────────────────
  $(document).on('click', '.ah-add-sec', function() {
    var type = $(this).data('type');
    if (sectionTemplates[type]) {
      var $card = sectionTemplates[type]();
      $('#ah-sections-builder').append($card);
      initRichEditors($card);
    }
  });

  // ── Move up/down ────────────────────────────────────────────────────────────
  $(document).on('click', '.ah-sec-up', function() {
    var $card = $(this).closest('.ah-section-card');
    var $prev = $card.prev('.ah-section-card');
    if ($prev.length) $prev.before($card);
  });
  $(document).on('click', '.ah-sec-dn', function() {
    var $card = $(this).closest('.ah-section-card');
    var $next = $card.next('.ah-section-card');
    if ($next.length) $next.after($card);
  });

  // ── Remove section ──────────────────────────────────────────────────────────
  $(document).on('click', '.ah-sec-rm', function() {
    if (confirm('Remove this section?')) {
      var $card = $(this).closest('.ah-section-card');
      destroyRichEditors($card);
      $card.remove();
    }
  });

  // ── List: add/remove items ──────────────────────────────────────────────────
  $(document).on('click', '.ah-list-add', function() {
    var $items = $(this).siblings('.ah-list-items');
    $items.append(
      '<div class="ah-list-item" style="display:flex;gap:6px;margin-bottom:6px;">' +
      '<input type="text" class="ah-list-item-text" placeholder="List item…" style="flex:1;box-sizing:border-box;">' +
      '<button type="button" class="ah-list-rm ah-btn ah-btn-danger ah-btn-sm">✕</button>' +
      '</div>'
    );
    $items.find('.ah-list-item:last input').focus();
  });
  $(document).on('click', '.ah-list-rm', function() {
    var $items = $(this).closest('.ah-list-items');
    if ($items.find('.ah-list-item').length > 1) $(this).closest('.ah-list-item').remove();
    else alert('At least one item is required.');
  });

  // ── Table: add row/column ───────────────────────────────────────────────────
  var cellStyle = 'padding:4px;border:1px solid var(--ah-border);';
  $(document).on('click', '.ah-table-add-row', function() {
    var $table = $(this).closest('.ah-section-card').find('.ah-table-editor');
    var cols   = $table.find('thead tr th').length || 2;
    var $tr    = $('<tr>');
    for (var i = 0; i < cols; i++) {
      $tr.append('<td style="' + cellStyle + '"><input type="text" placeholder="Cell" style="width:100%;min-width:80px;box-sizing:border-box;"></td>');
    }
    $table.find('tbody').append($tr);
  });
  $(document).on('click', '.ah-table-add-col', function() {
    var $table = $(this).closest('.ah-section-card').find('.ah-table-editor');
    var cols   = $table.find('thead tr th').length + 1;
    $table.find('thead tr').append('<th style="' + cellStyle + 'background:var(--ah-bg-light);"><input type="text" placeholder="Header ' + cols + '" style="width:100%;min-width:80px;box-sizing:border-box;"></th>');
    $table.find('tbody tr').each(function() {
      $(this).append('<td style="' + cellStyle + '"><input type="text" placeholder="Cell" style="width:100%;min-width:80px;box-sizing:border-box;"></td>');
    });
  });

  // ── Serialize sections → JSON on submit ─────────────────────────────────────
  $('#ah-custom-editor-form').on('submit', function() {
    syncRichEditors();
    var sections = [];
    $('#ah-sections-builder .ah-section-card').each(function() {
      var $c   = $(this);
      var type = $c.data('type');
      var s    = { type: type };
      switch (type) {
        case 'heading':
          s.level = $c.find('.ah-sec-level').val();
          s.text  = $c.find('.ah-sec-text').val();
          break;
        case 'paragraph':
          s.text = $c.find('.ah-sec-text').val();
          break;
        case 'list':
          s.ordered = $c.find('.ah-sec-list-type').val() === '1';
          s.items   = [];
          $c.find('.ah-list-item-text').each(function() { if (this.value.trim()) s.items.push(this.value); });
          break;
        case 'table':
          s.headers = [];
          $c.find('thead th input').each(function() { s.headers.push(this.value); });
          s.rows = [];
          $c.find('tbody tr').each(function() {
            var row = [];
            $(this).find('td input').each(function() { row.push(this.value); });
            s.rows.push(row);
          });
          break;
        case 'quote':
          s.text = $c.find('.ah-sec-text').val();
          s.cite = $c.find('.ah-sec-cite').val();
          break;
        case 'cta':
          s.text = $c.find('.ah-sec-cta-text').val();
          s.url  = $c.find('.ah-sec-cta-url').val();
          break;
      }
      sections.push(s);
    });
    $('#ah-sections-json').val(JSON.stringify(sections));
  });

  initRichEditors($('#ah-sections-builder'));
  $(window).on('load', function() {
    initRichEditors($('#ah-sections-builder'));
  });

})(jQuery);
</script>

<?php /* ══════════════ LIST VIEW ══════════════ */ ?>
<?php else : ?>

  <?php
  $status_opts = array( '' => 'All Statuses' );
  foreach ( [ 'publish' => 'Published', 'draft' => 'Draft', 'private' => 'Private', 'pending' => 'Pending' ] as $sv => $sl ) {
    $status_opts[ $sv ] = $sl;
  }
  $cat_opts = array( '0' => 'All Categories' );
  foreach ( get_categories( [ 'hide_empty' => false ] ) as $_fc ) {
    $cat_opts[ $_fc->term_id ] = $_fc->name;
  }
  $author_opts = array( '0' => 'All Authors' );
  foreach ( get_users( [ 'capability' => 'edit_posts', 'orderby' => 'display_name' ] ) as $_fu ) {
    $author_opts[ $_fu->ID ] = $_fu->display_name;
  }
  $flag_opts = array( '' => 'All Posts', 'featured' => '⭐ Featured', 'popular' => '🔥 Popular', 'suggested' => '💡 Suggested' );
  \Ah\Cms\Admin\Components\AdminComponents::filterBar( array(
    'page_slug'          => 'ah-posts',
    'search_placeholder' => 'Search posts…',
    'search_value'       => $search,
    'filters'            => array(
      array( 'name' => 'status',     'options' => $status_opts, 'selected' => $status_f ),
      array( 'name' => 'cat',        'options' => $cat_opts,    'selected' => $cat_f ),
      array( 'name' => 'author_id',  'options' => $author_opts, 'selected' => $author_f ),
      array( 'name' => 'flag',       'options' => $flag_opts,   'selected' => $flag_f ),
    ),
    'add_url'   => add_query_arg( [ 'page' => 'ah-posts', 'action' => 'templates' ], admin_url( 'admin.php' ) ),
    'add_label' => '📋 From Template',
  ) );
  ?>

  <?php if ( empty( $posts_list ) ) : ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:8px;">
      <?php ob_start(); ?>
        <div style="font-size:2.5rem;margin-bottom:12px;">✍️</div>
        <h3 style="margin:0 0 8px;">Blank Post</h3>
        <p style="color:var(--ah-muted);margin:0;font-size:.85rem;">Open the WordPress editor with a blank post - write freely.</p>
      <?php \Ah\Cms\Admin\Components\AdminComponents::card( '', '<a href="' . esc_url( admin_url( 'post-new.php' ) ) . '" style="text-decoration:none;color:inherit;text-align:center;display:block;transition:box-shadow .15s;" onmouseover="this.style.boxShadow=\'0 4px 20px rgba(0,0,0,.1)\'" onmouseout="this.style.boxShadow=\'\'">' . ob_get_clean() . '</a>' ); ?>
      <?php ob_start(); ?>
        <div style="font-size:2.5rem;margin-bottom:12px;">📋</div>
        <h3 style="margin:0 0 8px;">From Template</h3>
        <p style="color:var(--ah-muted);margin:0;font-size:.85rem;">Choose a pre-filled template - Blog, News, Guide, Case Study, FAQ.</p>
      <?php \Ah\Cms\Admin\Components\AdminComponents::card( '', '<a href="' . esc_url( add_query_arg( [ 'page' => 'ah-posts', 'action' => 'templates' ], admin_url( 'admin.php' ) ) ) . '" style="text-decoration:none;color:inherit;text-align:center;display:block;transition:box-shadow .15s;border-top:3px solid var(--ah-primary);" onmouseover="this.style.boxShadow=\'0 4px 20px rgba(0,0,0,.1)\'" onmouseout="this.style.boxShadow=\'\'">' . ob_get_clean() . '</a>' ); ?>
    </div>
  <?php else : ?>
    <?php
    $qe_tax_model  = class_exists( 'AH_Content_Taxonomy_Model' ) ? new AH_Content_Taxonomy_Model() : null;
    $qe_tax_groups = $qe_tax_model ? $qe_tax_model->get_active_terms_grouped() : [];
    $posts_table_rows = array();
    foreach ( $posts_list as $p ) {
      $p_cats      = get_the_category( $p->ID );
      $p_author    = get_the_author_meta( 'display_name', $p->post_author );
      $p_editor    = get_post_meta( $p->ID, '_ah_editor_mode', true ) ?: 'gutenberg';
      $p_edit_url  = $p_editor === 'custom'
        ? add_query_arg( [ 'page' => 'ah-posts', 'action' => 'edit-custom', 'id' => $p->ID ], admin_url( 'admin.php' ) )
        : get_edit_post_link( $p->ID );
      $posts_table_rows[] = (object) array(
        'ID'             => $p->ID,
        'post_title'     => $p->post_title,
        'post_excerpt'   => $p->post_excerpt,
        'post_status'    => $p->post_status,
        'post_modified'  => $p->post_modified,
        'cats'           => $p_cats,
        'author'         => $p_author,
        'editor_mode'    => $p_editor,
        'edit_url'       => $p_edit_url,
      );
    }
    ?>
    <?php \Ah\Cms\Admin\Components\AdminComponents::dataTable( array(
      'columns' => array(
        array( 'label' => 'Title', 'render' => function ( $p ) {
          $html = '<strong>' . esc_html( $p->post_title ?: '(no title)' ) . '</strong>';
          if ( $p->post_excerpt ) {
            $html .= '<small style="color:var(--ah-muted);display:block;">' . esc_html( wp_trim_words( $p->post_excerpt, 10 ) ) . '</small>';
          }
          return $html;
        } ),
        array( 'label' => 'WP Categories', 'render' => function ( $p ) {
          return '<small>' . ( $p->cats ? esc_html( implode( ', ', wp_list_pluck( $p->cats, 'name' ) ) ) : '-' ) . '</small>';
        } ),
        array( 'label' => 'CMS Terms', 'render' => function ( $p ) {
          ob_start();
          ( new AH_Content_Taxonomy_Model() )->render_badges( 'wp_post', (int) $p->ID );
          return ob_get_clean();
        } ),
        array( 'label' => 'Status', 'render' => function ( $p ) {
          $badge = [ 'publish' => 'active', 'draft' => 'draft', 'private' => 'inactive', 'pending' => 'draft' ];
          $label = [ 'publish' => 'Published', 'draft' => 'Draft', 'private' => 'Private', 'pending' => 'Pending' ];
          return '<span class="ah-badge ah-badge-' . esc_attr( $badge[ $p->post_status ] ?? 'draft' ) . '">' . esc_html( $label[ $p->post_status ] ?? $p->post_status ) . '</span>';
        } ),
        array( 'label' => 'Editor', 'render' => function ( $p ) {
          return '<small style="opacity:.7;">' . ( $p->editor_mode === 'custom' ? '📝 Form' : '🖊 WP' ) . '</small>';
        } ),
        array( 'label' => 'Author', 'render' => function ( $p ) {
          return '<small>' . esc_html( $p->author ) . '</small>';
        } ),
        array( 'label' => 'Modified', 'render' => function ( $p ) {
          return '<small>' . esc_html( wp_date( 'M j, Y', strtotime( $p->post_modified ) ) ) . '</small>';
        } ),
        array( 'label' => 'Actions', 'render' => function ( $p ) {
          $html  = '<a href="' . esc_url( $p->edit_url ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a> ';
          $html .= '<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-qe-open" data-id="' . esc_attr( $p->ID ) . '">Edit Meta</button> ';
          if ( $p->post_status === 'publish' ) {
            $html .= '<a href="' . esc_url( get_permalink( $p->ID ) ) . '" target="_blank" class="ah-btn ah-btn-secondary ah-btn-sm">View</a> ';
          }
          $trash_url = wp_nonce_url( add_query_arg( [ 'page' => 'ah-posts', 'trash_id' => $p->ID ], admin_url( 'admin.php' ) ), 'ah_trash_post' );
          $html .= '<a href="' . esc_url( $trash_url ) . '" class="ah-btn ah-btn-danger ah-btn-sm ah-confirm-delete" data-title="Delete Post" data-confirm="This post will be moved to trash.">Delete</a>';
          return $html;
        } ),
      ),
      'items'         => $posts_table_rows,
      'empty_message' => 'No posts found.',
    ) ); ?>

    <?php if ( $pages_count > 1 ) : ?>
      <?php \Ah\Cms\Admin\Components\AdminComponents::pagination( array(
        'total'    => $total,
        'per_page' => 20,
        'current'  => $paged,
        'base'     => add_query_arg( 'paged', '%#%', admin_url( 'admin.php?page=ah-posts' ) ),
      ) ); ?>
    <?php endif; ?>
  <?php endif; ?>

<?php endif; ?>

<?php
// ── Render Edit Meta modals for each post ──
if ( ! empty( $posts_list ) && isset( $qe_tax_model ) && $qe_tax_model ) :
  foreach ( $posts_list as $mp ) :
    $mp_id     = (int) $mp->ID;
    $mp_feat   = get_post_meta( $mp_id, '_ah_is_featured', true );
    $mp_pop    = get_post_meta( $mp_id, '_ah_is_popular', true );
    $mp_sug    = get_post_meta( $mp_id, '_ah_is_suggested', true );
    $mp_hl     = json_decode( get_post_meta( $mp_id, '_ah_highlight_links', true ) ?: '[]', true );
    $mp_rl     = ( new AH_Related_Links_Model() )->get_for( 'wp_post', $mp_id );
    $mp_terms  = $qe_tax_model->get_terms( 'wp_post', $mp_id );
    $mp_term_ids = wp_list_pluck( $mp_terms, 'id' );
?>
<div class="ah-qe-modal" id="ah-qe-<?php echo $mp_id; ?>" aria-hidden="true">
  <div class="ah-qe-backdrop"></div>
  <div class="ah-qe-card">
    <div class="ah-qe-head">
      <div class="ah-qe-head-l">
        <span class="ah-qe-head-title"><?php echo esc_html( $mp->post_title ); ?></span>
        <span class="ah-qe-pill"><?php echo esc_html( $mp->post_status ); ?></span>
      </div>
      <button type="button" class="ah-qe-close ah-qe-x" aria-label="Close">&times;</button>
    </div>
    <div class="ah-qe-body">
      <div class="ah-qe-grid">
        <!-- Left column: Flags + Highlight Links -->
        <div>
          <div class="ah-qe-sec">
            <div class="ah-qe-sec-h">Post Flags</div>
            <label class="ah-qe-flag"><input type="checkbox" class="ah-qe-featured" value="1" <?php checked( $mp_feat, '1' ); ?>> Featured</label>
            <label class="ah-qe-flag"><input type="checkbox" class="ah-qe-popular" value="1" <?php checked( $mp_pop, '1' ); ?>> Popular</label>
            <label class="ah-qe-flag ah-qe-flag--last"><input type="checkbox" class="ah-qe-suggested" value="1" <?php checked( $mp_sug, '1' ); ?>> Suggested</label>
          </div>
          <div class="ah-qe-sec" style="margin-top:12px;">
            <div class="ah-qe-sec-h" style="display:flex;justify-content:space-between;align-items:center;">Highlight Links <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-qe-hl-add" data-id="<?php echo $mp_id; ?>">+ Add</button></div>
            <div id="ah-qe-hl-rows-<?php echo $mp_id; ?>">
              <?php if ( ! empty( $mp_hl ) && is_array( $mp_hl ) ) : ?>
                <?php foreach ( $mp_hl as $hl ) : ?>
                  <div class="ah-qe-hl-row" style="display:flex;gap:6px;margin-bottom:5px;align-items:center;">
                    <input type="text" class="ah-qe-hl-name" placeholder="Label" value="<?php echo esc_attr( $hl['name'] ?? '' ); ?>" style="flex:1;min-width:0;padding:4px 8px;border:1px solid var(--ah-border);border-radius:4px;font-size:.82rem;outline:none;">
                    <input type="text" class="ah-qe-hl-url" placeholder="/slug/ or URL" value="<?php echo esc_attr( $hl['url'] ?? '' ); ?>" style="flex:1.6;min-width:0;padding:4px 8px;border:1px solid var(--ah-border);border-radius:4px;font-size:.82rem;outline:none;">
                    <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-qe-hl-remove" style="flex-shrink:0;padding:3px 8px;">&#10005;</button>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <!-- Right column: Taxonomy + Related Content -->
        <div>
          <div class="ah-qe-sec">
            <div class="ah-qe-sec-h">CMS Taxonomy Terms</div>
            <div style="display:flex;flex-wrap:wrap;gap:6px;">
              <?php foreach ( $qe_tax_groups as $key => $group ) : ?>
                <?php if ( empty( $group['items'] ) ) continue; ?>
                <div style="margin-bottom:8px;width:100%;">
                  <div class="ah-qe-sub-h"><?php echo esc_html( $group['label'] ); ?></div>
                  <?php foreach ( $group['items'] as $t ) :
                    $checked = in_array( (int) $t->id, $mp_term_ids );
                  ?>
                    <label class="ah-qe-chip <?php echo $checked ? 'is-on' : ''; ?>">
                      <input type="checkbox" class="ah-qe-term" value="<?php echo (int) $t->id; ?>" <?php checked( $checked ); ?>>
                      <span><?php echo esc_html( $t->name ); ?></span>
                    </label>
                  <?php endforeach; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="ah-qe-sec" style="margin-top:12px;">
            <div class="ah-qe-sec-h" style="display:flex;justify-content:space-between;align-items:center;">Related Content <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-rl-add">+ Add</button></div>
            <div class="ah-rl-wrap">
              <div class="ah-rl-rows">
                <?php if ( ! empty( $mp_rl ) && is_array( $mp_rl ) ) : ?>
                  <?php foreach ( $mp_rl as $rl ) : ?>
                    <div class="ah-rl-row" style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:5px;">
                      <input type="text" class="ah-rl-label" placeholder="Label" value="<?php echo esc_attr( $rl->label ?? '' ); ?>" style="padding:4px 8px;border:1px solid var(--ah-border);border-radius:4px;font-size:.82rem;outline:none;">
                      <input type="text" class="ah-rl-url" placeholder="URL" value="<?php echo esc_attr( $rl->url ?? '' ); ?>" style="padding:4px 8px;border:1px solid var(--ah-border);border-radius:4px;font-size:.82rem;outline:none;">
                      <select class="ah-rl-type" style="padding:4px 8px;border:1px solid var(--ah-border);border-radius:4px;font-size:.82rem;">
                        <?php
                        $rl_types = array( 'article' => 'Article', 'external' => 'External', 'support' => 'Support', 'image' => 'Image', 'calculator' => 'Calculator', 'static_component' => 'Component' );
                        $rl_type  = $rl->link_type ?? 'external';
                        ?>
                        <?php foreach ( $rl_types as $k => $v ) : ?>
                          <option value="<?php echo esc_attr( $k ); ?>" <?php selected( $rl_type, $k ); ?>><?php echo esc_html( $v ); ?></option>
                        <?php endforeach; ?>
                      </select>
                      <input type="text" class="ah-rl-container" placeholder="Container" value="<?php echo esc_attr( $rl->container ?? '' ); ?>" style="padding:4px 8px;border:1px solid var(--ah-border);border-radius:4px;font-size:.82rem;outline:none;">
                      <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-rl-remove" style="grid-column:span 2;flex-shrink:0;padding:3px 8px;">&#10005; Remove</button>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="ah-qe-foot">
      <button type="button" class="ah-btn ah-btn-secondary ah-qe-close">Cancel</button>
      <button type="button" class="ah-btn ah-btn-primary ah-qe-save" data-id="<?php echo $mp_id; ?>">Save Changes</button>
    </div>
  </div>
</div>
<?php
  endforeach;
endif;
?>

<style>
/* ── Edit-Meta modal ─────────────────────────────────────────────────────── */
body.ah-qe-lock { overflow: hidden; }
.ah-qe-holder td { padding: 0 !important; border: 0 !important; }
.ah-qe-modal { display: none; position: fixed; inset: 0; z-index: 100000; }
.ah-qe-modal.is-open { display: block; }
.ah-qe-backdrop { position: absolute; inset: 0; background: rgba(15,23,42,.55); }
.ah-qe-card {
  position: relative; z-index: 1;
  width: min(940px, 94vw); max-height: 90vh;
  margin: 5vh auto 0;
  display: flex; flex-direction: column;
  background: #fff; border-radius: 14px; overflow: hidden;
  box-shadow: 0 24px 70px rgba(2,6,23,.35);
  animation: ahQePop .18s ease;
}
@keyframes ahQePop { from { opacity: 0; transform: translateY(16px) scale(.985); } to { opacity: 1; transform: none; } }
.ah-qe-head {
  display: flex; align-items: center; justify-content: space-between;
  padding: 15px 20px; border-bottom: 1px solid #eef2f9;
  background: linear-gradient(180deg,#f8faff,#fff);
  flex: 0 0 auto;
}
.ah-qe-head-l { display: flex; align-items: center; gap: 10px; min-width: 0; }
.ah-qe-head-title { font-size: .95rem; color: var(--ah-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ah-qe-pill { font-size: .66rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: var(--ah-primary); background: #e8efff; padding: 3px 9px; border-radius: 5px; flex: 0 0 auto; }
.ah-qe-x { border: 0; background: transparent; font-size: 1.5rem; line-height: 1; cursor: pointer; color: var(--ah-muted); padding: 0 6px; border-radius: 6px; }
.ah-qe-x:hover { background: var(--ah-bg-light); color: var(--ah-text); }
.ah-qe-body { padding: 18px 20px; overflow-y: auto; }
.ah-qe-foot { padding: 13px 20px; border-top: 1px solid #eef2f9; background: #fafbff; display: flex; justify-content: flex-end; gap: 10px; flex: 0 0 auto; }
.ah-qe-grid { display: grid; grid-template-columns: 210px 1fr; gap: 16px; align-items: start; }
@media (max-width: 782px) { .ah-qe-grid { grid-template-columns: 1fr; } }
.ah-qe-sec { background: #fff; border: 1px solid #e2ecf9; border-radius: 10px; padding: 13px 15px; }
.ah-qe-sec-h { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: var(--ah-muted); margin-bottom: 10px; }
.ah-qe-hint { font-weight: 400; font-size: .68rem; text-transform: none; letter-spacing: 0; color: var(--ah-muted); }
.ah-qe-sub-h { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: var(--ah-muted); margin-bottom: 5px; }
.ah-qe-flag { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: .85rem; padding: 6px 0; border-bottom: 1px solid var(--ah-bg-light); }
.ah-qe-flag input { width: 15px; height: 15px; }
.ah-qe-flag--last { border-bottom: 0; }
.ah-qe-chip { display: inline-flex; align-items: center; gap: 4px; font-size: .78rem; padding: 4px 11px; border: 1px solid var(--ah-border); border-radius: 20px; cursor: pointer; background: #fff; user-select: none; transition: all .12s; }
.ah-qe-chip:hover { border-color: #b9c6e0; }
.ah-qe-chip.is-on { border-color: #4f7cf5; background: #e8efff; color: #1a49c4; }
</style>

<script>
(function($){
  function qeOpenModal(id){
    $('.ah-qe-modal.is-open').removeClass('is-open').attr('aria-hidden','true');
    $('#ah-qe-' + id).addClass('is-open').attr('aria-hidden','false');
    $('body').addClass('ah-qe-lock');
  }
  function qeCloseModals(){
    $('.ah-qe-modal.is-open').removeClass('is-open').attr('aria-hidden','true');
    $('body').removeClass('ah-qe-lock');
  }
  $(document).on('click', '.ah-qe-open', function() {
    qeOpenModal($(this).data('id'));
  });
  $(document).on('click', '.ah-qe-close, .ah-qe-backdrop', function() {
    qeCloseModals();
  });
  /* Esc closes the open modal */
  $(document).on('keydown', function(e){
    if (e.key === 'Escape' || e.keyCode === 27) closeModals();
  });
  /* Chip toggle: click label → toggle checkbox + restyle via class */
  $(document).on('click', '.ah-qe-chip', function(e) {
    e.preventDefault();
    var $chip = $(this), $cb = $chip.find('.ah-qe-term');
    var checked = !$cb.prop('checked');
    $cb.prop('checked', checked);
    $chip.toggleClass('is-on', checked);
  });
  /* Highlight Links: add row */
  $(document).on('click', '.ah-qe-hl-add', function() {
    var id = $(this).data('id');
    $('#ah-qe-hl-rows-' + id).append(
      '<div class="ah-qe-hl-row" style="display:flex;gap:6px;margin-bottom:5px;align-items:center;">' +
      '<input type="text" class="ah-qe-hl-name" placeholder="Label" style="flex:1;min-width:0;padding:4px 8px;border:1px solid var(--ah-border);border-radius:4px;font-size:.82rem;outline:none;">' +
      '<input type="text" class="ah-qe-hl-url"  placeholder="/slug/ or URL" style="flex:1.6;min-width:0;padding:4px 8px;border:1px solid var(--ah-border);border-radius:4px;font-size:.82rem;outline:none;">' +
      '<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ah-qe-hl-remove" style="flex-shrink:0;padding:3px 8px;">✕</button>' +
      '</div>'
    );
  });
  /* Highlight Links: remove row */
  $(document).on('click', '.ah-qe-hl-remove', function() {
    $(this).closest('.ah-qe-hl-row').remove();
  });

  /* Related Content: add row (clone the per-panel hidden template) */
  $(document).on('click', '.ah-rl-add', function() {
    var $wrap = $(this).closest('.ah-rl-wrap');
    var $tmpl = $wrap.find('.ah-rl-template .ah-rl-row').first();
    if ( ! $tmpl.length ) { return; }
    $wrap.find('.ah-rl-rows').first().append( $tmpl.clone() );
  });
  /* Related Content: remove row */
  $(document).on('click', '.ah-rl-remove', function() {
    $(this).closest('.ah-rl-row').remove();
  });

  $(document).on('click', '.ah-qe-save', function() {
    var $btn = $(this), id = $btn.data('id'), $row = $('#ah-qe-' + id);
    var taxIds = [];
    $row.find('.ah-qe-term:checked').each(function() { taxIds.push($(this).val()); });
    /* Collect highlight link pairs */
    var hlLinks = [];
    $row.find('#ah-qe-hl-rows-' + id + ' .ah-qe-hl-row').each(function() {
      var name = $.trim($(this).find('.ah-qe-hl-name').val());
      var url  = $.trim($(this).find('.ah-qe-hl-url').val());
      if (name || url) hlLinks.push({ name: name, url: url });
    });
    /* Collect Related Content rows (real rows only, not the hidden template) */
    /* Map icon emoji → ASCII type key so the DB column never receives multi-byte emoji */
    var rlIconMap = {'📄':'article','🧮':'calculator','🧩':'static_component','🖼️':'image','🖼':'image','🔗':'external','🛟':'support'};
    var relatedLinks = [];
    $row.find('.ah-rl-wrap .ah-rl-rows .ah-rl-row').each(function() {
      var $r     = $(this);
      var url    = $.trim($r.find('.ah-rl-url').val());
      var target = $r.find('.ah-rl-target').val();
      if (!url && !target) return; // skip empty rows
      var rawIcon = $.trim($r.find('.ah-rl-type').val());
      relatedLinks.push({
        link_type:     rlIconMap[rawIcon] || rawIcon || 'external',
        target:        target,
        url:           url,
        label:         $.trim($r.find('.ah-rl-label').val()),
        container:     $.trim($r.find('.ah-rl-container').val()),
        target_window: $r.find('.ah-rl-window').val(),
        sort_order:    $r.find('.ah-rl-order').val()
      });
    });
    $btn.text('Saving…').prop('disabled', true);
    $.post(ahAdmin.ajaxUrl, {
      action:          'ah_quick_save_post_meta',
      nonce:           ahAdmin.nonce,
      post_id:         id,
      is_featured:     $row.find('.ah-qe-featured').is(':checked')  ? 1 : 0,
      is_popular:      $row.find('.ah-qe-popular').is(':checked')   ? 1 : 0,
      is_suggested:    $row.find('.ah-qe-suggested').is(':checked') ? 1 : 0,
      taxonomy_ids:    taxIds,
      highlight_links: JSON.stringify(hlLinks),
      related_links:   JSON.stringify(relatedLinks)
    }, function(res) {
      if (res.success) {
        window.location.reload();
      } else {
        alert('Error: ' + (res.data && res.data.message ? res.data.message : 'Save failed.'));
        $btn.text('Save Changes').prop('disabled', false);
      }
    }).fail(function() {
      alert('Network error. Please try again.');
      $btn.text('Save Changes').prop('disabled', false);
    });
  });
})(jQuery);
</script>
</div>
