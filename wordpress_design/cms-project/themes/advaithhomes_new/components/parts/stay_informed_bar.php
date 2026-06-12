<?php
/**
 * components/parts/stay_informed_bar.php — Horizontal newsletter strip.
 *
 * Distinct from newsletter_cta (which is a full centred section).
 * This is a compact horizontal bar — icon + title/desc on the left, form on the right.
 * Form is presentational for now; wire to a REST route when ready.
 *
 * Props: $stay_informed { icon, title, description, placeholder, button_label, note }
 * Usage: adn_component( 'parts/stay_informed_bar', array( 'stay_informed' => $ctx['stay_informed'] ) );
 */

defined( 'ABSPATH' ) || exit;

$si = isset( $stay_informed ) && is_array( $stay_informed ) ? $stay_informed : array();
?>
<div class="stay-informed-inner">
	<div class="stay-informed-content">
		<?php if ( ! empty( $si['icon'] ) ) : ?>
			<div class="stay-informed-icon"><?php echo adn_icon( $si['icon'] ); ?></div>
		<?php endif; ?>
		<?php if ( ! empty( $si['title'] ) ) : ?>
			<h3><?php echo esc_html( $si['title'] ); ?></h3>
		<?php endif; ?>
		<?php if ( ! empty( $si['description'] ) ) : ?>
			<p><?php echo esc_html( $si['description'] ); ?></p>
		<?php endif; ?>
	</div>
	<form class="stay-informed-form" onsubmit="return false;">
		<input type="email"
		       placeholder="<?php echo esc_attr( isset( $si['placeholder'] ) ? $si['placeholder'] : '' ); ?>"
		       aria-label="<?php echo esc_attr( isset( $si['placeholder'] ) ? $si['placeholder'] : esc_html__( 'Email address', ADN_TEXT_DOMAIN ) ); ?>" />
		<button type="submit" class="btn btn-accent">
			<?php echo esc_html( isset( $si['button_label'] ) ? $si['button_label'] : '' ); ?>
		</button>
		<?php if ( ! empty( $si['note'] ) ) : ?>
			<div class="newsletter-spam"><?php echo esc_html( $si['note'] ); ?></div>
		<?php endif; ?>
	</form>
</div>
