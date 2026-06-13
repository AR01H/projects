<?php
/**
 * components/parts/sidebar_quick_tools.php - Sidebar: Quick Tools card (dark bg).
 *
 * Props: $quick_tools { heading, items[], cta { label, url } }
 * Usage: adn_component( 'parts/sidebar_quick_tools', array( 'quick_tools' => $ctx['sidebar']['quick_tools'] ) );
 */

defined( 'ABSPATH' ) || exit;

$quick_tools = isset( $quick_tools ) && is_array( $quick_tools ) ? $quick_tools : array();
$items       = isset( $quick_tools['items'] ) ? (array) $quick_tools['items'] : array();
$cta         = isset( $quick_tools['cta'] )   ? (array) $quick_tools['cta']   : array();
?>
<div class="sidebar-card sidebar-card--dark">
	<?php if ( ! empty( $quick_tools['heading'] ) ) : ?>
		<div class="sidebar-card-title"><?php echo esc_html( $quick_tools['heading'] ); ?></div>
	<?php endif; ?>

	<?php foreach ( $items as $item ) : ?>
		<a href="<?php echo esc_url( adn_link( isset( $item['url'] ) ? $item['url'] : '' ) ); ?>" class="sidebar-link-item">
			<div>
				<span class="sidebar-link-icon"><?php echo adn_icon( isset( $item['icon'] ) ? $item['icon'] : '' ); ?></span>
				<?php echo esc_html( isset( $item['label'] ) ? $item['label'] : '' ); ?>
			</div>
			<span class="sidebar-chevron">&rsaquo;</span>
		</a>
	<?php endforeach; ?>

	<?php if ( ! empty( $cta['label'] ) ) : ?>
		<a href="<?php echo esc_url( adn_link( isset( $cta['url'] ) ? $cta['url'] : '' ) ); ?>" class="btn btn-accent sidebar-cta">
			<?php echo esc_html( $cta['label'] ); ?>
		</a>
	<?php elseif ( ! empty( $view_all['label'] ) ) : ?>
		<a href="<?php echo esc_url( adn_link( isset( $view_all['url'] ) ? $view_all['url'] : '' ) ); ?>" class="view-all-small">
			<?php echo esc_html( $view_all['label'] ); ?>
		</a>
	<?php endif; ?>
</div>
