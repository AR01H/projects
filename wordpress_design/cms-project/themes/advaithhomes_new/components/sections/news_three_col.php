<?php
/**
 * components/sections/news_three_col.php - Section: News + Regulations + Hot Topics
 *
 * Props:
 *   $news        { heading, items[] { gradient, title, date, tag, url } }
 *   $regulations { heading, items[] { badge_lines[], title, url } }
 *   $hot_topics  { title, items[] { icon, text, desc, url }, cta { label, url } }
 *
 * All three columns rendered via parts/list_widget → cards/mini_card.
 */

defined( 'ABSPATH' ) || exit;

$news        = isset( $news )        && is_array( $news )        ? $news        : array();
$regulations = isset( $regulations ) && is_array( $regulations ) ? $regulations : array();
$hot_topics  = isset( $hot_topics )  && is_array( $hot_topics )  ? $hot_topics  : array();

/* ── Remap each column's items to mini_card props ── */

$news_cards = array();
foreach ( isset( $news['items'] ) ? (array) $news['items'] : array() as $_it ) {
	$news_cards[] = array(
		'img'   => isset( $_it['gradient'] ) ? (string) $_it['gradient'] : '',
		'title' => isset( $_it['title'] )    ? (string) $_it['title']    : '',
		'meta'  => isset( $_it['date'] )     ? (string) $_it['date']     : '',
		'tag'   => isset( $_it['tag'] )      ? (string) $_it['tag']      : '',
		'url'   => isset( $_it['url'] )      ? (string) $_it['url']      : '',
	);
}

$reg_cards = array();
foreach ( isset( $regulations['items'] ) ? (array) $regulations['items'] : array() as $_it ) {
	$reg_cards[] = array(
		'badge' => isset( $_it['badge_lines'] ) ? (array) $_it['badge_lines'] : array(),
		'title' => isset( $_it['title'] )       ? (string) $_it['title']      : '',
		'url'   => isset( $_it['url'] )         ? (string) $_it['url']        : '',
	);
}

$topic_cards = array();
foreach ( isset( $hot_topics['items'] ) ? (array) $hot_topics['items'] : array() as $_it ) {
	$topic_cards[] = array(
		'icon'  => isset( $_it['icon'] ) ? (string) $_it['icon'] : '',
		'title' => isset( $_it['text'] ) ? (string) $_it['text'] : '',
		'meta'  => isset( $_it['desc'] ) ? (string) $_it['desc'] : '',
		'url'   => isset( $_it['url'] )  ? (string) $_it['url']  : '',
	);
}

$hot_cta = isset( $hot_topics['cta'] ) && is_array( $hot_topics['cta'] ) ? $hot_topics['cta'] : array();
?>
<div class="news-three-inner">

	<div class="news-col news-col--news mini_card_container_design">
		<div class="news-widget">
			<?php adn_component( 'parts/list_widget', array( 'widget' => array(
				'heading' => isset( $news['heading'] ) ? (array) $news['heading'] : array(),
				'items'   => $news_cards,
				'tag'     => 'h4',
			) ) ); ?>
		</div>
	</div>

	<div class="news-col news-col--regulations mini_card_container_design">
		<?php adn_component( 'parts/list_widget', array( 'widget' => array(
			'heading' => isset( $regulations['heading'] ) ? (array) $regulations['heading'] : array(),
			'items'   => $reg_cards,
			'tag'     => 'h4',
		) ) ); ?>
	</div>

	<div class="hot-topics-col mini_card_container_design">
		<?php adn_component( 'parts/list_widget', array( 'widget' => array(
			'heading' => array( 'title' => isset( $hot_topics['title'] ) ? (string) $hot_topics['title'] : '' ),
			'items'   => $topic_cards,
			'cta'     => $hot_cta,
			'tag'     => 'h4',
		) ) ); ?>
	</div>

</div>
