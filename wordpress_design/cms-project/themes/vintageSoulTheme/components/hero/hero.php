<?php
/**
 * The main hero - full-width, JSON-driven, vintage editorial composition:
 * one framed image/video per slide, small floating annotation cards around
 * it. This is the page's own hero (components/hero-carousel is a separate,
 * simpler multi-slide banner elsewhere - not this).
 *
 * Call shape (matches data/content/hero.json, prepared by HomeController):
 *
 *   View::component( 'hero/hero', [
 *       'id'            => 'home-hero',      // optional, default 'vst-hero'
 *       'heading_level' => 1,                // optional, default 2 - pass 1 only where this is the page's own <h1>
 *       'settings'      => [ 'autoplay' => true, 'autoplay_delay' => 6500, 'pause_on_hover' => true, 'touch_enabled' => true, 'keyboard_enabled' => true ],
 *       'slides'        => [ [ 'media' => [...], 'content' => [...], 'cards' => [...] ], ... ],
 *   ] );
 *
 * Each slide:
 *   'media'   => [ 'type' => 'image'|'video', 'src', 'mobile_src', 'poster' (video only), 'alt' ]
 *   'content' => [ 'eyebrow', 'title', 'description', 'position', 'buttons' => [ [ 'label', 'icon', 'route', 'style' ], ... ] ]
 *              'position' one of: top-left, top-right, center-left, center-right, bottom-left, bottom-right, bottom-center
 *   'cards'   => [ [ 'id', 'number', 'title', 'text', 'position' => [...], 'connector' ], ... ]
 *
 * A card's `position` is deliberately NOT a fixed keyword - it's a plain
 * CSS-offset pair, e.g. `[ 'top' => '4%', 'left' => '-2%' ]` or
 * `[ 'bottom' => '6%', 'right' => '3%' ]` (exactly one vertical key -
 * `top` or `bottom` - and one horizontal key - `left` or `right`; each
 * value a CSS length: `%`, `px`, `rem` or `em`). That lets a card sit
 * anywhere around the media, not only at a handful of named slots. The
 * same top/bottom + left/right pair also decides which corner it visually
 * "belongs" to, purely to pick a tasteful entrance direction and connector
 * angle: a card anchored by `top`+`left` emerges from the media's centre
 * and travels out to the top-left as it scales up to full size (its
 * connector points the same way) - so the card always reads as having
 * come from the photo itself, no matter where its own resting spot is,
 * and the animation always reads as intentional no matter where a card
 * is actually placed.
 *
 * A slide with no media is dropped; a card with no title is dropped. With
 * one slide left, the toggle and dots are hidden automatically (there's
 * nothing to play/pause or jump between). There are no prev/next arrows -
 * swipe and the keyboard's arrow keys are the slide-navigation controls.
 *
 * Desktop (>=1024px): an absolute-positioned editorial stage - media
 * centred, cards floating at their own offset around it, connectors
 * optional per card. Tablet (768-1023px): same stage, but only the first
 * two cards render (see hero.css) to keep the photo readable. Below
 * 768px this is a deliberately different composition, not a shrunk
 * desktop one: media on top in normal flow, heading/copy below it, cards
 * become a swipeable one-at-a-time scroll-snap row (the same technique
 * components/certificate-carousel already uses for touch scroll, no JS
 * needed for that part) - a card's desktop offset is simply unused there.
 *
 * JS (assets/js/components/hero.js): slide advance/autoplay, video-driven
 * advance, pause-on-hover/focus, keyboard arrows, touch swipe on the media
 * stage, and picking `mobile_src` for a video (an <img>'s `mobile_src` is
 * handled with zero JS via <picture>/<source>).
 *
 * Existing texture/shape system, not reinvented here:
 *   .vst-hero__media  - its film grain is declared by hero.css directly
 *     (a new asset, assets/images/textures/grain/grain-c.svg, extending the
 *     existing grain-a/grain-b family) rather than borrowed from shape.css's
 *     grain-* classes - see hero.css' own comment on that ::before for why
 *     (short version: those paint behind the element's content, which is
 *     invisible when the content is an opaque photo). Plus tex-cane-ribbon-a
 *     (textures.css' ::after slot - the real paper-ribbon-with-cane.png
 *     asset, on-brand for a sugarcane theme; composes fine since the grain
 *     above only uses ::before).
 *   .vst-hero__content - tex-paper-aged-a (paper grain) + roughness-a
 *     (assets/css/shape.css' displaced-SVG-mask torn edge) for a hand-cut/
 *     aged-paper feel on the caption box. Neither shape.css class sets
 *     `position`, so they're safe to combine with this file's own
 *     `position: absolute` rule here - unlike some tex-* classes (e.g.
 *     tex-paper-aged-a itself), which force `position: relative` and
 *     would fight it if applied to a positioned element directly.
 *   .vst-hero__card - its face IS the real illustrated asset directly
 *     (assets/images/decorative/paper-ribbon-with-cane.png - referenced
 *     directly, not via --tex-cane-ribbon-image; see hero.css' own comment
 *     on this rule for why - the same hand-painted cane-sprig scroll used
 *     as a small accent on the media above) rather than a plain torn-paper
 *     rectangle - see hero.css' own
 *     comment on this rule for the background-size/padding reasoning.
 */

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;

