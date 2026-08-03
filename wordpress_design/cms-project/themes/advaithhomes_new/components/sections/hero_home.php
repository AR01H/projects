<?php
/**
 * components/sections/hero_home.php - Section: Home Hero
 *
 * Props: $hero { title_lines[], description, actions[], trust_items[], diagram, slides[] }
 *   title_lines : [ { text, accent(bool) } ]   rendered with <br> between lines
 *   actions     : [ { label, url, style: primary|outline } ]
 *   diagram     : { center_icon, center_lines[], nodes: [ { icon, label } ] }
 *   slides      : [ { image, media, media_mobile } ]  (optional, carousel when 2+)
 *
 * Usage: adn_component( 'sections/hero_home', array( 'hero' => $ctx['hero'] ) );
 */

defined( 'ABSPATH' ) || exit;

$hero = isset( $hero ) && is_array( $hero ) ? $hero : array();

$title_lines = isset( $hero['title_lines'] ) ? (array) $hero['title_lines'] : array();
$actions     = isset( $hero['actions'] ) ? (array) $hero['actions'] : array();
$trust_items = isset( $hero['trust_items'] ) ? (array) $hero['trust_items'] : array();
$diagram     = isset( $hero['diagram'] ) ? (array) $hero['diagram'] : array();
$nodes       = isset( $diagram['nodes'] ) ? (array) $diagram['nodes'] : array();
$_default_img = get_template_directory_uri() . THEME_DEFAULT_HERO_IMG;

// Carousel mode: 2+ slides from home_banner + home_banner_2
$_slides      = isset( $hero['slides'] ) && is_array( $hero['slides'] ) ? $hero['slides'] : array();
$_is_carousel = count( $_slides ) > 1;

