<?php
/**
 * Generic image gallery strip/grid.
 * Desktop: all cards in one row (horizontal scroll if overflow).
 * Mobile (≤767px): single-card carousel with dots + arrows + swipe.
 *
 * Switch data per page with `source` (defaults to gallery).
 */
defined( 'ABSPATH' ) || exit;

$gg_source = ( isset( $source ) && $source ) ? (string) $source : 'gallery';
$_d       = nt_data( $gg_source ) ?: [];
$tag      = $args['tag']      ?? $_d['tag'] ?? 'Gallery';
$title    = $args['title']    ?? $_d['title'] ?? 'Our Gallery';
$body     = $args['body']     ?? $_d['body'] ?? '';
$modifier = $args['modifier'] ?? '';
$id       = $args['id']       ?? 'nt-gstrip';
$images   = $args['images']   ?? $_d['images'] ?? [];

if ( empty( $images ) ) return;

$section_cls  = trim( 'nt-gallery-strip-section section ' . esc_attr( $modifier ) );
$track_id     = esc_attr( $id ) . '-track';
$dots_id      = esc_attr( $id ) . '-dots';
$prev_id      = esc_attr( $id ) . '-prev';
$next_id      = esc_attr( $id ) . '-next';
?>

<section class="<?php echo $section_cls; ?>">
	<div class="container wrapper">

		<?php get_template_part( 'components/parts/section-header', null, [
			'tag'          => $tag,
			'title'        => $title,
			'body'         => $body,
			// The vintage gallery-strip layout styles a green header TILE beside the
			// photo strip via `.nt-gallery-strip-section .nt-section-header`; the
			// default wrapper (`nt-section-center`) doesn't match that selector, so
			// the tile never rendered and the cream title fell onto the parchment.
			'wrapper_base' => 'nt-section-header fade-up',
		] ); ?>

		<div class="nt-gstrip fade-up" data-id="<?php echo esc_attr( $id ); ?>" data-nt-strip>
			<div class="nt-gstrip__track" id="<?php echo $track_id; ?>" data-nt-lightbox data-nt-strip-track
			     tabindex="0" role="region"
			     aria-label="<?php esc_attr_e( 'Photo strip, scrollable', NT_TEXT_DOMAIN ); ?>">
				<?php foreach ( $images as $i => $img ) : ?>
					<div class="nt-gstrip__card<?php echo $i === 0 ? ' active' : ''; ?>">
						<img src="<?php echo esc_url( $img['src'] ?? $img ); ?>"
							alt="<?php echo esc_attr( $img['label'] ?? 'Gallery image' ); ?>"
							loading="lazy"
							class="nt-gstrip__img">
						<?php if ( ! empty( $img['label'] ) ) : ?>
							<div class="nt-gstrip__caption">
								<strong><?php echo esc_html( $img['label'] ); ?></strong>
								<?php if ( ! empty( $img['desc'] ) ) : ?>
									<span><?php echo esc_html( $img['desc'] ); ?></span>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<?php /*
			   Desktop controls. Separate from the mobile nav below on purpose:
			   the two carousels work by different mechanisms and must not share
			   buttons. Mobile shows ONE card and legacy.js toggles .active
			   between them; desktop shows the whole row and scrolls the track.
			   initStripCarousel() in common.js drives these and removes them
			   entirely when the photos already fit, so they are never dead.
			*/ ?>
			<div class="nt-gstrip__scroll-nav" data-nt-strip-nav hidden>
				<button type="button" class="nt-gstrip__scroll-btn" data-nt-strip-prev
				        aria-label="<?php esc_attr_e( 'Scroll photos left', NT_TEXT_DOMAIN ); ?>"
				        aria-controls="<?php echo $track_id; ?>">&#8592;</button>
				<button type="button" class="nt-gstrip__scroll-btn" data-nt-strip-next
				        aria-label="<?php esc_attr_e( 'Scroll photos right', NT_TEXT_DOMAIN ); ?>"
				        aria-controls="<?php echo $track_id; ?>">&#8594;</button>
			</div>

			<!-- Mobile carousel nav -->
			<div class="nt-gstrip__nav">
				<div class="nt-gstrip__dots" id="<?php echo $dots_id; ?>" role="tablist" aria-label="Gallery navigation">
					<?php foreach ( $images as $i => $_ ) : ?>
						<button class="nt-dot<?php echo $i === 0 ? ' active' : ''; ?>"
							role="tab"
							aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
							aria-label="Image <?php echo $i + 1; ?>"></button>
					<?php endforeach; ?>
				</div>
				<div class="nt-gstrip__arrows">
					<button class="nt-v-btn button" id="<?php echo $prev_id; ?>" aria-label="Previous image">←</button>
					<button class="nt-v-btn button" id="<?php echo $next_id; ?>" aria-label="Next image">→</button>
				</div>
			</div>
		</div>

	</div>
</section>
