<?php
/**
 * components/sections/category_resources.php
 *
 * Props:
 *   $resources array { items: object[], heading: string }
 */

defined( 'ABSPATH' ) || exit;

$resources = isset( $resources ) && is_array( $resources ) ? $resources : array();
$items     = ( isset( $resources['items'] ) && is_array( $resources['items'] ) ) ? $resources['items'] : array();
$heading   = isset( $resources['heading'] ) && '' !== $resources['heading']
	? (string) $resources['heading']
	: ( defined( 'SITE_LABEL_USEFUL_RESOURCES' ) ? SITE_LABEL_USEFUL_RESOURCES : 'Useful Resources' );

if ( empty( $items ) || ! class_exists( 'AH_Resources_Model' ) ) { return; }

$type_labels = AH_Resources_Model::type_labels();
static $_res_carousel_uid = 0;
$uid = 'res-car-' . ( ++$_res_carousel_uid );
?>

<?php adn_component( 'parts/section_headers/section_header', array(
	'heading' => array( 'title' => $heading, 'link_label' => '', 'link_url' => '' ),
	'tag'     => 'h2',
) ); ?>

<div class="res-carousel" id="<?php echo esc_attr( $uid ); ?>">

	<button class="res-carousel__btn res-carousel__btn--prev" aria-label="Previous">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
			<polyline points="15 18 9 12 15 6"/>
		</svg>
	</button>

	<div class="res-carousel__track">
	<?php foreach ( $items as $r ) :
		$type      = isset( $r->type ) ? (string) $r->type : 'embed';
		$title     = isset( $r->title ) ? esc_html( $r->title ) : '';
		$desc      = isset( $r->description ) ? esc_html( $r->description ) : '';
		$url       = isset( $r->url ) ? (string) $r->url : '';
		$embed     = isset( $r->embed_code ) ? (string) $r->embed_code : '';
		$thumb     = isset( $r->thumbnail_url ) ? (string) $r->thumbnail_url : '';
		$type_lbl  = $type_labels[ $type ] ?? ucfirst( $type );
		$is_video  = in_array( $type, array( 'youtube', 'shorts' ), true );
		$is_social = in_array( $type, array( 'instagram', 'facebook', 'twitter', 'tiktok' ), true );
		$vid_id    = $is_video ? AH_Resources_Model::youtube_id( $url ) : '';
		$yt_thumb  = $vid_id ? 'https://img.youtube.com/vi/' . $vid_id . '/hqdefault.jpg' : '';
		$card_thumb = $thumb ?: $yt_thumb;

		$link_url        = isset( $r->link_url ) ? (string) $r->link_url : '';
		$highlight_label = isset( $r->highlight_label ) ? trim( (string) $r->highlight_label ) : '';
		$icon_map = array( 'instagram' => '📸', 'facebook' => '📘', 'twitter' => '🐦', 'tiktok' => '🎵', 'audio' => '🎧', 'pdf' => '📄', 'embed' => '🔗' );
	?>
	<div class="res-card-lib res-card-lib--<?php echo esc_attr( $type ); ?>">

		<?php /* ── Media ── */ ?>
		<?php if ( $is_video && $vid_id ) : ?>
		<div class="res-card-lib__media">
			<img src="<?php echo esc_url( $card_thumb ); ?>" alt="<?php echo $title; ?>" loading="lazy">
						<button class="res-card-lib__play" aria-label="Play"
				data-vid="<?php echo esc_attr( $vid_id ); ?>"
				data-type="<?php echo esc_attr( $type ); ?>">
				<svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="40" cy="40" r="38" fill="rgba(255,255,255,.92)"/>
					<polygon points="32,24 60,40 32,56" fill="#203c3e"/>
				</svg>
			</button>
		</div>

		<?php elseif ( $is_social ) : ?>
		<div class="res-card-lib__media">
			<?php if ( $card_thumb ) : ?>
				<img src="<?php echo esc_url( $card_thumb ); ?>" alt="<?php echo $title; ?>" loading="lazy">
			<?php else : ?>
				<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:44px;background:var(--slate-100);">
					<?php echo esc_html( $icon_map[ $type ] ?? '🔗' ); ?>
				</div>
			<?php endif; ?>
					</div>

		<?php elseif ( 'image' === $type && $url ) : ?>
		<div class="res-card-lib__media">
			<img src="<?php echo esc_url( $url ); ?>" alt="<?php echo $title; ?>" loading="lazy">
					</div>

		<?php elseif ( 'audio' === $type ) : ?>
		<div class="res-card-lib__media res-card-lib__media--placeholder">
			<div class="res-card-lib__icon-placeholder">🎧</div>
					</div>

		<?php elseif ( 'pdf' === $type ) : ?>
		<div class="res-card-lib__media res-card-lib__media--placeholder">
			<div class="res-card-lib__icon-placeholder">📄</div>
					</div>

		<?php elseif ( 'embed' === $type ) : ?>
		<?php if ( $card_thumb ) : ?>
		<div class="res-card-lib__media">
			<img src="<?php echo esc_url( $card_thumb ); ?>" alt="<?php echo $title; ?>" loading="lazy">
						<?php if ( $url || $link_url ) : ?>
			<div class="res-card-lib__link-overlay" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="28" height="28">
					<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
					<path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
				</svg>
			</div>
			<?php endif; ?>
		</div>
		<?php elseif ( $embed ) : ?>
		<?php
		$_embed_allowed = array(
			'iframe'     => array( 'src' => true, 'width' => true, 'height' => true, 'frameborder' => true, 'allowfullscreen' => true, 'allow' => true, 'loading' => true, 'style' => true, 'scrolling' => true, 'title' => true ),
			'blockquote' => array( 'class' => true, 'data-instgrm-permalink' => true, 'data-instgrm-version' => true, 'style' => true ),
			'script'     => array( 'async' => true, 'src' => true, 'charset' => true ),
		);
		?>
		<div class="res-card-lib__media res-card-lib__media--iframe">
			<?php echo wp_kses( $embed, array_merge( wp_kses_allowed_html( 'post' ), $_embed_allowed ) ); ?>
					</div>
		<?php elseif ( $url ) : ?>
		<div class="res-card-lib__media res-card-lib__media--iframe">
			<iframe src="<?php echo esc_url( $url ); ?>"
				loading="lazy"
				sandbox="allow-scripts allow-same-origin allow-popups allow-forms"
				allow="fullscreen"></iframe>
		</div>
		<?php else : ?>
		<div class="res-card-lib__media res-card-lib__media--placeholder">
			<div class="res-card-lib__icon-placeholder">🔗</div>
					</div>
		<?php endif; ?>
		<?php endif; ?>

		<?php /* ── Audio player ── */ ?>
		<?php if ( 'audio' === $type && $url ) : ?>
		<div class="res-card-lib__audio">
			<audio controls style="width:100%;"><source src="<?php echo esc_url( $url ); ?>"></audio>
		</div>
		<?php endif; ?>

		<?php if ( $highlight_label ) : ?><span class="res-card-lib__hl"><?php echo esc_html( $highlight_label ); ?></span><?php endif; ?>

		<?php /* ── Body — only render if there is content to show ── */ ?>
		<?php $ext_href = $link_url ?: ( $is_social ? $url : '' ); ?>
		<?php if ( $title || $ext_href || $link_url ) : ?>
		<div class="res-card-lib__body">
			<div class="res-card-lib__row">
				<?php if ( $title ) : ?><p class="res-card-lib__title"><?php echo $title; ?></p><?php endif; ?>
				<?php if ( $ext_href ) : ?>
				<a href="<?php echo esc_url( $ext_href ); ?>" target="_blank" rel="noopener noreferrer" class="res-card-lib__extbtn" aria-label="Open link">
					<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="13" height="13">
						<path d="M6 3H3a1 1 0 0 0-1 1v9a1 1 0 0 0 1 1h9a1 1 0 0 0 1-1v-3"/>
						<path d="M10 2h4v4M14 2 8 8"/>
					</svg>
				</a>
				<?php endif; ?>
			</div>
		</div>
		<?php endif; ?>

		<?php /* ── PDF download ── */ ?>
		<?php if ( 'pdf' === $type && $url ) : ?>
		<div class="res-card-lib__dl">
			<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer" download>⬇ Download PDF</a>
		</div>
		<?php endif; ?>

	</div>
	<?php endforeach; ?>
	</div><!-- /.res-carousel__track -->

	<button class="res-carousel__btn res-carousel__btn--next" aria-label="Next">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
			<polyline points="9 18 15 12 9 6"/>
		</svg>
	</button>

	<div class="res-carousel__dots"></div>

