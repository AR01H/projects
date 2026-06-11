<?php
/**
 * components/parts/post_sidebar_news.php
 *
 * Sidebar — Latest Property News list (from WP_Query in post_logical.php).
 *
 * Props (via extract):
 *   $latest_news = [ { icon, title, date, url }, … ]
 */

defined( 'ABSPATH' ) || exit;

$_items = ( isset( $latest_news ) && is_array( $latest_news ) ) ? $latest_news : array();

if ( empty( $_items ) ) {
	return;
}
?>
<div class="sidebar-box">
	<h3><?php esc_html_e( 'Latest Property News', ADN_TEXT_DOMAIN ); ?></h3>
	<ul class="sidebar-news-list" role="list">
		<?php foreach ( $_items as $_n ) :
			$_n_icon = esc_html( isset( $_n['icon'] )  ? (string) $_n['icon']  : '📰' );
			$_n_ttl  = esc_html( isset( $_n['title'] ) ? (string) $_n['title'] : '' );
			$_n_date = esc_html( isset( $_n['date'] )  ? (string) $_n['date']  : '' );
			$_n_url  = isset( $_n['url'] ) ? esc_url( (string) $_n['url'] ) : '#';
		?>
			<li>
				<a href="<?php echo $_n_url; ?>" class="sidebar-news-item">
					<span class="sidebar-news-img" aria-hidden="true"><?php echo $_n_icon; ?></span>
					<span class="sidebar-news-text">
						<span class="sidebar-news-title"><?php echo $_n_ttl; ?></span>
						<?php if ( '' !== $_n_date ) : ?>
							<span class="sidebar-news-date">📅 <?php echo $_n_date; ?></span>
						<?php endif; ?>
					</span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
