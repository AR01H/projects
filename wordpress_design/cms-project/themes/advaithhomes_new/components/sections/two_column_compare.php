<?php
/**
 * components/sections/two_column_compare.php
 *
 * Generic two-column pro/con comparison. Reused on the How It Works page
 * for two different purposes ("doing it alone vs. with us" and the "is
 * this for you?" self-check) - named after its shape, not either specific
 * use.
 *
 * Props: $compare {
 *   eyebrow, heading,
 *   con { label, items[] },
 *   pro { label, items[] }
 * }
 * Usage: adn_component( 'sections/two_column_compare', array( 'compare' => $ctx['comparison'] ) );
 */
defined( 'ABSPATH' ) || exit;

$_c    = isset( $compare ) && is_array( $compare ) ? $compare : array();
$_con  = isset( $_c['con'] ) && is_array( $_c['con'] ) ? $_c['con'] : array();
$_pro  = isset( $_c['pro'] ) && is_array( $_c['pro'] ) ? $_c['pro'] : array();
$_con_items = isset( $_con['items'] ) && is_array( $_con['items'] ) ? $_con['items'] : array();
$_pro_items = isset( $_pro['items'] ) && is_array( $_pro['items'] ) ? $_pro['items'] : array();
if ( empty( $_con_items ) && empty( $_pro_items ) ) return;

$_eyb = isset( $_c['eyebrow'] ) ? (string) $_c['eyebrow'] : '';
$_hdg = isset( $_c['heading'] ) ? (string) $_c['heading'] : '';
?>
<section class="hiw-compare-section">
	<div class="container">
		<?php adn_component( 'parts/section_headers/eyebrow_heading', array(
			'eyebrow'       => $_eyb,
			'heading'       => $_hdg,
			'wrapper_class' => 'icon-grid-header',
		) ); ?>

		<div class="hiw-compare-grid">
			<div class="hiw-compare-col hiw-compare-con">
				<span class="hiw-compare-label"><?php echo esc_html( isset( $_con['label'] ) ? (string) $_con['label'] : '' ); ?></span>
				<ul class="hiw-compare-list hiw-compare-list--cross">
					<?php foreach ( $_con_items as $_line ) : ?>
						<li><?php echo esc_html( (string) $_line ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="hiw-compare-col hiw-compare-pro">
				<span class="hiw-compare-label"><?php echo esc_html( isset( $_pro['label'] ) ? (string) $_pro['label'] : '' ); ?></span>
				<ul class="hiw-compare-list hiw-compare-list--check">
					<?php foreach ( $_pro_items as $_line ) : ?>
						<li><?php echo esc_html( (string) $_line ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</section>
