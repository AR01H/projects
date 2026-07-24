<?php
/**
 * Static HTML Pages - admin manager.
 * List + Edit pattern with reusable components.
 */
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use Ah\Cms\Admin\Components\AdminComponents;

$static_model = new AH_Static_Pages_Model();
$action       = sanitize_key( $_GET['action'] ?? 'list' );
$edit_slug    = isset( $_GET['edit'] ) ? sanitize_file_name( wp_unslash( $_GET['edit'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$notice       = '';
$n_type       = 'success';

// ── POST: save static page ──
if ( isset( $_POST['save_static_page'] ) && wp_verify_nonce( $_POST['ah_sp_nonce'] ?? '', 'ah_save_static_page' ) ) {
	$old_slug = sanitize_file_name( wp_unslash( $_POST['old_slug'] ?? '' ) );
	$slug     = sanitize_file_name( wp_unslash( $_POST['slug'] ?? '' ) );
	$title    = sanitize_text_field( $_POST['title'] ?? '' );
	$html     = wp_unslash( $_POST['html'] ?? '' );

	if ( ! $slug ) {
		$notice = 'Page slug is required.';
		$n_type = 'error';
	} elseif ( ! trim( $html ) ) {
		$notice = 'HTML content is empty.';
		$n_type = 'error';
	} else {
		$model  = new AH_Static_Pages_Model();
		$slug_changed = $old_slug !== '' && $old_slug !== $slug;
		$page_title   = $title !== '' ? $title : ucwords( str_replace( '-', ' ', $slug ) );

		$existing = get_page_by_path( $slug_changed ? $old_slug : $slug );
		if ( $existing ) {
			$page_id = (int) $existing->ID;
			wp_update_post( array( 'ID' => $page_id, 'post_title' => $page_title, 'post_name' => $slug ) );
			update_post_meta( $page_id, '_wp_page_template', 'TemplateStaticPage.php' );
			if ( $slug_changed ) {
				$model->delete_by_slug( $old_slug );
			}
			$model->upsert( $slug, $html, $page_id, $page_title );
			$notice = $old_slug && ! $slug_changed ? 'Page updated.' : 'Page renamed and updated.';
		} else {
			$page_id = wp_insert_post( array(
				'post_title'  => $page_title,
				'post_name'   => $slug,
				'post_status' => 'publish',
				'post_type'   => 'page',
			) );
			$page_id = ( is_wp_error( $page_id ) || ! $page_id ) ? 0 : (int) $page_id;
			if ( $page_id ) {
				update_post_meta( $page_id, '_wp_page_template', 'TemplateStaticPage.php' );
			}
			$model->upsert( $slug, $html, $page_id, $page_title );
			$notice = 'Page created.';
		}
		$action = 'list';
	}
}

// ── GET: delete ──
if ( isset( $_GET['delete_slug'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_del_static_page' ) ) {
	$del_slug = sanitize_file_name( wp_unslash( $_GET['delete_slug'] ) );
	$static_model->delete_by_slug( $del_slug );
	// Also delete the backing WP page
	$del_page = get_page_by_path( $del_slug );
	if ( $del_page ) {
		wp_delete_post( (int) $del_page->ID, true );
	}
	$notice = 'Page deleted.';
	$action = 'list';
}

$search = sanitize_text_field( $_GET['s'] ?? '' );
?>
<div class="wrap ah-wrap">
	<?php AdminComponents::pageHeader( 'editor-code', 'Static HTML Pages', 'Create and manage raw HTML pages. Each page renders inside your site layout with full style isolation.' ); ?>
	<?php if ( $notice ) : ?><?php AdminComponents::notice( $notice, $n_type ); ?><?php endif; ?>

	<?php if ( $action === 'add' || $action === 'edit' ) :
		$edit_row = $edit_slug ? $static_model->get_by_slug( $edit_slug ) : null;
		$edit_title   = $edit_row ? (string) ( $edit_row->title ?? '' ) : '';
		$edit_content = $edit_row ? (string) $edit_row->html : '';
		$page         = get_page_by_path( $edit_slug );
		$page_url     = $page ? get_permalink( $page->ID ) : '';
	?>
		<?php AdminComponents::backLink( add_query_arg( array( 'page' => 'ah-static-pages' ), admin_url( 'admin.php' ) ) ); ?>
		<?php ob_start(); ?>
			<form method="post">
				<?php wp_nonce_field( 'ah_save_static_page', 'ah_sp_nonce' ); ?>
				<input type="hidden" name="old_slug" value="<?php echo esc_attr( $edit_slug ); ?>">

				<div style="display:flex;justify-content:flex-end;gap:8px;margin-bottom:16px;">
					<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-static-pages' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary">Cancel</a>
					<button type="submit" name="save_static_page" value="1" class="ah-btn ah-btn-primary"><?php echo $edit_slug ? 'Update Page' : 'Create Page'; ?></button>
				</div>

				<div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;">
					<!-- Left: Editor -->
					<div>
						<?php AdminComponents::formRow( 'Page Slug', '<input type="text" name="slug" value="' . esc_attr( $edit_slug ) . '" placeholder="e.g. privacy-policy" required><p class="description">Lowercase letters, numbers, hyphens. Creates <code>/slug/</code> on your site.</p>' ); ?>
						<?php AdminComponents::formRow( 'Page Title', '<input type="text" name="title" value="' . esc_attr( $edit_title ) . '" placeholder="e.g. Privacy Policy">' ); ?>
						<?php AdminComponents::formRow( 'HTML Content',
							'<textarea name="html" rows="30" style="width:100%;font-family:Consolas,Monaco,monospace;font-size:12.5px;line-height:1.6;resize:vertical;" placeholder="Paste your full HTML here - include &lt;!DOCTYPE html&gt;, &lt;head&gt;, &lt;style&gt;, &lt;body&gt;, etc.">' . esc_textarea( $edit_content ) . '</textarea>'
						); ?>
					</div>

					<!-- Right: Settings -->
					<div>
						<?php if ( $edit_slug ) : ?>
						<?php AdminComponents::formRow( 'Shortcode',
							'<div style="background:#f0f4ff;border:1px solid #c5d0e6;border-radius:6px;padding:10px 14px;">'
							. '<code style="display:block;font-size:12px;color:#1e3a5f;word-break:break-all;">[ah_static_page slug="' . esc_attr( $edit_slug ) . '"]</code>'
							. '</div>'
						); ?>
						<?php AdminComponents::formRow( 'Quick Links', implode( '', array_filter( array(
							$page_url ? '<a href="' . esc_url( $page_url ) . '" target="_blank" class="ah-btn ah-btn-secondary ah-btn-sm" style="width:100%;justify-content:center;margin-bottom:6px;">View Page</a>' : '',
							'<a href="' . esc_url( admin_url( 'admin.php?page=ah-static-pages&edit=' . rawurlencode( $edit_slug ) . '&raw=1' ) ) . '" target="_blank" class="ah-btn ah-btn-secondary ah-btn-sm" style="width:100%;justify-content:center;margin-bottom:6px;">Raw / Print</a>',
							'<a href="' . esc_url( admin_url( 'admin.php?page=ah-static-pages&edit=' . rawurlencode( $edit_slug ) . '&themed=1' ) ) . '" target="_blank" class="ah-btn ah-btn-secondary ah-btn-sm" style="width:100%;justify-content:center;margin-bottom:6px;">Match Theme</a>',
						) ) ) ); ?>
						<?php endif; ?>

						<div class="ah-card" style="padding:14px;font-size:12px;color:var(--ah-muted);line-height:1.6;">
							<strong style="color:var(--ah-text);">How it works</strong><br>
							1. Enter a slug and paste your HTML.<br>
							2. A WordPress page is auto-created at <code>/slug/</code>.<br>
							3. Add it to your nav via <em>Appearance &rarr; Menus</em>.<br><br>
							<strong style="color:var(--ah-text);">Style isolation</strong><br>
							Content renders inside a same-origin <code>&lt;iframe&gt;</code> - your HTML is fully isolated from theme CSS.
						</div>
					</div>
				</div>
			</form>
		<?php AdminComponents::card( $edit_slug ? 'Edit: ' . $edit_slug : 'New Static Page', ob_get_clean() ); ?>

	<?php else : ?>
		<?php
		$all_pages = $static_model->all();
		// Filter by search
		if ( $search ) {
			$all_pages = array_values( array_filter( $all_pages, function ( $p ) use ( $search ) {
				return stripos( $p->slug, $search ) !== false || stripos( $p->title ?? '', $search ) !== false;
			} ) );
		}
		?>

		<?php AdminComponents::filterBar( array(
			'page_slug'          => 'ah-static-pages',
			'search_placeholder' => 'Search pages...',
			'search_value'       => $search,
			'add_url'            => add_query_arg( array( 'page' => 'ah-static-pages', 'action' => 'add' ), admin_url( 'admin.php' ) ),
			'add_label'          => '+ New Page',
		) ); ?>

		<?php
		$rows = array();
		foreach ( $all_pages as $sp ) {
			$s    = $sp->slug;
			$page = get_page_by_path( $s );
			$url  = $page ? get_permalink( $page->ID ) : '';

			$row = new \stdClass();
			$row->id       = $sp->id ?? 0;
			$row->slug     = $s;
			$row->title    = $sp->title ?? ucwords( str_replace( '-', ' ', $s ) );
			$row->status   = $sp->status ?? 'active';
			$row->has_page = (bool) $page;
			$row->view_url = $url;
			$row->edit_url = add_query_arg( array( 'page' => 'ah-static-pages', 'action' => 'edit', 'edit' => $s ), admin_url( 'admin.php' ) );
			$row->delete_url = wp_nonce_url( add_query_arg( array( 'page' => 'ah-static-pages', 'delete_slug' => $s ), admin_url( 'admin.php' ) ), 'ah_del_static_page' );
			$rows[] = $row;
		}
		AdminComponents::dataTable( array(
			'columns' => array(
				array( 'label' => 'Slug', 'render' => function ( $r ) {
					return '<strong style="font-family:monospace;font-size:13px;">' . esc_html( $r->slug ) . '</strong>';
				} ),
				array( 'label' => 'Title', 'render' => function ( $r ) {
					return esc_html( $r->title );
				} ),
				array( 'label' => 'Status', 'render' => function ( $r ) {
					return $r->has_page
						? '<span class="ah-badge ah-badge-active">Published</span>'
						: '<span class="ah-badge ah-badge-draft">No WP Page</span>';
				} ),
				array( 'label' => 'Shortcode', 'render' => function ( $r ) {
					return '<code style="font-size:11px;background:#f0f4ff;color:#3b5bdb;padding:2px 6px;border-radius:3px;border:1px solid #c5d0e6;" title="Click to copy" class="ah-copy-shortcode" data-sc=\'[ah_static_page slug="' . esc_attr( $r->slug ) . '"]\'>[ah_static_page slug="' . esc_html( $r->slug ) . '"]</code>';
				} ),
			),
			'items'         => $rows,
			'empty_message' => 'No static pages yet. Click "+ New Page" to create one.',
			'actions'       => function ( $r ) {
				$html = '<a href="' . esc_url( $r->edit_url ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>';
				if ( $r->view_url ) {
					$html .= ' <a href="' . esc_url( $r->view_url ) . '" target="_blank" class="ah-btn ah-btn-secondary ah-btn-sm">View</a>';
				}
				$html .= ' <a href="' . esc_url( $r->delete_url ) . '" class="ah-btn ah-btn-danger ah-btn-sm ah-confirm-delete" data-title="Delete Page" data-confirm="Delete the static page \'' . esc_attr( $r->slug ) . '\'? This will also remove the backing WordPress page.">Delete</a>';
				return $html;
			},
		) ); ?>
	<?php endif; ?>
</div>

<script>
jQuery(function ($) {
	// Copy shortcode to clipboard
	$(document).on('click', '.ah-copy-shortcode', function () {
		var $el = $(this);
		var sc  = $el.data('sc');
		if (navigator.clipboard) {
			navigator.clipboard.writeText(sc).then(function () {
				var orig = $el.text();
				$el.text('Copied!').css('background','#d1fae5').css('color','#065f46');
				setTimeout(function(){ $el.text(orig).css('background','').css('color',''); }, 1500);
			});
		}
	});
});
</script>
