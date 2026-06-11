<?php
/**
 * components/cards/journey_card.php — Component: Journey Card
 * Props: $card { icon, gradient, title, description, link_label, url }
 */

defined( 'ABSPATH' ) || exit;

$card = isset( $card ) && is_array( $card ) ? $card : array();
?>
<a href="<?php echo esc_url( adn_link( isset( $card['url'] ) ? $card['url'] : '' ) ); ?>" class="journey-card">
    <div class="journey-card-img" style="background:<?php echo esc_attr( isset( $card['gradient'] ) ? $card['gradient'] : '' ); ?>;"><?php echo esc_html( isset( $card['icon'] ) ? $card['icon'] : '' ); ?></div>
    <h3><?php echo esc_html( isset( $card['title'] ) ? $card['title'] : '' ); ?></h3>
    <p><?php echo esc_html( isset( $card['description'] ) ? $card['description'] : '' ); ?></p>
    <div class="journey-card-link"><?php echo esc_html( isset( $card['link_label'] ) ? $card['link_label'] : '' ); ?></div>
</a>
