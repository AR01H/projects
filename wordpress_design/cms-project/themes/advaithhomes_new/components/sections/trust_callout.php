<?php
/**
 * components/sections/trust_callout.php
 *
 * "Why It's Safe to Start" — animated graphical trust / info callout strip.
 *
 * Each card carries:
 *   - a coloured brand-icon badge (Font Awesome, via adn_icon)
 *   - a looping inline SVG animation unique to that card's topic
 *   - a short title + body paragraph
 *
 * Animations are pure CSS keyframes on opacity/transform only — no JS, no
 * image requests. They respect prefers-reduced-motion automatically.
 * All colours are CSS variable tokens from variables.css — zero new colours.
 *
 * Props: $callout {
 *   eyebrow  string  optional
 *   heading  string
 *   items[]  { icon, title, body }
 * }
 * Usage: adn_component( 'sections/trust_callout', array( 'callout' => $ctx['trust_callout'] ) );
 */
defined( 'ABSPATH' ) || exit;

$_c     = isset( $callout ) && is_array( $callout ) ? $callout : array();
$_items = isset( $_c['items'] ) && is_array( $_c['items'] ) ? $_c['items'] : array();
if ( empty( $_items ) ) return;

$_eyb = isset( $_c['eyebrow'] ) ? (string) $_c['eyebrow'] : '';
$_hdg = isset( $_c['heading'] ) ? (string) $_c['heading'] : '';

// Four looping inline SVG micro-animations — one per card (cycles if more than 4 items).
// 0: shield pulse  1: scale balance rock  2: handshake wave  3: group ripple
$_svgs = array(
	// 0: shield pulse — a shield badge breathes gently.
	'<svg class="tc-visual tc-visual--shield" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<path class="tc-shield-body" d="M40 8 L68 18 V40 C68 56 56 68 40 72 C24 68 12 56 12 40 V18 Z" fill="currentColor" fill-opacity="0.15" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/>
		<path class="tc-shield-check" d="M27 40 L36 50 L53 32" stroke="currentColor" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/>
	</svg>',
	// 1: balance scales rock side to side.
	'<svg class="tc-visual tc-visual--scales" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<g class="tc-scales-arm">
			<line x1="40" y1="14" x2="40" y2="66" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"/>
			<line x1="14" y1="26" x2="66" y2="26" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"/>
			<circle cx="14" cy="36" r="10" fill="currentColor" fill-opacity="0.14" stroke="currentColor" stroke-width="3.5"/>
			<circle cx="66" cy="36" r="10" fill="currentColor" fill-opacity="0.14" stroke="currentColor" stroke-width="3.5"/>
		</g>
		<circle cx="40" cy="14" r="4" fill="currentColor"/>
		<line x1="34" y1="66" x2="46" y2="66" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"/>
	</svg>',
	// 2: handshake — two hands come together and clasp.
	'<svg class="tc-visual tc-visual--handshake" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<g class="tc-hand-left">
			<path d="M8 48 C8 38 14 30 22 28 L32 26 L34 34 L26 36 C22 37 20 40 20 44 L22 54" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
		</g>
		<g class="tc-hand-right">
			<path d="M72 48 C72 38 66 30 58 28 L48 26 L46 34 L54 36 C58 37 60 40 60 44 L58 54" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
		</g>
		<path class="tc-clasp" d="M22 54 C24 60 30 64 40 64 C50 64 56 60 58 54" stroke="currentColor" stroke-width="4.5" stroke-linecap="round"/>
	</svg>',
	// 3: people/group — three circles bloom outward, representing real humans.
	'<svg class="tc-visual tc-visual--people" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<circle class="tc-person tc-person--mid" cx="40" cy="28" r="10" fill="currentColor" fill-opacity="0.18" stroke="currentColor" stroke-width="3.5"/>
		<path class="tc-person tc-person--mid" d="M22 60 C22 50 30 44 40 44 C50 44 58 50 58 60" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"/>
		<circle class="tc-person tc-person--left" cx="20" cy="34" r="7" fill="currentColor" fill-opacity="0.10" stroke="currentColor" stroke-width="3"/>
		<path class="tc-person tc-person--left" d="M8 64 C8 56 13 52 20 52" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
		<circle class="tc-person tc-person--right" cx="60" cy="34" r="7" fill="currentColor" fill-opacity="0.10" stroke="currentColor" stroke-width="3"/>
		<path class="tc-person tc-person--right" d="M72 64 C72 56 67 52 60 52" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
	</svg>',
);
?>
<section class="tc-section">
	<div class="tc-bg" aria-hidden="true">
		<?php /* Subtle floating line-art shapes reuse the same language as story_narrative.php */ ?>
		<svg class="tc-bg-shape tc-bg-shape--lock" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
			<rect x="18" y="36" width="44" height="34" rx="5" stroke="currentColor" stroke-width="3.5"/>
			<path d="M28 36 V26 C28 17 52 17 52 26 V36" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"/>
			<circle cx="40" cy="52" r="4" fill="currentColor"/>
		</svg>
		<svg class="tc-bg-shape tc-bg-shape--leaf" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M40 70 C40 40 14 20 14 14 C28 14 52 22 52 40 C62 30 66 14 70 10 C70 36 60 60 40 70 Z" stroke="currentColor" stroke-width="3.5" stroke-linejoin="round"/>
		</svg>
	</div>

	<div class="container">
		<?php adn_component( 'parts/section_headers/eyebrow_heading', array(
			'eyebrow'       => $_eyb,
			'heading'       => $_hdg,
			'wrapper_class' => 'tc-header',
		) ); ?>

		<div class="tc-grid">
			<?php foreach ( $_items as $_i => $_it ) :
				$_ico  = adn_icon( isset( $_it['icon'] )  ? (string) $_it['icon']  : '' );
				$_ttl  = esc_html( isset( $_it['title'] ) ? (string) $_it['title'] : '' );
				$_body = esc_html( isset( $_it['body'] )  ? (string) $_it['body']  : '' );
				$_svg  = $_svgs[ $_i % count( $_svgs ) ];
			?>
			<div class="tc-card">
				<div class="tc-card-visual" aria-hidden="true">
					<?php echo $_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hand-written static SVG, no dynamic data. ?>
				</div>
				<div class="tc-card-icon-wrap" aria-hidden="true">
					<span class="tc-card-icon"><?php echo $_ico; ?></span>
				</div>
				<h3 class="tc-card-title"><?php echo $_ttl; ?></h3>
				<p class="tc-card-body"><?php echo $_body; ?></p>
				<span class="tc-card-glow" aria-hidden="true"></span>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
