<?php
/**
 * components/parts/sidebar_featured_topics.php - Sidebar: Featured Topics list.
 *
 * Props: $featured_topics { heading, items[] { icon, label, url } }
 * Usage: adn_component( 'parts/sidebar_featured_topics', array( 'featured_topics' => $ctx['sidebar']['featured_topics'] ) );
 */

defined( 'ABSPATH' ) || exit;

$featured_topics = isset( $featured_topics ) && is_array( $featured_topics ) ? $featured_topics : array();
$items           = isset( $featured_topics['items'] ) ? (array) $featured_topics['items'] : array();
?>
<div class="sidebar-card">
	<?php if ( ! empty( $featured_topics['heading'] ) ) : ?>
		<div class="sidebar-card-title"><?php echo esc_html( $featured_topics['heading'] ); ?></div>
	<?php endif; ?>

	<?php foreach ( $items as $item ) : ?>
		<div class="hot-topic-sidebar-item">
			<span class="hot-topic-sidebar-icon"><?php echo adn_icon( isset( $item['icon'] ) ? $item['icon'] : '' ); ?></span>
			<a href="<?php echo esc_url( adn_link( isset( $item['url'] ) ? $item['url'] : '' ) ); ?>" class="hot-topic-sidebar-text">
				<?php echo esc_html( isset( $item['label'] ) ? $item['label'] : '' ); ?>
			</a>
		</div>
	<?php endforeach; ?>
</div>
