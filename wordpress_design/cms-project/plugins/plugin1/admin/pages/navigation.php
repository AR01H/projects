<?php
defined( 'ABSPATH' ) || exit;

$saved       = isset( $_GET['saved'] );
$nav_items   = AH_Admin_Bootstrap::get_navigation_data();
$nav_cta     = AH_Admin_Bootstrap::get_nav_cta_data();
$footer      = AH_Admin_Bootstrap::get_footer_data();
$suggestions = AH_Admin_Bootstrap::get_nav_link_suggestions();

if ( empty( $footer['columns'] ) ) {
	$footer['columns'] = array();
}
if ( empty( $footer['legal_links'] ) ) {
	$footer['legal_links'] = array();
}
?>
<div class="wrap ah-admin-wrap ah-nav-builder-wrap">
	<div class="ah-admin-header">
		<div class="ah-admin-logo">N</div>
		<div>
			<h1>Navigation and Footer Manager</h1>
			<p>Reusable header and footer content now lives in CMS ADMIN, so the same data can be shared across themes.</p>
		</div>
	</div>

	<?php if ( $saved ) : ?>
		<div class="ah-admin-notice ah-admin-notice--success">Navigation and footer settings saved.</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'ah_cms_navigation' ); ?>
		<input type="hidden" name="action" value="ah_cms_nav">

		<div class="ah-admin-box">
			<h2>Main Navigation</h2>
			<p class="ah-builder-note">Manage your reusable header menu here. Themes can render this data with their own markup.</p>
			<div class="ah-builder-actions">
				<button type="button" class="button button-secondary" id="ah-expand-all">Expand All</button>
				<button type="button" class="button button-secondary" id="ah-collapse-all">Collapse All</button>
			</div>

			<div id="ah-nav-items" class="ah-builder-stack">
				<?php foreach ( $nav_items as $nav_index => $item ) : ?>
					<div class="ah-builder-item is-open" data-kind="nav-item">
						<div class="ah-builder-item__bar">
							<span class="ah-builder-handle" title="Drag to reorder">::</span>
							<strong><?php echo esc_html( $item['label'] ?: 'Menu Item' ); ?></strong>
							<button type="button" class="ah-toggle-item" aria-expanded="true" aria-label="Toggle menu item">▾</button>
							<button type="button" class="button-link-delete ah-remove-item">Remove</button>
						</div>
						<div class="ah-builder-item__body">
						<div class="ah-builder-grid ah-builder-grid--nav">
							<input type="hidden" name="nav_items[<?php echo esc_attr( $nav_index ); ?>][id]" value="<?php echo esc_attr( $item['id'] ?? '' ); ?>">
							<label>
								<span>Label</span>
								<input type="text" name="nav_items[<?php echo esc_attr( $nav_index ); ?>][label]" value="<?php echo esc_attr( $item['label'] ?? '' ); ?>" class="regular-text ah-nav-title-input">
							</label>
							<label>
								<span>Type</span>
								<select name="nav_items[<?php echo esc_attr( $nav_index ); ?>][type]" class="ah-nav-type-select">
									<option value="link" <?php selected( $item['type'] ?? 'link', 'link' ); ?>>Direct Link</option>
									<option value="dropdown" <?php selected( $item['type'] ?? '', 'dropdown' ); ?>>Dropdown</option>
								</select>
								<small class="ah-field-help">Choose <strong>Dropdown</strong> to enable submenu links for items like About.</small>
							</label>
							<label class="ah-link-only">
								<span>URL</span>
								<input type="text" name="nav_items[<?php echo esc_attr( $nav_index ); ?>][url]" value="<?php echo esc_attr( $item['url'] ?? '' ); ?>" class="regular-text">
							</label>
							<label>
								<span>Icon / Note</span>
								<input type="text" name="nav_items[<?php echo esc_attr( $nav_index ); ?>][icon]" value="<?php echo esc_attr( $item['icon'] ?? '' ); ?>" class="regular-text" placeholder="Optional">
							</label>
							<label>
								<span>Short Description</span>
								<input type="text" name="nav_items[<?php echo esc_attr( $nav_index ); ?>][description]" value="<?php echo esc_attr( $item['description'] ?? '' ); ?>" class="regular-text" placeholder="Optional helper text">
							</label>
							<label class="ah-checkbox-field">
								<input type="checkbox" name="nav_items[<?php echo esc_attr( $nav_index ); ?>][visible]" value="1" <?php checked( ! empty( $item['visible'] ) ); ?>>
								<span>Show this menu item</span>
							</label>
						</div>

						<div class="ah-submenu-wrap" <?php if ( ( $item['type'] ?? 'link' ) !== 'dropdown' ) echo 'style="display:none"'; ?>>
							<div class="ah-submenu-head">
								<h3>Submenu Links</h3>
								<button type="button" class="button ah-add-submenu">+ Add Submenu Link</button>
							</div>
							<div class="ah-builder-stack ah-submenu-list">
								<?php foreach ( (array) ( $item['submenu'] ?? array() ) as $sub_index => $sub_item ) : ?>
									<div class="ah-submenu-item is-open">
										<div class="ah-builder-inline-head">
											<span class="ah-builder-handle">::</span>
											<strong><?php echo esc_html( $sub_item['label'] ?? 'Submenu Link' ); ?></strong>
											<button type="button" class="ah-toggle-subitem" aria-expanded="true" aria-label="Toggle submenu link">▾</button>
											<button type="button" class="button-link-delete ah-remove-item">Remove</button>
										</div>
										<div class="ah-submenu-item__body">
										<div class="ah-builder-grid ah-builder-grid--submenu">
											<label class="ah-suggest-wrap">
												<span>Autosuggest</span>
												<input type="text" class="regular-text ah-link-suggest-input" placeholder="Type page, blog, or static page name">
												<div class="ah-suggestions" style="display:none"></div>
											</label>
											<label>
												<span>Label</span>
												<input type="text" name="nav_items[<?php echo esc_attr( $nav_index ); ?>][submenu][<?php echo esc_attr( $sub_index ); ?>][label]" value="<?php echo esc_attr( $sub_item['label'] ?? '' ); ?>" class="regular-text">
											</label>
											<label>
												<span>URL</span>
												<input type="text" name="nav_items[<?php echo esc_attr( $nav_index ); ?>][submenu][<?php echo esc_attr( $sub_index ); ?>][url]" value="<?php echo esc_attr( $sub_item['url'] ?? '' ); ?>" class="regular-text ah-link-url-field">
											</label>
											<label>
												<span>Description</span>
												<input type="text" name="nav_items[<?php echo esc_attr( $nav_index ); ?>][submenu][<?php echo esc_attr( $sub_index ); ?>][description]" value="<?php echo esc_attr( $sub_item['description'] ?? '' ); ?>" class="regular-text">
											</label>
											<label>
												<span>Icon</span>
												<input type="text" name="nav_items[<?php echo esc_attr( $nav_index ); ?>][submenu][<?php echo esc_attr( $sub_index ); ?>][icon]" value="<?php echo esc_attr( $sub_item['icon'] ?? '' ); ?>" class="regular-text">
											</label>
											<label class="ah-checkbox-field">
												<input type="checkbox" name="nav_items[<?php echo esc_attr( $nav_index ); ?>][submenu][<?php echo esc_attr( $sub_index ); ?>][highlight]" value="1" <?php checked( ! empty( $sub_item['highlight'] ) ); ?>>
												<span>Highlight this link</span>
											</label>
										</div>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="ah-builder-grid ah-builder-grid--footer" style="margin-top:16px">
				<label>
					<span>Header CTA Label</span>
					<input type="text" name="nav_cta[label]" value="<?php echo esc_attr( $nav_cta['label'] ?? '' ); ?>" class="regular-text">
				</label>
				<label>
					<span>Header CTA URL</span>
					<input type="text" name="nav_cta[url]" value="<?php echo esc_attr( $nav_cta['url'] ?? '' ); ?>" class="regular-text">
				</label>
			</div>

			<p><button type="button" class="button button-secondary" id="ah-add-nav-item">+ Add Menu Item</button></p>
		</div>

		<div class="ah-admin-box">
			<h2>Footer Settings</h2>
			<p class="ah-builder-note">Store footer copy, CTA, columns, and legal links once for any compatible theme.</p>

			<div class="ah-builder-grid ah-builder-grid--footer">
				<label>
					<span>Brand Description</span>
					<textarea name="footer_brand_description" rows="4" class="large-text"><?php echo esc_textarea( $footer['brand_description'] ?? '' ); ?></textarea>
				</label>
				<label>
					<span>Badge Text</span>
					<input type="text" name="footer_badge_text" value="<?php echo esc_attr( $footer['badge_text'] ?? '' ); ?>" class="regular-text">
				</label>
				<label>
					<span>Phone Note</span>
					<input type="text" name="footer_contact[phone_note]" value="<?php echo esc_attr( $footer['contact']['phone_note'] ?? '' ); ?>" class="regular-text">
				</label>
				<label>
					<span>Email Note</span>
					<input type="text" name="footer_contact[email_note]" value="<?php echo esc_attr( $footer['contact']['email_note'] ?? '' ); ?>" class="regular-text">
				</label>
				<label>
					<span>Address Note</span>
					<input type="text" name="footer_contact[address_note]" value="<?php echo esc_attr( $footer['contact']['address_note'] ?? '' ); ?>" class="regular-text">
				</label>
				<label>
					<span>Footer CTA Label</span>
					<input type="text" name="footer_cta[label]" value="<?php echo esc_attr( $footer['cta']['label'] ?? '' ); ?>" class="regular-text">
				</label>
				<label>
					<span>Footer CTA URL</span>
					<input type="text" name="footer_cta[url]" value="<?php echo esc_attr( $footer['cta']['url'] ?? '' ); ?>" class="regular-text">
				</label>
			</div>
		</div>

		<div class="ah-admin-box">
			<h2>Footer Columns</h2>
			<div id="ah-footer-columns" class="ah-builder-stack">
				<?php foreach ( $footer['columns'] as $column_index => $column ) : ?>
					<div class="ah-builder-item" data-kind="footer-column">
						<div class="ah-builder-item__bar">
							<span class="ah-builder-handle">::</span>
							<strong><?php echo esc_html( $column['title'] ?? 'Footer Column' ); ?></strong>
							<button type="button" class="ah-toggle-item" aria-expanded="false" aria-label="Toggle footer column">▸</button>
							<button type="button" class="button-link-delete ah-remove-item">Remove</button>
						</div>
						<div class="ah-builder-item__body" style="display:none">
						<label>
							<span>Column Title</span>
							<input type="text" name="footer_columns[<?php echo esc_attr( $column_index ); ?>][title]" value="<?php echo esc_attr( $column['title'] ?? '' ); ?>" class="regular-text ah-column-title-input">
						</label>
						<div class="ah-submenu-head">
							<h3>Links</h3>
							<button type="button" class="button ah-add-footer-link">+ Add Footer Link</button>
						</div>
						<div class="ah-builder-stack ah-footer-links">
							<?php foreach ( (array) ( $column['items'] ?? array() ) as $link_index => $link ) : ?>
								<div class="ah-submenu-item">
									<div class="ah-builder-inline-head">
										<span class="ah-builder-handle">::</span>
										<strong><?php echo esc_html( $link['label'] ?? 'Footer Link' ); ?></strong>
										<button type="button" class="button-link-delete ah-remove-item">Remove</button>
									</div>
									<div class="ah-builder-grid ah-builder-grid--submenu">
										<label class="ah-suggest-wrap">
											<span>Autosuggest</span>
											<input type="text" class="regular-text ah-link-suggest-input" placeholder="Type page, blog, or static page name">
											<div class="ah-suggestions" style="display:none"></div>
										</label>
										<label>
											<span>Label</span>
											<input type="text" name="footer_columns[<?php echo esc_attr( $column_index ); ?>][items][<?php echo esc_attr( $link_index ); ?>][label]" value="<?php echo esc_attr( $link['label'] ?? '' ); ?>" class="regular-text">
										</label>
										<label>
											<span>URL</span>
											<input type="text" name="footer_columns[<?php echo esc_attr( $column_index ); ?>][items][<?php echo esc_attr( $link_index ); ?>][url]" value="<?php echo esc_attr( $link['url'] ?? '' ); ?>" class="regular-text ah-link-url-field">
										</label>
										<label class="ah-checkbox-field">
											<input type="checkbox" name="footer_columns[<?php echo esc_attr( $column_index ); ?>][items][<?php echo esc_attr( $link_index ); ?>][highlight]" value="1" <?php checked( ! empty( $link['highlight'] ) ); ?>>
											<span>Highlight this link</span>
										</label>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<p><button type="button" class="button button-secondary" id="ah-add-footer-column">+ Add Footer Column</button></p>
		</div>

		<div class="ah-admin-box">
			<h2>Footer Legal Links</h2>
			<div id="ah-footer-legal-links" class="ah-builder-stack">
				<?php foreach ( $footer['legal_links'] as $legal_index => $legal_link ) : ?>
					<div class="ah-submenu-item">
						<div class="ah-builder-inline-head">
							<span class="ah-builder-handle">::</span>
							<strong><?php echo esc_html( $legal_link['label'] ?? 'Legal Link' ); ?></strong>
							<button type="button" class="button-link-delete ah-remove-item">Remove</button>
						</div>
						<div class="ah-builder-grid ah-builder-grid--submenu">
							<label class="ah-suggest-wrap">
								<span>Autosuggest</span>
								<input type="text" class="regular-text ah-link-suggest-input" placeholder="Type page, blog, or static page name">
								<div class="ah-suggestions" style="display:none"></div>
							</label>
							<label>
								<span>Label</span>
								<input type="text" name="footer_legal_links[<?php echo esc_attr( $legal_index ); ?>][label]" value="<?php echo esc_attr( $legal_link['label'] ?? '' ); ?>" class="regular-text">
							</label>
							<label>
								<span>URL</span>
								<input type="text" name="footer_legal_links[<?php echo esc_attr( $legal_index ); ?>][url]" value="<?php echo esc_attr( $legal_link['url'] ?? '' ); ?>" class="regular-text ah-link-url-field">
							</label>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<p><button type="button" class="button button-secondary" id="ah-add-legal-link">+ Add Legal Link</button></p>
		</div>

		<p class="submit">
			<?php submit_button( 'Save Navigation and Footer', 'primary', 'submit', false ); ?>
		</p>
	</form>
