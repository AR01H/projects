<?php
/**
 * components/parts/sidebar_most_read.php — Sidebar: numbered most-read list.
 *
 * Props: $most_read { heading, items[] { num, title, url }, view_all { label, url } }
 * Usage: adn_component( 'parts/sidebar_most_read', array( 'most_read' => $ctx['sidebar']['most_read'] ) );
 */

defined( 'ABSPATH' ) || exit;

$most_read = isset( $most_read ) && is_array( $most_read ) ? $most_read : array();
$items     = isset( $most_read['items'] )    ? (array) $most_read['items']    : array();
$view_all  = isset( $most_read['view_all'] ) ? (array) $most_read['view_all'] : array();
?>
<div class="sidebar-card">
	<?php if ( ! empty( $most_read['heading'] ) ) : ?>
		<div class="sidebar-card-title"><?php echo esc_html( $most_read['heading'] ); ?></div>
	<?php endif; ?>

	<div class="most-read-list">
		<?php foreach ( $items as $item ) : ?>
			<div class="most-read-item">
				<div class="most-read-num"><?php echo esc_html( isset( $item['num'] ) ? $item['num'] : '' ); ?></div>
				<a href="<?php echo esc_url( adn_link( isset( $item['url'] ) ? $item['url'] : '' ) ); ?>" class="most-read-title">
					<?php echo esc_html( isset( $item['title'] ) ? $item['title'] : '' ); ?>
				</a>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if ( ! empty( $view_all['label'] ) ) : ?>
		<a href="<?php echo esc_url( adn_link( isset( $view_all['url'] ) ? $view_all['url'] : '' ) ); ?>" class="view-all-small">
			<?php echo esc_html( $view_all['label'] ); ?>
		</a>
	<?php endif; ?>
</div>
