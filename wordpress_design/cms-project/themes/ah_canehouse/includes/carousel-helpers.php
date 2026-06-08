<?php
/**
 * CH Carousel - Helpers
 *
 * Include in functions.php:
 *   require_once get_template_directory() . '/includes/carousel-helpers.php';
 *
 * @package YourTheme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ── 1. ENQUEUE ────────────────────────────────────────────────────────────────

function ch_carousel_enqueue_assets() {
    $dir = get_template_directory_uri() . '/assets';
    $ver = wp_get_theme()->get( 'Version' );

    wp_enqueue_style(
        'ch-carousel',
        $dir . '/css/carousel.css',
        [],
        $ver
    );
    wp_enqueue_script(
        'ch-carousel',
        $dir . '/js/carousel.js',
        [],
        $ver,
        true  // footer
    );
}
add_action( 'wp_enqueue_scripts', 'ch_carousel_enqueue_assets' );


// ── 2. RENDER FUNCTION ────────────────────────────────────────────────────────

/**
 * Render a CH Carousel.
 *
 * @param array  $items    Array of card data (shape depends on $type).
 * @param string $type     'image' | 'feature' | 'step' | 'selector'
 * @param array  $config   Optional overrides:
 *   - visible      int    Cards visible at once (default 3)
 *   - visible_md   int    Cards at ≤900 px (default 2)
 *   - visible_sm   int    Cards at ≤600 px (default 1)
 *   - autoplay     int    Autoplay ms, 0 = off (default 0)
 *   - loop         bool   Loop carousel (default true)
 *   - floating_nav bool   Arrows float beside track (default false)
 *   - selector     bool   Enable selector/picker mode (default false)
 *   - css_vars     array  Extra CSS variable overrides [ '--cc-card-bg' => '#fff', ... ]
 *   - wrapper_class string  Extra class on .ch-carousel
 *
 * @return string HTML
 */
function render_carousel( array $items, string $type = 'feature', array $config = [] ): string {
    if ( empty( $items ) ) return '';

    $cfg = wp_parse_args( $config, [
        'visible'       => 3,
        'visible_md'    => 2,
        'visible_sm'    => 1,
        'autoplay'      => 0,
        'loop'          => true,
        'floating_nav'  => false,
        'selector'      => false,
        'css_vars'      => [],
        'wrapper_class' => '',
    ] );

    // ── inline CSS variables ──────────────────────────────────────────────────
    $inline_vars = array_merge(
        [
            '--cc-items-visible'    => (int) $cfg['visible'],
            '--cc-items-visible-md' => (int) $cfg['visible_md'],
            '--cc-items-visible-sm' => (int) $cfg['visible_sm'],
        ],
        (array) $cfg['css_vars']
    );
    $style_attr = ch_carousel_vars_to_style( $inline_vars );

    // ── wrapper classes ───────────────────────────────────────────────────────
    $classes = [ 'ch-carousel' ];
    if ( $cfg['floating_nav'] ) $classes[] = 'ch-carousel--floating-nav';
    if ( $cfg['wrapper_class'] ) $classes[] = esc_attr( $cfg['wrapper_class'] );

    // ── data attributes ───────────────────────────────────────────────────────
    $data_attrs  = '';
    if ( $cfg['autoplay'] )  $data_attrs .= ' data-autoplay="' . (int) $cfg['autoplay'] . '"';
    if ( ! $cfg['loop'] )    $data_attrs .= ' data-loop="false"';
    if ( $cfg['selector'] )  $data_attrs .= ' data-selector="true"';

    // ── render items ──────────────────────────────────────────────────────────
    $items_html = '';
    foreach ( $items as $item ) {
        $items_html .= '<div class="ch-carousel__item">';
        $items_html .= ch_carousel_render_card( (array) $item, $type );
        $items_html .= '</div>';
    }

    // ── dots ──────────────────────────────────────────────────────────────────
    $dots_html = '';
    foreach ( $items as $i => $_ ) {
        $active      = $i === 0 ? ' is-active' : '';
        $dots_html  .= '<button type="button" class="ch-carousel__dot' . $active . '" aria-label="' . esc_attr( sprintf( __( 'Go to slide %d', 'your-theme' ), $i + 1 ) ) . '"></button>';
    }

    // ── output ────────────────────────────────────────────────────────────────
    ob_start();
    ?>
    <div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
         style="<?php echo esc_attr( $style_attr ); ?>"
         <?php echo $data_attrs; ?>>

        <div class="ch-carousel__viewport">
            <div class="ch-carousel__track">
                <?php echo $items_html; ?>
            </div>
        </div>

        <nav class="ch-carousel__nav" aria-label="<?php esc_attr_e( 'Carousel navigation', 'your-theme' ); ?>">
            <button type="button" class="ch-carousel__arrow" data-dir="prev" aria-label="<?php esc_attr_e( 'Previous', 'your-theme' ); ?>">&#8249;</button>
            <div class="ch-carousel__dots"><?php echo $dots_html; ?></div>
            <button type="button" class="ch-carousel__arrow" data-dir="next" aria-label="<?php esc_attr_e( 'Next', 'your-theme' ); ?>">&#8250;</button>
        </nav>

    </div>
    <?php
    return ob_get_clean();
}