</div>

<style>
.ah-nav-builder-wrap { max-width: 1080px; }
.ah-builder-note { color: #64748b; margin: 0 0 16px; font-size: 13px; }
.ah-builder-actions { display:flex; gap:10px; margin:0 0 16px; }
.ah-builder-stack { display: flex; flex-direction: column; gap: 14px; }
.ah-builder-item, .ah-submenu-item { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; }
.ah-builder-item__bar, .ah-builder-inline-head, .ah-submenu-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
.ah-builder-inline-head strong, .ah-builder-item__bar strong { flex: 1; }
.ah-builder-handle { cursor: grab; color: #94a3b8; font-weight: 700; letter-spacing: 1px; user-select: none; }
.ah-toggle-item { border: 0; background: transparent; color: #64748b; font-size: 18px; line-height: 1; cursor: pointer; padding: 0 4px; }
.ah-toggle-item:hover { color: #0f172a; }
.ah-toggle-subitem { border: 0; background: transparent; color: #64748b; font-size: 16px; line-height: 1; cursor: pointer; padding: 0 4px; }
.ah-toggle-subitem:hover { color: #0f172a; }
.ah-builder-item__body { padding-top: 4px; }
.ah-submenu-item__body { padding-top: 4px; }
.ah-builder-item:not(.is-open) .ah-builder-item__bar { margin-bottom: 0; }
.ah-submenu-item:not(.is-open) .ah-builder-inline-head { margin-bottom: 0; }
.ah-builder-grid { display: grid; gap: 12px; }
.ah-builder-grid label { display: flex; flex-direction: column; gap: 6px; font-weight: 600; color: #0f172a; }
.ah-builder-grid label span { font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
.ah-builder-grid input[type="text"], .ah-builder-grid textarea, .ah-builder-grid select { width: 100%; max-width: 100%; }
.ah-field-help { color:#64748b; font-size:12px; font-weight:500; margin-top:4px; }
.ah-builder-grid--nav { grid-template-columns: repeat(3, minmax(0, 1fr)); }
.ah-builder-grid--submenu, .ah-builder-grid--footer { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.ah-checkbox-field { flex-direction: row !important; align-items: center; gap: 8px !important; padding-top: 24px; }
.ah-checkbox-field span { font-size: 14px !important; text-transform: none !important; letter-spacing: 0 !important; color: #0f172a !important; }
.ah-submenu-wrap { margin-top: 16px; border-top: 1px solid #e2e8f0; padding-top: 16px; }
.ah-suggest-wrap { position: relative; }
.ah-suggestion-item { display: flex; justify-content: space-between; gap: 10px; }
.ah-suggestion-item small { color: #94a3b8; }
.ah-builder-item.is-dragging, .ah-submenu-item.is-dragging { opacity: .75; box-shadow: 0 10px 26px rgba(15, 23, 42, .10); }
@media (max-width: 782px) {
	.ah-builder-grid--nav, .ah-builder-grid--submenu, .ah-builder-grid--footer { grid-template-columns: 1fr; }
}
</style>

<script>
(function() {
	'use strict';

	var suggestions = <?php echo wp_json_encode( $suggestions ); ?>;

	function setSortable(selector) {
		if (!window.jQuery || !jQuery.fn.sortable) {
			return;
		}
		document.querySelectorAll(selector).forEach(function(container) {
			if (container.dataset.sortableReady === '1') {
				return;
			}
			jQuery(container).sortable({
				handle: '.ah-builder-handle',
				items: '> .ah-builder-item, > .ah-submenu-item',
				start: function(event, ui) { ui.item.addClass('is-dragging'); },
				stop: function(event, ui) {
					ui.item.removeClass('is-dragging');
					renumberAll();
				}
			});
			container.dataset.sortableReady = '1';
		});
	}

	function createInput(name, value, className, placeholder) {
		return '<input type="text" name="' + name + '" value="' + (value || '') + '" class="regular-text ' + (className || '') + '" placeholder="' + (placeholder || '') + '">';
	}

	function createSuggestBlock() {
		return '<label class="ah-suggest-wrap"><span>Autosuggest</span><input type="text" class="regular-text ah-link-suggest-input" placeholder="Type page, blog, or static page name"><div class="ah-suggestions" style="display:none"></div></label>';
	}

	function submenuRowHtml(prefix) {
		return '' +
			'<div class="ah-submenu-item is-open">' +
				'<div class="ah-builder-inline-head">' +
					'<span class="ah-builder-handle">::</span>' +
					'<strong>Submenu Link</strong>' +
					'<button type="button" class="ah-toggle-subitem" aria-expanded="true" aria-label="Toggle submenu link">▾</button>' +
					'<button type="button" class="button-link-delete ah-remove-item">Remove</button>' +
				'</div>' +
				'<div class="ah-submenu-item__body">' +
				'<div class="ah-builder-grid ah-builder-grid--submenu">' +
					createSuggestBlock() +
					'<label><span>Label</span>' + createInput(prefix + '[label]', '', '', '') + '</label>' +
					'<label><span>URL</span>' + createInput(prefix + '[url]', '', 'ah-link-url-field', '') + '</label>' +
					'<label><span>Description</span>' + createInput(prefix + '[description]', '', '', '') + '</label>' +
					'<label><span>Icon</span>' + createInput(prefix + '[icon]', '', '', '') + '</label>' +
					'<label class="ah-checkbox-field"><input type="checkbox" name="' + prefix + '[highlight]" value="1"><span>Highlight this link</span></label>' +
				'</div>' +
				'</div>' +
			'</div>';
	}

	function footerLinkRowHtml(prefix) {
		return '' +
			'<div class="ah-submenu-item">' +
				'<div class="ah-builder-inline-head">' +
					'<span class="ah-builder-handle">::</span>' +
					'<strong>Footer Link</strong>' +
					'<button type="button" class="button-link-delete ah-remove-item">Remove</button>' +
				'</div>' +
				'<div class="ah-builder-grid ah-builder-grid--submenu">' +
					createSuggestBlock() +
					'<label><span>Label</span>' + createInput(prefix + '[label]', '', '', '') + '</label>' +
					'<label><span>URL</span>' + createInput(prefix + '[url]', '', 'ah-link-url-field', '') + '</label>' +
					'<label class="ah-checkbox-field"><input type="checkbox" name="' + prefix + '[highlight]" value="1"><span>Highlight this link</span></label>' +
				'</div>' +
			'</div>';
	}

	function legalLinkRowHtml(prefix) {
		return '' +
			'<div class="ah-submenu-item">' +
				'<div class="ah-builder-inline-head">' +
					'<span class="ah-builder-handle">::</span>' +
					'<strong>Legal Link</strong>' +
					'<button type="button" class="button-link-delete ah-remove-item">Remove</button>' +
				'</div>' +
				'<div class="ah-builder-grid ah-builder-grid--submenu">' +
					createSuggestBlock() +
					'<label><span>Label</span>' + createInput(prefix + '[label]', '', '', '') + '</label>' +
					'<label><span>URL</span>' + createInput(prefix + '[url]', '', 'ah-link-url-field', '') + '</label>' +
				'</div>' +
			'</div>';
	}

	function navItemHtml(index) {
		return '' +
			'<div class="ah-builder-item is-open" data-kind="nav-item">' +
				'<div class="ah-builder-item__bar">' +
					'<span class="ah-builder-handle" title="Drag to reorder">::</span>' +
					'<strong>Menu Item</strong>' +
					'<button type="button" class="ah-toggle-item" aria-expanded="true" aria-label="Toggle menu item">▾</button>' +
					'<button type="button" class="button-link-delete ah-remove-item">Remove</button>' +
				'</div>' +
				'<div class="ah-builder-item__body">' +
				'<div class="ah-builder-grid ah-builder-grid--nav">' +
					'<input type="hidden" name="nav_items[' + index + '][id]" value="">' +
					'<label><span>Label</span>' + createInput('nav_items[' + index + '][label]', '', 'ah-nav-title-input', '') + '</label>' +
					'<label><span>Type</span><select name="nav_items[' + index + '][type]" class="ah-nav-type-select"><option value="link">Direct Link</option><option value="dropdown">Dropdown</option></select></label>' +
					'<label class="ah-link-only"><span>URL</span>' + createInput('nav_items[' + index + '][url]', '', '', '') + '</label>' +
					'<label><span>Icon / Note</span>' + createInput('nav_items[' + index + '][icon]', '', '', 'Optional') + '</label>' +
					'<label><span>Short Description</span>' + createInput('nav_items[' + index + '][description]', '', '', 'Optional helper text') + '</label>' +
					'<label class="ah-checkbox-field"><input type="checkbox" name="nav_items[' + index + '][visible]" value="1" checked><span>Show this menu item</span></label>' +
				'</div>' +
				'<div class="ah-submenu-wrap" style="display:none">' +
					'<div class="ah-submenu-head"><h3>Submenu Links</h3><button type="button" class="button ah-add-submenu">+ Add Submenu Link</button></div>' +
					'<div class="ah-builder-stack ah-submenu-list"></div>' +
				'</div>' +
				'</div>' +
			'</div>';
	}

	function footerColumnHtml(index) {
		return '' +
			'<div class="ah-builder-item" data-kind="footer-column">' +
				'<div class="ah-builder-item__bar">' +
					'<span class="ah-builder-handle">::</span>' +
					'<strong>Footer Column</strong>' +
					'<button type="button" class="ah-toggle-item" aria-expanded="false" aria-label="Toggle footer column">▸</button>' +
					'<button type="button" class="button-link-delete ah-remove-item">Remove</button>' +
				'</div>' +
				'<div class="ah-builder-item__body" style="display:none">' +
				'<label><span>Column Title</span>' + createInput('footer_columns[' + index + '][title]', '', 'ah-column-title-input', '') + '</label>' +
				'<div class="ah-submenu-head"><h3>Links</h3><button type="button" class="button ah-add-footer-link">+ Add Footer Link</button></div>' +
				'<div class="ah-builder-stack ah-footer-links"></div>' +
				'</div>' +
			'</div>';
	}

	function setExpanded(card, expanded) {
		var body = card.querySelector('.ah-builder-item__body');
		var toggle = card.querySelector('.ah-toggle-item');
		if (!body || !toggle) {
			return;
		}
		card.classList.toggle('is-open', expanded);
		body.style.display = expanded ? '' : 'none';
		toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
		toggle.textContent = expanded ? '▾' : '▸';
	}

	function setSubExpanded(card, expanded) {
		var body = card.querySelector('.ah-submenu-item__body');
		var toggle = card.querySelector('.ah-toggle-subitem');
		if (!body || !toggle) {
			return;
		}
		card.classList.toggle('is-open', expanded);
		body.style.display = expanded ? '' : 'none';
		toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
		toggle.textContent = expanded ? '▾' : '▸';
	}

	function renumberAll() {
		document.querySelectorAll('#ah-nav-items > .ah-builder-item').forEach(function(item, navIndex) {
			item.querySelectorAll('[name]').forEach(function(field) {
				field.name = field.name.replace(/nav_items\[\d+\]/, 'nav_items[' + navIndex + ']');
			});

			item.querySelectorAll('.ah-submenu-list > .ah-submenu-item').forEach(function(subItem, subIndex) {
				subItem.querySelectorAll('[name]').forEach(function(field) {
					field.name = field.name.replace(/nav_items\[\d+\]\[submenu\]\[\d+\]/, 'nav_items[' + navIndex + '][submenu][' + subIndex + ']');
				});
			});
		});

		document.querySelectorAll('#ah-footer-columns > .ah-builder-item').forEach(function(item, columnIndex) {
			item.querySelectorAll('[name]').forEach(function(field) {
				field.name = field.name.replace(/footer_columns\[\d+\]/, 'footer_columns[' + columnIndex + ']');
			});
			item.querySelectorAll('.ah-footer-links > .ah-submenu-item').forEach(function(linkItem, linkIndex) {
				linkItem.querySelectorAll('[name]').forEach(function(field) {
					field.name = field.name.replace(/footer_columns\[\d+\]\[items\]\[\d+\]/, 'footer_columns[' + columnIndex + '][items][' + linkIndex + ']');
				});
			});
		});

		document.querySelectorAll('#ah-footer-legal-links > .ah-submenu-item').forEach(function(item, legalIndex) {
			item.querySelectorAll('[name]').forEach(function(field) {
				field.name = field.name.replace(/footer_legal_links\[\d+\]/, 'footer_legal_links[' + legalIndex + ']');
			});
		});
	}

	function attachSuggest(root) {
		root.querySelectorAll('.ah-suggest-wrap').forEach(function(wrap) {
			if (wrap.dataset.ready === '1') {
				return;
			}
			wrap.dataset.ready = '1';

			var input = wrap.querySelector('.ah-link-suggest-input');
			var drop = wrap.querySelector('.ah-suggestions');

			if (!input || !drop) {
				return;
			}

			input.addEventListener('input', function() {
				var query = input.value.toLowerCase().trim();
				if (!query) {
					drop.style.display = 'none';
					return;
				}

				var matches = suggestions.filter(function(item) {
					return item.label.toLowerCase().indexOf(query) !== -1 || item.url.toLowerCase().indexOf(query) !== -1;
				}).slice(0, 8);

				if (!matches.length) {
					drop.style.display = 'none';
					return;
				}

				drop.innerHTML = matches.map(function(item) {
					return '<div class="ah-suggestion-item" data-label="' + item.label.replace(/"/g, '&quot;') + '" data-url="' + item.url.replace(/"/g, '&quot;') + '">' +
						'<span>' + item.label + '</span><small>' + item.type + '</small></div>';
				}).join('');
				drop.style.display = 'block';

				drop.querySelectorAll('.ah-suggestion-item').forEach(function(row) {
					row.addEventListener('mousedown', function(event) {
						event.preventDefault();
						var labelField = wrap.parentNode.parentNode.querySelector('input[name*="[label]"]');
						var urlField = wrap.parentNode.parentNode.querySelector('.ah-link-url-field');
						if (labelField) {
							labelField.value = row.dataset.label;
						}
						if (urlField) {
							urlField.value = row.dataset.url;
						}
						input.value = row.dataset.label;
						drop.style.display = 'none';
					});
				});
			});

			document.addEventListener('click', function(event) {
				if (!wrap.contains(event.target)) {
					drop.style.display = 'none';
				}
			});
		});
	}

	function syncTypeCard(card) {
		var select = card.querySelector('.ah-nav-type-select');
		var linkOnly = card.querySelector('.ah-link-only');
		var submenuWrap = card.querySelector('.ah-submenu-wrap');
		if (!select || !linkOnly || !submenuWrap) {
			return;
		}
		if (select.value === 'dropdown') {
			linkOnly.style.display = 'none';
			submenuWrap.style.display = '';
			setExpanded(card, true);
		} else {
			linkOnly.style.display = '';
			submenuWrap.style.display = 'none';
		}
	}

	document.querySelectorAll('.ah-nav-type-select').forEach(function(select) {
		select.addEventListener('change', function() {
			syncTypeCard(select.closest('.ah-builder-item'));
		});
		syncTypeCard(select.closest('.ah-builder-item'));
	});

	document.addEventListener('click', function(event) {
		if (event.target.matches('.ah-remove-item')) {
			event.preventDefault();
			var row = event.target.closest('.ah-builder-item, .ah-submenu-item');
			if (row) {
				row.remove();
				renumberAll();
			}
		}

		if (event.target.matches('.ah-toggle-item')) {
			event.preventDefault();
			var card = event.target.closest('.ah-builder-item');
			if (card) {
				setExpanded(card, !card.classList.contains('is-open'));
			}
		}

		if (event.target.matches('.ah-toggle-subitem')) {
			event.preventDefault();
			var subCard = event.target.closest('.ah-submenu-item');
			if (subCard) {
				setSubExpanded(subCard, !subCard.classList.contains('is-open'));
			}
		}

		if (event.target.matches('#ah-expand-all')) {
			event.preventDefault();
			document.querySelectorAll('#ah-nav-items > .ah-builder-item').forEach(function(card) {
				setExpanded(card, true);
			});
			document.querySelectorAll('#ah-nav-items .ah-submenu-item').forEach(function(card) {
				setSubExpanded(card, true);
			});
		}

		if (event.target.matches('#ah-collapse-all')) {
			event.preventDefault();
			document.querySelectorAll('#ah-nav-items .ah-submenu-item').forEach(function(card) {
				setSubExpanded(card, false);
			});
			document.querySelectorAll('#ah-nav-items > .ah-builder-item').forEach(function(card) {
				setExpanded(card, false);
			});
		}

		if (event.target.matches('#ah-add-nav-item')) {
			event.preventDefault();
			var navItems = document.getElementById('ah-nav-items');
			navItems.insertAdjacentHTML('beforeend', navItemHtml(navItems.children.length));
			var newItem = navItems.lastElementChild;
			syncTypeCard(newItem);
			attachSuggest(newItem);
			setSortable('.ah-builder-stack, .ah-submenu-list, .ah-footer-links');
			renumberAll();
		}

		if (event.target.matches('.ah-add-submenu')) {
			event.preventDefault();
			var navCard = event.target.closest('.ah-builder-item');
			var list = navCard.querySelector('.ah-submenu-list');
			var navIndex = Array.prototype.indexOf.call(document.querySelectorAll('#ah-nav-items > .ah-builder-item'), navCard);
			var nextIndex = list.children.length;
			list.insertAdjacentHTML('beforeend', submenuRowHtml('nav_items[' + navIndex + '][submenu][' + nextIndex + ']'));
			attachSuggest(list.lastElementChild);
			setSortable('.ah-builder-stack, .ah-submenu-list, .ah-footer-links');
			renumberAll();
		}

		if (event.target.matches('#ah-add-footer-column')) {
			event.preventDefault();
			var columns = document.getElementById('ah-footer-columns');
			columns.insertAdjacentHTML('beforeend', footerColumnHtml(columns.children.length));
			setSortable('.ah-builder-stack, .ah-submenu-list, .ah-footer-links');
			renumberAll();
		}

		if (event.target.matches('.ah-add-footer-link')) {
			event.preventDefault();
			var column = event.target.closest('.ah-builder-item');
			var list = column.querySelector('.ah-footer-links');
			var columnIndex = Array.prototype.indexOf.call(document.querySelectorAll('#ah-footer-columns > .ah-builder-item'), column);
			var nextIndex = list.children.length;
			list.insertAdjacentHTML('beforeend', footerLinkRowHtml('footer_columns[' + columnIndex + '][items][' + nextIndex + ']'));
			attachSuggest(list.lastElementChild);
			setSortable('.ah-builder-stack, .ah-submenu-list, .ah-footer-links');
			renumberAll();
		}

		if (event.target.matches('#ah-add-legal-link')) {
			event.preventDefault();
			var legal = document.getElementById('ah-footer-legal-links');
			legal.insertAdjacentHTML('beforeend', legalLinkRowHtml('footer_legal_links[' + legal.children.length + ']'));
			attachSuggest(legal.lastElementChild);
			setSortable('.ah-builder-stack, .ah-submenu-list, .ah-footer-links');
			renumberAll();
		}
	});

	attachSuggest(document);
	setSortable('.ah-builder-stack, .ah-submenu-list, .ah-footer-links');
	document.querySelectorAll('.ah-builder-item').forEach(function(card) {
		setExpanded(card, card.classList.contains('is-open'));
	});
	document.querySelectorAll('.ah-submenu-item').forEach(function(card) {
		setSubExpanded(card, card.classList.contains('is-open'));
	});
	renumberAll();
})();
</script>
