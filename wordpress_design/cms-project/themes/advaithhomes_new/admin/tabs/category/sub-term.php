<?php
/**
 * admin/tabs/category/sub-term.php - Settings for one category guide page.
 *
 * Inner tabs:  Appearance | Journey | Content | Calculators | Sidebar | CTA
 * Saves per-section to wp_ah_category_settings via AH_Category_Settings.
 */

defined( 'ABSPATH' ) || exit;

$slug = sanitize_key( isset( $_GET['subtab'] ) ? wp_unslash( $_GET['subtab'] ) : '' );
$all  = class_exists( 'AH_Category_Settings' ) ? AH_Category_Settings::get_all( $slug ) : array();

// ── Unpack all sections ────────────────────────────────────────────────────────
$appearance = isset( $all['appearance'] )    && is_array( $all['appearance'] )    ? $all['appearance']    : array();
$journey_d  = isset( $all['journey'] )       && is_array( $all['journey'] )       ? $all['journey']       : array();
$ht_data    = isset( $all['hot_topics'] )    && is_array( $all['hot_topics'] )    ? $all['hot_topics']    : array();
$pp_data    = isset( $all['popular_posts'] ) && is_array( $all['popular_posts'] ) ? $all['popular_posts'] : array();
$calc_d     = isset( $all['calculators'] )   && is_array( $all['calculators'] )   ? $all['calculators']   : array();
$sidebar_d  = isset( $all['sidebar'] )       && is_array( $all['sidebar'] )       ? $all['sidebar']       : array();
$cta_d      = isset( $all['cta_banner'] )    && is_array( $all['cta_banner'] )    ? $all['cta_banner']    : array();

// Appearance.
$thumbnail_id = ! empty( $appearance['thumbnail_id'] ) ? (int) $appearance['thumbnail_id'] : 0;

// Journey.
$journey_heading        = isset( $journey_d['heading'] )        ? $journey_d['heading']        : '';
$journey_steps          = isset( $journey_d['steps'] )          && is_array( $journey_d['steps'] )   ? $journey_d['steps']   : array();
$journey_tip_icon       = isset( $journey_d['tip_icon'] )       ? $journey_d['tip_icon']       : '';
$journey_tip_text       = isset( $journey_d['tip_text'] )       ? $journey_d['tip_text']       : '';
$journey_tip_link_label = isset( $journey_d['tip_link_label'] ) ? $journey_d['tip_link_label'] : '';
$journey_tip_link_url   = isset( $journey_d['tip_link_url'] )   ? $journey_d['tip_link_url']   : '';

// Hot Topics.
$ht_heading   = isset( $ht_data['heading'] )       ? $ht_data['heading']       : '';
$ht_items     = isset( $ht_data['items'] )         && is_array( $ht_data['items'] ) ? $ht_data['items'] : array();
$ht_all_label = isset( $ht_data['view_all_label'] ) ? $ht_data['view_all_label'] : '';
$ht_all_url   = isset( $ht_data['view_all_url'] )   ? $ht_data['view_all_url']   : '';

// Popular Posts.
$pp_heading = isset( $pp_data['heading'] ) ? $pp_data['heading'] : '';
$pp_items   = isset( $pp_data['items'] )   && is_array( $pp_data['items'] ) ? $pp_data['items'] : array();

// Calculators.
$calc_items = isset( $calc_d['items'] ) && is_array( $calc_d['items'] ) ? $calc_d['items'] : array();

// Sidebar.
$sidebar_tools     = isset( $sidebar_d['tools'] )           && is_array( $sidebar_d['tools'] )    ? $sidebar_d['tools']    : array();
$sidebar_cta_label = isset( $sidebar_d['cta_label'] )       ? $sidebar_d['cta_label']       : '';
$sidebar_cta_url   = isset( $sidebar_d['cta_url'] )         ? $sidebar_d['cta_url']         : '';
$expert_heading    = isset( $sidebar_d['expert_heading'] )  ? $sidebar_d['expert_heading']  : '';
$expert_subtitle   = isset( $sidebar_d['expert_subtitle'] ) ? $sidebar_d['expert_subtitle'] : '';
$experts           = isset( $sidebar_d['experts'] )         && is_array( $sidebar_d['experts'] )   ? $sidebar_d['experts']   : array();
$expert_cta_label  = isset( $sidebar_d['expert_cta_label'] ) ? $sidebar_d['expert_cta_label'] : '';
$expert_cta_url    = isset( $sidebar_d['expert_cta_url'] )   ? $sidebar_d['expert_cta_url']   : '';

// CTA.
$cta_icon        = isset( $cta_d['icon'] )        ? $cta_d['icon']        : '';
$cta_title       = isset( $cta_d['title'] )       ? $cta_d['title']       : '';
$cta_description = isset( $cta_d['description'] ) ? $cta_d['description'] : '';
$cta_btn_label   = isset( $cta_d['btn_label'] )   ? $cta_d['btn_label']   : '';
$cta_btn_url     = isset( $cta_d['btn_url'] )     ? $cta_d['btn_url']     : '';

// Featured Topics.
$ft_d       = isset( $all['featured_topics'] ) && is_array( $all['featured_topics'] ) ? $all['featured_topics'] : array();
$ft_heading = isset( $ft_d['heading'] ) ? $ft_d['heading'] : '';
$ft_items   = isset( $ft_d['items'] )   && is_array( $ft_d['items'] ) ? $ft_d['items'] : array();

// External Links.
$el_d       = isset( $all['external_links'] ) && is_array( $all['external_links'] ) ? $all['external_links'] : array();
$el_heading = isset( $el_d['heading'] ) ? $el_d['heading'] : '';
$el_items   = isset( $el_d['items'] )   && is_array( $el_d['items'] ) ? $el_d['items'] : array();

// Calculators – registered + selected.
$calc_heading       = isset( $calc_d['heading'] )       ? $calc_d['heading']       : '';
$calc_selected_keys = isset( $calc_d['selected_keys'] ) && is_array( $calc_d['selected_keys'] ) ? $calc_d['selected_keys'] : array();
$all_calcs          = function_exists( 'adn_calculators' ) ? adn_calculators() : array();

$term_name = ucwords( str_replace( '-', ' ', $slug ) );
?>