// ── 3. CARD RENDERER ──────────────────────────────────────────────────────────

/**
 * Render a single card by type.
 * Extend this function to add more card variants.
 *
 * @param array  $item  Card data.
 * @param string $type  Card type.
 * @return string HTML
 */
function ch_carousel_render_card( array $item, string $type ): string {
    switch ( $type ) {

        /* ── Image card ──────────────────────────────────────────────────── */
        case 'image':
            $img_url  = esc_url( $item['image'] ?? '' );
            $img_alt  = esc_attr( $item['title'] ?? '' );
            $title    = esc_html( $item['title'] ?? '' );
            $subtitle = esc_html( $item['subtitle'] ?? '' );
            return sprintf(
                '<div class="ch-card ch-card--image">
                    <img src="%s" alt="%s" class="ch-card__img" loading="lazy">
                    <div class="ch-card__overlay"></div>
                    <div class="ch-card__caption">
                        <h3 class="ch-card__title">%s</h3>
                        %s
                    </div>
                </div>',
                $img_url,
                $img_alt,
                $title,
                $subtitle ? '<p class="ch-card__subtitle">' . $subtitle . '</p>' : ''
            );

        /* ── Feature card (icon + title + text ± checklist) ─────────────── */
        case 'feature':
            $icon      = $item['icon'] ?? '';
            $icon_type = $item['icon_type'] ?? 'emoji'; // 'emoji' or 'img'
            $title     = esc_html( $item['title'] ?? '' );
            $text      = esc_html( $item['text'] ?? '' );
            $checks    = (array) ( $item['checklist'] ?? [] );
            $align     = ! empty( $item['left'] ) ? ' ch-card--left' : '';
            $border_top= isset( $item['border_top_color'] )
                ? ' style="--cc-card-border-top:3px solid ' . esc_attr( $item['border_top_color'] ) . '"'
                : '';

            $icon_html = '';
            if ( $icon ) {
                if ( $icon_type === 'img' ) {
                    $icon_html = '<div class="ch-card__icon"><img src="' . esc_url( $icon ) . '" alt=""></div>';
                } else {
                    $icon_html = '<div class="ch-card__icon">' . esc_html( $icon ) . '</div>';
                }
            }

            $check_html = '';
            if ( $checks ) {
                $check_html = '<ul class="ch-card__checklist">';
                foreach ( $checks as $c ) {
                    $check_html .= '<li>' . esc_html( $c ) . '</li>';
                }
                $check_html .= '</ul>';
            }

            return sprintf(
                '<div class="ch-card ch-card--feature%s"%s>%s<h3 class="ch-card__title">%s</h3><p class="ch-card__text">%s</p>%s</div>',
                $align, $border_top, $icon_html, $title, $text, $check_html
            );

        /* ── Step card ───────────────────────────────────────────────────── */
        case 'step':
            $step  = esc_html( $item['step'] ?? '' );
            $icon  = $item['icon'] ?? '';
            $title = esc_html( $item['title'] ?? '' );
            $text  = esc_html( $item['text'] ?? '' );

            $icon_html = $icon
                ? '<div class="ch-card__icon">' . esc_html( $icon ) . '</div>'
                : '';

            return sprintf(
                '<div class="ch-card ch-card--step">
                    <div class="ch-card__badge">%s</div>
                    %s
                    <h3 class="ch-card__title">%s</h3>
                    <p class="ch-card__text">%s</p>
                </div>',
                $step, $icon_html, $title, $text
            );

        /* ── Selector / picker card ───────────────────────────────────────── */
        case 'selector':
            $icon  = $item['icon'] ?? '';
            $label = esc_html( $item['label'] ?? '' );
            $value = esc_attr( $item['value'] ?? $label );

            return sprintf(
                '<div class="ch-card ch-card--feature ch-card--selector" data-value="%s">
                    <div class="ch-card__icon">%s</div>
                    <h3 class="ch-card__title">%s</h3>
                </div>',
                $value, esc_html( $icon ), $label
            );

        default:
            return '';
    }
}


// ── 4. UTILITY ────────────────────────────────────────────────────────────────

/**
 * Convert an array of CSS variable key→value pairs into a style string.
 *
 * @param  array  $vars  e.g. [ '--cc-card-bg' => '#fff' ]
 * @return string        e.g. "--cc-card-bg:#fff;"
 */
function ch_carousel_vars_to_style( array $vars ): string {
    $parts = [];
    foreach ( $vars as $prop => $val ) {
        if ( $val !== null && $val !== '' ) {
            $parts[] = esc_attr( $prop ) . ':' . esc_attr( $val );
        }
    }
    return implode( ';', $parts );
}