$valid_content_positions = array( 'top-left', 'top-right', 'center-left', 'center-right', 'bottom-left', 'bottom-right', 'bottom-center' );
$css_length_pattern       = '/^-?\d+(\.\d+)?(%|px|rem|em)$/';

$sanitize_length = static function ( $value, $fallback ) use ( $css_length_pattern ) {
	$value = trim( (string) $value );
	return ( '' !== $value && preg_match( $css_length_pattern, $value ) ) ? $value : $fallback;
};

$id             = ( isset( $id ) && '' !== trim( (string) $id ) ) ? sanitize_html_class( (string) $id ) : 'vst-hero';
$heading_level  = isset( $heading_level ) ? (int) $heading_level : 2;
$heading_level  = ( $heading_level >= 1 && $heading_level <= 6 ) ? $heading_level : 2;
$heading_tag    = 'h' . $heading_level;

$settings_in     = isset( $settings ) && is_array( $settings ) ? $settings : array();
$autoplay        = ! empty( $settings_in['autoplay'] );
$autoplay_delay  = isset( $settings_in['autoplay_delay'] ) ? max( 2000, (int) $settings_in['autoplay_delay'] ) : 6000;
$pause_on_hover  = array_key_exists( 'pause_on_hover', $settings_in ) ? ! empty( $settings_in['pause_on_hover'] ) : true;
$touch_enabled   = array_key_exists( 'touch_enabled', $settings_in ) ? ! empty( $settings_in['touch_enabled'] ) : true;
$keyboard_enabled = array_key_exists( 'keyboard_enabled', $settings_in ) ? ! empty( $settings_in['keyboard_enabled'] ) : true;
$transition_duration = isset( $settings_in['transition_duration'] ) ? max( 200, (int) $settings_in['transition_duration'] ) : 900;

$slides_in = isset( $slides ) && is_array( $slides ) ? $slides : array();

