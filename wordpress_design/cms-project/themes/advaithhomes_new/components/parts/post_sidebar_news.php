<?php
/**
 * components/parts/post_sidebar_news.php
 *
 * Sidebar - Latest News list (from WP_Query in post_logical.php).
 *
 * Props (via extract):
 *   $latest_news = [ { icon, title, date, url, thumbnail_url }, … ]
 */

defined( 'ABSPATH' ) || exit;

$_items = ( isset( $latest_news ) && is_array( $latest_news ) ) ? $latest_news : array();

if ( empty( $_items ) ) {
	return;
}
?>
<div class="sidebar-box mini_card_container_design">
	<h3><?php echo esc_html( 'Latest ' . SITE_NEWS_NOUN ); ?></h3>
	<ul class="sidebar-news-list" role="list">
		<?php foreach ( $_items as $_n ) :
			$_n_icon  = adn_icon( isset( $_n['icon'] )  ? (string) $_n['icon']  : '📰' );
			$_n_ttl   = esc_html( isset( $_n['title'] ) ? (string) $_n['title'] : '' );
			$_n_date  = esc_html( isset( $_n['date'] )  ? (string) $_n['date']  : '' );
			$_n_url   = isset( $_n['url'] ) ? esc_url( (string) $_n['url'] ) : '#';
			$_n_thumb = isset( $_n['thumbnail_url'] ) ? esc_url( (string) $_n['thumbnail_url'] ) : '';
		?>
			<li>
				<a href="<?php echo $_n_url; ?>" class="sidebar-news-item">
					<span class="sidebar-news-img" aria-hidden="true">
						<?php if ( $_n_thumb ) : ?>
							<img src="<?php echo $_n_thumb; ?>" alt="" loading="lazy">
						<?php else : ?>
							<?php echo $_n_icon; ?>
						<?php endif; ?>
					</span>
					<span class="sidebar-news-text">
						<span class="sidebar-news-title"><?php echo $_n_ttl; ?></span>
					</span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>

