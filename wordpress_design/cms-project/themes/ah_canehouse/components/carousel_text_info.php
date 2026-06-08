<?php
/**
 * carousel_text_info — reusable carousel: section header + icon cards + dots/arrows.
 *
 * HOW TO USE
 * ──────────
 * Call get_template_part() from any page template or component.
 * Pass all your data through the third argument ($args array).
 * The component renders nothing and returns early if 'items' is empty.
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  QUICK EXAMPLE (copy-paste ready)                               │
 * └─────────────────────────────────────────────────────────────────┘
 *
 *  get_template_part( 'components/carousel_text_info', null, [
 *
 *    'section_id'    => 'my-section',      // <section id="my-section">
 *    'section_class' => 'my-section',      // <section class="my-section">
 *    'prefix'        => 'my',              // CSS prefix → my-carousel, my-card …
 *
 *    'tag'           => 'What We Offer',   // small eyebrow text above title
 *    'title'         => 'Our Services',    // main heading
 *    'body'          => 'A short line.',   // optional paragraph under heading
 *
 *    'items_visible' => 3,                 // how many cards visible at once
 *
 *    'items' => [
 *      [
 *        'icon'  => '🎉',                  // emoji shown at top of card
 *        'title' => 'Birthdays',           // card heading
 *        'desc'  => 'Make it special.',    // card paragraph
 *        'items' => [                      // tick list inside the card
 *          '2-hour session',
 *          'Custom menu',
 *          'Staff included',
 *        ],
 *        'cta_text' => 'Book Now',         // optional button (needs cta_url too)
 *        'cta_url'  => '/contact/',
 *      ],
 *      [ 'icon' => '🌿', 'title' => 'Festivals', 'desc' => '...', 'items' => [] ],
 *      [ 'icon' => '💍', 'title' => 'Weddings',  'desc' => '...', 'items' => [] ],
 *    ],
 *
 *  ] );
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  ALL AVAILABLE OPTIONS                                          │
 * └─────────────────────────────────────────────────────────────────┘
 *
 *  WRAPPER
 *    'section_id'      string   id on <section>                 default: '{prefix}-section'
 *    'section_class'   string   class on <section>              default: '{prefix}-section'
 *    'container_class' string   class on inner <div>            default: '{prefix}-container'
 *    'prefix'          string   CSS class prefix for all parts  default: 'ch-cti'
 *                               → pass 'ch-hire' to reuse existing ch-hire-* stylesheet
 *
 *  HEADER  (forwarded to components/section-header)
 *    'tag'             string   Eyebrow label
 *    'title'           string   Main heading (span/em/strong allowed)
 *    'body'            string   Sub-paragraph
 *    'header_class'    string   Extra class on the header wrapper
 *    'dark'            bool     Dark-background colour preset
 *
 *  CAROUSEL
 *    'carousel_id'     string   id on carousel wrapper          default: '{prefix}-carousel'
 *    'track_id'        string   id on the sliding track         default: '{prefix}-track'
 *    'dots_id'         string   id on the dots row              default: '{prefix}-dots'
 *    'items_visible'   int      Cards visible at once           default: 3
 *
 *  CARD ELEMENT CLASS OVERRIDES
 *    'card_icon_class' string   Class on the icon <div>         default: '{prefix}-card-icon'
 *    'card_list_class' string   Class on the <ul>               default: '{prefix}-card-list'
 *                               → pass 'ch-h-card-list' to get the ✓ tick style
 *
 *  CARD ITEM FIELDS  (one array entry per card)
 *    'icon'            string   Emoji at top of card
 *    'title'           string   Card heading  (h3)
 *    'desc'            string   Short paragraph
 *    'items'           array    List of strings — styled by card_list_class CSS
 *    'color'           string   Accent CSS value  e.g. '#e74c3c'
 *    'cta_text'        string   Button label  (only shown when cta_url is also set)
 *    'cta_url'         string   Button URL
 *    'card_class'      string   Extra class added to this card only
 *
 *  NAVIGATION
 *    'nav_label'       string   aria-label on dots tablist      default: 'Carousel navigation'
 *    'show_dots'       bool     Show dot buttons                default: true
 *    'show_arrows'     bool     Show ← → arrow buttons          default: true
 *    'prev_label'      string   aria-label on ← button          default: 'Previous'
 *    'next_label'      string   aria-label on → button          default: 'Next'
 */

defined( 'ABSPATH' ) || exit;

/* ── Read args ─────────────────────────────────────────────────────────────── */

/* CSS prefix used for all variant classes — defaults to 'ch-cti'.
 * Pass 'prefix' => 'ch-hire' to reuse the existing ch-hire-* stylesheet. */
$p = sanitize_html_class( $args['prefix'] ?? 'ch-cti' );

$section_id      = $args['section_id']      ?? "{$p}-section";
$section_class   = $args['section_class']   ?? "{$p}-section";
$container_class = $args['container_class'] ?? "{$p}-container";

$header_class    = $args['header_class']    ?? '';
$dark            = ! empty( $args['dark'] );

$carousel_id     = $args['carousel_id']     ?? "{$p}-carousel";
$track_id        = $args['track_id']        ?? "{$p}-track";
$dots_id         = $args['dots_id']         ?? "{$p}-dots";
$items_visible     = (int) ( $args['items_visible'] ?? 3 );