</div><!-- /.res-carousel -->

<script>
(function(){
	var car   = document.getElementById('<?php echo esc_js( $uid ); ?>');
	if (!car) return;
	var track = car.querySelector('.res-carousel__track');
	var prev  = car.querySelector('.res-carousel__btn--prev');
	var next  = car.querySelector('.res-carousel__btn--next');
	var dotsW = car.querySelector('.res-carousel__dots');
	var cards = track.querySelectorAll('.res-card-lib');
	var total = cards.length;

	/* dots */
	var dots = [];
	for (var i = 0; i < total; i++) {
		var d = document.createElement('button');
		d.className = 'res-carousel__dot' + (i === 0 ? ' res-carousel__dot--active' : '');
		d.setAttribute('aria-label', 'Go to ' + (i+1));
		(function(idx){ d.addEventListener('click', function(){ scrollTo(idx); }); })(i);
		dotsW.appendChild(d);
		dots.push(d);
	}
	if (total <= 1) { dotsW.style.display = 'none'; }

	function getCardWidth() {
		return cards[0] ? cards[0].offsetWidth + 20 : 320;
	}

	function scrollTo(idx) {
		track.scrollTo({ left: idx * getCardWidth(), behavior: 'smooth' });
	}

	prev.addEventListener('click', function(){
		track.scrollBy({ left: -getCardWidth(), behavior: 'smooth' });
	});
	next.addEventListener('click', function(){
		track.scrollBy({ left: getCardWidth(), behavior: 'smooth' });
	});

	function isScrollable() {
		return track.scrollWidth > track.clientWidth + 4;
	}

	function atEnd() {
		return track.scrollLeft + track.clientWidth >= track.scrollWidth - 8;
	}

	function updateState() {
		var scrollable = isScrollable();
		prev.classList.toggle('res-hidden', !scrollable);
		next.classList.toggle('res-hidden', !scrollable);
		dotsW.style.display = scrollable ? '' : 'none';
		if (!scrollable) return;
		prev.disabled = track.scrollLeft < 2;
		next.disabled = atEnd();
		var idx = Math.round(track.scrollLeft / getCardWidth());
		dots.forEach(function(d,i){ d.className = 'res-carousel__dot' + (i===idx ? ' res-carousel__dot--active' : ''); });
	}
	track.addEventListener('scroll', updateState, { passive: true });
	window.addEventListener('resize', updateState);
	updateState();

	/* play button click → swap thumbnail for iframe, hide the title overlay while it plays */
	car.querySelectorAll('.res-card-lib__play').forEach(function(btn){
		btn.addEventListener('click', function(){
			var vid  = this.dataset.vid;
			var type = this.dataset.type;
			var media = this.closest('.res-card-lib__media');
			var card  = this.closest('.res-card-lib');
			var src = 'https://www.youtube.com/embed/' + vid + '?autoplay=1&rel=0&modestbranding=1';
			if (type === 'shorts') src = 'https://www.youtube.com/embed/' + vid + '?autoplay=1';
			media.innerHTML = '<iframe src="' + src + '" allow="autoplay;encrypted-media;picture-in-picture" allowfullscreen loading="lazy"></iframe>';
			if (card) { card.classList.add('is-playing'); }
		});
	});
})();
</script>
