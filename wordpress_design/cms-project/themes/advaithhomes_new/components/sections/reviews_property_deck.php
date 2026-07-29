<?php
/**
 * components/sections/reviews_property_deck.php - "Property Deck" fanned
 * carousel for pages/PageReviews.php's 'property_deck' section.
 *
 * Props:
 *   $reviews array<object>  property_deck-type AH_Reviews_Model rows
 *   $heading string         Section heading text
 *
 * Left: a fanned stack of cards - the active one up front in a gold gradient,
 * neighbours peeking out grayscale on either side. Right: a detail panel
 * synced to the active card (location, headline, quote, name, result stat).
 * All N reviews render server-side (fan cards + detail panels); a small
 * script just toggles which are .is-active/.is-prev/.is-next/.is-hidden on
 * prev/next click - no content is written by JS, only classes, so nothing
 * here needs re-escaping client-side.
 *
 * Usage:
 *   adn_component( 'sections/reviews_property_deck', array(
 *       'reviews' => $property_deck_reviews,
 *       'heading' => $heading_text,
 *   ) );
 */

defined( 'ABSPATH' ) || exit;

$reviews = isset( $reviews ) && is_array( $reviews ) ? $reviews : array();
$heading = isset( $heading ) ? (string) $heading : '';

if ( empty( $reviews ) ) { return; }

static $_rvd_uid = 0;
$uid   = 'rvd-' . ( ++$_rvd_uid );
$total = count( $reviews );
?>

<?php adn_component( 'parts/section_headers/section_header', array(
    'heading' => array( 'title' => $heading, 'link_label' => '', 'link_url' => '' ),
    'tag'     => 'h2',
) ); ?>

<div class="reviews-property-deck" id="<?php echo esc_attr( $uid ); ?>">

    <div class="reviews-property-deck__fan">
        <?php foreach ( $reviews as $i => $_r ) :
            $_img   = ! empty( $_r->reviewer_image_id ) ? wp_get_attachment_image_url( (int) $_r->reviewer_image_id, 'thumbnail' ) : '';
            $_quote = wp_trim_words( wp_strip_all_tags( (string) $_r->review_text ), 10 );
            $_pos   = 0 === $i ? 'is-active' : ( 1 === $i ? 'is-next' : ( $i === $total - 1 ? 'is-prev' : 'is-hidden' ) );
        ?>
            <div class="reviews-property-deck__card <?php echo esc_attr( $_pos ); ?>" data-index="<?php echo (int) $i; ?>">
                <?php if ( $_img ) : ?>
                    <img class="reviews-property-deck__portrait" src="<?php echo esc_url( $_img ); ?>" alt="<?php echo esc_attr( $_r->reviewer_name ); ?>">
                <?php endif; ?>
                <p class="reviews-property-deck__snippet">&ldquo;<?php echo esc_html( $_quote ); ?>&rdquo;</p>
                <div class="reviews-property-deck__card-name"><?php echo esc_html( $_r->reviewer_name ); ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="reviews-property-deck__detail">
        <?php foreach ( $reviews as $i => $_r ) :
            $_title = ! empty( $_r->story_title ) ? (string) $_r->story_title : (string) $_r->reviewer_name;
        ?>
            <div class="reviews-property-deck__panel<?php echo 0 === $i ? ' is-active' : ''; ?>" data-index="<?php echo (int) $i; ?>">
                <?php if ( ! empty( $_r->division_category ) ) : ?>
                    <div class="reviews-property-deck__location">&mdash; <?php echo esc_html( strtoupper( $_r->division_category ) ); ?></div>
                <?php endif; ?>
                <h3 class="reviews-property-deck__title"><?php echo esc_html( $_title ); ?></h3>
                <p class="reviews-property-deck__quote">&ldquo;<?php echo esc_html( wp_strip_all_tags( (string) $_r->review_text ) ); ?>&rdquo;</p>
                <div class="reviews-property-deck__meta">
                    <div class="reviews-property-deck__name"><?php echo esc_html( $_r->reviewer_name ); ?></div>
                    <?php if ( ! empty( $_r->stat_line ) ) : ?>
                        <div class="reviews-property-deck__stat"><?php echo esc_html( $_r->stat_line ); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if ( $total > 1 ) : ?>
            <div class="reviews-property-deck__nav">
                <button type="button" class="reviews-property-deck__btn reviews-property-deck__btn--prev" aria-label="<?php echo esc_attr__( 'Previous', ADN_TEXT_DOMAIN ); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <button type="button" class="reviews-property-deck__btn reviews-property-deck__btn--next" aria-label="<?php echo esc_attr__( 'Next', ADN_TEXT_DOMAIN ); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php if ( $total > 1 ) : ?>
<script>
(function () {
    var root = document.getElementById('<?php echo esc_js( $uid ); ?>');
    if (!root) return;
    var cards  = root.querySelectorAll('.reviews-property-deck__card');
    var panels = root.querySelectorAll('.reviews-property-deck__panel');
    var prev   = root.querySelector('.reviews-property-deck__btn--prev');
    var next   = root.querySelector('.reviews-property-deck__btn--next');
    var total  = cards.length;
    var idx    = 0;

    function show(i) {
        idx = (i + total) % total;
        cards.forEach(function (c) {
            var n    = parseInt(c.getAttribute('data-index'), 10);
            var diff = (n - idx + total) % total;
            c.classList.remove('is-active', 'is-prev', 'is-next', 'is-hidden');
            if (diff === 0) c.classList.add('is-active');
            else if (diff === 1) c.classList.add('is-next');
            else if (diff === total - 1) c.classList.add('is-prev');
            else c.classList.add('is-hidden');
        });
        panels.forEach(function (p) {
            p.classList.toggle('is-active', parseInt(p.getAttribute('data-index'), 10) === idx);
        });
    }
    prev.addEventListener('click', function () { show(idx - 1); });
    next.addEventListener('click', function () { show(idx + 1); });
})();
</script>
<?php endif; ?>