<style>
.adn-inner-tabs { margin-top: 12px; }
.adn-inner-tab-nav { border-bottom: 1px solid #c3c4c7; margin-bottom: 20px; padding-bottom: 0; }
.adn-inner-tab-nav a.nav-tab { margin-bottom: -1px; }
.adn-inner-panel { display: none; }
.adn-inner-panel.is-active { display: block; }
.adn-rep-row { display: flex; gap: 8px; align-items: center; margin-bottom: 8px; }
.adn-post-pill { display: flex; gap: 8px; align-items: center; margin-bottom: 8px; background: #f6f7f7; padding: 6px 10px; border-radius: 4px; border: 1px solid #dcdcde; }
.adn-post-pill .pill-title { flex: 1; font-size: 13px; color: #1d2327; }
.adn-search-wrap { position: relative; margin-bottom: 6px; }
.adn-search-results { display: none; position: absolute; z-index: 200; background: #fff; border: 1px solid #c3c4c7; box-shadow: 0 4px 12px rgba(0,0,0,.12); width: 100%; max-height: 220px; overflow-y: auto; border-radius: 0 0 4px 4px; }
.adn-search-results .sr-item { padding: 8px 12px; cursor: pointer; font-size: 13px; border-bottom: 1px solid #f0f0f1; transition: background .1s; }
.adn-search-results .sr-item:last-child { border-bottom: 0; }
.adn-search-results .sr-item:hover { background: #f0f6fc; }
.adn-search-results .sr-empty { padding: 10px 12px; color: #999; font-size: 13px; }
#cat-thumb-preview-wrap { width: 160px; min-height: 108px; background: #f0f0f1; border: 1px solid #c3c4c7; display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: 4px; }
#cat-thumb-preview-wrap img { width: 100%; height: auto; display: block; }
</style>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action"    value="adn_save_category_term">
	<input type="hidden" name="term_slug" value="<?php echo esc_attr( $slug ); ?>">
	<?php wp_nonce_field( 'adn_save_category_term_' . $slug ); ?>

	<div class="adn-inner-tabs">

		<?php /* ── Inner Tab Nav ─────────────────────────────────────── */ ?>
		<div class="adn-inner-tab-nav nav-tab-wrapper" style="border-bottom:1px solid #c3c4c7;">
			<a href="#adn-tab-appearance"   class="nav-tab nav-tab-active" data-panel="adn-tab-appearance">Appearance</a>
			<a href="#adn-tab-journey"      class="nav-tab" data-panel="adn-tab-journey">Journey</a>
			<a href="#adn-tab-content"      class="nav-tab" data-panel="adn-tab-content">Content</a>
			<a href="#adn-tab-calc"         class="nav-tab" data-panel="adn-tab-calc">Calculators</a>
			<a href="#adn-tab-sidebar"      class="nav-tab" data-panel="adn-tab-sidebar">Sidebar</a>
			<a href="#adn-tab-cta"          class="nav-tab" data-panel="adn-tab-cta">CTA Banner</a>
		</div>

		<?php /* ══════════════════════ APPEARANCE ══════════════════════ */ ?>
		<div id="adn-tab-appearance" class="adn-inner-panel is-active">
			<div class="card" style="max-width:none;">
				<h2><?php esc_html_e( 'Category Thumbnail', ADN_TEXT_DOMAIN ); ?></h2>
				<p class="description"><?php esc_html_e( 'Used as the hero background image on this category page. Overrides the parent term\'s image.', ADN_TEXT_DOMAIN ); ?></p>
				<div style="display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap;margin-top:12px;">
					<div id="cat-thumb-preview-wrap">
						<?php if ( $thumbnail_id ) : ?>
							<?php echo wp_get_attachment_image( $thumbnail_id, array( 160, 108 ), false, array( 'id' => 'cat-thumb-img', 'style' => 'width:100%;height:auto;' ) ); ?>
						<?php else : ?>
							<span style="color:#999;font-size:12px;padding:8px;">No image</span>
						<?php endif; ?>
					</div>
					<div>
						<input type="hidden" name="appearance[thumbnail_id]" id="cat-thumb-id" value="<?php echo esc_attr( $thumbnail_id ); ?>">
						<p style="margin-top:0;">
							<button type="button" class="button" id="cat-thumb-select">
								<?php esc_html_e( 'Select / Change Image', ADN_TEXT_DOMAIN ); ?>
							</button>
							&nbsp;
							<button type="button" class="button" id="cat-thumb-remove" <?php echo $thumbnail_id ? '' : 'style="display:none"'; ?>>
								<?php esc_html_e( 'Remove', ADN_TEXT_DOMAIN ); ?>
							</button>
						</p>
						<p class="description"><?php esc_html_e( 'Recommended: 1400×600 px. JPEG / WebP.', ADN_TEXT_DOMAIN ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<?php /* ══════════════════════ JOURNEY ══════════════════════════ */ ?>
		<div id="adn-tab-journey" class="adn-inner-panel">
			<div class="card" style="max-width:none;">
				<h2><?php esc_html_e( 'Journey Steps', ADN_TEXT_DOMAIN ); ?></h2>
				<p class="description"><?php esc_html_e( 'Steps in the "Your Journey" carousel. Leave empty to hide the whole section.', ADN_TEXT_DOMAIN ); ?></p>

				<table class="form-table" role="presentation"><tbody>
					<tr>
						<th><?php esc_html_e( 'Section Heading', ADN_TEXT_DOMAIN ); ?></th>
						<td><input type="text" class="regular-text" name="journey[heading]"
							value="<?php echo esc_attr( $journey_heading ); ?>"
							placeholder="<?php echo esc_attr( 'Your ' . $term_name . ' Journey' ); ?>"></td>
					</tr>
				</tbody></table>

				<p style="margin:16px 0 6px;font-weight:600;"><?php esc_html_e( 'Steps', ADN_TEXT_DOMAIN ); ?></p>
				<div id="journey-steps-wrap">
					<?php foreach ( $journey_steps as $i => $step ) : ?>
						<div class="adn-rep-row">
							<input type="text" name="journey[steps][<?php echo (int) $i; ?>][icon]"
								value="<?php echo esc_attr( isset( $step['icon'] ) ? $step['icon'] : '' ); ?>"
								placeholder="🔍" style="width:52px;text-align:center;">
							<input type="text" name="journey[steps][<?php echo (int) $i; ?>][label]"
								value="<?php echo esc_attr( isset( $step['label'] ) ? $step['label'] : '' ); ?>"
								placeholder="<?php esc_attr_e( 'Step label', ADN_TEXT_DOMAIN ); ?>" style="width:180px;">
							<input type="text" name="journey[steps][<?php echo (int) $i; ?>][desc]"
								value="<?php echo esc_attr( isset( $step['desc'] ) ? $step['desc'] : '' ); ?>"
								placeholder="<?php esc_attr_e( 'Short description', ADN_TEXT_DOMAIN ); ?>" style="flex:1;">
							<button type="button" class="button adn-rep-remove" title="Remove">&#x2715;</button>
						</div>
					<?php endforeach; ?>
				</div>
				<button type="button" class="button adn-rep-add"
					data-wrap="journey-steps-wrap" data-prefix="journey[steps]" data-tpl="journey">
					<?php esc_html_e( '+ Add Step', ADN_TEXT_DOMAIN ); ?>
				</button>

				<h4 style="margin-top:24px;"><?php esc_html_e( 'Tip Banner (optional)', ADN_TEXT_DOMAIN ); ?></h4>
				<table class="form-table" role="presentation"><tbody>
					<tr>
						<th><?php esc_html_e( 'Icon', ADN_TEXT_DOMAIN ); ?></th>
						<td><input type="text" name="journey[tip_icon]" value="<?php echo esc_attr( $journey_tip_icon ); ?>" placeholder="💡" style="width:52px;text-align:center;"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Text', ADN_TEXT_DOMAIN ); ?></th>
						<td><input type="text" class="large-text" name="journey[tip_text]" value="<?php echo esc_attr( $journey_tip_text ); ?>" placeholder="<?php esc_attr_e( 'Buying takes 3–6 months on average.', ADN_TEXT_DOMAIN ); ?>"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Link Label', ADN_TEXT_DOMAIN ); ?></th>
						<td><input type="text" class="regular-text" name="journey[tip_link_label]" value="<?php echo esc_attr( $journey_tip_link_label ); ?>" placeholder="View Full Timeline →"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Link URL', ADN_TEXT_DOMAIN ); ?></th>
						<td><input type="text" class="regular-text" name="journey[tip_link_url]" value="<?php echo esc_attr( $journey_tip_link_url ); ?>" placeholder="/guides/step-by-step/"></td>
					</tr>
				</tbody></table>
			</div>
		</div>

		<?php /* ══════════════════════ CONTENT ═══════════════════════════ */ ?>
		<div id="adn-tab-content" class="adn-inner-panel">

			<?php /* ── Hot Topics ─────────────────────────────────────── */ ?>
			<div class="card" style="max-width:none;margin-bottom:20px;">
				<h2><?php esc_html_e( 'Sidebar Hot Topics', ADN_TEXT_DOMAIN ); ?></h2>
				<p class="description"><?php esc_html_e( 'Popular questions shown in the sidebar. Search and select posts, then customise the icon and label.', ADN_TEXT_DOMAIN ); ?></p>

				<table class="form-table" role="presentation"><tbody>
					<tr>
						<th><?php esc_html_e( 'Section Heading', ADN_TEXT_DOMAIN ); ?></th>
						<td><input type="text" class="regular-text" name="hot_topics[heading]" value="<?php echo esc_attr( $ht_heading ); ?>" placeholder="🔥 Hot Topics"></td>
					</tr>
				</tbody></table>

				<p style="margin:16px 0 6px;font-weight:600;"><?php esc_html_e( 'Selected Topics', ADN_TEXT_DOMAIN ); ?></p>
				<div class="adn-search-wrap" style="max-width:500px;">
					<input type="text" id="ht-search" class="regular-text" placeholder="<?php esc_attr_e( 'Type to search posts…', ADN_TEXT_DOMAIN ); ?>" autocomplete="off" style="width:100%;">
					<div id="ht-search-results" class="adn-search-results"></div>
				</div>
				<div id="hot-topics-selected">
					<?php foreach ( $ht_items as $i => $item ) : ?>
						<div class="adn-post-pill">
							<input type="text" name="hot_topics[items][<?php echo (int) $i; ?>][icon]"
								value="<?php echo esc_attr( isset( $item['icon'] ) ? $item['icon'] : '' ); ?>"
								placeholder="💡" style="width:52px;text-align:center;" title="Icon emoji">
							<input type="text" name="hot_topics[items][<?php echo (int) $i; ?>][label]"
								value="<?php echo esc_attr( isset( $item['label'] ) ? $item['label'] : '' ); ?>"
								style="flex:1;" placeholder="<?php esc_attr_e( 'Topic label', ADN_TEXT_DOMAIN ); ?>">
							<input type="hidden" name="hot_topics[items][<?php echo (int) $i; ?>][url]"
								value="<?php echo esc_attr( isset( $item['url'] ) ? $item['url'] : '' ); ?>">
							<button type="button" class="button adn-pill-remove" title="Remove">&#x2715;</button>
						</div>
					<?php endforeach; ?>
				</div>
				<p class="description" style="margin-top:6px;"><?php esc_html_e( 'Click a search result to add it. Edit icon and label inline. Drag rows to reorder (coming soon).', ADN_TEXT_DOMAIN ); ?></p>

				<table class="form-table" role="presentation" style="margin-top:12px;"><tbody>
					<tr>
						<th><?php esc_html_e( 'View All Label', ADN_TEXT_DOMAIN ); ?></th>
						<td><input type="text" class="regular-text" name="hot_topics[view_all_label]" value="<?php echo esc_attr( $ht_all_label ); ?>" placeholder="View All Hot Topics →"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'View All URL', ADN_TEXT_DOMAIN ); ?></th>
						<td><input type="text" class="regular-text" name="hot_topics[view_all_url]" value="<?php echo esc_attr( $ht_all_url ); ?>" placeholder="/guides/"></td>
					</tr>
				</tbody></table>
			</div>

			<?php /* ── Popular Posts ──────────────────────────────────── */ ?>
			<div class="card" style="max-width:none;margin-bottom:20px;">
				<h2><?php esc_html_e( 'Popular Posts / Guides', ADN_TEXT_DOMAIN ); ?></h2>
				<p class="description"><?php esc_html_e( 'Curated posts shown as guide cards below the CMS articles grid. Title &amp; URL are loaded live from WordPress.', ADN_TEXT_DOMAIN ); ?></p>

				<table class="form-table" role="presentation"><tbody>
					<tr>
						<th><?php esc_html_e( 'Section Heading', ADN_TEXT_DOMAIN ); ?></th>
						<td><input type="text" class="regular-text" name="popular_posts[heading]" value="<?php echo esc_attr( $pp_heading ); ?>" placeholder="Popular Guides"></td>
					</tr>
				</tbody></table>

				<p style="margin:16px 0 6px;font-weight:600;"><?php esc_html_e( 'Selected Posts', ADN_TEXT_DOMAIN ); ?></p>
				<div class="adn-search-wrap" style="max-width:500px;">
					<input type="text" id="pp-search" class="regular-text" placeholder="<?php esc_attr_e( 'Type to search posts…', ADN_TEXT_DOMAIN ); ?>" autocomplete="off" style="width:100%;">
					<div id="pp-search-results" class="adn-search-results"></div>
				</div>
				<div id="popular-posts-selected">
					<?php foreach ( $pp_items as $i => $item ) :
						$_pp_id   = ! empty( $item['post_id'] ) ? (int) $item['post_id'] : 0;
						$_pp_post = $_pp_id ? get_post( $_pp_id ) : null;
						if ( ! $_pp_post ) { continue; }
					?>
						<div class="adn-post-pill">
							<span class="pill-title"><?php echo esc_html( $_pp_post->post_title ); ?></span>
							<input type="hidden" name="popular_posts[items][<?php echo (int) $i; ?>][post_id]" value="<?php echo $_pp_id; ?>">
							<button type="button" class="button adn-pill-remove" title="Remove">&#x2715;</button>
						</div>
					<?php endforeach; ?>
				</div>
				<p class="description" style="margin-top:6px;"><?php esc_html_e( 'Up to 6 posts. Title and URL always pulled fresh from WordPress.', ADN_TEXT_DOMAIN ); ?></p>
			</div>

			<?php /* ── Featured Topics ──────────────────────────────────── */ ?>
			<div class="card" style="max-width:none;margin-bottom:20px;">
				<h2><?php esc_html_e( 'Featured Topics', ADN_TEXT_DOMAIN ); ?></h2>
				<p class="description"><?php esc_html_e( 'CMS taxonomy topics to highlight in the sidebar. Search and click to add; edit icon and name inline.', ADN_TEXT_DOMAIN ); ?></p>

				<table class="form-table" role="presentation"><tbody>
					<tr>
						<th><?php esc_html_e( 'Section Heading', ADN_TEXT_DOMAIN ); ?></th>
						<td><input type="text" class="regular-text" name="featured_topics[heading]" value="<?php echo esc_attr( $ft_heading ); ?>" placeholder="Browse Topics"></td>
					</tr>
				</tbody></table>

				<p style="margin:16px 0 6px;font-weight:600;"><?php esc_html_e( 'Selected Topics', ADN_TEXT_DOMAIN ); ?></p>
				<div class="adn-search-wrap" style="max-width:500px;">
					<input type="text" id="ft-search" class="regular-text" placeholder="<?php esc_attr_e( 'Type to search taxonomy topics…', ADN_TEXT_DOMAIN ); ?>" autocomplete="off" style="width:100%;">
					<div id="ft-search-results" class="adn-search-results"></div>
				</div>
				<div id="featured-topics-selected">
					<?php foreach ( $ft_items as $i => $item ) : ?>
						<div class="adn-post-pill">
							<input type="text" name="featured_topics[items][<?php echo (int) $i; ?>][icon]"
								value="<?php echo esc_attr( isset( $item['icon'] ) ? $item['icon'] : '' ); ?>"
								placeholder="📚" style="width:52px;text-align:center;" title="Icon">
							<input type="text" name="featured_topics[items][<?php echo (int) $i; ?>][name]"
								value="<?php echo esc_attr( isset( $item['name'] ) ? $item['name'] : '' ); ?>"
								style="flex:1;" placeholder="<?php esc_attr_e( 'Topic name', ADN_TEXT_DOMAIN ); ?>">
							<input type="hidden" name="featured_topics[items][<?php echo (int) $i; ?>][url]"
								value="<?php echo esc_attr( isset( $item['url'] ) ? $item['url'] : '' ); ?>">
							<input type="hidden" name="featured_topics[items][<?php echo (int) $i; ?>][term_id]"
								value="<?php echo (int) ( isset( $item['term_id'] ) ? $item['term_id'] : 0 ); ?>">
							<button type="button" class="button adn-pill-remove" title="Remove">&#x2715;</button>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<?php /* ── Related External Links ────────────────────────────── */ ?>
			<div class="card" style="max-width:none;margin-bottom:20px;">
				<h2><?php esc_html_e( 'Related External Links', ADN_TEXT_DOMAIN ); ?></h2>
				<p class="description"><?php esc_html_e( 'External resource links shown in the sidebar. Add icon, title, URL and optional description.', ADN_TEXT_DOMAIN ); ?></p>

				<table class="form-table" role="presentation"><tbody>
					<tr>
						<th><?php esc_html_e( 'Section Heading', ADN_TEXT_DOMAIN ); ?></th>
						<td><input type="text" class="regular-text" name="external_links[heading]" value="<?php echo esc_attr( $el_heading ); ?>" placeholder="Useful Resources"></td>
					</tr>
				</tbody></table>

				<p style="margin:16px 0 6px;font-weight:600;"><?php esc_html_e( 'Links', ADN_TEXT_DOMAIN ); ?></p>
				<div id="ext-links-wrap">
					<?php foreach ( $el_items as $i => $item ) : ?>
						<div class="adn-rep-row" style="flex-wrap:wrap;align-items:flex-start;gap:6px;padding:8px;background:#f6f7f7;border-radius:4px;margin-bottom:8px;">
							<div style="display:flex;gap:6px;align-items:center;width:100%;">
								<input type="text" name="external_links[items][<?php echo (int) $i; ?>][icon]"
									value="<?php echo esc_attr( isset( $item['icon'] ) ? $item['icon'] : '' ); ?>"
									placeholder="🔗" style="width:52px;text-align:center;">
								<input type="text" name="external_links[items][<?php echo (int) $i; ?>][title]"
									value="<?php echo esc_attr( isset( $item['title'] ) ? $item['title'] : '' ); ?>"
									placeholder="<?php esc_attr_e( 'Link title', ADN_TEXT_DOMAIN ); ?>" style="flex:1;">
								<input type="url" name="external_links[items][<?php echo (int) $i; ?>][url]"
									value="<?php echo esc_attr( isset( $item['url'] ) ? $item['url'] : '' ); ?>"
									placeholder="https://" style="width:240px;">
								<button type="button" class="button adn-rep-remove" title="Remove">&#x2715;</button>
							</div>
							<input type="text" name="external_links[items][<?php echo (int) $i; ?>][desc]"
								value="<?php echo esc_attr( isset( $item['desc'] ) ? $item['desc'] : '' ); ?>"
								placeholder="<?php esc_attr_e( 'Brief description (optional)', ADN_TEXT_DOMAIN ); ?>" style="width:100%;margin-top:4px;">
						</div>
					<?php endforeach; ?>
				</div>
				<button type="button" class="button adn-rep-add"
					data-wrap="ext-links-wrap" data-prefix="external_links[items]" data-tpl="extlink">
					<?php esc_html_e( '+ Add Link', ADN_TEXT_DOMAIN ); ?>
				</button>
			</div>

		</div><?php /* end #adn-tab-content */ ?>

		<?php /* ══════════════════════ CALCULATORS ═══════════════════════ */ ?>
		<div id="adn-tab-calc" class="adn-inner-panel">
			<div class="card" style="max-width:none;">
				<h2><?php esc_html_e( 'Calculator Shortcuts', ADN_TEXT_DOMAIN ); ?></h2>
				<p class="description"><?php esc_html_e( 'Select which registered calculators to feature on this category page.', ADN_TEXT_DOMAIN ); ?></p>

				<table class="form-table" role="presentation"><tbody>
					<tr>
						<th><?php esc_html_e( 'Section Heading', ADN_TEXT_DOMAIN ); ?></th>
						<td><input type="text" class="regular-text" name="calc[heading]" value="<?php echo esc_attr( $calc_heading ); ?>" placeholder="Useful Calculators"></td>
					</tr>
				</tbody></table>

				<p style="margin:16px 0 8px;font-weight:600;"><?php esc_html_e( 'Available Calculators', ADN_TEXT_DOMAIN ); ?></p>

				<?php if ( empty( $all_calcs ) ) : ?>
					<p class="description">
						<?php esc_html_e( 'No calculators registered yet.', ADN_TEXT_DOMAIN ); ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=adn-theme-calculators&subtab=list' ) ); ?>">
							<?php esc_html_e( 'Go to Manage Calculator → Calculator List', ADN_TEXT_DOMAIN ); ?>
						</a>
					</p>
				<?php else : ?>
					<?php foreach ( $all_calcs as $key => $calc ) :
						$_c_meta    = function_exists( 'adn_calculator_meta' ) ? adn_calculator_meta( $key ) : array();
						$_c_label   = ! empty( $_c_meta['label'] ) ? $_c_meta['label'] : ( isset( $calc['label'] ) ? $calc['label'] : $key );
						$_c_icon    = isset( $calc['icon'] )  ? $calc['icon']  : '🧮';
						$_c_enabled = ! isset( $_c_meta['enabled'] ) || ! empty( $_c_meta['enabled'] );
						$_checked   = in_array( $key, $calc_selected_keys, true );
					?>
						<label style="display:flex;align-items:center;gap:10px;padding:10px 12px;margin-bottom:6px;background:#f6f7f7;border:1px solid #dcdcde;border-radius:4px;cursor:pointer;<?php echo $_checked ? 'background:#e8f5e9;border-color:#66bb6a;' : ''; ?>">
							<input type="checkbox" name="calc[selected_keys][]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $_checked ); ?>>
							<span style="font-size:22px;line-height:1;"><?php echo esc_html( $_c_icon ); ?></span>
							<span style="flex:1;">
								<strong><?php echo esc_html( $_c_label ); ?></strong>
								<?php if ( ! $_c_enabled ) : ?>
									<em style="color:#999;font-size:12px;"> (<?php esc_html_e( 'disabled in Calculator List', ADN_TEXT_DOMAIN ); ?>)</em>
								<?php endif; ?>
								<br><code style="font-size:11px;color:#666;">[ah_calculator key="<?php echo esc_attr( $key ); ?>"]</code>
							</span>
						</label>
					<?php endforeach; ?>
				<?php endif; ?>

				<div style="margin-top:20px;padding:12px 16px;background:#e8f0fe;border-left:4px solid #4285f4;border-radius:0 4px 4px 0;">
					<p style="margin:0;font-size:13px;">
						<strong><?php esc_html_e( 'Shortcode', ADN_TEXT_DOMAIN ); ?>:</strong>
						<code>[adn_cat_calculators slug="<?php echo esc_attr( $slug ); ?>"]</code>
						- <?php esc_html_e( 'Embeds these calculator shortcuts on any page or post.', ADN_TEXT_DOMAIN ); ?>
					</p>
				</div>
			</div>
		</div>

		<?php /* ══════════════════════ SIDEBAR ════════════════════════════ */ ?>
		<div id="adn-tab-sidebar" class="adn-inner-panel">

			<?php /* ── Quick Tools ──────────────────────────────────────── */ ?>
			<div class="card" style="max-width:none;margin-bottom:20px;">
				<h2><?php esc_html_e( 'Sidebar Quick Tools', ADN_TEXT_DOMAIN ); ?></h2>
				<p class="description"><?php esc_html_e( 'Links in the sidebar dark card. Leave empty to hide it.', ADN_TEXT_DOMAIN ); ?></p>
				<div id="sidebar-tools-wrap">
					<?php foreach ( $sidebar_tools as $i => $tool ) : ?>
						<div class="adn-rep-row">
							<input type="text" name="sidebar[tools][<?php echo (int) $i; ?>][icon]"
								value="<?php echo esc_attr( isset( $tool['icon'] ) ? $tool['icon'] : '' ); ?>"
								placeholder="🧮" style="width:52px;text-align:center;">
							<input type="text" name="sidebar[tools][<?php echo (int) $i; ?>][label]"
								value="<?php echo esc_attr( isset( $tool['label'] ) ? $tool['label'] : '' ); ?>"
								placeholder="<?php esc_attr_e( 'Tool label', ADN_TEXT_DOMAIN ); ?>" style="flex:1;">
							<input type="text" name="sidebar[tools][<?php echo (int) $i; ?>][url]"
								value="<?php echo esc_attr( isset( $tool['url'] ) ? $tool['url'] : '' ); ?>"
								placeholder="/calculators/" style="width:200px;">
							<button type="button" class="button adn-rep-remove" title="Remove">&#x2715;</button>
						</div>
					<?php endforeach; ?>
				</div>
				<button type="button" class="button adn-rep-add"
					data-wrap="sidebar-tools-wrap" data-prefix="sidebar[tools]" data-tpl="link">
					<?php esc_html_e( '+ Add Tool', ADN_TEXT_DOMAIN ); ?>
				</button>
				<table class="form-table" role="presentation" style="margin-top:16px;"><tbody>
					<tr>
						<th><?php esc_html_e( 'CTA Label', ADN_TEXT_DOMAIN ); ?></th>
						<td><input type="text" class="regular-text" name="sidebar[cta_label]" value="<?php echo esc_attr( $sidebar_cta_label ); ?>" placeholder="View All Calculators →"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'CTA URL', ADN_TEXT_DOMAIN ); ?></th>
						<td><input type="text" class="regular-text" name="sidebar[cta_url]" value="<?php echo esc_attr( $sidebar_cta_url ); ?>" placeholder="/calculators/"></td>
					</tr>
				</tbody></table>
			</div>

			<?php /* ── Expert Help ───────────────────────────────────────── */ ?>
			<div class="card" style="max-width:none;margin-bottom:20px;">
				<h2><?php esc_html_e( 'Sidebar Expert Help', ADN_TEXT_DOMAIN ); ?></h2>
				<p class="description"><?php esc_html_e( 'Expert types shown in the sidebar. Leave empty to hide.', ADN_TEXT_DOMAIN ); ?></p>
				<table class="form-table" role="presentation"><tbody>
					<tr>
						<th><?php esc_html_e( 'Heading', ADN_TEXT_DOMAIN ); ?></th>
						<td><input type="text" class="regular-text" name="sidebar[expert_heading]" value="<?php echo esc_attr( $expert_heading ); ?>" placeholder="Need Help From a Professional?"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Subtitle', ADN_TEXT_DOMAIN ); ?></th>
						<td><input type="text" class="regular-text" name="sidebar[expert_subtitle]" value="<?php echo esc_attr( $expert_subtitle ); ?>" placeholder="Speak to trusted property professionals."></td>
					</tr>
				</tbody></table>
				<p style="margin:12px 0 6px;font-weight:600;"><?php esc_html_e( 'Expert Types', ADN_TEXT_DOMAIN ); ?></p>
				<div id="experts-wrap">
					<?php foreach ( $experts as $i => $expert ) : ?>
						<div class="adn-rep-row">
							<input type="text" name="sidebar[experts][<?php echo (int) $i; ?>][icon]"
								value="<?php echo esc_attr( isset( $expert['icon'] ) ? $expert['icon'] : '' ); ?>"
								placeholder="🏦" style="width:52px;text-align:center;">
							<input type="text" name="sidebar[experts][<?php echo (int) $i; ?>][name]"
								value="<?php echo esc_attr( isset( $expert['name'] ) ? $expert['name'] : '' ); ?>"
								placeholder="<?php esc_attr_e( 'Expert name', ADN_TEXT_DOMAIN ); ?>" style="width:160px;">
							<input type="text" name="sidebar[experts][<?php echo (int) $i; ?>][desc]"
								value="<?php echo esc_attr( isset( $expert['desc'] ) ? $expert['desc'] : '' ); ?>"
								placeholder="<?php esc_attr_e( 'Short description', ADN_TEXT_DOMAIN ); ?>" style="flex:1;">
							<input type="text" name="sidebar[experts][<?php echo (int) $i; ?>][url]"
								value="<?php echo esc_attr( isset( $expert['url'] ) ? $expert['url'] : '' ); ?>"
								placeholder="/ask-expert/" style="width:160px;">
							<button type="button" class="button adn-rep-remove" title="Remove">&#x2715;</button>
						</div>
					<?php endforeach; ?>
				</div>
				<button type="button" class="button adn-rep-add"
					data-wrap="experts-wrap" data-prefix="sidebar[experts]" data-tpl="expert">
					<?php esc_html_e( '+ Add Expert', ADN_TEXT_DOMAIN ); ?>
				</button>
				<table class="form-table" role="presentation" style="margin-top:16px;"><tbody>
					<tr>
						<th><?php esc_html_e( 'CTA Label', ADN_TEXT_DOMAIN ); ?></th>
						<td><input type="text" class="regular-text" name="sidebar[expert_cta_label]" value="<?php echo esc_attr( $expert_cta_label ); ?>" placeholder="Find the Right Expert →"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'CTA URL', ADN_TEXT_DOMAIN ); ?></th>
						<td><input type="text" class="regular-text" name="sidebar[expert_cta_url]" value="<?php echo esc_attr( $expert_cta_url ); ?>" placeholder="/ask-expert/"></td>
					</tr>
				</tbody></table>
			</div>

		</div><?php /* end #adn-tab-sidebar */ ?>

		<?php /* ══════════════════════ CTA BANNER ════════════════════════ */ ?>
		<div id="adn-tab-cta" class="adn-inner-panel">
			<div class="card" style="max-width:none;">
				<h2><?php esc_html_e( 'CTA Banner', ADN_TEXT_DOMAIN ); ?></h2>
				<p class="description"><?php esc_html_e( 'Bottom call-to-action banner. Leave Title empty to hide.', ADN_TEXT_DOMAIN ); ?></p>
				<table class="form-table" role="presentation"><tbody>
					<tr>
						<th><?php esc_html_e( 'Icon', ADN_TEXT_DOMAIN ); ?></th>
						<td><input type="text" name="cta[icon]" value="<?php echo esc_attr( $cta_icon ); ?>" placeholder="🏡" style="width:52px;text-align:center;"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Title', ADN_TEXT_DOMAIN ); ?></th>
						<td><input type="text" class="large-text" name="cta[title]" value="<?php echo esc_attr( $cta_title ); ?>"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Description', ADN_TEXT_DOMAIN ); ?></th>
						<td><textarea class="large-text" rows="2" name="cta[description]"><?php echo esc_textarea( $cta_description ); ?></textarea></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Button Label', ADN_TEXT_DOMAIN ); ?></th>
						<td><input type="text" class="regular-text" name="cta[btn_label]" value="<?php echo esc_attr( $cta_btn_label ); ?>" placeholder="Get Personalised Guidance →"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Button URL', ADN_TEXT_DOMAIN ); ?></th>
						<td><input type="text" class="regular-text" name="cta[btn_url]" value="<?php echo esc_attr( $cta_btn_url ); ?>" placeholder="/ask-an-expert/"></td>
					</tr>
				</tbody></table>
			</div>
		</div>

	</div><?php /* end .adn-inner-tabs */ ?>

	<?php submit_button( __( 'Save Settings', ADN_TEXT_DOMAIN ) ); ?>
</form>

<script>
(function () {

	// ── Inner tab switching ─────────────────────────────────────────────────────
	var tabLinks = document.querySelectorAll('.adn-inner-tab-nav a.nav-tab');
	tabLinks.forEach(function (a) {
		a.addEventListener('click', function (e) {
			e.preventDefault();
			tabLinks.forEach(function (t) { t.classList.remove('nav-tab-active'); });
			document.querySelectorAll('.adn-inner-panel').forEach(function (p) { p.classList.remove('is-active'); });
			this.classList.add('nav-tab-active');
			var panel = document.getElementById(this.dataset.panel);
			if (panel) { panel.classList.add('is-active'); }
		});
	});

	// ── Repeatable rows ──────────────────────────────────────────────────────────
	var TPL = {
		journey: function (p, i) {
			return '<input type="text" name="' + p + '[' + i + '][icon]" placeholder="🔍" style="width:52px;text-align:center;">'
				+ '<input type="text" name="' + p + '[' + i + '][label]" placeholder="Step label" style="width:180px;">'
				+ '<input type="text" name="' + p + '[' + i + '][desc]" placeholder="Short description" style="flex:1;">';
		},
		link: function (p, i) {
			return '<input type="text" name="' + p + '[' + i + '][icon]" placeholder="💡" style="width:52px;text-align:center;">'
				+ '<input type="text" name="' + p + '[' + i + '][label]" placeholder="Label" style="flex:1;">'
				+ '<input type="text" name="' + p + '[' + i + '][url]" placeholder="/guides/" style="width:200px;">';
		},
		expert: function (p, i) {
			return '<input type="text" name="' + p + '[' + i + '][icon]" placeholder="🏦" style="width:52px;text-align:center;">'
				+ '<input type="text" name="' + p + '[' + i + '][name]" placeholder="Expert name" style="width:160px;">'
				+ '<input type="text" name="' + p + '[' + i + '][desc]" placeholder="Short description" style="flex:1;">'
				+ '<input type="text" name="' + p + '[' + i + '][url]" placeholder="/ask-expert/" style="width:160px;">';
		},
		extlink: function (p, i) {
			return '<div style="display:flex;gap:6px;align-items:center;width:100%;">'
				+ '<input type="text" name="' + p + '[' + i + '][icon]" placeholder="🔗" style="width:52px;text-align:center;">'
				+ '<input type="text" name="' + p + '[' + i + '][title]" placeholder="Link title" style="flex:1;">'
				+ '<input type="url" name="' + p + '[' + i + '][url]" placeholder="https://" style="width:240px;">'
				+ '<button type="button" class="button adn-rep-remove" title="Remove">&#x2715;</button>'
				+ '</div>'
				+ '<input type="text" name="' + p + '[' + i + '][desc]" placeholder="Brief description (optional)" style="width:100%;margin-top:4px;">';
		}
	};

	function nextRepIndex(wrapId, prefix) {
		var inputs = document.querySelectorAll('#' + wrapId + ' input[name^="' + prefix + '["]');
		var max = -1;
		inputs.forEach(function (el) {
			var m = el.name.match(/\[(\d+)\]/);
			if (m) { max = Math.max(max, parseInt(m[1], 10)); }
		});
		return max + 1;
	}

	document.addEventListener('click', function (e) {
		if (e.target.classList.contains('adn-rep-remove')) {
			e.target.closest('.adn-rep-row').remove();
			return;
		}
		var btn = e.target.closest('.adn-rep-add');
		if (btn) {
			var wrapId = btn.dataset.wrap;
			var prefix = btn.dataset.prefix;
			var tpl    = btn.dataset.tpl;
			var wrap   = document.getElementById(wrapId);
			if (!wrap || !TPL[tpl]) { return; }
			var idx = nextRepIndex(wrapId, prefix);
			var div = document.createElement('div');
			div.className = 'adn-rep-row';
			if (tpl === 'extlink') {
				div.style.cssText = 'flex-wrap:wrap;align-items:flex-start;gap:6px;padding:8px;background:#f6f7f7;border-radius:4px;margin-bottom:8px;';
				div.innerHTML = TPL[tpl](prefix, idx);
			} else {
				div.innerHTML = TPL[tpl](prefix, idx)
					+ '<button type="button" class="button adn-rep-remove" title="Remove">&#x2715;</button>';
			}
			wrap.appendChild(div);
		}
	});

	// ── WordPress Media Uploader (Thumbnail) ────────────────────────────────────
	var mediaFrame = null;
	var thumbId    = document.getElementById('cat-thumb-id');
	var thumbWrap  = document.getElementById('cat-thumb-preview-wrap');
	var thumbRemove = document.getElementById('cat-thumb-remove');

	document.getElementById('cat-thumb-select').addEventListener('click', function () {
		if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
			alert('WordPress media library is not available on this page.');
			return;
		}
		if (mediaFrame) { mediaFrame.open(); return; }
		mediaFrame = wp.media({
			title:    'Select Category Thumbnail',
			button:   { text: 'Use this image' },
			multiple: false,
			library:  { type: 'image' }
		});
		mediaFrame.on('select', function () {
			var att = mediaFrame.state().get('selection').first().toJSON();
			thumbId.value = att.id;
			thumbWrap.innerHTML = '<img src="' + att.url + '" style="width:100%;height:auto;display:block;" alt="">';
			thumbRemove.style.display = '';
		});
		mediaFrame.open();
	});

	if (thumbRemove) {
		thumbRemove.addEventListener('click', function () {
			thumbId.value     = '';
			thumbWrap.innerHTML = '<span style="color:#999;font-size:12px;padding:8px;">No image</span>';
			this.style.display = 'none';
		});
	}

	// ── Generic AJAX search picker ───────────────────────────────────────────────
	var _ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';
	var _nonce   = '<?php echo esc_js( wp_create_nonce( 'adn_cat_search' ) ); ?>';
	var _slug    = '<?php echo esc_js( $slug ); ?>';

	function SearchPicker(opts) {
		var searchInput = document.getElementById(opts.searchId);
		var resultsDiv  = document.getElementById(opts.resultsId);
		var selectedDiv = document.getElementById(opts.selectedId);
		var prefix      = opts.prefix;
		var buildPill   = opts.buildPill;
		var action      = opts.action || 'adn_cat_post_search';
		var _timer;

		if (!searchInput || !resultsDiv || !selectedDiv) { return; }

		function pillCount() {
			return selectedDiv.querySelectorAll('.adn-post-pill').length;
		}
		function renumber() {
			selectedDiv.querySelectorAll('.adn-post-pill').forEach(function (pill, idx) {
				pill.querySelectorAll('[name]').forEach(function (el) {
					el.name = el.name.replace(/\[\d+\]/, '[' + idx + ']');
				});
			});
		}

		searchInput.addEventListener('input', function () {
			clearTimeout(_timer);
			var q = this.value.trim();
			if (q.length < 1) { resultsDiv.style.display = 'none'; return; }
			_timer = setTimeout(function () {
				fetch(_ajaxUrl + '?action=' + encodeURIComponent(action)
					+ '&nonce=' + encodeURIComponent(_nonce)
					+ '&q='     + encodeURIComponent(q)
					+ '&slug='  + encodeURIComponent(_slug))
					.then(function (r) { return r.json(); })
					.then(function (data) {
						if (!data || !data.success || !data.data || !data.data.length) {
							resultsDiv.innerHTML = '<div class="sr-empty">No results found</div>';
						} else {
							resultsDiv.innerHTML = data.data.map(function (p) {
								var icon = p.icon ? p.icon.replace(/"/g,'&quot;') : '';
								return '<div class="sr-item"'
									+ ' data-id="' + p.id + '"'
									+ ' data-title="' + (p.title||'').replace(/&/g,'&amp;').replace(/"/g,'&quot;') + '"'
									+ ' data-url="' + (p.url||'').replace(/"/g,'&quot;') + '"'
									+ ' data-icon="' + icon + '">'
									+ (icon ? '<span style="margin-right:6px;">' + icon + '</span>' : '')
									+ (p.title||p.name||'') + '</div>';
							}).join('');
						}
						resultsDiv.style.display = 'block';
					})
					.catch(function () { resultsDiv.style.display = 'none'; });
			}, 300);
		});

		resultsDiv.addEventListener('click', function (e) {
			var item = e.target.closest('.sr-item');
			if (!item) { return; }
			var pill = document.createElement('div');
			pill.className = 'adn-post-pill';
			pill.innerHTML = buildPill(prefix, pillCount(), item.dataset);
			selectedDiv.appendChild(pill);
			resultsDiv.style.display = 'none';
			searchInput.value = '';
		});

		document.addEventListener('click', function (e) {
			if (!resultsDiv.contains(e.target) && e.target !== searchInput) {
				resultsDiv.style.display = 'none';
			}
		});

		selectedDiv.addEventListener('click', function (e) {
			var rmBtn = e.target.closest('.adn-pill-remove');
			if (rmBtn) {
				rmBtn.closest('.adn-post-pill').remove();
				renumber();
			}
		});
	}

	// Hot Topics picker (WP posts).
	new SearchPicker({
		searchId:   'ht-search',
		resultsId:  'ht-search-results',
		selectedId: 'hot-topics-selected',
		prefix:     'hot_topics[items]',
		buildPill: function (prefix, idx, d) {
			return '<input type="text" name="' + prefix + '[' + idx + '][icon]" placeholder="💡" style="width:52px;text-align:center;" title="Icon">'
				+ '<input type="text" name="' + prefix + '[' + idx + '][label]" value="' + d.title.replace(/"/g,'&quot;') + '" style="flex:1;" placeholder="Label">'
				+ '<input type="hidden" name="' + prefix + '[' + idx + '][url]" value="' + d.url.replace(/"/g,'&quot;') + '">'
				+ '<button type="button" class="button adn-pill-remove" title="Remove">&#x2715;</button>';
		}
	});

	// Popular Posts picker (WP posts).
	new SearchPicker({
		searchId:   'pp-search',
		resultsId:  'pp-search-results',
		selectedId: 'popular-posts-selected',
		prefix:     'popular_posts[items]',
		buildPill: function (prefix, idx, d) {
			return '<span class="pill-title">' + d.title.replace(/</g,'&lt;') + '</span>'
				+ '<input type="hidden" name="' + prefix + '[' + idx + '][post_id]" value="' + d.id + '">'
				+ '<button type="button" class="button adn-pill-remove" title="Remove">&#x2715;</button>';
		}
	});

	// Featured Topics picker (CMS taxonomy terms).
	new SearchPicker({
		searchId:   'ft-search',
		resultsId:  'ft-search-results',
		selectedId: 'featured-topics-selected',
		prefix:     'featured_topics[items]',
		action:     'adn_cat_tax_search',
		buildPill: function (prefix, idx, d) {
			return '<input type="text" name="' + prefix + '[' + idx + '][icon]" value="' + (d.icon||'').replace(/"/g,'&quot;') + '" placeholder="📚" style="width:52px;text-align:center;" title="Icon">'
				+ '<input type="text" name="' + prefix + '[' + idx + '][name]" value="' + d.title.replace(/"/g,'&quot;') + '" style="flex:1;">'
				+ '<input type="hidden" name="' + prefix + '[' + idx + '][url]" value="' + d.url.replace(/"/g,'&quot;') + '">'
				+ '<input type="hidden" name="' + prefix + '[' + idx + '][term_id]" value="' + d.id + '">'
				+ '<button type="button" class="button adn-pill-remove" title="Remove">&#x2715;</button>';
		}
	});

})();
</script>
