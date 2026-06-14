<?php
/**
 * components/cards/news_item.php - News list item card (direct link).
 * Props: $item { title, date, tag, gradient, url }
 *
 * Clicking anywhere on the card navigates to the article URL — no dropdown.
 */

defined( 'ABSPATH' ) || exit;

$item     = isset( $item ) && is_array( $item ) ? $item : array();
$item_url = ! empty( $item['url'] ) && '#' !== $item['url'] ? esc_url( adn_link( $item['url'] ) ) : '';
$tag      = $item_url ? 'a' : 'div';
$attrs    = $item_url ? ' href="' . $item_url . '"' : '';
?>
<<?php echo $tag . $attrs; ?> class="news-item<?php echo $item_url ? '' : ' news-item--no-link'; ?>">
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
    <?php if ( $item_url ) : ?>
        <span class="news-item-arrow" aria-hidden="true">&#8250;</span>
    <?php endif; ?>
</<?php echo $tag; ?>>
