<?php
/**
 * Movie-title header - a carved wooden signboard used as the page header on
 * every page except home.
 *
 * HTML + CSS + SVG only. No canvas, and no image anywhere in the title.
 *
 * WHY THE TITLE IS SVG TEXT
 * The reference sets its headline on a CURVE, rising in the middle. CSS cannot
 * bend a line of text - the only ways are one span per letter with a rotation
 * each (which breaks kerning, ligatures, and any language that shapes letters
 * together) or an image (excluded, and unreadable). SVG <textPath> bends real
 * text: still selectable, still translatable, still one string in the DOM, and
 * it scales with the viewBox instead of needing a font-size per breakpoint.
 * It stays inside the <h1>, so the document outline is unchanged.
 *
 * The carved metal is built by stacking several <text> elements on the SAME
 * path - a dark outline, extrusion copies stepped downwards, the gold gradient,
 * then a highlight - which is how a title like this is drawn by hand.
 *
 * GENERIC: every string is data. Nothing here names a business, place or
 * language - swap admin/data/page_headers.json and the same board serves a
 * different trade.
 *
 * Data (page_headers.json[<header>], merged in by NT_Section_Renderer):
 *   kicker    small line ABOVE the board (credit / eyebrow)
 *   subtitle  small curved line inside the board, above the title
 *   title     the carved headline (required - renders nothing without it)
 *   arc_text  text set along the lower arc
 *   ribbon    plaque slung under the board
 *   ornament  side motif: crossed | rope | chain | botanical (default crossed)
 *
 * The sprite is inlined rather than linked because <use href="file.svg#id"> is
 * unreliable in Safari and fails outright cross-origin - a CDN in front of this
 * site would silently blank every ornament.
 */
defined( 'ABSPATH' ) || exit;

$mh_title = isset( $title ) ? trim( (string) $title ) : '';
if ( '' === $mh_title ) {
	return;
}

$mh_kicker   = isset( $kicker )   ? (string) $kicker   : '';
$mh_subtitle = isset( $subtitle ) ? (string) $subtitle : '';
$mh_arc      = isset( $arc_text ) ? (string) $arc_text : '';
$mh_ribbon   = isset( $ribbon )   ? (string) $ribbon   : '';

/*
 * Side ornament. The artwork is an IMAGE from the theme, named in JSON, rather
 * than a vector motif chosen from a hardcoded list - so a different business
 * drops in its own artwork without touching this file.
 *
 * The path is checked against the theme directory with realpath() before it is
 * used: `ornament_image` comes from an admin-editable JSON file, and an
 * unchecked path there is a traversal.
 */
$mh_orn_rel = ( isset( $ornament_image ) && is_string( $ornament_image ) && $ornament_image )
	? ltrim( (string) $ornament_image, '/' )
	: '';   // No default: a board shows side artwork only when JSON asks for it.

$mh_orn_url  = '';
$mh_orn_real = $mh_orn_rel ? realpath( get_theme_file_path( $mh_orn_rel ) ) : false;
$mh_theme_dir = realpath( get_theme_file_path( '' ) );
if ( $mh_orn_real && $mh_theme_dir && 0 === strpos( $mh_orn_real, $mh_theme_dir ) && is_file( $mh_orn_real ) ) {
	$mh_orn_url = get_theme_file_uri( $mh_orn_rel );
}

// Kept only as a body-class hook for per-page tweaks.
$mh_allowed  = array( 'crossed', 'rope', 'chain', 'botanical' );
$mh_ornament = isset( $ornament ) && in_array( $ornament, $mh_allowed, true ) ? $ornament : 'crossed';

/*
 * Type size is chosen here, in viewBox units, from the length of the string.
 * SVG text does not wrap, so a long headline must be set smaller rather than
 * flowing onto a second line. These steps are picked so the longest title in
 * the JSON still clears the frame; header.js trims any remaining overhang.
 */
$mh_len = function_exists( 'mb_strlen' ) ? mb_strlen( $mh_title ) : strlen( $mh_title );
if ( $mh_len <= 10 ) {
	$mh_size = 150;
	$mh_track = 2;
} elseif ( $mh_len <= 16 ) {
	$mh_size = 124;
	$mh_track = 1;
} elseif ( $mh_len <= 24 ) {
	$mh_size = 96;
	$mh_track = 0;
} elseif ( $mh_len <= 34 ) {
	$mh_size = 74;
	$mh_track = 0;
} else {
	$mh_size = 58;
	$mh_track = 0;
}

