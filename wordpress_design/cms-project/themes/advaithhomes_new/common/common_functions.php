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

/**
 * Render an icon as Font Awesome.
 *
 * Accepts any of:
 *   - a Font Awesome class ("fa-house", "fa-solid fa-house", "fa-brands fa-youtube")
 *   - a known emoji, which is mapped to a Font Awesome icon
 *   - any other glyph/text, returned as-is so nothing ever disappears
 *
 * Theme-wide icon output goes through this so data can stay as emojis while the
 * site renders a consistent Font Awesome set.
 *
 * @param string $icon  Icon value from data.
 * @param string $class Extra CSS classes for the <i>.
 * @return string HTML (already escaped).
 */
function adn_icon( $icon, $class = '' ) {
    $icon = trim( (string) $icon );
    if ( '' === $icon ) {
        return '';
    }

    // Already a Font Awesome class - use it directly (default to solid style).
    if ( false !== strpos( $icon, 'fa-' ) ) {
        $has_style = ( false !== strpos( $icon, 'fa-solid' ) || false !== strpos( $icon, 'fa-regular' ) || false !== strpos( $icon, 'fa-brands' ) );
        $cls       = $has_style ? $icon : 'fa-solid ' . $icon;
        return '<i class="ah-ico ' . esc_attr( trim( $cls . ' ' . $class ) ) . '" aria-hidden="true"></i>';
    }

    // Map a known emoji to a Font Awesome icon.
    $map = adn_icon_emoji_map();
    if ( isset( $map[ $icon ] ) ) {
        return '<i class="ah-ico ' . esc_attr( trim( $map[ $icon ] . ' ' . $class ) ) . '" aria-hidden="true"></i>';
    }

    // Unknown glyph - keep it so layouts never lose their icon.
    return '<span class="ah-emoji">' . esc_html( $icon ) . '</span>';
}

/**
 * Emoji → Font Awesome class lookup used by adn_icon().
 * Extend via the 'adn_icon_emoji_map' filter.
 */
function adn_icon_emoji_map() {
    static $map = null;
    if ( null !== $map ) {
        return $map;
    }
    $map = array(
        '🏠'  => 'fa-solid fa-house',
        '🏡'  => 'fa-solid fa-house-chimney',
        '🏘️' => 'fa-solid fa-house-chimney-window',
        '🏘'  => 'fa-solid fa-house-chimney-window',
        '📦'  => 'fa-solid fa-box',
        '👥'  => 'fa-solid fa-users',
        '🧮'  => 'fa-solid fa-calculator',
        '🏦'  => 'fa-solid fa-building-columns',
        '💳'  => 'fa-solid fa-credit-card',
        '💰'  => 'fa-solid fa-coins',
        '💵'  => 'fa-solid fa-money-bill-wave',
        '💷'  => 'fa-solid fa-sterling-sign',
        '📅'  => 'fa-solid fa-calendar-days',
        '⚖️' => 'fa-solid fa-scale-balanced',
        '⚖'  => 'fa-solid fa-scale-balanced',
        '🚚'  => 'fa-solid fa-truck',
        '🔍'  => 'fa-solid fa-magnifying-glass',
        '💡'  => 'fa-solid fa-lightbulb',
        '🕐'  => 'fa-solid fa-clock',
        '⏱️' => 'fa-solid fa-stopwatch',
        '✉️' => 'fa-solid fa-envelope',
        '✉'  => 'fa-solid fa-envelope',
        '📧'  => 'fa-solid fa-envelope',
        '📞'  => 'fa-solid fa-phone',
        '💬'  => 'fa-solid fa-comment-dots',
        '🔥'  => 'fa-solid fa-fire',
        '📋'  => 'fa-solid fa-clipboard',
        '📝'  => 'fa-solid fa-pen-to-square',
        '📐'  => 'fa-solid fa-ruler-combined',
        '🤝'  => 'fa-solid fa-handshake',
        '📊'  => 'fa-solid fa-chart-column',
        '📈'  => 'fa-solid fa-chart-line',
        '📄'  => 'fa-solid fa-file-lines',
        '📰'  => 'fa-solid fa-newspaper',
        'ℹ️' => 'fa-solid fa-circle-info',
        '✓'  => 'fa-solid fa-check',
        '✔️' => 'fa-solid fa-check',
        '✅'  => 'fa-solid fa-circle-check',
        '📍'  => 'fa-solid fa-location-dot',
        '⭐'  => 'fa-solid fa-star',
        '🎉'  => 'fa-solid fa-gift',
        '🔑'  => 'fa-solid fa-key',
        '🏷️' => 'fa-solid fa-tag',
        '🛡️' => 'fa-solid fa-shield-halved',
        '📱'  => 'fa-solid fa-mobile-screen',
        '🌍'  => 'fa-solid fa-earth-europe',
        '👤'  => 'fa-solid fa-user',
        '🏗️' => 'fa-solid fa-helmet-safety',
        // Brand / social glyphs used in the footer.
        'f'   => 'fa-brands fa-facebook-f',
        '𝕏'  => 'fa-brands fa-x-twitter',
        'in'  => 'fa-brands fa-linkedin-in',
        '◎'  => 'fa-brands fa-instagram',
        '📷'  => 'fa-brands fa-instagram',
        '▶'  => 'fa-brands fa-youtube',
    );
    return apply_filters( 'adn_icon_emoji_map', $map );
}

/**
 * Open a standard theme page.
 * Calls get_header(), renders main_header, and breadcrumb if $ctx has one.
 * Always pair with adn_page_close() at the bottom of the template.
 *
 * @param array $ctx Page context (must contain 'chrome'; optional 'breadcrumb').
 */
function adn_page_open( array $ctx ) {
    get_header();
    adn_component( 'parts/main_header', array( 'chrome' => isset( $ctx['chrome'] ) ? $ctx['chrome'] : array() ) );
    if ( ! empty( $ctx['breadcrumb'] ) ) {
        adn_component( 'parts/breadcrumb', array( 'items' => $ctx['breadcrumb'] ) );
    }
}

/**
 * Close a standard theme page.
 * Renders pre_footer, main_footer, post_footer, post_footer_notice, then get_footer().
 *
 * @param array $ctx Page context (must contain 'chrome.footer').
 */
function adn_page_close( array $ctx ) {
    adn_component( 'parts/pre_footer' );
    adn_component( 'parts/main_footer', array( 'footer' => isset( $ctx['chrome']['footer'] ) ? $ctx['chrome']['footer'] : array() ) );
    adn_component( 'parts/post_footer' );
    adn_component( 'parts/post_footer_notice' );
    get_footer();
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