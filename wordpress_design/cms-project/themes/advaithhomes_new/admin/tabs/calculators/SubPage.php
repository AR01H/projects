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


