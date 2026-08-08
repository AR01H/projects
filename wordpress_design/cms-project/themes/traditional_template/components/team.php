<?php
/**
 * Team - people cards (photo, name, role, short bio, optional links).
 *
 * GENERIC: any group of people (staff, founders, trainers, practitioners).
 * Switch data per page with `source`.
 * Data: { tag, title (em allowed), sub, items[] { name, role, photo, bio, link } }
 */
defined( 'ABSPATH' ) || exit;

$tm_source = ( isset( $source ) && $source ) ? (string) $source : 'team';
$data      = App_Helpers::data( $tm_source );
$items     = ( is_array( $data ) && ! empty( $data['items'] ) ) ? (array) $data['items'] : array();
if ( empty( $items ) ) {
	return;
}
$tag   = $data['tag']   ?? '';
$title = $data['title'] ?? '';
$sub   = $data['sub']   ?? '';
?>
<section class="app-team" id="team">
	<div class="container">

		<?php get_template_part( 'components/parts/section-header', null, array(
	'tag'   => $tag ?? '',
	'title' => $title ?? '',
	'body'  => $sub ?? ''
) ); ?>

		<div class="app-team__grid">
			<?php foreach ( $items as $item ) :
				$item = (array) $item;
				$name = $item['name'] ?? '';
				if ( '' === trim( (string) $name ) ) {
					continue;
				}
			?>
				<article class="app-team__card">
					<?php if ( ! empty( $item['photo'] ) ) : ?>
						<figure class="app-team__photo">
							<img src="<?php echo esc_url( $item['photo'] ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy">
						</figure>
					<?php endif; ?>
					<h3 class="app-team__name"><?php echo esc_html( $name ); ?></h3>
					<?php if ( ! empty( $item['role'] ) ) : ?>
						<span class="app-team__role"><?php echo esc_html( $item['role'] ); ?></span>
					<?php endif; ?>
					<?php if ( ! empty( $item['bio'] ) ) : ?>
						<p class="app-team__bio"><?php echo esc_html( $item['bio'] ); ?></p>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>

	</div>
</section>
