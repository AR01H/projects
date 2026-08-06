<?php
/**
 * components/parts/stamp.php - THE rubber stamp.
 *
 * One reusable ink stamp for the whole site: a ring of text around the top, a
 * ring around the bottom, and a line or two struck across the middle.
 *
 * WHY IT IS SVG, NOT CSS
 * The previous stamps set their curved text with rotated spans. That is what
 * produced the broken one on the franchise page - the bottom of the ring came
 * out upside down and mirrored, and the middle line ran outside the circle
 * because nothing was measuring it. SVG <textPath> bends real text along a
 * real arc: the bottom arc is simply drawn left-to-right along the bottom, so
 * the letters sit the right way up, and `textLength` on the middle line makes
 * it fit the circle instead of overflowing it.
 *
 * The text stays real text - selectable, translatable, and available to a
 * screen reader through the <title> - rather than being baked into an image.
 *
 * Args (all optional except one of top/middle):
 *   top      string  Text around the upper arc.
 *   bottom   string  Text around the lower arc (reads left to right).
 *   middle   string|array  One or two struck lines across the centre.
 *   small    string  A tiny line under the middle - a year, a batch number.
 *   tone     string  ink | gold | red  (the pad colour). Default ink.
 *   size     int     Pixel diameter. Default 168.
 *   tilt     int     Degrees of rotation. Default -8, like a hand stamp.
 *   class    string  Extra class.
 *
 *   nt_component( 'parts/stamp', array(
 *       'top'    => 'Family Business',
 *       'bottom' => 'Full Support',
 *       'middle' => 'Proven Model',
 *   ) );
 */

defined( 'ABSPATH' ) || exit;

$nt_top    = trim( (string) ( $top ?? '' ) );
$nt_bottom = trim( (string) ( $bottom ?? '' ) );
$nt_small  = trim( (string) ( $small ?? '' ) );

$nt_middle = $middle ?? '';
$nt_middle = is_array( $nt_middle ) ? array_values( array_filter( array_map( 'trim', array_map( 'strval', $nt_middle ) ) ) )
	: array_filter( array( trim( (string) $nt_middle ) ) );
$nt_middle = array_slice( $nt_middle, 0, 2 );   // two struck lines is the limit that stays legible

if ( '' === $nt_top && '' === $nt_bottom && empty( $nt_middle ) ) {
	return;
}

$nt_tone = in_array( (string) ( $tone ?? 'ink' ), array( 'ink', 'gold', 'red' ), true ) ? (string) $tone : 'ink';
$nt_size = max( 90, min( 420, (int) ( $size ?? 168 ) ) );
$nt_tilt = max( -25, min( 25, (int) ( $tilt ?? -8 ) ) );

// Unique ids: several stamps can share a page, and duplicate SVG ids would
// make every one of them follow the first one's arcs.
$nt_uid = 'nt-stamp-' . wp_unique_id();

// Accessible name: the whole stamp read as one phrase.
$nt_label = trim( implode( ' — ', array_filter( array_merge( array( $nt_top ), $nt_middle, array( $nt_bottom, $nt_small ) ) ) ) );
?>
<span class="nt-stamp nt-stamp--<?php echo esc_attr( $nt_tone ); ?> <?php echo esc_attr( $class ?? '' ); ?>"
      style="--nt-stamp-size:<?php echo esc_attr( (string) $nt_size ); ?>px;--nt-stamp-tilt:<?php echo esc_attr( (string) $nt_tilt ); ?>deg;">
	<svg class="nt-stamp__svg" viewBox="0 0 200 200" role="img"
	     aria-label="<?php echo esc_attr( $nt_label ); ?>">
		<defs>
			<?php /* Upper arc: drawn left-to-right OVER the top, so the text
			         rides the outside of the curve the right way up. */ ?>
			<path id="<?php echo esc_attr( $nt_uid ); ?>-top"
			      d="M 28,100 A 72,72 0 0 1 172,100" fill="none"/>

			<?php /* Lower arc: ALSO drawn left-to-right, but along the bottom
			         (sweep flag 0). Reusing the top path with a flip is what
			         put the old stamp's lower text on its head. */ ?>
			<path id="<?php echo esc_attr( $nt_uid ); ?>-bottom"
			      d="M 32,100 A 68,68 0 0 0 168,100" fill="none"/>
		</defs>

		<?php /* Two rings: a heavy outer and a hairline inner, the way a real
		         die is cut. */ ?>
		<circle class="nt-stamp__ring" cx="100" cy="100" r="92" />
		<circle class="nt-stamp__ring nt-stamp__ring--thin" cx="100" cy="100" r="80" />

		<?php if ( '' !== $nt_top ) : ?>
			<text class="nt-stamp__arc">
				<textPath href="#<?php echo esc_attr( $nt_uid ); ?>-top" startOffset="50%" text-anchor="middle">
					<?php echo esc_html( $nt_top ); ?>
				</textPath>
			</text>
		<?php endif; ?>

		<?php if ( '' !== $nt_bottom ) : ?>
			<text class="nt-stamp__arc nt-stamp__arc--bottom">
				<textPath href="#<?php echo esc_attr( $nt_uid ); ?>-bottom" startOffset="50%" text-anchor="middle">
					<?php echo esc_html( $nt_bottom ); ?>
				</textPath>
			</text>
		<?php endif; ?>

		<?php
		// Side stars, the usual separators between the two arcs of a die.
		if ( '' !== $nt_top && '' !== $nt_bottom ) :
			?>
			<circle class="nt-stamp__pip" cx="16" cy="100" r="3.4"/>
			<circle class="nt-stamp__pip" cx="184" cy="100" r="3.4"/>
		<?php endif; ?>

		<?php if ( ! empty( $nt_middle ) ) : ?>
			<?php
			// Rules above and below the struck line - the classic die layout.
			$nt_two = count( $nt_middle ) > 1;
			?>
			<line class="nt-stamp__rule" x1="44" y1="<?php echo $nt_two ? '76' : '84'; ?>" x2="156" y2="<?php echo $nt_two ? '76' : '84'; ?>"/>
			<line class="nt-stamp__rule" x1="44" y1="<?php echo $nt_two ? '128' : '120'; ?>" x2="156" y2="<?php echo $nt_two ? '128' : '120'; ?>"/>

			<?php foreach ( $nt_middle as $nt_i => $nt_line ) : ?>
				<?php
				// textLength + lengthAdjust is what keeps a long phrase INSIDE
				// the die instead of running out over the ring.
				$nt_y = $nt_two ? ( 96 + $nt_i * 24 ) : 108;
				?>
				<text class="nt-stamp__mid<?php echo $nt_two ? ' nt-stamp__mid--two' : ''; ?>"
				      x="100" y="<?php echo esc_attr( (string) $nt_y ); ?>"
				      text-anchor="middle"
				      textLength="<?php echo $nt_two ? '104' : '112'; ?>"
				      lengthAdjust="spacingAndGlyphs">
					<?php echo esc_html( $nt_line ); ?>
				</text>
			<?php endforeach; ?>
		<?php endif; ?>

		<?php if ( '' !== $nt_small ) : ?>
			<text class="nt-stamp__small" x="100" y="<?php echo empty( $nt_middle ) ? '112' : '146'; ?>" text-anchor="middle">
				<?php echo esc_html( $nt_small ); ?>
			</text>
		<?php endif; ?>
	</svg>
</span>
