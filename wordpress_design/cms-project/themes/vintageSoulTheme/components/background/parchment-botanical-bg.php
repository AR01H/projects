<?php
/**
 * VintageSoulTheme - Parchment Botanical Animated SVG Background
 *
 * Renders a subtle, randomized animated botanical SVG background layer
 * suitable for light parchment-colored pages (about, history, contact, etc.)
 *
 * Usage: View::component('background/parchment-botanical-bg', ['seed' => 42]);
 *
 * Props:
 *   int $seed   - Randomisation seed (use different values per page)
 *   string $class - Extra class to add to wrapper
 */

defined( 'ABSPATH' ) || exit;

$seed  = (int) ( $seed ?? 1 );
$class = isset( $class ) ? ' ' . (string) $class : '';

// Deterministic pseudo-random helpers based on seed
function vst_bg_rnd( int &$s ): float {
	$s = ( ( $s * 1103515245 ) + 12345 ) & 0x7fffffff;
	return $s / 0x7fffffff;
}

$s = $seed;

// Generate leaf elements - 8 leaves
$leaves = array();
for ( $i = 0; $i < 8; $i++ ) {
	$leaves[] = array(
		'cx'    => round( vst_bg_rnd( $s ) * 100, 1 ),
		'cy'    => round( vst_bg_rnd( $s ) * 100, 1 ),
		'rx'    => round( 18 + vst_bg_rnd( $s ) * 22, 1 ),
		'ry'    => round( 5 + vst_bg_rnd( $s ) * 8, 1 ),
		'rot'   => round( vst_bg_rnd( $s ) * 360 ),
		'class' => 'vst-bg-leaf-' . chr( ord('a') + ( $i % 5 ) ),
		'op'    => round( 0.06 + vst_bg_rnd( $s ) * 0.10, 3 ),
		'color' => ( $i % 3 === 0 ) ? '#3a7d4a' : ( $i % 3 === 1 ? '#4a8f5e' : '#2e6b3f' ),
	);
}

// Cane stalk positions - 3 stalks
$stalks = array(
	array( 'x' => round( vst_bg_rnd( $s ) * 20 + 2 ), 'class' => 'vst-bg-cane-a', 'op' => 0.06 ),
	array( 'x' => round( 75 + vst_bg_rnd( $s ) * 20 ), 'class' => 'vst-bg-cane-b', 'op' => 0.05 ),
	array( 'x' => round( 40 + vst_bg_rnd( $s ) * 20 ), 'class' => 'vst-bg-cane-c', 'op' => 0.04 ),
);

// Gold dust particles - 6 circles
$particles = array();
for ( $i = 0; $i < 6; $i++ ) {
	$particles[] = array(
		'cx'    => round( vst_bg_rnd( $s ) * 98 + 1, 1 ),
		'cy'    => round( vst_bg_rnd( $s ) * 95 + 2, 1 ),
		'r'     => round( 1.5 + vst_bg_rnd( $s ) * 2.5, 1 ),
		'class' => 'vst-bg-gold-' . ( $i + 1 ),
		'color' => ( $i % 2 === 0 ) ? '#d49842' : '#f6d599',
	);
}
?>
<div class="vst-parchment-botanical-bg<?php echo esc_attr( $class ); ?>" aria-hidden="true">
	<svg viewBox="0 0 100 100" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">

		<!-- Watermark cane silhouette -->
		<g class="vst-bg-watermark" opacity="0.04">
			<text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle"
			      font-family="serif" font-size="65" fill="#2a5e35" opacity="0.7"
			      transform="rotate(-15 50 50)">🌾</text>
		</g>

		<!-- Animated leaf shapes -->
		<?php foreach ( $leaves as $leaf ) : ?>
		<ellipse
			class="<?php echo esc_attr( $leaf['class'] ); ?>"
			cx="<?php echo $leaf['cx']; ?>%"
			cy="<?php echo $leaf['cy']; ?>%"
			rx="<?php echo $leaf['rx'] * 0.35; ?>"
			ry="<?php echo $leaf['ry'] * 0.35; ?>"
			fill="<?php echo esc_attr( $leaf['color'] ); ?>"
			opacity="<?php echo $leaf['op']; ?>"
			transform="rotate(<?php echo $leaf['rot']; ?>, <?php echo $leaf['cx']; ?>, <?php echo $leaf['cy']; ?>)"
		/>
		<?php endforeach; ?>

		<!-- Sugarcane stalk silhouettes -->
		<?php foreach ( $stalks as $stalk ) : ?>
		<g class="<?php echo esc_attr( $stalk['class'] ); ?>" opacity="<?php echo $stalk['op']; ?>">
			<rect x="<?php echo $stalk['x']; ?>" y="0" width="0.6" height="100" rx="0.3" fill="#2a5e35"/>
			<ellipse cx="<?php echo $stalk['x'] + 0.3; ?>" cy="25" rx="1.8" ry="0.6" fill="#3a7d4a" transform="rotate(-20 <?php echo $stalk['x'] + 0.3; ?> 25)"/>
			<ellipse cx="<?php echo $stalk['x'] + 0.3; ?>" cy="50" rx="1.8" ry="0.6" fill="#3a7d4a" transform="rotate(20 <?php echo $stalk['x'] + 0.3; ?> 50)"/>
			<ellipse cx="<?php echo $stalk['x'] + 0.3; ?>" cy="75" rx="1.8" ry="0.6" fill="#3a7d4a" transform="rotate(-15 <?php echo $stalk['x'] + 0.3; ?> 75)"/>
		</g>
		<?php endforeach; ?>

		<!-- Gold dust particles -->
		<?php foreach ( $particles as $p ) : ?>
		<circle
			class="<?php echo esc_attr( $p['class'] ); ?>"
			cx="<?php echo $p['cx']; ?>%"
			cy="<?php echo $p['cy']; ?>%"
			r="<?php echo $p['r'] * 0.35; ?>"
			fill="<?php echo esc_attr( $p['color'] ); ?>"
		/>
		<?php endforeach; ?>

	</svg>
</div>
