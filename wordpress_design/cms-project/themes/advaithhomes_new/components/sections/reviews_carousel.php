<?php
/**
 * components/sections/reviews_carousel.php - Home page "Client Reviews" carousel.
 *
 * Props:
 *   $reviews array { items: object[], heading: string }
 *
 * Track + prev/next arrows + dot indicators, mirroring the existing
 * .res-carousel pattern (components/sections/category_resources.php) so this
 * behaves consistently with the site's other carousels. Each slide is
 * AH_Reviews_Model::render_carousel_card() - a fixed design (4-line clamped
 * text) used uniformly here regardless of each review's own admin-selected
 * `representing` layout (that field only affects the [ah_review] shortcode
 * and the /reviews/ listing page).
 *
 * Usage: adn_component( 'sections/reviews_carousel', array( 'reviews' => $ctx['reviews'] ?? array() ) );
 */

defined( 'ABSPATH' ) || exit;

$reviews = isset( $reviews ) && is_array( $reviews ) ? $reviews : array();
$items   = ( isset( $reviews['items'] ) && is_array( $reviews['items'] ) ) ? $reviews['items'] : array();
$heading = isset( $reviews['heading'] ) && '' !== $reviews['heading']
	? (string) $reviews['heading']
	: ( defined( 'PAGE_TITLE_REVIEWS' ) ? PAGE_TITLE_REVIEWS : 'What Our Clients Say' );

if ( empty( $items ) || ! class_exists( 'AH_Reviews_Model' ) ) { return; }

static $_rvc_uid = 0;
$uid = 'rvc-' . ( ++$_rvc_uid );
?>

<?php adn_component( 'parts/section_headers/section_header', array(
	'heading' => array( 'title' => $heading, 'link_label' => '', 'link_url' => '' ),
	'tag'     => 'h2',
) ); ?>

<div class="rvc-carousel" id="<?php echo esc_attr( $uid ); ?>">

	<button class="rvc-carousel__btn rvc-carousel__btn--prev" aria-label="<?php echo esc_attr__( 'Previous', ADN_TEXT_DOMAIN ); ?>">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
			<polyline points="15 18 9 12 15 6"/>
		</svg>
	</button>

	<div class="rvc-carousel__track">
		<?php foreach ( $items as $_review ) : ?>
			<?php echo AH_Reviews_Model::render_carousel_card( $_review ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_carousel_card() already escapes every field internally. ?>
		<?php endforeach; ?>
	</div><!-- /.rvc-carousel__track -->

	<button class="rvc-carousel__btn rvc-carousel__btn--next" aria-label="<?php echo esc_attr__( 'Next', ADN_TEXT_DOMAIN ); ?>">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
			<polyline points="9 18 15 12 9 6"/>
		</svg>
	</button>

	<div class="rvc-carousel__dots"></div>

</div><!-- /.rvc-carousel -->

<script>
(function(){
	var car   = document.getElementById('<?php echo esc_js( $uid ); ?>');
	if (!car) return;
	var track = car.querySelector('.rvc-carousel__track');
	var prev  = car.querySelector('.rvc-carousel__btn--prev');
	var next  = car.querySelector('.rvc-carousel__btn--next');
	var dotsW = car.querySelector('.rvc-carousel__dots');
	var cards = track.querySelectorAll('.ah-review-card--carousel');
	var total = cards.length;

	var dots = [];
	for (var i = 0; i < total; i++) {
		var d = document.createElement('button');
		d.className = 'rvc-carousel__dot' + (i === 0 ? ' rvc-carousel__dot--active' : '');
		d.setAttribute('aria-label', 'Go to ' + (i + 1));
		(function (idx) { d.addEventListener('click', function () { scrollToCard(idx); }); })(i);
		dotsW.appendChild(d);
		dots.push(d);
	}

	function getCardWidth() {
		return cards[0] ? cards[0].offsetWidth + 20 : 320;
	}
	function scrollToCard(idx) {
		track.scrollTo({ left: idx * getCardWidth(), behavior: 'smooth' });
	}
	prev.addEventListener('click', function () {
		track.scrollBy({ left: -getCardWidth(), behavior: 'smooth' });
	});
	next.addEventListener('click', function () {
		track.scrollBy({ left: getCardWidth(), behavior: 'smooth' });
	});

	function isScrollable() { return track.scrollWidth > track.clientWidth + 4; }
	function atEnd() { return track.scrollLeft + track.clientWidth >= track.scrollWidth - 8; }

	function updateState() {
		var scrollable = isScrollable();
		prev.classList.toggle('rvc-hidden', !scrollable);
		next.classList.toggle('rvc-hidden', !scrollable);
		dotsW.style.display = ( scrollable && total > 1 ) ? 'flex' : 'none';
		if (!scrollable) return;
		prev.disabled = track.scrollLeft < 2;
		next.disabled = atEnd();
		var idx = Math.round(track.scrollLeft / getCardWidth());
		dots.forEach(function (d, i) { d.className = 'rvc-carousel__dot' + (i === idx ? ' rvc-carousel__dot--active' : ''); });
	}
	track.addEventListener('scroll', updateState, { passive: true });
	window.addEventListener('resize', updateState);
	updateState();
})();
</script>
