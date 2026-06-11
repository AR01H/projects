<?php
/**
 * components/sections/calculators.php — Section: Popular Calculators grid
 *
 * Props: $items [ { icon, name, url } ]
 * Usage: adn_component( 'sections/calculators', array( 'items' => $ctx['calculators']['items'] ) );
 */

defined( 'ABSPATH' ) || exit;

$items = isset( $items ) ? (array) $items : array();
?>
<div class="calc-grid">
    <?php foreach ( $items as $card ) : ?>
        <?php adn_component( 'cards/calc_card', array( 'card' => $card ) ); ?>
    <?php endforeach; ?>
</div>
