<?php
/**
 * components/parts/sidebar_toc.php - Sidebar sticky table of contents.
 *
 * Props: $toc { sidebar_title, items[] { num, label, target } }
 *   target maps to an id="" anchor in the article body for scroll-spy.
 *   First item is marked active by default; JS updates it on scroll.
 *
 * Usage: adn_component( 'parts/sidebar_toc', array( 'toc' => $ctx['toc'] ) );
 */

defined( 'ABSPATH' ) || exit;

$toc   = isset( $toc ) && is_array( $toc ) ? $toc : array();
$items = isset( $toc['items'] ) ? (array) $toc['items'] : array();
if ( empty( $items ) ) {
	return;
}
?>
<div class="article-toc">
	<?php if ( ! empty( $toc['sidebar_title'] ) ) : ?>
		<div class="toc-title"><?php echo esc_html( $toc['sidebar_title'] ); ?></div>
	<?php endif; ?>

	<div class="toc-list">
		<?php foreach ( $items as $i => $item ) : ?>
			<div class="toc-item<?php echo 0 === $i ? ' active' : ''; ?>"
			     data-target="<?php echo esc_attr( isset( $item['target'] ) ? $item['target'] : '' ); ?>">
				<span class="toc-num"><?php echo esc_html( isset( $item['num'] ) ? $item['num'] . '.' : '' ); ?></span>
				<?php echo esc_html( isset( $item['label'] ) ? $item['label'] : '' ); ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
