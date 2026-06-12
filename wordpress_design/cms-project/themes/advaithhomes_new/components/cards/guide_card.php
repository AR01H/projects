<?php
/**
 * components/cards/guide_card.php — Component: Guide / Insight Card
 * Props: $card { icon, gradient, category, title, description, read_more, url }
 */

defined( 'ABSPATH' ) || exit;

$card = isset( $card ) && is_array( $card ) ? $card : array();
?>
<a href="<?php echo esc_url( adn_link( isset( $card['url'] ) ? $card['url'] : '' ) ); ?>" class="guide-card">
    <div class="guide-card-img" style="background:<?php echo esc_attr( isset( $card['gradient'] ) ? $card['gradient'] : '' ); ?>;"><?php echo adn_icon( isset( $card['icon'] ) ? $card['icon'] : '' ); ?></div>
    <div class="guide-card-body">
        <div class="guide-card-category"><?php echo esc_html( isset( $card['category'] ) ? $card['category'] : '' ); ?></div>
        <div class="guide-card-title"><?php echo esc_html( isset( $card['title'] ) ? $card['title'] : '' ); ?></div>
        <div class="guide-card-desc"><?php echo esc_html( isset( $card['description'] ) ? $card['description'] : '' ); ?></div>
        <div class="read-more"><?php echo esc_html( isset( $card['read_more'] ) ? $card['read_more'] : '' ); ?></div>
    </div>
</a>
