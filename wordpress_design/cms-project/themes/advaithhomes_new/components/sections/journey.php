<?php
/**
 * components/sections/journey.php - Section: "Where are you in your journey?" cards
 *
 * Props: $cards [ { icon, gradient, title, description, link_label, url } ]
 * Usage: adn_component( 'sections/journey', array( 'cards' => $ctx['journey']['cards'] ) );
 */

defined( 'ABSPATH' ) || exit;

$cards = isset( $cards ) ? (array) $cards : array();
?>
<div class="journey-cards">
    <?php foreach ( $cards as $card ) : ?>
        <?php adn_component( 'cards/journey_card', array( 'card' => $card ) ); ?>
    <?php endforeach; ?>
</div>
