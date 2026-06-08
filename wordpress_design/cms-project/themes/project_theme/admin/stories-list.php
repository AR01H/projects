<?php
/**
 * Admin view: Stories list table
 * Rendered by PT_Stories_Admin::page_stories() when action=list (default).
 */

defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/includes/admin/class-pt-stories-db.php';

$stories  = PT_Stories_DB::all();
$saved    = isset( $_GET['saved'] )    ? (int) $_GET['saved']    : -1;
$deleted  = isset( $_GET['deleted'] )  ? (bool) $_GET['deleted'] : false;
$reordered = isset( $_GET['reordered'] ) ? (bool) $_GET['reordered'] : false;
?>
<div class="wrap pt-admin-wrap">

	<div class="pt-admin-header">
		<div class="pt-admin-logo">PT</div>
		<div>
			<h1>Stories</h1>
			<p>Client success stories and case studies.</p>
		</div>
	</div>

	<?php if ( $saved === 1 ) : ?>
		<div class="pt-notice pt-notice--ok">Story saved successfully.</div>
	<?php elseif ( $saved === 0 ) : ?>
		<div class="pt-notice pt-notice--err">Save failed. Please try again.</div>
	<?php elseif ( $deleted ) : ?>
		<div class="pt-notice pt-notice--ok">Story deleted.</div>
	<?php elseif ( $reordered ) : ?>
		<div class="pt-notice pt-notice--ok">Sort order saved.</div>
	<?php endif; ?>

	<div class="pt-stories-table-wrap">

		<div class="pt-stories-toolbar">
			<h2>All Stories (<?php echo count( $stories ); ?>)</h2>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=pt-stories&action=add' ) ); ?>" class="button button-primary">
				+ Add New
			</a>
		</div>

		<?php if ( empty( $stories ) ) : ?>
			<div style="padding:40px;text-align:center;color:#94a3b8;">
				<p style="font-size:1.05rem;margin-bottom:16px;">No stories yet.</p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=pt-stories&action=add' ) ); ?>" class="button button-primary button-hero">
					Add Your First Story
				</a>
			</div>
		<?php else : ?>

		<!-- Reorder form (hidden inputs updated by JS sortable) -->
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="pt-reorder-form">
			<?php wp_nonce_field( 'pt_story_reorder' ); ?>
			<input type="hidden" name="action" value="pt_story_reorder">
			<div id="pt-order-inputs">
				<?php foreach ( $stories as $s ) : ?>
					<input type="hidden" name="order[]" value="<?php echo esc_attr( $s['id'] ); ?>">
				<?php endforeach; ?>
			</div>
		</form>

		<table class="pt-stories-table" id="pt-stories-table">
			<thead>
				<tr>
					<th class="col-sort"></th>
					<th>Story</th>
					<th>Client</th>
					<th>Industry</th>
					<th style="width:80px;text-align:center">Featured</th>
					<th style="width:80px;text-align:center">Published</th>
					<th class="col-order">Order</th>
					<th style="width:100px">Actions</th>
				</tr>
			</thead>
			<tbody id="pt-stories-tbody">
				<?php foreach ( $stories as $s ) : ?>
				<tr data-id="<?php echo esc_attr( $s['id'] ); ?>">
					<td class="col-sort">
						<span class="pt-drag-handle" title="Drag to reorder">&#8597;</span>
					</td>
					<td>
						<div class="pt-story-title"><?php echo esc_html( $s['title'] ); ?></div>
						<div class="pt-story-id"><?php echo esc_html( $s['id'] ); ?></div>
					</td>
					<td><?php echo esc_html( $s['client'] ); ?></td>
					<td><?php echo esc_html( $s['industry'] ); ?></td>
					<td style="text-align:center">
						<span class="pt-badge <?php echo $s['featured'] ? 'pt-badge--yes' : 'pt-badge--no'; ?>">
							<?php echo $s['featured'] ? 'Yes' : 'No'; ?>
						</span>
					</td>
					<td style="text-align:center">
						<span class="pt-badge <?php echo $s['published'] ? 'pt-badge--yes' : 'pt-badge--no'; ?>">
							<?php echo $s['published'] ? 'Live' : 'Draft'; ?>
						</span>
					</td>
					<td class="col-order" style="text-align:center;color:#94a3b8">
						<?php echo (int) $s['sort_order']; ?>
					</td>
					<td>
						<div class="pt-row-actions">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=pt-stories&action=edit&id=' . urlencode( $s['id'] ) ) ); ?>">Edit</a>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
							      onsubmit="return confirm('Delete &laquo;<?php echo esc_js( $s['title'] ); ?>&raquo;? This cannot be undone.')">
								<?php wp_nonce_field( 'pt_story_delete' ); ?>
								<input type="hidden" name="action" value="pt_story_delete">
								<input type="hidden" name="id" value="<?php echo esc_attr( $s['id'] ); ?>">
								<button type="submit" class="del">Delete</button>
							</form>
						</div>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php endif; ?>

	</div><!-- .pt-stories-table-wrap -->

</div><!-- .wrap -->

<?php if ( ! empty( $stories ) ) : ?>
<script>
(function($){
	var $tbody   = $('#pt-stories-tbody');
	var $inputs  = $('#pt-order-inputs');

	$tbody.sortable({
		handle: '.pt-drag-handle',
		placeholder: 'pt-sortable-ghost',
		axis: 'y',
		update: function(){
			// Rebuild hidden inputs in the new order
			var newInputs = '';
			$tbody.find('tr').each(function(){
				var id = $(this).data('id');
				newInputs += '<input type="hidden" name="order[]" value="' + id + '">';
			});
			$inputs.html( newInputs );
		}
	});

	// Auto-submit reorder form when dragging stops
	$tbody.on( 'sortupdate', function(){
		$('#pt-reorder-form').submit();
	});
}(jQuery));
</script>
<?php endif; ?>
