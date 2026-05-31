<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

$nav_items = ch_get_theme_navigation();
$nav_cta   = ch_get_nav_cta();
$footer    = ch_get_theme_footer();
$settings  = ch_get_settings();

// Fallback footer columns
$footer_cols = (array) ( $footer['columns'] ?? [] );
if ( empty( $footer_cols ) ) {
	$footer_cols = [
		[ 'title' => 'Our Juice',  'items' => [ ['label'=>'Build Your Juice','url'=>home_url('/#build')], ['label'=>'Health Benefits','url'=>home_url('/#benefits')] ] ],
		[ 'title' => 'Services',   'items' => [ ['label'=>'Event Hire','url'=>home_url('/#hire')], ['label'=>'Franchise','url'=>home_url('/#franchise')], ['label'=>'Hire Us','url'=>home_url('/#contact')] ] ],
	];
}

// Suggestions for the URL picker
$page_suggestions = ch_get_nav_link_suggestions();
?>
<div class="wrap ch-admin-wrap">
<h1>🗺️ Navigation &amp; Footer</h1>

<?php if ( isset( $_GET['saved'] ) ) : ?>
	<div class="ch-notice ch-notice--success">✅ Navigation saved successfully.</div>
<?php endif; ?>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="ch-nav-form">
	<?php wp_nonce_field( 'ch_theme_nav' ); ?>
	<input type="hidden" name="action" value="ch_theme_nav">

	<!-- ── HEADER NAVIGATION ─────────────────────────────────────────────── -->
	<div class="ch-card">
		<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.2rem;">
			<h2 style="margin:0;">🔗 Header Navigation</h2>
			<button type="button" id="ch-add-nav-item" class="button button-primary">+ Add Item</button>
		</div>
		<p style="color:#666;font-size:.83rem;margin-bottom:1rem;">
			Drag items to reorder. Toggle type to <strong>Dropdown</strong> to add submenu items. Items with <strong>Visible</strong> unchecked are hidden.
		</p>

		<div id="ch-nav-items">
			<?php foreach ( $nav_items as $i => $item ) :
				$item = (array) $item;
				$type = $item['type'] ?? 'link';
				$subs = (array) ( $item['submenu'] ?? [] );
			?>
			<div class="ch-nav-item" data-idx="<?php echo $i; ?>">
				<div class="ch-nav-item__header">
					<span class="ch-nav-drag" title="Drag to reorder">⠿</span>
					<input type="hidden" name="nav_items[<?php echo $i; ?>][id]" value="<?php echo esc_attr( $item['id'] ?? '' ); ?>" class="ch-nav-id">
					<input type="text"   name="nav_items[<?php echo $i; ?>][label]" value="<?php echo esc_attr( $item['label'] ?? '' ); ?>"
						class="ch-nav-label" placeholder="Label" required>
					<select name="nav_items[<?php echo $i; ?>][type]" class="ch-nav-type" style="width:110px;">
						<option value="link"     <?php selected($type,'link'); ?>>Link</option>
						<option value="dropdown" <?php selected($type,'dropdown'); ?>>Dropdown</option>
					</select>
					<input type="text" name="nav_items[<?php echo $i; ?>][url]" value="<?php echo esc_attr( $item['url'] ?? '' ); ?>"
						class="ch-nav-url" placeholder="URL or #anchor" style="<?php echo $type==='dropdown'?'opacity:.4;':'' ?>">
					<label class="ch-nav-vis-wrap" title="Show in nav">
						<input type="checkbox" name="nav_items[<?php echo $i; ?>][visible]" value="1"
							<?php checked( isset( $item['visible'] ) ? (bool)$item['visible'] : true ); ?>>
						<span>Visible</span>
					</label>
					<button type="button" class="ch-nav-toggle-sub button" style="<?php echo $type==='dropdown'?'':'display:none'; ?>">
						▸ <?php echo count($subs); ?> submenu
					</button>
					<button type="button" class="ch-nav-remove" title="Remove">✕</button>
				</div>

				<!-- Submenu items -->
				<div class="ch-nav-sublist" style="<?php echo $type==='dropdown'?'':'display:none'; ?>">
					<div class="ch-sub-items">
						<?php foreach ( $subs as $j => $sub ) :
							$sub = (array) $sub;
						?>
						<div class="ch-sub-item">
							<input type="text" name="nav_items[<?php echo $i; ?>][submenu][<?php echo $j; ?>][icon]"
								value="<?php echo esc_attr( $sub['icon'] ?? '' ); ?>" placeholder="🌿" style="width:48px;" title="Icon">
							<input type="text" name="nav_items[<?php echo $i; ?>][submenu][<?php echo $j; ?>][label]"
								value="<?php echo esc_attr( $sub['label'] ?? '' ); ?>" placeholder="Label" style="width:140px;" required>
							<input type="text" name="nav_items[<?php echo $i; ?>][submenu][<?php echo $j; ?>][url]"
								value="<?php echo esc_attr( $sub['url'] ?? '' ); ?>" placeholder="URL" style="flex:1;">
							<input type="text" name="nav_items[<?php echo $i; ?>][submenu][<?php echo $j; ?>][description]"
								value="<?php echo esc_attr( $sub['description'] ?? '' ); ?>" placeholder="Description (optional)" style="flex:1;">
							<label title="Highlight (lime colour)">
								<input type="checkbox" name="nav_items[<?php echo $i; ?>][submenu][<?php echo $j; ?>][highlight]" value="1"
									<?php checked( ! empty( $sub['highlight'] ) ); ?>>
								⭐
							</label>
							<button type="button" class="ch-sub-remove" title="Remove">✕</button>
						</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="ch-add-sub button" style="margin-top:.5rem;">+ Add Submenu Item</button>
				</div>
			</div>
			<?php endforeach; ?>
		</div>

		<!-- URL picker datalist -->
		<datalist id="ch-url-suggestions">
			<?php foreach ( $page_suggestions as $s ) : ?>
				<option value="<?php echo esc_attr( $s['url'] ); ?>"><?php echo esc_html( $s['label'] ); ?></option>
			<?php endforeach; ?>
		</datalist>
	</div>

	<!-- ── CTA BUTTON ────────────────────────────────────────────────────── -->
	<div class="ch-card">
		<h2>🟢 CTA Button (top-right)</h2>
		<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
			<div class="ch-row">
				<label>Button Label</label>
				<input type="text" name="nav_cta[label]"
					value="<?php echo esc_attr( $nav_cta['label'] ?? 'Hire Us' ); ?>"
					placeholder="Hire Us">
			</div>
			<div class="ch-row">
				<label>Button URL</label>
				<input type="text" name="nav_cta[url]"
					value="<?php echo esc_attr( $nav_cta['url'] ?? home_url('/#contact') ); ?>"
					placeholder="/#contact" list="ch-url-suggestions">
			</div>
		</div>
	</div>

	<!-- ── FOOTER ────────────────────────────────────────────────────────── -->
	<div class="ch-card">
		<h2>🦶 Footer Settings</h2>

		<div class="ch-row">
			<label>Brand Description</label>
			<textarea name="footer[brand_description]" rows="3" style="width:100%;"><?php
				echo esc_textarea( $footer['brand_description'] ?? 'Fresh sugarcane juice pressed live, served cool.' );
			?></textarea>
		</div>
		<div class="ch-row">
			<label>Badge Text <small style="color:#888;">(optional)</small></label>
			<input type="text" name="footer[badge_text]"
				value="<?php echo esc_attr( $footer['badge_text'] ?? '' ); ?>"
				placeholder="e.g. Proudly UK Based 🇬🇧">
		</div>
		<div class="ch-row">
			<label>Copyright Suffix</label>
			<input type="text" name="footer[copyright_suffix]"
				value="<?php echo esc_attr( $footer['copyright_suffix'] ?? 'Pressed Fresh. Served Cool.' ); ?>"
				placeholder="Pressed Fresh. Served Cool.">
		</div>
		<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
			<div class="ch-row">
				<label>CTA Button Label</label>
				<input type="text" name="footer[cta][label]"
					value="<?php echo esc_attr( $footer['cta']['label'] ?? 'Send a Message 🌿' ); ?>"
					placeholder="Send a Message 🌿">
			</div>
			<div class="ch-row">
				<label>CTA Button URL</label>
				<input type="text" name="footer[cta][url]"
					value="<?php echo esc_attr( $footer['cta']['url'] ?? home_url('/#contact') ); ?>"
					placeholder="/#contact">
			</div>
		</div>

		<h3 style="margin:1.5rem 0 .8rem;font-size:1rem;">Footer Link Columns</h3>
		<p style="color:#666;font-size:.83rem;margin-bottom:1rem;">Each column has a title and a list of links. Leave title empty to hide a column.</p>

		<div id="ch-footer-cols">
			<?php foreach ( $footer_cols as $ci => $col ) :
				$col = (array) $col;
			?>
			<div class="ch-footer-col-block" data-ci="<?php echo $ci; ?>">
				<div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;">
					<strong style="font-size:.85rem;">Column <?php echo $ci + 1; ?></strong>
					<input type="text" name="footer[columns][<?php echo $ci; ?>][title]"
						value="<?php echo esc_attr( $col['title'] ?? '' ); ?>"
						placeholder="Column Title" style="flex:1;">
					<button type="button" class="ch-footer-col-remove button" style="color:red;">✕ Remove Column</button>
				</div>
				<div class="ch-footer-links-list">
					<?php foreach ( (array) ( $col['items'] ?? [] ) as $li => $link ) :
						$link = (array) $link;
					?>
					<div class="ch-footer-link-row" style="display:flex;gap:.5rem;margin-bottom:.4rem;align-items:center;">
						<input type="text" name="footer[columns][<?php echo $ci; ?>][items][<?php echo $li; ?>][label]"
							value="<?php echo esc_attr( $link['label'] ?? '' ); ?>" placeholder="Label" style="width:180px;">
						<input type="text" name="footer[columns][<?php echo $ci; ?>][items][<?php echo $li; ?>][url]"
							value="<?php echo esc_attr( $link['url'] ?? '' ); ?>" placeholder="URL" style="flex:1;" list="ch-url-suggestions">
						<label title="Highlight (lime)"><input type="checkbox" name="footer[columns][<?php echo $ci; ?>][items][<?php echo $li; ?>][highlight]" value="1" <?php checked( ! empty($link['highlight']) ); ?>> ⭐</label>
						<button type="button" class="ch-footer-link-remove button" style="color:red;">✕</button>
					</div>
					<?php endforeach; ?>
				</div>
				<button type="button" class="ch-add-footer-link button" style="margin-top:.4rem;">+ Add Link</button>
			</div>
			<?php endforeach; ?>
		</div>
		<button type="button" id="ch-add-footer-col" class="button" style="margin-top:.8rem;">+ Add Footer Column</button>
	</div>

	<!-- ── CONTACT INFO ──────────────────────────────────────────────────── -->
	<div class="ch-card">
		<h2>📬 Footer Contact Column</h2>
		<p style="color:#666;font-size:.83rem;margin-bottom:.8rem;">These appear in the "Get In Touch" column. Set phone/email in Site Settings.</p>
		<div class="ch-row">
			<label>Phone Note <small>(below phone number)</small></label>
			<input type="text" name="footer[contact][phone_note]"
				value="<?php echo esc_attr( $footer['contact']['phone_note'] ?? '' ); ?>"
				placeholder="e.g. Mon–Sat 9am–9pm">
		</div>
		<div class="ch-row">
			<label>Email Note</label>
			<input type="text" name="footer[contact][email_note]"
				value="<?php echo esc_attr( $footer['contact']['email_note'] ?? '' ); ?>"
				placeholder="e.g. We reply within 24hrs">
		</div>
	</div>

	<?php submit_button( '💾 Save Navigation & Footer', 'primary', 'submit', false ); ?>
