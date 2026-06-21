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
		$_card['icon'] = ! empty( $_it['icon'] ) ? (string) $_it['icon'] : '📰';
	}
	$news_cards[] = $_card;
}

$reg_cards = array();
foreach ( isset( $regulations['items'] ) ? (array) $regulations['items'] : array() as $_it ) {
	$_rthumb = isset( $_it['thumbnail'] ) ? (string) $_it['thumbnail'] : '';
	$_rcard  = array(
		'title' => isset( $_it['title'] ) ? (string) $_it['title'] : '',
		'meta'  => isset( $_it['date'] )  ? (string) $_it['date']  : '',
		'url'   => isset( $_it['url'] )   ? (string) $_it['url']   : '',
	);
	if ( '' !== $_rthumb ) {
		$_rcard['img_url'] = $_rthumb;
		// overlay: 'overlay' field first, then join badge_lines (legacy)
		$_roverlay = isset( $_it['overlay'] ) ? (string) $_it['overlay'] : '';
		if ( '' === $_roverlay && ! empty( $_it['badge_lines'] ) ) {
			$_roverlay = implode( ' ', (array) $_it['badge_lines'] );
		}
		if ( '' !== $_roverlay ) { $_rcard['overlay'] = $_roverlay; }
	} else {
		$_rcard['icon'] = ! empty( $_it['icon'] ) ? (string) $_it['icon'] : '📋';
		// show overlay text as tag badge when no thumbnail
		$_roverlay = isset( $_it['overlay'] ) ? (string) $_it['overlay'] : '';
		if ( '' === $_roverlay && ! empty( $_it['badge_lines'] ) ) {
			$_roverlay = implode( ' ', (array) $_it['badge_lines'] );
		}
		if ( '' !== $_roverlay ) { $_rcard['tag'] = $_roverlay; }
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
<div class="ntc-carousel-wrap">

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

	<div class="ntc-nav">
		<button class="ntc-arrow ntc-arrow--prev" aria-label="<?php esc_attr_e( 'Previous', ADN_TEXT_DOMAIN ); ?>">
			<i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
		</button>
		<div class="ntc-dots"></div>
		<button class="ntc-arrow ntc-arrow--next" aria-label="<?php esc_attr_e( 'Next', ADN_TEXT_DOMAIN ); ?>">
			<i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
		</button>
	</div>

</div>

<script>
(function(){
	var wrap      = document.currentScript.previousElementSibling;
	var track     = wrap.querySelector('.news-three-inner');
	var prev      = wrap.querySelector('.ntc-arrow--prev');
	var next      = wrap.querySelector('.ntc-arrow--next');
	var dotsEl    = wrap.querySelector('.ntc-dots');
	var panels    = Array.prototype.slice.call( track.children );
	var panelH    = [];
	var scrollTmr;

	function isMobile(){ return window.innerWidth <= 680; }

	var dots = panels.map(function(_, i){
		var d = document.createElement('span');
		d.className = 'ntc-dot' + (i === 0 ? ' active' : '');
		d.addEventListener('click', function(){
			track.scrollTo({ left: panels[i].offsetLeft - track.offsetLeft, behavior: 'smooth' });
		});
		dotsEl.appendChild(d);
		return d;
	});

	function activeIndex(){
		var mid = track.scrollLeft + track.clientWidth / 2;
		var best = 0, bestD = Infinity;
		panels.forEach(function(p, i){
			var d = Math.abs((p.offsetLeft - track.offsetLeft + p.offsetWidth / 2) - mid);
			if(d < bestD){ bestD = d; best = i; }
		});
		return best;
	}

	function measureHeights(){
		panelH = panels.map(function(p){ return p.offsetHeight; });
	}

	function syncHeight(){
		if( !isMobile() ){ track.style.height = ''; return; }
		var h = panelH[ activeIndex() ];
		if( h ) track.style.height = h + 'px';
	}

	function update(){
		var idx = activeIndex();
		dots.forEach(function(d,i){ d.classList.toggle('active', i === idx); });
		prev.classList.toggle('ntc-arrow--hidden', track.scrollLeft <= 2);
		next.classList.toggle('ntc-arrow--hidden', track.scrollLeft >= track.scrollWidth - track.clientWidth - 2);
	}

	function onScroll(){
		update();
		clearTimeout(scrollTmr);
		scrollTmr = setTimeout(syncHeight, 120);
	}

	prev.addEventListener('click', function(){ track.scrollBy({ left: -(track.clientWidth + 16), behavior: 'smooth' }); });
	next.addEventListener('click', function(){ track.scrollBy({ left:  (track.clientWidth + 16), behavior: 'smooth' }); });
	track.addEventListener('scroll', onScroll, { passive: true });
	window.addEventListener('resize', function(){ measureHeights(); syncHeight(); update(); });

	if( document.readyState === 'complete' ){
		measureHeights(); syncHeight(); update();
	} else {
		window.addEventListener('load', function(){ measureHeights(); syncHeight(); update(); });
	}
}());
</script>
