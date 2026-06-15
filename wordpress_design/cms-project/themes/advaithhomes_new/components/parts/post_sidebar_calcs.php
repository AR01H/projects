<?php
/**
 * components/parts/post_sidebar_calcs.php
 *
 * Sidebar - Popular Calculators list.
 *
 * Props (via extract):
 *   $calculators = [
 *       'view_all_url' => string,
 *       'items'        => [ { icon, label, url }, … ]
 *   ]
 */

defined( 'ABSPATH' ) || exit;

$_calcs    = isset( $calculators ) ? (array) $calculators : array();
$_items    = isset( $_calcs['items'] ) ? (array) $_calcs['items'] : array();
$_view_url = isset( $_calcs['view_all_url'] ) ? adn_link( (string) $_calcs['view_all_url'] ) : '';

if ( empty( $_items ) ) {
	return;
}
?>
<div class="sidebar-box">
	<h3><?php esc_html_e( 'Popular Calculators', ADN_TEXT_DOMAIN ); ?></h3>
	<ul class="sidebar-calc-list" role="list">
		<?php foreach ( $_items as $_c ) :
			$_c_icon  = adn_icon( isset( $_c['icon'] )  ? (string) $_c['icon']  : '🧮' );
			$_c_label = esc_html( isset( $_c['label'] ) ? (string) $_c['label'] : '' );
			$_c_url   = isset( $_c['url'] ) ? adn_link( (string) $_c['url'] ) : '#';
		?>
			<li>
				<a href="<?php echo esc_url( $_c_url ); ?>" class="sidebar-calc-item">
					<span class="sidebar-calc-icon" aria-hidden="true"><?php echo $_c_icon; ?></span>
					<span class="sidebar-calc-label"><?php echo $_c_label; ?></span>
					<span class="sidebar-arrow" aria-hidden="true">→</span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php if ( '' !== $_view_url ) : ?>
		<a href="<?php echo esc_url( $_view_url ); ?>" class="sidebar-view-all">
			<?php echo esc_html( 'View all ' . SITE_TOOLS_PLURAL ); ?> →
		</a>
	<?php endif; ?>
</div>
