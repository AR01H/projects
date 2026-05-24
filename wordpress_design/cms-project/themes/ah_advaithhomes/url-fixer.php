<?php
// -----------------------------------------------
// Fix hardcoded local URLs for external domains
// -----------------------------------------------

function ah_should_fix_url() {
    return isset($_SERVER['HTTP_HOST']) && 
           $_SERVER['HTTP_HOST'] !== 'wp_advithhomes_project.test';
}

function ah_fix_url($url) {
    if (!ah_should_fix_url() || empty($url)) return $url;
    return str_replace(
        ['https://wp_advithhomes_project.test', 'http://wp_advithhomes_project.test'],
        (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'],
        $url
    );
}

function ah_fix_content($content) {
    if (!ah_should_fix_url() || empty($content)) return $content;
    return str_replace(
        ['https://wp_advithhomes_project.test', 'http://wp_advithhomes_project.test'],
        (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'],
        $content
    );
}

add_filter('the_content',                 'ah_fix_content');
add_filter('wp_get_attachment_url',       'ah_fix_url');
add_filter('wp_get_attachment_image_src', function($image) {
    if (is_array($image)) { $image[0] = ah_fix_url($image[0]); }
    return $image;
});
add_filter('wp_calculate_image_srcset', function($sources) {
    foreach ($sources as &$source) { $source['url'] = ah_fix_url($source['url']); }
    return $sources;
});
add_filter('acf/load_value/type=image',   'ah_fix_url', 10, 3);
add_filter('acf/load_value/type=gallery', 'ah_fix_content', 10, 3);
add_filter('acf/load_value/type=url',     'ah_fix_url', 10, 3);
add_filter('acf/load_value/type=file',    'ah_fix_url', 10, 3);

add_filter('wp_head', function() {
    if (!ah_should_fix_url()) return;
    ob_start(function($buffer) { return ah_fix_content($buffer); });
});
add_action('wp_footer', function() {
    if (!ah_should_fix_url()) return;
    ob_end_flush();
}, 999);