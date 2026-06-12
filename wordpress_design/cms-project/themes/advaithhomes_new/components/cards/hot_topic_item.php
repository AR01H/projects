<?php
/**
 * components/cards/hot_topic_item.php — Component: Hot Topic List Item
 * Props: $item { icon, text, url }
 */

defined( 'ABSPATH' ) || exit;

$item = isset( $item ) && is_array( $item ) ? $item : array();
?>
<div class="hot-topic-item">
    <span class="hot-topic-icon"><?php echo adn_icon( isset( $item['icon'] ) ? $item['icon'] : '' ); ?></span>
    <a href="<?php echo esc_url( adn_link( isset( $item['url'] ) ? $item['url'] : '' ) ); ?>" class="hot-topic-text"><?php echo esc_html( isset( $item['text'] ) ? $item['text'] : '' ); ?></a>
</div>
