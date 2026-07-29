<?php
/**
 * components/sections/reviews_spotlight_slider.php - "Client Spotlights"
 * one-at-a-time slider for pages/PageReviews.php.
 *
 * Props:
 *   $reviews array<object>  Spotlight-type AH_Reviews_Model rows (already
 *                            filtered + ordered by the caller)
 *   $heading string         Section heading text
 *
 * Spotlight is already a wide editorial layout (big quote + portrait) -
 * stacking several full-width felt like a wall of near-identical blocks, so
 * this shows exactly one at a time with prev/next arrows + dots underneath.
 * Falls back to a single plain card with no controls when there's only one.
 *
 * Usage:
 *   adn_component( 'sections/reviews_spotlight_slider', array(
 *       'reviews' => $spotlight_reviews,
 *       'heading' => $heading_text,
 *   ) );
 */

defined( 'ABSPATH' ) || exit;

$reviews = isset( $reviews ) && is_array( $reviews ) ? $reviews : array();
$heading = isset( $heading ) ? (string) $heading : '';

if ( empty( $reviews ) || ! class_exists( 'AH_Reviews_Model' ) ) { return; }

static $_rvs_uid = 0;
$uid   = 'rvs-' . ( ++$_rvs_uid );
$total = count( $reviews );
?>

<?php adn_component( 'parts/section_headers/section_header', array(
    'heading' => array( 'title' => $heading, 'link_label' => '', 'link_url' => '' ),
    'tag'     => 'h2',
) ); ?>

<div class="reviews-spotlight-slider" id="<?php echo esc_attr( $uid ); ?>">
    <div class="reviews-spotlight-slider__track">
        <?php foreach ( $reviews as $i => $_review ) : ?>
            <div class="reviews-spotlight-slider__slide<?php echo 0 === $i ? ' is-active' : ''; ?>">
                <?php echo AH_Reviews_Model::render_review( $_review ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_review() already escapes every field internally. ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ( $total > 1 ) : ?>
        <div class="reviews-spotlight-slider__nav">
            <button type="button" class="reviews-spotlight-slider__btn reviews-spotlight-slider__btn--prev" aria-label="<?php echo esc_attr__( 'Previous', ADN_TEXT_DOMAIN ); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <div class="reviews-spotlight-slider__dots">
                <?php for ( $i = 0; $i < $total; $i++ ) : ?>
                    <button type="button" class="reviews-spotlight-slider__dot<?php echo 0 === $i ? ' is-active' : ''; ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: slide number */ __( 'Go to spotlight %d', ADN_TEXT_DOMAIN ), $i + 1 ) ); ?>"></button>
                <?php endfor; ?>
            </div>
            <button type="button" class="reviews-spotlight-slider__btn reviews-spotlight-slider__btn--next" aria-label="<?php echo esc_attr__( 'Next', ADN_TEXT_DOMAIN ); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>
    <?php endif; ?>
</div>

<?php if ( $total > 1 ) : ?>
<script>
(function () {
    var root = document.getElementById('<?php echo esc_js( $uid ); ?>');
    if (!root) return;
    var slides = root.querySelectorAll('.reviews-spotlight-slider__slide');
    var dots   = root.querySelectorAll('.reviews-spotlight-slider__dot');
    var prev   = root.querySelector('.reviews-spotlight-slider__btn--prev');
    var next   = root.querySelector('.reviews-spotlight-slider__btn--next');
    var total  = slides.length;
    var idx    = 0;

    function show(i) {
        idx = (i + total) % total;
        slides.forEach(function (s, n) { s.classList.toggle('is-active', n === idx); });
        dots.forEach(function (d, n) { d.classList.toggle('is-active', n === idx); });
    }
    prev.addEventListener('click', function () { show(idx - 1); });
    next.addEventListener('click', function () { show(idx + 1); });
    dots.forEach(function (d, n) { d.addEventListener('click', function () { show(n); }); });
})();
</script>
<?php endif; ?>
