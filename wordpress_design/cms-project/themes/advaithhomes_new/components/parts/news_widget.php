<?php
/**
 * components/parts/news_widget.php - Reusable Latest News aside widget.
 *
 * Props: $widget { heading {title, link_label, link_url}, items[] }
 * Usage: adn_component( 'parts/news_widget', array( 'widget' => array(
 *            'heading' => $ctx['news']['heading'],
 *            'items'   => $ctx['news']['items'],
 *        ) ) );
 *
 * Designed as a self-contained white card that can be dropped into any
 * sidebar, section, or page layout.
 */

defined( 'ABSPATH' ) || exit;

$widget   = isset( $widget ) && is_array( $widget ) ? $widget : array();
$heading  = isset( $widget['heading'] ) && is_array( $widget['heading'] ) ? $widget['heading'] : array();
$items    = isset( $widget['items'] )   && is_array( $widget['items'] )   ? $widget['items']   : array();
$view_url = ! empty( $heading['link_url'] )   ? (string) $heading['link_url']   : '';
$view_lbl = ! empty( $heading['link_label'] ) ? (string) $heading['link_label'] : 'View all →';

if ( empty( $items ) ) { return; }
?>
<div class="news-widget">
    <?php if ( ! empty( $heading['title'] ) || $view_url ) : ?>
    <div class="news-widget-header">
        <?php if ( ! empty( $heading['title'] ) ) : ?>
            <span class="news-widget-title"><?php echo esc_html( $heading['title'] ); ?></span>
        <?php endif; ?>
        <?php if ( $view_url ) : ?>
            <a href="<?php echo esc_url( adn_link( $view_url ) ); ?>" class="news-widget-view-all">
                <?php echo esc_html( $view_lbl ); ?>
            </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="news-widget-items mini_card_container_design">
        <?php foreach ( $items as $item ) : ?>
            <?php adn_component( 'cards/news_item', array( 'item' => $item ) ); ?>
        <?php endforeach; ?>
    </div>
</div>
