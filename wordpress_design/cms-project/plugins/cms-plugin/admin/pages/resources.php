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
<h1><span class="dashicons dashicons-video-alt3"></span> Resources</h1>
<?php if ( $notice ) : ?><div class="ah-notice ah-notice-<?php echo esc_attr( $n_type ); ?>"><?php echo esc_html( $notice ); ?></div><?php endif; ?>

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
<div class="ah-table-top" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:16px;">
	<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-resources', 'action' => 'edit' ), admin_url( 'admin.php' ) ) ); ?>" class="button button-primary">+ Add Resource</a>
	<form method="get" style="display:flex;gap:6px;flex-wrap:wrap;flex:1;">
		<input type="hidden" name="page" value="ah-resources">
		<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search…" style="min-width:180px;">
		<select name="type_filter">
			<option value="">All Types</option>
			<?php foreach ( $type_labels as $k => $v ) : ?>
				<option value="<?php echo esc_attr( $k ); ?>"<?php selected( $f_type, $k ); ?>><?php echo esc_html( $v ); ?></option>
			<?php endforeach; ?>
		</select>
		<select name="status_filter">
			<option value="">All Statuses</option>
			<option value="active"<?php selected( $f_stat, 'active' ); ?>>Active</option>
			<option value="inactive"<?php selected( $f_stat, 'inactive' ); ?>>Inactive</option>
		</select>
		<button type="submit" class="button">Filter</button>
	</form>
</div>

<table class="wp-list-table widefat fixed striped">
	<thead>
		<tr>
			<th style="width:40px;">ID</th>
			<th>Title</th>
			<th style="width:150px;">Type</th>
			<th style="width:80px;">Order</th>
			<th style="width:80px;">Status</th>
			<th style="width:130px;">Shortcode</th>
			<th style="width:110px;">Actions</th>
		</tr>
	</thead>
	<tbody>
	<?php if ( empty( $items ) ) : ?>
		<tr><td colspan="8" style="text-align:center;padding:24px;color:#999;">No resources yet. <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-resources', 'action' => 'edit' ), admin_url( 'admin.php' ) ) ); ?>">Add one →</a></td></tr>
	<?php else : ?>
		<?php foreach ( $items as $item ) :
			$edit_url   = add_query_arg( array( 'page' => 'ah-resources', 'action' => 'edit', 'id' => $item->id ), admin_url( 'admin.php' ) );
			$delete_url = wp_nonce_url( add_query_arg( array( 'page' => 'ah-resources', 'delete_id' => $item->id ), admin_url( 'admin.php' ) ), 'ah_del_resource' );
			$ctx_parts  = array_filter( explode( ',', (string) $item->context ) );
			$ctx_labels = array_map( function( $c ) use ( $context_labels ) { return $context_labels[ $c ] ?? $c; }, $ctx_parts );
		?>
		<tr>
			<td><?php echo esc_html( $item->id ); ?></td>
			<td>
				<strong><a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $item->title ?: '(no title)' ); ?></a></strong>
				<?php if ( $item->url ) : ?>
					<br><small style="color:#999;"><?php echo esc_html( mb_strimwidth( (string) $item->url, 0, 60, '…' ) ); ?></small>
				<?php endif; ?>
			</td>
			<td><?php echo esc_html( $type_labels[ $item->type ] ?? $item->type ); ?></td>
			<td><?php echo esc_html( $item->sort_order ); ?></td>
			<td><span style="color:<?php echo $item->status === 'active' ? '#22c55e' : '#6b7280'; ?>;"><?php echo esc_html( $item->status ); ?></span></td>
			<td><code style="font-size:11px;">[ah_resource id="<?php echo esc_attr( $item->id ); ?>"]</code></td>
			<td>
				<a href="<?php echo esc_url( $edit_url ); ?>" class="button button-small">Edit</a>
				<a href="<?php echo esc_url( $delete_url ); ?>" class="button button-small ah-confirm-delete" style="color:#dc2626;">Del</a>
			</td>
		</tr>
		<?php endforeach; ?>
	<?php endif; ?>
	</tbody>
</table>

<?php if ( $meta['total_pages'] > 1 ) : ?>
<div class="tablenav bottom" style="margin-top:12px;">
	<div class="tablenav-pages">
		<?php
		$page_url = add_query_arg( array( 'page' => 'ah-resources', 's' => $search, 'type_filter' => $f_type, 'ctx_filter' => $f_ctx, 'status_filter' => $f_stat ), admin_url( 'admin.php' ) );
		for ( $pg = 1; $pg <= $meta['total_pages']; $pg++ ) :
			$is_cur = ( $pg === $paged );
		?>
		<a href="<?php echo esc_url( add_query_arg( 'paged', $pg, $page_url ) ); ?>"
		   class="button<?php echo $is_cur ? ' button-primary' : ''; ?>"
		   style="margin:0 1px;"><?php echo esc_html( $pg ); ?></a>
		<?php endfor; ?>
		<span style="margin-left:8px;color:#666;"><?php echo esc_html( $meta['total'] ); ?> total</span>
	</div>
