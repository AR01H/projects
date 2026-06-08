<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

// ── Active tab & saved flag ───────────────────────────────────────────────────
$active_tab = sanitize_key( $_GET['tab'] ?? 'business' );
$saved      = isset( $_GET['saved'] ) ? (int) $_GET['saved'] : 0;

// ── Load all data (available in each required tab file via shared scope) ──────
$s                 = ch_get_settings();
$certifications    = ch_get_certifications();
$enquiry_types     = ch_get_enquiry_types()    ?? [];
$occasions         = ch_get_occasions()        ?? [];
$events_why        = ch_get_events_why()       ?? [];
$about_mvv         = ch_get_about_mvv()        ?? [];
$about_quality     = ch_get_about_quality()    ?? [];
$events_gallery    = ch_get_events_gallery();
$franchise_gallery = ch_get_franchise_gallery();
$about_gallery     = ch_get_about_gallery();
$equipment_gallery = ch_get_equipment_gallery();
$flavours          = ch_get_flavours()         ?? [];

// ── Tab registry - add a new entry here to get a new tab ─────────────────────
$tabs = [
	'business'  => '📋 Business Details',
	'contact'   => '📬 Contact Form',
	'booking'   => '📅 Booking Wizard',
	'galleries' => '🖼️ Gallery Images',
	'eventswhy' => '🎯 Events Why',
	'about'     => '🏢 About Page',
	'certs'     => '✅ Certifications',
	'flavours'  => '🍋 Flavours',
];

