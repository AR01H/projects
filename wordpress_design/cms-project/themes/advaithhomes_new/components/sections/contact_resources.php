<?php
/**
 * components/sections/contact_resources.php
 * Props: $resources { heading, items[] { icon, title, desc, url } }
 */
defined( 'ABSPATH' ) || exit;

$_res   = isset( $resources ) ? (array) $resources : array();
$_hdg   = esc_html( isset( $_res['heading'] ) ? (string) $_res['heading'] : 'While you wait, explore popular resources' );
$_items = isset( $_res['items'] ) ? (array) $_res['items'] : array();
if ( empty( $_items ) ) return;
?>
<section class="contact-resources-section">
	<div class="container">
		<h2 class="contact-section-heading"><?php echo $_hdg; ?></h2>
		<div class="contact-resources-grid">
			<?php foreach ( $_items as $_r ) :
				$_ri   = adn_icon( isset( $_r['icon'] )  ? (string) $_r['icon']  : '' );
				$_rt   = esc_html( isset( $_r['title'] ) ? (string) $_r['title'] : '' );
				$_rd   = esc_html( isset( $_r['desc'] )  ? (string) $_r['desc']  : '' );
				$_rurl = esc_url( adn_link( isset( $_r['url'] ) ? (string) $_r['url'] : '#' ) );
			?>
				<a href="<?php echo $_rurl; ?>" class="contact-resource-card">
					<span class="cr-icon" aria-hidden="true"><?php echo $_ri; ?></span>
					<h3><?php echo $_rt; ?></h3>
					<p><?php echo $_rd; ?></p>
					<span class="cr-arrow" aria-hidden="true">→</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
