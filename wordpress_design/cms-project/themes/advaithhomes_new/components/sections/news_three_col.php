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
$news2       = isset( $news2 )       && is_array( $news2 )       ? $news2       : array();
$regulations = isset( $regulations ) && is_array( $regulations ) ? $regulations : array();
$hot_topics  = isset( $hot_topics )  && is_array( $hot_topics )  ? $hot_topics  : array();

/* ── Remap each column's items to mini_card props ── */

$news_cards = array();
foreach ( isset( $news['items'] ) ? (array) $news['items'] : array() as $_it ) {
	$_thumb = isset( $_it['thumbnail'] ) ? (string) $_it['thumbnail'] : '';
	$_card  = array(
		'title'         => isset( $_it['title'] ) ? (string) $_it['title'] : '',
		'meta'          => isset( $_it['date'] )  ? (string) $_it['date']  : '',
		'meta_full'     => isset( $_it['date_full'] ) ? (string) $_it['date_full'] : '',
		'tag'           => isset( $_it['tag'] )   ? (string) $_it['tag']   : '',
		'url'           => isset( $_it['url'] )   ? (string) $_it['url']   : '',
		'description'   => isset( $_it['description'] ) ? (string) $_it['description'] : '',
		'date_on_image' => true, // Latest News only - other columns keep the date in the card body.
	);
	if ( '' === $_thumb ) {
		$_thumb = get_template_directory_uri() . THEME_DEFAULT_GENERIC_IMG . '?v=' . LOCAL_CACHE_VERSION;
	}
	$_card['img_url'] = $_thumb;
	$news_cards[] = $_card;
}

$news2_cards = array();
foreach ( isset( $news2['items'] ) ? (array) $news2['items'] : array() as $_it ) {
	$_thumb = isset( $_it['thumbnail'] ) ? (string) $_it['thumbnail'] : '';
	$_card  = array(
		'title'       => isset( $_it['title'] ) ? (string) $_it['title'] : '',
		'meta'        => isset( $_it['date'] )  ? (string) $_it['date']  : '',
		'tag'         => isset( $_it['tag'] )   ? (string) $_it['tag']   : '',
		'url'         => isset( $_it['url'] )   ? (string) $_it['url']   : '',
		'description' => isset( $_it['description'] ) ? (string) $_it['description'] : '',
	);
	if ( '' === $_thumb ) {
		$_thumb = get_template_directory_uri() . THEME_DEFAULT_GENERIC_IMG . '?v=' . LOCAL_CACHE_VERSION;
	}
	$_card['img_url'] = $_thumb;
	$news2_cards[] = $_card;
}

$reg_cards = array();
foreach ( isset( $regulations['items'] ) ? (array) $regulations['items'] : array() as $_it ) {
	$_rthumb = isset( $_it['thumbnail'] ) ? (string) $_it['thumbnail'] : '';
	$_rcard  = array(
		'title' => isset( $_it['title'] ) ? (string) $_it['title'] : '',
		'meta'  => isset( $_it['date'] )  ? (string) $_it['date']  : '',
		'url'   => isset( $_it['url'] )   ? (string) $_it['url']   : '',
	);
	
	// Determine overlay tag
	$_roverlay = isset( $_it['overlay'] ) ? (string) $_it['overlay'] : '';
	if ( '' === $_roverlay && ! empty( $_it['badge_lines'] ) ) {
		$_roverlay = implode( ' ', (array) $_it['badge_lines'] );
	}
	if ( '' !== $_roverlay ) { $_rcard['tag'] = $_roverlay; }
	
	if ( '' === $_rthumb ) {
		$_rthumb = get_template_directory_uri() . THEME_DEFAULT_GENERIC_IMG . '?v=' . LOCAL_CACHE_VERSION;
	}
	$_rcard['img_url'] = $_rthumb;
	
	$reg_cards[] = $_rcard;
}

$topic_cards = array();
foreach ( isset( $hot_topics['items'] ) ? (array) $hot_topics['items'] : array() as $_it ) {
	$_tthumb = isset( $_it['thumbnail'] ) ? (string) $_it['thumbnail'] : '';
	$_tcard  = array(
		'title' => isset( $_it['text'] ) ? (string) $_it['text'] : '',
		'url'   => isset( $_it['url'] )  ? (string) $_it['url']  : '',
		'icon'  => isset( $_it['icon'] ) ? (string) $_it['icon'] : '',
	);
	
	if ( '' === $_tthumb ) {
		$_tthumb = get_template_directory_uri() . THEME_DEFAULT_TOPIC_IMG . '?v=' . LOCAL_CACHE_VERSION;
	}
	$_tcard['img_url'] = $_tthumb;
	
	$topic_cards[] = $_tcard;
}

$hot_cta = isset( $hot_topics['cta'] ) && is_array( $hot_topics['cta'] ) ? $hot_topics['cta'] : array();

