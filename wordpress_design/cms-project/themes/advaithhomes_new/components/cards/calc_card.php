<?php
/**
 * components/cards/calc_card.php - Component: Calculator Card
 * Props: $card { icon, name, url }
 */

defined( 'ABSPATH' ) || exit;

$card = isset( $card ) && is_array( $card ) ? $card : array();
?>
<a href="<?php echo esc_url( adn_link( isset( $card['url'] ) ? $card['url'] : '' ) ); ?>" class="calc-card">
    <div class="calc-card-icon"><?php echo adn_icon( isset( $card['icon'] ) ? $card['icon'] : '' ); ?></div>
    <div class="calc-card-name"><?php echo esc_html( isset( $card['name'] ) ? $card['name'] : '' ); ?></div>
</a>
