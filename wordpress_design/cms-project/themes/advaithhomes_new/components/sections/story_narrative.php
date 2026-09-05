<?php
/**
 * components/sections/story_narrative.php
 *
 * Narrative version of the step timeline: same alternating left/right
 * layout, same gradient node + dashed centre line + white card language as
 * step_timeline.php (so it reads as part of the same page, not a bolted-on
 * design), but each node holds a small looping animated inline SVG instead
 * of a static icon, and each card tells one beat of the story instead of a
 * mechanical step.
 *
 * Props: $story {
 *   eyebrow, heading,
 *   chapters[] { lede, body, cta? { label, url } }
 * }
 * Usage: adn_component( 'sections/story_narrative', array( 'story' => $ctx['story'] ) );
 *
 * Visuals cycle question -> send -> listen -> idea -> door by index, so
 * any number of chapters still gets one. Pure CSS keyframe loops on
 * transform/opacity only - no JS, no image requests.
 */
defined( 'ABSPATH' ) || exit;

$_s        = isset( $story ) && is_array( $story ) ? $story : array();
$_chapters = isset( $_s['chapters'] ) && is_array( $_s['chapters'] ) ? $_s['chapters'] : array();
if ( empty( $_chapters ) ) return;

$_eyb = isset( $_s['eyebrow'] ) ? (string) $_s['eyebrow'] : '';
$_hdg = isset( $_s['heading'] ) ? (string) $_s['heading'] : '';

