<?php
/**
 * components/cards/news_card.php - Grid news card (used inside news_section grid).
 *
 * Props: $item { cat_key, icon, bg_class, pill_class, category, title, excerpt, date, read_time, url }
 * Usage: adn_component( 'cards/news_card', array( 'item' => $item ) );
 */

defined( 'ABSPATH' ) || exit;

$item     = isset( $item ) && is_array( $item ) ? $item : array();
$cat_key  = isset( $item['cat_key'] )  ? sanitize_key( $item['cat_key'] )   : 'all';
$bg_class = isset( $item['bg_class'] ) && '' !== $item['bg_class'] ? ' news-card-img--' . sanitize_html_class( $item['bg_class'] ) : '';
$url      = esc_url( adn_link( isset( $item['url'] ) ? $item['url'] : '' ) );
?>
<div class="news-card" data-cat="<?php echo esc_attr( $cat_key ); ?>">
	<a href="<?php echo $url; ?>" class="news-card-img<?php echo $bg_class; ?>" tabindex="-1" aria-hidden="true">
		<?php
		$thumbnail = ! empty( $item['thumbnail'] ) ? (string) $item['thumbnail'] : '';
		if ( empty( $thumbnail ) ) {
			$thumbnail = get_template_directory_uri() . THEME_DEFAULT_NEWS_IMG . '?v=' . LOCAL_CACHE_VERSION;
		}
		?>
		<img src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php echo esc_attr( isset( $item['title'] ) ? $item['title'] : '' ); ?>" style="width:100%;height:100%;object-fit:cover;display:block;" onerror="this.onerror=null;this.src='<?php echo esc_url( get_template_directory_uri() . THEME_DEFAULT_NEWS_IMG . '?v=' . LOCAL_CACHE_VERSION ); ?>';" />
	</a>

	<div class="news-card-body">
		<?php if ( ! empty( $item['pill_class'] ) ) : ?>
			<span class="news-card-cat-pill <?php echo esc_attr( $item['pill_class'] ); ?>">
				<?php echo esc_html( isset( $item['category'] ) ? $item['category'] : '' ); ?>
			</span>
		<?php endif; ?>

		<?php if ( ! empty( $item['title'] ) ) : ?>
			<h3 class="news-card-title">
				<a href="<?php echo $url; ?>"><?php echo esc_html( $item['title'] ); ?></a>
			</h3>
		<?php endif; ?>

		<?php if ( ! empty( $item['excerpt'] ) ) : ?>
			<p class="news-card-excerpt"><?php echo esc_html( $item['excerpt'] ); ?></p>
		<?php endif; ?>
	</div>
</div>
