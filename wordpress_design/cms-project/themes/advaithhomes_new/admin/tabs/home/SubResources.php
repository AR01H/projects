<?php
defined( 'ABSPATH' ) || exit;

$saved       = get_option( 'adn_home_resources', array() );
$library_ids = isset( $saved['library_ids'] ) && is_array( $saved['library_ids'] )
	? array_map( 'absint', $saved['library_ids'] )
	: array();
$res_heading = isset( $saved['heading'] ) ? (string) $saved['heading'] : '';

$all_resources = class_exists( 'AH_Resources_Model' ) ? ( new AH_Resources_Model() )->get_active() : array();
$type_labels   = class_exists( 'AH_Resources_Model' ) ? AH_Resources_Model::type_labels() : array();

$page_slug = ADN_Theme_Admin::tab_page_slug( 'home' );
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Home Page - Resources', ADN_TEXT_DOMAIN ); ?></h1>

	<?php if ( isset( $_GET['adn_saved'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Resources saved.', ADN_TEXT_DOMAIN ); ?></p></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="adn_save_home_resources">
		<?php wp_nonce_field( 'adn_save_home_resources' ); ?>

		<div class="card" style="max-width:none;margin-top:16px;">
			<h2><?php esc_html_e( 'Resources', ADN_TEXT_DOMAIN ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Select resources from the library to display on the Home page. Add or edit resources in', ADN_TEXT_DOMAIN ); ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-resources' ) ); ?>" target="_blank"><?php esc_html_e( 'CMS Admin → Resources', ADN_TEXT_DOMAIN ); ?> ↗</a>
			</p>

			<table class="form-table" style="margin:12px 0 4px;">
				<tr>
					<th style="width:160px;"><?php esc_html_e( 'Section Heading', ADN_TEXT_DOMAIN ); ?></th>
					<td>
						<input type="text" name="heading" value="<?php echo esc_attr( $res_heading ); ?>"
							style="width:380px;" placeholder="<?php esc_attr_e( 'e.g. Useful Resources', ADN_TEXT_DOMAIN ); ?>">
						<p class="description"><?php esc_html_e( 'Displayed above the resource cards on the home page.', ADN_TEXT_DOMAIN ); ?></p>
					</td>
				</tr>
			</table>

			<?php if ( empty( $all_resources ) ) : ?>
				<p style="color:#999;margin-top:12px;"><?php esc_html_e( 'No active resources yet. Add some in CMS Admin → Resources first.', ADN_TEXT_DOMAIN ); ?></p>
			<?php else : ?>
			<div style="margin-top:14px;display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:8px;">
				<?php foreach ( $all_resources as $r ) :
					$checked  = in_array( (int) $r->id, $library_ids, true );
					$type_lbl = $type_labels[ $r->type ] ?? $r->type;
				?>
				<label style="display:flex;align-items:flex-start;gap:8px;padding:10px 12px;background:#f6f7f7;border:1px solid <?php echo $checked ? '#2271b1' : '#e2e4e7'; ?>;border-radius:4px;cursor:pointer;">
					<input type="checkbox" name="resource_ids[]" value="<?php echo esc_attr( $r->id ); ?>"<?php checked( $checked ); ?> style="margin-top:2px;flex-shrink:0;">
					<span>
						<strong style="display:block;font-size:13px;"><?php echo esc_html( $r->title ?: '(no title)' ); ?></strong>
						<span style="font-size:11px;color:#666;"><?php echo esc_html( $type_lbl ); ?></span>
						<?php if ( $r->url ) : ?>
							<span style="display:block;font-size:11px;color:#999;word-break:break-all;"><?php echo esc_html( mb_strimwidth( (string) $r->url, 0, 55, '…' ) ); ?></span>
						<?php endif; ?>
					</span>
				</label>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $all_resources ) ) : ?>
		<p style="margin-top:16px;">
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Resources', ADN_TEXT_DOMAIN ); ?></button>
		</p>
		<?php endif; ?>
	</form>
</div>
