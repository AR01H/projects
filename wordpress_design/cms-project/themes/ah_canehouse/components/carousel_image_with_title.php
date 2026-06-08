<?php
/**
 * carousel_image_with_title — reusable image-card carousel: section header + image cards + optional marquee strip.
 *
 * HOW TO USE
 * ──────────
 * Call get_template_part() from any page template or component.
 * Pass all your data through the third argument ($args array).
 * Returns early if 'items' is empty.
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  QUICK EXAMPLE (copy-paste ready)                               │
 * └─────────────────────────────────────────────────────────────────┘
 *
 *  get_template_part( 'components/carousel_image_with_title', null, [
 *
 *    'section_id'    => 'gallery',          // <section id="gallery">
 *    'section_class' => 'my-gallery',       // <section class="my-gallery">
 *    'prefix'        => 'my',               // CSS prefix → my-showcase, my-card …
 *
 *    'tag'   => 'Our Locations',            // eyebrow text above title
 *    'title' => 'Where We <em>Are</em>',    // main heading (HTML tags allowed)
 *    'body'  => 'Find us near you.',        // optional sub-paragraph
 *
 *    'items' => [
 *      [
 *        'image' => 'https://…/photo.jpg',  // card image src
 *        'title' => 'London',               // card heading (h3)
 *        'desc'  => 'City of lights.',      // card sub-text
 *      ],
 *      [ 'image' => '…', 'title' => 'Manchester', 'desc' => '…' ],
 *      [ 'image' => '…', 'title' => 'Bristol',    'desc' => '…' ],
 *    ],
 *
 *    // Optional scrolling marquee strip below the carousel
 *    'marquee_items' => [
 *      [ 'icon' => '📍', 'name' => 'London' ],
 *      [ 'icon' => '📍', 'name' => 'Manchester' ],
 *    ],
 *
 *  ] );
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  ALL AVAILABLE OPTIONS                                          │
 * └─────────────────────────────────────────────────────────────────┘
 *
 *  WRAPPER
 *    'section_id'      string   id on <section>                     default: '{prefix}-section'
 *    'section_class'   string   class on <section>                  default: '{prefix}-section'
 *    'prefix'          string   CSS class prefix for all parts      default: 'ch-ciw'
 *                               → pass 'ch-franchise' to start fresh with that prefix
 *
 *  HEADER  (forwarded to components/section-header)
 *    'tag'             string   Eyebrow label
 *    'title'           string   Main heading (em/strong/span allowed)
 *    'body'            string   Sub-paragraph
 *    'header_class'    string   Extra class on the header wrapper
 *    'dark'            bool     Dark-background colour preset
 *
 *  SHOWCASE CLASSES  (override when reusing an existing stylesheet)
 *    'showcase_class'  string   Class on showcase wrapper            default: '{prefix}-showcase'
 *    'track_class'     string   Class on inner track <div>           default: '{prefix}-showcase-container'
 *    'card_class'      string   Base class on each card              default: '{prefix}-showcase-card'
 *    'card_info_class' string   Class on the info overlay <div>      default: '{prefix}-showcase-info'
 *    'controls_class'  string   Class on controls wrapper            default: '{prefix}-showcase-controls'
 *    'btn_class'       string   Class on prev/next buttons           default: '{prefix}-s-btn'
 *
 *  SHOWCASE IDS  (JS typically hooks on these)
 *    'track_id'        string   id on the track <div>                default: '{prefix}-showcase-track'
 *    'prev_id'         string   id on the ← button                  default: '{prefix}-showcase-prev'
 *    'next_id'         string   id on the → button                  default: '{prefix}-showcase-next'
 *    'prev_label'      string   aria-label on ← button              default: 'Previous'
 *    'next_label'      string   aria-label on → button              default: 'Next'
 *    'show_controls'   bool     Render the prev/next buttons         default: true
 *
 *  CARD ITEM FIELDS  (one array entry per card)
 *    'image'           string   Image URL
 *    'title'           string   Card heading (h3)
 *    'desc'            string   Card sub-text (p)
 *
 *  MARQUEE STRIP  (optional — omit 'marquee_items' to hide entirely)
 *    'marquee_items'        array    List of { icon, name } objects
 *    'default_icon'         string   Fallback icon when item has none  default: '📍'
 *    'marquee_class'        string   Class on marquee <div>            default: '{prefix}-marquee'
 *    'marquee_track_class'  string   Class on marquee inner track      default: '{prefix}-marquee-track'
 *    'marquee_item_class'   string   Class on each item                default: '{prefix}-item'
 *    'marquee_icon_class'   string   Class on icon <span>              default: '{prefix}-icon'
 *    'marquee_name_class'   string   Class on name <span>              default: '{prefix}-name'
 */

defined( 'ABSPATH' ) || exit;

/* ── Read args ─────────────────────────────────────────────────────────────── */

$p = sanitize_html_class( $args['prefix'] ?? 'ch-ciw' );

$section_id    = $args['section_id']    ?? "{$p}-section";
$section_class = $args['section_class'] ?? "{$p}-section";

$dark         = ! empty( $args['dark'] );
$header_class = $args['header_class'] ?? '';

