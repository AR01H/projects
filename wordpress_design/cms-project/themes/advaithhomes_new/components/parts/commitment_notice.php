<?php
/**
 * components/parts/commitment_notice.php - Reusable Buyer Commitment Callout Notice
 *
 * Renders a high-impact glassmorphic callout banner with an icon, badge, text,
 * and subtle ambient animated SVG background glyphs.
 *
 * Props:
 *   $notice array {
 *     icon?          string  FontAwesome icon class (e.g. 'fa-solid fa-bullseye')
 *     badge?         string  Pill tag label (e.g. 'For Committed Buyers')
 *     text           string  Main notice copy (supports safe HTML like <b></b>)
 *     wrapper_class? string  Optional extra CSS class
 *   }
 *
 * Usage:
 *   adn_component( 'parts/commitment_notice', array(
 *       'notice' => $ctx['commitment_notice'],
 *   ) );
 */
defined( 'ABSPATH' ) || exit;

$_n = isset( $notice ) && is_array( $notice ) ? $notice : array();
if ( empty( $_n['text'] ) ) return;

$_ico  = isset( $_n['icon'] )  ? (string) $_n['icon']  : 'fa-solid fa-bullseye';
$_bdg  = isset( $_n['badge'] ) ? (string) $_n['badge'] : 'For Serious Buyers';
$_txt  = (string) $_n['text'];
$_wrap = 'hiw-process-commitment-banner';
if ( ! empty( $_n['wrapper_class'] ) ) {
	$_wrap .= ' ' . sanitize_html_class( $_n['wrapper_class'] );
}
?>
<div class="<?php echo esc_attr( $_wrap ); ?>" role="region" aria-label="<?php echo esc_attr( $_bdg ); ?>">
	<?php /* Ambient animated background glows & subtle watermarks */ ?>
	<div class="hiw-pcb-bg" aria-hidden="true">
		<span class="hiw-pcb-orb hiw-pcb-orb--1"></span>
		<span class="hiw-pcb-orb hiw-pcb-orb--2"></span>
		<span class="hiw-pcb-shimmer"></span>

		<!-- Concentric Radar / Target Reticle SVG -->
		<svg class="hiw-pcb-glyph hiw-pcb-glyph--target" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
			<circle cx="50" cy="50" r="46" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 6" opacity="0.4"/>
			<circle cx="50" cy="50" r="32" stroke="currentColor" stroke-width="2" stroke-dasharray="8 4" opacity="0.6"/>
			<circle cx="50" cy="50" r="18" stroke="currentColor" stroke-width="2" opacity="0.8"/>
			<circle cx="50" cy="50" r="5" fill="currentColor"/>
			<line x1="50" y1="2" x2="50" y2="18" stroke="currentColor" stroke-width="2"/>
			<line x1="50" y1="82" x2="50" y2="98" stroke="currentColor" stroke-width="2"/>
			<line x1="2" y1="50" x2="18" y2="50" stroke="currentColor" stroke-width="2"/>
			<line x1="82" y1="50" x2="98" y2="50" stroke="currentColor" stroke-width="2"/>
		</svg>

		<!-- Architectural Key / Milestone SVG -->
		<svg class="hiw-pcb-glyph hiw-pcb-glyph--key" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
			<circle cx="34" cy="38" r="18" stroke="currentColor" stroke-width="3"/>
			<circle cx="34" cy="38" r="8" stroke="currentColor" stroke-width="2"/>
			<path d="M47 51 L84 88 M84 88v-14 M84 88h-14 M70 74h-8" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
	</div>

	<div class="hiw-pcb-content">
		<div class="hiw-pcb-badge">
			<span class="hiw-pcb-badge-dot"></span>
			<span class="hiw-pcb-icon" aria-hidden="true"><?php echo adn_icon( $_ico ); ?></span>
			<span class="hiw-pcb-badge-text"><?php echo esc_html( $_bdg ); ?></span>
		</div>
		<div class="hiw-pcb-text-wrap">
			<p class="hiw-pcb-text"><?php echo function_exists( 'wp_kses_post' ) ? wp_kses_post( $_txt ) : $_txt; ?></p>
		</div>
	</div>
</div>
