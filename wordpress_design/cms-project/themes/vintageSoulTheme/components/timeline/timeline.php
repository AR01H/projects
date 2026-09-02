<?php
/**
 * VintageSoulTheme - Reusable Timeline / Milestones Component
 *
 * Renders an authentic vintage vertical timeline with dotted gold/bronze track,
 * botanical milestone nodes, year badges, and parchment note cards.
 */

defined( 'ABSPATH' ) || exit;

use VintageSoul\Support\View;

$items       = isset( $items ) && is_array( $items ) ? $items : array();
$tag         = (string) ( $tag ?? '' );
$title       = (string) ( $title ?? '' );
$sub         = (string) ( $sub ?? '' );
$variant     = (string) ( $variant ?? 'parchment' );
$extra_class = isset( $class ) ? (string) $class : '';

if ( empty( $items ) ) {
	return;
}
?>
<section class="section milestone-timeline-section<?php echo '' !== $extra_class ? ' ' . esc_attr( $extra_class ) : ''; ?>">
	<div class="container container--narrow">
		<?php if ( '' !== $title || '' !== $tag ) : ?>
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'     => $tag,
					'title'   => $title,
					'sub'     => $sub,
					'variant' => $variant,
				)
			);
			?>
		<?php endif; ?>

		<div class="milestone-timeline">
			<?php foreach ( $items as $idx => $ms ) :
				$step_badge = (string) ( $ms['step'] ?? ( $ms['badge'] ?? ( $ms['phase'] ?? sprintf( 'STAGE %02d', $idx + 1 ) ) ) );
				$m_ttl      = (string) ( $ms['title'] ?? '' );
				$m_dsc      = (string) ( $ms['desc'] ?? ( $ms['text'] ?? '' ) );
			?>
				<div class="milestone-item">
					<div class="milestone-item__year"><?php echo esc_html( $step_badge ); ?></div>
					<div class="milestone-item__content frame--rough-cut">
						<?php if ( '' !== $m_ttl ) : ?>
							<h4 class="milestone-item__title"><?php echo esc_html( $m_ttl ); ?></h4>
						<?php endif; ?>
						<?php if ( '' !== $m_dsc ) : ?>
							<p class="milestone-item__desc"><?php echo esc_html( $m_dsc ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