// ── Safe tab file map ─────────────────────────────────────────────────────────
$tab_files = [
	'business'  => __DIR__ . '/content-settings/tab-business.php',
	'contact'   => __DIR__ . '/content-settings/tab-contact.php',
	'booking'   => __DIR__ . '/content-settings/tab-booking.php',
	'galleries' => __DIR__ . '/content-settings/tab-galleries.php',
	'eventswhy' => __DIR__ . '/content-settings/tab-eventswhy.php',
	'about'     => __DIR__ . '/content-settings/tab-about.php',
	'certs'     => __DIR__ . '/content-settings/tab-certs.php',
	'flavours'     => __DIR__ . '/content-settings/tab-flavours.php',
];
?>
<div class="wrap ch-admin-wrap ch-cs-wrap">
	<h1>🎛️ Content Settings</h1>
	<p style="color:#666;margin-bottom:1.5rem;">Manage all previously hardcoded content from one place. Each tab saves independently.</p>

	<?php if ( $saved ) : ?>
		<div class="ch-notice ch-notice--success">✅ Settings saved successfully.</div>
	<?php endif; ?>

	<!-- ── Tab Nav ────────────────────────────────────────────────────────── -->
	<nav class="ch-cs-tabs">
		<?php foreach ( $tabs as $key => $label ) : ?>
			<a href="<?php echo esc_url( add_query_arg( [ 'page' => 'ch-content-settings', 'tab' => $key ], admin_url( 'admin.php' ) ) ); ?>"
				class="ch-cs-tab <?php echo $active_tab === $key ? 'active' : ''; ?>">
				<?php echo esc_html( $label ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<!-- ── Tab Content ───────────────────────────────────────────────────── -->
	<?php
	$tab_file = $tab_files[ $active_tab ] ?? '';
	if ( $tab_file && is_file( realpath( $tab_file ) ) ) {
		require realpath( $tab_file );
	} else {
		echo '<div class="ch-notice ch-notice--warning">Tab not found.</div>';
	}
	?>
</div>

<style>
/* ── Tab nav ─────────────────────────────────────────────────────────────────── */
.ch-cs-tabs { display:flex; gap:.3rem; margin-bottom:1.5rem; border-bottom:2px solid #e0e0e0; padding-bottom:0; flex-wrap:wrap; }
.ch-cs-tab { display:inline-block; padding:.55rem 1.1rem; border-radius:6px 6px 0 0; text-decoration:none; color:#555; font-size:.85rem; font-weight:600; border:1px solid transparent; border-bottom:none; margin-bottom:-2px; transition:all .15s; }
.ch-cs-tab:hover { background:#f0f0f0; color:#222; }
.ch-cs-tab.active { background:#fff; border-color:#e0e0e0; color:#2d5a1b; border-bottom-color:#fff; }

/* ── Cards & rows ───────────────────────────────────────────────────────────── */
.ch-cs-desc { color:#666; margin-bottom:1.2rem; font-size:.88rem; }
.ch-cs-hint { color:#888; font-size:.78rem; display:block; }
.ch-row { display:flex; gap:.8rem; align-items:center; margin-bottom:.9rem; flex-wrap:wrap; }
.ch-row label { min-width:160px; font-weight:600; font-size:.85rem; }
.ch-row input[type="text"], .ch-row input[type="tel"], .ch-row input[type="email"], .ch-row input[type="url"] { flex:1; padding:.45rem .6rem; border:1px solid #ddd; border-radius:4px; min-width:180px; }

/* ── Repeater ───────────────────────────────────────────────────────────────── */
.ch-rep-header { display:grid; grid-template-columns:1fr 1fr 36px; gap:.5rem; padding:.3rem .4rem; font-size:.75rem; font-weight:700; color:#888; text-transform:uppercase; letter-spacing:.04em; margin-bottom:.3rem; }
.ch-rep-header--single { grid-template-columns:1fr 36px; }
.ch-repeater { display:flex; flex-direction:column; gap:.4rem; margin-bottom:.8rem; }
.ch-rep-row { display:grid; grid-template-columns:1fr 1fr 36px; gap:.5rem; align-items:center; background:#f9f9f9; border:1px solid #e8e8e8; border-radius:6px; padding:.5rem .6rem; }
.ch-rep-row--single { grid-template-columns:1fr 36px; }
.ch-rep-row input { padding:.4rem .6rem; border:1px solid #ddd; border-radius:4px; width:100%; box-sizing:border-box; font-size:.88rem; }
.ch-rep-remove { width:32px; height:32px; background:#fff; border:1px solid #ddd; border-radius:4px; cursor:pointer; color:#c00; font-size:.85rem; line-height:1; display:flex; align-items:center; justify-content:center; transition:all .15s; padding:0; }
.ch-rep-remove:hover { background:#c00; color:#fff; border-color:#c00; }
.ch-rep-add { margin-top:.2rem; }

/* ── Multi-column repeater rows ─────────────────────────────────────────────── */
.ch-rep-row--gallery    { grid-template-columns: 2fr 1fr 1fr 36px; }
.ch-rep-row--eventswhy  { grid-template-columns: 60px 1fr 2fr 36px; }
.ch-rep-row--mvv        { grid-template-columns: 60px 1fr 2fr 36px; }
.ch-rep-header--certs,
.ch-rep-row--certs      { grid-template-columns: 50px 1fr 2fr 1fr 36px; }
</style>

<script>
(function(){
	/* ── Remove row ─────────────────────────────────────────────────────── */
	document.addEventListener('click', function(e){
		if (!e.target.classList.contains('ch-rep-remove')) return;
		var row = e.target.closest('.ch-rep-row');
		if (!row) return;
		row.remove();
		reindex(e.target.closest('.ch-repeater'));
	});

	/* ── Add row ────────────────────────────────────────────────────────── */
	document.querySelectorAll('.ch-rep-add').forEach(function(btn){
		btn.addEventListener('click', function(){
			var repId    = btn.dataset.target;
			var prefix   = btn.dataset.prefix;
			var isSingle = btn.dataset.single === '1';
			var columns  = btn.dataset.columns ? btn.dataset.columns.split(',') : [];
			var rep      = document.getElementById(repId);
			if (!rep) return;
			var idx = rep.querySelectorAll('.ch-rep-row').length;
			var row = document.createElement('div');
			var placeholders = { src:'https://...', label:'Label', desc:'Caption', icon:'🌿', title:'Title', text:'Description', value:'key', quality:'Point' };
			var rowMod = rep.querySelector('.ch-rep-row') ? rep.querySelector('.ch-rep-row').className.replace('ch-rep-row','').trim() : '';

			if (isSingle) {
				row.className = 'ch-rep-row ch-rep-row--single';
				row.innerHTML = '<input type="text" name="' + prefix + '[' + idx + ']" placeholder="Type here..." />'
					+ '<button type="button" class="ch-rep-remove" title="Remove">✕</button>';
			} else if (columns.length >= 2) {
				row.className = 'ch-rep-row' + (rowMod ? ' ' + rowMod : '');
				var inputs = columns.map(function(col){
					var ph = placeholders[col] || col;
					var st = col === 'icon' ? ' style="text-align:center;font-size:1.3rem;"' : '';
					return '<input type="text" name="' + prefix + '[' + idx + '][' + col + ']" placeholder="' + ph + '"' + st + '>';
				}).join('');
				row.innerHTML = inputs + '<button type="button" class="ch-rep-remove" title="Remove">✕</button>';
			} else {
				row.className = 'ch-rep-row';
				row.innerHTML =
					'<input type="text" name="' + prefix + '[' + idx + '][value]" placeholder="key" />' +
					'<input type="text" name="' + prefix + '[' + idx + '][label]" placeholder="Label" />' +
					'<button type="button" class="ch-rep-remove" title="Remove">✕</button>';
			}
			rep.appendChild(row);
			row.querySelector('input').focus();
		});
	});

	/* ── Reindex names after remove ─────────────────────────────────────── */
	function reindex(rep){
		if (!rep) return;
		rep.querySelectorAll('.ch-rep-row').forEach(function(row, idx){
			row.querySelectorAll('input').forEach(function(inp){
				inp.name = inp.name.replace(/\[\d+\]/, '[' + idx + ']');
			});
		});
	}
})();
</script>
