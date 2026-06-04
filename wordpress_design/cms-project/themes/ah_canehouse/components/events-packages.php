<?php
/**
 * Event packages carousel section.
 *
 * Args (all optional):
 *  tag    (string)  Section eyebrow tag.   Default: 'Event Types'
 *  title  (string)  Section heading HTML.  Default: 'We Cater for <span class="accent">Every Occasion</span>'
 *  body   (string)  Section body text.     Default: preset copy
 *  limit  (int)     Max packages to show.  Default: 0 (all)
 */
defined( 'ABSPATH' ) || exit;

$packages = ch_get_hire_packages();
$limit    = $args['limit'] ?? 0;
if ( $limit > 0 ) {
	$packages = array_slice( $packages, 0, $limit );
}
if ( empty( $packages ) ) return;

$tag   = $args['tag']   ?? 'Event Types';
$title = $args['title'] ?? 'We Cater for <span class="accent">Every Occasion</span>';
$body  = $args['body']  ?? 'Whether it\'s 50 guests or 500, The Cane House brings the freshest live-press experience to your event.';
$allowed = [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [] ];
?>

<section class="ch-events-packages-section">
	<div class="container">
		<?php get_template_part( 'components/section-header', null, [
			'tag'   => $tag,
			'title' => $title,
			'body'  => $body,
		] ); ?>
		<div class="ch-pkg-carousel fade-up">
			<div class="ch-pkg-track" id="ch-pkg-track">
				<?php
				$color_cycle = [ 'green', 'amber', 'teal', 'purple', 'coral', 'indigo' ];
				foreach ( $packages as $pi => $pkg ) :
					$pkg   = (array) $pkg;
					$color = $pkg['color'] ?? $color_cycle[ $pi % count( $color_cycle ) ];
				?>
					<div class="ch-package-card ch-pkg--<?php echo esc_attr( $color ); ?><?php echo $pi === 0 ? ' active' : ''; ?>">
						<div class="ch-package-card__icon"><?php echo esc_html( $pkg['icon'] ?? '🎉' ); ?></div>
						<h3 class="ch-package-card__title"><?php echo esc_html( $pkg['title'] ?? '' ); ?></h3>
						<p class="ch-package-card__desc"><?php echo esc_html( $pkg['desc'] ?? '' ); ?></p>
						<?php if ( ! empty( $pkg['items'] ) ) : ?>
							<ul class="ch-package-list">
								<?php foreach ( (array) $pkg['items'] as $item ) : ?>
									<li><?php echo esc_html( $item ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="ch-pkg-nav">
				<div class="ch-pkg-dots" id="ch-pkg-dots" role="tablist" aria-label="Event packages navigation">
					<?php foreach ( $packages as $pi => $_ ) : ?>
						<button class="ch-dot<?php echo $pi === 0 ? ' active' : ''; ?>"
							role="tab" aria-selected="<?php echo $pi === 0 ? 'true' : 'false'; ?>"
							aria-label="Package <?php echo $pi + 1; ?>"></button>
					<?php endforeach; ?>
				</div>
				<div class="ch-pkg-arrows">
					<button class="ch-v-btn" id="ch-pkg-prev" aria-label="Previous package">←</button>
					<button class="ch-v-btn" id="ch-pkg-next" aria-label="Next package">→</button>
				</div>
			</div>
		</div>
	</div>
</section>
