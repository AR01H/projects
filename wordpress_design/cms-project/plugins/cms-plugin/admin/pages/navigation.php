<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use Ah\Cms\Feature\Navigation\Controller\NavigationAdminController;
use Ah\Cms\Admin\Components\BuilderComponents;

$saved       = isset( $_GET['saved'] );
$nav_items   = NavigationAdminController::get_navigation_data();
$nav_cta     = NavigationAdminController::get_nav_cta_data();
$footer      = NavigationAdminController::get_footer_data();
$suggestions = NavigationAdminController::get_nav_link_suggestions();

if ( empty( $footer['columns'] ) ) {
	$footer['columns'] = array();
}
if ( empty( $footer['legal_links'] ) ) {
	$footer['legal_links'] = array();
}
?>
<div class="wrap ah-wrap">
	<?php \Ah\Cms\Admin\Components\AdminComponents::pageHeader( 'admin-network', 'Navigation Editor', 'Manage header menu items, footer columns, and legal links.' ); ?>

	<?php if ( $saved ) : ?>
		<?php \Ah\Cms\Admin\Components\AdminComponents::notice( 'Navigation and footer settings saved.', 'success' ); ?>
	<?php endif; ?>

	<?php
	// Tab bar
	$active_tab = sanitize_key( $_GET['tab'] ?? 'main-nav' );
	?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'ah_cms_navigation' ); ?>
		<input type="hidden" name="action" value="ah_cms_nav">
		<input type="hidden" name="active_tab" value="<?php echo esc_attr( $active_tab ); ?>">
		<?php
		\Ah\Cms\Admin\Components\AdminComponents::tabBarUrl( array(
			'main-nav'         => 'Main Navigation',
			'footer-settings'  => 'Footer Settings',
			'footer-columns'   => 'Footer Columns',
			'footer-legal'     => 'Footer Legal Links',
		), $active_tab );
		?>

		<?php if ( $active_tab === 'main-nav' ) : ?>
		<div class="ah-card" style="border-top-left-radius:0;">
			<div class="ah-card-header"><h2>Main Navigation</h2></div>
			<p class="ah-builder-note">Manage your reusable header menu here. Themes can render this data with their own markup.</p>
			<?php BuilderComponents::actionBar(); ?>

			<div id="ah-nav-items" class="ah-builder-stack">
				<?php foreach ( $nav_items as $nav_index => $item ) :
					$badge = BuilderComponents::badge( 'Header', 'header', ! empty( $item['visible'] ) );

					ob_start();

					echo '<input type="hidden" name="nav_items[' . esc_attr( $nav_index ) . '][id]" value="' . esc_attr( $item['id'] ?? '' ) . '">';

					$panel_image_url  = esc_attr( $item['panel_image'] ?? '' );
					$has_panel        = ! empty( $item['panel_image'] );
					$panel_src        = $has_panel ? esc_url( $item['panel_image'] ) : '';
					$panel_display    = $has_panel ? 'block' : 'none';
					$panel_image_html = '<div class="ah-media-picker">'
						. '<input type="url" name="nav_items[' . esc_attr( $nav_index ) . '][panel_image]" value="' . $panel_image_url . '" class="regular-text ah-media-url" placeholder="https://… or use Select Image">'
						. '<button type="button" class="button ah-media-select-btn">Select Image</button>'
						. '<img src="' . $panel_src . '" class="ah-media-preview" style="max-width:120px;max-height:70px;display:' . $panel_display . ';margin-top:6px;border-radius:4px;">'
						. '</div>';

					BuilderComponents::grid( array(
						array( 'Label', BuilderComponents::field( 'nav_items[' . $nav_index . '][label]', 'Label', $item['label'] ?? '', 'text', array( 'class' => 'ah-nav-title-input' ) ) ),
						array( 'Type', BuilderComponents::field( 'nav_items[' . $nav_index . '][type]', 'Type', $item['type'] ?? 'link', 'select', array( 'options' => array( 'link' => 'Direct Link', 'dropdown' => 'Dropdown' ) ) ), 'Choose <strong>Dropdown</strong> to enable submenu links for items like About.' ),
						array( 'URL', BuilderComponents::field( 'nav_items[' . $nav_index . '][url]', 'URL', $item['url'] ?? '', 'text' ), '', 'ah-link-only' ),
						array( 'Icon / Note', BuilderComponents::field( 'nav_items[' . $nav_index . '][icon]', 'Icon / Note', $item['icon'] ?? '', 'text', array( 'placeholder' => 'Optional' ) ) ),
						array( 'Short Description', BuilderComponents::field( 'nav_items[' . $nav_index . '][description]', 'Short Description', $item['description'] ?? '', 'text', array( 'placeholder' => 'Optional helper text' ) ) ),
						array( 'CSS Class', BuilderComponents::field( 'nav_items[' . $nav_index . '][css_class]', 'CSS Class', $item['css_class'] ?? '', 'text', array( 'placeholder' => 'e.g. custom-nav-item' ) ) ),
						array( 'Panel Image', $panel_image_html, '', 'ah-dropdown-only ah-media-field' ),
					), 'ah-builder-grid--nav' );

					echo BuilderComponents::field( 'nav_items[' . $nav_index . '][visible]', 'Show this menu item', ! empty( $item['visible'] ), 'checkbox' );

					echo '<div class="ah-submenu-wrap"' . ( ( $item['type'] ?? 'link' ) !== 'dropdown' ? ' style="display:none"' : '' ) . '>';
					BuilderComponents::submenuHead( 'Submenu Links', '+ Add Submenu Link', 'ah-add-submenu' );
					echo '<div class="ah-builder-stack ah-submenu-list">';
					foreach ( (array) ( $item['submenu'] ?? array() ) as $sub_index => $sub_item ) :
						ob_start();
						echo BuilderComponents::autosuggest();
						BuilderComponents::grid( array(
							array( 'Label', BuilderComponents::field( 'nav_items[' . $nav_index . '][submenu][' . $sub_index . '][label]', 'Label', $sub_item['label'] ?? '', 'text' ) ),
							array( 'URL', BuilderComponents::field( 'nav_items[' . $nav_index . '][submenu][' . $sub_index . '][url]', 'URL', $sub_item['url'] ?? '', 'text', array( 'class' => 'ah-link-url-field' ) ) ),
							array( 'Description', BuilderComponents::field( 'nav_items[' . $nav_index . '][submenu][' . $sub_index . '][description]', 'Description', $sub_item['description'] ?? '', 'text' ) ),
							array( 'Icon', BuilderComponents::field( 'nav_items[' . $nav_index . '][submenu][' . $sub_index . '][icon]', 'Icon', $sub_item['icon'] ?? '', 'text' ) ),
							array( 'CSS Class', BuilderComponents::field( 'nav_items[' . $nav_index . '][submenu][' . $sub_index . '][css_class]', 'CSS Class', $sub_item['css_class'] ?? '', 'text', array( 'placeholder' => 'e.g. custom-submenu-link' ) ) ),
						), 'ah-builder-grid--submenu' );
						echo BuilderComponents::field( 'nav_items[' . $nav_index . '][submenu][' . $sub_index . '][highlight]', 'Highlight this link', ! empty( $sub_item['highlight'] ), 'checkbox' );
						$sub_body = ob_get_clean();
						BuilderComponents::inlineItem( $sub_item['label'] ?? 'Submenu Link', $sub_body, '', false );
					endforeach;
					echo '</div></div>';

					$item_body = ob_get_clean();
					BuilderComponents::item( $item['label'] ?: 'Menu Item', $item_body, 'nav-item', false, $badge );
				endforeach; ?>
			</div>

			<div style="margin-top:16px">
				<?php BuilderComponents::grid( array(
					array( 'Header CTA Label', BuilderComponents::field( 'nav_cta[label]', 'Header CTA Label', $nav_cta['label'] ?? '', 'text' ) ),
					array( 'Header CTA URL', BuilderComponents::field( 'nav_cta[url]', 'Header CTA URL', $nav_cta['url'] ?? '', 'text' ) ),
				), 'ah-builder-grid--footer' ); ?>
			</div>

			<?php BuilderComponents::addButton( '+ Add Menu Item', 'ah-add-nav-item' ); ?>
		</div>
		<?php endif; // main-nav tab ?>

		<?php if ( $active_tab === 'footer-settings' ) : ?>
		<div class="ah-card" style="border-top-left-radius:0;">
			<div class="ah-card-header"><h2>Footer Settings</h2></div>
			<p class="ah-builder-note">Store footer copy, CTA, columns, and legal links once for any compatible theme.</p>
			<?php BuilderComponents::grid( array(
				array( 'Brand Description', BuilderComponents::field( 'footer_brand_description', 'Brand Description', $footer['brand_description'] ?? '', 'textarea', array( 'rows' => 4 ) ) ),
				array( 'Badge Text', BuilderComponents::field( 'footer_badge_text', 'Badge Text', $footer['badge_text'] ?? '', 'text' ) ),
				array( 'Footer CTA Label', BuilderComponents::field( 'footer_cta[label]', 'Footer CTA Label', $footer['cta']['label'] ?? '', 'text' ) ),
				array( 'Footer CTA URL', BuilderComponents::field( 'footer_cta[url]', 'Footer CTA URL', $footer['cta']['url'] ?? '', 'text' ) ),
			), 'ah-builder-grid--footer' ); ?>
		</div>
		<?php endif; // footer-settings tab ?>

		<?php if ( $active_tab === 'footer-columns' ) : ?>
		<div class="ah-card" style="border-top-left-radius:0;">
			<div class="ah-card-header"><h2>Footer Columns</h2></div>
			<div id="ah-footer-columns" class="ah-builder-stack">
				<?php foreach ( $footer['columns'] as $column_index => $column ) :
					ob_start();

					echo '<label><span>Column Title</span>';
					echo BuilderComponents::field( 'footer_columns[' . $column_index . '][title]', 'Column Title', $column['title'] ?? '', 'text', array( 'class' => 'ah-column-title-input' ) );
					echo '</label>';

					BuilderComponents::submenuHead( 'Links', '+ Add Footer Link', 'ah-add-footer-link' );

					echo '<div class="ah-builder-stack ah-footer-links">';
					foreach ( (array) ( $column['items'] ?? array() ) as $link_index => $link ) :
						ob_start();
						echo BuilderComponents::autosuggest();
						BuilderComponents::grid( array(
							array( 'Label', BuilderComponents::field( 'footer_columns[' . $column_index . '][items][' . $link_index . '][label]', 'Label', $link['label'] ?? '', 'text' ) ),
							array( 'URL', BuilderComponents::field( 'footer_columns[' . $column_index . '][items][' . $link_index . '][url]', 'URL', $link['url'] ?? '', 'text', array( 'class' => 'ah-link-url-field', 'placeholder' => 'Page slug, mailto:hello@example.com, tel:+44..., or https://...' ) ) ),
						), 'ah-builder-grid--submenu' );
						echo BuilderComponents::field( 'footer_columns[' . $column_index . '][items][' . $link_index . '][highlight]', 'Highlight this link', ! empty( $link['highlight'] ), 'checkbox' );
						$link_body = ob_get_clean();
						$link_badge = BuilderComponents::badge( 'Footer', 'footer', true );
						BuilderComponents::inlineItem( $link['label'] ?? 'Footer Link', $link_body, $link_badge, false );
					endforeach;
					echo '</div>';

					$column_body = ob_get_clean();
					BuilderComponents::item( $column['title'] ?? 'Footer Column', $column_body, 'footer-column', false );
				endforeach; ?>
			</div>

			<?php BuilderComponents::addButton( '+ Add Footer Column', 'ah-add-footer-column' ); ?>
		</div>
		<?php endif; // footer-columns tab ?>

		<?php if ( $active_tab === 'footer-legal' ) : ?>
		<div class="ah-card" style="border-top-left-radius:0;">
			<div class="ah-card-header"><h2>Footer Legal Links</h2></div>
			<div id="ah-footer-legal-links" class="ah-builder-stack">
				<?php foreach ( $footer['legal_links'] as $legal_index => $legal_link ) :
					ob_start();
					echo BuilderComponents::autosuggest();
					BuilderComponents::grid( array(
						array( 'Label', BuilderComponents::field( 'footer_legal_links[' . $legal_index . '][label]', 'Label', $legal_link['label'] ?? '', 'text' ) ),
						array( 'URL', BuilderComponents::field( 'footer_legal_links[' . $legal_index . '][url]', 'URL', $legal_link['url'] ?? '', 'text', array( 'class' => 'ah-link-url-field' ) ) ),
					), 'ah-builder-grid--submenu' );
					$legal_body = ob_get_clean();
					BuilderComponents::inlineItem( $legal_link['label'] ?? 'Legal Link', $legal_body, '', false );
				endforeach; ?>
			</div>

			<?php BuilderComponents::addButton( '+ Add Legal Link', 'ah-add-legal-link' ); ?>
		</div>
		<?php endif; // footer-legal tab ?>

		<p class="submit">
			<?php submit_button( 'Save Navigation and Footer', 'primary', 'submit', false ); ?>
		</p>
	</form>
