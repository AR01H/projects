<?php
/**
 * carousel_mini_grid_with_badge_container — mini-card carousel in a two-column layout:
 * left = card carousel (icon + title + desc + optional badge), right = visual panel (image + label).
 *
 * HOW TO USE
 * ──────────
 * Call get_template_part() from any page template or component.
 * Pass all data through the third argument ($args array).
 * Returns early if 'items' is empty.
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  QUICK EXAMPLE (copy-paste ready)                               │
 * └─────────────────────────────────────────────────────────────────┘
 *
 *  get_template_part( 'components/carousel_mini_grid_with_badge_container', null, [
 *
 *    'section_id'    => 'certifications',    // <section id="certifications">
 *    'section_class' => 'my-certs-section',  // <section class="...">
 *    'prefix'        => 'my-certs',          // CSS prefix → my-certs-carousel, my-certs-card …
 *
 *    'tag'   => 'Official & Verified',       // eyebrow text
 *    'title' => 'Our Certifications',        // main heading
 *    'body'  => 'Fully compliant.',          // optional sub-paragraph
 *
 *    'items' => [
 *      [
 *        'icon'  => '✅',                    // emoji or text icon at card left
 *        'title' => 'Food Hygiene',          // card heading
 *        'desc'  => 'Registered with NCASS.',// card sub-text
 *        'badge' => 'Level 5',              // optional badge pill (omit or empty to hide)
 *      ],
 *      [ 'icon' => '🛡️', 'title' => 'Public Liability', 'desc' => '£5M cover.', 'badge' => '' ],
 *    ],
 *
 *    // Optional right-side visual panel
 *    'visual_image' => get_template_directory_uri() . '/assets/images/ncass_logo.png',
 *    'visual_alt'   => 'NCASS logo',
 *    'visual_label' => 'We are officially a member of',
 *
 *  ] );
 *
 * ┌─────────────────────────────────────────────────────────────────┐
 * │  ALL AVAILABLE OPTIONS                                          │
 * └─────────────────────────────────────────────────────────────────┘
 *
 *  WRAPPER
 *    'section_id'      string   id on <section>                       default: '{prefix}-section'
 *    'section_class'   string   class on <section>                    default: '{prefix}-section'
 *    'container_class' string   class on inner container <div>        default: 'container'
 *    'layout_class'    string   class on the two-column layout <div>  default: '{prefix}-layout'
 *    'prefix'          string   CSS class prefix for all parts        default: 'ch-mini'
 *
 *  HEADER  (forwarded to components/section-header)
 *    'tag'             string   Eyebrow label
 *    'title'           string   Main heading (em/strong/span allowed)
 *    'body'            string   Sub-paragraph
 *    'header_class'    string   Extra class on the header wrapper
 *    'dark'            bool     Dark-background colour preset
 *
 *  CAROUSEL CLASSES  (override when reusing an existing stylesheet)
 *    'carousel_class'  string   Class on carousel wrapper             default: '{prefix}-carousel'
 *    'track_class'     string   Class on the sliding track            default: '{prefix}-track'
 *    'card_class'      string   Base class on each card               default: '{prefix}-card'
 *    'card_icon_class' string   Class on the icon <div>               default: '{prefix}-card-icon'
 *    'card_body_class' string   Class on the text body <div>          default: '{prefix}-card-body'
 *    'card_title_class'string   Class on the title <div>              default: '{prefix}-card-title'
 *    'card_desc_class' string   Class on the desc <div>               default: '{prefix}-card-desc'
 *    'card_badge_class'string   Class on the badge <span>             default: '{prefix}-card-badge'
 *
 *  CAROUSEL IDS
 *    'carousel_id'     string   id on carousel wrapper                default: '{prefix}-carousel'
 *    'track_id'        string   id on the track                       default: '{prefix}-track'
 *
 *  NAVIGATION CLASSES
 *    'nav_class'       string   Class on nav wrapper                  default: '{prefix}-nav'
 *    'dots_class'      string   Class on dots row                     default: '{prefix}-dots'
 *    'dot_class'       string   Class on each dot button              default: 'ch-dot'
 *    'arrows_class'    string   Class on arrows wrapper               default: '{prefix}-arrows'
 *    'btn_class'       string   Class on prev/next buttons            default: 'ch-v-btn'
 *
 *  NAVIGATION IDS + LABELS
 *    'dots_id'         string   id on dots row                        default: '{prefix}-dots'
 *    'prev_id'         string   id on ← button                       default: '{prefix}-prev'
 *    'next_id'         string   id on → button                       default: '{prefix}-next'
 *    'nav_label'       string   aria-label on dots tablist            default: 'Carousel navigation'
 *    'prev_label'      string   aria-label on ← button               default: 'Previous'
 *    'next_label'      string   aria-label on → button               default: 'Next'
 *    'show_dots'       bool     Render the dot buttons                default: true
 *    'show_arrows'     bool     Render the ← → buttons               default: true
 *
 *  CARD ITEM FIELDS  (one array entry per card)
 *    'icon'            string   Emoji or text icon
 *    'title'           string   Card heading
 *    'desc'            string   Card sub-text
 *    'badge'           string   Badge pill text  (hidden when empty or not set)
 *    'default_icon'    string   Fallback icon when item has none      default: '✅'
 *
 *  VISUAL PANEL  (right column — omit 'visual_image' to hide entirely)
 *    'visual_image'        string   Image URL
 *    'visual_alt'          string   Image alt text                    default: ''
 *    'visual_label'        string   Text shown below image            default: ''
 *    'visual_class'        string   Class on visual wrapper <div>     default: '{prefix}-visual'
 *    'visual_img_class'    string   Class on <img>                    default: '{prefix}-img'
 *    'visual_badge_class'  string   Class on label <span>             default: '{prefix}-badge'
 *    'visual_extra_class'  string   Extra class on visual wrapper     default: 'fade-right'
 */

