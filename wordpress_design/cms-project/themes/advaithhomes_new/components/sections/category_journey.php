<?php
/**
 * components/sections/category_journey.php
 * Card-grid layout: each step is a premium card with step number badge, icon, title and description.
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
<div class="jny-cards-header">
	<h2 class="jny-cards-heading"><?php echo esc_html( $journey['heading'] ); ?></h2>
	<p class="jny-cards-sub"><?php esc_html_e( 'Follow these steps to navigate your journey with confidence.', 'adn' ); ?></p>
</div>
<?php endif; ?>

<div class="jny-carousel-wrapper" data-jny-carousel>
	<div class="jny-carousel-viewport" data-jny-carousel-viewport>
		<div class="jny-carousel-track jny-cards-grid" data-jny-carousel-track>

			<?php foreach ( $steps as $i => $step ) :
				$step  = (array) $step;
				$num   = isset( $step['num'] )   ? (string) $step['num']   : (string) ( $i + 1 );
				$label = isset( $step['label'] ) ? (string) $step['label'] : '';
				$desc  = isset( $step['desc'] )  ? (string) $step['desc']  : '';
				$icon  = isset( $step['icon'] )  ? trim( (string) $step['icon'] ) : '';

				$_icon_html = ( '' !== $icon ) ? adn_icon( $icon ) : '';
				if ( $_icon_html && false !== strpos( $_icon_html, 'fa-circle-dot' ) ) {
					$_icon_html = '';
				}

				// Cycle accent colours through 4 shades of primary/accent
				$_accent_idx = $i % 4;
			?>
			<div class="jny-card" data-label="<?php echo esc_attr( $label ); ?>"
				data-desc="<?php echo esc_attr( $desc ); ?>"
				data-num="<?php echo esc_attr( str_pad( $num, 2, '0', STR_PAD_LEFT ) ); ?>">
				<!-- Icon -->
				<div class="jny-card__icon-wrap">
					<?php if ( $_icon_html ) : ?>
						<?php echo $_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php else : ?>
						<span class="jny-card__icon-num"><?php echo esc_html( str_pad( $num, 2, '0', STR_PAD_LEFT ) ); ?></span>
					<?php endif; ?>
				</div>

				<!-- Content -->
				<div class="jny-card__body">
					<?php if ( $label ) : ?>
					<strong class="jny-card__label"><?php echo esc_html( $label ); ?></strong>
					<?php endif; ?>
					<?php if ( $desc ) : ?>
					<p class="jny-card__desc"><?php echo esc_html( $desc ); ?></p>
					<?php endif; ?>
				</div>

				<!-- Connector line (visual only, not last) -->
				<?php if ( $i < $_jny_total - 1 ) : ?>
				<div class="jny-card__arrow" aria-hidden="true">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
						stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
						width="16" height="16" aria-hidden="true">
						<polyline points="9 18 15 12 9 6"/>
					</svg>
				</div>
				<?php endif; ?>

			</div>
			<?php endforeach; ?>

		</div>
	</div>

	<!-- Carousel Navigation Controls -->
	<button type="button" class="jny-carousel-control jny-carousel-control--prev" data-jny-carousel-prev aria-label="<?php esc_attr_e( 'Previous step', 'adn' ); ?>" disabled>
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
			<polyline points="15 18 9 12 15 6"/>
		</svg>
	</button>
	<button type="button" class="jny-carousel-control jny-carousel-control--next" data-jny-carousel-next aria-label="<?php esc_attr_e( 'Next step', 'adn' ); ?>">
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
			<polyline points="9 18 15 12 9 6"/>
		</svg>
	</button>

	<!-- Indicators -->
</div>

<?php if ( $tip ) : ?>
<div class="jny-cards-tip">
	<span class="jny-cards-tip__icon"><?php echo adn_icon( isset( $tip['icon'] ) ? $tip['icon'] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	<span class="jny-cards-tip__text"><?php
		echo wp_kses(
			isset( $tip['text'] ) ? $tip['text'] : '',
			array( 'strong' => array() )
		);
	?></span>
	<?php if ( ! empty( $tip['link_label'] ) && ! empty( $tip['link_url'] ) ) : ?>
		<a href="<?php echo esc_url( adn_link( $tip['link_url'] ) ); ?>" class="jny-cards-tip__cta">
			<?php echo esc_html( $tip['link_label'] ); ?> →
		</a>
	<?php endif; ?>
</div>
<?php endif; ?>
