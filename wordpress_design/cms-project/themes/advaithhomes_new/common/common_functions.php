<?php
function getRequestParameter( $title = '', $default_value = '' ) {
    if ( empty( $title ) ) {
        return $default_value;
    }
    if ( isset( $_REQUEST[ $title ] ) ) {
        return sanitize_text_field( wp_unslash( $_REQUEST[ $title ] ) );
    }
    return $default_value;
}
function getJsonParameter( $title = '', $default_value = '' ) {
    $data = getJsonData();
    if ( empty( $title ) ) {
        return $default_value;
    }
    if ( is_array( $data ) && isset( $data[ $title ] ) ) {
        return $data[ $title ];
    }
    return $default_value;
}
function getJsonData() {
    static $data = null;
    if ( $data === null ) {
        $raw = file_get_contents( 'php://input' );
        if ( ! empty( $raw ) ) {
            $decoded = json_decode( $raw, true );
            $data    = ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded ) ) ? $decoded : array();
        } else {
            $data = array();
        }
    }
    return $data;
}
/**
 * Render a component partial from /components/{name}.php.
 * $context keys become local variables inside the component file.
 *
 * Usage: adn_component( 'form_builder/form_builder', array( 'form' => $config ) );
 */
function adn_component( $name, $context = array() ) {
    // Realpath containment: a tampered $name can never escape /components/.
    $base = realpath( ADN_THEME_DIR . '/components' );
    $file = realpath( ADN_THEME_DIR . '/components/' . $name . '.php' );
    if ( ! $base || ! $file || 0 !== strpos( $file, $base ) || ! is_file( $file ) ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[ADN] Component not found: ' . $name );
        }
        return;
    }
    extract( $context, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract
    include $file;
}

/**
 * Convenience wrapper for the form builder component.
 *
 * Usage: adn_render_form( array( 'id' => 'contact', 'fields' => array( ... ) ) );
 */
function adn_render_form( $config ) {
    adn_component( 'form_builder/form_builder', array( 'form' => $config ) );
}

function adn_get_allowed_languages() {
    return array( 'en', 'te' );
}
function getLanguageStrings( $lang ) {
    static $cache = array();
    // Whitelist guard: never build an include path from an untrusted value (prevents Local File Inclusion).
    if ( ! in_array( $lang, adn_get_allowed_languages(), true ) ) {
        $lang = 'en';
    }
    if ( isset( $cache[ $lang ] ) ) {
        return $cache[ $lang ];
    }
    $file = ADN_THEME_DIR . '/languages/' . $lang . '.php';
    if ( file_exists( $file ) ) {
        $cache[ $lang ] = include $file;
    } else {
        $cache[ $lang ] = array();
    }
    return $cache[ $lang ];
}
function lang_translate( $title, $lang = '' ) {
    if ( empty( $lang ) ) {
        $lang = adn_get_current_language();
    }
    $strings = getLanguageStrings( $lang );
    if ( isset( $strings[ $title ] ) ) {
        return $strings[ $title ];
    }
    $default_strings = getLanguageStrings( 'en' );
    return isset( $default_strings[ $title ] ) ? $default_strings[ $title ] : $title;
}
function adn_get_current_language() {
    $allowed = adn_get_allowed_languages();

    // Query string wins (the cookie is written separately on the `init` hook).
    if ( isset( $_GET['lang'] ) ) {
        $lang = sanitize_key( wp_unslash( $_GET['lang'] ) );
        if ( in_array( $lang, $allowed, true ) ) {
            return $lang;
        }
    }

    // Validate the cookie too - it is user-controlled and must never be trusted as-is.
    if ( isset( $_COOKIE['site_lang'] ) ) {
        $lang = sanitize_key( wp_unslash( $_COOKIE['site_lang'] ) );
        if ( in_array( $lang, $allowed, true ) ) {
            return $lang;
        }
    }

    return 'en';
}
function adn_set_language_cookie() {
    if ( ! isset( $_GET['lang'] ) ) {
        return;
    }
    $lang = sanitize_key( wp_unslash( $_GET['lang'] ) );
    if ( in_array( $lang, adn_get_allowed_languages(), true ) ) {
        setcookie( 'site_lang', $lang, time() + ( 86400 * 30 ), COOKIEPATH, COOKIE_DOMAIN );
    }
}