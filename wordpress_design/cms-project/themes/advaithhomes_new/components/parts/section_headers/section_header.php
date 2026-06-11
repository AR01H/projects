<?php
/**
 * components/parts/section_headers/section_header.php — Component: Section Header
 *
 * One heading component for every section-title style in the design:
 *   - "section-header"  : h2 + "View all →" link        (calculators, guides)
 *   - "journey-title"   : centered h2 + underline accent (journey)
 *   - "news-col-title"  : h3 + "View all →" link         (news / regulations columns)
 *
 * Props ($heading + options injected by adn_component()):
 *   $heading       array  { title, link_label?, link_url? }   (required)
 *   $tag           string 'h2' (default) | 'h3'
 *   $wrapper_class string defaults to 'section-header'
 *   $underline     bool   adds <div class="underline-accent"> (journey style)
 *
 * Usage:
 *   adn_component( 'parts/section_headers/section_header', array(
 *       'heading' => $ctx['calculators']['heading'],
 *   ) );
 */

defined( 'ABSPATH' ) || exit;

$heading       = isset( $heading ) && is_array( $heading ) ? $heading : array();
$tag           = isset( $tag ) && in_array( $tag, array( 'h2', 'h3' ), true ) ? $tag : 'h2';
$wrapper_class = isset( $wrapper_class ) && '' !== $wrapper_class ? $wrapper_class : 'section-header';
$underline     = ! empty( $underline );

$title      = isset( $heading['title'] ) ? $heading['title'] : '';
$link_label = isset( $heading['link_label'] ) ? $heading['link_label'] : '';
$link_url   = isset( $heading['link_url'] ) ? $heading['link_url'] : '';

if ( '' === $title ) {
    return;
}
?>
<div class="<?php echo esc_attr( $wrapper_class ); ?>">
    <<?php echo $tag; // phpcs:ignore -- whitelisted above ?>><?php echo esc_html( $title ); ?></<?php echo $tag; // phpcs:ignore ?>>
    <?php if ( $underline ) : ?>
        <div class="underline-accent"></div>
    <?php endif; ?>
    <?php if ( '' !== $link_label && '' !== $link_url ) : ?>
        <a href="<?php echo esc_url( adn_link( $link_url ) ); ?>" class="view-all-link"><?php echo esc_html( $link_label ); ?></a>
    <?php endif; ?>
</div>
