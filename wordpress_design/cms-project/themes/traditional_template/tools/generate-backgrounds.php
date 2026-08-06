<?php
/**
 * Generates the decorative background PNGs for traditional_template.
 *
 * All art is drawn procedurally with GD from the theme's own --trad-* palette,
 * so it stays in step with a re-skin and adds no binary blobs of unknown
 * provenance to the repo. Everything is transparent-background RGBA.
 *
 *   ground-cane.png   a wide silhouette of cane stalks + grass along a ground
 *                     line, sat at the bottom of a heading band
 *   ground-soil.png   a softer earth/hedgerow strip for lighter sections
 *   paper-grain.png   a subtle fibre tile for the paper sheets
 *   leaf-fall.png     scattered leaves, used as a static parallax layer
 */

$out = $argv[1] ?? __DIR__;

/* ── Palette (matches assets/css/variables.css) ───────────────────────────── */
$GREEN = array( 0x18, 0x3e, 0x16 );
$DARK  = array( 0x0f, 0x1c, 0x0b );
$GOLD  = array( 0xc3, 0xa1, 0x35 );
$BROWN = array( 0x6b, 0x4f, 0x30 );

function img( $w, $h ) {
	$im = imagecreatetruecolor( $w, $h );
	imagesavealpha( $im, true );
	imagealphablending( $im, true );
	imagefill( $im, 0, 0, imagecolorallocatealpha( $im, 0, 0, 0, 127 ) );
	return $im;
}
function col( $im, $rgb, $alpha = 0 ) {
	return imagecolorallocatealpha( $im, $rgb[0], $rgb[1], $rgb[2], $alpha );
}

/**
 * One arching, tapering cane leaf.
 *
 * Both edges are walked along a quadratic curve and meet at a point, which is
 * what stops a leaf reading as a rectangle. $dir is 1 or -1 for the side it
 * sweeps to; $droop is how far the tip falls; $thick is the width at the base.
 */
function leaf_blade( $im, $x, $y, $dir, $len, $droop, $thick, $colour ) {
	$steps = 14;
	$top    = array();
	$bottom = array();

	for ( $i = 0; $i <= $steps; $i++ ) {
		$t = $i / $steps;

		// Quadratic path: out along the stalk, then arcing down to the tip.
		$px = $x + $dir * $len * $t;
		$py = $y + $droop * $t * $t;

		// Width swells just past the base, then tapers to nothing at the tip.
		$w = $thick * sin( M_PI * min( 1, $t * 1.05 ) ) * ( 1 - $t * 0.35 );

		$top[]    = array( $px, $py - $w );
		$bottom[] = array( $px, $py + $w );
	}

	$points = array();
	foreach ( $top as $p ) {
		$points[] = (int) $p[0];
		$points[] = (int) $p[1];
	}
	foreach ( array_reverse( $bottom ) as $p ) {
		$points[] = (int) $p[0];
		$points[] = (int) $p[1];
	}
	imagefilledpolygon( $im, $points, $colour );
}

