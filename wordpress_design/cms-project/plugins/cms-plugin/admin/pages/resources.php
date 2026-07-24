<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

$model  = new AH_Resources_Model();
$notice = '';
$n_type = 'success';
$action = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id = (int) ( $_GET['id'] ?? 0 );

// ── Save ──────────────────────────────────────────────────────────────────────
if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	if ( ! wp_verify_nonce( $_POST['_ah_res_nonce'] ?? '', 'ah_save_resource' ) ) wp_die( 'Security.' );

	$contexts = array_map( 'sanitize_key', (array) ( $_POST['context'] ?? array() ) );

	$data = array(
		'type'          => sanitize_key( $_POST['type'] ?? 'youtube' ),
		'title'         => sanitize_text_field( $_POST['title'] ?? '' ),
		'url'           => esc_url_raw( $_POST['url'] ?? '' ),
		'embed_code'    => $_POST['embed_code'] ?? '',
		'thumbnail_url' => esc_url_raw( $_POST['thumbnail_url'] ?? '' ),

		'link_url'        => esc_url_raw( $_POST['link_url'] ?? '' ),
		'highlight_label' => sanitize_text_field( $_POST['highlight_label'] ?? '' ),
		'context'       => implode( ',', $contexts ),
		'sort_order'    => (int) ( $_POST['sort_order'] ?? 0 ),
		'status'        => sanitize_key( $_POST['status'] ?? 'active' ),
	);

	// Sanitize embed_code: allow iframe + common social embed tags only.
	$allowed_embed = array_merge( wp_kses_allowed_html( 'post' ), array(
		'iframe'  => array( 'src' => true, 'width' => true, 'height' => true, 'frameborder' => true, 'allowfullscreen' => true, 'allow' => true, 'loading' => true, 'style' => true, 'scrolling' => true ),
		'script'  => array( 'async' => true, 'src' => true, 'charset' => true ),
		'blockquote' => array( 'class' => true, 'data-instgrm-permalink' => true, 'data-instgrm-version' => true, 'style' => true ),
	) );
	$data['embed_code'] = wp_kses( $data['embed_code'], $allowed_embed );

	if ( $edit_id ) {
		$model->update( $edit_id, $data );
		$notice = 'Resource updated.';
	} else {
		$edit_id = $model->create( $data );
		$notice  = 'Resource saved.';
	}
	$action  = 'list';
	$edit_id = 0;
}

