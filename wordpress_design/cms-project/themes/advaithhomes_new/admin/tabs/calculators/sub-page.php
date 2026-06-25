<?php
/**
 * admin/tabs/calculators/sub-page.php - Calculators page content settings.
 *
 * Controls hero, trust bar, search placeholder, popular calcs selection,
 * sidebar help CTA and find-my-calc CTA. All saved to adn_calculators_page option.
 */

defined( 'ABSPATH' ) || exit;

$pg    = get_option( 'adn_calculators_page', array() );
$tools = function_exists( 'adn_calculators' ) ? adn_calculators() : array();

// Helper: read a saved string field.
function _cp( $pg, $key, $default = '' ) {
	return isset( $pg[ $key ] ) && '' !== $pg[ $key ] ? (string) $pg[ $key ] : $default;
}

$popular_keys = isset( $pg['popular_keys'] ) && is_array( $pg['popular_keys'] ) ? $pg['popular_keys'] : array();
?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="adn_save_tools_page">
	<?php wp_nonce_field( 'adn_save_tools_page' ); ?>

	<?php /* ── Hero ──────────────────────────────────────────────────── */ ?>
	<div class="card" style="max-width:none;margin-bottom:20px;">
		<h2 style="margin-top:0;"><?php esc_html_e( 'Hero Banner', ADN_TEXT_DOMAIN ); ?></h2>
		<table class="form-table" role="presentation"><tbody>
			<tr>
				<th scope="row"><label for="cp_hero_title"><?php esc_html_e( 'Title', ADN_TEXT_DOMAIN ); ?></label></th>
				<td><input type="text" id="cp_hero_title" name="hero_title" class="regular-text"
					value="<?php echo esc_attr( _cp( $pg, 'hero_title' ) ); ?>" placeholder="All Calculators"></td>
			</tr>
			<tr>
				<th scope="row"><label for="cp_hero_desc"><?php esc_html_e( 'Description', ADN_TEXT_DOMAIN ); ?></label></th>
				<td><textarea id="cp_hero_desc" name="hero_desc" class="large-text" rows="2"
					placeholder="Smart tools and calculators to help you plan, budget and make confident decisions."><?php echo esc_textarea( _cp( $pg, 'hero_desc' ) ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="cp_hero_icon"><?php esc_html_e( 'Background icon', ADN_TEXT_DOMAIN ); ?></label></th>
				<td>
					<input type="text" id="cp_hero_icon" name="hero_icon" style="width:90px;font-size:20px;text-align:center;"
						value="<?php echo esc_attr( _cp( $pg, 'hero_icon' ) ); ?>" placeholder="🏠🧮">
					<p class="description"><?php esc_html_e( 'One or two emojis shown as the decorative background of the hero.', ADN_TEXT_DOMAIN ); ?></p>
				</td>
			</tr>
		</tbody></table>
	</div>

	<?php /* ── Trust bar ────────────────────────────────────────────── */ ?>
	<div class="card" style="max-width:none;margin-bottom:20px;">
		<h2 style="margin-top:0;"><?php esc_html_e( 'Trust Bar', ADN_TEXT_DOMAIN ); ?></h2>
		<p class="description" style="margin-bottom:14px;">
			<?php esc_html_e( 'Four trust signals shown below the hero. Leave all blank to use defaults.', ADN_TEXT_DOMAIN ); ?>
		</p>
		<?php
		$trust_defaults = array(
			1 => array( 'icon' => '⏱', 'title' => 'Accurate & Up to Date', 'subtitle' => 'Based on latest UK data' ),
			2 => array( 'icon' => '✓',  'title' => 'Free to Use',           'subtitle' => 'No sign-up required' ),
			3 => array( 'icon' => '≡',  'title' => 'Easy to Understand',    'subtitle' => 'Simple, clear results' ),
			4 => array( 'icon' => '◎',  'title' => 'Independent',           'subtitle' => 'Unbiased information' ),
		);
		for ( $i = 1; $i <= 4; $i++ ) :
			$def = $trust_defaults[ $i ];
		?>
		<div style="display:flex;gap:12px;align-items:center;margin-bottom:10px;padding:10px;background:#f9f9f9;border:1px solid #eee;border-radius:4px;">
			<span style="font-size:13px;font-weight:600;color:#555;min-width:50px;">
				<?php /* translators: %d: trust item number */ ?>
				<?php echo esc_html( sprintf( __( 'Item %d', ADN_TEXT_DOMAIN ), $i ) ); ?>
			</span>
			<input type="text" name="trust_<?php echo $i; ?>_icon" style="width:60px;font-size:18px;text-align:center;"
				value="<?php echo esc_attr( _cp( $pg, 'trust_' . $i . '_icon' ) ); ?>"
				placeholder="<?php echo esc_attr( $def['icon'] ); ?>">
			<input type="text" class="regular-text" name="trust_<?php echo $i; ?>_title"
				value="<?php echo esc_attr( _cp( $pg, 'trust_' . $i . '_title' ) ); ?>"
				placeholder="<?php echo esc_attr( $def['title'] ); ?>">
			<input type="text" class="regular-text" name="trust_<?php echo $i; ?>_subtitle"
				value="<?php echo esc_attr( _cp( $pg, 'trust_' . $i . '_subtitle' ) ); ?>"
				placeholder="<?php echo esc_attr( $def['subtitle'] ); ?>">
		</div>
		<?php endfor; ?>
	</div>

	<?php /* ── Search ───────────────────────────────────────────────── */ ?>
	<div class="card" style="max-width:none;margin-bottom:20px;">
		<h2 style="margin-top:0;"><?php esc_html_e( 'Search Bar', ADN_TEXT_DOMAIN ); ?></h2>
		<table class="form-table" role="presentation"><tbody>
			<tr>
				<th scope="row"><label for="cp_search"><?php esc_html_e( 'Placeholder text', ADN_TEXT_DOMAIN ); ?></label></th>
				<td><input type="text" id="cp_search" name="search_placeholder" class="regular-text"
					value="<?php echo esc_attr( _cp( $pg, 'search_placeholder' ) ); ?>" placeholder="Search calculators…"></td>
			</tr>
		</tbody></table>
	</div>

	<?php /* ── Popular calculators ────────────────────────────────── */ ?>
	<div class="card" style="max-width:none;margin-bottom:20px;">
		<h2 style="margin-top:0;"><?php esc_html_e( 'Popular Calculators', ADN_TEXT_DOMAIN ); ?></h2>
		<div class="notice notice-info inline" style="margin:0;padding:10px 14px;">
			<p style="margin:0;">
				<?php esc_html_e( 'Popular calculators are now managed per-calculator. Go to', ADN_TEXT_DOMAIN ); ?>
				<a href="<?php echo esc_url( ADN_Theme_Admin::tab_url( 'calculators', 'list' ) ); ?>">
					<?php esc_html_e( 'Calculator List', ADN_TEXT_DOMAIN ); ?>
				</a>
				<?php esc_html_e( 'and tick the "Popular Calculator" checkbox on each one you want featured.', ADN_TEXT_DOMAIN ); ?>
			</p>
		</div>
	</div>

	<?php
	/* ── Sidebar Highlight Links: helper to render one section ─────── */
	$_render_hl_section = function( $num, $pg ) {
		$key_head  = 'sidebar_hl' . $num . '_heading';
		$key_items = 'sidebar_hl' . $num . '_items';
		$saved_items = isset( $pg[ $key_items ] ) && is_array( $pg[ $key_items ] ) ? $pg[ $key_items ] : array();
		$heading_val = isset( $pg[ $key_head ] ) ? $pg[ $key_head ] : '';
		?>
		<div class="card" style="max-width:none;margin-bottom:20px;">
			<h2 style="margin-top:0;">
				<?php echo esc_html( sprintf( __( 'Sidebar Section %d', ADN_TEXT_DOMAIN ), $num ) ); ?>
			</h2>
			<p class="description" style="margin-bottom:14px;">
				<?php esc_html_e( 'Leave heading blank to hide this section.', ADN_TEXT_DOMAIN ); ?>
			</p>
			<table class="form-table" role="presentation"><tbody>
				<tr>
					<th scope="row">
						<label for="cp_hl<?php echo esc_attr( $num ); ?>_heading">
							<?php esc_html_e( 'Heading', ADN_TEXT_DOMAIN ); ?>
						</label>
					</th>
					<td>
						<input type="text"
							id="cp_hl<?php echo esc_attr( $num ); ?>_heading"
							name="<?php echo esc_attr( $key_head ); ?>"
							class="regular-text"
							value="<?php echo esc_attr( $heading_val ); ?>"
							placeholder="<?php esc_attr_e( 'e.g. Popular Guides', ADN_TEXT_DOMAIN ); ?>">
					</td>
				</tr>
			</tbody></table>
			<?php for ( $i = 0; $i < 6; $i++ ) :
				$_icon  = isset( $saved_items[ $i ]['icon'] )  ? $saved_items[ $i ]['icon']  : '';
				$_label = isset( $saved_items[ $i ]['label'] ) ? $saved_items[ $i ]['label'] : '';
				$_url   = isset( $saved_items[ $i ]['url'] )   ? $saved_items[ $i ]['url']   : '';
			?>
			<div style="display:flex;gap:10px;align-items:center;margin-bottom:8px;padding:10px;background:#f9f9f9;border:1px solid #eee;border-radius:4px;">
				<span style="font-size:12px;font-weight:600;color:#555;min-width:40px;">
					<?php echo esc_html( sprintf( __( 'Link %d', ADN_TEXT_DOMAIN ), $i + 1 ) ); ?>
				</span>
				<input type="text"
					name="<?php echo esc_attr( $key_items ); ?>[<?php echo $i; ?>][icon]"
					style="width:54px;font-size:18px;text-align:center;"
					value="<?php echo esc_attr( $_icon ); ?>" placeholder="🏠">
				<input type="text" class="regular-text"
					name="<?php echo esc_attr( $key_items ); ?>[<?php echo $i; ?>][label]"
					value="<?php echo esc_attr( $_label ); ?>"
					placeholder="<?php esc_attr_e( 'Link label', ADN_TEXT_DOMAIN ); ?>">
				<input type="text" class="regular-text"
					name="<?php echo esc_attr( $key_items ); ?>[<?php echo $i; ?>][url]"
					value="<?php echo esc_attr( $_url ); ?>"
					placeholder="/buying-a-home/">
			</div>
			<?php endfor; ?>
		</div>
		<?php
	};
	?>
	<?php $_ = $_render_hl_section( 1, $pg ); ?>
	<?php $_ = $_render_hl_section( 2, $pg ); ?>

	<?php /* ── Sidebar help CTA ─────────────────────────────────────── */ ?>
	<div class="card" style="max-width:none;margin-bottom:20px;">
		<h2 style="margin-top:0;"><?php esc_html_e( 'Sidebar Help CTA', ADN_TEXT_DOMAIN ); ?></h2>
		<table class="form-table" role="presentation"><tbody>
			<tr>
				<th scope="row"><label for="cp_sh_title"><?php esc_html_e( 'Title', ADN_TEXT_DOMAIN ); ?></label></th>
				<td><input type="text" id="cp_sh_title" name="sidebar_help_title" class="regular-text"
					value="<?php echo esc_attr( _cp( $pg, 'sidebar_help_title' ) ); ?>"
					placeholder="Need Help Using a Calculator?"></td>
			</tr>
			<tr>
				<th scope="row"><label for="cp_sh_text"><?php esc_html_e( 'Body text', ADN_TEXT_DOMAIN ); ?></label></th>
				<td><textarea id="cp_sh_text" name="sidebar_help_text" class="large-text" rows="2"
					placeholder="Our guides explain how each calculator works and what the results mean."><?php echo esc_textarea( _cp( $pg, 'sidebar_help_text' ) ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="cp_sh_btn_label"><?php esc_html_e( 'Button label', ADN_TEXT_DOMAIN ); ?></label></th>
				<td><input type="text" id="cp_sh_btn_label" name="sidebar_help_btn_label" class="regular-text"
					value="<?php echo esc_attr( _cp( $pg, 'sidebar_help_btn_label' ) ); ?>"
					placeholder="View Calculator Guide →"></td>
			</tr>
			<tr>
				<th scope="row"><label for="cp_sh_btn_url"><?php esc_html_e( 'Button URL', ADN_TEXT_DOMAIN ); ?></label></th>
				<td><input type="text" id="cp_sh_btn_url" name="sidebar_help_btn_url" class="regular-text"
					value="<?php echo esc_attr( _cp( $pg, 'sidebar_help_btn_url' ) ); ?>"
					placeholder="/buying-guides/"></td>
			</tr>
		</tbody></table>
	</div>

	<?php /* ── Find my calculator CTA ──────────────────────────────── */ ?>
	<div class="card" style="max-width:none;margin-bottom:20px;">
		<h2 style="margin-top:0;"><?php esc_html_e( '"Find My Calculator" CTA', ADN_TEXT_DOMAIN ); ?></h2>
		<p class="description" style="margin-bottom:14px;">
			<?php esc_html_e( 'Banner shown at the bottom of the all-calculators list.', ADN_TEXT_DOMAIN ); ?>
		</p>
		<table class="form-table" role="presentation"><tbody>
			<tr>
				<th scope="row"><label for="cp_fc_title"><?php esc_html_e( 'Title', ADN_TEXT_DOMAIN ); ?></label></th>
				<td><input type="text" id="cp_fc_title" name="find_cta_title" class="regular-text"
					value="<?php echo esc_attr( _cp( $pg, 'find_cta_title' ) ); ?>"
					placeholder="Not sure which calculator you need?"></td>
			</tr>
			<tr>
				<th scope="row"><label for="cp_fc_desc"><?php esc_html_e( 'Description', ADN_TEXT_DOMAIN ); ?></label></th>
				<td><textarea id="cp_fc_desc" name="find_cta_desc" class="large-text" rows="2"
					placeholder="Answer a few simple questions and we'll recommend the right calculator for you."><?php echo esc_textarea( _cp( $pg, 'find_cta_desc' ) ); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><label for="cp_fc_btn_label"><?php esc_html_e( 'Button label', ADN_TEXT_DOMAIN ); ?></label></th>
				<td><input type="text" id="cp_fc_btn_label" name="find_cta_btn_label" class="regular-text"
					value="<?php echo esc_attr( _cp( $pg, 'find_cta_btn_label' ) ); ?>"
					placeholder="Find My Calculator →"></td>
			</tr>
			<tr>
				<th scope="row"><label for="cp_fc_btn_url"><?php esc_html_e( 'Button URL', ADN_TEXT_DOMAIN ); ?></label></th>
				<td><input type="text" id="cp_fc_btn_url" name="find_cta_btn_url" class="regular-text"
					value="<?php echo esc_attr( _cp( $pg, 'find_cta_btn_url' ) ); ?>"
					placeholder="/ask-expert/"></td>
			</tr>
		</tbody></table>
	</div>

	<?php submit_button( __( 'Save Page Settings', ADN_TEXT_DOMAIN ) ); ?>
</form>


