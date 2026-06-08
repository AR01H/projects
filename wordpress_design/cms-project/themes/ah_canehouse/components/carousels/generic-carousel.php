<?php
/**
 * Component: Generic Carousel
 * File:      components/generic-carousel.php
 *
 * ── USAGE ────────────────────────────────────────────────────────────────────
 *
 *  get_template_part( 'components/generic-carousel', null, [
 *
 *      // REQUIRED
 *      'items' => [...],          // array of card data (see examples below)
 *      'type'  => 'feature',      // 'image' | 'feature' | 'step' | 'selector'
 *
 *      // LAYOUT (optional)
 *      'visible'    => 3,         // items visible at once (default: 3)
 *      'visible_md' => 2,         // at ≤ 900 px container (default: 2)
 *      'visible_sm' => 1,         // at ≤ 500 px container (default: 1)
 *
 *      // BEHAVIOUR (optional)
 *      'autoplay'      => 4500,   // ms, 0 = off (default: 0)
 *      'loop'          => true,   // loop carousel (default: true)
 *      'floating_nav'  => false,  // arrows float beside track (default: false)
 *      'selector'      => false,  // click-to-select mode (default: false)
 *      'ticker'        => false,  // continuous scroll ticker (default: false)
 *      'ticker_speed'  => 60,     // px/s for ticker (default: 60)
 *      'direction'     => 'horizontal', // 'horizontal' | 'vertical'
 *
 *      // STYLE OVERRIDES (optional)
 *      'css_vars' => [
 *          '--cc-card-bg'      => '#fff',
 *          '--cc-title-color'  => '#2d5a1b',
 *          // any --cc-* variable from carousel.css
 *      ],
 *
 *      // EXTRA CLASS (optional)
 *      'class' => 'my-custom-class',
 *
 *  ] );
 *
 * ── ITEM SHAPES ──────────────────────────────────────────────────────────────
 *
 *  TYPE: 'image'
 *      [ 'image' => 'url', 'title' => '', 'subtitle' => '' ]
 *
 *  TYPE: 'feature'
 *      [ 'icon' => '🌿', 'icon_type' => 'emoji', // or 'img' for <img src>
 *        'title' => '', 'text' => '',
 *        'left' => false,                          // left-align content
 *        'border_top_color' => '#4a8c2a',          // optional accent top border
 *        'checklist' => ['Item 1', 'Item 2'] ]     // optional bullet list
 *
 *  TYPE: 'step'
 *      [ 'step' => '1', 'icon' => '📞', 'title' => '', 'text' => '' ]
 *
 *  TYPE: 'selector'
 *      [ 'icon' => '🍋', 'label' => 'Lemon', 'value' => 'lemon' ]
 *
 * ── EXAMPLES ─────────────────────────────────────────────────────────────────
 *
 *  // Full-width - 3 image cards
 *  get_template_part( 'components/generic-carousel', null, [
 *      'type'     => 'image',
 *      'visible'  => 3,
 *      'autoplay' => 4500,
 *      'items'    => [
 *          [ 'image' => get_template_directory_uri() . '/assets/images/a.jpg', 'title' => 'Commercial Press', 'subtitle' => 'Stainless steel' ],
 *          [ 'image' => get_template_directory_uri() . '/assets/images/b.jpg', 'title' => 'Event Stall',       'subtitle' => 'Mobile setup'    ],
 *          [ 'image' => get_template_directory_uri() . '/assets/images/c.jpg', 'title' => 'Live Press',        'subtitle' => 'Fresh to order'  ],
 *      ],
 *  ] );
 *
 *  // Inside a narrow grid column - 1 feature card visible, auto-detects container
 *  get_template_part( 'components/generic-carousel', null, [
 *      'type'       => 'feature',
 *      'visible'    => 1,
 *      'visible_md' => 1,
 *      'visible_sm' => 1,
 *      'items'    => [
 *          [ 'icon' => '💒', 'title' => 'Weddings', 'text' => 'Live juice at your reception.' ],
 *          [ 'icon' => '🏛️', 'title' => 'Corporate', 'text' => 'Wellness days and conferences.' ],
 *      ],
 *  ] );
 *
 *  // Blend selector - inside any column width
 *  get_template_part( 'components/generic-carousel', null, [
 *      'type'     => 'selector',
 *      'visible'  => 3,
 *      'selector' => true,
 *      'items'    => [
 *          [ 'icon' => '🌿', 'label' => 'Pure Cane', 'value' => 'pure-cane' ],
 *          [ 'icon' => '🍋', 'label' => 'Lemon',     'value' => 'lemon'     ],
 *          [ 'icon' => '🫚', 'label' => 'Ginger',    'value' => 'ginger'    ],
 *      ],
 *      'css_vars' => [
 *          '--cc-card-bg'            => '#fff',
 *          '--cc-card-border'        => '1px solid var(--client-color-4)',
 *          '--cc-card-border-active' => '2px solid var(--client-color-2)',
 *          '--cc-card-bg-active'     => 'var(--client-color-6)',
 *          '--cc-card-cursor'        => 'pointer',
 *      ],
 *  ] );
 *
 * @package YourTheme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ══════════════════════════════════════════════════════════════════════════════
   CARD RENDERER - self-contained, no external dependency needed.
   Extend the switch below to add more card types.
   ══════════════════════════════════════════════════════════════════════════════ */
