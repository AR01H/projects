<?php
/**
 * components/cards/guide_card.php - Component: Guide / Insight Card
 * Props: $card { icon, gradient, category, title, description, read_more, url }
 */

defined( 'ABSPATH' ) || exit;

$card       = isset( $card ) && is_array( $card ) ? $card : array();
$_gc_imgurl = isset( $card['image'] ) ? (string) $card['image'] : '';
$_gc_icon   = isset( $card['icon'] ) ? $card['icon'] : '';

// Fallback to default category image if none is provided
if ( empty( $_gc_imgurl ) ) {
    $_gc_imgurl = get_template_directory_uri() . THEME_DEFAULT_CATEGORY_IMG . '?v=' . LOCAL_CACHE_VERSION;
}
?>
<a href="<?php echo esc_url( adn_link( isset( $card['url'] ) ? $card['url'] : '' ) ); ?>" class="guide-card<?php echo '' !== $_gc_imgurl ? ' guide-card--has-img' : ''; ?>">

    <?php if ( '' !== $_gc_imgurl ) : ?>
    <div class="guide-card-img">
        <img src="<?php echo esc_url( $_gc_imgurl ); ?>" alt="<?php echo esc_attr( isset($card['title']) ? $card['title'] : '' ); ?>" loading="lazy" />
        <?php if ( ! empty( $card['category'] ) ) : ?>
            <span class="guide-card-badge">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="14" height="14"><path d="M4 19.5v-15A2.5 2.5 0 016.5 2H20v20H6.5a2.5 2.5 0 01-2.5-2.5zM6.5 18H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 6h6M10 10h6M10 14h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <?php echo esc_html( $card['category'] ); ?>
            </span>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="guide-card-no-img-header">
        <?php if ( ! empty( $card['category'] ) ) : ?>
            <span class="guide-card-badge">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="14" height="14"><path d="M4 19.5v-15A2.5 2.5 0 016.5 2H20v20H6.5a2.5 2.5 0 01-2.5-2.5zM6.5 18H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 6h6M10 10h6M10 14h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <?php echo esc_html( $card['category'] ); ?>
            </span>
        <?php endif; ?>
        <?php if ( $_gc_icon ) : ?>
            <span class="guide-card-icon-circle"><?php echo adn_icon( $_gc_icon ); ?></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="guide-card-body">
        <?php if ( ! empty( $card['title'] ) ) : ?>
            <h3 class="card-title-highlight"><?php echo esc_html( $card['title'] ); ?></h3>
        <?php endif; ?>
        
        <p class="card-desc-text" title="<?php echo esc_attr( ! empty($card['description']) ? $card['description'] : '' ); ?>">
            <?php echo esc_html( ! empty($card['description']) ? $card['description'] : '' ); ?>
        </p>
        
        <div class="guide-card-footer">
            <span class="read-more"><?php echo esc_html( isset( $card['read_more'] ) ? $card['read_more'] : SITE_BTN_EXPLORE_ARROW ); ?></span>
        </div>
    </div>
</a>