$slides = array();
foreach ( $slides_in as $slide_in ) {
	$slide_in = (array) $slide_in;

	$media = (array) ( $slide_in['media'] ?? array() );
	$media_type = ( 'video' === ( $media['type'] ?? '' ) ) ? 'video' : 'image';
	$media_src  = trim( (string) ( $media['src'] ?? '' ) );
	if ( '' === $media_src ) {
		continue; // A slide needs a primary media source - never image + video at once, never neither.
	}

	$content = (array) ( $slide_in['content'] ?? array() );
	$title   = trim( (string) ( $content['title'] ?? '' ) );

	$content_position = (string) ( $content['position'] ?? 'bottom-left' );
	if ( ! in_array( $content_position, $valid_content_positions, true ) ) {
		$content_position = 'bottom-left';
	}

	$buttons = is_array( $content['buttons'] ?? null ) ? $content['buttons'] : array();
	$buttons = array_values(
		array_filter(
			array_map(
				static function ( $btn ) {
					$btn = (array) $btn;
					return array(
						'label' => trim( (string) ( $btn['label'] ?? '' ) ),
						'icon'  => (string) ( $btn['icon'] ?? '' ),
						'route' => (string) ( $btn['route'] ?? '' ),
						'ghost' => 'ghost' === ( $btn['style'] ?? '' ),
					);
				},
				$buttons
			),
			static function ( $btn ) {
				return '' !== $btn['label'] && '' !== $btn['route'];
			}
		)
	);

	$cards_in = is_array( $slide_in['cards'] ?? null ) ? $slide_in['cards'] : array();
	$cards    = array();
	foreach ( $cards_in as $card_index => $card_in ) {
		$card_in    = (array) $card_in;
		$card_title = trim( (string) ( $card_in['title'] ?? '' ) );
		if ( '' === $card_title ) {
			continue;
		}

		$pos_in    = (array) ( $card_in['position'] ?? array() );
		$has_top   = isset( $pos_in['top'] ) && '' !== trim( (string) $pos_in['top'] );
		$has_bottom = ! $has_top && isset( $pos_in['bottom'] ) && '' !== trim( (string) $pos_in['bottom'] );
		$has_left  = isset( $pos_in['left'] ) && '' !== trim( (string) $pos_in['left'] );
		$has_right = ! $has_left && isset( $pos_in['right'] ) && '' !== trim( (string) $pos_in['right'] );
		if ( ! $has_top && ! $has_bottom ) {
			$has_top = true; // No vertical anchor given - default to a sensible spot rather than dropping the card.
		}
		if ( ! $has_left && ! $has_right ) {
			$has_left = true;
		}

		$vert_key  = $has_top ? 'top' : 'bottom';
		$horiz_key = $has_left ? 'left' : 'right';

		$cards[] = array(
			'id'         => sanitize_html_class( '' !== (string) ( $card_in['id'] ?? '' ) ? (string) $card_in['id'] : ( 'card-' . ( $card_index + 1 ) ) ),
			'number'     => (string) ( $card_in['number'] ?? sprintf( '%02d', $card_index + 1 ) ),
			'title'      => $card_title,
			'text'       => (string) ( $card_in['text'] ?? '' ),
			'vert_key'   => $vert_key,
			'vert_val'   => $sanitize_length( $pos_in[ $vert_key ] ?? '', '38%' ),
			'horiz_key'  => $horiz_key,
			'horiz_val'  => $sanitize_length( $pos_in[ $horiz_key ] ?? '', '50%' ),
			'quadrant'   => $vert_key . '-' . $horiz_key, // e.g. "top-left" - an animation/connector hint only, not a layout slot.
			'connector'  => ! empty( $card_in['connector'] ),
		);
	}

	if ( '' === $title && empty( $cards ) ) {
		continue;
	}

	$slides[] = array(
		'id'      => sanitize_html_class( '' !== (string) ( $slide_in['id'] ?? '' ) ? (string) $slide_in['id'] : ( 'slide-' . ( count( $slides ) + 1 ) ) ),
		'media'   => array(
			'type'       => $media_type,
			'src'        => $media_src,
			'mobile_src' => trim( (string) ( $media['mobile_src'] ?? '' ) ),
			'poster'     => trim( (string) ( $media['poster'] ?? '' ) ),
			'alt'        => (string) ( $media['alt'] ?? '' ),
		),
		'content' => array(
			'eyebrow'     => (string) ( $content['eyebrow'] ?? '' ),
			'title'       => $title,
			'description' => (string) ( $content['description'] ?? '' ),
			'position'    => $content_position,
			'buttons'     => $buttons,
		),
		'cards'   => $cards,
	);
}

if ( empty( $slides ) ) {
	return;
}

$count      = count( $slides );
$has_multiple_slides = $count > 1;
?>
<section
	class="vst-hero"
	id="<?php echo esc_attr( $id ); ?>"
	data-vst-hero
	<?php echo ( $has_multiple_slides && $autoplay ) ? ' data-vst-hero-autoplay="1"' : ''; ?>
	data-autoplay-delay="<?php echo esc_attr( (string) $autoplay_delay ); ?>"
	data-pause-on-hover="<?php echo $pause_on_hover ? '1' : '0'; ?>"
	data-touch-enabled="<?php echo $touch_enabled ? '1' : '0'; ?>"
	data-keyboard-enabled="<?php echo $keyboard_enabled ? '1' : '0'; ?>"
	style="--vst-hero-transition-duration: <?php echo esc_attr( (string) $transition_duration ); ?>ms;"
	aria-roledescription="carousel"
