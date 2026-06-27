<?php
/**
 * components/sections/home_banners_carousel.php
 *
 * Full-width promo banner slider - 16:9 ratio, crossfade transitions,
 * animated content, slide counter, prev/next, dots, progress bar, swipe.
 *
 * Props:
 *   $items    array  Active banners from AH_Banners_Helper::get_all(true)
 *   $autoplay int    Auto-slide ms (0 = off). Default 5000.
 */

defined( 'ABSPATH' ) || exit;

$items    = isset( $items )    && is_array( $items ) ? array_values( $items ) : array();
$autoplay = isset( $autoplay ) ? (int) $autoplay     : 5000;

if ( empty( $items ) ) { return; }

static $_uid = 0;
$_id    = 'bsl-' . ( ++$_uid );
$_count = 0;

/* Pre-count valid slides (image required) */
foreach ( $items as $b ) {
	if ( ! empty( $b['image'] ) ) { $_count++; }
}
if ( 0 === $_count ) { return; }
?>

<div class="bsl" id="<?php echo esc_attr( $_id ); ?>" data-autoplay="<?php echo (int) $autoplay; ?>">

	<?php
	$_real = 0;
	foreach ( $items as $b ) :
		$b       = (array) $b;
		$image   = isset( $b['image'] )        ? (string) $b['image']        : '';
		$image_m = isset( $b['image_mobile'] ) ? (string) $b['image_mobile'] : '';
		$sub     = isset( $b['subtitle'] )     ? (string) $b['subtitle']     : '';
		$title   = isset( $b['title'] )        ? $b['title']                 : '';
		$desc    = isset( $b['description'] )  ? (string) $b['description']  : '';
		$btn_t   = isset( $b['btn_text'] )     ? (string) $b['btn_text']     : '';
		$btn_u   = isset( $b['btn_url'] )      ? (string) $b['btn_url']      : '';
		$btn_tgt = ( isset( $b['btn_target'] ) && '_blank' === $b['btn_target'] ) ? '_blank' : '_self';
		$overlay = isset( $b['overlay'] )      ? (string) $b['overlay']      : 'rgba(10,25,47,0.42)';
		$align   = isset( $b['text_align'] )   ? (string) $b['text_align']   : 'left';
		$pos     = isset( $b['text_pos'] )     ? (string) $b['text_pos']     : 'middle';

		if ( '' === $image ) { continue; }

		/* Detect media type from URL extension */
		$_ext      = strtolower( pathinfo( (string) wp_parse_url( $image, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
		$_is_video = in_array( $_ext, array( 'mp4', 'webm', 'ogg', 'mov' ), true );
		$_is_gif   = ( 'gif' === $_ext );
		$_mime_map = array( 'mp4' => 'video/mp4', 'webm' => 'video/webm', 'ogg' => 'video/ogg', 'mov' => 'video/mp4' );
		$_mime     = $_is_video ? ( $_mime_map[ $_ext ] ?? 'video/mp4' ) : '';

		$_active = ( 0 === $_real );
	?>
	<div class="bsl__slide<?php echo $_active ? ' bsl__slide--active' : ''; ?>"
	     aria-hidden="<?php echo $_active ? 'false' : 'true'; ?>">

		<!-- Background media (image / gif / video) -->
		<div class="bsl__bg">

			<?php if ( $_is_video ) : ?>
			<video class="bsl__video" autoplay muted loop playsinline preload="<?php echo $_active ? 'auto' : 'none'; ?>">
				<source src="<?php echo esc_url( $image ); ?>" type="<?php echo esc_attr( $_mime ); ?>">
			</video>

			<?php elseif ( $image_m && ! $_is_gif ) : ?>
			<picture>
				<source media="(max-width:640px)" srcset="<?php echo esc_url( $image_m ); ?>">
				<img src="<?php echo esc_url( $image ); ?>"
				     alt="<?php echo esc_attr( wp_strip_all_tags( $title ) ); ?>"
				     loading="<?php echo $_active ? 'eager' : 'lazy'; ?>">
			</picture>

			<?php else : ?>
			<img src="<?php echo esc_url( $image ); ?>"
			     alt="<?php echo esc_attr( wp_strip_all_tags( $title ) ); ?>"
			     loading="<?php echo $_active ? 'eager' : 'lazy'; ?>">
			<?php endif; ?>

			<!-- Admin overlay colour -->
			<div class="bsl__overlay" style="background:<?php echo esc_attr( $overlay ); ?>;"></div>
			<!-- Fixed bottom-to-top gradient scrim -->
			<div class="bsl__scrim"></div>
		</div>

		<!-- Content -->
		<div class="bsl__content bsl__content--<?php echo esc_attr( $align ); ?> bsl__content--<?php echo esc_attr( $pos ); ?>">
			<div class="bsl__inner">

				<?php if ( '' !== $sub ) : ?>
				<span class="bsl__subtitle"><?php echo esc_html( $sub ); ?></span>
				<?php endif; ?>

				<?php if ( '' !== $title ) : ?>
				<h2 class="bsl__title"><?php echo wp_kses( $title, array(
					'br'     => array(),
					'em'     => array(),
					'strong' => array(),
					'span'   => array( 'class' => array() ),
				) ); ?></h2>
				<?php endif; ?>

				<?php if ( '' !== $desc ) : ?>
				<p class="bsl__desc"><?php echo esc_html( $desc ); ?></p>
				<?php endif; ?>

				<?php if ( '' !== $btn_t && '' !== $btn_u ) : ?>
				<div class="bsl__actions">
					<a class="bsl__btn bsl__btn--primary"
					   href="<?php echo esc_url( adn_link( $btn_u ) ); ?>"
					   target="<?php echo esc_attr( $btn_tgt ); ?>"
					   <?php if ( '_blank' === $btn_tgt ) echo 'rel="noopener noreferrer"'; ?>>
						<?php echo esc_html( $btn_t ); ?>
						<i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
					</a>
				</div>
				<?php endif; ?>

			</div>
		</div>

	</div>
	<?php
	$_real++;
	endforeach;
	?>

	<?php if ( $_count > 1 ) : ?>

	<!-- Bottom controls bar -->
	<div class="bsl__bar">

		<!-- Dot indicators -->
		<div class="bsl__dots" role="tablist">
			<?php for ( $d = 0; $d < $_count; $d++ ) : ?>
			<button class="bsl__dot<?php echo 0 === $d ? ' bsl__dot--active' : ''; ?>"
			        role="tab"
			        aria-selected="<?php echo 0 === $d ? 'true' : 'false'; ?>"
			        aria-label="<?php echo esc_attr( sprintf( __( 'Slide %d', ADN_TEXT_DOMAIN ), $d + 1 ) ); ?>"
			        data-idx="<?php echo (int) $d; ?>"></button>
			<?php endfor; ?>
		</div>

		<!-- Slide counter -->
		<div class="bsl__counter" aria-live="polite" aria-atomic="true">
			<span class="bsl__counter-cur">01</span>
			<span class="bsl__counter-sep">/</span>
			<span class="bsl__counter-total"><?php echo str_pad( $_count, 2, '0', STR_PAD_LEFT ); ?></span>
		</div>

	</div>

	<!-- Progress bar -->
	<div class="bsl__progress" aria-hidden="true">
		<div class="bsl__progress-bar"></div>
	</div>

	<?php endif; ?>

</div>

<script>
(function(){
	var el     = document.getElementById(<?php echo wp_json_encode( $_id ); ?>);
	var slides = el.querySelectorAll('.bsl__slide');
	var dots   = el.querySelectorAll('.bsl__dot');
	var bar    = el.querySelector('.bsl__progress-bar');
	var ccur   = el.querySelector('.bsl__counter-cur');
	var delay  = parseInt(el.dataset.autoplay, 10) || 0;
	var total  = slides.length;
	var cur    = 0;
	var timer  = null;

	if (total <= 1) return;

	function goTo(idx) {
		var prev = cur;
		cur = (idx + total) % total;
		if (cur === prev) return;

		slides[prev].classList.remove('bsl__slide--active');
		slides[prev].setAttribute('aria-hidden', 'true');
		if (dots[prev]) { dots[prev].classList.remove('bsl__dot--active'); dots[prev].setAttribute('aria-selected','false'); }

		slides[cur].classList.add('bsl__slide--active');
		slides[cur].setAttribute('aria-hidden', 'false');
		if (dots[cur])  { dots[cur].classList.add('bsl__dot--active');  dots[cur].setAttribute('aria-selected','true'); }
		if (ccur) ccur.textContent = String(cur + 1).padStart(2, '0');

		resetBar();
	}

	function resetBar() {
		if (!bar || !delay) return;
		bar.style.transition = 'none';
		bar.style.width = '0%';
		requestAnimationFrame(function(){
			requestAnimationFrame(function(){
				bar.style.transition = 'width ' + delay + 'ms linear';
				bar.style.width = '100%';
			});
		});
	}

	function startTimer() {
		clearInterval(timer);
		if (delay > 0) timer = setInterval(function(){ goTo(cur + 1); }, delay);
	}
	function stopTimer() {
		clearInterval(timer);
		if (bar) { bar.style.transition = 'none'; }
	}

	dots.forEach(function(d){ d.addEventListener('click', function(){ goTo(+d.dataset.idx); startTimer(); }); });

	el.addEventListener('mouseenter', stopTimer);
	el.addEventListener('mouseleave', startTimer);

	/* Touch swipe */
	var tsX = 0, tsY = 0;
	el.addEventListener('touchstart', function(e){ tsX = e.touches[0].clientX; tsY = e.touches[0].clientY; }, { passive: true });
	el.addEventListener('touchend', function(e){
		var dx = e.changedTouches[0].clientX - tsX;
		var dy = e.changedTouches[0].clientY - tsY;
		if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 40) {
			goTo(dx < 0 ? cur + 1 : cur - 1); startTimer();
		}
	}, { passive: true });

	startTimer();
	resetBar();
}());
</script>
