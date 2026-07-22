<?php
/**
 * admin/tabs/calculators/sub-new.php - Add or Edit a DB-stored calculator.
 *
 * Add mode  : no query params - shows a blank form.
 * Edit mode : ?edit_key=KEY - loads the existing DB row and pre-fills everything.
 *
 * Shortcode to embed: [ah_calculator key="your-key"]
 */

defined( 'ABSPATH' ) || exit;

$edit_key = isset( $_GET['edit_key'] ) ? sanitize_key( wp_unslash( $_GET['edit_key'] ) ) : '';
$row      = ( $edit_key && class_exists( 'AH_Calculator_DB' ) ) ? AH_Calculator_DB::get( $edit_key ) : null;
$is_edit  = ( null !== $row );

// Defaults (blank for add, row values for edit).
$f_key    = $is_edit ? $row['calc_key']     : '';
$f_title  = $is_edit ? $row['title']        : '';
$f_label  = $is_edit ? $row['label']        : '';
$f_icon   = $is_edit ? $row['icon']         : '';
$f_html   = $is_edit ? $row['html_content'] : '';
$f_js     = $is_edit ? $row['js_content']   : '';
$f_status = $is_edit ? $row['status']       : 'active';

// Meta settings - read from adn_calculators_meta when editing an existing calc.
$f_meta = ( $is_edit && function_exists( 'adn_calculator_meta' ) ) ? adn_calculator_meta( $f_key ) : array();

$_cat_labels = function_exists( 'adn_calculator_categories' ) ? adn_calculator_categories() : array();
$f_saved_cats = isset( $f_meta['categories'] ) && is_array( $f_meta['categories'] ) ? $f_meta['categories'] : array();

// Generate example HTML snippet for the blank-form placeholder.
$example_html = '<div class="ah-calc ah-your-key">

  <div class="ah-calc-form">
    <div class="form-group">
      <label for="inputAmount">Loan Amount</label>
      <div class="input-prefix-wrap">
        <span class="input-prefix">£</span>
        <input type="number" id="inputAmount" value="200000" min="0" step="1000">
      </div>
    </div>
    <!-- add more fields... -->
    <button type="button" class="calc-submit-btn" id="calcBtn">Calculate</button>
  </div>

  <div class="ah-calc-result" id="result" style="display:none;">
    <div class="result-main-amount" id="resultValue">£0</div>
  </div>

</div>';

