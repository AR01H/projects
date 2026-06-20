<?php
/**
 * components/parts/sidebar_guide_parents.php - Sidebar: Browse by Guide Topic.
 *
 * Props: $guide_parents { heading, items[] { icon, label, url, count }, view_all { label, url } }
 */

defined( 'ABSPATH' ) || exit;

$guide_parents = isset( $guide_parents ) && is_array( $guide_parents ) ? $guide_parents : array();
$items         = isset( $guide_parents['items'] )    ? (array) $guide_parents['items']    : array();
$view_all      = isset( $guide_parents['view_all'] ) ? (array) $guide_parents['view_all'] : array();

if ( empty( $items ) ) { return; }

$_all_url   = ! empty( $view_all['url'] )   ? esc_url( adn_link( (string) $view_all['url'] ) )   : '';
$_all_label = ! empty( $view_all['label'] ) ? esc_html( (string) $view_all['label'] ) : '';
?>
<div class="sw-panel">
	<div class="sw-header">
		<h3 class="sw-title"><?php echo esc_html( isset( $guide_parents['heading'] ) ? $guide_parents['heading'] : adn_term( 'sidebar.browse_topics', 'Browse by Topic' ) ); ?></h3>
		<?php if ( '' !== $_all_url && '' !== $_all_label ) : ?>
			<a href="<?php echo $_all_url; ?>" class="sw-view-all"><?php echo $_all_label; ?></a>
		<?php endif; ?>
	</div>

	<ul class="sw-list" role="list">
		<?php foreach ( $items as $item ) :
			$icon      = isset( $item['icon'] )      ? (string) $item['icon']  : '📚';
			$label     = isset( $item['label'] )     ? (string) $item['label'] : '';
			$url       = isset( $item['url'] )       ? (string) $item['url']   : '#';
			$count     = isset( $item['count'] )     ? (int)    $item['count'] : 0;
			$thumbnail = isset( $item['thumbnail'] ) ? (string) $item['thumbnail'] : '';
			$is_active = ! empty( $item['is_active'] );
		?>
		<li class="sw-item<?php echo $is_active ? ' sw-item--active' : ''; ?>">
			<a href="<?php echo esc_url( adn_link( $url ) ); ?>" class="sw-item-link<?php echo $is_active ? ' sw-item-link--active' : ''; ?>">
				<?php if ( '' !== $thumbnail ) : ?>
					<span class="sw-item-thumb" aria-hidden="true"><img src="<?php echo esc_url( $thumbnail ); ?>" alt="" loading="lazy"></span>
				<?php else : ?>
					<span class="sw-item-icon" aria-hidden="true"><?php echo adn_icon( $icon ); ?></span>
				<?php endif; ?>
				<span class="sw-item-label"><?php echo esc_html( $label ); ?></span>
				<?php if ( $count > 0 ) : ?>
					<span class="sw-item-count"><?php echo esc_html( (string) $count ); ?></span>
				<?php endif; ?>
				<span class="sw-item-arrow" aria-hidden="true">›</span>
			</a>
		</li>
		<?php endforeach; ?>
	</ul>
</div>
