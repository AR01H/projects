<?php
/**
 * Admin view: Add / Edit a story
 * Rendered by PT_Stories_Admin::page_stories() when action=edit|add.
 */

defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/includes/admin/class-pt-stories-db.php';

$action = sanitize_key( $_GET['action'] ?? 'add' );
$id     = sanitize_title( $_GET['id'] ?? '' );
$story  = ( $action === 'edit' && $id ) ? PT_Stories_DB::find( $id ) : null;

/* If edit was requested but ID not found, fall back to add */
if ( $action === 'edit' && ! $story ) {
	$action = 'add';
}

$is_new    = $action === 'add';
$page_title = $is_new ? 'Add New Story' : 'Edit Story';

/* Prefill defaults for add form */
$f = array_merge( [
	'id'             => '',
	'title'          => '',
	'client'         => '',
	'industry'       => '',
	'tagline'        => '',
	'summary'        => '',
	'result_1_label' => '',
	'result_1_value' => '',
	'result_2_label' => '',
	'result_2_value' => '',
	'result_3_label' => '',
	'result_3_value' => '',
	'image'          => '',
	'featured'       => 0,
	'published'      => 1,
	'sort_order'     => 0,
], (array) $story );

/* Error from redirect */
$error = sanitize_key( $_GET['error'] ?? '' );
?>
<div class="wrap pt-admin-wrap">

	<div class="pt-admin-header">
		<div class="pt-admin-logo">PT</div>
		<div>
			<h1><?php echo esc_html( $page_title ); ?></h1>
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=pt-stories' ) ); ?>">
					&larr; Back to Stories
				</a>
			</p>
		</div>
	</div>

	<?php if ( $error === 'noid' ) : ?>
		<div class="pt-notice pt-notice--err">Slug (ID) is required. Please fill in the Story Slug field.</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'pt_story_save' ); ?>
		<input type="hidden" name="action" value="pt_story_save">

		<!-- ══ Tabs nav ═══════════════════════════════════════════ -->
		<div class="pt-tabs" role="tablist">
			<button type="button" class="pt-tab-btn is-active" data-tab="details"  role="tab">Story Details</button>
			<button type="button" class="pt-tab-btn"           data-tab="results"  role="tab">Results</button>
			<button type="button" class="pt-tab-btn"           data-tab="settings" role="tab">Settings</button>
		</div>

		<!-- ══ Tab 1: Details ═════════════════════════════════════ -->
		<div class="pt-tab-pane is-active" id="pt-tab-details">
			<div class="pt-admin-box">
				<h2>Story Details</h2>
				<div class="pt-form-grid">

					<div class="pt-form-group">
						<label for="f-title">Title</label>
						<input type="text" id="f-title" name="title"
						       value="<?php echo esc_attr( $f['title'] ); ?>"
						       placeholder="Landmark Residences - Sold Out in 90 Days" required>
					</div>

					<div class="pt-form-group">
						<label for="f-id">
							Story Slug (ID)
							<?php if ( ! $is_new ) : ?>
								<span style="font-weight:400;text-transform:none;letter-spacing:0;color:#d97706">- changing this creates a new record</span>
							<?php endif; ?>
						</label>
						<input type="text" id="f-id" name="id"
						       value="<?php echo esc_attr( $f['id'] ); ?>"
						       placeholder="landmark-residences"
						       <?php echo $is_new ? '' : 'style="background:#f8fafc"'; ?>
						       required>
						<span class="pt-form-hint">URL-safe, lowercase, hyphens only. Auto-fills from title if empty.</span>
					</div>

					<div class="pt-form-group">
						<label for="f-client">Client Name</label>
						<input type="text" id="f-client" name="client"
						       value="<?php echo esc_attr( $f['client'] ); ?>"
						       placeholder="Landmark Group">
					</div>

					<div class="pt-form-group">
						<label for="f-industry">Industry</label>
						<input type="text" id="f-industry" name="industry"
						       value="<?php echo esc_attr( $f['industry'] ); ?>"
						       placeholder="Residential, Commercial, etc.">
					</div>

					<div class="pt-form-group full">
						<label for="f-tagline">Tagline <span class="pt-form-hint">(shown under title on cards)</span></label>
						<input type="text" id="f-tagline" name="tagline"
						       value="<?php echo esc_attr( $f['tagline'] ); ?>"
						       placeholder="Premium residential launch that sold out in record time.">
					</div>

					<div class="pt-form-group full">
						<label for="f-summary">Summary <span class="pt-form-hint">(shown on featured card only)</span></label>
						<textarea id="f-summary" name="summary" rows="4"
						          placeholder="Describe the challenge, approach, and outcome in 2-3 sentences..."><?php echo esc_textarea( $f['summary'] ); ?></textarea>
					</div>

					<div class="pt-form-group full">
						<label for="f-image">Image URL</label>
						<input type="url" id="f-image" name="image"
						       value="<?php echo esc_attr( $f['image'] ); ?>"
						       placeholder="https://…/story-image.jpg">
						<span class="pt-form-hint">Use the Media Library URL. Recommended ratio 16:9.</span>
					</div>

				</div>
			</div>
		</div>

		<!-- ══ Tab 2: Results ═════════════════════════════════════ -->
		<div class="pt-tab-pane" id="pt-tab-results">
			<div class="pt-admin-box">
				<h2>Results / Metrics</h2>
				<p style="color:#64748b;font-size:.875rem;margin:0 0 20px">Up to 3 key outcome metrics shown as a results strip on each story card.</p>

				<div class="pt-results-grid">

					<?php for ( $i = 1; $i <= 3; $i++ ) :
						$lk = "result_{$i}_label";
						$vk = "result_{$i}_value";
					?>
					<div class="pt-result-card">
						<h3>Result <?php echo $i; ?></h3>
						<div class="pt-result-pair" style="margin-bottom:14px">
							<label for="f-<?php echo $vk; ?>">Value</label>
							<input type="text" id="f-<?php echo $vk; ?>" name="<?php echo $vk; ?>"
							       value="<?php echo esc_attr( $f[ $vk ] ); ?>"
							       placeholder="120 / 34% / 90 days"
							       data-result-val="<?php echo $i; ?>">
						</div>
						<div class="pt-result-pair">
							<label for="f-<?php echo $lk; ?>">Label</label>
							<input type="text" id="f-<?php echo $lk; ?>" name="<?php echo $lk; ?>"
							       value="<?php echo esc_attr( $f[ $lk ] ); ?>"
							       placeholder="Units Sold / ROI Increase"
							       data-result-lbl="<?php echo $i; ?>">
						</div>
						<!-- Live preview -->
						<div class="pt-result-preview" id="pt-preview-<?php echo $i; ?>">
							<div>
								<div class="pt-result-preview__val" id="pt-pv-<?php echo $i; ?>">
									<?php echo esc_html( $f[ $vk ] ?: '-' ); ?>
								</div>
								<div class="pt-result-preview__lbl" id="pt-pl-<?php echo $i; ?>">
									<?php echo esc_html( $f[ $lk ] ?: 'Label' ); ?>
								</div>
							</div>
						</div>
					</div>
					<?php endfor; ?>

				</div>
			</div>
		</div>

		<!-- ══ Tab 3: Settings ════════════════════════════════════ -->
		<div class="pt-tab-pane" id="pt-tab-settings">
			<div class="pt-admin-box">
				<h2>Settings</h2>
				<div class="pt-form-grid">

					<div class="pt-form-group">
						<label for="f-sort_order">Sort Order</label>
						<input type="number" id="f-sort_order" name="sort_order"
						       value="<?php echo (int) $f['sort_order']; ?>"
						       min="0" step="1" placeholder="0">
						<span class="pt-form-hint">Lower number appears first. You can also drag rows in the list view.</span>
					</div>

					<div class="pt-form-group">
						<label>&nbsp;</label>
						<div class="pt-form-checkbox-row">
							<input type="checkbox" id="f-featured" name="featured" value="1"
							       <?php checked( 1, (int) $f['featured'] ); ?>>
							<label for="f-featured">Featured Story</label>
						</div>
						<div class="pt-form-checkbox-row">
							<input type="checkbox" id="f-published" name="published" value="1"
							       <?php checked( 1, (int) $f['published'] ); ?>>
							<label for="f-published">Published (visible on site)</label>
						</div>
					</div>

				</div>
			</div>
		</div>

		<!-- ══ Submit ═════════════════════════════════════════════ -->
		<div style="display:flex;gap:12px;align-items:center;margin-top:8px;">
			<input type="submit" class="button button-primary button-large"
			       value="<?php echo $is_new ? 'Create Story' : 'Save Changes'; ?>">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=pt-stories' ) ); ?>"
			   class="button button-large">Cancel</a>
		</div>

	</form>