/* Showcase class overrides */
$showcase_class  = $args['showcase_class']  ?? "{$p}-showcase";
$track_class     = $args['track_class']     ?? "{$p}-showcase-container";
$card_class_base = $args['card_class']      ?? "{$p}-showcase-card";
$card_info_class = $args['card_info_class'] ?? "{$p}-showcase-info";
$controls_class  = $args['controls_class']  ?? "{$p}-showcase-controls";
$btn_class       = $args['btn_class']       ?? "{$p}-s-btn";

/* Showcase IDs */
$track_id   = $args['track_id']   ?? "{$p}-showcase-track";
$prev_id    = $args['prev_id']    ?? "{$p}-showcase-prev";
$next_id    = $args['next_id']    ?? "{$p}-showcase-next";
$prev_label = $args['prev_label'] ?? 'Previous';
$next_label = $args['next_label'] ?? 'Next';
$show_controls = isset( $args['show_controls'] ) ? (bool) $args['show_controls'] : true;

$items = is_array( $args['items'] ?? null ) ? $args['items'] : [];

/* Marquee */
$marquee_items       = is_array( $args['marquee_items'] ?? null ) ? $args['marquee_items'] : [];
$default_icon        = $args['default_icon']        ?? '📍';
$marquee_class       = $args['marquee_class']       ?? "{$p}-marquee";
$marquee_track_class = $args['marquee_track_class'] ?? "{$p}-marquee-track";
$marquee_item_class  = $args['marquee_item_class']  ?? "{$p}-item";
$marquee_icon_class  = $args['marquee_icon_class']  ?? "{$p}-icon";
$marquee_name_class  = $args['marquee_name_class']  ?? "{$p}-name";

if ( empty( $items ) ) return;

$item_count = count( $items );
?>

<section id="<?php echo esc_attr( $section_id ); ?>" class="<?php echo esc_attr( $section_class ); ?>">

	<?php
	get_template_part( 'components/section-header', null, [
		'tag'           => $args['tag']   ?? '',
		'title'         => $args['title'] ?? '',
		'body'          => $args['body']  ?? '',
		'dark'          => $dark,
		'wrapper_class' => $header_class,
	] );
	?>

	<!-- ── Showcase carousel ──────────────────────────────────────────────── -->
	<div class="<?php echo esc_attr( $showcase_class ); ?>">
		<div class="<?php echo esc_attr( $track_class ); ?>" id="<?php echo esc_attr( $track_id ); ?>">

			<?php foreach ( $items as $idx => $card ) :
				$card  = (array) $card;
				$cls   = $card_class_base;
				if ( $idx === 0 )                  $cls .= ' active';
				if ( $idx === 1 )                  $cls .= ' next';
				if ( $idx === $item_count - 1 )    $cls .= ' prev';
			?>
				<div class="<?php echo esc_attr( $cls ); ?>" data-index="<?php echo (int) $idx; ?>">
					<img src="<?php echo esc_url( $card['image'] ?? '' ); ?>"
					     alt="<?php echo esc_attr( $card['title'] ?? '' ); ?>"
					     loading="lazy">
					<div class="<?php echo esc_attr( $card_info_class ); ?>">
						<?php if ( ! empty( $card['title'] ) ) : ?>
							<h3><?php echo esc_html( $card['title'] ); ?></h3>
						<?php endif; ?>
						<?php if ( ! empty( $card['desc'] ) ) : ?>
							<p><?php echo esc_html( $card['desc'] ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>

		</div><!-- .track -->

		<?php if ( $show_controls ) : ?>
		<div class="<?php echo esc_attr( $controls_class ); ?>">
			<button class="<?php echo esc_attr( $btn_class ); ?>"
			        id="<?php echo esc_attr( $prev_id ); ?>"
			        aria-label="<?php echo esc_attr( $prev_label ); ?>">←</button>
			<button class="<?php echo esc_attr( $btn_class ); ?>"
			        id="<?php echo esc_attr( $next_id ); ?>"
			        aria-label="<?php echo esc_attr( $next_label ); ?>">→</button>
		</div>
		<?php endif; ?>

	</div><!-- .showcase -->

	<?php if ( $marquee_items ) : ?>
	<!-- ── Marquee strip ──────────────────────────────────────────────────── -->
	<div class="<?php echo esc_attr( $marquee_class ); ?>" aria-hidden="true">
		<div class="<?php echo esc_attr( $marquee_track_class ); ?>">

			<?php
			/* Rendered twice — second pass keeps the infinite-scroll illusion. */
			for ( $pass = 0; $pass < 2; $pass++ ) :
				foreach ( $marquee_items as $loc ) :
					$loc = (array) $loc;
			?>
				<div class="<?php echo esc_attr( $marquee_item_class ); ?>">
					<span class="<?php echo esc_attr( $marquee_icon_class ); ?>"><?php echo esc_html( $loc['icon'] ?? $default_icon ); ?></span>
					<span class="<?php echo esc_attr( $marquee_name_class ); ?>"><?php echo esc_html( $loc['name'] ?? '' ); ?></span>
				</div>
			<?php
				endforeach;
			endfor;
			?>

		</div><!-- .marquee-track -->
	</div><!-- .marquee -->
	<?php endif; ?>

</section>