$_visuals = array(
	// 0: a question is asked - bubble breathes, "?" blinks.
	'<svg class="story-visual story-visual--question" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<path class="sv-bubble" d="M18 24h64a9 9 0 0 1 9 9v28a9 9 0 0 1-9 9H48l-16 15V70H18a9 9 0 0 1-9-9V33a9 9 0 0 1 9-9z" fill="currentColor" fill-opacity="0.16" stroke="currentColor" stroke-width="5" stroke-linejoin="round"/>
		<text class="sv-mark" x="50" y="58" text-anchor="middle" font-size="30" font-weight="800" fill="currentColor">?</text>
	</svg>',
	// 1: they reach out - paper plane glides forward and drifts back.
	'<svg class="story-visual story-visual--send" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<g class="sv-plane">
			<path d="M10 56 L86 16 L58 88 L44 58 Z" fill="currentColor" fill-opacity="0.18" stroke="currentColor" stroke-width="5" stroke-linejoin="round"/>
			<path d="M44 58 L86 16" stroke="currentColor" stroke-width="3.4"/>
		</g>
	</svg>',
	// 2: someone listens - waves ripple outward from a solid centre dot.
	'<svg class="story-visual story-visual--listen" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<circle cx="50" cy="50" r="9" fill="currentColor"/>
		<circle class="sv-wave sv-wave-1" cx="50" cy="50" r="20" stroke="currentColor" stroke-width="4.5"/>
		<circle class="sv-wave sv-wave-2" cx="50" cy="50" r="32" stroke="currentColor" stroke-width="3.6"/>
		<circle class="sv-wave sv-wave-3" cx="50" cy="50" r="44" stroke="currentColor" stroke-width="2.8"/>
	</svg>',
	// 3: it gets clearer - bulb glows, rays pulse around it.
	'<svg class="story-visual story-visual--idea" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<path class="sv-bulb" d="M50 14a25 25 0 0 0-14 46c3 2 5 6 5 10h18c0-4 2-8 5-10a25 25 0 0 0-14-46z" fill="currentColor" fill-opacity="0.18" stroke="currentColor" stroke-width="5"/>
		<path d="M41 78h18M44 85h12" stroke="currentColor" stroke-width="5" stroke-linecap="round"/>
		<g class="sv-rays" stroke="currentColor" stroke-width="5" stroke-linecap="round">
			<path d="M50 0v8"/>
			<path d="M15 19l6 6"/>
			<path d="M85 19l-6 6"/>
			<path d="M6 50h8"/>
			<path d="M86 50h8"/>
		</g>
	</svg>',
	// 4: your turn - a door eases open on its hinge, inviting the next step.
	'<svg class="story-visual story-visual--door" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<rect x="18" y="10" width="60" height="80" rx="3" fill="currentColor" fill-opacity="0.1" stroke="currentColor" stroke-width="4.5"/>
		<g class="sv-door">
			<path d="M80 10 L56 17 L56 83 L80 90 Z" fill="currentColor" fill-opacity="0.22" stroke="currentColor" stroke-width="5" stroke-linejoin="round"/>
			<circle cx="64" cy="52" r="3.2" fill="currentColor"/>
		</g>
	</svg>',
);
?>
<section class="story-section">

	<?php /* Decorative, inert background - house/key/roof/door line-art
	   drifting slowly behind the timeline. Purely ambient (aria-hidden,
	   pointer-events:none via CSS), reinforces the property theme without
	   competing with the foreground content. */ ?>
	<div class="story-bg" aria-hidden="true">
		<svg class="story-bg-shape story-bg-shape--house" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M15 60 L60 22 L105 60" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
			<path d="M27 50v48h66V50" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/>
			<path d="M50 98V70h20v28" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/>
			<path d="M78 34V22h12v20" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/>
		</svg>
		<svg class="story-bg-shape story-bg-shape--key" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
			<circle cx="34" cy="34" r="18" stroke="currentColor" stroke-width="4"/>
			<path d="M47 47 L98 98 M98 98v-16 M98 98h-16" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
		<svg class="story-bg-shape story-bg-shape--door" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
			<rect x="30" y="14" width="60" height="96" rx="3" stroke="currentColor" stroke-width="4"/>
			<circle cx="72" cy="62" r="3.5" fill="currentColor"/>
		</svg>
		<svg class="story-bg-shape story-bg-shape--roof" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M10 66 L60 20 L110 66" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
			<path d="M82 38V20h14v33" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/>
		</svg>
	</div>

	<div class="container">

		<?php adn_component( 'parts/section_headers/eyebrow_heading', array(
			'eyebrow'       => $_eyb,
			'heading'       => $_hdg,
			'wrapper_class' => 'hiw-process-header story-header',
		) ); ?>

		<div class="hiw-timeline story-timeline">
			<?php foreach ( $_chapters as $_i => $_ch ) :
				$_lede   = esc_html( isset( $_ch['lede'] ) ? (string) $_ch['lede'] : '' );
				$_body   = esc_html( isset( $_ch['body'] ) ? (string) $_ch['body'] : '' );
				$_cta    = isset( $_ch['cta'] ) && is_array( $_ch['cta'] ) ? $_ch['cta'] : array();
				$_visual = $_visuals[ $_i % count( $_visuals ) ];
				$_side   = ( 0 === $_i % 2 ) ? 'hiw-step--left' : 'hiw-step--right';
				$_last   = ( $_i === count( $_chapters ) - 1 );
			?>
				<div class="hiw-step <?php echo esc_attr( $_side ); ?><?php echo $_last ? ' hiw-step--last' : ''; ?>">
					<div class="hiw-step-node story-node">
						<?php echo $_visual; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed, hand-written inline SVG defined in this file only, no dynamic/user data. ?>
					</div>
					<div class="hiw-step-card story-card">
						<?php if ( '' !== $_lede ) : ?><p class="story-lede"><?php echo $_lede; ?></p><?php endif; ?>
						<?php if ( '' !== $_body ) : ?><p><?php echo $_body; ?></p><?php endif; ?>
						<?php if ( ! empty( $_cta['url'] ) ) : ?>
							<a href="<?php echo esc_url( adn_link( $_cta['url'] ) ); ?>" class="btn btn-accent btn-lg story-cta">
								<?php echo esc_html( isset( $_cta['label'] ) ? (string) $_cta['label'] : '' ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
