<?php
/**
 * components/cards/news_item.php — Component: News List Item
 * Props: $item { title, date, tag, gradient, url }
 */

defined( 'ABSPATH' ) || exit;

$item = isset( $item ) && is_array( $item ) ? $item : array();
?>
<a href="<?php echo esc_url( adn_link( isset( $item['url'] ) ? $item['url'] : '' ) ); ?>" class="news-item">
    <div class="news-item-img" style="background:<?php echo esc_attr( isset( $item['gradient'] ) ? $item['gradient'] : '' ); ?>;border-radius:6px;"></div>
    <div class="news-item-content">
        <div class="card-title-highlight"><?php echo esc_html( isset( $item['title'] ) ? $item['title'] : '' ); ?></div>
        <div class="news-item-meta">
            <span class="card-desc-text"><?php echo esc_html( isset( $item['date'] ) ? $item['date'] : '' ); ?></span>
            <?php if ( ! empty( $item['tag'] ) ) : ?>
                <span class="highlight-textblock"><?php echo esc_html( $item['tag'] ); ?></span>
            <?php endif; ?>
        </div>
    </div>
</a>