/*
 * ── Look, from JSON ───────────────────────────────────────────────────────
 * Not just the words: the timber and gold colours, the roughness of the cut
 * edge, the shadow depth and the grain strength are all data too, so a page can
 * carry its own board without a line of CSS. Anything absent falls back to the
 * defaults in header.css.
 *
 * Values are whitelisted and sanitised before they reach the style attribute -
 * an unfiltered JSON string written into `style` is a CSS injection, and the
 * file is editable from the admin screens.
 */
$mh_style_raw = ( isset( $style ) && is_array( $style ) ) ? $style : array();

$mh_colour_map = array(
	'wood_darkest' => '--wood-darkest',
	'wood_dark'    => '--wood-dark',
	'wood_mid'     => '--wood-mid',
	'wood_warm'    => '--wood-warm',
	'wood_light'   => '--wood-light',
	'gold_1'       => '--gold-1',
	'gold_2'       => '--gold-2',
	'gold_3'       => '--gold-3',
	'gold_4'       => '--gold-4',
	'gold_deep'    => '--gold-deep',
	'stone'        => '--stone',
	'stone_light'  => '--stone-light',
	'stone_shade'  => '--stone-shade',
	'stone_deep'   => '--stone-deep',
	'ink'          => '--ink',
	'sheen'        => '--sheen',
	'sunlight'     => '--sunlight',
	'shadow'       => '--shadow',
);

// key => array( css var, min, max, css unit )
$mh_number_map = array(
	'grain'     => array( '--grain-opacity',  0, 1,    '' ),
	'relief'    => array( '--relief-opacity', 0, 1,    '' ),
	'cracks'    => array( '--cracks-opacity', 0, 1,    '' ),
	'gloss'     => array( '--gloss-opacity',  0, 1,    '' ),
	'shade'     => array( '--shade-opacity',  0, 1,    '' ),
	'frame_w'   => array( '--frame-w',        0, 60,   'px' ),
	'board_h'   => array( '--board-h',      120, 720,  'px' ),
	'title_lift' => array( '--title-lift', -80, 80,    'px' ),
);

$mh_vars = array();

