<?php
/**
 * Horizontal image gallery strip.
 * Desktop: all cards in one row (horizontal scroll if overflow).
 * Mobile (≤767px): single-card carousel with dots + arrows + swipe.
 *
 * Args:
 *  tag       (string)  Eyebrow tag.                    Default: 'Gallery'
 *  title     (string)  Heading HTML.                   Default: ''
 *  body      (string)  Intro text.                     Default: ''
 *  modifier  (string)  Extra CSS class on section.     Default: ''
 *  id        (string)  Unique ID for JS hooks.         Default: 'ch-gstrip'
 *  bg        (string)  CSS background value.           Default: 'var(--ch-white)'
 *  images    (array)   Array of [ 'src', 'label', 'desc' ]
 */
defined( 'ABSPATH' ) || exit;

$tag      = $args['tag']      ?? 'Gallery';
$title    = $args['title']    ?? '';
$body     = $args['body']     ?? '';
$modifier = $args['modifier'] ?? '';
$id       = $args['id']       ?? 'ch-gstrip';
$bg       = $args['bg']       ?? 'var(--ch-white)';
$images   = $args['images']   ?? [];

if ( empty( $images ) ) return;

$allowed      = [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ];
$section_cls  = trim( 'ch-gallery-strip-section ' . esc_attr( $modifier ) );
$track_id     = esc_attr( $id ) . '-track';
$dots_id      = esc_attr( $id ) . '-dots';
$prev_id      = esc_attr( $id ) . '-prev';
$next_id      = esc_attr( $id ) . '-next';
?>

<section class="<?php echo $section_cls; ?>" style="background:<?php echo esc_attr( $bg ); ?>;">
	<div class="container">

		<?php if ( $tag || $title ) : ?>
			<div class="ch-section-center fade-up">
				<?php if ( $tag ) : ?><div class="section-tag"><?php echo esc_html( $tag ); ?></div><?php endif; ?>
				<?php if ( $title ) : ?><h2 class="section-title"><?php echo wp_kses( $title, $allowed ); ?></h2><?php endif; ?>
				<?php if ( $body )  : ?><p class="section-body"><?php echo esc_html( $body ); ?></p><?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="ch-gstrip fade-up" data-id="<?php echo esc_attr( $id ); ?>">
			<div class="ch-gstrip__track" id="<?php echo $track_id; ?>">
				<?php foreach ( $images as $i => $img ) : ?>
					<div class="ch-gstrip__card<?php echo $i === 0 ? ' active' : ''; ?>">
						<img src="<?php echo esc_url( $img['src'] ?? '' ); ?>"
							alt="<?php echo esc_attr( $img['label'] ?? '' ); ?>"
							loading="lazy"
							class="ch-gstrip__img">
						<?php if ( ! empty( $img['label'] ) ) : ?>
							<div class="ch-gstrip__caption">
								<strong><?php echo esc_html( $img['label'] ); ?></strong>
								<?php if ( ! empty( $img['desc'] ) ) : ?>
									<span><?php echo esc_html( $img['desc'] ); ?></span>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- Mobile carousel nav -->
			<div class="ch-gstrip__nav">
				<div class="ch-gstrip__dots" id="<?php echo $dots_id; ?>" role="tablist" aria-label="Gallery navigation">
					<?php foreach ( $images as $i => $_ ) : ?>
						<button class="ch-dot<?php echo $i === 0 ? ' active' : ''; ?>"
							role="tab"
							aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
							aria-label="Image <?php echo $i + 1; ?>"></button>
					<?php endforeach; ?>
				</div>
				<div class="ch-gstrip__arrows">
					<button class="ch-v-btn" id="<?php echo $prev_id; ?>" aria-label="Previous image">←</button>
					<button class="ch-v-btn" id="<?php echo $next_id; ?>" aria-label="Next image">→</button>
				</div>
			</div>
		</div>

	</div>
</section>
