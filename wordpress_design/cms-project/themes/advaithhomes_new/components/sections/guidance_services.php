<?php
/**
 * components/sections/guidance_services.php
 * Props: $services { heading, items[] { icon, title, desc, url, cta } }
 */
defined( 'ABSPATH' ) || exit;

$_s     = isset( $services ) ? (array) $services : array();
$_hdg   = esc_html( isset( $_s['heading'] ) ? (string) $_s['heading'] : 'We can help you with' );
$_items = isset( $_s['items'] ) ? (array) $_s['items'] : array();
if ( empty( $_items ) ) return;
?>
<div class="guidance-services-panel">
	<h2><?php echo $_hdg; ?></h2>
	<div class="guidance-services-grid">
		<?php foreach ( $_items as $_item ) :
			$_si  = esc_html( isset( $_item['icon'] )  ? (string) $_item['icon']  : '' );
			$_st  = esc_html( isset( $_item['title'] ) ? (string) $_item['title'] : '' );
			$_sd  = esc_html( isset( $_item['desc'] )  ? (string) $_item['desc']  : '' );
			$_sc  = esc_html( isset( $_item['cta'] )   ? (string) $_item['cta']   : 'Get Guidance' );
			$_url = esc_url( adn_link( isset( $_item['url'] ) ? (string) $_item['url'] : '#' ) );
		?>
			<div class="guidance-service-card">
				<span class="gs-icon" aria-hidden="true"><?php echo $_si; ?></span>
				<h3><?php echo $_st; ?></h3>
				<p><?php echo $_sd; ?></p>
				<a href="<?php echo $_url; ?>" class="gs-cta">
					<?php echo $_sc; ?> <span aria-hidden="true">→</span>
				</a>
			</div>
		<?php endforeach; ?>
	</div>
</div>