if ( $_is_carousel ) :
	$_uid = 'hero-carousel-' . wp_unique_id();
	$_slide_count = count( $_slides );
	$_autoplay = 6000;
	?>
	<div class="hero-carousel" id="<?php echo esc_attr( $_uid ); ?>" data-autoplay="<?php echo (int) $_autoplay; ?>">
		<?php foreach ( $_slides as $_si => $_slide ) :
			$_s_img  = ! empty( $_slide['media']['url'] ) ? adn_versioned_url( $_slide['media']['url'] ) : ( get_the_post_thumbnail_url( get_the_ID(), 'large' ) ?: $_default_img );
			$_s_type = isset( $_slide['media']['type'] ) ? $_slide['media']['type'] : 'image';
			$_s_mobile_img  = '';
			$_s_mobile_type = 'image';
			if ( ! empty( $_slide['media_mobile']['url'] ) ) {
				$_s_mobile_img  = adn_versioned_url( $_slide['media_mobile']['url'] );
				$_s_mobile_type = isset( $_slide['media_mobile']['type'] ) ? $_slide['media_mobile']['type'] : 'image';
			}
			$_ext      = strtolower( pathinfo( (string) wp_parse_url( $_s_img, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
			$_is_video = in_array( $_ext, array( 'mp4', 'webm', 'ogg', 'mov' ), true );
			$_is_gif   = ( 'gif' === $_ext );
			$_mime_map = array( 'mp4' => 'video/mp4', 'webm' => 'video/webm', 'ogg' => 'video/ogg', 'mov' => 'video/mp4' );
			$_mime     = $_is_video ? ( $_mime_map[ $_ext ] ?? 'video/mp4' ) : '';
		?>
		<div class="hero-carousel__slide<?php echo 0 === $_si ? ' hero-carousel__slide--active' : ''; ?>"
		     data-index="<?php echo (int) $_si; ?>"
		     aria-hidden="<?php echo 0 === $_si ? 'false' : 'true'; ?>">
			<div class="hero-carousel__bg">
				<?php if ( $_is_video ) : ?>
					<video class="hero-carousel__video" <?php echo 0 === $_si ? 'autoplay' : ''; ?> muted loop playsinline preload="<?php echo 0 === $_si ? 'auto' : 'none'; ?>">
						<source src="<?php echo esc_url( $_s_img ); ?>" type="<?php echo esc_attr( $_mime ); ?>">
					</video>
				<?php elseif ( $_s_mobile_img && ! $_is_gif ) : ?>
					<picture>
						<source media="(max-width:640px)" srcset="<?php echo esc_url( $_s_mobile_img ); ?>">
						<img src="<?php echo esc_url( $_s_img ); ?>" alt="" loading="<?php echo 0 === $_si ? 'eager' : 'lazy'; ?>">
					</picture>
				<?php else : ?>
					<img src="<?php echo esc_url( $_s_img ); ?>" alt="" loading="<?php echo 0 === $_si ? 'eager' : 'lazy'; ?>">
				<?php endif; ?>
				<div class="hero-carousel__overlay"></div>
			</div>
		</div>
		<?php endforeach; ?>
		<div class="hero-carousel__dots" role="tablist">
			<?php for ( $_d = 0; $_d < $_slide_count; $_d++ ) : ?>
			<button class="hero-carousel__dot<?php echo 0 === $_d ? ' hero-carousel__dot--active' : ''; ?>"
			        role="tab"
			        aria-selected="<?php echo 0 === $_d ? 'true' : 'false'; ?>"
			        data-index="<?php echo (int) $_d; ?>"></button>
			<?php endfor; ?>
		</div>
	</div>
	<script>
	(function(){
		var el = document.getElementById(<?php echo wp_json_encode( $_uid ); ?>);
		if (!el) return;
		var slides = el.querySelectorAll('.hero-carousel__slide');
		var dots   = el.querySelectorAll('.hero-carousel__dot');
		var delay  = parseInt(el.dataset.autoplay, 10) || 0;
		var total  = slides.length;
		var cur    = 0;
		var timer  = null;
		function goTo(idx) {
			var prev = cur;
			cur = (idx + total) % total;
			if (cur === prev) return;
			slides[prev].classList.remove('hero-carousel__slide--active');
			slides[prev].setAttribute('aria-hidden', 'true');
			if (dots[prev]) { dots[prev].classList.remove('hero-carousel__dot--active'); }
			slides[cur].classList.add('hero-carousel__slide--active');
			slides[cur].setAttribute('aria-hidden', 'false');
			if (dots[cur]) { dots[cur].classList.add('hero-carousel__dot--active'); }

			/* Switching slides only toggles CSS visibility - a <video> on a
			   newly-active slide never got told to play (and the outgoing one
			   keeps decoding in the background), so drive playback manually. */
			var prevVideo = slides[prev].querySelector('.hero-carousel__video');
			if (prevVideo) { prevVideo.pause(); }
			var curVideo = slides[cur].querySelector('.hero-carousel__video');
			if (curVideo) { curVideo.currentTime = 0; curVideo.play().catch(function(){}); }
		}
		function startTimer() {
			clearInterval(timer);
			if (delay > 0) timer = setInterval(function(){ goTo(cur + 1); }, delay);
		}
		dots.forEach(function(d){ d.addEventListener('click', function(){ goTo(+d.dataset.index); startTimer(); }); });
		el.addEventListener('mouseenter', function(){ clearInterval(timer); });
		el.addEventListener('mouseleave', startTimer);
		startTimer();
	})();
	</script>
<?php else :
	// Single slide mode (original behavior)
	if ( ! empty( $hero['media']['url'] ) ) {
		$_hero_img  = $hero['media']['url'];
		$_hero_type = isset( $hero['media']['type'] ) ? $hero['media']['type'] : 'image';
	} else {
		$_hero_img  = get_the_post_thumbnail_url( get_the_ID(), 'large' ) ?: $_default_img;
		$_hero_type = 'image';
	}
	$_hero_img = adn_versioned_url( $_hero_img );

	$_mobile_img  = '';
	$_mobile_type = 'image';
	if ( ! empty( $hero['media_mobile']['url'] ) ) {
		$_mobile_img  = adn_versioned_url( $hero['media_mobile']['url'] );
		$_mobile_type = isset( $hero['media_mobile']['type'] ) ? $hero['media_mobile']['type'] : 'image';
	}
	?>
	<?php adn_component( 'sections/page_hero_bg_banner', array(
		'hero_img'    => $_hero_img,
		'media_type'  => $_hero_type,
		'mobile_img'  => $_mobile_img,
		'mobile_type' => $_mobile_type,
		'is_home'     => false,
	) ); ?>
<?php endif; ?>

<div class="hero-home-inner">
    <div class="hero-content">
        <h1 class="hero-title">
            <?php
            $line_count = count( $title_lines );
            foreach ( array_values( $title_lines ) as $i => $line ) {
                $text = isset( $line['text'] ) ? $line['text'] : '';
                if ( ! empty( $line['accent'] ) ) {
                    echo '<span class="accent">' . esc_html( $text ) . '</span>';
                } else if($i==2) {
                    echo '<span class="hero-sub-line">'.esc_html( $text ).'</span>';
                }else{
                    echo esc_html( $text );
                }
                if ( $i < $line_count - 1 ) {
                    // echo '<br>';
                }
            }
            ?>
        </h1>
        <p class="hero-desc"><?php echo esc_html( isset( $hero['description'] ) ? $hero['description'] : '' ); ?></p>
        <div class="hero-actions">
            <?php foreach ( $actions as $action ) :
                $style = isset( $action['style'] ) && 'outline' === $action['style'] ? 'btn-outline premium-btn-outline' : 'btn-primary premium-btn-dark';
                ?>
                <a href="<?php echo esc_url( adn_link( isset( $action['url'] ) ? $action['url'] : '' ) ); ?>"
                   class="btn <?php echo esc_attr( $style ); ?> btn-md"><?php echo esc_html( isset( $action['label'] ) ? $action['label'] : '' ); ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="hero-visual hero-visual--in-hero">
        <?php adn_component( 'sections/hero_home_diagram', array( 'diagram' => $diagram ) ); ?>
    </div>
</div>
