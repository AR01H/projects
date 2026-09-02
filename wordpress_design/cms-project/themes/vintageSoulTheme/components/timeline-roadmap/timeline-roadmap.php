<?php
/**
 * VintageSoulTheme - Reusable Alternating Timeline Roadmap Component
 *
 * Renders an editorial zigzag timeline with a vertical spine,
 * numbered milestone nodes, and era-badge cards alternating left/right.
 *
 * Props:
 *   milestones (array) - Array of { era, location, title, desc, icon } objects
 */
defined( 'ABSPATH' ) || exit;

$milestones = isset( $milestones ) ? (array) $milestones : array();

if ( empty( $milestones ) ) {
	return;
}
?>
<div class="vintage-timeline-roadmap">
	<div class="timeline-roadmap-spine" aria-hidden="true"></div>
	<?php foreach ( $milestones as $m_idx => $mile ) :
		$m_era   = (string) ( $mile['era'] ?? '' );
		$m_loc   = (string) ( $mile['location'] ?? '' );
		$m_ttl   = (string) ( $mile['title'] ?? '' );
		$m_dsc   = (string) ( $mile['desc'] ?? '' );
		$m_ico   = (string) ( $mile['icon'] ?? '🌱' );
		$is_even = ( 0 === $m_idx % 2 );
	?>
		<div class="timeline-milestone-row<?php echo $is_even ? ' timeline-milestone-row--left' : ' timeline-milestone-row--right'; ?>">
			<div class="timeline-milestone-node" aria-hidden="true">
				<span class="timeline-milestone-node__num"><?php echo esc_html( sprintf( '%02d', $m_idx + 1 ) ); ?></span>
			</div>
			<div class="timeline-milestone-card frame--rough-cut">
				<div class="timeline-milestone-card__header">
					<div class="timeline-milestone-card__era-badge">
						<span class="timeline-milestone-card__icon"><?php echo esc_html( $m_ico ); ?></span>
						<strong class="timeline-milestone-card__era"><?php echo esc_html( $m_era ); ?></strong>
					</div>
					<?php if ( '' !== $m_loc ) : ?>
						<span class="timeline-milestone-card__loc">📍 <?php echo esc_html( $m_loc ); ?></span>
					<?php endif; ?>
				</div>
				<h3 class="timeline-milestone-card__title"><?php echo esc_html( $m_ttl ); ?></h3>
				<p class="timeline-milestone-card__desc"><?php echo esc_html( $m_dsc ); ?></p>
			</div>
		</div>
	<?php endforeach; ?>
</div>
