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
        '❓'  => 'fa-regular fa-question',
        '❗'  => 'fa-regular fa-circle-exclamation',

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
        '📈'  => 'fa-solid fa-arrow-trend-up',
        '📉'  => 'fa-solid fa-arrow-trend-down',   // FA 6 Free ✓
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

        // ── Music & Entertainment ────────────────────────────────────────────
        '🎵'  => 'fa-solid fa-music',
        '🎶'  => 'fa-solid fa-music',
        '🎸'  => 'fa-solid fa-guitar',
        '🎹'  => 'fa-solid fa-piano-keys',
        '🎤'  => 'fa-solid fa-microphone',
        '🎧'  => 'fa-solid fa-headphones',
        '🎙️' => 'fa-solid fa-microphone-lines',
        '📺'  => 'fa-solid fa-tv',
        '🎞️' => 'fa-solid fa-film',
        '🎬'  => 'fa-solid fa-clapperboard',
        '🎭'  => 'fa-solid fa-masks-theater',
        '🎨'  => 'fa-solid fa-palette',
        '🖌️' => 'fa-solid fa-paintbrush',

        // ── Sports & Activities ──────────────────────────────────────────────
        '⚽'  => 'fa-solid fa-futbol',
        '🏀'  => 'fa-solid fa-basketball',
        '🏈'  => 'fa-solid fa-football',
        '⚾'  => 'fa-solid fa-baseball',
        '🎾'  => 'fa-solid fa-table-tennis-paddle-ball',
        '🏐'  => 'fa-solid fa-volleyball',
        '🏋️' => 'fa-solid fa-dumbbell',
        '🚴'  => 'fa-solid fa-person-biking',
        '🏊'  => 'fa-solid fa-person-swimming',
        '🏃'  => 'fa-solid fa-person-running',
        '🧗'  => 'fa-solid fa-person-hiking',
        '⛷️' => 'fa-solid fa-person-skiing',
        '🎳'  => 'fa-solid fa-bowling-ball',
        '🥊'  => 'fa-solid fa-boxing-glove',
        '🏌️' => 'fa-solid fa-golf-ball-tee',
        '♟️' => 'fa-solid fa-chess',

        // ── Gaming ───────────────────────────────────────────────────────────
        '🎮'  => 'fa-solid fa-gamepad',
        '🕹️' => 'fa-solid fa-gamepad',
        '🎲'  => 'fa-solid fa-dice',
        '🃏'  => 'fa-solid fa-cards',
        '♠️' => 'fa-solid fa-spade',
        '♥️' => 'fa-solid fa-heart',
        '♦️' => 'fa-solid fa-diamond',
        '♣️' => 'fa-solid fa-club',

        // ── Animals & Nature (extras) ────────────────────────────────────────
        '🐕'  => 'fa-solid fa-dog',
        '🐈'  => 'fa-solid fa-cat',
        '🐟'  => 'fa-solid fa-fish',
        '🦁'  => 'fa-solid fa-paw',
        '🐾'  => 'fa-solid fa-paw',
        '🦋'  => 'fa-solid fa-bugs',
        '🐝'  => 'fa-solid fa-bugs',
        '🐛'  => 'fa-solid fa-bugs',
        '🌹'  => 'fa-solid fa-rose',
        '🌺'  => 'fa-solid fa-seedling',
        '🌻'  => 'fa-solid fa-seedling',
        '🌸'  => 'fa-solid fa-seedling',
        '🍄'  => 'fa-solid fa-seedling',
        '🌾'  => 'fa-solid fa-wheat-awn',

        // ── Travel & Places ──────────────────────────────────────────────────
        '🏖️' => 'fa-solid fa-umbrella-beach',
        '🏔️' => 'fa-solid fa-mountain',
        '⛺'  => 'fa-solid fa-campground',
        '🏕️' => 'fa-solid fa-campground',
        '🗼'  => 'fa-solid fa-tower-observation',
        '🌉'  => 'fa-solid fa-bridge',
        '🌐'  => 'fa-solid fa-globe',
        '🛂'  => 'fa-solid fa-passport',
        '🧳'  => 'fa-solid fa-suitcase',
        '🛬'  => 'fa-solid fa-plane-arrival',
        '🛫'  => 'fa-solid fa-plane-departure',
        '🚂'  => 'fa-solid fa-train',
        '🚆'  => 'fa-solid fa-train-subway',
        '⛴️' => 'fa-solid fa-ferry',
        '🚀'  => 'fa-solid fa-rocket',

        // ── Clothing & Accessories ───────────────────────────────────────────
        '👔'  => 'fa-solid fa-shirt',
        '👗'  => 'fa-solid fa-shirt',
        '👟'  => 'fa-solid fa-shoe-prints',
        '👠'  => 'fa-solid fa-shoe-prints',
        '🎩'  => 'fa-solid fa-hat-wizard',
        '💍'  => 'fa-solid fa-ring',
        '💎'  => 'fa-solid fa-gem',
        '👓'  => 'fa-solid fa-glasses',
        '🕶️' => 'fa-solid fa-glasses',
        '👜'  => 'fa-solid fa-bag-shopping',
        '🎒'  => 'fa-solid fa-bag-shopping',
        '🧣'  => 'fa-solid fa-scarf',        // FA 6.4+
        '🧤'  => 'fa-solid fa-mitten',       // FA 6.4+
        '🧢'  => 'fa-solid fa-baseball-bat-ball', // closest available

        // ── Home & Household ─────────────────────────────────────────────────
        '🛁'  => 'fa-solid fa-bath',
        '🚿'  => 'fa-solid fa-shower',
        '🛏️' => 'fa-solid fa-bed',
        '🪑'  => 'fa-solid fa-chair',
        '🪟'  => 'fa-solid fa-window-maximize',
        '🚪'  => 'fa-solid fa-door-open',
        '🧹'  => 'fa-solid fa-broom',
        '🧺'  => 'fa-solid fa-basket-shopping',
        '🧻'  => 'fa-solid fa-toilet-paper',
        '🪣'  => 'fa-solid fa-bucket',
        '🧰'  => 'fa-solid fa-toolbox',
        '🪚'  => 'fa-solid fa-saw',
        '🪜'  => 'fa-solid fa-stairs',
        '💈'  => 'fa-solid fa-barber-pole',  // FA 6+

        // ── Science & Learning ───────────────────────────────────────────────
        '🔭'  => 'fa-solid fa-telescope',
        '🌡️' => 'fa-solid fa-temperature-half',
        '⚗️' => 'fa-solid fa-flask-vial',
        '🧭'  => 'fa-solid fa-compass',
        '🗺'  => 'fa-solid fa-map',
        '📐'  => 'fa-solid fa-ruler-combined',
        '🖊️' => 'fa-solid fa-pen',
        '🖋️' => 'fa-solid fa-pen-fancy',
        '📓'  => 'fa-solid fa-book',
        '📔'  => 'fa-solid fa-book-bookmark',
        '📒'  => 'fa-solid fa-book',

        // ── People (extras) ──────────────────────────────────────────────────
        '👋'  => 'fa-solid fa-hand-wave',
        '👍'  => 'fa-solid fa-thumbs-up',
        '👎'  => 'fa-solid fa-thumbs-down',
        '👏'  => 'fa-solid fa-hands-clapping',
        '🙏'  => 'fa-solid fa-hands-praying',
        '🧠'  => 'fa-solid fa-brain',
        '👁'  => 'fa-solid fa-eye',
        '👀'  => 'fa-solid fa-eyes',
        '💀'  => 'fa-solid fa-skull',
        '🦴'  => 'fa-solid fa-bone',
        '🦶'  => 'fa-solid fa-shoe-prints',
        '🫀'  => 'fa-solid fa-heart-pulse',
        '🫁'  => 'fa-solid fa-lungs',

        // ── Symbols (extras) ─────────────────────────────────────────────────
        '©️' => 'fa-regular fa-copyright',
        '®️' => 'fa-solid fa-registered',
        '™️' => 'fa-solid fa-trademark',
        '🔅'  => 'fa-solid fa-brightness-low',  // FA 6.4+
        '🔆'  => 'fa-solid fa-brightness',      // FA 6.4+
        '〰️' => 'fa-solid fa-wave-square',
        '🔀'  => 'fa-solid fa-shuffle',
        '⏭️' => 'fa-solid fa-forward-fast',
        '⏮️' => 'fa-solid fa-backward-fast',
        '⏩'  => 'fa-solid fa-forward',
        '⏪'  => 'fa-solid fa-backward',
        '⏸️' => 'fa-solid fa-pause',
        '⏺️' => 'fa-solid fa-circle',
        '⏹️' => 'fa-solid fa-stop',
        '▶️' => 'fa-solid fa-play',
        '🔊'  => 'fa-solid fa-volume-high',
        '🔉'  => 'fa-solid fa-volume-low',
        '🔈'  => 'fa-solid fa-volume-off',
        '🔇'  => 'fa-solid fa-volume-xmark',
        '🌈'  => 'fa-solid fa-rainbow',
        '☁️' => 'fa-solid fa-cloud',
        '🌤️' => 'fa-solid fa-cloud-sun',
        '🌦️' => 'fa-solid fa-cloud-sun-rain',
        '❄'  => 'fa-solid fa-snowflake',
        '⚡'  => 'fa-solid fa-bolt',
        '🌀'  => 'fa-solid fa-hurricane',

        // ── Numbers 0–9 (shortcodes) ─────────────────────────────────────────
        '0'  => 'fa-solid fa-0',
        '1'  => 'fa-solid fa-1',
        '2'  => 'fa-solid fa-2',
        '3'  => 'fa-solid fa-3',
        '4'  => 'fa-solid fa-4',
        '5'  => 'fa-solid fa-5',
        '6'  => 'fa-solid fa-6',
        '7'  => 'fa-solid fa-7',
        '8'  => 'fa-solid fa-8',
        '9'  => 'fa-solid fa-9',

        // ── Single-letter shortcuts (a–z) ────────────────────────────────────
        // Only those with a clear semantic mapping
        'a'  => 'fa-solid fa-a',
        'b'  => 'fa-solid fa-b',
        'c'  => 'fa-solid fa-c',
        'd'  => 'fa-solid fa-d',
        'e'  => 'fa-solid fa-e',
        'g'  => 'fa-solid fa-g',
        'h'  => 'fa-solid fa-h',
        'i'  => 'fa-solid fa-i',
        'j'  => 'fa-solid fa-j',
        'k'  => 'fa-solid fa-k',
        'l'  => 'fa-solid fa-l',
        'm'  => 'fa-solid fa-m',
        'n'  => 'fa-solid fa-n',
        'o'  => 'fa-solid fa-o',
        'p'  => 'fa-solid fa-p',
        'q'  => 'fa-solid fa-q',
        'r'  => 'fa-solid fa-r',
        's'  => 'fa-solid fa-s',
        't'  => 'fa-solid fa-t',
        'u'  => 'fa-solid fa-u',
        'v'  => 'fa-solid fa-v',
        'w'  => 'fa-solid fa-w',
        'x'  => 'fa-solid fa-x',
        'y'  => 'fa-solid fa-y',
        'z'  => 'fa-solid fa-z',

        // ── Extra Brand Shortcodes ───────────────────────────────────────────
        'fb'  => 'fa-brands fa-facebook',
        'ig'  => 'fa-brands fa-instagram',
        'tw'  => 'fa-brands fa-x-twitter',
        'li'  => 'fa-brands fa-linkedin',
        'sc'  => 'fa-brands fa-snapchat',
        'rd'  => 'fa-brands fa-reddit',
        'dc'  => 'fa-brands fa-discord',
        'sp'  => 'fa-brands fa-spotify',
        'am'  => 'fa-brands fa-amazon',
        'gg'  => 'fa-brands fa-google',
        'ap'  => 'fa-brands fa-apple',
        'ms'  => 'fa-brands fa-microsoft',
        'wa'  => 'fa-brands fa-whatsapp',
        'tg'  => 'fa-brands fa-telegram',
        'sl'  => 'fa-brands fa-slack',
        'tw2' => 'fa-brands fa-twitch',
        'dr'  => 'fa-brands fa-dribbble',
        'bh'  => 'fa-brands fa-behance',
        'vm'  => 'fa-brands fa-vimeo',
        'md'  => 'fa-brands fa-medium',
        'pa'  => 'fa-brands fa-patreon',
        'et'  => 'fa-brands fa-etsy',
        'sh'  => 'fa-brands fa-shopify',
        'pp'  => 'fa-brands fa-paypal',
        'st'  => 'fa-brands fa-stripe',
        'so'  => 'fa-brands fa-stack-overflow',
        'cp'  => 'fa-brands fa-codepen',
        'npm' => 'fa-brands fa-npm',
        'dh'  => 'fa-brands fa-docker',
        'fx'  => 'fa-brands fa-firefox',
        'cr'  => 'fa-brands fa-chrome',
        'sf'  => 'fa-brands fa-safari',
        'vi'  => 'fa-brands fa-vk',
        'lf'  => 'fa-brands fa-line',
        'qq'  => 'fa-brands fa-qq',
        'wc'  => 'fa-brands fa-weixin',    // WeChat
        'bl'  => 'fa-brands fa-tumblr',
        'fo'  => 'fa-brands fa-font-awesome',
        'xb'  => 'fa-brands fa-xbox',
        'ps'  => 'fa-brands fa-playstation',
        'st2' => 'fa-brands fa-steam',
        'bt'  => 'fa-brands fa-bitcoin',
        'eth' => 'fa-brands fa-ethereum',

        // ── Additional Missing Icons ─────────────────────────────────────────────────
        '🧑'  => 'fa-solid fa-person',
        '👦'  => 'fa-solid fa-child',
        '👧'  => 'fa-solid fa-child-dress',
        '👩'  => 'fa-solid fa-person-dress',
        '👨'  => 'fa-solid fa-person',
        '👵'  => 'fa-solid fa-person-cane',
        '🧓'  => 'fa-solid fa-person-cane',
        '🧑‍🦯' => 'fa-solid fa-person-walking-with-cane',
        '🧑‍🦼' => 'fa-solid fa-wheelchair',
        '♿'  => 'fa-solid fa-wheelchair',
        '🧑‍🦽' => 'fa-solid fa-wheelchair-move',
        '🧑‍🍳' => 'fa-solid fa-kitchen-set',
        '🧑‍🌾' => 'fa-solid fa-tractor',
        '🧑‍🔧' => 'fa-solid fa-screwdriver-wrench',
        '🧑‍🔬' => 'fa-solid fa-flask',
        '🧑‍🎨' => 'fa-solid fa-palette',
        '🧑‍✈️' => 'fa-solid fa-plane',
        '🧑‍🚒' => 'fa-solid fa-fire-extinguisher',
        '🧑‍🚀' => 'fa-solid fa-rocket',
        '👮'  => 'fa-solid fa-shield',
        '💂'  => 'fa-solid fa-shield-halved',
        '🕵️' => 'fa-solid fa-user-secret',
        '👷'  => 'fa-solid fa-helmet-safety',

        // ── Flags ─────────────────────────────────────────────────────────────────────
        '🏁'  => 'fa-solid fa-flag-checkered',
        '🚩'  => 'fa-solid fa-flag',
        '🏳️' => 'fa-solid fa-flag',
        '🏴'  => 'fa-solid fa-flag',

        // ── Weather (extras) ──────────────────────────────────────────────────────────
        '🌨️' => 'fa-solid fa-snowflake',
        '🌩️' => 'fa-solid fa-cloud-bolt',
        '🌫️' => 'fa-solid fa-smog',
        '🌬️' => 'fa-solid fa-wind',
        '🌂'  => 'fa-solid fa-umbrella',
        '☂️' => 'fa-solid fa-umbrella',
        '☔'  => 'fa-solid fa-umbrella',
        '⛄'  => 'fa-solid fa-snowman',

        // ── Space ─────────────────────────────────────────────────────────────────────
        '🌑'  => 'fa-solid fa-moon',
        '🌕'  => 'fa-solid fa-moon',
        '⭐'  => 'fa-solid fa-star',
        '🌟'  => 'fa-regular fa-star',
        '💥'  => 'fa-solid fa-burst',
        '☄️' => 'fa-solid fa-meteor',

        // ── Finance (extras) ─────────────────────────────────────────────────────────
        '📊'  => 'fa-solid fa-chart-pie',
        '🏦'  => 'fa-solid fa-piggy-bank',
        '🪙'  => 'fa-solid fa-coins',
        '💹'  => 'fa-solid fa-chart-line',

        // ── Food (extras) ────────────────────────────────────────────────────────────
        '🍔'  => 'fa-solid fa-burger',
        '🌮'  => 'fa-solid fa-bowl-food',
        '🍞'  => 'fa-solid fa-bread-slice',
        '🧁'  => 'fa-solid fa-cake-candles',
        '🎂'  => 'fa-solid fa-cake-candles',
        '🍰'  => 'fa-solid fa-cake-candles',
        '🍳'  => 'fa-solid fa-egg',
        '🥞'  => 'fa-solid fa-layer-group',
        '🍜'  => 'fa-solid fa-bowl-rice',
        '🍚'  => 'fa-solid fa-bowl-rice',
        '🍣'  => 'fa-solid fa-fish',
        '🧆'  => 'fa-solid fa-circle',
        '🫖'  => 'fa-solid fa-mug-saucer',
        '🥤'  => 'fa-solid fa-glass-water',
        '🧊'  => 'fa-solid fa-cube',

        // ── Medical (extras) ─────────────────────────────────────────────────────────
        '🚑'  => 'fa-solid fa-truck-medical',
        '🩼'  => 'fa-solid fa-person-cane',
        '🩺'  => 'fa-solid fa-stethoscope',
        '🏋️' => 'fa-solid fa-dumbbell',
        '🧴'  => 'fa-solid fa-pump-soap',
        '🧼'  => 'fa-solid fa-soap',
        '🪥'  => 'fa-solid fa-toothbrush',

        // ── Office & Stationery (extras) ─────────────────────────────────────────────
        '🗒️' => 'fa-solid fa-notepad',
        '📇'  => 'fa-solid fa-address-card',
        '🗳️' => 'fa-solid fa-box-ballot',
        '📮'  => 'fa-solid fa-mailbox',
        '🖇️' => 'fa-solid fa-paperclip',
        '📌'  => 'fa-solid fa-thumbtack',

        // ── Security ─────────────────────────────────────────────────────────────────
        '🔒'  => 'fa-solid fa-lock',
        '🛡'  => 'fa-solid fa-shield',
        '🔏'  => 'fa-solid fa-file-shield',
        '🪪'  => 'fa-solid fa-id-card',
        '🆔'  => 'fa-solid fa-id-badge',

        // ── Arrows & UI ──────────────────────────────────────────────────────────────
        '⬇️' => 'fa-solid fa-arrow-down',
        '↪️' => 'fa-solid fa-arrow-turn-up',
        '↔️' => 'fa-solid fa-arrows-left-right',
        '↕️' => 'fa-solid fa-arrows-up-down',
        '🔃'  => 'fa-solid fa-arrows-rotate',
        '🔁'  => 'fa-solid fa-repeat',
        '🔂'  => 'fa-solid fa-repeat-1',

        // ── Misc UI ──────────────────────────────────────────────────────────────────
        '🪄'  => 'fa-solid fa-wand-magic-sparkles',
        '🎀'  => 'fa-solid fa-ribbon',
        '🎗️' => 'fa-solid fa-ribbon',
        '🪞'  => 'fa-solid fa-mirror',
        '🗑'  => 'fa-solid fa-trash',
        '📍'  => 'fa-solid fa-map-pin',
        '🔦'  => 'fa-solid fa-flashlight',
        '🕯️' => 'fa-solid fa-candle-holder',
        '🪔'  => 'fa-solid fa-oil-can',
        '🧯'  => 'fa-solid fa-fire-extinguisher',
        '🛗'  => 'fa-solid fa-elevator',
        '🪤'  => 'fa-solid fa-object-ungroup',
        '🪣'  => 'fa-solid fa-bucket',
        '🧲'  => 'fa-solid fa-magnet',
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