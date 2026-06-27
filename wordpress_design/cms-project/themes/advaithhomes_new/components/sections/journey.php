<?php
/**
 * components/sections/journey.php - Section: "Where are you in your journey?" cards
 *
 * Props: $cards [ { icon, gradient, title, description, link_label, url } ]
 * Usage: adn_component( 'sections/journey', array( 'cards' => $ctx['journey']['cards'] ) );
 */

defined( 'ABSPATH' ) || exit;

$cards = isset( $cards ) ? (array) $cards : array();
static $_jny_uid = 0;
$_jny_id = 'journey-track-' . ( ++$_jny_uid );
?>
<div class="journey-carousel-wrap">
    <button class="jny-arrow jny-arrow--prev" aria-label="<?php esc_attr_e( 'Previous', ADN_TEXT_DOMAIN ); ?>" data-track="<?php echo esc_attr( $_jny_id ); ?>">
        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
    </button>

    <div class="journey-cards" id="<?php echo esc_attr( $_jny_id ); ?>">
        <?php foreach ( $cards as $_jny_i => $card ) : ?>
            <?php adn_component( 'cards/journey_card', array( 'card' => $card, 'num' => $_jny_i + 1 ) ); ?>
        <?php endforeach; ?>
    </div>

    <button class="jny-arrow jny-arrow--next" aria-label="<?php esc_attr_e( 'Next', ADN_TEXT_DOMAIN ); ?>" data-track="<?php echo esc_attr( $_jny_id ); ?>">
        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
    </button>
</div>

<script>
(function(){
    var wrap  = document.currentScript.previousElementSibling;
    var track = wrap.querySelector('.journey-cards');
    var prev  = wrap.querySelector('.jny-arrow--prev');
    var next  = wrap.querySelector('.jny-arrow--next');
    var cardW = 0;

    function getCardW() {
        var c = track.querySelector('.jny-card');
        return c ? c.offsetWidth + 16 : 280;
    }
    function update() {
        var atStart = track.scrollLeft <= 2;
        var atEnd   = track.scrollLeft >= track.scrollWidth - track.clientWidth - 2;
        prev.classList.toggle('jny-arrow--hidden', atStart);
        next.classList.toggle('jny-arrow--hidden', atEnd);
    }
    prev.addEventListener('click', function(){ track.scrollBy({ left: -getCardW(), behavior: 'smooth' }); });
    next.addEventListener('click', function(){ track.scrollBy({ left:  getCardW(), behavior: 'smooth' }); });
    track.addEventListener('scroll', update, { passive: true });
    update();
}());
</script>
