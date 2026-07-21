<?php
defined( 'ABSPATH' ) || exit;

$packages = nt_data( 'hire_packages' ) ?: [];
$limit    = $args['limit'] ?? 3;
if ( $limit > 0 ) {
	$packages = array_slice( $packages, 0, $limit );
}
if ( empty( $packages ) ) return;

$content = nt_data( 'content' )['events_preview'] ?? [];
$section_tag   = $args['tag']        ?? $content['tag']     ?? '';
$section_title = $args['heading']    ?? $content['heading'] ?? '';
$section_body  = $args['body']       ?? $content['body']    ?? '';
$more_url      = $args['more_url']   ?? nt_page_url( 'events' );
$more_label    = $args['more_label'] ?? $content['more_label'] ?? 'View All Event Packages →';
?>

<section class="nt-events-preview-section">
	<div class="container">

		<?php get_template_part( 'components/section-header', null, [
			'tag'   => $section_tag,
			'title' => $section_title,
			'body'  => $section_body,
		] ); ?>

		<div class="nt-epc-carousel fade-up">
			<div class="nt-epc-track" id="nt-epc-track">
				<?php
				$color_cycle = [ 'green', 'amber', 'teal', 'purple', 'coral', 'indigo' ];
				foreach ( $packages as $pi => $pkg ) :
					$pkg   = (array) $pkg;
					$icon  = esc_html( $pkg['icon']  ?? '🎉' );
					$name  = esc_html( $pkg['title'] ?? '' );
					$desc  = esc_html( $pkg['desc']  ?? '' );
					$color = $pkg['color'] ?? $color_cycle[ $pi % count( $color_cycle ) ];
				?>
					<div class="nt-epc nt-epc--<?php echo esc_attr( $color ); ?><?php echo $pi === 0 ? ' active' : ''; ?>">
						<div class="nt-epc__icon"><?php echo $icon; ?></div>
						<h3 class="nt-epc__title"><?php echo $name; ?></h3>
						<p class="nt-epc__desc"><?php echo $desc; ?></p>
						<!-- <a href="<?php echo esc_url( home_url( '/events/' ) ); ?>" class="nt-epc__link">
							Book This →
						</a> -->
					</div>
				<?php endforeach; ?>
			</div>

			<!-- Dots + arrows (mobile only) -->
			<div class="nt-epc-nav">
				<div class="nt-epc-dots" id="nt-epc-dots" role="tablist" aria-label="Events navigation">
					<?php foreach ( $packages as $pi => $_ ) : ?>
						<button class="nt-dot<?php echo $pi === 0 ? ' active' : ''; ?>"
							role="tab" aria-selected="<?php echo $pi === 0 ? 'true' : 'false'; ?>"
							aria-label="Event <?php echo $pi + 1; ?>"></button>
					<?php endforeach; ?>
				</div>
				<div class="nt-epc-arrows">
					<button class="nt-v-btn" id="nt-epc-prev" aria-label="Previous event">←</button>
					<button class="nt-v-btn" id="nt-epc-next" aria-label="Next event">→</button>
				</div>
			</div>
		</div>

		<div class="nt-more-wrap" style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
			<button type="button" class="btn" data-nt-open="nt-events-modal">📅 Book an Event</button>
			<a href="<?php echo esc_url( $more_url ); ?>" class="btn-outline"><?php echo esc_html( $more_label ); ?></a>
		</div>

	</div>
</section>

<?php
get_template_part( 'components/parts/form-modal', null, array(
	'id'     => 'nt-events-modal',
	'title'  => __( 'Book Your Event 🎉', NT_TEXT_DOMAIN ),
	'sub'    => __( 'Live juice counter for weddings, parties & corporate events.', NT_TEXT_DOMAIN ),
	'config' => nt_data( 'form_events' ),
) );
?>
