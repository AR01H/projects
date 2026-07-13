<?php
/**
 * components/parts/featured_in.php - "As featured in:" logo marquee strip.
 *
 * Usage:
 *   adn_component( 'parts/featured_in' );
 *     → shows first section
 *
 *   adn_component( 'parts/featured_in', array( 'section' => 'press' ) );
 *     → shows the section whose ID is "press"
 *
 * Sections are managed in CMS ADMIN → Featured In.
 */
defined( 'ABSPATH' ) || exit;

$_section_id = isset( $props['section'] ) ? sanitize_key( $props['section'] ) : '';

$_raw  = get_option( 'ah_featured_in_sections', '' );
$_all  = $_raw ? json_decode( $_raw, true ) : array();
$_data = null;

if ( is_array( $_all ) && ! empty( $_all ) ) {
	if ( '' !== $_section_id ) {
		foreach ( $_all as $_s ) {
			if ( isset( $_s['id'] ) && $_s['id'] === $_section_id ) {
				$_data = $_s;
				break;
			}
		}
	}
}

if ( empty( $_data['logos'] ) ) {
	return;
}

$_heading = ( isset( $_data['heading'] ) && '' !== $_data['heading'] )
	? esc_html( $_data['heading'] )
	: 'As featured in';

$_logos = (array) $_data['logos'];
?>
<section class="fi-section">
	<div class="fi-inner">
		<span class="fi-label"><?php echo $_heading; ?></span>
		<div class="fi-track-wrap" aria-hidden="true">
			<div class="fi-track">
				<?php
				/* Render 4× - animates -25% per loop so it works even with few logos */
				for ( $_pass = 0; $_pass < 4; $_pass++ ) :
					foreach ( $_logos as $_logo ) :
						$_img  = isset( $_logo['image_url'] ) ? esc_url( $_logo['image_url'] ) : '';
						$_href = ( isset( $_logo['link'] ) && '' !== $_logo['link'] ) ? esc_url( $_logo['link'] ) : '';
						$_alt  = isset( $_logo['label'] ) ? esc_attr( $_logo['label'] ) : '';
						if ( '' === $_img ) { continue; }
						?>
						<div class="fi-logo">
							<?php if ( '' !== $_href ) : ?>
								<a href="<?php echo $_href; ?>" target="_blank" rel="noopener noreferrer" tabindex="-1">
									<img src="<?php echo $_img; ?>" alt="<?php echo $_alt; ?>" loading="eager">
								</a>
							<?php else : ?>
								<img src="<?php echo $_img; ?>" alt="<?php echo $_alt; ?>" loading="eager">
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				<?php endfor; ?>
			</div>
		</div>
	</div>
</section>
<script>
(function(){
	var section = document.currentScript.previousElementSibling;
	var track   = section ? section.querySelector('.fi-track') : null;
	if (!track) return;

	/* Start paused so the animation does not begin until we've measured */
	track.style.animationPlayState = 'paused';

	function measureAndStart() {
		var logos   = track.querySelectorAll('.fi-logo');
		var total   = logos.length;
		if (!total) { track.style.animationPlayState = 'running'; return; }

		/* 4 PHP passes rendered — measure first N logos = one pass */
		var passLen = Math.round(total / 4);
		var w = 0;
		for (var i = 0; i < passLen; i++) {
			var st = window.getComputedStyle(logos[i]);
			w += logos[i].getBoundingClientRect().width
			   + parseFloat(st.marginRight || 0)
			   + parseFloat(st.marginLeft  || 0);
		}

		if (w > 0) {
			track.style.setProperty('--fi-scroll-x', '-' + Math.round(w) + 'px');
		}

		/* Unpause only after the property is set — animation starts seamlessly */
		track.style.animationPlayState = '';
	}

	/* Use requestAnimationFrame so the browser has finished layout/paint */
	requestAnimationFrame(function() {
		requestAnimationFrame(measureAndStart);
	});

	/* Re-measure on resize (debounced) */
	var _rt;
	window.addEventListener('resize', function() {
		clearTimeout(_rt);
		_rt = setTimeout(function() {
			track.style.animationPlayState = 'paused';
			requestAnimationFrame(function() {
				requestAnimationFrame(measureAndStart);
			});
		}, 120);
	});
}());
</script>
