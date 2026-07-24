<?php
/**
 * Home Hero Banners - admin manager.
 * List + Edit pattern with reusable components.
 */
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

use Ah\Cms\Admin\Components\AdminComponents;

require_once AH_THEME_DIR . '/helper/BannersHelper.php';

$action  = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id = (int) ( $_GET['id'] ?? 0 );
$notice  = '';
$n_type  = 'success';
$saved   = isset( $_GET['saved'] );

// ── POST: save single banner ──
if ( isset( $_POST['save_banner'] ) && wp_verify_nonce( $_POST['ah_banner_nonce'] ?? '', 'ah_save_banner' ) ) {
	$save_id = (int) ( $_POST['banner_id'] ?? 0 );
	$data = $_POST;
	// Convert media IDs to URLs (supports both images and videos)
	if ( ! empty( $data['image_id'] ) && is_numeric( $data['image_id'] ) ) {
		$att_url = wp_get_attachment_url( (int) $data['image_id'] );
		$data['image'] = $att_url ?? '';
	}
	if ( ! empty( $data['image_mobile_id'] ) && is_numeric( $data['image_mobile_id'] ) ) {
		$att_url = wp_get_attachment_url( (int) $data['image_mobile_id'] );
		$data['image_mobile'] = $att_url ?? '';
	}
	AH_Banners_Helper::save_single( $save_id, $data );
	$notice = $save_id ? 'Banner updated.' : 'Banner created.';
	$action = 'list';
}

// ── POST: save autoplay ──
if ( isset( $_POST['save_autoplay'] ) && wp_verify_nonce( $_POST['ah_banner_nonce'] ?? '', 'ah_save_banner' ) ) {
	AH_Banners_Helper::save_autoplay( (int) ( $_POST['autoplay_ms'] ?? 5000 ) );
	$notice = 'Autoplay speed saved.';
}

