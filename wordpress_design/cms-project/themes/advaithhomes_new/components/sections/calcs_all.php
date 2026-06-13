<?php
/**
 * components/sections/calcs_all.php - All calculators: filter tabs + 2-col list + find-my-calc CTA.
 *
 * Props:
 *   $filter_tabs[]  { key, label }
 *   $all_calcs[]    { icon, categories[], title, desc, url }
 *   $find_cta       { title, description, button_label, button_url }
 *
 * Tab keys must match the category keys used in each item's categories[] array.
 * JS (calculators.js) handles filtering; data-category is space-separated keys.
 *
 * Usage: adn_component( 'sections/calcs_all', array( 'filter_tabs' => ..., 'all_calcs' => ..., 'find_cta' => ... ) );
 */

defined( 'ABSPATH' ) || exit;

$filter_tabs = isset( $filter_tabs ) && is_array( $filter_tabs ) ? $filter_tabs : array();
$all_calcs   = isset( $all_calcs )   && is_array( $all_calcs )   ? $all_calcs   : array();
$find_cta    = isset( $find_cta )    && is_array( $find_cta )    ? $find_cta    : array();
?>
<section class="all-calcs-section">
	<h2><?php echo esc_html__( 'All Calculators', ADN_TEXT_DOMAIN ); ?></h2>

	<?php /* ── Filter tabs ── */ ?>
	<?php if ( ! empty( $filter_tabs ) ) : ?>
		<div class="calcs-filter-tabs" role="tablist">
			<?php foreach ( $filter_tabs as $tab ) :
				$key   = isset( $tab['key'] )   ? sanitize_key( $tab['key'] )  : '';
				$label = isset( $tab['label'] )  ? (string) $tab['label']       : '';
			?>
				<button
					class="calc-tab<?php echo 'all' === $key ? ' active' : ''; ?>"
					data-tab="<?php echo esc_attr( $key ); ?>"
					role="tab"
					aria-selected="<?php echo 'all' === $key ? 'true' : 'false'; ?>"
					type="button"
				><?php echo esc_html( $label ); ?></button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php /* ── Calculator list ── */ ?>
	<?php if ( ! empty( $all_calcs ) ) : ?>
		<div class="calcs-list" id="calcsList">
			<?php foreach ( $all_calcs as $item ) :
				adn_component( 'cards/calc_list_item', array( 'item' => $item ) );
			endforeach; ?>
		</div>
	<?php endif; ?>

	<?php /* ── Find my calculator CTA ── */ ?>
	<?php if ( ! empty( $find_cta['title'] ) ) : ?>
		<div class="find-calc-cta">
			<div class="find-calc-cta-text">
				<h4><?php echo esc_html( $find_cta['title'] ); ?></h4>
				<?php if ( ! empty( $find_cta['description'] ) ) : ?>
					<p><?php echo esc_html( $find_cta['description'] ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $find_cta['button_label'] ) ) : ?>
				<a
					href="<?php echo esc_url( adn_link( isset( $find_cta['button_url'] ) ? $find_cta['button_url'] : '' ) ); ?>"
					class="btn btn-primary find-calc-btn"
				>
					<?php echo esc_html( $find_cta['button_label'] ); ?>
				</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

</section>
