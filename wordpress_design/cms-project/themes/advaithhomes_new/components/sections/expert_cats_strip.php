<?php
/**
 * components/sections/expert_cats_strip.php
 * Props: $categories[] { key, label, active }
 */
defined( 'ABSPATH' ) || exit;

$_cats = ( isset( $categories ) && is_array( $categories ) ) ? $categories : array();
if ( empty( $_cats ) ) return;
?>
<div class="expert-cats-strip" role="tablist" aria-label="<?php esc_attr_e( 'Filter experts by category', ADN_TEXT_DOMAIN ); ?>">
	<div class="expert-cats-inner container">
		<?php foreach ( $_cats as $_c ) :
			$_ck      = esc_attr( sanitize_key( isset( $_c['key'] )   ? (string) $_c['key']   : 'all' ) );
			$_cl      = esc_html( isset( $_c['label'] ) ? (string) $_c['label'] : '' );
			$_active  = ! empty( $_c['active'] );
		?>
			<button
				type="button"
				class="expert-cat-tab<?php echo $_active ? ' active' : ''; ?>"
				data-cat="<?php echo $_ck; ?>"
				role="tab"
				aria-selected="<?php echo $_active ? 'true' : 'false'; ?>"
			><?php echo $_cl; ?></button>
		<?php endforeach; ?>
	</div>
</div>
