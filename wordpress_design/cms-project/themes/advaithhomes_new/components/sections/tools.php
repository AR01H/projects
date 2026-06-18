<?php
/**
 * components/sections/tools.php - Section: Popular Tools grid
 *
 * Props: $items [ { icon, name, url } ]
 * Usage: adn_component( 'sections/tools', array( 'items' => $ctx['calculators']['items'] ) );
 */

defined( 'ABSPATH' ) || exit;

$items = isset( $items ) ? (array) $items : array();
?>
<div class="tool-grid">
    <?php foreach ( $items as $card ) : ?>
        <?php adn_component( 'cards/tool_card', array( 'card' => $card ) ); ?>
    <?php endforeach; ?>
</div>