/* ── 1. Ground with cane stalks ───────────────────────────────────────────── */
function make_ground( $file, $w, $h, $base, $accent, $stalks, $alphaFloor ) {
	$im = img( $w, $h );

	// Ground line: a soft, slightly uneven mound rather than a ruler edge.
	$groundY = (int) ( $h * 0.86 );
	$ground  = col( $im, $base, $alphaFloor );
	$points  = array();
	for ( $x = 0; $x <= $w; $x += 12 ) {
		$y = $groundY + (int) ( sin( $x / 90 ) * 5 + sin( $x / 23 ) * 2 );
		$points[] = $x;
		$points[] = $y;
	}
	$points[] = $w; $points[] = $h;
	$points[] = 0;  $points[] = $h;
	imagefilledpolygon( $im, $points, $ground );

	mt_srand( 20260806 );  // deterministic: re-running gives the same art

	// Cane stalks: tapered verticals with joint rings, plus a blade or two.
	for ( $i = 0; $i < $stalks; $i++ ) {
		$x      = (int) mt_rand( 0, $w );
		$height = (int) mt_rand( (int) ( $h * 0.30 ), (int) ( $h * 0.80 ) );
		$width  = mt_rand( 3, 7 );
		$lean   = mt_rand( -14, 14 );
		$alpha  = mt_rand( $alphaFloor - 12, $alphaFloor + 22 );
		$alpha  = max( 20, min( 118, $alpha ) );
		$stalk  = col( $im, $base, $alpha );
		$ring   = col( $im, $accent, min( 120, $alpha + 14 ) );

		$topY = $groundY - $height;
		// The stalk itself, drawn as a leaning quad so it tapers naturally.
		imagefilledpolygon( $im, array(
			$x - $width,        $groundY,
			$x + $width,        $groundY,
			$x + $lean + 1,     $topY,
			$x + $lean - 1,     $topY,
		), $stalk );

		// Joint rings every so often up the stalk.
		$joints = max( 2, (int) ( $height / 34 ) );
		for ( $j = 1; $j <= $joints; $j++ ) {
			$t  = $j / ( $joints + 1 );
			$jy = (int) ( $groundY - $height * $t );
			$jx = (int) ( $x + $lean * $t );
			$jw = (int) ( $width * ( 1 - $t * 0.55 ) ) + 1;
			imagefilledrectangle( $im, $jx - $jw, $jy - 1, $jx + $jw, $jy + 1, $ring );
		}

		// One or two leaves peeling off the top third. Cane leaves arch and
		// taper - a straight quad reads as a plank, so each edge is walked
		// along a curve and the tip is brought to a point.
		$leaves = mt_rand( 1, 3 );
		for ( $l = 0; $l < $leaves; $l++ ) {
			$ly  = (int) ( $topY + mt_rand( 0, (int) ( $height * 0.35 ) ) );
			$lx  = (int) ( $x + $lean * 0.85 );
			$dir = ( mt_rand( 0, 1 ) ? 1 : -1 );
			leaf_blade( $im, $lx, $ly, $dir, mt_rand( 34, 96 ), mt_rand( 16, 46 ), mt_rand( 5, 10 ), $stalk );
		}
	}

	// Grass tufts along the ground line so the silhouette does not float.
	for ( $i = 0; $i < (int) ( $w / 7 ); $i++ ) {
		$x     = mt_rand( 0, $w );
		$gy    = $groundY + (int) ( sin( $x / 90 ) * 5 + sin( $x / 23 ) * 2 );
		$blade = col( $im, $base, max( 30, min( 120, mt_rand( $alphaFloor - 6, $alphaFloor + 30 ) ) ) );
		$len   = mt_rand( 8, 34 );
		$dir   = mt_rand( -9, 9 );
		imagefilledpolygon( $im, array(
			$x - 2,        $gy + 4,
			$x + 2,        $gy + 4,
			$x + $dir,     $gy - $len,
		), $blade );
	}

	imagepng( $im, $file, 9 );
	imagedestroy( $im );
	echo "wrote $file\n";
}

/* ── 2. Paper fibre tile ──────────────────────────────────────────────────── */
function make_grain( $file, $size ) {
	$im = img( $size, $size );
	mt_srand( 771 );
	for ( $i = 0; $i < $size * $size / 5; $i++ ) {
		$x = mt_rand( 0, $size - 1 );
		$y = mt_rand( 0, $size - 1 );
		$v = mt_rand( 0, 40 );
		$c = imagecolorallocatealpha( $im, 90 + $v, 74 + $v, 46 + $v, mt_rand( 112, 126 ) );
		imagesetpixel( $im, $x, $y, $c );
	}
	// A few longer fibres, the thing that makes handmade paper read as paper.
	for ( $i = 0; $i < $size / 3; $i++ ) {
		$x = mt_rand( 0, $size );
		$y = mt_rand( 0, $size );
		$c = imagecolorallocatealpha( $im, 120, 100, 70, mt_rand( 116, 124 ) );
		imageline( $im, $x, $y, $x + mt_rand( -18, 18 ), $y + mt_rand( -4, 4 ), $c );
	}
	imagepng( $im, $file, 9 );
	imagedestroy( $im );
	echo "wrote $file\n";
}

/* ── 3. Scattered leaves (static parallax layer) ──────────────────────────── */
function make_leaves( $file, $w, $h, $base ) {
	$im = img( $w, $h );
	mt_srand( 4242 );
	for ( $i = 0; $i < 26; $i++ ) {
		$leaf = col( $im, $base, mt_rand( 92, 118 ) );
		leaf_blade(
			$im,
			mt_rand( 0, $w ),
			mt_rand( 0, $h ),
			( mt_rand( 0, 1 ) ? 1 : -1 ),
			mt_rand( 26, 72 ),
			mt_rand( 10, 30 ),
			mt_rand( 4, 9 ),
			$leaf
		);
	}
	imagepng( $im, $file, 9 );
	imagedestroy( $im );
	echo "wrote $file\n";
}

make_ground( "$out/ground-cane.png", 1600, 340, $GREEN, $GOLD, 40, 74 );
make_ground( "$out/ground-soil.png", 1600, 220, $BROWN, $GOLD, 22, 92 );
make_grain( "$out/paper-grain.png", 220 );
make_leaves( "$out/leaf-fall.png", 900, 600, $GREEN );
