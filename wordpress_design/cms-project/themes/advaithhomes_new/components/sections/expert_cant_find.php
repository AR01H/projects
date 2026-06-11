<?php
/**
 * components/sections/expert_cant_find.php
 * Props: $cant_find_cta { icon, heading, desc, button_label, button_url }
 */
defined( 'ABSPATH' ) || exit;

$_c     = isset( $cant_find_cta ) ? (array) $cant_find_cta : array();
$_ico   = esc_html( isset( $_c['icon'] )         ? (string) $_c['icon']         : '🔍' );
$_hdg   = esc_html( isset( $_c['heading'] )      ? (string) $_c['heading']      : "Can't find the right expert?" );
$_dsc   = esc_html( isset( $_c['desc'] )         ? (string) $_c['desc']         : '' );
$_btn   = esc_html( isset( $_c['button_label'] ) ? (string) $_c['button_label'] : 'Get Matched Now' );
$_url   = esc_url( adn_link( isset( $_c['button_url'] ) ? (string) $_c['button_url'] : '#' ) );
?>
<div class="expert-cant-find-cta">
	<div class="ecf-left">
		<span class="ecf-icon" aria-hidden="true"><?php echo $_ico; ?></span>
		<div>
			<h3><?php echo $_hdg; ?></h3>
			<?php if ( '' !== $_dsc ) : ?>
				<p><?php echo $_dsc; ?></p>
			<?php endif; ?>
		</div>
	</div>
	<a href="<?php echo $_url; ?>" class="btn btn-primary ecf-btn">
		<?php echo $_btn; ?> →
	</a>
</div>