// ── Delete ────────────────────────────────────────────────────────────────────
if ( isset( $_GET['delete_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_del_resource' ) ) {
	$model->delete( (int) $_GET['delete_id'] );
	$notice = 'Resource deleted.';
}

$type_labels    = AH_Resources_Model::type_labels();
$context_labels = AH_Resources_Model::context_labels();
?>
<div class="wrap ah-wrap">
<?php \Ah\Cms\Admin\Components\AdminComponents::pageHeader( 'video-alt3', 'Resources', 'Add videos, documents, and embedded content for the resources section.' ); ?>
<?php if ( $notice ) : ?><?php \Ah\Cms\Admin\Components\AdminComponents::notice( $notice, $n_type ); ?><?php endif; ?>

<?php if ( $action === 'list' ) :
	$search  = sanitize_text_field( $_GET['s'] ?? '' );
	$f_type  = sanitize_key( $_GET['type_filter'] ?? '' );
	$f_ctx   = sanitize_key( $_GET['ctx_filter'] ?? '' );
	$f_stat  = sanitize_key( $_GET['status_filter'] ?? '' );
	$paged   = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
	$result  = $model->get_paginated( $paged, $search, $f_stat, $f_type, $f_ctx );
	$items   = $result['items'];
	$meta    = $result['meta'];
?>
<?php \Ah\Cms\Admin\Components\AdminComponents::filterBar( array(
	'page_slug'          => 'ah-resources',
	'search_placeholder' => 'Search…',
	'search_value'       => $search,
	'filters'            => array(
		array(
			'name'     => 'type_filter',
			'options'  => array_merge( array( '' => 'All Types' ), $type_labels ),
			'selected' => $f_type,
		),
		array(
			'name'     => 'status_filter',
			'options'  => array( '' => 'All Statuses', 'active' => 'Active', 'inactive' => 'Inactive' ),
			'selected' => $f_stat,
		),
	),
	'add_url'   => add_query_arg( array( 'page' => 'ah-resources', 'action' => 'edit' ), admin_url( 'admin.php' ) ),
	'add_label' => '+ Add Resource',
) ); ?>

<?php
\Ah\Cms\Admin\Components\AdminComponents::dataTable( array(
	'columns' => array(
		array( 'label' => 'ID', 'style' => 'width:40px', 'render' => function ( $item ) {
			return esc_html( $item->id );
		} ),
		array( 'label' => 'Title', 'render' => function ( $item ) use ( $type_labels ) {
			$edit_url = add_query_arg( array( 'page' => 'ah-resources', 'action' => 'edit', 'id' => $item->id ), admin_url( 'admin.php' ) );
			$html = '<strong><a href="' . esc_url( $edit_url ) . '">' . esc_html( $item->title ?: '(no title)' ) . '</a></strong>';
			if ( $item->url ) {
				$html .= '<br><small style="color:#999;">' . esc_html( mb_strimwidth( (string) $item->url, 0, 60, '…' ) ) . '</small>';
			}
			return $html;
		} ),
		array( 'label' => 'Type', 'style' => 'width:150px', 'render' => function ( $item ) use ( $type_labels ) {
			return esc_html( $type_labels[ $item->type ] ?? $item->type );
		} ),
		array( 'label' => 'Order', 'style' => 'width:80px', 'render' => function ( $item ) {
			return esc_html( $item->sort_order );
		} ),
		array( 'label' => 'Status', 'style' => 'width:80px', 'render' => function ( $item ) {
			return \Ah\Cms\Admin\Components\AdminComponents::statusBadge( $item->status );
		} ),
		array( 'label' => 'Shortcode', 'style' => 'width:130px', 'render' => function ( $item ) {
			return '<code style="font-size:11px;">[ah_resource id="' . esc_attr( $item->id ) . '"]</code>';
		} ),
	),
	'items'         => $items,
	'empty_message' => 'No resources yet.',
	'actions'       => function ( $item ) {
		$edit_url   = add_query_arg( array( 'page' => 'ah-resources', 'action' => 'edit', 'id' => $item->id ), admin_url( 'admin.php' ) );
		$delete_url = wp_nonce_url( add_query_arg( array( 'page' => 'ah-resources', 'delete_id' => $item->id ), admin_url( 'admin.php' ) ), 'ah_del_resource' );
		$html = '<a href="' . esc_url( $edit_url ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>';
		ob_start();
		\Ah\Cms\Admin\Components\AdminComponents::confirmDelete( $delete_url );
		$html .= ob_get_clean();
		return $html;
	},
) ); ?>

<?php echo AH_Pagination::render( $meta ); ?>

<?php ob_start(); ?>
	<strong>Shortcode Reference:</strong><br>
	<code>[ah_resource id="1"]</code> - embed a single resource by ID<br>
	<code>[ah_resources context="category" type="youtube" limit="3"]</code> - list resources by context/type<br>
	<code>[ah_resources context="home" show_title="1" show_desc="1"]</code> - with title and description<br>
	<em>Available contexts: <?php echo esc_html( implode( ', ', array_keys( $context_labels ) ) ); ?></em>
<?php \Ah\Cms\Admin\Components\AdminComponents::formSection( 'Shortcode Reference', ob_get_clean() ); ?>

<?php elseif ( $action === 'edit' ) :
	$item = $edit_id ? $model->find( $edit_id ) : null;
	$v    = array(
		'type'          => $item ? $item->type          : 'youtube',
		'title'         => $item ? $item->title         : '',
		'url'           => $item ? $item->url           : '',
		'embed_code'    => $item ? $item->embed_code    : '',
		'thumbnail_url' => $item ? $item->thumbnail_url : '',

		'link_url'        => $item ? ( $item->link_url ?? '' ) : '',
		'highlight_label' => $item ? ( $item->highlight_label ?? '' ) : '',
		'context'       => $item ? array_filter( explode( ',', (string) $item->context ) ) : array(),
		'sort_order'    => $item ? $item->sort_order    : 0,
		'status'        => $item ? $item->status        : 'active',
	);
?>
<?php \Ah\Cms\Admin\Components\AdminComponents::backLink( add_query_arg( array( 'page' => 'ah-resources' ), admin_url( 'admin.php' ) ), '← Back to list' ); ?>

<form method="post" action="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-resources', 'action' => 'edit', 'id' => $edit_id ?: '' ), admin_url( 'admin.php' ) ) ); ?>">
<?php wp_nonce_field( 'ah_save_resource', '_ah_res_nonce' ); ?>

<div style="display:grid;grid-template-columns:1fr 280px;gap:20px;max-width:1000px;">

<div>
	<!-- Core fields -->
	<?php ob_start(); ?>
		<?php
		$type_select = '<select name="type" id="res-type" style="min-width:220px;">';
		foreach ( $type_labels as $k => $lbl ) {
			$type_select .= '<option value="' . esc_attr( $k ) . '"' . selected( $v['type'], $k, false ) . '>' . esc_html( $lbl ) . '</option>';
		}
		$type_select .= '</select>';
		?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Type', $type_select ); ?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Title / Label', '<input type="text" name="title" value="' . esc_attr( $v['title'] ) . '" style="width:100%;" placeholder="Label name printed on the card">' ); ?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Highlight Label', '<input type="text" name="highlight_label" value="' . esc_attr( $v['highlight_label'] ) . '" style="width:240px;" placeholder="e.g. Image · Instagram · Video" maxlength="40"><p class="description">Short tag shown on the card. Type anything — Image, Video, Instagram, etc.</p>' ); ?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( '<label id="url-label">URL</label>', '<input type="url" name="url" value="' . esc_url( $v['url'] ) . '" style="width:100%;" placeholder="https://…" id="res-url">', '', 'row-url' ); ?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Embed Code', '<textarea name="embed_code" rows="5" style="width:100%;font-family:monospace;font-size:12px;" placeholder="Paste the embed/iframe code here…">' . esc_textarea( $v['embed_code'] ) . '</textarea><p class="description">For Instagram, Facebook, TikTok, Twitter: paste the platform\'s embed code here, or just paste the URL above (WordPress will try to auto-embed).</p>', '', 'row-embed' ); ?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Thumbnail URL', '<input type="url" name="thumbnail_url" value="' . esc_url( $v['thumbnail_url'] ) . '" style="width:100%;" placeholder="Optional override thumbnail image URL">' ); ?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Link URL', '<input type="url" name="link_url" value="' . esc_url( $v['link_url'] ) . '" style="width:100%;" placeholder="https://… (optional — shows a Learn More link on front end)"><p class="description">If set, a "Learn more →" link is shown below this resource wherever it appears on the site.</p>' ); ?>
	<?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Resource Details', ob_get_clean() ); ?>
