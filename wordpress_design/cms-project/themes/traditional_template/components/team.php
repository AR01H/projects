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
$data      = nt_data( $tm_source );
$items     = ( is_array( $data ) && ! empty( $data['items'] ) ) ? (array) $data['items'] : array();
if ( empty( $items ) ) {
	return;
}
$tag   = $data['tag']   ?? '';
$title = $data['title'] ?? '';
$sub   = $data['sub']   ?? '';
?>
<section class="nt-team" id="team">
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

		<div class="nt-team__grid">
			<?php foreach ( $items as $item ) :
				$item = (array) $item;
				$name = $item['name'] ?? '';
				if ( '' === trim( (string) $name ) ) {
					continue;
				}
			?>
				<article class="nt-team__card">
					<?php if ( ! empty( $item['photo'] ) ) : ?>
						<figure class="nt-team__photo">
							<img src="<?php echo esc_url( $item['photo'] ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy">
						</figure>
					<?php endif; ?>
					<h3 class="nt-team__name"><?php echo esc_html( $name ); ?></h3>
					<?php if ( ! empty( $item['role'] ) ) : ?>
						<span class="nt-team__role"><?php echo esc_html( $item['role'] ); ?></span>
					<?php endif; ?>
					<?php if ( ! empty( $item['bio'] ) ) : ?>
						<p class="nt-team__bio"><?php echo esc_html( $item['bio'] ); ?></p>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>

	</div>
</section>
