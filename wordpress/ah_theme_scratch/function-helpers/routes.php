<?php

add_action('rest_api_init', function () {

    register_rest_route('ah_theme_scratch/v1', '/hello', [
        'methods'  => 'GET',
        'callback' => function () {
            return [
                'message' => 'Hello World'
            ];
        },
    ]);

});


function mytheme_custom_routes() {

    add_rewrite_rule(
        '^about-us/?$',
        'index.php?custom_page=about',
        'top'
    );

    add_rewrite_rule(
        '^blog/([^/]+)/?$',
        'index.php?name=$matches[1]',
        'top'
    );
}

add_action('init', 'mytheme_custom_routes');