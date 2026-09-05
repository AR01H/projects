<?php
/**
 * components/sections/icon_feature_grid.php
 *
 * Generic icon + title + desc feature grid. Reused across the site for
 * several different concepts (Why Choose Us on the Guidance page, plus Why
 * Choose Us / What You Get / Before You Start on the How It Works page) -
 * deliberately named after its shape, not any one of those uses, since it
 * isn't tied to any single page or purpose.
 *
 * Props: $grid {
 *   eyebrow, heading,
 *   items[] { icon, title, desc },
 *   variant  'light' swaps the section to the page's light background -
 *            the default is white (matching the Guidance page's original
 *            look); use 'light' only where this component repeats more
 *            than once on one page and needs to alternate against its
 *            neighbours instead of visually merging together.
 * }
 * Usage: adn_component( 'sections/icon_feature_grid', array( 'grid' => $ctx['why_choose'] ) );
 */
defined( 'ABSPATH' ) || exit;

$_g     = isset( $grid ) && is_array( $grid ) ? $grid : array();
$_items = isset( $_g['items'] ) && is_array( $_g['items'] ) ? $_g['items'] : array();
if ( empty( $_items ) ) return;

$_eyb     = isset( $_g['eyebrow'] ) ? (string) $_g['eyebrow'] : '';
$_hdg     = isset( $_g['heading'] ) ? (string) $_g['heading'] : '';
$_variant = ( isset( $_g['variant'] ) && 'light' === $_g['variant'] ) ? ' icon-grid-section--light' : '';
?>
<section class="icon-grid-section<?php echo esc_attr( $_variant ); ?>">
	<div class="container">
		<?php adn_component( 'parts/section_headers/eyebrow_heading', array(
			'eyebrow'       => $_eyb,
			'heading'       => $_hdg,
			'wrapper_class' => 'icon-grid-header',
		) ); ?>
		<div class="icon-grid">
			<?php foreach ( $_items as $_it ) :
				$_ico = adn_icon( isset( $_it['icon'] )  ? (string) $_it['icon']  : '' );
				$_ttl = esc_html( isset( $_it['title'] ) ? (string) $_it['title'] : '' );
				$_dsc = esc_html( isset( $_it['desc'] )  ? (string) $_it['desc']  : '' );
			?>
				<div class="icon-grid-item">
					<span class="icon-grid-icon" aria-hidden="true"><?php echo $_ico; ?></span>
					<h3><?php echo $_ttl; ?></h3>
					<p><?php echo $_dsc; ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