>
	<div class="vst-hero__stage">
		<div class="vst-hero__track">
			<?php foreach ( $slides as $i => $slide ) :
				$is_active = ( 0 === $i );
				$media     = $slide['media'];
				$content   = $slide['content'];
			?>
				<div
					class="vst-hero__slide<?php echo $is_active ? ' is-active' : ''; ?>"
					id="<?php echo esc_attr( $id . '-' . $slide['id'] ); ?>"
					role="group"
					aria-roledescription="slide"
					aria-label="<?php echo esc_attr( sprintf( /* translators: 1: slide number 2: total slides */ __( 'Slide %1$d of %2$d', 'vintagesoul' ), $i + 1, $count ) ); ?>"
					<?php echo $is_active ? '' : ' inert'; ?>
				>
					<?php
					/*
					 * .vst-hero__frame wraps ONLY the media + content, not the cards
					 * below - it's the positioning context .vst-hero__content overlays
					 * against (position:absolute, at every breakpoint, per the caller's
					 * choice - text sits on the photo on mobile too, not stacked below
					 * it). It has to be a dedicated wrapper rather than using
					 * .vst-hero__slide itself for that: on mobile the cards row stays
					 * in normal flow below the photo, so the SLIDE's own height is
					 * media + cards combined - content positioned against that would
					 * miscalculate "bottom-left" etc. against the wrong box. Sized to
					 * exactly the media's own box (frame has no size itself; media's
					 * aspect-ratio drives it), so it works out to the same thing the
					 * old md+-only rule already did there.
					 */
					?>
					<div class="vst-hero__frame">
						<figure class="vst-hero__media tex-cane-ribbon-a" data-media-type="<?php echo esc_attr( $media['type'] ); ?>">
							<?php if ( 'video' === $media['type'] ) : ?>
								<video
									class="vst-hero__media-el"
									<?php echo ( '' !== $media['mobile_src'] ) ? 'data-mobile-src="' . esc_url( $media['mobile_src'] ) . '"' : ''; ?>
									<?php echo ( '' !== $media['poster'] ) ? 'poster="' . esc_url( $media['poster'] ) . '"' : ''; ?>
									src="<?php echo esc_url( $media['src'] ); ?>"
									muted
									loop
									playsinline
									<?php echo ( $is_active && $autoplay ) ? 'autoplay' : ''; ?>
									preload="<?php echo $is_active ? 'auto' : 'none'; ?>"
								></video>
							<?php else : ?>
								<picture>
									<?php if ( '' !== $media['mobile_src'] && $media['mobile_src'] !== $media['src'] ) : ?>
										<source media="(max-width: 767px)" srcset="<?php echo esc_url( $media['mobile_src'] ); ?>">
									<?php endif; ?>
									<img
										class="vst-hero__media-el"
										src="<?php echo esc_url( $media['src'] ); ?>"
										alt="<?php echo esc_attr( $media['alt'] ); ?>"
										loading="<?php echo $is_active ? 'eager' : 'lazy'; ?>"
										decoding="async"
									>
								</picture>
							<?php endif; ?>
							<span class="vst-hero__media-frame" aria-hidden="true"></span>
							<span class="vst-hero__media-vignette" aria-hidden="true"></span>
						</figure>

						<?php if ( '' !== $content['title'] ) : ?>
							<div class="vst-hero__content roughness-a" data-position="<?php echo esc_attr( $content['position'] ); ?>">
								<?php if ( '' !== $content['eyebrow'] ) : ?>
									<p class="vst-hero__eyebrow"><?php echo esc_html( $content['eyebrow'] ); ?></p>
								<?php endif; ?>
								<?php $slide_heading_tag = ( 0 === $i ) ? $heading_tag : 'h' . min( 6, $heading_level + 1 ); ?>
								<<?php echo tag_escape( $slide_heading_tag ); ?> class="vst-hero__title"><?php echo esc_html( $content['title'] ); ?></<?php echo tag_escape( $slide_heading_tag ); ?>>
								<?php if ( '' !== $content['description'] ) : ?>
									<p class="vst-hero__desc"><?php echo esc_html( $content['description'] ); ?></p>
								<?php endif; ?>
								<?php if ( ! empty( $content['buttons'] ) ) : ?>
									<div class="vst-hero__actions">
										<?php foreach ( $content['buttons'] as $btn ) : ?>
											<a class="roughness-a btn<?php echo $btn['ghost'] ? ' btn--outline' : ''; ?>" href="<?php echo esc_url( RouteService::url( $btn['route'] ) ); ?>">
												<?php if ( '' !== $btn['icon'] ) : ?>
													<span aria-hidden="true"><?php echo esc_html( $btn['icon'] ); ?></span>
												<?php endif; ?>
												<?php echo esc_html( $btn['label'] ); ?>
											</a>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>

					<?php if ( ! empty( $slide['cards'] ) ) : ?>
						<div class="vst-hero__cards">
							<?php foreach ( $slide['cards'] as $j => $card ) :
								$rotate = ( 0 === $j % 2 ) ? '-1.1deg' : '1.4deg';
								// Large, deliberately not-precise offsets toward the media's centre - the
								// card should read as emerging from the middle of the photo and travelling
								// out to its spot, not just nudging in from just outside its own position.
								$from_x = ( 'left' === $card['horiz_key'] ) ? '-140px' : '140px';
								$from_y = ( 'top' === $card['vert_key'] ) ? '-110px' : '110px';
								$slot_style = sprintf(
									'--vst-card-%1$s: %2$s; --vst-card-%3$s: %4$s; --vst-card-from-x: %5$s; --vst-card-from-y: %6$s;',
									$card['vert_key'],
									$card['vert_val'],
									$card['horiz_key'],
									$card['horiz_val'],
									$from_x,
									$from_y
								);
							?>
								<div class="vst-hero__card-slot" style="<?php echo esc_attr( $slot_style ); ?>">
									<?php if ( $card['connector'] ) : ?>
										<span class="vst-hero__connector" data-quadrant="<?php echo esc_attr( $card['quadrant'] ); ?>" aria-hidden="true"></span>
									<?php endif; ?>
									<article class="vst-hero__card" style="--vst-card-rotate: <?php echo esc_attr( $rotate ); ?>;">
										<p class="vst-hero__card-title"><?php echo esc_html( $card['title'] ); ?></p>
										<?php if ( '' !== $card['text'] ) : ?>
											<p class="vst-hero__card-text"><?php echo esc_html( $card['text'] ); ?></p>
										<?php endif; ?>
									</article>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( $has_multiple_slides && $autoplay ) : ?>
			<button
				type="button"
				class="carousel-toggle vst-hero__toggle"
				data-hero-toggle
				data-label-pause="<?php echo esc_attr__( 'Pause slideshow', 'vintagesoul' ); ?>"
				data-label-play="<?php echo esc_attr__( 'Play slideshow', 'vintagesoul' ); ?>"
				aria-label="<?php esc_attr_e( 'Pause slideshow', 'vintagesoul' ); ?>"
			>
				<span class="carousel-toggle__icon" aria-hidden="true"></span>
			</button>
		<?php endif; ?>
		<?php if ( $has_multiple_slides ) : ?>
			<div class="carousel-dots vst-hero__dots" role="tablist" aria-label="<?php esc_attr_e( 'Slides', 'vintagesoul' ); ?>">
				<?php foreach ( $slides as $i => $slide ) : ?>
					<button
						type="button"
						class="carousel-dot vst-hero__dot<?php echo ( 0 === $i ) ? ' is-active' : ''; ?>"
						data-hero-slide="<?php echo esc_attr( $i ); ?>"
						role="tab"
						aria-selected="<?php echo ( 0 === $i ) ? 'true' : 'false'; ?>"
						aria-controls="<?php echo esc_attr( $id . '-' . $slide['id'] ); ?>"
						aria-label="<?php echo esc_attr( sprintf( /* translators: %d: slide number */ __( 'Slide %d', 'vintagesoul' ), $i + 1 ) ); ?>"
					></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
