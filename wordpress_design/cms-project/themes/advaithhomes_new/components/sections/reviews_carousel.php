<?php
/**
 * components/sections/reviews_carousel.php - Luxury "Client Reviews & Stories" carousel.
 *
 * Props:
 *   $reviews array {
 *     items: object[],
 *     heading?: string,
 *     eyebrow?: string,
 *     subheading?: string
 *   }
 *
 * Usage:
 *   adn_component( 'sections/reviews_carousel', array(
 *       'reviews' => array(
 *           'items'      => $_reviews,
 *           'heading'    => 'What Genuine Homebuyers Say',
 *           'eyebrow'    => 'Real Homebuyer Stories',
 *           'subheading' => 'Read authentic experiences from buyers who secured their UK homes with our dedicated guidance.',
 *       ),
 *   ) );
 */
defined( 'ABSPATH' ) || exit;

$reviews = isset( $reviews ) && is_array( $reviews ) ? $reviews : array();
$items   = ( isset( $reviews['items'] ) && is_array( $reviews['items'] ) ) ? $reviews['items'] : array();
$heading = isset( $reviews['heading'] ) && '' !== $reviews['heading']
	? (string) $reviews['heading']
	: ( defined( 'PAGE_TITLE_REVIEWS' ) ? PAGE_TITLE_REVIEWS : 'What Genuine Homebuyers Say' );
$eyebrow = isset( $reviews['eyebrow'] ) && '' !== $reviews['eyebrow']
	? (string) $reviews['eyebrow']
	: 'Real Homebuyer Stories';
$subheading = isset( $reviews['subheading'] ) && '' !== $reviews['subheading']
	? (string) $reviews['subheading']
	: 'Read authentic experiences from buyers who found and secured their UK property with our independent guidance.';

if ( empty( $items ) || ! class_exists( 'AH_Reviews_Model' ) ) { return; }

static $_rvc_uid = 0;
$uid = 'rvc-' . ( ++$_rvc_uid );
?>

<div class="rvc-container-wrap">
	<?php /* Ambient animated background glows */ ?>
	<div class="rvc-bg" aria-hidden="true">
		<span class="rvc-orb rvc-orb--1"></span>
		<span class="rvc-orb rvc-orb--2"></span>
	</div>

	<?php adn_component( 'parts/section_headers/eyebrow_heading', array(
		'eyebrow'       => $eyebrow,
		'heading'       => $heading,
		'subheading'    => $subheading,
		'wrapper_class' => 'rvc-header',
	) ); ?>



	<div class="rvc-carousel" id="<?php echo esc_attr( $uid ); ?>">

		<button class="rvc-carousel__btn rvc-carousel__btn--prev" aria-label="<?php echo esc_attr__( 'Previous', ADN_TEXT_DOMAIN ); ?>">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
				<polyline points="15 18 9 12 15 6"/>
			</svg>
		</button>

		<div class="rvc-carousel__track">
			<?php foreach ( $items as $_review ) : ?>
				<?php echo AH_Reviews_Model::render_carousel_card( $_review ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endforeach; ?>
		</div><!-- /.rvc-carousel__track -->

		<button class="rvc-carousel__btn rvc-carousel__btn--next" aria-label="<?php echo esc_attr__( 'Next', ADN_TEXT_DOMAIN ); ?>">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
				<polyline points="9 18 15 12 9 6"/>
			</svg>
		</button>

		<div class="rvc-carousel__dots"></div>

	</div><!-- /.rvc-carousel -->
</div>

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
		dotsW.classList.toggle( 'is-available', scrollable && total > 1 );
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
