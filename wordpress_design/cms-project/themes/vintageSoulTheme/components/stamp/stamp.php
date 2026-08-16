<?php

defined( 'ABSPATH' ) || exit;

$center = isset( $center ) ? trim( (string) $center ) : '';
$top    = isset( $top ) ? trim( (string) $top ) : '';
$bottom = isset( $bottom ) ? trim( (string) $bottom ) : '';
$size   = isset( $size ) && (int) $size > 0 ? (int) $size : 160;
$id     = ( isset( $id ) && '' !== trim( (string) $id ) ) ? sanitize_html_class( (string) $id ) : 'stamp';

if ( '' === $center ) {
	return;
}

$top_curve_id    = $id . '-top-curve';
$bottom_curve_id = $id . '-bottom-curve';
$filter_id       = $id . '-gritty';
?>
<svg class="stamp" viewBox="0 0 160 160" width="<?php echo esc_attr( (string) $size ); ?>" height="<?php echo esc_attr( (string) $size ); ?>" role="img" aria-label="<?php echo esc_attr( trim( $top . ' ' . $center . ' ' . $bottom ) ); ?>">
	<defs>
		<path id="<?php echo esc_attr( $top_curve_id ); ?>" d="M 25 80 A 55 55 0 0 1 135 80" />
		<path id="<?php echo esc_attr( $bottom_curve_id ); ?>" d="M 135 80 A 55 55 0 0 1 25 80" />
		<filter id="<?php echo esc_attr( $filter_id ); ?>" x="-10%" y="-10%" width="120%" height="120%">
			<feTurbulence type="fractalNoise" baseFrequency="1.5" numOctaves="3" result="noise" />
			<feColorMatrix type="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 6 -2" in="noise" result="coloredNoise" />
			<feComposite operator="in" in="SourceGraphic" in2="coloredNoise" result="composite" />
		</filter>
	</defs>
	<g class="stamp__mark" filter="url(#<?php echo esc_attr( $filter_id ); ?>)">
		<circle class="stamp__ring stamp__ring--outer" cx="80" cy="80" r="76" fill="none" />
		<circle class="stamp__ring stamp__ring--inner" cx="80" cy="80" r="69" fill="none" />
		<?php if ( '' !== $top ) : ?>
			<text class="stamp__text stamp__text--arc">
				<textPath href="#<?php echo esc_attr( $top_curve_id ); ?>" startOffset="50%" text-anchor="middle"><?php echo esc_html( $top ); ?></textPath>
			</text>
		<?php endif; ?>
		<?php if ( '' !== $bottom ) : ?>
			<text class="stamp__text stamp__text--arc">
				<textPath href="#<?php echo esc_attr( $bottom_curve_id ); ?>" startOffset="50%" text-anchor="middle"><?php echo esc_html( $bottom ); ?></textPath>
			</text>
		<?php endif; ?>
		<text class="stamp__text stamp__text--center" x="80" y="86" text-anchor="middle"><?php echo esc_html( $center ); ?></text>
		<?php if ( '' !== $top || '' !== $bottom ) : ?>
			<line class="stamp__rule" x1="20" y1="60" x2="140" y2="60" />
			<line class="stamp__rule" x1="20" y1="100" x2="140" y2="100" />
		<?php endif; ?>
	</g>
</svg>
