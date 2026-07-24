<?php
/**
 * Milestones - a horizontal dated timeline ("milestones over the years").
 *
 * GENERIC: any ordered list of dated moments (company history, roadmap,
 * project phases). Switch data per page with `source`.
 * Data: { tag, title (em allowed), sub, items[] { year, title, desc } }
 */
defined( 'ABSPATH' ) || exit;

$ms_source = ( isset( $source ) && $source ) ? (string) $source : 'milestones';
$data      = nt_data( $ms_source );
$items     = ( is_array( $data ) && ! empty( $data['items'] ) ) ? (array) $data['items'] : array();
if ( empty( $items ) ) {
	return;
}
$tag   = $data['tag']   ?? '';
$title = $data['title'] ?? '';
$sub   = $data['sub']   ?? '';
?>
<section class="nt-milestones" id="milestones">
	<div class="container">

		<?php if ( $tag || $title || $sub ) : ?>
			<div class="nt-section-center">
				<?php if ( $tag ) : ?><div class="nt-section-tag"><?php echo esc_html( $tag ); ?></div><?php endif; ?>
				<?php if ( $title ) : ?>
					<h2 class="section-title"><?php echo wp_kses( $title, array( 'em' => array() ) ); ?></h2>
				<?php endif; ?>
				<?php if ( $sub ) : ?><p class="section-body"><?php echo esc_html( $sub ); ?></p><?php endif; ?>
			</div>
		<?php endif; ?>

		<ol class="nt-milestones__track">
			<?php foreach ( $items as $item ) :
				$item = (array) $item;
				$year = $item['year'] ?? '';
				if ( '' === trim( (string) $year ) ) {
					continue;
				}
			?>
				<li class="nt-milestones__item">
					<span class="nt-milestones__year"><?php echo esc_html( $year ); ?></span>
					<span class="nt-milestones__dot" aria-hidden="true"></span>
					<h3 class="nt-milestones__title"><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
					<?php if ( ! empty( $item['desc'] ) ) : ?>
						<p class="nt-milestones__desc"><?php echo esc_html( $item['desc'] ); ?></p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>

	</div>
</section>
