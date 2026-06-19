<?php
/**
 * components/parts/sidebar_news_mini.php - Sidebar: compact news list.
 *
 * Props: $news_mini { heading, items[] { title, date, url }, view_all { label, url } }
 */

defined( 'ABSPATH' ) || exit;

$news_mini = isset( $news_mini ) && is_array( $news_mini ) ? $news_mini : array();
$items     = isset( $news_mini['items'] )    ? (array) $news_mini['items']    : array();
$view_all  = isset( $news_mini['view_all'] ) ? (array) $news_mini['view_all'] : array();

if ( empty( $items ) ) { return; }

$_all_url   = ! empty( $view_all['url'] )   ? esc_url( adn_link( (string) $view_all['url'] ) )   : '';
$_all_label = ! empty( $view_all['label'] ) ? esc_html( (string) $view_all['label'] )             : '';
?>
<div class="sw-panel">
	<div class="sw-header">
		<h3 class="sw-title"><?php echo esc_html( ! empty( $news_mini['heading'] ) ? $news_mini['heading'] : ( defined( 'SITE_NEWS_NOUN' ) ? 'Latest ' . SITE_NEWS_NOUN : 'Latest News' ) ); ?></h3>
		<?php if ( '' !== $_all_url && '' !== $_all_label ) : ?>
			<a href="<?php echo $_all_url; ?>" class="sw-view-all"><?php echo $_all_label; ?></a>
		<?php endif; ?>
	</div>

	<ul class="sw-list" role="list">
		<?php foreach ( $items as $item ) :
			$_title = isset( $item['title'] ) ? (string) $item['title'] : '';
			$_date  = isset( $item['date'] )  ? (string) $item['date']  : '';
			$_url   = isset( $item['url'] )   ? esc_url( adn_link( (string) $item['url'] ) ) : '#';
			if ( '' === $_title ) { continue; }
		?>
		<li class="sw-item">
			<a href="<?php echo $_url; ?>" class="sw-item-link">
				<span class="sw-item-icon" aria-hidden="true">📰</span>
				<span class="sw-item-label">
					<?php echo esc_html( $_title ); ?>
					<?php if ( '' !== $_date ) : ?>
						<span class="sw-item-meta"><?php echo esc_html( $_date ); ?></span>
					<?php endif; ?>
				</span>
			</a>
		</li>
		<?php endforeach; ?>
	</ul>
</div>
