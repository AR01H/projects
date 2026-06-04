<?php
/**
 * Component: Quality & Promise
 * Renders the Quality list and Promise card used on the About page.
 */
defined( 'ABSPATH' ) || exit;

// Expect $quality_items and $promise to be set globally by the page template.
// If they are not, we fallback to empty arrays.
// Accept values passed via args for tag, title, body
$quality_items = $args['values'] ?? $quality_items;
$tag           = $args['tag']   ?? 'Why We Do It';
$title         = $args['title'] ?? 'What Makes <span class="accent">The Cane House</span> Different?';
$body          = $args['body']  ?? 'At The Cane House, we serve freshly pressed sugarcane juice and natural fruit blends that are prepared fresh for every customer. Our drinks offer a refreshing alternative to fizzy drinks and processed juices, bringing a traditional summer favourite enjoyed by millions to the heart of Sutton.';


ob_start();
foreach ( $quality_items as $item ) {
    echo '<li>✓ ' . esc_html( is_array( $item ) ? ( $item['text'] ?? '' ) : $item ) . '</li>';
}
$about_values_extra = '<ul class="values-list">' . ob_get_clean() . '</ul>';

$_promise = ch_get_about_promise();
$_promise_tags_html = '';
foreach ( $_promise['tags'] as $tag ) {
    $_promise_tags_html .= '<div class="promise-tag">' . esc_html( $tag ) . '</div>';
}
$about_values_visual = '<div style="display:flex;align-items:center;justify-content:center;">'
    . '<div class="promise-card">'
    . '<span class="promise-icon">' . esc_html( $_promise['icon'] ?? '🌱' ) . '</span>'
    . '<div class="promise-title">' . esc_html( $_promise['title'] ?? 'Our Promise' ) . '</div>'
    . '<div class="promise-sub">' . esc_html( $_promise['sub'] ?? '' ) . '</div>'
    . '<div class="promise-tags">' . $_promise_tags_html . '</div>'
    . '</div>'
    . '</div>';
?>
<div class="about-values">
    <?php get_template_part( 'components/image-text-split', null, [
        'layout'        => 'image-right',
        'section_class' => 'about-values',
        'inner_class'   => 'values-content',
        'tag'           => $tag,
        'title'         => $title,
        'body'          => $body,
        'extra_html'    => $about_values_extra,
        'visual_html'   => $about_values_visual,
        'content_anim' => 'fade-left',
        'visual_anim'  => 'fade-right',
    ] ); ?>
</div>
<?php
// Clean up temporary variables.
unset( $_promise, $_promise_tags_html, $about_values_extra, $about_values_visual );
?>