</form>
</div>

<!-- ─────────────────────────────────────────────────────────────────────────
     STYLES & JS
────────────────────────────────────────────────────────────────────────── -->
<style>
.ch-nav-item{background:#f9f9f9;border:1px solid #e0e0e0;border-radius:8px;margin-bottom:.6rem;overflow:hidden;}
.ch-nav-item__header{display:flex;align-items:center;gap:.4rem;padding:.7rem .8rem;flex-wrap:wrap;}
.ch-nav-drag{cursor:grab;font-size:1.2rem;color:#aaa;flex-shrink:0;user-select:none;}
.ch-nav-label{font-weight:600;flex:1;min-width:120px;padding:.35rem .6rem;border:1px solid #ddd;border-radius:4px;}
.ch-nav-type{padding:.35rem .4rem;border:1px solid #ddd;border-radius:4px;}
.ch-nav-url{flex:2;padding:.35rem .6rem;border:1px solid #ddd;border-radius:4px;}
.ch-nav-vis-wrap{font-size:.78rem;color:#555;display:flex;align-items:center;gap:.25rem;white-space:nowrap;}
.ch-nav-remove{background:#fee2e2;border:1px solid #fca5a5;color:#dc2626;border-radius:4px;padding:.2rem .6rem;cursor:pointer;font-size.8rem;flex-shrink:0;}
.ch-nav-remove:hover{background:#dc2626;color:#fff;}
.ch-nav-toggle-sub{font-size:.78rem;}
.ch-nav-sublist{background:#fff;border-top:1px solid #e5e7eb;padding:.8rem 1rem;}
.ch-sub-items{display:flex;flex-direction:column;gap:.4rem;}
.ch-sub-item{display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;background:#f8f8f8;border:1px solid #e5e7eb;border-radius:6px;padding:.5rem .7rem;}
.ch-sub-item input[type=text]{padding:.3rem .5rem;border:1px solid #ddd;border-radius:4px;font-size:.82rem;}
.ch-sub-remove{background:#fee2e2;border:1px solid #fca5a5;color:#dc2626;border-radius:4px;padding:.15rem .5rem;cursor:pointer;flex-shrink:0;}
.ch-sub-remove:hover{background:#dc2626;color:#fff;}
.ch-footer-col-block{background:#f9f9f9;border:1px solid #e0e0e0;border-radius:8px;padding:1rem;margin-bottom:.8rem;}
.ch-footer-col-block:last-child{margin-bottom:0;}
</style>

<script>
(function(){
'use strict';

// ── Helpers ──────────────────────────────────────────────────────────────────
function reindex() {
	document.querySelectorAll('.ch-nav-item').forEach(function(item, i) {
		item.dataset.idx = i;
		item.querySelectorAll('[name]').forEach(function(el) {
			el.name = el.name.replace(/nav_items\[\d+\]/, 'nav_items[' + i + ']');
		});
	});
}

function makeSubItem(idx, j) {
	var d = document.createElement('div');
	d.className = 'ch-sub-item';
	d.innerHTML =
		'<input type="text" name="nav_items[' + idx + '][submenu][' + j + '][icon]" placeholder="🌿" style="width:48px;" title="Icon">' +
		'<input type="text" name="nav_items[' + idx + '][submenu][' + j + '][label]" placeholder="Label" style="width:140px;" required>' +
		'<input type="text" name="nav_items[' + idx + '][submenu][' + j + '][url]" placeholder="URL" style="flex:1;" list="ch-url-suggestions">' +
		'<input type="text" name="nav_items[' + idx + '][submenu][' + j + '][description]" placeholder="Description" style="flex:1;">' +
		'<label title="Highlight"><input type="checkbox" name="nav_items[' + idx + '][submenu][' + j + '][highlight]" value="1"> ⭐</label>' +
		'<button type="button" class="ch-sub-remove" title="Remove">✕</button>';
	d.querySelector('.ch-sub-remove').addEventListener('click', function(){ d.remove(); });
	return d;
}

function makeNavItem(i) {
	var d = document.createElement('div');
	d.className = 'ch-nav-item';
	d.dataset.idx = i;
	d.innerHTML =
		'<div class="ch-nav-item__header">' +
			'<span class="ch-nav-drag" title="Drag to reorder">⠿</span>' +
			'<input type="hidden" name="nav_items[' + i + '][id]" value="">' +
			'<input type="text" name="nav_items[' + i + '][label]" class="ch-nav-label" placeholder="Label" required>' +
			'<select name="nav_items[' + i + '][type]" class="ch-nav-type" style="width:110px;"><option value="link">Link</option><option value="dropdown">Dropdown</option></select>' +
			'<input type="text" name="nav_items[' + i + '][url]" class="ch-nav-url" placeholder="URL or #anchor" list="ch-url-suggestions">' +
			'<label class="ch-nav-vis-wrap"><input type="checkbox" name="nav_items[' + i + '][visible]" value="1" checked> <span>Visible</span></label>' +
			'<button type="button" class="ch-nav-toggle-sub button" style="display:none;">▸ 0 submenu</button>' +
			'<button type="button" class="ch-nav-remove">✕</button>' +
		'</div>' +
		'<div class="ch-nav-sublist" style="display:none;">' +
			'<div class="ch-sub-items"></div>' +
			'<button type="button" class="ch-add-sub button" style="margin-top:.5rem;">+ Add Submenu Item</button>' +
		'</div>';
	attachItemEvents(d);
	return d;
}

function attachItemEvents(item) {
	var typeSelect    = item.querySelector('.ch-nav-type');
	var urlInput      = item.querySelector('.ch-nav-url');
	var toggleSubBtn  = item.querySelector('.ch-nav-toggle-sub');
	var sublist       = item.querySelector('.ch-nav-sublist');
	var subItems      = item.querySelector('.ch-sub-items');
	var addSubBtn     = item.querySelector('.ch-add-sub');
	var removeBtn     = item.querySelector('.ch-nav-remove');
	var labelInput    = item.querySelector('.ch-nav-label');

	// Auto-slug the id field from label
	if (labelInput) {
		labelInput.addEventListener('input', function() {
			var idField = item.querySelector('.ch-nav-id');
			if (idField && !idField.dataset.locked) {
				idField.value = labelInput.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
			}
		});
		var idField = item.querySelector('.ch-nav-id');
		if (idField && idField.value) idField.dataset.locked = '1';
	}

	// Type change
	if (typeSelect) {
		typeSelect.addEventListener('change', function() {
			var isDropdown = typeSelect.value === 'dropdown';
			if (urlInput) urlInput.style.opacity = isDropdown ? '.4' : '1';
			if (toggleSubBtn) toggleSubBtn.style.display = isDropdown ? '' : 'none';
			if (sublist)      sublist.style.display      = isDropdown ? '' : 'none';
		});
	}

	// Toggle submenu panel
	if (toggleSubBtn) {
		toggleSubBtn.addEventListener('click', function() {
			sublist.style.display = sublist.style.display === 'none' ? '' : 'none';
		});
	}

	// Add sub item
	if (addSubBtn) {
		addSubBtn.addEventListener('click', function() {
			var idx = item.dataset.idx;
			var j   = subItems.children.length;
			var sub = makeSubItem(idx, j);
			subItems.appendChild(sub);
			updateSubCount(item);
		});
	}

	// Remove item
	if (removeBtn) {
		removeBtn.addEventListener('click', function() {
			if (confirm('Remove this nav item?')) {
				item.remove();
				reindex();
			}
		});
	}

	// Sub remove (existing)
	item.querySelectorAll('.ch-sub-remove').forEach(function(btn) {
		btn.addEventListener('click', function(){ btn.closest('.ch-sub-item').remove(); updateSubCount(item); });
	});
}

function updateSubCount(item) {
	var btn = item.querySelector('.ch-nav-toggle-sub');
	var n   = item.querySelectorAll('.ch-sub-item').length;
	if (btn) btn.textContent = '▸ ' + n + ' submenu';
}

// ── Init existing items ───────────────────────────────────────────────────────
document.querySelectorAll('.ch-nav-item').forEach(attachItemEvents);

// ── Add new nav item ──────────────────────────────────────────────────────────
document.getElementById('ch-add-nav-item').addEventListener('click', function() {
	var wrap = document.getElementById('ch-nav-items');
	var idx  = wrap.children.length;
	wrap.appendChild(makeNavItem(idx));
	reindex();
});

// ── Footer columns ────────────────────────────────────────────────────────────
var footerColIdx = <?php echo count($footer_cols); ?>;
var footerLinkIdx = {};
<?php foreach ($footer_cols as $ci => $col) :
	$col   = (array) $col;
	$items = (array) ($col['items'] ?? []);
?>
footerLinkIdx[<?php echo $ci; ?>] = <?php echo count($items); ?>;
<?php endforeach; ?>

function makeFooterLinkRow(ci, li) {
	var d = document.createElement('div');
	d.className = 'ch-footer-link-row';
	d.style.cssText = 'display:flex;gap:.5rem;margin-bottom:.4rem;align-items:center;';
	d.innerHTML =
		'<input type="text" name="footer[columns][' + ci + '][items][' + li + '][label]" placeholder="Label" style="width:180px;">' +
		'<input type="text" name="footer[columns][' + ci + '][items][' + li + '][url]" placeholder="URL" style="flex:1;" list="ch-url-suggestions">' +
		'<label title="Highlight"><input type="checkbox" name="footer[columns][' + ci + '][items][' + li + '][highlight]" value="1"> ⭐</label>' +
		'<button type="button" class="ch-footer-link-remove button" style="color:red;">✕</button>';
	d.querySelector('.ch-footer-link-remove').addEventListener('click', function(){ d.remove(); });
	return d;
}

document.querySelectorAll('.ch-add-footer-link').forEach(function(btn) {
	btn.addEventListener('click', function() {
		var block = btn.closest('.ch-footer-col-block');
		var ci    = block.dataset.ci;
		if (!footerLinkIdx[ci]) footerLinkIdx[ci] = 0;
		var li    = footerLinkIdx[ci]++;
		var list  = block.querySelector('.ch-footer-links-list');
		list.appendChild(makeFooterLinkRow(ci, li));
	});
});

document.querySelectorAll('.ch-footer-link-remove').forEach(function(btn) {
	btn.addEventListener('click', function(){ btn.closest('.ch-footer-link-row').remove(); });
});

document.querySelectorAll('.ch-footer-col-remove').forEach(function(btn) {
	btn.addEventListener('click', function() {
		if (confirm('Remove this footer column?')) btn.closest('.ch-footer-col-block').remove();
	});
});

document.getElementById('ch-add-footer-col').addEventListener('click', function() {
	var ci    = footerColIdx++;
	footerLinkIdx[ci] = 0;
	var block = document.createElement('div');
	block.className    = 'ch-footer-col-block';
	block.dataset.ci   = ci;
	block.innerHTML =
		'<div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;">' +
			'<strong style="font-size:.85rem;">New Column</strong>' +
			'<input type="text" name="footer[columns][' + ci + '][title]" placeholder="Column Title" style="flex:1;">' +
			'<button type="button" class="ch-footer-col-remove button" style="color:red;">✕ Remove Column</button>' +
		'</div>' +
		'<div class="ch-footer-links-list"></div>' +
		'<button type="button" class="ch-add-footer-link button" style="margin-top:.4rem;">+ Add Link</button>';
	block.querySelector('.ch-footer-col-remove').addEventListener('click', function() {
		if (confirm('Remove this footer column?')) block.remove();
	});
	block.querySelector('.ch-add-footer-link').addEventListener('click', function() {
		var li = footerLinkIdx[ci]++;
		block.querySelector('.ch-footer-links-list').appendChild(makeFooterLinkRow(ci, li));
	});
	document.getElementById('ch-footer-cols').appendChild(block);
});

})();
</script>
