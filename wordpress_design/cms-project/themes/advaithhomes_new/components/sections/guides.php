<?php
/**
 * components/sections/guides.php — Section: Guides & Insights carousel
 *
 * Props: $items [ { icon, gradient, category, title, description, read_more, url } ]
 * Usage: adn_component( 'sections/guides', array( 'items' => $ctx['guides']['items'] ) );
 */

defined( 'ABSPATH' ) || exit;

$items = isset( $items ) ? (array) $items : array();
?>
<div class="guides-carousel">
    <?php foreach ( $items as $card ) : ?>
        <?php adn_component( 'cards/guide_card', array( 'card' => $card ) ); ?>
    <?php endforeach; ?>
</div>
