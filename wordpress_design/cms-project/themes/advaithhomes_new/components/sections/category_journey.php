<?php
/**
 * components/sections/category_journey.php
 * Vertical timeline carousel: icon rings on left, content cards on right.
 * Prev/next arrows scroll through steps. Description clamped to 2 lines;
 * a "More" button triggers a JS popup with the full content.
 *
 * Props: $journey { heading, steps[], tip { icon, text, link_label, link_url } }
 */

defined( 'ABSPATH' ) || exit;

$journey = isset( $journey ) && is_array( $journey ) ? $journey : array();
$steps   = isset( $journey['steps'] ) ? (array) $journey['steps'] : array();
$tip     = isset( $journey['tip'] )   ? (array) $journey['tip']   : array();

if ( empty( $steps ) ) { return; }

$_jny_total = count( $steps );
?>

<?php if ( ! empty( $journey['heading'] ) ) : ?>
<h2 class="jny-vtl-heading"><?php echo esc_html( $journey['heading'] ); ?></h2>
<?php endif; ?>

<div class="jny-vtl" data-jny-vtl>

	<!-- Up arrow -->
	<button type="button" class="jny-vtl-nav jny-vtl-nav--up" data-jny-vtl-prev aria-label="Previous step" disabled>
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="18 15 12 9 6 15"/></svg>
	</button>

	<!-- Scrollable track -->
	<div class="jny-vtl-viewport" data-jny-vtl-vp>
		<div class="jny-vtl-track" data-jny-vtl-track>

			<?php foreach ( $steps as $i => $step ) :
				$step  = (array) $step;
				$num   = isset( $step['num'] )   ? (string) $step['num']   : (string) ( $i + 1 );
				$label = isset( $step['label'] ) ? (string) $step['label'] : '';
				$desc  = isset( $step['desc'] )  ? (string) $step['desc']  : '';
				$icon  = isset( $step['icon'] )  ? trim( (string) $step['icon'] ) : '';
				$is_last = ( $i === $_jny_total - 1 );

				// Resolve icon HTML - if adn_icon returns the generic fallback dot, use step number instead.
				$_icon_html = ( '' !== $icon ) ? adn_icon( $icon ) : '';
				if ( $_icon_html && false !== strpos( $_icon_html, 'fa-circle-dot' ) ) {
					$_icon_html = '';
				}
			?>
			<div class="jny-vtl-row" data-jny-vtl-row
				data-label="<?php echo esc_attr( $label ); ?>"
				data-desc="<?php echo esc_attr( $desc ); ?>"
				data-num="<?php echo esc_attr( str_pad( $num, 2, '0', STR_PAD_LEFT ) ); ?>">

				<!-- Left: icon ring + vertical connector to next -->
				<div class="jny-vtl-icon-col">
					<div class="jny-vtl-ring">
						<div class="jny-vtl-circle">
							<?php if ( $_icon_html ) : ?>
								<?php echo $_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped - already escaped by adn_icon() ?>
							<?php else : ?>
								<span class="jny-vtl-num"><?php echo esc_html( str_pad( $num, 2, '0', STR_PAD_LEFT ) ); ?></span>
							<?php endif; ?>
						</div>
					</div>
					<?php if ( ! $is_last ) : ?>
					<div class="jny-vtl-vline" aria-hidden="true"></div>
					<?php endif; ?>
				</div>

				<!-- Horizontal connector dot -->
				<div class="jny-vtl-connector" aria-hidden="true">
					<div class="jny-vtl-hline"></div>
					<div class="jny-vtl-dot"></div>
				</div>

				<!-- Content card -->
				<div class="jny-vtl-card">
					<?php if ( $label ) : ?>
					<strong class="jny-vtl-label"><?php echo esc_html( $label ); ?></strong>
					<?php endif; ?>
					<?php if ( $desc ) : ?>
					<div class="jny-vtl-desc-wrap">
						<p class="jny-vtl-desc"><?php echo esc_html( $desc ); ?></p>
						<button type="button" class="jny-vtl-more" aria-label="<?php echo esc_attr( sprintf( 'Read more about %s', $label ) ); ?>">
							More <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" width="12" height="12" aria-hidden="true"><path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/></svg>
						</button>
					</div>
					<?php endif; ?>
				</div>

			</div>
			<?php endforeach; ?>

		</div>
	</div>

	<!-- Down arrow -->
	<button type="button" class="jny-vtl-nav jny-vtl-nav--down" data-jny-vtl-next aria-label="Next step">
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
	</button>

	<?php if ( $tip ) : ?>
		<div class="journey-tip" style="margin-top:28px; width:100%;">
			<div class="journey-tip-left">
				<span class="journey-tip-icon"><?php echo adn_icon( isset( $tip['icon'] ) ? $tip['icon'] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="journey-tip-text"><?php
					echo wp_kses(
						isset( $tip['text'] ) ? $tip['text'] : '',
						array( 'strong' => array() )
					);
				?></span>
			</div>
			<?php if ( ! empty( $tip['link_label'] ) && ! empty( $tip['link_url'] ) ) : ?>
				<a href="<?php echo esc_url( adn_link( $tip['link_url'] ) ); ?>" class="journey-tip-cta">
					<?php echo esc_html( $tip['link_label'] ); ?>
				</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

</div>
