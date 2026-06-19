<?php
/**
 * components/parts/post_sidebar_news.php - Sidebar: Latest News list.
 *
 * Props: $latest_news[] { icon, title, date, url, thumbnail_url }
 */

defined( 'ABSPATH' ) || exit;

$_items   = ( isset( $latest_news ) && is_array( $latest_news ) ) ? $latest_news : array();
$_all_url = defined( 'SITE_NEWS_URL' ) ? esc_url( home_url( SITE_NEWS_URL ) ) : '';

if ( empty( $_items ) ) { return; }
?>
<div class="sw-panel">
	<div class="sw-header">
		<h3 class="sw-title"><?php echo esc_html( 'Latest ' . SITE_NEWS_NOUN ); ?></h3>
		<?php if ( '' !== $_all_url ) : ?>
			<a href="<?php echo $_all_url; ?>" class="sw-view-all"><?php esc_html_e( 'View all →', ADN_TEXT_DOMAIN ); ?></a>
		<?php endif; ?>
	</div>

	<ul class="sw-list" role="list">
		<?php foreach ( $_items as $_n ) :
			$_thumb = isset( $_n['thumbnail_url'] ) ? esc_url( (string) $_n['thumbnail_url'] ) : '';
			$_icon  = adn_icon( isset( $_n['icon'] ) ? (string) $_n['icon'] : '📰' );
			$_url   = isset( $_n['url'] ) ? esc_url( (string) $_n['url'] ) : '#';
		?>
		<li class="sw-item">
			<a href="<?php echo $_url; ?>" class="sw-item-link">
				<span class="sw-item-icon" aria-hidden="true">
					<?php if ( $_thumb ) : ?>
						<img src="<?php echo $_thumb; ?>" alt="" loading="lazy" style="width:22px;height:22px;object-fit:cover;border-radius:3px;">
					<?php else : ?>
						<?php echo $_icon; ?>
					<?php endif; ?>
				</span>
				<span class="sw-item-label">
					<?php echo esc_html( isset( $_n['title'] ) ? (string) $_n['title'] : '' ); ?>
					<?php if ( ! empty( $_n['date'] ) ) : ?>
						<span class="sw-item-meta"><?php echo esc_html( (string) $_n['date'] ); ?></span>
					<?php endif; ?>
				</span>
			</a>
		</li>
		<?php endforeach; ?>
	</ul>
</div>
