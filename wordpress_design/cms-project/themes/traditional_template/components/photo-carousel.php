<?php
/**
 * Vintage Hanging Photo Gallery
 * Replaces the generic carousel with a clothesline polaroid layout.
 */
defined( 'ABSPATH' ) || exit;

$images  = nt_data( 'photo_carousel' ) ?? [];
if ( empty( $images ) ) return;

$content = nt_data( 'content' )['photo_carousel'] ?? [];

// We'll use a fixed set of rotations so they look organic but don't jump around on page load.
$rotations = [ '-3deg', '4deg', '-2deg', '5deg', '-4deg', '2deg' ];
?>

<section class="nt-gallery-hanging" id="gallery">
	<div class="container">
		<?php
		get_template_part( 'components/parts/section-header-dark', null, [
			'tag'   => $content['tag']     ?? 'Gallery',
			'title' => 'Photo <em>Gallery</em>',
			'body'  => $content['body']    ?? '',
		] );
		?>

		<div class="nt-gallery-hanging__clothesline">
			<!-- The rope spanning across -->
			<div class="nt-gallery-hanging__rope"></div>

			<!-- The hanging polaroids -->
			<div class="nt-gallery-hanging__items">
				<?php foreach ( $images as $i => $img ) : 
					$rot = $rotations[ $i % count($rotations) ];
				?>
					<div class="nt-gallery-polaroid" style="--rot: <?php echo $rot; ?>;">
						<!-- The wooden clothespin -->
						<div class="nt-gallery-polaroid__pin"></div>
						
						<div class="nt-gallery-polaroid__inner">
							<div class="nt-gallery-polaroid__photo">
								<img src="<?php echo esc_url( $img['src'] ?? '' ); ?>"
									 alt="<?php echo esc_attr( $img['label'] ?? 'Gallery image' ); ?>"
									 loading="lazy">
							</div>
							
							<?php if ( ! empty( $img['label'] ) ) : ?>
								<div class="nt-gallery-polaroid__caption">
									<?php echo esc_html( $img['label'] ); ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
