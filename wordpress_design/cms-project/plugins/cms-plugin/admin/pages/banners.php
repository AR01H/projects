<?php
/**
 * Home Hero Banners - admin manager (plugin level).
 * Repeater UI: add / remove / drag-reorder banner slides, each saved to the DB.
 */
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

require_once AH_THEME_DIR . '/helper/class-banners-helper.php';

$banners  = AH_Banners_Helper::get_all();         // all (active + inactive) for editing
$autoplay = AH_Banners_Helper::get_autoplay();
$saved    = isset( $_GET['saved'] ) ? (int) $_GET['saved'] : 0;

// First run: pre-fill the editor with defaults so the user sees example rows.
if ( empty( $banners ) ) {
	$banners = AH_Banners_Helper::defaults();
}

$align_opts = array( 'left' => 'Left', 'center' => 'Center', 'right' => 'Right' );
$pos_opts   = array( 'top' => 'Top', 'middle' => 'Middle', 'bottom' => 'Bottom' );

/**
 * Render a single banner row. $i is the numeric index, $b the data (or empty for template).
 */
if ( ! function_exists( 'ah_banner_row' ) ) :
function ah_banner_row( $i, array $b, array $align_opts, array $pos_opts ): void {
	$image    = $b['image']        ?? '';
	$image_m  = $b['image_mobile'] ?? '';
	$subtitle = $b['subtitle']    ?? '';
	$title    = $b['title']       ?? '';
	$desc     = $b['description']  ?? '';
	$btn_text = $b['btn_text']    ?? '';
	$btn_url  = $b['btn_url']     ?? '';
	$target   = $b['btn_target']  ?? '_self';
	$align    = $b['text_align']  ?? 'center';
	$pos      = $b['text_pos']    ?? 'middle';
	$overlay  = $b['overlay']     ?? 'rgba(26,58,15,0.45)';
	$status   = $b['status']      ?? 'active';
	$n        = '[' . $i . ']';
	?>
	<div class="ahb-row" data-row>
		<div class="ahb-row__head">
			<span class="ahb-drag dashicons dashicons-move" title="Drag to reorder"></span>
			<strong class="ahb-row__title"><?php echo esc_html( wp_strip_all_tags( str_replace( '<br>', ' ', $title ) ) ?: 'New Banner' ); ?></strong>
			<label class="ahb-status">
				<select name="banners<?php echo esc_attr( $n ); ?>[status]">
					<option value="active"   <?php selected( $status, 'active' ); ?>>● Active</option>
					<option value="inactive" <?php selected( $status, 'inactive' ); ?>>○ Hidden</option>
				</select>
			</label>
			<button type="button" class="button ahb-remove" title="Remove banner">✕</button>
		</div>

		<div class="ahb-row__body">
			<!-- Images: desktop + optional mobile -->
			<div class="ahb-images">
				<div class="ahb-field ahb-field--image">
					<label>Desktop Image <small>(wide - landscape works best)</small></label>
					<div class="ahb-img-wrap">
						<div class="ahb-img-preview" <?php echo $image ? 'style="background-image:url(\'' . esc_url( $image ) . '\')"' : ''; ?>></div>
						<div class="ahb-img-controls">
							<input type="url" class="ahb-img-url" name="banners<?php echo esc_attr( $n ); ?>[image]"
								value="<?php echo esc_attr( $image ); ?>" placeholder="https://…/banner.jpg">
							<button type="button" class="button ahb-pick">Choose / Upload</button>
						</div>
					</div>
				</div>
				<div class="ahb-field ahb-field--image">
					<label>Mobile Image <small>(optional - tall/portrait; falls back to desktop)</small></label>
					<div class="ahb-img-wrap">
						<div class="ahb-img-preview ahb-img-preview--mobile" <?php echo $image_m ? 'style="background-image:url(\'' . esc_url( $image_m ) . '\')"' : ''; ?>></div>
						<div class="ahb-img-controls">
							<input type="url" class="ahb-img-url" name="banners<?php echo esc_attr( $n ); ?>[image_mobile]"
								value="<?php echo esc_attr( $image_m ); ?>" placeholder="Leave blank to reuse desktop">
							<button type="button" class="button ahb-pick">Choose / Upload</button>
						</div>
					</div>
				</div>
			</div>

			<div class="ahb-grid">
				<div class="ahb-field">
					<label>Subtitle <small>(small text above the title)</small></label>
					<input type="text" name="banners<?php echo esc_attr( $n ); ?>[subtitle]" value="<?php echo esc_attr( $subtitle ); ?>" placeholder="Welcome to">
				</div>
				<div class="ahb-field">
					<label>Title <small>(&lt;br&gt; / &lt;em&gt; / &lt;strong&gt; allowed)</small></label>
					<input type="text" name="banners<?php echo esc_attr( $n ); ?>[title]" value="<?php echo esc_attr( $title ); ?>" placeholder="The Cane House">
				</div>
			</div>

			<div class="ahb-field">
				<label>Description</label>
				<textarea name="banners<?php echo esc_attr( $n ); ?>[description]" rows="2" placeholder="Short supporting line…"><?php echo esc_textarea( $desc ); ?></textarea>
			</div>

			<div class="ahb-grid">
				<div class="ahb-field">
					<label>Button Text <small>(blank = no button)</small></label>
					<input type="text" name="banners<?php echo esc_attr( $n ); ?>[btn_text]" value="<?php echo esc_attr( $btn_text ); ?>" placeholder="Explore Now">
				</div>
				<div class="ahb-field">
					<label>Button URL</label>
					<input type="text" name="banners<?php echo esc_attr( $n ); ?>[btn_url]" value="<?php echo esc_attr( $btn_url ); ?>" placeholder="/events/ or https://…">
				</div>
				<div class="ahb-field">
					<label>Open In</label>
					<select name="banners<?php echo esc_attr( $n ); ?>[btn_target]">
						<option value="_self"  <?php selected( $target, '_self' ); ?>>Same tab</option>
						<option value="_blank" <?php selected( $target, '_blank' ); ?>>New tab</option>
					</select>
				</div>
			</div>

			<div class="ahb-grid">
				<div class="ahb-field">
					<label>Text Horizontal</label>
					<select name="banners<?php echo esc_attr( $n ); ?>[text_align]">
						<?php foreach ( $align_opts as $v => $l ) : ?>
							<option value="<?php echo esc_attr( $v ); ?>" <?php selected( $align, $v ); ?>><?php echo esc_html( $l ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="ahb-field">
					<label>Text Vertical</label>
					<select name="banners<?php echo esc_attr( $n ); ?>[text_pos]">
						<?php foreach ( $pos_opts as $v => $l ) : ?>
							<option value="<?php echo esc_attr( $v ); ?>" <?php selected( $pos, $v ); ?>><?php echo esc_html( $l ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="ahb-field">
					<label>Overlay Darkness <small>(CSS colour)</small></label>
					<input type="text" name="banners<?php echo esc_attr( $n ); ?>[overlay]" value="<?php echo esc_attr( $overlay ); ?>" placeholder="rgba(26,58,15,0.45)">
				</div>
			</div>
		</div>
	</div>
	<?php
}
endif;
?>

<div class="wrap ahb-wrap">
	<h1>🖼️ Home Hero Banners</h1>
	<p style="color:#666;max-width:760px;">
		Manage the rotating hero banner shown on the home page. Add as many slides as you like,
		drag to reorder, and hide any without deleting. Saved to the database - the theme reads it automatically.
	</p>

	<?php if ( $saved ) : ?>
		<div class="notice notice-success is-dismissible"><p><strong>✅ Banners saved.</strong></p></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="ahb-form">
		<?php wp_nonce_field( 'ah_banners_save' ); ?>
		<input type="hidden" name="action" value="ah_save_banners">

		<!-- Global settings -->
		<div class="ahb-settings">
			<label for="ahb-autoplay"><strong>Auto-slide speed</strong></label>
			<input type="number" id="ahb-autoplay" name="autoplay_ms" min="1000" max="30000" step="500"
				value="<?php echo esc_attr( $autoplay ); ?>" style="width:120px;"> ms
			<span style="color:#888;">(1000 = 1 second · how long each slide stays)</span>
		</div>

		<!-- Filter bar -->
		<div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;flex-wrap:wrap;">
			<span style="font-size:12px;font-weight:600;color:#6b7280">Show:</span>
			<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ahb-filter-btn active" data-filter="all">All</button>
			<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ahb-filter-btn" data-filter="active">● Active only</button>
			<button type="button" class="ah-btn ah-btn-secondary ah-btn-sm ahb-filter-btn" data-filter="inactive">○ Hidden only</button>
		</div>
		<script>
		document.addEventListener('DOMContentLoaded', function() {
			document.querySelectorAll('.ahb-filter-btn').forEach(function(btn) {
				btn.addEventListener('click', function() {
					var f = this.dataset.filter;
					document.querySelectorAll('.ahb-filter-btn').forEach(function(b) { b.classList.remove('active'); });
					this.classList.add('active');
					document.querySelectorAll('#ahb-rows .ahb-row').forEach(function(row) {
						var sel = row.querySelector('select[name$="[status]"]');
						if (!sel || f === 'all') { row.style.display = ''; return; }
						row.style.display = (sel.value === f) ? '' : 'none';
					});
				});
			});
		});
		</script>

		<!-- Repeater -->
		<div id="ahb-rows">
			<?php foreach ( $banners as $i => $b ) : ?>
				<?php ah_banner_row( $i, (array) $b, $align_opts, $pos_opts ); ?>
			<?php endforeach; ?>
		</div>

		<p>
			<button type="button" class="button button-secondary" id="ahb-add">➕ Add Banner</button>
		</p>

		<p style="margin-top:1.5rem;">
			<?php submit_button( '💾 Save All Banners', 'primary', 'submit', false ); ?>
		</p>
	</form>

	<!-- Hidden template for new rows -->
	<script type="text/html" id="ahb-tpl">
		<?php ah_banner_row( '__i__', array(), $align_opts, $pos_opts ); ?>
	</script>
</div>

<style>
.ahb-wrap { max-width: 960px; }
.ahb-settings { background:#fff; border:1px solid #dcdcde; border-radius:8px; padding:14px 18px; margin:1rem 0 1.5rem; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.ahb-row { background:#fff; border:1px solid #dcdcde; border-radius:8px; margin-bottom:14px; overflow:hidden; }
.ahb-row__head { display:flex; align-items:center; gap:10px; padding:10px 14px; background:#f6f7f7; border-bottom:1px solid #e5e7eb; }
.ahb-row__title { flex:1; font-size:14px; color:#1d2327; }
.ahb-drag { cursor:grab; color:#8c8f94; }
.ahb-status select { font-size:12px; }
.ahb-remove { color:#b32d2e !important; border-color:#dcaeae !important; }
.ahb-remove:hover { background:#b32d2e !important; color:#fff !important; }
.ahb-row__body { padding:16px; display:flex; flex-direction:column; gap:14px; }
.ahb-field { display:flex; flex-direction:column; gap:4px; }
.ahb-field label { font-weight:600; font-size:12px; color:#3c434a; }
.ahb-field label small { font-weight:400; color:#888; }
.ahb-field input, .ahb-field textarea, .ahb-field select { width:100%; }
.ahb-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; }
.ahb-grid:has(> .ahb-field:nth-child(2):last-child) { grid-template-columns:1fr 1fr; }
.ahb-images { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
.ahb-img-wrap { display:flex; gap:14px; align-items:flex-start; }
.ahb-img-preview { width:160px; height:90px; border-radius:6px; background:#eef0e8 center/cover no-repeat; border:1px solid #dcdcde; flex:0 0 auto; }
.ahb-img-preview--mobile { width:64px; height:114px; }
.ahb-img-controls { flex:1; display:flex; flex-direction:column; gap:8px; }
.ahb-row.ahb-sorting { opacity:.6; }
@media (max-width:782px){ .ahb-grid, .ahb-images{ grid-template-columns:1fr; } .ahb-img-wrap{ flex-direction:column; } }
</style>

<script>
( function ( $ ) {
	var counter = <?php echo (int) ( count( $banners ) + 5 ); ?>;

	function refreshTitles() {
		$( '#ahb-rows .ahb-row' ).each( function () {
			var t = $( this ).find( 'input[name$="[title]"]' ).val() || 'New Banner';
			$( this ).find( '.ahb-row__title' ).text( t.replace( /<br\s*\/?>/gi, ' ' ).replace( /<[^>]+>/g, '' ) );
		} );
	}

	// Add row
	$( '#ahb-add' ).on( 'click', function () {
		var html = $( '#ahb-tpl' ).html().replace( /__i__/g, counter++ );
		$( '#ahb-rows' ).append( html );
		refreshTitles();
	} );

	// Remove row
	$( '#ahb-rows' ).on( 'click', '.ahb-remove', function () {
		if ( $( '#ahb-rows .ahb-row' ).length <= 1 ) {
			alert( 'Keep at least one banner. Set it to "Hidden" if you want to disable it.' );
			return;
		}
		$( this ).closest( '.ahb-row' ).remove();
	} );

	// Live title in header
	$( '#ahb-rows' ).on( 'input', 'input[name$="[title]"]', refreshTitles );

	// Image preview on URL change
	$( '#ahb-rows' ).on( 'input', '.ahb-img-url', function () {
		var url = $( this ).val();
		$( this ).closest( '.ahb-img-wrap' ).find( '.ahb-img-preview' )
			.css( 'background-image', url ? "url('" + url + "')" : 'none' );
	} );

	// WP media picker
	var frame = null;
	$( '#ahb-rows' ).on( 'click', '.ahb-pick', function () {
		var $btn = $( this );
		frame = wp.media( { title: 'Select Banner Image', multiple: false, library: { type: 'image' } } );
		frame.on( 'select', function () {
			var att = frame.state().get( 'selection' ).first().toJSON();
			var $wrap = $btn.closest( '.ahb-img-wrap' );
			$wrap.find( '.ahb-img-url' ).val( att.url );
			$wrap.find( '.ahb-img-preview' ).css( 'background-image', "url('" + att.url + "')" );
		} );
		frame.open();
	} );

	// Drag-reorder
	if ( $.fn.sortable ) {
		$( '#ahb-rows' ).sortable( {
			handle: '.ahb-drag',
			placeholder: 'ahb-row',
			forcePlaceholderSize: true,
			start: function ( e, ui ) { ui.item.addClass( 'ahb-sorting' ); },
			stop:  function ( e, ui ) { ui.item.removeClass( 'ahb-sorting' ); }
		} );
	}

	refreshTitles();
} )( jQuery );
</script>
