<?php
/**
 * components/parts/sidebar_browse_cats.php — Browse by Category link list.
 *
 * Mirrors the top category tabs. JS syncs both when a category is selected.
 *
 * Props: $categories[] { key, label, count }
 * Usage: adn_component( 'parts/sidebar_browse_cats', array( 'categories' => $ctx['categories'] ) );
 */

defined( 'ABSPATH' ) || exit;

$categories = isset( $categories ) && is_array( $categories ) ? $categories : array();

if ( empty( $categories ) ) {
	return;
}
?>
<div class="news-sb-box">
	<div class="news-sb-title"><?php echo esc_html__( 'Browse by Category', ADN_TEXT_DOMAIN ); ?></div>
	<ul class="sb-cat-list">
		<?php foreach ( $categories as $cat ) :
			$key   = isset( $cat['key'] )   ? (string) $cat['key']   : '';
			$label = isset( $cat['label'] ) ? (string) $cat['label'] : '';
			$count = isset( $cat['count'] ) ? (int)    $cat['count'] : 0;
		?>
			<li class="sb-cat-item<?php echo 'all' === $key ? ' active' : ''; ?>">
				<button class="sb-cat-btn" data-cat="<?php echo esc_attr( $key ); ?>">
					<span class="sb-cat-label"><?php echo esc_html( $label ); ?></span>
					<?php if ( $count > 0 ) : ?>
						<span class="sb-cat-count"><?php echo esc_html( (string) $count ); ?></span>
					<?php endif; ?>
				</button>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
