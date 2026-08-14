<?php
/**
 * components/decorative/leaf-drift.php - the drifting "air" layer.
 *
 * Cane leaves lift off the ground and float up through a heading band, each
 * on its own path. Used behind every INNER page heading; the home page is
 * deliberately left clean, so the caller (or the `home` guard below) skips it
 * there.
 *
 * Everything is data:
 *   admin/data/decor.json -> "leaves": {
 *     "enabled": true,
 *     "count": 14,               // how many leaves are in the air
 *     "shapes": ["leaf","cane"], // which silhouettes to cycle through
 *     "opacity": 0.34,           // ceiling for the per-leaf random fade
 *     "ground": "assets/images/backgrounds/ground-cane.svg",
 *     "pages_off": ["home"]      // page keys that never get the layer
 *   }
 *
 * The leaves are inline SVG painted with currentColor, so they take the
 * section's own ink colour - a dark band gets pale leaves and a parchment
 * band gets green ones with no extra rules.
 *
 * assets/js/ui-kit.js randomises each leaf's x-position, delay, duration,
 * drift, spin, scale and opacity on load, so no two visits look the same, and
 * removes the whole layer under prefers-reduced-motion.
 *
 * Args:
 *   count   int     Override the JSON count.
 *   ground  bool    Draw the ground silhouette along the bottom edge (default true).
 *   class   string  Extra class on the wrapper.
 */

defined( 'ABSPATH' ) || exit;

$nt_decor  = App_Helpers::data( 'decor' );
$nt_leaves = ( isset( $nt_decor['leaves'] ) && is_array( $nt_decor['leaves'] ) ) ? $nt_decor['leaves'] : array();

if ( empty( $nt_leaves['enabled'] ) ) {
	return;
}

// Never on the pages listed in JSON (home by default) - the front page keeps
// its own cleaner treatment.
$nt_page_key = (string) get_query_var( 'app_active_page' );
$nt_pages_off = array_map( 'strval', (array) ( $nt_leaves['pages_off'] ?? array( 'home' ) ) );
if ( in_array( $nt_page_key, $nt_pages_off, true ) || is_front_page() ) {
	return;
}

$nt_count = isset( $count ) ? (int) $count : (int) ( $nt_leaves['count'] ?? 12 );
$nt_count = max( 1, min( 40, $nt_count ) );   // keep the DOM (and the GPU) sane

$nt_shapes = array_values( array_filter( array_map( 'strval', (array) ( $nt_leaves['shapes'] ?? array( 'leaf' ) ) ) ) );
if ( empty( $nt_shapes ) ) {
	$nt_shapes = array( 'leaf' );
}

$nt_ceiling = (float) ( $nt_leaves['opacity'] ?? 0.34 );
$nt_ground  = ( ! isset( $ground ) || $ground ) ? (string) ( $nt_leaves['ground'] ?? '' ) : '';

/**
 * The silhouettes. Kept here rather than in NT_Icons because these are
 * decorative artwork (filled, organic) not UI icons (stroked, 24px grid).
 */
$nt_shape_svg = array(
	'leaf'  => '<svg viewBox="0 0 40 24" fill="currentColor" aria-hidden="true"><path d="M39 2c-14-3-27 1-34 9-3 3-4 7-4 11 4-1 8-3 11-6 6-6 15-11 26-13-9 4-17 9-22 16-2 3-4 6-4 9 5-1 10-4 14-8C33 14 38 8 39 2Z"/></svg>',
	'blade' => '<svg viewBox="0 0 40 16" fill="currentColor" aria-hidden="true"><path d="M0 14c8-9 22-14 40-14-6 7-16 12-27 14-4 1-9 1-13 0Z"/></svg>',
	'cane'  => '<svg viewBox="0 0 14 40" fill="currentColor" aria-hidden="true"><path d="M4 0h6v40H4z" opacity=".85"/><path d="M4 9h6v1.6H4zM4 19h6v1.6H4zM4 29h6v1.6H4z" opacity=".45"/></svg>',
	'seed'  => '<svg viewBox="0 0 18 18" fill="currentColor" aria-hidden="true"><path d="M9 0c5 4 7 8 7 11a7 7 0 1 1-14 0C2 8 4 4 9 0Z"/></svg>',
);
?>
<div class="app-leaves <?php echo esc_attr( $class ?? '' ); ?>" data-nt-leaves aria-hidden="true"
     style="--app-leaf-ceiling:<?php echo esc_attr( (string) $nt_ceiling ); ?>;">

	<?php if ( '' !== $nt_ground ) : ?>
		<span class="app-leaves__ground"
		      style="background-image:url('<?php echo esc_url( get_template_directory_uri() . '/' . ltrim( $nt_ground, '/' ) ); ?>');"></span>
	<?php endif; ?>

	<?php
	for ( $nt_i = 0; $nt_i < $nt_count; $nt_i++ ) :
		$nt_shape = $nt_shapes[ $nt_i % count( $nt_shapes ) ];
		$nt_svg   = $nt_shape_svg[ $nt_shape ] ?? $nt_shape_svg['leaf'];
		?>
		<span class="app-leaf app-leaf--<?php echo esc_attr( sanitize_html_class( $nt_shape ) ); ?>">
			<?php echo $nt_svg; // phpcs:ignore WordPress.Security.EscapeOutput -- constant decorative SVG. ?>
		</span>
	<?php endfor; ?>
</div>