// ── GET: delete ──
if ( isset( $_GET['delete_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_del_banner' ) ) {
	AH_Banners_Helper::delete_single( (int) $_GET['delete_id'] );
	$notice = 'Banner deleted.';
	$action = 'list';
}

if ( $saved ) { $notice = 'Banners saved.'; }

$autoplay = AH_Banners_Helper::get_autoplay();
$align_opts = array( 'left' => 'Left', 'center' => 'Center', 'right' => 'Right' );
$pos_opts   = array( 'top' => 'Top', 'middle' => 'Middle', 'bottom' => 'Bottom' );
?>
<div class="wrap ah-wrap">
	<?php AdminComponents::pageHeader( 'format-gallery', 'Home Hero Banners', 'Create and manage hero banner slides shown on the homepage.' ); ?>
	<?php if ( $notice ) : ?><?php AdminComponents::notice( $notice, $n_type ); ?><?php endif; ?>

	<?php if ( $action === 'add' || $action === 'edit' ) :
		$banner = $edit_id ? AH_Banners_Helper::find( $edit_id ) : null;
	?>
		<?php AdminComponents::backLink( add_query_arg( array( 'page' => 'ah-banners' ), admin_url( 'admin.php' ) ) ); ?>
		<?php ob_start(); ?>
			<form method="post">
				<?php wp_nonce_field( 'ah_save_banner', 'ah_banner_nonce' ); ?>
				<input type="hidden" name="banner_id" value="<?php echo esc_attr( $edit_id ); ?>">
				<div style="display:flex;justify-content:flex-end;gap:8px;margin-bottom:16px;">
					<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-banners' ), admin_url( 'admin.php' ) ) ); ?>" class="ah-btn ah-btn-secondary">Cancel</a>
					<button type="submit" name="save_banner" value="1" class="ah-btn ah-btn-primary">Save</button>
				</div>
				<div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;">
					<!-- Left: Text fields -->
					<div>
						<?php AdminComponents::formRow( 'Title', '<input type="text" name="title" value="' . esc_attr( $banner['title'] ?? '' ) . '" placeholder="The Cane House"><p class="description">HTML allowed: &lt;br&gt; &lt;em&gt; &lt;strong&gt;</p>' ); ?>
						<?php AdminComponents::formRow( 'Subtitle', '<input type="text" name="subtitle" value="' . esc_attr( $banner['subtitle'] ?? '' ) . '" placeholder="Welcome to">' ); ?>
						<?php AdminComponents::formRow( 'Description', '<textarea name="description" rows="2">' . esc_textarea( $banner['description'] ?? '' ) . '</textarea>' ); ?>
						<?php AdminComponents::formGrid( array(
							array( 'Button Text', '<input type="text" name="btn_text" value="' . esc_attr( $banner['btn_text'] ?? '' ) . '" placeholder="Explore Now">' ),
							array( 'Button URL', '<input type="text" name="btn_url" value="' . esc_attr( $banner['btn_url'] ?? '' ) . '" placeholder="/events/ or https://…">' ),
						) ); ?>
						<?php AdminComponents::formGrid( array(
							array( 'Open In', '<select name="btn_target"><option value="_self"' . selected( $banner['btn_target'] ?? '_self', '_self', false ) . '>Same tab</option><option value="_blank"' . selected( $banner['btn_target'] ?? '', '_blank', false ) . '>New tab</option></select>' ),
							array( 'Text Horizontal', '<select name="text_align">' . implode( '', array_map( function ( $v, $l ) use ( $banner ) { return '<option value="' . esc_attr( $v ) . '"' . selected( $banner['text_align'] ?? 'center', $v, false ) . '>' . esc_html( $l ) . '</option>'; }, array_keys( $align_opts ), $align_opts ) ) . '</select>' ),
							array( 'Text Vertical', '<select name="text_pos">' . implode( '', array_map( function ( $v, $l ) use ( $banner ) { return '<option value="' . esc_attr( $v ) . '"' . selected( $banner['text_pos'] ?? 'middle', $v, false ) . '>' . esc_html( $l ) . '</option>'; }, array_keys( $pos_opts ), $pos_opts ) ) . '</select>' ),
						) ); ?>
						<?php AdminComponents::formGrid( array(
							array( 'Overlay', '<input type="text" name="overlay" value="' . esc_attr( $banner['overlay'] ?? 'rgba(26,58,15,0.45)' ) . '" placeholder="rgba(26,58,15,0.45)">' ),
							array( 'Status', '<select name="status"><option value="active"' . selected( $banner['status'] ?? 'active', 'active', false ) . '>Active</option><option value="inactive"' . selected( $banner['status'] ?? '', 'inactive', false ) . '>Hidden</option></select>' ),
							array( 'Order', '<input type="number" name="sort_order" value="' . esc_attr( $banner['sort_order'] ?? 0 ) . '">' ),
						) ); ?>
					</div>
					<!-- Right: Media (stacked, compact) -->
					<div style="display:flex;flex-direction:column;gap:16px;">
						<?php AdminComponents::mediaField( 'image_id', 'Desktop Image / Video', $banner['image_id'] ?? $banner['image'] ?? '', array( 'type' => 'media' ) ); ?>
						<?php AdminComponents::mediaField( 'image_mobile_id', 'Mobile Image / Video <small>(optional)</small>', $banner['image_mobile_id'] ?? $banner['image_mobile'] ?? '', array( 'type' => 'media' ) ); ?>
					</div>
				</div>
			</form>
		<?php AdminComponents::card( $edit_id ? 'Edit Banner' : 'Add Banner', ob_get_clean() ); ?>

	<?php else : ?>
		<?php
		$banners = AH_Banners_Helper::get_all();
		// Autoplay setting
		ob_start();
		?>
		<form method="post" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
			<?php wp_nonce_field( 'ah_save_banner', 'ah_banner_nonce' ); ?>
			<label style="font-weight:600;font-size:13px;">Auto-slide speed</label>
			<input type="number" name="autoplay_ms" min="1000" max="30000" step="500" value="<?php echo esc_attr( $autoplay ); ?>" style="width:100px;"> ms
			<span style="color:var(--ah-muted);font-size:12px;">(how long each slide stays)</span>
			<button type="submit" name="save_autoplay" value="1" class="ah-btn ah-btn-secondary ah-btn-sm">Save Speed</button>
		</form>
		<?php AdminComponents::card( 'Global Settings', ob_get_clean() ); ?>

		<?php AdminComponents::filterBar( array(
			'page_slug'          => 'ah-banners',
			'search_placeholder' => 'Search banners…',
			'hidden_inputs'      => array(),
			'add_url'            => add_query_arg( array( 'page' => 'ah-banners', 'action' => 'add' ), admin_url( 'admin.php' ) ),
			'add_label'          => '+ Add Banner',
		) ); ?>

		<?php
		$banner_rows = array();
		foreach ( $banners as $b ) {
			$row = new \stdClass();
			$row->id = $b['id'] ?? 0;
			$row->title = wp_strip_all_tags( str_replace( '<br>', ' ', $b['title'] ?? '' ) ) ?: '(no title)';
			$row->subtitle = $b['subtitle'] ?? '';
			$row->status = $b['status'] ?? 'active';
			$row->sort_order = $b['sort_order'] ?? 0;
			$row->image = $b['image'] ?? '';
			$row->edit_url = add_query_arg( array( 'page' => 'ah-banners', 'action' => 'edit', 'id' => $b['id'] ), admin_url( 'admin.php' ) );
			$row->delete_url = wp_nonce_url( add_query_arg( array( 'page' => 'ah-banners', 'delete_id' => $b['id'] ), admin_url( 'admin.php' ) ), 'ah_del_banner' );
			$banner_rows[] = $row;
		}
		AdminComponents::dataTable( array(
			'columns' => array(
				array( 'label' => '', 'style' => 'width:50px', 'render' => function ( $r ) {
					if ( ! $r->image ) return '<span style="color:var(--ah-muted);">—</span>';
					$ext = strtolower( pathinfo( wp_parse_url( $r->image, PHP_URL_PATH ) ?? '', PATHINFO_EXTENSION ) );
					if ( in_array( $ext, array( 'mp4', 'webm', 'ogv', 'ogg', 'mov' ), true ) ) {
						return '<span style="display:inline-flex;align-items:center;justify-content:center;width:40px;height:24px;background:#1e293b;border-radius:4px;color:#fff;font-size:10px;">&#9654;</span>';
					}
					return '<img src="' . esc_url( $r->image ) . '" style="width:40px;height:24px;object-fit:cover;border-radius:4px;">';
				} ),
				array( 'label' => 'Title', 'render' => function ( $r ) {
					$html = '<strong>' . esc_html( $r->title ) . '</strong>';
					if ( $r->subtitle ) $html .= '<br><small style="color:var(--ah-muted);">' . esc_html( $r->subtitle ) . '</small>';
					return $html;
				} ),
				array( 'label' => 'Status', 'render' => function ( $r ) {
					return '<span class="ah-badge ah-badge-' . esc_attr( $r->status ) . '">' . esc_html( $r->status ) . '</span>';
				} ),
				array( 'label' => 'Order', 'render' => function ( $r ) {
					return (int) $r->sort_order;
				} ),
			),
			'items'         => $banner_rows,
			'empty_message' => 'No banners yet. Click "+ Add Banner" to create one.',
			'actions'       => function ( $r ) {
				$html = '<a href="' . esc_url( $r->edit_url ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>';
				$html .= ' <a href="' . esc_url( $r->delete_url ) . '" class="ah-btn ah-btn-danger ah-btn-sm ah-confirm-delete" data-title="Delete Banner" data-confirm="Delete this banner slide?">Delete</a>';
				return $html;
			},
		) ); ?>
	<?php endif; ?>
</div>
