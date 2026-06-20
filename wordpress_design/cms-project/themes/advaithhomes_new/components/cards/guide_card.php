<?php
/**
 * components/cards/guide_card.php - Component: Guide / Insight Card
 * Props: $card { icon, gradient, category, title, description, read_more, url }
 */

defined( 'ABSPATH' ) || exit;

$card       = isset( $card ) && is_array( $card ) ? $card : array();
$_gc_imgurl = isset( $card['image'] ) ? (string) $card['image'] : '';
?>
<a href="<?php echo esc_url( adn_link( isset( $card['url'] ) ? $card['url'] : '' ) ); ?>" class="guide-card">
    <?php if ( '' !== $_gc_imgurl ) : ?>
    <div class="guide-card-img guide-card-img--photo">
        <img src="<?php echo esc_url( $_gc_imgurl ); ?>" alt="" loading="lazy" />
        <?php if ( ! empty( $card['parent_name'] ) ) : ?>
            <span class="guide-card-parent-badge"><?php echo esc_html( $card['parent_name'] ); ?></span>
        <?php endif; ?>
    </div>
    <?php else : ?>
    <div class="guide-card-img" style="background:<?php echo esc_attr( isset( $card['gradient'] ) ? $card['gradient'] : '' ); ?>;">
        <span class="guide-card-icon"><?php echo adn_icon( isset( $card['icon'] ) ? $card['icon'] : '' ); ?></span>
        <?php if ( ! empty( $card['parent_name'] ) ) : ?>
            <span class="guide-card-parent-badge"><?php echo esc_html( $card['parent_name'] ); ?></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <div class="guide-card-body">
        <div class="guide-card-category highlight-textblock"><?php echo esc_html( isset( $card['category'] ) ? $card['category'] : '' ); ?></div>
        <?php if ( ! empty( $card['title'] ) ) : ?>
        <div class="card-title-highlight"><?php echo esc_html( $card['title'] ); ?></div>
        <?php endif; ?>
        <div class="card-desc-text"><?php echo esc_html( isset( $card['description'] ) ? $card['description'] : '' ); ?></div>
        <div class="read-more"><?php echo esc_html( isset( $card['read_more'] ) ? $card['read_more'] : SITE_BTN_EXPLORE_ARROW ); ?></div>
    </div>
</a>