</div>
<?php endif; ?>

<div style="margin-top:20px;padding:14px 16px;background:#f8f9fa;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;">
	<strong>Shortcode Reference:</strong><br>
	<code>[ah_resource id="1"]</code> - embed a single resource by ID<br>
	<code>[ah_resources context="category" type="youtube" limit="3"]</code> - list resources by context/type<br>
	<code>[ah_resources context="home" show_title="1" show_desc="1"]</code> - with title and description<br>
	<em>Available contexts: <?php echo esc_html( implode( ', ', array_keys( $context_labels ) ) ); ?></em>
</div>

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
<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-resources' ), admin_url( 'admin.php' ) ) ); ?>" class="button" style="margin-bottom:16px;">← Back to list</a>

<form method="post" action="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-resources', 'action' => 'edit', 'id' => $edit_id ?: '' ), admin_url( 'admin.php' ) ) ); ?>">
<?php wp_nonce_field( 'ah_save_resource', '_ah_res_nonce' ); ?>

<div style="display:grid;grid-template-columns:1fr 280px;gap:20px;max-width:1000px;">

<div>
	<!-- Core fields -->
	<div class="ah-card" style="margin-bottom:16px;">
		<h3 style="margin:0 0 16px;">Resource Details</h3>

		<table class="form-table" style="margin:0;">
			<tr>
				<th style="width:130px;">Type</th>
				<td>
					<select name="type" id="res-type" style="min-width:220px;">
						<?php foreach ( $type_labels as $k => $lbl ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>"<?php selected( $v['type'], $k ); ?>><?php echo esc_html( $lbl ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th>Title / Label</th>
				<td><input type="text" name="title" value="<?php echo esc_attr( $v['title'] ); ?>" style="width:100%;" placeholder="Label name printed on the card"></td>
			</tr>
			<tr>
				<th>Highlight Label</th>
				<td>
					<input type="text" name="highlight_label" value="<?php echo esc_attr( $v['highlight_label'] ); ?>" style="width:240px;" placeholder="e.g. Image · Instagram · Video" maxlength="40">
					<p class="description">Short tag shown on the card. Type anything — Image, Video, Instagram, etc.</p>
				</td>
			</tr>
			<tr id="row-url">
				<th><label id="url-label">URL</label></th>
				<td><input type="url" name="url" value="<?php echo esc_url( $v['url'] ); ?>" style="width:100%;" placeholder="https://…" id="res-url"></td>
			</tr>
			<tr id="row-embed">
				<th>Embed Code</th>
				<td>
					<textarea name="embed_code" rows="5" style="width:100%;font-family:monospace;font-size:12px;" placeholder="Paste the embed/iframe code here…"><?php echo esc_textarea( $v['embed_code'] ); ?></textarea>
					<p class="description">For Instagram, Facebook, TikTok, Twitter: paste the platform's embed code here, or just paste the URL above (WordPress will try to auto-embed).</p>
				</td>
			</tr>
			<tr>
				<th>Thumbnail URL</th>
				<td><input type="url" name="thumbnail_url" value="<?php echo esc_url( $v['thumbnail_url'] ); ?>" style="width:100%;" placeholder="Optional override thumbnail image URL"></td>
			</tr>
			<tr>
				<th><label>Link URL</label></th>
				<td>
					<input type="url" name="link_url" value="<?php echo esc_url( $v['link_url'] ); ?>" style="width:100%;" placeholder="https://… (optional — shows a Learn More link on front end)">
					<p class="description">If set, a "Learn more →" link is shown below this resource wherever it appears on the site.</p>
				</td>
			</tr>
		</table>
	</div>
</div>

<div>
	<!-- Settings sidebar -->
	<div class="ah-card" style="margin-bottom:16px;">
		<h3 style="margin:0 0 14px;">Publish</h3>
		<label><strong>Status</strong></label><br>
		<select name="status" style="width:100%;margin:4px 0 12px;">
			<option value="active"<?php selected( $v['status'], 'active' ); ?>>Active</option>
			<option value="inactive"<?php selected( $v['status'], 'inactive' ); ?>>Inactive</option>
		</select>

		<label><strong>Sort Order</strong></label><br>
		<input type="number" name="sort_order" value="<?php echo esc_attr( $v['sort_order'] ); ?>" style="width:100%;margin:4px 0 12px;" min="0">

		<button type="submit" class="button button-primary" style="width:100%;">Save Resource</button>
	</div>

	<?php if ( $edit_id ) : ?>
	<div class="ah-card">
		<h3 style="margin:0 0 10px;">Shortcode</h3>
		<code>[ah_resource id="<?php echo esc_attr( $edit_id ); ?>"]</code>
	</div>
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