foreach ( $mh_colour_map as $mh_key => $mh_var ) {
	if ( empty( $mh_style_raw[ $mh_key ] ) || ! is_string( $mh_style_raw[ $mh_key ] ) ) {
		continue;
	}
	$mh_val = trim( $mh_style_raw[ $mh_key ] );
	// Hex, rgb() or rgba() only - nothing that could carry a url() or a semicolon.
	if ( preg_match( '/^#(?:[0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $mh_val )
		|| preg_match( '/^rgba?\(\s*[0-9]{1,3}\s*,\s*[0-9]{1,3}\s*,\s*[0-9]{1,3}\s*(?:,\s*(?:0|1|0?\.[0-9]{1,3})\s*)?\)$/', $mh_val ) ) {
		$mh_vars[ $mh_var ] = $mh_val;
	}
}

foreach ( $mh_number_map as $mh_key => $mh_spec ) {
	if ( ! isset( $mh_style_raw[ $mh_key ] ) || ! is_numeric( $mh_style_raw[ $mh_key ] ) ) {
		continue;
	}
	$mh_num = min( $mh_spec[2], max( $mh_spec[1], (float) $mh_style_raw[ $mh_key ] ) );
	$mh_vars[ $mh_spec[0] ] = rtrim( rtrim( number_format( $mh_num, 3, '.', '' ), '0' ), '.' ) . $mh_spec[3];
}

/*
 * Roughness has to be rebuilt rather than set: it is the displacement scale
 * INSIDE the mask's data URI, and a custom property cannot reach in there.
 * 0 gives a clean machine-cut edge, higher values a rougher hand-cut one.
 */
$mh_rough = isset( $mh_style_raw['roughness'] ) && is_numeric( $mh_style_raw['roughness'] )
	? min( 30, max( 0, (float) $mh_style_raw['roughness'] ) )
	: null;

if ( null !== $mh_rough ) {
	$mh_vars['--banner'] = "url(\"data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='1200' height='340' preserveAspectRatio='none'><filter id='r'><feTurbulence type='fractalNoise' baseFrequency='0.008 0.02' numOctaves='3' seed='13' result='n'/><feDisplacementMap in='SourceGraphic' in2='n' scale='" . $mh_rough . "' xChannelSelector='R' yChannelSelector='G'/></filter><path d='M 10,72 C 260,10 940,10 1190,72 L 1190,284 C 940,326 260,326 10,284 Z' fill='white' filter='url(%23r)'/></svg>\")";
}

$mh_style_attr = '';
foreach ( $mh_vars as $mh_var => $mh_val ) {
	$mh_style_attr .= $mh_var . ':' . $mh_val . ';';
}

// Inline the sprite once per request. Symbol ids are namespaced app-mh-*.
$mh_sprite      = '';
$mh_sprite_real = realpath( get_theme_file_path( 'components/movie-header/icons.svg' ) );
$mh_theme_real  = realpath( get_theme_file_path( '' ) );
if ( $mh_sprite_real && $mh_theme_real && 0 === strpos( $mh_sprite_real, $mh_theme_real ) ) {
	$mh_sprite = (string) file_get_contents( $mh_sprite_real ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
}

// Unique per instance: SVG ids are document-global, and two boards on one page
// would otherwise share a gradient and a path.
$mh_uid = 'app-mh-' . wp_rand( 1000, 9999 );
$mh_id  = static function ( $suffix ) use ( $mh_uid ) {
	return $mh_uid . '-' . $suffix;
};
?>
<section class="app-mh app-mh--<?php echo esc_attr( $mh_ornament ); ?> app-has-leaves"
         aria-labelledby="<?php echo esc_attr( $mh_id( 'title' ) ); ?>"
         <?php echo $mh_style_attr ? 'style="' . esc_attr( $mh_style_attr ) . '"' : ''; ?>>

	<?php
	// The drifting "air" layer: cane leaves lift off the ground and float up
	// behind the board. The part removes itself on the front page and under
	// prefers-reduced-motion, so this call is safe everywhere.
	// See components/parts/leaf-drift.php + admin/data/decor.json -> "leaves".
	app_component( 'parts/leaf-drift' );
	?>

	<?php if ( $mh_sprite ) : ?>
		<div class="app-mh__sprite" aria-hidden="true"><?php echo $mh_sprite; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme-owned static SVG sprite. ?></div>
	<?php endif; ?>

	<div class="app-mh__stage">

		<?php if ( $mh_kicker ) : ?>
			<p class="app-mh__kicker"><?php echo esc_html( $mh_kicker ); ?></p>
		<?php endif; ?>

		<div class="app-mh__rig">

			<?php if ( $mh_orn_url ) : ?>
				<span class="app-mh__orn app-mh__orn--left" aria-hidden="true">
					<img src="<?php echo esc_url( $mh_orn_url ); ?>" alt="" loading="lazy" decoding="async">
				</span>
			<?php endif; ?>

			<div class="app-mh__board">

				<?php /* Frame and timber are separate masked layers so the banner
				         silhouette (bowed top AND bottom) can be cut from both,
				         while the lettering above them stays unmasked. */ ?>
				<span class="app-mh__frame" aria-hidden="true"></span>
				<span class="app-mh__panel" aria-hidden="true">
					<span class="app-mh__grain"></span>
					<span class="app-mh__relief"></span>
					<span class="app-mh__shade"></span>
					<span class="app-mh__gloss"></span>
					<span class="app-mh__cracks"></span>
				</span>
				<span class="app-mh__rivets" aria-hidden="true"></span>

				<h1 class="app-mh__title" id="<?php echo esc_attr( $mh_id( 'title' ) ); ?>" data-nt-mh-fit>
					<svg class="app-mh__title-svg" viewBox="0 0 1200 330"
					     preserveAspectRatio="xMidYMid meet" focusable="false"
					     data-nt-mh-size="<?php echo esc_attr( (string) $mh_size ); ?>">
						<defs>
							<?php /* Stops carry CLASSES, not stop-color="var(--…)". var() is
							         not substituted inside SVG presentation attributes - only
							         inside CSS declarations - so an inline var() there silently
							         renders black. The colours are applied from header.css. */ ?>
							<linearGradient id="<?php echo esc_attr( $mh_id( 'gold' ) ); ?>"
							                gradientUnits="userSpaceOnUse" x1="0" y1="122" x2="0" y2="300">
								<stop class="app-mh__g1" offset="0%"/>
								<stop class="app-mh__g1" offset="18%"/>
								<stop class="app-mh__g2" offset="44%"/>
								<stop class="app-mh__g3" offset="68%"/>
								<stop class="app-mh__g4" offset="90%"/>
								<stop class="app-mh__g5" offset="100%"/>
							</linearGradient>

							<!-- The headline rides over a hill: low at the ends, high in
							     the middle, matching the bow of the board. -->
							<path id="<?php echo esc_attr( $mh_id( 'curve' ) ); ?>"
							      d="M 96,288 Q 600,152 1104,288" fill="none"/>

							<!-- Shallower arc for the small line above. -->
							<path id="<?php echo esc_attr( $mh_id( 'curve-sub' ) ); ?>"
							      d="M 214,96 Q 600,34 986,96" fill="none"/>
						</defs>

						<?php if ( $mh_subtitle ) : ?>
							<text class="app-mh__sub-text" aria-hidden="true">
								<textPath href="#<?php echo esc_attr( $mh_id( 'curve-sub' ) ); ?>"
								          startOffset="50%" text-anchor="middle">
									<?php echo esc_html( $mh_subtitle ); ?>
								</textPath>
							</text>
						<?php endif; ?>

						<g class="app-mh__type" filter="url(#app-mh-rough-type)" font-size="<?php echo esc_attr( (string) $mh_size ); ?>"
						   letter-spacing="<?php echo esc_attr( (string) $mh_track ); ?>">

							<?php
							/*
							 * Extrusion: the same string stepped downwards in darkening
							 * browns, deepest first. This is what gives the letters a
							 * side wall instead of a flat drop shadow.
							 */
							$mh_depth = array(
								array( 15, '#160c05' ),
								array( 12, '#1d1108' ),
								array( 9, '#25170b' ),
								array( 6, '#2e1c0d' ),
								array( 3, '#3a230f' ),
							);
							foreach ( $mh_depth as $mh_step ) :
								?>
								<g transform="translate(0 <?php echo esc_attr( (string) $mh_step[0] ); ?>)" aria-hidden="true">
									<text class="app-mh__type-cut" fill="<?php echo esc_attr( $mh_step[1] ); ?>"
									      stroke="<?php echo esc_attr( $mh_step[1] ); ?>">
										<textPath href="#<?php echo esc_attr( $mh_id( 'curve' ) ); ?>"
										          startOffset="50%" text-anchor="middle"><?php echo esc_html( $mh_title ); ?></textPath>
									</text>
								</g>
							<?php endforeach; ?>

							<?php /* The face. This is the copy screen readers announce. */ ?>
							<text class="app-mh__type-face"
							      fill="url(#<?php echo esc_attr( $mh_id( 'gold' ) ); ?>)">
								<textPath href="#<?php echo esc_attr( $mh_id( 'curve' ) ); ?>"
								          startOffset="50%" text-anchor="middle"><?php echo esc_html( $mh_title ); ?></textPath>
							</text>

						</g>
					</svg>
				</h1>

				<?php if ( $mh_arc ) : ?>
					<svg class="app-mh__arc" viewBox="0 0 1200 150"
					     preserveAspectRatio="xMidYMid meet"
					     role="img" aria-label="<?php echo esc_attr( $mh_arc ); ?>" focusable="false">
						<defs>
							<path id="<?php echo esc_attr( $mh_id( 'arc' ) ); ?>"
							      d="M 150,44 Q 600,150 1050,44" fill="none"/>
						</defs>
						<text class="app-mh__arc-text">
							<textPath href="#<?php echo esc_attr( $mh_id( 'arc' ) ); ?>"
							          startOffset="50%" text-anchor="middle"><?php echo esc_html( $mh_arc ); ?></textPath>
						</text>
					</svg>
				<?php endif; ?>

				<span class="app-mh__medal" aria-hidden="true">
					<svg viewBox="0 0 120 120" role="presentation" focusable="false">
						<use href="#app-mh-medallion"></use>
					</svg>
				</span>
			</div>

			<?php if ( $mh_orn_url ) : ?>
				<span class="app-mh__orn app-mh__orn--right" aria-hidden="true">
					<img src="<?php echo esc_url( $mh_orn_url ); ?>" alt="" loading="lazy" decoding="async">
				</span>
			<?php endif; ?>

		</div>

		<?php if ( $mh_ribbon ) : ?>
			<p class="app-mh__ribbon"><span><?php echo esc_html( $mh_ribbon ); ?></span></p>
		<?php endif; ?>

	</div>
</section>
