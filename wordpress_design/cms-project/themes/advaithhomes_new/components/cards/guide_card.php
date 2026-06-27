<?php
/**
 * components/cards/guide_card.php - Component: Guide / Insight Card
 * Props: $card { icon, gradient, category, title, description, read_more, url }
 */

defined( 'ABSPATH' ) || exit;

$card       = isset( $card ) && is_array( $card ) ? $card : array();
$_gc_imgurl = isset( $card['image'] ) ? (string) $card['image'] : '';
?>
<a href="<?php echo esc_url( adn_link( isset( $card['url'] ) ? $card['url'] : '' ) ); ?>" class="guide-card<?php echo '' !== $_gc_imgurl ? ' guide-card--has-img' : ''; ?>">

    <?php if ( '' !== $_gc_imgurl ) : ?>
    <?php /* Photo band only - no badge, no cream area */ ?>
    <div class="guide-card-img guide-card-img--photo">
        <img src="<?php echo esc_url( $_gc_imgurl ); ?>" alt="" loading="lazy" />
    </div>
    <?php endif; ?>
    <?php /* No-image cards: no top section - body handles layout */ ?>

    <div class="guide-card-body">
        <?php /* Head: icon circle (left) + title/category (right) — same row */ ?>
        <div class="guide-card-head">
            <?php $_gc_icon = isset( $card['icon'] ) ? $card['icon'] : ''; ?>
            <div class="guide-card-head-text">
                <?php if ( ! empty( $card['category'] ) ) : ?>
                    <div class="guide-card-category"><?php echo esc_html( $card['category'] ); ?></div>
                    <?php endif; ?>
                    <?php if ( ! empty( $card['title'] ) ) : ?>
                        <div class="card-title-highlight"><?php echo esc_html( $card['title'] ); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php if ( $_gc_icon ) : ?>
                    <span class="guide-card-icon-sm"><?php echo adn_icon( $_gc_icon ); ?></span>
                    <?php endif; ?>
        </div>
        <?php if ( ! empty( $card['description'] ) ) : ?>
        <div class="card-desc-text"><?php echo esc_html( $card['description'] ); ?></div>
        <?php endif; ?>
        <div class="read-more"><?php echo esc_html( isset( $card['read_more'] ) ? $card['read_more'] : SITE_BTN_EXPLORE_ARROW ); ?></div>
    </div>
</a>
