<?php
defined( 'ABSPATH' ) || exit;

$packages = ch_get_hire_packages();
$limit    = $args['limit'] ?? ch_home_limit( 'events_preview', 3 );
if ( $limit > 0 ) {
	$packages = array_slice( $packages, 0, $limit );
}
if ( empty( $packages ) ) return;

$section_tag   = $args['tag']        ?? 'Events & Hire';
$section_title = $args['heading']    ?? 'Bring Us to <span class="accent">Your Event</span>';
$section_body  = $args['body']       ?? 'Available for weddings, Mehndi nights, Eid parties, corporate events, and more across the UK.';
$more_url      = $args['more_url']   ?? home_url( '/events/' );
$more_label    = $args['more_label'] ?? 'View All Event Packages →';
?>

<section class="ch-events-preview-section">
	<div class="container">

		<?php get_template_part( 'components/section-header', null, [
			'tag'   => $section_tag,
			'title' => $section_title,
			'body'  => $section_body,
		] ); ?>

		<div class="ch-epc-carousel fade-up">
			<div class="ch-epc-track" id="ch-epc-track">
				<?php
				$color_cycle = [ 'green', 'amber', 'teal', 'purple', 'coral', 'indigo' ];
				foreach ( $packages as $pi => $pkg ) :
					$pkg   = (array) $pkg;
					$icon  = esc_html( $pkg['icon']  ?? '🎉' );
					$name  = esc_html( $pkg['title'] ?? '' );
					$desc  = esc_html( $pkg['desc']  ?? '' );
					$color = $pkg['color'] ?? $color_cycle[ $pi % count( $color_cycle ) ];
				?>
					<div class="ch-epc ch-epc--<?php echo esc_attr( $color ); ?><?php echo $pi === 0 ? ' active' : ''; ?>">
						<div class="ch-epc__icon"><?php echo $icon; ?></div>
						<h3 class="ch-epc__title"><?php echo $name; ?></h3>
						<p class="ch-epc__desc"><?php echo $desc; ?></p>
						<a href="<?php echo esc_url( home_url( '/events/' ) ); ?>" class="ch-epc__link">
							Book This →
						</a>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- Dots + arrows (mobile only) -->
			<div class="ch-epc-nav">
				<div class="ch-epc-dots" id="ch-epc-dots" role="tablist" aria-label="Events navigation">
					<?php foreach ( $packages as $pi => $_ ) : ?>
						<button class="ch-dot<?php echo $pi === 0 ? ' active' : ''; ?>"
							role="tab" aria-selected="<?php echo $pi === 0 ? 'true' : 'false'; ?>"
							aria-label="Event <?php echo $pi + 1; ?>"></button>
					<?php endforeach; ?>
				</div>
				<div class="ch-epc-arrows">
					<button class="ch-v-btn" id="ch-epc-prev" aria-label="Previous event">←</button>
					<button class="ch-v-btn" id="ch-epc-next" aria-label="Next event">→</button>
				</div>
			</div>
		</div>

		<?php ch_more_button( $more_url, $more_label ); ?>

	</div>
</section>