/* Per-element class overrides — useful when the stylesheet uses a different
 * naming convention from the prefix pattern (e.g. ch-h-card-icon vs ch-hire-card-icon). */
$card_icon_class   = $args['card_icon_class'] ?? "{$p}-card-icon";
$card_list_class   = $args['card_list_class'] ?? "{$p}-card-list";

$items             = is_array( $args['items'] ?? null ) ? $args['items'] : [];

$nav_label       = $args['nav_label']       ?? 'Carousel navigation';
$show_arrows     = isset( $args['show_arrows'] ) ? (bool) $args['show_arrows'] : true;
$show_dots       = isset( $args['show_dots'] )   ? (bool) $args['show_dots']   : true;
$prev_label      = $args['prev_label']      ?? 'Previous';
$next_label      = $args['next_label']      ?? 'Next';

if ( empty( $items ) ) return;
?>

<section id="<?php echo esc_attr( $section_id ); ?>" class="<?php echo esc_attr( $section_class ); ?>">
	<div class="<?php echo esc_attr( $container_class ); ?>">

		<?php
		/* ── Section header ─────────────────────────────────────────────────── */
		get_template_part( 'components/section-header', null, [
			'tag'           => $args['tag']   ?? '',
			'title'         => $args['title'] ?? '',
			'body'          => $args['body']  ?? '',
			'dark'          => $dark,
			'wrapper_class' => $header_class,
		] );
		?>

		<!-- ── Carousel ──────────────────────────────────────────────────────── -->
		<div class="<?php echo esc_attr( $p ); ?>-carousel ch-carousel"
		     id="<?php echo esc_attr( $carousel_id ); ?>"
		     style="--cc-items-visible:<?php echo $items_visible; ?>">

			<div class="ch-carousel__viewport">
				<div class="<?php echo esc_attr( $p ); ?>-track ch-carousel__track" id="<?php echo esc_attr( $track_id ); ?>">

					<?php foreach ( $items as $i => $item ) :
						$item       = (array) $item;
						$icon       = $item['icon']       ?? '';
						$title      = $item['title']      ?? '';
						$desc       = $item['desc']       ?? '';
						$list_items = is_array( $item['items'] ?? null ) ? $item['items'] : [];
						$color      = $item['color']      ?? '';
						$cta_text   = $item['cta_text']   ?? '';
						$cta_url    = $item['cta_url']    ?? '';
						$card_class = $item['card_class'] ?? '';
					?>
					<div class="<?php echo esc_attr( $p ); ?>-card ch-carousel__item fade-up<?php echo $card_class ? ' ' . esc_attr( $card_class ) : ''; ?>"
					     <?php if ( $color ) echo 'style="--cti-accent:' . esc_attr( $color ) . '"'; ?>>

						<?php if ( $icon ) : ?>
							<div class="<?php echo esc_attr( $card_icon_class ); ?>" aria-hidden="true">
								<?php echo esc_html( $icon ); ?>
							</div>
						<?php endif; ?>

						<?php if ( $title ) : ?>
							<h3><?php echo esc_html( $title ); ?></h3>
						<?php endif; ?>

						<?php if ( $desc ) : ?>
							<p><?php echo esc_html( $desc ); ?></p>
						<?php endif; ?>

						<?php if ( $list_items ) : ?>
							<ul class="<?php echo esc_attr( $card_list_class ); ?>">
								<?php foreach ( $list_items as $li ) : ?>
									<li><?php echo esc_html( $li ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>

						<?php if ( $cta_text && $cta_url ) : ?>
							<a href="<?php echo esc_url( $cta_url ); ?>"
							   class="<?php echo esc_attr( $p ); ?>-card-cta btn-lime">
								<?php echo esc_html( $cta_text ); ?>
							</a>
						<?php endif; ?>

					</div><!-- .{p}-card -->
					<?php endforeach; ?>

				</div><!-- .{p}-track -->
			</div><!-- .ch-carousel__viewport -->

			<?php if ( $show_dots || $show_arrows ) : ?>
			<!-- Dots + arrows -->
			<div class="<?php echo esc_attr( $p ); ?>-nav ch-carousel__nav">

				<?php if ( $show_dots ) : ?>
				<div class="<?php echo esc_attr( $p ); ?>-dots ch-carousel__dots"
				     id="<?php echo esc_attr( $dots_id ); ?>"
				     role="tablist"
				     aria-label="<?php echo esc_attr( $nav_label ); ?>">
					<?php foreach ( $items as $i => $_ ) : ?>
						<button class="ch-dot ch-carousel__dot"
						        role="tab"
						        aria-label="<?php echo esc_attr( ( $i + 1 ) . ' of ' . count( $items ) ); ?>">
						</button>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>

				<?php if ( $show_arrows ) : ?>
				<div class="<?php echo esc_attr( $p ); ?>-arrows ch-carousel__arrows">
					<button class="ch-v-btn ch-carousel__arrow"
					        data-dir="prev"
					        aria-label="<?php echo esc_attr( $prev_label ); ?>">←</button>
					<button class="ch-v-btn ch-carousel__arrow"
					        data-dir="next"
					        aria-label="<?php echo esc_attr( $next_label ); ?>">→</button>
				</div>
				<?php endif; ?>

			</div><!-- .{p}-nav -->
			<?php endif; ?>

		</div><!-- .{p}-carousel -->

	</div><!-- .container -->
</section>