</div><!-- .wrap -->

<script>
(function(){
	/* ── Tab switching ─────────────────────────────────────────── */
	var btns  = document.querySelectorAll('.pt-tab-btn');
	var panes = document.querySelectorAll('.pt-tab-pane');

	btns.forEach(function(btn){
		btn.addEventListener('click', function(){
			btns.forEach(function(b){ b.classList.remove('is-active'); });
			panes.forEach(function(p){ p.classList.remove('is-active'); });
			btn.classList.add('is-active');
			document.getElementById('pt-tab-' + btn.dataset.tab).classList.add('is-active');
		});
	});

	/* ── Auto-slug from title (add only) ───────────────────────── */
	<?php if ( $is_new ) : ?>
	var titleEl = document.getElementById('f-title');
	var idEl    = document.getElementById('f-id');
	var idTouched = false;

	idEl.addEventListener('input', function(){ idTouched = true; });
	titleEl.addEventListener('input', function(){
		if ( idTouched ) return;
		idEl.value = titleEl.value
			.toLowerCase()
			.replace(/[^a-z0-9\s-]/g, '')
			.trim()
			.replace(/\s+/g, '-');
	});
	<?php endif; ?>

	/* ── Live result preview ────────────────────────────────────── */
	for ( var i = 1; i <= 3; i++ ) {
		(function(idx){
			var valEl = document.querySelector('[data-result-val="' + idx + '"]');
			var lblEl = document.querySelector('[data-result-lbl="' + idx + '"]');
			var pvEl  = document.getElementById('pt-pv-' + idx);
			var plEl  = document.getElementById('pt-pl-' + idx);

			if ( valEl && pvEl ) {
				valEl.addEventListener('input', function(){ pvEl.textContent = this.value || '-'; });
			}
			if ( lblEl && plEl ) {
				lblEl.addEventListener('input', function(){ plEl.textContent = this.value || 'Label'; });
			}
		}(i));
	}
}());
</script>