defined( 'ABSPATH' ) || exit;

/* ── Read args ─────────────────────────────────────────────────────────────── */

$p = sanitize_html_class( $args['prefix'] ?? 'ch-mini' );

$section_id      = $args['section_id']      ?? "{$p}-section";
$section_class   = $args['section_class']   ?? "{$p}-section";
$container_class = $args['container_class'] ?? 'container';
$layout_class    = $args['layout_class']    ?? "{$p}-layout";

$dark         = ! empty( $args['dark'] );
$header_class = $args['header_class'] ?? '';

/* Carousel structure */
$carousel_class = $args['carousel_class'] ?? "{$p}-carousel";
$track_class    = $args['track_class']    ?? "{$p}-track";
$carousel_id    = $args['carousel_id']    ?? "{$p}-carousel";
$track_id       = $args['track_id']       ?? "{$p}-track";

/* Card classes */
$card_class        = $args['card_class']        ?? "{$p}-card";
$card_icon_class   = $args['card_icon_class']   ?? "{$p}-card-icon";
$card_body_class   = $args['card_body_class']   ?? "{$p}-card-body";
$card_title_class  = $args['card_title_class']  ?? "{$p}-card-title";
$card_desc_class   = $args['card_desc_class']   ?? "{$p}-card-desc";
$card_badge_class  = $args['card_badge_class']  ?? "{$p}-card-badge";
$default_icon      = $args['default_icon']      ?? '✅';

/* Navigation */
$nav_class    = $args['nav_class']    ?? "{$p}-nav";
$dots_class   = $args['dots_class']   ?? "{$p}-dots";
$dot_class    = $args['dot_class']    ?? 'ch-dot';
$arrows_class = $args['arrows_class'] ?? "{$p}-arrows";
$btn_class    = $args['btn_class']    ?? 'ch-v-btn';
$dots_id      = $args['dots_id']      ?? "{$p}-dots";
$prev_id      = $args['prev_id']      ?? "{$p}-prev";
$next_id      = $args['next_id']      ?? "{$p}-next";
$nav_label    = $args['nav_label']    ?? 'Carousel navigation';
$prev_label   = $args['prev_label']   ?? 'Previous';
$next_label   = $args['next_label']   ?? 'Next';
$show_dots    = isset( $args['show_dots'] )   ? (bool) $args['show_dots']   : true;
$show_arrows  = isset( $args['show_arrows'] ) ? (bool) $args['show_arrows'] : true;

$items = is_array( $args['items'] ?? null ) ? $args['items'] : [];

/* Visual panel */
$visual_image       = $args['visual_image']       ?? '';
$visual_alt         = $args['visual_alt']         ?? '';
$visual_label       = $args['visual_label']       ?? '';
$visual_class       = $args['visual_class']       ?? "{$p}-visual";
$visual_img_class   = $args['visual_img_class']   ?? "{$p}-img";
$visual_badge_class = $args['visual_badge_class'] ?? "{$p}-badge";
$visual_extra_class = $args['visual_extra_class'] ?? 'fade-right';

if ( empty( $items ) ) return;

