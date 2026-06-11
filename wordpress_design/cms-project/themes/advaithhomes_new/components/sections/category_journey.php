<?php
/**
 * components/sections/category_journey.php — Category journey steps with tip banner.
 *
 * Props: $journey { heading, steps[], tip { icon, text, link_label, link_url } }
 * Usage: adn_component( 'sections/category_journey', array( 'journey' => $ctx['journey'] ) );
 *
 * NOTE: tip.text may contain <strong> for emphasis — rendered with wp_kses.
 */

defined( 'ABSPATH' ) || exit;

$journey = isset( $journey ) && is_array( $journey ) ? $journey : array();
$steps   = isset( $journey['steps'] ) ? (array) $journey['steps'] : array();
$tip     = isset( $journey['tip'] )   ? (array) $journey['tip']   : array();
?>
<?php if ( ! empty( $journey['heading'] ) ) : ?>
	<h2 class="journey-section-heading"><?php echo esc_html( $journey['heading'] ); ?></h2>
<?php endif; ?>

<?php if ( $steps ) : ?>
	<div class="journey-steps">
		<?php foreach ( $steps as $step ) : ?>
			<?php $active = ! empty( $step['active'] ) ? ' active' : ''; ?>
			<div class="journey-step<?php echo esc_attr( $active ); ?>">
				<div class="step-dot"><?php echo esc_html( isset( $step['icon'] ) ? $step['icon'] : '' ); ?></div>
				<div class="step-num"><?php echo esc_html( isset( $step['num'] ) ? $step['num'] . '.' : '' ); ?></div>
				<div class="step-label"><?php echo esc_html( isset( $step['label'] ) ? $step['label'] : '' ); ?></div>
				<div class="step-desc"><?php echo esc_html( isset( $step['desc'] ) ? $step['desc'] : '' ); ?></div>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<?php if ( $tip ) : ?>
	<div class="journey-tip">
		<div class="journey-tip-left">
			<span class="journey-tip-icon"><?php echo esc_html( isset( $tip['icon'] ) ? $tip['icon'] : '' ); ?></span>
			<span class="journey-tip-text"><?php
				echo wp_kses(
					isset( $tip['text'] ) ? $tip['text'] : '',
					array( 'strong' => array() )
				);
			?></span>
		</div>
		<?php if ( ! empty( $tip['link_label'] ) && ! empty( $tip['link_url'] ) ) : ?>
			<a href="<?php echo esc_url( adn_link( $tip['link_url'] ) ); ?>" class="btn btn-outline btn-sm">
				<?php echo esc_html( $tip['link_label'] ); ?>
			</a>
		<?php endif; ?>
	</div>
<?php endif; ?>