</div>

<div>
	<!-- Settings sidebar -->
	<?php
	$status_select = '<select name="status" style="width:100%;margin:4px 0 12px;">'
		. '<option value="active"' . selected( $v['status'], 'active', false ) . '>Active</option>'
		. '<option value="inactive"' . selected( $v['status'], 'inactive', false ) . '>Inactive</option>'
		. '</select>';
	?>
	<?php ob_start(); ?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( '<strong>Status</strong>', $status_select ); ?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::formRow( '<strong>Sort Order</strong>', '<input type="number" name="sort_order" value="' . esc_attr( $v['sort_order'] ) . '" style="width:100%;margin:4px 0 12px;" min="0">' ); ?>
		<button type="submit" class="ah-btn ah-btn-primary" style="width:100%;">Save Resource</button>
	<?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Publish', ob_get_clean() ); ?>

	<?php if ( $edit_id ) : ?>
	<?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Shortcode', '<code>[ah_resource id="' . esc_attr( $edit_id ) . '"]</code>' ); ?>
	<?php endif; ?>
</div>

</div><!-- grid -->
</form>

<script>
(function($){
	var TYPE_URL_LABEL = {
		youtube: 'YouTube URL', shorts: 'YouTube Shorts URL',
		instagram: 'Instagram Post/Reel URL', facebook: 'Facebook URL',
		twitter: 'Twitter/X Post URL', tiktok: 'TikTok Video URL',
		image: 'Image URL', audio: 'Audio File URL', pdf: 'PDF File URL',
		embed: ''
	};
	var EMBED_TYPES = ['instagram','facebook','twitter','tiktok','embed'];
	function applyType(type) {
		var label = TYPE_URL_LABEL[type] || 'URL';
		$('#url-label').text(label);
		if (type === 'embed') {
			$('#row-url').hide(); $('#row-embed').show();
		} else if (EMBED_TYPES.indexOf(type) !== -1) {
			$('#row-url').show(); $('#row-embed').show();
		} else {
			$('#row-url').show(); $('#row-embed').hide();
		}
		// Update placeholder
		var ph = {
			youtube:'https://www.youtube.com/watch?v=…',
			shorts:'https://www.youtube.com/shorts/…',
			instagram:'https://www.instagram.com/p/…',
			facebook:'https://www.facebook.com/…/videos/…',
			twitter:'https://twitter.com/user/status/…',
			tiktok:'https://www.tiktok.com/@user/video/…',
			image:'https://example.com/image.jpg',
			audio:'https://example.com/audio.mp3',
			pdf:'https://example.com/file.pdf'
		};
		$('#res-url').attr('placeholder', ph[type] || 'https://…');
	}
	applyType($('#res-type').val());
	$('#res-type').on('change', function(){ applyType($(this).val()); });
})(jQuery);
</script>

<?php endif; ?>
</div>