/* Skip cards that have no title — matches the original foreach guard */
$items = array_values( array_filter( $items, static fn( $c ) => ! empty( (array) $c['title'] ) ) );
if ( empty( $items ) ) return;
?>

<section id="<?php echo esc_attr( $section_id ); ?>" class="<?php echo esc_attr( $section_class ); ?>">
	<div class="<?php echo esc_attr( $container_class ); ?>">

		<?php
		get_template_part( 'components/section-header', null, [
			'tag'           => $args['tag']   ?? '',
			'title'         => $args['title'] ?? '',
			'body'          => $args['body']  ?? '',
			'dark'          => $dark,
			'wrapper_class' => $header_class,
		] );
		?>

		<div class="<?php echo esc_attr( $layout_class ); ?>">

			<!-- ── Card carousel ─────────────────────────────────────────────── -->
			<div class="<?php echo esc_attr( $carousel_class ); ?>" id="<?php echo esc_attr( $carousel_id ); ?>">

				<div class="<?php echo esc_attr( $track_class ); ?>" id="<?php echo esc_attr( $track_id ); ?>">
					<?php foreach ( $items as $i => $cert ) :
						$cert  = (array) $cert;
						$badge = trim( $cert['badge'] ?? '' );
					?>
						<div class="<?php echo esc_attr( $card_class ); ?><?php echo $i === 0 ? ' active' : ''; ?>">

							<div class="<?php echo esc_attr( $card_icon_class ); ?>">
								<?php echo esc_html( $cert['icon'] ?? $default_icon ); ?>
							</div>

							<div class="<?php echo esc_attr( $card_body_class ); ?>">
								<div class="<?php echo esc_attr( $card_title_class ); ?>"><?php echo esc_html( $cert['title'] ); ?></div>
								<?php if ( ! empty( $cert['desc'] ) ) : ?>
									<div class="<?php echo esc_attr( $card_desc_class ); ?>"><?php echo esc_html( $cert['desc'] ); ?></div>
								<?php endif; ?>
							</div>

							<?php if ( $badge !== '' && $badge !== "''" ) : ?>
								<span class="<?php echo esc_attr( $card_badge_class ); ?>"><?php echo esc_html( $badge ); ?></span>
							<?php endif; ?>

						</div><!-- .card -->
					<?php endforeach; ?>
				</div><!-- .track -->

				<?php if ( $show_dots || $show_arrows ) : ?>
				<!-- Dots + arrows -->
				<div class="<?php echo esc_attr( $nav_class ); ?>">

					<?php if ( $show_dots ) : ?>
					<div class="<?php echo esc_attr( $dots_class ); ?>"
					     id="<?php echo esc_attr( $dots_id ); ?>"
					     role="tablist"
					     aria-label="<?php echo esc_attr( $nav_label ); ?>">
						<?php foreach ( $items as $i => $_ ) : ?>
							<button class="<?php echo esc_attr( $dot_class ); ?><?php echo $i === 0 ? ' active' : ''; ?>"
							        role="tab"
							        aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
							        aria-label="<?php echo esc_attr( ( $i + 1 ) . ' of ' . count( $items ) ); ?>">
							</button>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>

					<?php if ( $show_arrows ) : ?>
					<div class="<?php echo esc_attr( $arrows_class ); ?>">
						<button class="<?php echo esc_attr( $btn_class ); ?>"
						        id="<?php echo esc_attr( $prev_id ); ?>"
						        aria-label="<?php echo esc_attr( $prev_label ); ?>">←</button>
						<button class="<?php echo esc_attr( $btn_class ); ?>"
						        id="<?php echo esc_attr( $next_id ); ?>"
						        aria-label="<?php echo esc_attr( $next_label ); ?>">→</button>
					</div>
					<?php endif; ?>

				</div><!-- .nav -->
				<?php endif; ?>

			</div><!-- .carousel -->

			<!-- ── Visual panel ──────────────────────────────────────────────── -->
			<?php if ( $visual_image ) : ?>
				<div class="<?php echo esc_attr( $visual_class . ( $visual_extra_class ? ' ' . $visual_extra_class : '' ) ); ?>">
					<img src="<?php echo esc_url( $visual_image ); ?>"
					     alt="<?php echo esc_attr( $visual_alt ); ?>"
					     class="<?php echo esc_attr( $visual_img_class ); ?>"
					     loading="lazy">
					<?php if ( $visual_label ) : ?>
						<span class="<?php echo esc_attr( $visual_badge_class ); ?>"><?php echo esc_html( $visual_label ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		</div><!-- .layout -->
	</div><!-- .container -->
</section>
