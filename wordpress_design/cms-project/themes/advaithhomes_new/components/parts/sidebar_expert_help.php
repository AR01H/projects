<?php
/**
 * components/parts/sidebar_expert_help.php — Sidebar: Expert Help panel.
 *
 * Props: $expert_help { heading, subtitle, experts[], cta { label, url } }
 * Usage: adn_component( 'parts/sidebar_expert_help', array( 'expert_help' => $ctx['sidebar']['expert_help'] ) );
 */

defined( 'ABSPATH' ) || exit;

$expert_help = isset( $expert_help ) && is_array( $expert_help ) ? $expert_help : array();
$experts     = isset( $expert_help['experts'] ) ? (array) $expert_help['experts'] : array();
$cta         = isset( $expert_help['cta'] )     ? (array) $expert_help['cta']     : array();
?>
<div class="expert-sidebar">
	<?php if ( ! empty( $expert_help['heading'] ) ) : ?>
		<div class="expert-title"><?php echo esc_html( $expert_help['heading'] ); ?></div>
	<?php endif; ?>

	<?php if ( ! empty( $expert_help['subtitle'] ) ) : ?>
		<div class="expert-subtitle"><?php echo esc_html( $expert_help['subtitle'] ); ?></div>
	<?php endif; ?>

	<?php foreach ( $experts as $expert ) : ?>
		<a href="<?php echo esc_url( adn_link( isset( $expert['url'] ) ? $expert['url'] : '' ) ); ?>" class="expert-type">
			<span class="expert-type-icon"><?php echo adn_icon( isset( $expert['icon'] ) ? $expert['icon'] : '' ); ?></span>
			<div class="expert-type-content">
				<div class="expert-type-name"><?php echo esc_html( isset( $expert['name'] ) ? $expert['name'] : '' ); ?></div>
				<div class="expert-type-desc"><?php echo esc_html( isset( $expert['desc'] ) ? $expert['desc'] : '' ); ?></div>
			</div>
			<span class="expert-type-chevron">&rsaquo;</span>
		</a>
	<?php endforeach; ?>

	<?php if ( ! empty( $cta['label'] ) ) : ?>
		<a href="<?php echo esc_url( adn_link( isset( $cta['url'] ) ? $cta['url'] : '' ) ); ?>" class="btn btn-primary sidebar-cta">
			<?php echo esc_html( $cta['label'] ); ?>
		</a>
	<?php endif; ?>
</div>
