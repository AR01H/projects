<?php




function mytheme_asset($path) {
    $image_path = get_template_directory_uri() . '/assets/' . ltrim($path, '/');
    return $image_path;
}

function mytheme_image($file) {
    $image_path = get_template_directory_uri() . '/assets/images/' . ltrim($file, '/');
    return $image_path;
}

function mytheme_icon($file) {
    return mytheme_asset('icons/' . $file);
}

function mytheme_css($file) {
    return mytheme_asset('css/' . $file);
}

function mytheme_js($file) {
    return mytheme_asset('js/' . $file);
}