$example_js = '(function () {
  \'use strict\';

  function el(id) { return document.getElementById(id); }
  function gbp(n) { return \'£\' + Math.round(n).toLocaleString(\'en-GB\'); }

  function calc() {
    var amount = parseFloat(el(\'inputAmount\').value) || 0;
    // ... your calculation here ...
    var result = amount * 0.05;

    el(\'resultValue\').textContent = gbp(result);
    el(\'result\').style.display = \'block\';
  }

  document.addEventListener(\'DOMContentLoaded\', function () {
    var btn = el(\'calcBtn\');
    if (btn) { btn.addEventListener(\'click\', calc); }
    el(\'inputAmount\').addEventListener(\'input\', calc);
    calc();
  });
})();';
?>
<div class="card" style="max-width:none;">
	<h2 style="margin-top:0;">
		<?php echo $is_edit
			? esc_html( sprintf( __( 'Edit Calculator: %s', ADN_TEXT_DOMAIN ), $f_title ) )
			: esc_html__( 'Add New Calculator', ADN_TEXT_DOMAIN ); ?>
	</h2>

	<p class="description" style="margin-bottom:20px;">
		<?php if ( $is_edit ) : ?>
			<?php esc_html_e( 'Update the HTML and JS below. The shortcode for this calculator is:', ADN_TEXT_DOMAIN ); ?>
			<code>[ah_calculator key="<?php echo esc_attr( $f_key ); ?>"]</code>
		<?php else : ?>
			<?php esc_html_e( 'Create a new calculator. Write the HTML form and the JavaScript logic. Once saved, embed it anywhere with:', ADN_TEXT_DOMAIN ); ?>
			<code>[ah_calculator key="your-key"]</code>
		<?php endif; ?>
	</p>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="adn_save_calc_new">
		<?php wp_nonce_field( 'adn_save_calc_new' ); ?>
		<?php if ( $is_edit ) : ?>
			<input type="hidden" name="edit_key" value="<?php echo esc_attr( $f_key ); ?>">
		<?php endif; ?>

		<?php /* ── Identity ──────────────────────────────────────────────── */ ?>
		<div class="card" style="max-width:none;background:#fafafa;margin-bottom:24px;">
			<h3 style="margin-top:0;"><?php esc_html_e( 'Identity', ADN_TEXT_DOMAIN ); ?></h3>
			<table class="form-table" role="presentation"><tbody>

				<?php if ( ! $is_edit ) : ?>
				<tr>
					<th scope="row">
						<label for="calc_key"><?php esc_html_e( 'Calculator Key', ADN_TEXT_DOMAIN ); ?> <span style="color:#b91c1c;">*</span></label>
					</th>
					<td>
						<input type="text" id="calc_key" name="calc_key" class="regular-text" required
							pattern="[a-z0-9\-]+" placeholder="e.g. roi-calculator" value="">
						<p class="description">
							<?php esc_html_e( 'Lowercase letters, numbers and hyphens only. Cannot be changed after saving. Used in the shortcode key="…".', ADN_TEXT_DOMAIN ); ?>
						</p>
					</td>
				</tr>
				<?php else : ?>
				<tr>
					<th scope="row"><?php esc_html_e( 'Calculator Key', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<code style="font-size:14px;"><?php echo esc_html( $f_key ); ?></code>
						<span class="description">&nbsp;<?php esc_html_e( 'Key cannot be changed after creation.', ADN_TEXT_DOMAIN ); ?></span>
					</td>
				</tr>
				<?php endif; ?>

				<tr>
					<th scope="row">
						<label for="calc_title"><?php esc_html_e( 'Title', ADN_TEXT_DOMAIN ); ?> <span style="color:#b91c1c;">*</span></label>
					</th>
					<td>
						<input type="text" id="calc_title" name="title" class="regular-text" required
							value="<?php echo esc_attr( $f_title ); ?>" placeholder="ROI Calculator Calculator">
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="calc_label"><?php esc_html_e( 'Short Label', ADN_TEXT_DOMAIN ); ?></label>
					</th>
					<td>
						<input type="text" id="calc_label" name="label" class="regular-text"
							value="<?php echo esc_attr( $f_label ); ?>" placeholder="ROI Calculator">
						<p class="description"><?php esc_html_e( 'Shown in calculator card grids. Defaults to Title if blank.', ADN_TEXT_DOMAIN ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="calc_icon"><?php esc_html_e( 'Icon', ADN_TEXT_DOMAIN ); ?></label>
					</th>
					<td>
						<input type="text" id="calc_icon" name="icon"
							style="width:60px;font-size:22px;text-align:center;padding:4px;"
							value="<?php echo esc_attr( $f_icon ); ?>" placeholder="icon">
						<p class="description"><?php esc_html_e( 'Paste an emoji. Shown on the calculator card.', ADN_TEXT_DOMAIN ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Status', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<label>
							<input type="radio" name="status" value="active" <?php checked( $f_status, 'active' ); ?>>
							<?php esc_html_e( 'Active - visible and embeddable', ADN_TEXT_DOMAIN ); ?>
						</label>
						&nbsp;&nbsp;
						<label>
							<input type="radio" name="status" value="inactive" <?php checked( $f_status, 'inactive' ); ?>>
							<?php esc_html_e( 'Inactive - hidden everywhere', ADN_TEXT_DOMAIN ); ?>
						</label>
					</td>
				</tr>

			</tbody></table>
		</div>

		<?php /* ── Settings ────────────────────────────────────────────── */ ?>
		<div class="card" style="max-width:none;background:#fafafa;margin-bottom:24px;">
			<h3 style="margin-top:0;"><?php esc_html_e( 'Settings', ADN_TEXT_DOMAIN ); ?></h3>
			<p class="description" style="margin-bottom:12px;">
				<?php esc_html_e( 'How this calculator appears on the /calculators/ listing and category pages.', ADN_TEXT_DOMAIN ); ?>
			</p>
			<table class="form-table" role="presentation"><tbody>

				<tr>
					<th scope="row"><?php esc_html_e( 'Description', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<textarea class="large-text" rows="2" name="meta_desc"><?php echo esc_textarea( isset( $f_meta['desc'] ) ? $f_meta['desc'] : '' ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Short description shown under the title in the /calculators/ listing.', ADN_TEXT_DOMAIN ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Categories / Parent Terms', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<?php
						$f_parent_terms = isset( $f_meta['parent_terms'] ) && is_array( $f_meta['parent_terms'] ) ? $f_meta['parent_terms'] : array();
						$all_cats = array_unique( array_merge( $f_saved_cats, $f_parent_terms ) );
						?>
						<input type="text" class="large-text" name="meta_categories"
							value="<?php echo esc_attr( implode( ', ', $all_cats ) ); ?>"
							placeholder="<?php esc_attr_e( 'e.g. buying-a-home, property-knowledge', ADN_TEXT_DOMAIN ); ?>">
						<p class="description"><?php esc_html_e( 'Enter category slugs separated by commas (e.g. buying-a-home, tax-saving). Any custom slugs you write here will dynamically generate filter tabs on the calculators listing page.', ADN_TEXT_DOMAIN ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Thumbnail Image', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<?php
						$_sn_thumb_id  = isset( $f_meta['thumbnail_id'] ) ? (int) $f_meta['thumbnail_id'] : 0;
						$_sn_thumb_url = $_sn_thumb_id ? wp_get_attachment_image_url( $_sn_thumb_id, 'thumbnail' ) : false;
						?>
						<div style="display:flex;gap:10px;align-items:flex-start;">
							<div id="sn_thumb_prev" style="min-width:80px;">
								<?php if ( $_sn_thumb_url ) : ?>
									<img src="<?php echo esc_url( $_sn_thumb_url ); ?>" style="width:80px;height:60px;object-fit:cover;border-radius:4px;display:block;" alt="">
								<?php else : ?>
									<div style="width:80px;height:60px;background:#f3f4f6;border:1px dashed #d1d5db;border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:11px;color:#9ca3af;"><?php esc_html_e( 'No image', ADN_TEXT_DOMAIN ); ?></div>
								<?php endif; ?>
							</div>
							<div>
								<input type="hidden" id="sn_thumb_id" name="meta_thumbnail_id"
									value="<?php echo esc_attr( (string) $_sn_thumb_id ); ?>">
								<button type="button" class="button sn-media-select"
									data-target="sn_thumb_id"
									data-preview="sn_thumb_prev">
									<?php esc_html_e( 'Select / Change Image', ADN_TEXT_DOMAIN ); ?>
								</button>
								<?php if ( $_sn_thumb_url ) : ?>
									<button type="button" class="button sn-media-remove"
										data-target="sn_thumb_id"
										data-preview="sn_thumb_prev"
										style="margin-left:4px;">
										<?php esc_html_e( 'Remove', ADN_TEXT_DOMAIN ); ?>
									</button>
								<?php endif; ?>
							</div>
						</div>
						<p class="description"><?php esc_html_e( 'Optional thumbnail shown on the calculator card instead of the emoji icon.', ADN_TEXT_DOMAIN ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Highlight Badge', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<input type="text" class="regular-text" name="meta_highlight"
							value="<?php echo esc_attr( isset( $f_meta['highlight'] ) ? $f_meta['highlight'] : '' ); ?>"
							placeholder="<?php esc_attr_e( 'e.g. Popular, New, Tax Saving', ADN_TEXT_DOMAIN ); ?>">
						<p class="description"><?php esc_html_e( 'Small badge shown on the calculator card. Leave blank for none.', ADN_TEXT_DOMAIN ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Popular Calculator', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="meta_is_popular" value="1"
								<?php checked( ! empty( $f_meta['is_popular'] ) ); ?>>
							<?php esc_html_e( 'Feature in the "Popular Calculators" section on the /calculators/ page and home page', ADN_TEXT_DOMAIN ); ?>
						</label>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Featured Calculator', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="meta_is_featured" value="1"
								<?php checked( ! empty( $f_meta['is_featured'] ) ); ?>>
							<?php esc_html_e( 'Feature this calculator on specific highlighted pages', ADN_TEXT_DOMAIN ); ?>
						</label>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Suggestion Calculator', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="meta_is_suggestion" value="1"
								<?php checked( ! empty( $f_meta['is_suggestion'] ) ); ?>>
							<?php esc_html_e( 'Include this calculator as a suggested tool on related posts or resources', ADN_TEXT_DOMAIN ); ?>
						</label>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Suggested Featured Title', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<input type="text" class="regular-text" name="meta_featured_title"
							value="<?php echo esc_attr( isset( $f_meta['featured_title'] ) ? $f_meta['featured_title'] : '' ); ?>"
							placeholder="e.g. Mortgage Calculator">
						<p class="description"><?php esc_html_e( 'Optional - only used when this calculator is Featured. Leave blank to use the default calculator title.', ADN_TEXT_DOMAIN ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Suggested Featured Description', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<textarea class="large-text" rows="2" name="meta_featured_desc"
							placeholder="e.g. Estimate your monthly mortgage repayments..."><?php echo esc_textarea( isset( $f_meta['featured_desc'] ) ? $f_meta['featured_desc'] : '' ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Optional - only used when this calculator is Featured. Leave blank to use the default description.', ADN_TEXT_DOMAIN ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Suggested Featured Benefit Bullets', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<input type="text" class="regular-text" style="margin-bottom:6px;display:block;" name="meta_benefit_1"
							value="<?php echo esc_attr( isset( $f_meta['benefit_1'] ) ? $f_meta['benefit_1'] : '' ); ?>"
							placeholder="Benefit 1 (e.g. Repayment & interest only)"><br>
						<input type="text" class="regular-text" style="margin-bottom:6px;display:block;" name="meta_benefit_2"
							value="<?php echo esc_attr( isset( $f_meta['benefit_2'] ) ? $f_meta['benefit_2'] : '' ); ?>"
							placeholder="Benefit 2 (e.g. Variable or fixed rates)"><br>
						<input type="text" class="regular-text" style="margin-bottom:6px;display:block;" name="meta_benefit_3"
							value="<?php echo esc_attr( isset( $f_meta['benefit_3'] ) ? $f_meta['benefit_3'] : '' ); ?>"
							placeholder="Benefit 3 (e.g. Overpayment options)"><br>
						<input type="text" class="regular-text" style="margin-bottom:6px;display:block;" name="meta_benefit_4"
							value="<?php echo esc_attr( isset( $f_meta['benefit_4'] ) ? $f_meta['benefit_4'] : '' ); ?>"
							placeholder="Benefit 4 (e.g. Amortization schedule)">
						<p class="description"><?php esc_html_e( 'Bullet points with checkmarks displayed inside the Featured Calculator box.', ADN_TEXT_DOMAIN ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Hide from Listing', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="meta_hidden_from_listing" value="1"
								<?php checked( ! empty( $f_meta['hidden_from_listing'] ) ); ?>>
							<?php esc_html_e( 'Hide this calculator from the /calculators/ listing page (still works via shortcode and direct URL)', ADN_TEXT_DOMAIN ); ?>
						</label>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Card Link URL', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<input type="text" class="regular-text" name="meta_card_url"
							value="<?php echo esc_attr( isset( $f_meta['card_url'] ) ? $f_meta['card_url'] : '' ); ?>"
							placeholder="/calculators/<?php echo $f_key ? esc_attr( $f_key ) : 'your-key'; ?>/">
						<p class="description"><?php esc_html_e( 'Where the calculator card links to. Leave blank to use the auto-generated detail page URL.', ADN_TEXT_DOMAIN ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Help Text', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<textarea class="large-text" rows="2" name="meta_help"><?php echo esc_textarea( isset( $f_meta['help'] ) ? $f_meta['help'] : '' ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Optional note shown below the calculator widget on its detail page.', ADN_TEXT_DOMAIN ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Read-Guide Label', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<input type="text" class="regular-text" name="meta_guide_label"
							value="<?php echo esc_attr( isset( $f_meta['guide_label'] ) ? $f_meta['guide_label'] : '' ); ?>"
							placeholder="Read the full guide →">
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Read-Guide URL', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<input type="text" class="regular-text" name="meta_guide_url"
							value="<?php echo esc_attr( isset( $f_meta['guide_url'] ) ? $f_meta['guide_url'] : '' ); ?>"
							placeholder="/guides/<?php echo $f_key ? esc_attr( $f_key ) : 'your-key'; ?>/">
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Highlight Links Heading', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<input type="text" class="regular-text" name="meta_hl_heading"
							value="<?php echo esc_attr( isset( $f_meta['hl_heading'] ) ? $f_meta['hl_heading'] : '' ); ?>"
							placeholder="<?php esc_attr_e( 'Useful Guides', ADN_TEXT_DOMAIN ); ?>">
						<p class="description"><?php esc_html_e( 'Heading shown above the sidebar link list on this tool\'s page. Leave blank to hide the section.', ADN_TEXT_DOMAIN ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Highlight Links', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<p class="description" style="margin-bottom:10px;"><?php esc_html_e( 'Up to 6 links shown in the sidebar. Each link needs at least a label to appear.', ADN_TEXT_DOMAIN ); ?></p>
						<?php
						$_sn_hl_links = isset( $f_meta['hl_links'] ) && is_array( $f_meta['hl_links'] ) ? $f_meta['hl_links'] : array();
						for ( $li = 0; $li < 6; $li++ ) :
							$_sn_icon  = isset( $_sn_hl_links[ $li ]['icon'] )  ? $_sn_hl_links[ $li ]['icon']  : '';
							$_sn_label = isset( $_sn_hl_links[ $li ]['label'] ) ? $_sn_hl_links[ $li ]['label'] : '';
							$_sn_url   = isset( $_sn_hl_links[ $li ]['url'] )   ? $_sn_hl_links[ $li ]['url']   : '';
						?>
						<div style="display:flex;gap:8px;align-items:center;margin-bottom:6px;">
							<span style="font-size:12px;color:#6b7280;min-width:44px;">
								<?php
								/* translators: %d: link row number */
								echo esc_html( sprintf( __( 'Link %d', ADN_TEXT_DOMAIN ), $li + 1 ) );
								?>
							</span>
							<input type="text"
								style="width:56px;font-size:18px;text-align:center;"
								name="meta_hl_links[<?php echo $li; ?>][icon]"
								value="<?php echo esc_attr( $_sn_icon ); ?>"
								placeholder="icon">
							<input type="text" class="regular-text"
								name="meta_hl_links[<?php echo $li; ?>][label]"
								value="<?php echo esc_attr( $_sn_label ); ?>"
								placeholder="<?php esc_attr_e( 'Guide title', ADN_TEXT_DOMAIN ); ?>">
							<input type="text" class="regular-text"
								name="meta_hl_links[<?php echo $li; ?>][url]"
								value="<?php echo esc_attr( $_sn_url ); ?>"
								placeholder="/guides/...">
						</div>
						<?php endfor; ?>
					</td>
				</tr>

			</tbody></table>
		</div>

		<?php /* ── Page Content ──────────────────────────────────────────── */ ?>
		<div class="card" style="max-width:none;background:#fafafa;margin-bottom:24px;">
			<h3 style="margin-top:0;"><?php esc_html_e( 'Page Content', ADN_TEXT_DOMAIN ); ?></h3>
			<p class="description" style="margin-bottom:12px;">
				<?php esc_html_e( 'HTML content shown on this calculator\'s page, above and below the widget. Tags like <p>, <ul>, <strong>, <a>, <h2>–<h4> are supported.', ADN_TEXT_DOMAIN ); ?>
			</p>
			<table class="form-table" role="presentation"><tbody>

				<tr>
					<th scope="row"><?php esc_html_e( 'Before Calculator', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<textarea class="large-text" rows="5" name="meta_before_content"
							style="font-family:'SFMono-Regular',Consolas,'Liberation Mono',Menlo,monospace;font-size:12px;"
							placeholder="<?php esc_attr_e( '<p>Introductory text shown above the calculator widget.</p>', ADN_TEXT_DOMAIN ); ?>"
						><?php echo esc_textarea( isset( $f_meta['before_content'] ) ? $f_meta['before_content'] : '' ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Rendered directly above the calculator widget on the tool\'s page.', ADN_TEXT_DOMAIN ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'After Calculator', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<textarea class="large-text" rows="5" name="meta_after_content"
							style="font-family:'SFMono-Regular',Consolas,'Liberation Mono',Menlo,monospace;font-size:12px;"
							placeholder="<?php esc_attr_e( '<p>Explanatory notes, disclaimers or extra detail shown below the calculator.</p>', ADN_TEXT_DOMAIN ); ?>"
						><?php echo esc_textarea( isset( $f_meta['after_content'] ) ? $f_meta['after_content'] : '' ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Rendered directly below the calculator widget on the tool\'s page.', ADN_TEXT_DOMAIN ); ?></p>
					</td>
				</tr>

			</tbody></table>
		</div>

		<?php /* ── HTML ──────────────────────────────────────────────────── */ ?>
		<div style="margin-bottom:28px;">
			<h3 style="margin-bottom:6px;"><?php esc_html_e( 'HTML', ADN_TEXT_DOMAIN ); ?></h3>
			<p class="description" style="margin-bottom:10px;">
				<?php esc_html_e( 'Full HTML for the calculator widget. Use class="ah-calc ah-{your-key}" on the root element. The shared', ADN_TEXT_DOMAIN ); ?>
				<code>calculators.css</code>
				<?php esc_html_e( 'is already loaded - CSS variables like --color-primary, --color-bg, --radius-sm are available. Inputs must use IDs your JS reads.', ADN_TEXT_DOMAIN ); ?>
			</p>
			<?php if ( ! $is_edit && empty( $f_html ) ) : ?>
			<details style="margin-bottom:8px;">
				<summary style="cursor:pointer;color:#2271b1;font-size:13px;"><?php esc_html_e( 'Show example HTML', ADN_TEXT_DOMAIN ); ?></summary>
				<pre style="background:#f6f7f7;border:1px solid #ddd;padding:12px;font-size:12px;overflow:auto;margin-top:8px;"><?php echo esc_html( $example_html ); ?></pre>
			</details>
			<?php endif; ?>
			<textarea name="html_content" rows="22"
				style="width:100%;font-family:'SFMono-Regular',Consolas,'Liberation Mono',Menlo,monospace;font-size:13px;line-height:1.6;background:#1e1e1e;color:#d4d4d4;padding:14px 16px;border-radius:4px;border:1px solid #3c3c3c;tab-size:4;resize:vertical;"
				placeholder="<?php echo esc_attr( $example_html ); ?>"><?php echo esc_textarea( $f_html ); ?></textarea>
		</div>

		<?php /* ── JavaScript ────────────────────────────────────────────── */ ?>
		<div style="margin-bottom:28px;">
			<h3 style="margin-bottom:6px;"><?php esc_html_e( 'JavaScript', ADN_TEXT_DOMAIN ); ?></h3>
			<p class="description" style="margin-bottom:10px;">
				<?php esc_html_e( 'Vanilla JS only - no libraries, no imports. Runs inside the isolated iframe. Use document.getElementById() to read inputs and write results. Wrap everything in an IIFE to avoid polluting globals.', ADN_TEXT_DOMAIN ); ?>
			</p>
			<?php if ( ! $is_edit && empty( $f_js ) ) : ?>
			<details style="margin-bottom:8px;">
				<summary style="cursor:pointer;color:#2271b1;font-size:13px;"><?php esc_html_e( 'Show example JS', ADN_TEXT_DOMAIN ); ?></summary>
				<pre style="background:#f6f7f7;border:1px solid #ddd;padding:12px;font-size:12px;overflow:auto;margin-top:8px;"><?php echo esc_html( $example_js ); ?></pre>
			</details>
			<?php endif; ?>
			<textarea name="js_content" rows="18"
				style="width:100%;font-family:'SFMono-Regular',Consolas,'Liberation Mono',Menlo,monospace;font-size:13px;line-height:1.6;background:#1e1e1e;color:#d4d4d4;padding:14px 16px;border-radius:4px;border:1px solid #3c3c3c;tab-size:4;resize:vertical;"
				placeholder="<?php echo esc_attr( $example_js ); ?>"><?php echo esc_textarea( $f_js ); ?></textarea>
		</div>

		<?php /* ── Actions ───────────────────────────────────────────────── */ ?>
		<div style="display:flex;gap:12px;align-items:center;padding-top:8px;">
			<?php submit_button(
				$is_edit
					? __( 'Update Calculator', ADN_TEXT_DOMAIN )
					: __( 'Save Calculator', ADN_TEXT_DOMAIN ),
				'primary', 'submit', false
			); ?>
			<?php if ( $is_edit ) : ?>
				<a href="<?php echo esc_url( ADN_Theme_Admin::tab_url( 'calculators', 'new' ) ); ?>" class="button">
					<?php esc_html_e( '+ Add Another', ADN_TEXT_DOMAIN ); ?>
				</a>
				<a href="<?php echo esc_url( adn_calc_page_url( $f_key ) ); ?>" class="button" target="_blank" rel="noopener">
					<?php esc_html_e( 'View Calculator →', ADN_TEXT_DOMAIN ); ?>
				</a>
			<?php endif; ?>
			<a href="<?php echo esc_url( ADN_Theme_Admin::tab_url( 'calculators', 'list' ) ); ?>" class="button">
				<?php esc_html_e( '← Back to List', ADN_TEXT_DOMAIN ); ?>
			</a>
		</div>

	</form>
</div>

<?php /* ── Media Uploader ────────────────────────────────────────────── */ ?>
<script>
(function ($) {
	'use strict';
	var frame;
	$(document).on('click', '.sn-media-select', function (e) {
		e.preventDefault();
		var $btn    = $(this);
		var tgt     = $btn.data('target');
		var prevId  = $btn.data('preview');

		frame = wp.media({
			title:    '<?php echo esc_js( __( 'Select Thumbnail Image', ADN_TEXT_DOMAIN ) ); ?>',
			button:   { text: '<?php echo esc_js( __( 'Use this image', ADN_TEXT_DOMAIN ) ); ?>' },
			multiple: false,
			library:  { type: 'image' }
		});

		frame.on('select', function () {
			var att = frame.state().get('selection').first().toJSON();
			$('#' + tgt).val(att.id);
			var thumbUrl = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
			$('#' + prevId).html('<img src="' + thumbUrl + '" style="width:80px;height:60px;object-fit:cover;border-radius:4px;display:block;" alt="">');
			$btn.next('.sn-media-remove').show();
		});

		frame.open();
	});

	$(document).on('click', '.sn-media-remove', function (e) {
		e.preventDefault();
		var $btn   = $(this);
		var tgt    = $btn.data('target');
		var prevId = $btn.data('preview');
		$('#' + tgt).val('0');
		$('#' + prevId).html('<div style="width:80px;height:60px;background:#f3f4f6;border:1px dashed #d1d5db;border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:11px;color:#9ca3af;"><?php echo esc_js( __( 'No image', ADN_TEXT_DOMAIN ) ); ?></div>');
		$btn.hide();
	});
})(jQuery);
</script>