</div>

<style>
/* Placement badges */
.ah-placement-badge {
	display: inline-flex; align-items: center;
	font-size: 10px; font-weight: 700; letter-spacing: .04em;
	padding: 2px 8px; border-radius: 20px;
	text-transform: uppercase;
	opacity: 0.3;
	border: 1.5px solid currentColor;
	transition: opacity 0.2s;
	user-select: none;
	flex-shrink: 0;
}
.ah-placement-badge.is-active { opacity: 1; }
.ah-badge-header { color: #166534; background: #dcfce7; border-color: #86efac; }
.ah-badge-footer { color: #1e40af; background: #dbeafe; border-color: #93c5fd; }
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
					'<label><span>CSS Class</span>' + createInput(prefix + '[css_class]', '', '', 'e.g. custom-submenu-link') + '</label>' +
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
					'<label><span>CSS Class</span>' + createInput('nav_items[' + index + '][css_class]', '', '', 'e.g. custom-nav-item') + '</label>' +
					'<label class="ah-dropdown-only ah-media-field"><span>Panel Image</span><div class="ah-media-picker"><input type="url" name="nav_items[' + index + '][panel_image]" value="" class="regular-text ah-media-url" placeholder="https://… or use Select Image"><button type="button" class="button ah-media-select-btn">Select Image</button><img class="ah-media-preview" style="max-width:120px;max-height:70px;display:none;margin-top:6px;border-radius:4px;"></div></label>' +
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
		var dropdownOnly = card.querySelector('.ah-dropdown-only');
		var submenuWrap = card.querySelector('.ah-submenu-wrap');
		if (!select || !linkOnly || !submenuWrap) {
			return;
		}
		if (select.value === 'dropdown') {
			linkOnly.style.display = 'none';
			if (dropdownOnly) dropdownOnly.style.display = '';
			submenuWrap.style.display = '';
			setExpanded(card, true);
		} else {
			linkOnly.style.display = '';
			if (dropdownOnly) dropdownOnly.style.display = 'none';
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

	// Live-update Header badge when visible checkbox changes
	document.addEventListener('change', function(event) {
		if (!event.target.matches('input[name*="[visible]"]')) { return; }
		var card = event.target.closest('.ah-builder-item');
		if (!card) { return; }
		var badge = card.querySelector('.ah-badge-header');
		if (badge) { badge.classList.toggle('is-active', event.target.checked); }
	});

	attachSuggest(document);
	document.querySelectorAll('.ah-builder-item').forEach(function(card) {
		setExpanded(card, card.classList.contains('is-open'));
	});
	document.querySelectorAll('.ah-submenu-item').forEach(function(card) {
		setSubExpanded(card, card.classList.contains('is-open'));
	});
	renumberAll();

	// Defer sortable init until jQuery + jQuery UI are loaded (footer scripts)
	function initSortable() {
		if (window.jQuery && jQuery.fn.sortable) {
			setSortable('.ah-builder-stack, .ah-submenu-list, .ah-footer-links');
		} else {
			setTimeout(initSortable, 100);
		}
	}
	initSortable();

	// Media picker: open WP media library on "Select Image" click
	document.addEventListener('click', function(e) {
		if (!e.target.matches('.ah-media-select-btn')) { return; }
		e.preventDefault();
		var picker = e.target.closest('.ah-media-picker');
		var urlInput = picker ? picker.querySelector('.ah-media-url') : null;
		var preview  = picker ? picker.querySelector('.ah-media-preview') : null;
		if (!urlInput) { return; }
		var frame = wp.media({
			title: 'Select Panel Image',
			button: { text: 'Use this image' },
			multiple: false,
			library: { type: 'image' }
		});
		frame.on('select', function() {
			var attachment = frame.state().get('selection').first().toJSON();
			urlInput.value = attachment.url;
			if (preview) {
				preview.src = attachment.url;
				preview.style.display = 'block';
			}
		});
		frame.open();
	});

	// Show/hide preview as URL is typed manually
	document.addEventListener('input', function(e) {
		if (!e.target.matches('.ah-media-url')) { return; }
		var picker = e.target.closest('.ah-media-picker');
		var preview = picker ? picker.querySelector('.ah-media-preview') : null;
		if (!preview) { return; }
		if (e.target.value) {
			preview.src = e.target.value;
			preview.style.display = 'block';
		} else {
			preview.style.display = 'none';
		}
	});
})();
</script>
