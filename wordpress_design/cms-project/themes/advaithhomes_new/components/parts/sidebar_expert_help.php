<?php
/**
 * components/parts/sidebar_expert_help.php - Sidebar: Expert Help panel.
 *
 * Props: $expert_help { heading, subtitle, experts[], cta { label, url } }
 */

defined( 'ABSPATH' ) || exit;

$expert_help = isset( $expert_help ) && is_array( $expert_help ) ? $expert_help : array();
$experts     = isset( $expert_help['experts'] ) ? (array) $expert_help['experts'] : array();
$cta         = isset( $expert_help['cta'] )     ? (array) $expert_help['cta']     : array();

if ( empty( $cta['label'] ) && empty( $expert_help['heading'] ) ) { return; }
?>
<div class="sw-panel">
	<div class="sw-header">
		<h3 class="sw-title"><?php echo esc_html( isset( $expert_help['heading'] ) ? $expert_help['heading'] : adn_term( 'sidebar.expert_help_heading', 'Need Expert Help?' ) ); ?></h3>
	</div>

	<?php if ( ! empty( $expert_help['subtitle'] ) ) : ?>
		<p class="sw-subtitle"><?php echo esc_html( $expert_help['subtitle'] ); ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $experts ) ) : ?>
		<div class="sw-list" role="list">
			<?php foreach ( $experts as $expert ) : ?>
			<div class="sw-item">
				<a href="<?php echo esc_url( adn_link( isset( $expert['url'] ) ? $expert['url'] : '' ) ); ?>" class="sw-item-link">
					<span class="sw-item-icon" aria-hidden="true"><?php echo adn_icon( isset( $expert['icon'] ) ? $expert['icon'] : '' ); ?></span>
					<span class="sw-item-label">
						<?php echo esc_html( isset( $expert['name'] ) ? $expert['name'] : '' ); ?>
						<?php if ( ! empty( $expert['desc'] ) ) : ?>
							<span class="sw-item-meta"><?php echo esc_html( $expert['desc'] ); ?></span>
						<?php endif; ?>
					</span>
					<span class="sw-item-arrow" aria-hidden="true">›</span>
				</a>
			</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $cta['label'] ) ) : ?>
		<div class="sw-footer">
			<a href="<?php echo esc_url( adn_link( isset( $cta['url'] ) ? $cta['url'] : '' ) ); ?>" class="sw-cta-btn">
				<?php echo esc_html( $cta['label'] ); ?>
			</a>
		</div>
	<?php endif; ?>
</div>
