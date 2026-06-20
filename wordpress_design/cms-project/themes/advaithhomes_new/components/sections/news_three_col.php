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
	$_thumb = isset( $_it['thumbnail'] ) ? (string) $_it['thumbnail'] : '';
	$_card  = array(
		'title' => isset( $_it['title'] ) ? (string) $_it['title'] : '',
		'meta'  => isset( $_it['date'] )  ? (string) $_it['date']  : '',
		'tag'   => isset( $_it['tag'] )   ? (string) $_it['tag']   : '',
		'url'   => isset( $_it['url'] )   ? (string) $_it['url']   : '',
	);
	if ( '' !== $_thumb ) {
		$_card['img_url'] = $_thumb;
	} else {
		$_card['icon'] = '📰';
	}
	$news_cards[] = $_card;
}

$reg_cards = array();
foreach ( isset( $regulations['items'] ) ? (array) $regulations['items'] : array() as $_it ) {
	$_rthumb  = isset( $_it['thumbnail'] )   ? (string) $_it['thumbnail'] : '';
	$_rbadges = isset( $_it['badge_lines'] ) ? (array) $_it['badge_lines'] : array();
	$_rcard   = array(
		'title' => isset( $_it['title'] ) ? (string) $_it['title'] : '',
		'url'   => isset( $_it['url'] )   ? (string) $_it['url']   : '',
	);
	if ( '' !== $_rthumb ) {
		$_rcard['img_url'] = $_rthumb;
		if ( ! empty( $_rbadges ) ) {
			$_rcard['overlay'] = implode( ' ', $_rbadges );
		}
	} else {
		$_rcard['icon'] = '📋';
	}
	$reg_cards[] = $_rcard;
}

$topic_cards = array();
foreach ( isset( $hot_topics['items'] ) ? (array) $hot_topics['items'] : array() as $_it ) {
	$_tthumb = isset( $_it['thumbnail'] ) ? (string) $_it['thumbnail'] : '';
	$_tcard  = array(
		'title' => isset( $_it['text'] ) ? (string) $_it['text'] : '',
		'url'   => isset( $_it['url'] )  ? (string) $_it['url']  : '',
	);
	if ( '' !== $_tthumb ) {
		$_tcard['img_url'] = $_tthumb;
	} else {
		$_raw_icon      = isset( $_it['icon'] ) ? trim( (string) $_it['icon'] ) : '';
		$_tcard['icon'] = '' !== $_raw_icon ? $_raw_icon : '🔥';
	}
	$topic_cards[] = $_tcard;
}

$hot_cta = isset( $hot_topics['cta'] ) && is_array( $hot_topics['cta'] ) ? $hot_topics['cta'] : array();
?>
<div class="news-three-inner">

	<?php if ( ! empty( $news_cards ) ) : ?>
	<div class="news-col news-col--news mini_card_container_design">
		<div class="news-widget">
			<?php adn_component( 'parts/list_widget', array( 'widget' => array(
				'heading' => isset( $news['heading'] ) ? (array) $news['heading'] : array(),
				'items'   => $news_cards,
				'tag'     => 'h4',
			) ) ); ?>
		</div>
	</div>
	<?php endif; ?>

	<?php if ( ! empty( $reg_cards ) ) : ?>
	<div class="news-col news-col--regulations mini_card_container_design">
		<?php adn_component( 'parts/list_widget', array( 'widget' => array(
			'heading' => array( 'title' => isset( $regulations['heading']['title'] ) ? (string) $regulations['heading']['title'] : '' ),
			'items'   => $reg_cards,
			'tag'     => 'h4',
		) ) ); ?>
	</div>
	<?php endif; ?>

	<?php if ( ! empty( $topic_cards ) ) : ?>
	<div class="hot-topics-col mini_card_container_design">
		<?php adn_component( 'parts/list_widget', array( 'widget' => array(
			'heading' => array( 'title' => isset( $hot_topics['title'] ) ? (string) $hot_topics['title'] : '' ),
			'items'   => $topic_cards,
			'cta'     => $hot_cta,
			'tag'     => 'h4',
		) ) ); ?>
	</div>
	<?php endif; ?>

</div>
