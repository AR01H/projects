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
$data      = App_Helpers::data( $ms_source );
$items     = ( is_array( $data ) && ! empty( $data['items'] ) ) ? (array) $data['items'] : array();
if ( empty( $items ) ) {
	return;
}
$tag   = $data['tag']   ?? '';
$title = $data['title'] ?? '';
$sub   = $data['sub']   ?? '';
?>
<section class="app-milestones" id="milestones">
	<div class="container">

		<?php get_template_part( 'components/section-heading/section-header', null, array(
	'tag'   => $tag ?? '',
	'title' => $title ?? '',
	'body'  => $sub ?? ''
) ); ?>

		<ol class="app-milestones__track">
			<?php foreach ( $items as $item ) :
				$item = (array) $item;
				$year = $item['year'] ?? '';
				if ( '' === trim( (string) $year ) ) {
					continue;
				}
			?>
				<li class="app-milestones__item">
					<span class="app-milestones__year"><?php echo esc_html( $year ); ?></span>
					<span class="app-milestones__dot" aria-hidden="true"></span>
					<h3 class="app-milestones__title"><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
					<?php if ( ! empty( $item['desc'] ) ) : ?>
						<p class="app-milestones__desc"><?php echo esc_html( $item['desc'] ); ?></p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>

	</div>
</section>
