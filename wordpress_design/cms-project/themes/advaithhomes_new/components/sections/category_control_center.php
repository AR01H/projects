<?php
/**
 * components/sections/category_control_center.php
 * Parent-term control centre: admin spotlights + journey stepper.
 *
 * Props:
 *   journey    array { heading, steps[], tip{} }
 *   spotlights array { terms[] }
 */

defined( 'ABSPATH' ) || exit;

$journey    = isset( $journey ) && is_array( $journey ) ? $journey : array();
$spotlights = isset( $spotlights ) && is_array( $spotlights ) ? $spotlights : array();
$terms      = isset( $spotlights['terms'] ) && is_array( $spotlights['terms'] )
	? array_values( array_filter( array_map( 'sanitize_key', $spotlights['terms'] ) ) )
	: array();

if ( empty( $journey ) && empty( $terms ) ) { return; }
?>
<?php if ( ! empty( $terms ) ) : ?>
<div class="category-control-centre__spotlights ">
	<?php foreach ( $terms as $_term_slug ) :
		$_sp_props = \Adn\Theme\Service\SpotlightService::buildProps( $_term_slug );
		if ( $_sp_props ) :
	?>
		<?php adn_component( 'parts/spotlights_widget', array_merge( $_sp_props, array( 'mode' => 'compact' ) ) ); ?>
	<?php endif; endforeach; ?>
</div>
<?php endif; ?>

<section class="category-control-centre">
	<div class="">
		<div class="category-control-centre__panel">

			<?php if ( ! empty( $journey ) ) : ?>
			<div class="category-control-centre__journey">
				<?php adn_component( 'sections/category_journey', array( 'journey' => $journey ) ); ?>
			</div>
			<?php endif; ?>
		</div>
	</div>
</section>
