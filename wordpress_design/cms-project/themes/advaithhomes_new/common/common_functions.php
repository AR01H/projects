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

    // Plain-text icon name typed by admin (e.g. "home", "search", "key").
    static $text_map = null;
    if ( null === $text_map ) {
        $text_map = array(
            'home'       => 'fa-solid fa-house',
            'house'      => 'fa-solid fa-house',
            'search'     => 'fa-solid fa-magnifying-glass',
            'key'        => 'fa-solid fa-key',
            'star'       => 'fa-solid fa-star',
            'money'      => 'fa-solid fa-coins',
            'coins'      => 'fa-solid fa-coins',
            'phone'      => 'fa-solid fa-phone',
            'email'      => 'fa-solid fa-envelope',
            'envelope'   => 'fa-solid fa-envelope',
            'calendar'   => 'fa-solid fa-calendar-days',
            'location'   => 'fa-solid fa-location-dot',
            'pin'        => 'fa-solid fa-location-dot',
            'building'   => 'fa-solid fa-building-columns',
            'bank'       => 'fa-solid fa-building-columns',
            'check'      => 'fa-solid fa-circle-check',
            'tick'       => 'fa-solid fa-circle-check',
            'info'       => 'fa-solid fa-circle-info',
            'user'       => 'fa-solid fa-user',
            'users'      => 'fa-solid fa-users',
            'people'     => 'fa-solid fa-users',
            'document'   => 'fa-solid fa-file-lines',
            'file'       => 'fa-solid fa-file-lines',
            'clipboard'  => 'fa-solid fa-clipboard',
            'shield'     => 'fa-solid fa-shield-halved',
            'handshake'  => 'fa-solid fa-handshake',
            'deal'       => 'fa-solid fa-handshake',
            'chart'      => 'fa-solid fa-chart-line',
            'graph'      => 'fa-solid fa-chart-line',
            'lightbulb'  => 'fa-solid fa-lightbulb',
            'idea'       => 'fa-solid fa-lightbulb',
            'truck'      => 'fa-solid fa-truck',
            'move'       => 'fa-solid fa-truck',
            'pen'        => 'fa-solid fa-pen-to-square',
            'edit'       => 'fa-solid fa-pen-to-square',
            'clock'      => 'fa-solid fa-clock',
            'time'       => 'fa-solid fa-clock',
            'calculator' => 'fa-solid fa-calculator',
            'calc'       => 'fa-solid fa-calculator',
            'fire'       => 'fa-solid fa-fire',
            'gift'       => 'fa-solid fa-gift',
            'tag'        => 'fa-solid fa-tag',
            'map'        => 'fa-solid fa-map',
            'ruler'      => 'fa-solid fa-ruler-combined',
            'newspaper'  => 'fa-solid fa-newspaper',
            'news'       => 'fa-solid fa-newspaper',
            'box'        => 'fa-solid fa-box',
            'credit'     => 'fa-solid fa-credit-card',
            'card'       => 'fa-solid fa-credit-card',
            'mobile'     => 'fa-solid fa-mobile-screen',
            'globe'      => 'fa-solid fa-earth-europe',
            'world'      => 'fa-solid fa-earth-europe',
            'helmet'     => 'fa-solid fa-helmet-safety',
            'construct'  => 'fa-solid fa-helmet-safety',
            'scale'      => 'fa-solid fa-scale-balanced',
            'law'        => 'fa-solid fa-scale-balanced',
        );
    }
    $icon_lower = strtolower( $icon );
    if ( isset( $text_map[ $icon_lower ] ) ) {
        return '<i class="ah-ico ' . esc_attr( trim( $text_map[ $icon_lower ] . ' ' . $class ) ) . '" aria-hidden="true"></i>';
    }

    // Completely unknown value → neutral outline icon.
    return $icon;
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

        // ── Housing & Property ───────────────────────────────────────────
        '🏠'  => 'fa-solid fa-house',
        '🏡'  => 'fa-solid fa-house-chimney',
        '🏘️' => 'fa-solid fa-house-chimney-window',
        '🏘'  => 'fa-solid fa-house-chimney-window',
        '🏗️' => 'fa-solid fa-helmet-safety',
        '🏢'  => 'fa-solid fa-building',
        '🏬'  => 'fa-solid fa-store',
        '🏪'  => 'fa-solid fa-shop',
        '🏥'  => 'fa-solid fa-hospital',
        '🏦'  => 'fa-solid fa-building-columns',
        '🏫'  => 'fa-solid fa-school',
        '⛪'  => 'fa-solid fa-church',
        '🏛️' => 'fa-solid fa-landmark',
        '🗺️' => 'fa-solid fa-map',
        '📍'  => 'fa-solid fa-location-dot',
        '📌'  => 'fa-solid fa-thumbtack',

        // ── People & Users ───────────────────────────────────────────────
        '👤'  => 'fa-solid fa-user',
        '👥'  => 'fa-solid fa-users',
        '🧑‍💼' => 'fa-solid fa-user-tie',
        '👶'  => 'fa-solid fa-baby',
        '🧒'  => 'fa-solid fa-child',
        '👴'  => 'fa-solid fa-person-cane',
        '🤝'  => 'fa-solid fa-handshake',
        '🙋'  => 'fa-solid fa-hand',
        '🧑‍⚕️' => 'fa-solid fa-user-doctor',
        '🧑‍🏫' => 'fa-solid fa-chalkboard-user',

        // ── Finance & Commerce ───────────────────────────────────────────
        '🧮'  => 'fa-solid fa-calculator',
        '💳'  => 'fa-solid fa-credit-card',
        '💰'  => 'fa-solid fa-coins',
        '💵'  => 'fa-solid fa-money-bill-wave',
        '💷'  => 'fa-solid fa-sterling-sign',
        '💶'  => 'fa-solid fa-euro-sign',
        '💴'  => 'fa-solid fa-yen-sign',
        '💸'  => 'fa-solid fa-money-bill-transfer',
        '🏧'  => 'fa-solid fa-money-check-dollar',
        '🛒'  => 'fa-solid fa-cart-shopping',
        '🛍️' => 'fa-solid fa-bag-shopping',
        '🏷️' => 'fa-solid fa-tag',
        '🎁'  => 'fa-solid fa-gift',
        '🎉'  => 'fa-solid fa-gift',
        '📦'  => 'fa-solid fa-box',
        '🚚'  => 'fa-solid fa-truck',
        '⚖️' => 'fa-solid fa-scale-balanced',
        '⚖'  => 'fa-solid fa-scale-balanced',

        // ── Documents & Office ───────────────────────────────────────────
        '📋'  => 'fa-solid fa-clipboard',
        '📝'  => 'fa-solid fa-pen-to-square',
        '📄'  => 'fa-solid fa-file-lines',
        '📃'  => 'fa-solid fa-file',
        '📜'  => 'fa-solid fa-scroll',
        '📰'  => 'fa-solid fa-newspaper',
        '📐'  => 'fa-solid fa-ruler-combined',
        '📏'  => 'fa-solid fa-ruler-horizontal',
        '🗂️' => 'fa-solid fa-folder-open',
        '📁'  => 'fa-solid fa-folder',
        '🗃️' => 'fa-solid fa-cabinet-filing',
        '🗄️' => 'fa-solid fa-server',
        '🖨️' => 'fa-solid fa-print',
        '✏️' => 'fa-solid fa-pencil',
        '✒️' => 'fa-solid fa-pen-nib',

        // ── Communication ────────────────────────────────────────────────
        '✉️' => 'fa-solid fa-envelope',
        '✉'  => 'fa-solid fa-envelope',
        '📧'  => 'fa-solid fa-envelope',
        '📨'  => 'fa-solid fa-envelope-open',
        '📩'  => 'fa-solid fa-envelope-open-text',
        '📞'  => 'fa-solid fa-phone',
        '📟'  => 'fa-solid fa-pager',
        '📠'  => 'fa-solid fa-fax',
        '💬'  => 'fa-solid fa-comment-dots',
        '💭'  => 'fa-solid fa-comment',
        '📣'  => 'fa-solid fa-bullhorn',
        '📢'  => 'fa-solid fa-tower-broadcast',
        '🔔'  => 'fa-solid fa-bell',
        '🔕'  => 'fa-solid fa-bell-slash',

        // ── Technology & Devices ─────────────────────────────────────────
        '📱'  => 'fa-solid fa-mobile-screen',
        '💻'  => 'fa-solid fa-laptop',
        '🖥️' => 'fa-solid fa-desktop',
        '⌨️' => 'fa-solid fa-keyboard',
        '🖱️' => 'fa-solid fa-computer-mouse',
        '🖨️' => 'fa-solid fa-print',
        '💾'  => 'fa-solid fa-floppy-disk',
        '💿'  => 'fa-solid fa-compact-disc',
        '📷'  => 'fa-brands fa-instagram',
        '📸'  => 'fa-solid fa-camera',
        '🖼️' => 'fa-solid fa-image',
        '🖼'  => 'fa-solid fa-image',
        '📹'  => 'fa-solid fa-video',
        '📡'  => 'fa-solid fa-satellite-dish',
        '🔋'  => 'fa-solid fa-battery-full',
        '🔌'  => 'fa-solid fa-plug',
        '💡'  => 'fa-solid fa-lightbulb',

        // ── Navigation & Transport ───────────────────────────────────────
        '🚗'  => 'fa-solid fa-car',
        '🚕'  => 'fa-solid fa-taxi',
        '🚙'  => 'fa-solid fa-car-side',
        '🚌'  => 'fa-solid fa-bus',
        '✈️' => 'fa-solid fa-plane',
        '🚢'  => 'fa-solid fa-ship',
        '🚲'  => 'fa-solid fa-bicycle',
        '🛵'  => 'fa-solid fa-motorcycle',
        '🚁'  => 'fa-solid fa-helicopter',
        '⛽'  => 'fa-solid fa-gas-pump',
        '🗺️' => 'fa-solid fa-map',
        '🧭'  => 'fa-solid fa-compass',
        '🚦'  => 'fa-solid fa-traffic-light',
        '🅿️' => 'fa-solid fa-square-parking',
        '⚓'  => 'fa-solid fa-anchor',

        // ── Health & Medical ─────────────────────────────────────────────
        '🏥'  => 'fa-solid fa-hospital',
        '💊'  => 'fa-solid fa-pills',
        '💉'  => 'fa-solid fa-syringe',
        '🩺'  => 'fa-solid fa-stethoscope',
        '🩹'  => 'fa-solid fa-bandage',
        '🧬'  => 'fa-solid fa-dna',
        '🦷'  => 'fa-solid fa-tooth',
        '👁️' => 'fa-solid fa-eye',
        '❤️' => 'fa-solid fa-heart',
        '💪'  => 'fa-solid fa-dumbbell',
        '🧘'  => 'fa-solid fa-spa',
        '🩻'  => 'fa-solid fa-x-ray',

        // ── Food & Drink ─────────────────────────────────────────────────
        '🍽️' => 'fa-solid fa-utensils',
        '🍴'  => 'fa-solid fa-utensils',
        '☕'  => 'fa-solid fa-mug-hot',
        '🍺'  => 'fa-solid fa-beer-mug-empty',
        '🍷'  => 'fa-solid fa-wine-glass',
        '🥂'  => 'fa-solid fa-champagne-glasses',
        '🍕'  => 'fa-solid fa-pizza-slice',
        '🥗'  => 'fa-solid fa-bowl-food',
        '🫙'  => 'fa-solid fa-jar',
        '🧂'  => 'fa-solid fa-shaker-of-salt',  // FA 6.4+
        '🧃'  => 'fa-solid fa-glass-water',
        '🍾'  => 'fa-solid fa-bottle-droplet',   // FA 6.4+
        '🫗'  => 'fa-solid fa-glass-water-droplet',

        // ── Fruits 🍎 ────────────────────────────────────────────────────
        // FA Free doesn't have individual fruit icons, so these map to
        // the closest semantic alternatives.
        '🍎'  => 'fa-solid fa-apple-whole',      // FA 6 Free ✓
        '🍏'  => 'fa-solid fa-apple-whole',
        '🍊'  => 'fa-solid fa-lemon',
        '🍋'  => 'fa-solid fa-lemon',             // FA 6 Free ✓
        '🍇'  => 'fa-solid fa-seedling',
        '🍓'  => 'fa-solid fa-seedling',
        '🫐'  => 'fa-solid fa-seedling',
        '🍒'  => 'fa-solid fa-seedling',
        '🍑'  => 'fa-solid fa-seedling',
        '🥝'  => 'fa-solid fa-seedling',
        '🍍'  => 'fa-solid fa-seedling',
        '🥭'  => 'fa-solid fa-seedling',
        '🍌'  => 'fa-solid fa-seedling',
        '🍉'  => 'fa-solid fa-seedling',
        '🍈'  => 'fa-solid fa-seedling',
        '🍐'  => 'fa-solid fa-seedling',
        '🫒'  => 'fa-solid fa-seedling',

        // ── Nature & Environment ─────────────────────────────────────────
        '🌍'  => 'fa-solid fa-earth-europe',
        '🌎'  => 'fa-solid fa-earth-americas',
        '🌏'  => 'fa-solid fa-earth-asia',
        '🌳'  => 'fa-solid fa-tree',
        '🌲'  => 'fa-solid fa-tree',
        '🌵'  => 'fa-solid fa-cactus',            // FA 6 Free ✓
        '🌿'  => 'fa-solid fa-leaf',
        '🍃'  => 'fa-solid fa-leaf',
        '🍂'  => 'fa-solid fa-leaf',
        '🌱'  => 'fa-solid fa-seedling',
        '💧'  => 'fa-solid fa-droplet',
        '🌊'  => 'fa-solid fa-water',
        '❄️' => 'fa-solid fa-snowflake',
        '☀️' => 'fa-solid fa-sun',
        '🌙'  => 'fa-solid fa-moon',
        '⛅'  => 'fa-solid fa-cloud-sun',
        '🌧️' => 'fa-solid fa-cloud-rain',
        '⛈️' => 'fa-solid fa-cloud-bolt',
        '🌪️' => 'fa-solid fa-tornado',
        '🔥'  => 'fa-solid fa-fire',

        // ── Tools & Settings ─────────────────────────────────────────────
        '🔧'  => 'fa-solid fa-wrench',
        '🔨'  => 'fa-solid fa-hammer',
        '⚙️' => 'fa-solid fa-gear',
        '🔩'  => 'fa-solid fa-screwdriver-wrench',
        '🪛'  => 'fa-solid fa-screwdriver',
        '🔐'  => 'fa-solid fa-lock',
        '🔓'  => 'fa-solid fa-lock-open',
        '🔑'  => 'fa-solid fa-key',
        '🗝️' => 'fa-solid fa-key',
        '🛡️' => 'fa-solid fa-shield-halved',
        '🛟'  => 'fa-solid fa-life-ring',
        '⚠️' => 'fa-solid fa-triangle-exclamation',
        '🚫'  => 'fa-solid fa-ban',
        '❌'  => 'fa-solid fa-xmark',
        '❓'  => 'fa-solid fa-circle-question',
        '❗'  => 'fa-solid fa-circle-exclamation',

        // ── Time & Calendar ──────────────────────────────────────────────
        '📅'  => 'fa-solid fa-calendar-days',
        '📆'  => 'fa-solid fa-calendar-check',
        '🗓️' => 'fa-solid fa-calendar',
        '🕐'  => 'fa-solid fa-clock',
        '⏱️' => 'fa-solid fa-stopwatch',
        '⏰'  => 'fa-solid fa-alarm-clock',       // FA 6 Free ✓
        '⌛'  => 'fa-solid fa-hourglass-end',
        '⏳'  => 'fa-solid fa-hourglass-half',

        // ── Analytics & Data ─────────────────────────────────────────────
        '📊'  => 'fa-solid fa-chart-column',
        '📈'  => 'fa-solid fa-chart-line',
        '📉'  => 'fa-solid fa-chart-line-down',   // FA 6 Free ✓
        '🔍'  => 'fa-solid fa-magnifying-glass',
        '🔎'  => 'fa-solid fa-magnifying-glass-plus',
        '🧪'  => 'fa-solid fa-flask',
        '🧫'  => 'fa-solid fa-vial',
        '🧲'  => 'fa-solid fa-magnet',
        '📻'  => 'fa-solid fa-radio',

        // ── Symbols & Misc ───────────────────────────────────────────────
        'ℹ️' => 'fa-solid fa-circle-info',
        '✓'  => 'fa-solid fa-check',
        '✔️' => 'fa-solid fa-check',
        '✅'  => 'fa-solid fa-circle-check',
        '⭐'  => 'fa-solid fa-star',
        '🌟'  => 'fa-solid fa-star',
        '💫'  => 'fa-solid fa-wand-magic-sparkles',
        '🎯'  => 'fa-solid fa-bullseye',
        '🏆'  => 'fa-solid fa-trophy',
        '🥇'  => 'fa-solid fa-medal',
        '🎓'  => 'fa-solid fa-graduation-cap',
        '📚'  => 'fa-solid fa-book',
        '📖'  => 'fa-solid fa-book-open',
        '🔖'  => 'fa-solid fa-bookmark',
        '🏅'  => 'fa-solid fa-medal',
        '♻️' => 'fa-solid fa-recycle',
        '🧩'  => 'fa-solid fa-puzzle-piece',
        '🔗'  => 'fa-solid fa-link',
        '📎'  => 'fa-solid fa-paperclip',
        '🗑️' => 'fa-solid fa-trash-can',
        '📤'  => 'fa-solid fa-upload',
        '📥'  => 'fa-solid fa-download',
        '🔄'  => 'fa-solid fa-arrows-rotate',
        '↩️' => 'fa-solid fa-arrow-turn-down',   // or fa-reply
        '➡️' => 'fa-solid fa-arrow-right',
        '⬆️' => 'fa-solid fa-arrow-up',
        '➕'  => 'fa-solid fa-plus',
        '➖'  => 'fa-solid fa-minus',

        // ── Brand / Social (footer) ──────────────────────────────────────
        'f'   => 'fa-brands fa-facebook-f',
        '𝕏'  => 'fa-brands fa-x-twitter',
        'in'  => 'fa-brands fa-linkedin-in',
        '◎'  => 'fa-brands fa-instagram',
        '📷'  => 'fa-brands fa-instagram',
        '▶'  => 'fa-brands fa-youtube',
        '🐦'  => 'fa-brands fa-x-twitter',
        '💼'  => 'fa-brands fa-linkedin',
        'gh'  => 'fa-brands fa-github',
        '🐙'  => 'fa-brands fa-github',
        'wp'  => 'fa-brands fa-wordpress',
        'tt'  => 'fa-brands fa-tiktok',
        'pin' => 'fa-brands fa-pinterest',
        'yt'  => 'fa-brands fa-youtube',
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
    if ( ! empty( $_GET['content'] ) && 'true' === (string) $_GET['content'] ) {
        ?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<?php wp_head(); ?>
</head>
<body class="adn-content-only">
        <?php
        return;
    }
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
    if ( ! empty( $_GET['content'] ) && 'true' === (string) $_GET['content'] ) {
        wp_footer();
        ?>
</body>
</html>
        <?php
        return;
    }
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