<?php
/**
 * components/parts/sidebar_quick_tools.php - Sidebar: Quick Tools list.
 *
 * Props: $quick_tools { heading, items[] { icon, label, url }, cta { label, url } }
 */

defined( 'ABSPATH' ) || exit;

$quick_tools = isset( $quick_tools ) && is_array( $quick_tools ) ? $quick_tools : array();
$items       = isset( $quick_tools['items'] ) ? (array) $quick_tools['items'] : array();
$cta         = isset( $quick_tools['cta'] )   ? (array) $quick_tools['cta']   : array();

if ( empty( $items ) ) { return; }

$_cta_url   = ! empty( $cta['url'] )   ? esc_url( adn_link( (string) $cta['url'] ) )   : '';
$_cta_label = ! empty( $cta['label'] ) ? esc_html( (string) $cta['label'] )             : '';
?>
<div class="sw-panel">
	<div class="sw-header">
		<h3 class="sw-title"><?php echo esc_html( ! empty( $quick_tools['heading'] ) ? $quick_tools['heading'] : ( defined( 'SITE_TOOLS_PLURAL' ) ? SITE_TOOLS_PLURAL : 'Tools' ) ); ?></h3>
	</div>

	<ul class="sw-list" role="list">
		<?php foreach ( $items as $item ) :
			$_icon  = isset( $item['icon'] )  ? (string) $item['icon']  : '🧮';
			$_label = isset( $item['label'] ) ? (string) $item['label'] : '';
			$_url   = isset( $item['url'] )   ? esc_url( adn_link( (string) $item['url'] ) ) : '#';
			if ( '' === $_label ) { continue; }
		?>
		<li class="sw-item">
			<a href="<?php echo $_url; ?>" class="sw-item-link">
				<span class="sw-item-icon" aria-hidden="true"><?php echo adn_icon( $_icon ); ?></span>
				<span class="sw-item-label"><?php echo esc_html( $_label ); ?></span>
				<span class="sw-item-arrow" aria-hidden="true">›</span>
			</a>
		</li>
		<?php endforeach; ?>
	</ul>

	<?php if ( '' !== $_cta_url && '' !== $_cta_label ) : ?>
	<div class="sw-footer">
		<a href="<?php echo $_cta_url; ?>" class="sw-view-all"><?php echo $_cta_label; ?></a>
	</div>
	<?php endif; ?>
</div>