if ( ! function_exists( 'ch_carousel_render_card' ) ) {
    function ch_carousel_render_card( array $item, string $type ): string {
        switch ( $type ) {

            /* ── image ─────────────────────────────────────────────────────── */
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

            /* ── feature (icon + title + text ± checklist) ─────────────────── */
            case 'feature':
                $icon       = $item['icon'] ?? '';
                $icon_type  = $item['icon_type'] ?? 'emoji'; // 'emoji' | 'img'
                $title      = esc_html( $item['title'] ?? '' );
                $text       = esc_html( $item['text'] ?? '' );
                $checks     = (array) ( $item['checklist'] ?? [] );
                $align_cls  = ! empty( $item['left'] ) ? ' ch-card--left' : '';
                $border_top = isset( $item['border_top_color'] )
                    ? ' style="--cc-card-border-top:3px solid ' . esc_attr( $item['border_top_color'] ) . '"'
                    : '';

                // icon html
                $icon_html = '';
                if ( $icon ) {
                    if ( $icon_type === 'img' ) {
                        $icon_html = '<div class="ch-card__icon"><img src="' . esc_url( $icon ) . '" alt=""></div>';
                    } else {
                        $icon_html = '<div class="ch-card__icon">' . esc_html( $icon ) . '</div>';
                    }
                }

                // checklist html
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
                    $align_cls, $border_top, $icon_html, $title, $text, $check_html
                );

            /* ── step ──────────────────────────────────────────────────────── */
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

            /* ── selector / picker ─────────────────────────────────────────── */
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
}

/* ── resolve args ──────────────────────────────────────────────────────────── */
$items = $args['items'] ?? [];
$type  = $args['type']  ?? 'feature';

if ( empty( $items ) || ! is_array( $items ) ) return;

$visible    = (int) ( $args['visible']    ?? 3 );
$visible_md = (int) ( $args['visible_md'] ?? 2 );
$visible_sm = (int) ( $args['visible_sm'] ?? 1 );
$autoplay   = (int) ( $args['autoplay']   ?? 0 );
$loop       = isset( $args['loop'] ) ? (bool) $args['loop'] : true;
$float_nav    = ! empty( $args['floating_nav'] );
$selector     = ! empty( $args['selector'] );
$ticker       = ! empty( $args['ticker'] );
$ticker_speed = (int) ( $args['ticker_speed'] ?? 60 );
$direction    = ( isset( $args['direction'] ) && $args['direction'] === 'vertical' ) ? 'vertical' : 'horizontal';
$css_vars     = (array) ( $args['css_vars'] ?? [] );
$extra_class  = isset( $args['class'] ) ? ' ' . esc_attr( $args['class'] ) : '';

/* ── inline CSS variables ──────────────────────────────────────────────────── */
$vars = array_merge(
    [
        '--cc-items-visible'    => $visible,
        '--cc-items-visible-md' => $visible_md,
        '--cc-items-visible-sm' => $visible_sm,
    ],
    $css_vars
);

$style = implode( ';', array_map(
    fn( $k, $v ) => esc_attr( $k ) . ':' . esc_attr( $v ),
    array_keys( $vars ),
    $vars
) );

/* ── wrapper classes ───────────────────────────────────────────────────────── */
$classes = 'ch-carousel';
if ( $float_nav )                $classes .= ' ch-carousel--floating-nav';
if ( $ticker )                   $classes .= ' ch-carousel--ticker';
if ( $direction === 'vertical' ) $classes .= ' ch-carousel--vertical';
if ( $extra_class )              $classes .= $extra_class;

/* ── nav visibility ────────────────────────────────────────────────────────── */
// 'auto'  = show when items > visible, hide when items ≤ visible (default)
// 'show'  = always show
// 'hide'  = always hide
$nav_mode = $args['nav'] ?? 'auto';

/* ── data attributes ───────────────────────────────────────────────────────── */
$data  = '';
if ( $autoplay )                 $data .= ' data-autoplay="' . $autoplay . '"';
if ( ! $loop )                   $data .= ' data-loop="false"';
if ( $selector )                 $data .= ' data-selector="true"';
if ( $ticker )                   $data .= ' data-ticker="true" data-ticker-speed="' . $ticker_speed . '"';
if ( $direction === 'vertical' ) $data .= ' data-direction="vertical"';
if ( $nav_mode === 'hide' )      $data .= ' data-nav="hide"';
if ( $nav_mode === 'show' )      $data .= ' data-nav="show"';

/* ── unique ID for JS targeting ────────────────────────────────────────────── */
static $cc_instance = 0;
$cc_instance++;
$carousel_id = 'ch-carousel-' . $cc_instance;
?>
<div id="<?php echo esc_attr( $carousel_id ); ?>"
     class="<?php echo esc_attr( $classes ); ?>"
     style="<?php echo $style; ?>"
     <?php echo $data; ?>>

    <div class="ch-carousel__viewport">
        <div class="ch-carousel__track" style="<?php echo $style; ?>">
            <?php foreach ( $items as $item ) : ?>
                <div class="ch-carousel__item">
                    <?php echo ch_carousel_render_card( (array) $item, $type ); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ( count( $items ) > 1 ) : ?>
        <nav class="ch-carousel__nav"
             aria-label="<?php esc_attr_e( 'Carousel navigation', 'your-theme' ); ?>">

            <button type="button"
                    class="ch-carousel__arrow"
                    data-dir="prev"
                    aria-label="<?php esc_attr_e( 'Previous', 'your-theme' ); ?>">&#8249;</button>

            <div class="ch-carousel__dots">
                <?php foreach ( $items as $i => $_ ) : ?>
                    <button type="button"
                            class="ch-carousel__dot<?php echo $i === 0 ? ' is-active' : ''; ?>"
                            aria-label="<?php echo esc_attr( sprintf( __( 'Slide %d', 'your-theme' ), $i + 1 ) ); ?>">
                    </button>
                <?php endforeach; ?>
            </div>

            <button type="button"
                    class="ch-carousel__arrow"
                    data-dir="next"
                    aria-label="<?php esc_attr_e( 'Next', 'your-theme' ); ?>">&#8250;</button>

        </nav>
    <?php endif; ?>

</div>