$num_cols = ( ! empty( $news_cards ) ? 1 : 0 ) + ( ! empty( $news2_cards ) ? 1 : 0 ) + ( ! empty( $reg_cards ) ? 1 : 0 ) + ( ! empty( $topic_cards ) ? 1 : 0 );
$has_2fr_class = '';
if ( $num_cols > 1 && ( ( isset( $is_home_news ) && $is_home_news ) || ! empty( $topic_cards ) || ! empty( $news2_cards ) ) ) {
	$has_2fr_class = 'news-three-col--has-2fr';
}
?>
<div class="ntc-carousel-wrap">

	<div class="news-three-inner <?php echo $has_2fr_class; ?>">

		<?php if ( ! empty( $news_cards ) ) :
		// Use hero card layout when: explicitly flagged home news, OR when hot_topics column is present.
		$_use_hero = ( isset( $is_home_news ) && $is_home_news ) || ! empty( $topic_cards );
		?>
		<div class="news-col news-col--news <?php echo $_use_hero ? 'news-col--news-2fr' : ''; ?> mini_card_container_design">
			<div class="news-widget">
				<?php
				$widget_type = $_use_hero ? 'parts/news_list_widget' : 'parts/list_widget';
				adn_component( $widget_type, array( 'widget' => array(
					'heading' => isset( $news['heading'] ) ? (array) $news['heading'] : array(),
					'items'   => $news_cards,
					'tag'     => 'h4',
				) ) );
				?>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( ! empty( $news2_cards ) ) :
		$_use_hero = ( isset( $is_home_news ) && $is_home_news ) || ! empty( $topic_cards );
		?>
		<div class="news-col news-col--news <?php echo $_use_hero ? 'news-col--news-2fr' : ''; ?> mini_card_container_design">
			<div class="news-widget">
				<?php
				$widget_type = $_use_hero ? 'parts/news_list_widget' : 'parts/list_widget';
				adn_component( $widget_type, array( 'widget' => array(
					'heading' => isset( $news2['heading'] ) ? (array) $news2['heading'] : array(),
					'items'   => $news2_cards,
					'tag'     => 'h4',
				) ) );
				?>
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
		<div class="news-col hot-topics-col mini_card_container_design">
			<?php adn_component( 'parts/hot_topics_widget', array( 'widget' => array(
				'heading' => isset( $hot_topics['title'] ) ? (string) $hot_topics['title'] : '',
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
	/* ── Clamp a scrollable list to exactly 5 items height ── */
	function clampList( list ) {
		list.style.maxHeight = '';
		var items = list.children;
		if ( items.length <= 5 ) { return; }
		var cs  = getComputedStyle( list );
		var gap = parseFloat( cs.gap || cs.rowGap ) || 0;
		var h   = 0;
		for ( var i = 0; i < 5; i++ ) {
			h += items[ i ].getBoundingClientRect().height;
			if ( i < 4 ) { h += gap; }
		}
		list.style.maxHeight = Math.ceil( h ) + 'px';
	}

	function applyFourItemHeight( wrap ) {
		/* News / regulation / topics panels */
		wrap.querySelectorAll( '.list-widget-items' ).forEach( clampList );
		/* Spotlight panel — find sibling .news-sp-row__spotlight in same section */
		var section = wrap.closest( '.news-three-col, section' );
		if ( section ) {
			section.querySelectorAll( '.spotlight-items' ).forEach( clampList );
		}
	}

	function initCarousel(wrap) {
		if (wrap.classList.contains('ntc-initialized')) return;
		wrap.classList.add('ntc-initialized');

		/* Run after full layout (fonts + images settled) */
		if ( document.readyState === 'complete' ) {
			applyFourItemHeight( wrap );
		} else {
			window.addEventListener( 'load', function () { applyFourItemHeight( wrap ); } );
		}
		window.addEventListener( 'resize', function () { applyFourItemHeight( wrap ); } );

		var track  = wrap.querySelector('.news-three-inner');
		if (!track) return;
		
		var prev   = wrap.querySelector('.ntc-arrow--prev');
		var next   = wrap.querySelector('.ntc-arrow--next');
		var dotsEl = wrap.querySelector('.ntc-dots');
		var panels = Array.prototype.slice.call( track.children );

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

		function equalizeHeights(){
			/* Reset so we measure natural heights */
			panels.forEach(function(p){ p.style.height = ''; });
			if( window.innerWidth > 680 ){ return; } /* desktop: natural heights */
			var maxH = 0;
			panels.forEach(function(p){ if( p.offsetHeight > maxH ) maxH = p.offsetHeight; });
			panels.forEach(function(p){ p.style.height = maxH + 'px'; });
		}

		function update(){
			var idx = activeIndex();
			dots.forEach(function(d,i){ d.classList.toggle('active', i === idx); });
			prev.classList.toggle('ntc-arrow--hidden', track.scrollLeft <= 2);
			next.classList.toggle('ntc-arrow--hidden', track.scrollLeft >= track.scrollWidth - track.clientWidth - 2);
		}

		prev.addEventListener('click', function(){ track.scrollBy({ left: -(track.clientWidth + 16), behavior: 'smooth' }); });
		next.addEventListener('click', function(){ track.scrollBy({ left:  (track.clientWidth + 16), behavior: 'smooth' }); });
		track.addEventListener('scroll', update, { passive: true });
		window.addEventListener('resize', function(){ equalizeHeights(); update(); });

		if( document.readyState === 'complete' ){
			equalizeHeights(); update();
		} else {
			window.addEventListener('load', function(){ equalizeHeights(); update(); });
		}
	}

	document.querySelectorAll('.ntc-carousel-wrap').forEach(initCarousel);
}());
</script>
