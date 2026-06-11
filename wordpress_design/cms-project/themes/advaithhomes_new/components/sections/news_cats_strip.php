<?php
/**
 * components/sections/news_cats_strip.php — Sticky category tabs strip.
 *
 * Props: $categories[] { key, label, count }
 * The "all" tab is active by default; JS (news.js) handles switching.
 * Usage: adn_component( 'sections/news_cats_strip', array( 'categories' => $ctx['categories'] ) );
 */

defined( 'ABSPATH' ) || exit;

$categories = isset( $categories ) && is_array( $categories ) ? $categories : array();
?>
<div class="news-cats-strip" id="newsCatsStrip">
	<div class="news-cats-inner">
		<?php foreach ( $categories as $cat ) :
			$key   = isset( $cat['key'] )   ? (string) $cat['key']   : '';
			$label = isset( $cat['label'] ) ? (string) $cat['label'] : '';
			$count = isset( $cat['count'] ) ? (int)    $cat['count'] : 0;
		?>
			<button
				class="news-cat-tab<?php echo 'all' === $key ? ' active' : ''; ?>"
				data-cat="<?php echo esc_attr( $key ); ?>"
				aria-pressed="<?php echo 'all' === $key ? 'true' : 'false'; ?>"
			>
				<?php echo esc_html( $label ); ?>
				<?php if ( $count > 0 ) : ?>
					<span class="news-cat-count"><?php echo esc_html( (string) $count ); ?></span>
				<?php endif; ?>
			</button>
		<?php endforeach; ?>
	</div>
</div>
