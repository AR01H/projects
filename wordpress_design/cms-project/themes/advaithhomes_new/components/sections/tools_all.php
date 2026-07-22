<?php
/**
 * components/sections/tools_all.php
 *
 * All Calculators — exactly matches the site's "Latest News" widget layout:
 *   • Header: 🧮 All Calculators [count] | filter pills
 *   • Hero+list: 1st calc = big hero card (left), next 3 = mini_card rows (right)
 *   • Filter pills (visible, full-width pill strip)
 *   • Remaining calculators in 4-col grid (filtered by pills)
 *   • Optional CTA bar at bottom
 *
 * Props:
 *   $filter_tabs[]  { key, label }
 *   $all_tools[]    { icon, categories[], title, desc, url }
 *   $find_cta       { title, description, button_label, button_url }
 */
defined( 'ABSPATH' ) || exit;

$filter_tabs = isset( $filter_tabs ) && is_array( $filter_tabs ) ? $filter_tabs : array();
$all_tools   = isset( $all_tools )   && is_array( $all_tools )   ? $all_tools   : array();
$find_cta    = isset( $find_cta )    && is_array( $find_cta )    ? $find_cta    : array();

if ( empty( $all_tools ) ) { return; }

// Count per category for pill badges
$tab_counts = array();
foreach ( $all_tools as $tool ) {
	foreach ( (array) ( $tool['categories'] ?? array() ) as $c ) {
		$c = sanitize_key( $c );
		$tab_counts[ $c ] = ( $tab_counts[ $c ] ?? 0 ) + 1;
	}
}
$total = count( $all_tools );

// Featured (first 4, same grid design as "Popular Calculators" on the main
// Calculators page - see components/sections/tools_popular.php), rest → grid.
$featured_items = array_slice( $all_tools, 0, 4 );
$grid_items     = array_slice( $all_tools, 4 );
?>
<section class="tc-section tc-all-section" id="allToolsSection">
	<div class="container">
		<?php /* ── Widget 1: Featured Calculators ── */ ?>
		<div class="tc-widget">
			<?php /* ── Widget header: icon + title + count ── */ ?>
			<div class="tc-widget-header">
				<div class="tc-widget-title">
					<span class="tc-widget-icon">🧮</span>
					<h2><?php esc_html_e( 'Featured Calculators', ADN_TEXT_DOMAIN ); ?></h2>
				</div>
			</div>

			<div class="popular-tools-grid">
			<?php foreach ( $featured_items as $tool ) : ?>
				<?php
				$tool_card = array(
					'icon'      => $tool['icon'] ?? '🧮',
					'name'      => $tool['title'] ?? '',
					'url'       => $tool['url'] ?? '',
					'thumbnail' => isset( $tool['thumbnail'] ) && '' !== $tool['thumbnail'] ? (string) $tool['thumbnail'] : '',
					'highlight' => ! empty( $tool['highlight'] ) ? (string) $tool['highlight'] : '',
				);
				adn_component( 'cards/tool_card', array( 'card' => $tool_card ) );
				?>
			<?php endforeach; ?>
			</div>
		</div><!-- /.tc-widget (Hero Widget) -->
	</div>
</section>

<?php if ( ! empty( $filter_tabs ) || ! empty( $grid_items ) || ! empty( $find_cta['title'] ) ) : ?>
<section class="tc-section tc-all-grid-section">
	<div class="container">
		<div class="tc-widget">

			<?php /* ── Filter pills strip ── */ ?>
			<?php if ( ! empty( $filter_tabs ) ) : ?>
				<div class="tc-all-pills-bar">
					<div class="calc-filter-pills" role="tablist" id="calcFilterPills">
						<?php foreach ( $filter_tabs as $tab ) :
							$key   = isset( $tab['key'] )  ? sanitize_key( $tab['key'] ) : '';
							$label = isset( $tab['label'] ) ? (string) $tab['label']      : '';
							$count = 'all' === $key ? $total : ( $tab_counts[ $key ] ?? 0 );
							if ( 'all' !== $key && $count < 1 ) { continue; }
						?>
							<button
								class="calc-pill<?php echo 'all' === $key ? ' active' : ''; ?>"
								data-filter="<?php echo esc_attr( $key ); ?>"
								role="tab"
								aria-selected="<?php echo 'all' === $key ? 'true' : 'false'; ?>"
								type="button"
							><?php echo esc_html( $label ); ?> <span class="calc-pill-count"><?php echo esc_html( $count ); ?></span></button>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php /* ── Remaining calculators grid (items 5+) ── */ ?>
			<?php if ( ! empty( $grid_items ) ) : ?>
				<div class="tc-all-body">
					<div class="calc-grid" id="toolsList">
						<?php
						// Include hero + side in the filterable list too (as hidden items that JS can show/hide)
						foreach ( $all_tools as $idx => $item ) :
							$cats  = implode( ' ', array_map( 'sanitize_key', (array)($item['categories']??[]) ) );
							adn_component( 'cards/tool_list_item', array( 'item' => array(							'index'      => $idx,								'categories' => isset( $item['categories'] ) ? (array) $item['categories'] : array(),
								'icon'       => $item['icon'] ?? '🧮',
								'title'      => $item['title'] ?? '',
								'desc'       => $item['desc'] ?? '',
								'url'        => $item['url'] ?? '',
							) ) );
						endforeach; ?>
					</div>
					<div class="calc-empty-state" id="calcEmptyState" style="display:none;">
						<span><?php esc_html_e( 'No calculators found in this category.', ADN_TEXT_DOMAIN ); ?></span>
					</div>
				</div>
			<?php endif; ?>

			<?php /* ── CTA bar ── */ ?>
			<?php if ( ! empty( $find_cta['title'] ) ) : ?>
				<div class="tc-all-cta">
					<div class="tc-all-cta-text">
						<strong><?php echo esc_html( $find_cta['title'] ); ?></strong>
						<?php if ( ! empty( $find_cta['description'] ) ) : ?>
							<span><?php echo esc_html( $find_cta['description'] ); ?></span>
						<?php endif; ?>
					</div>
					<?php if ( ! empty( $find_cta['button_label'] ) ) : ?>
						<a href="<?php echo esc_url( adn_link( $find_cta['button_url'] ?? '' ) ); ?>" class="tc-all-cta-btn">
							<?php echo esc_html( $find_cta['button_label'] ); ?> &rarr;
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		</div><!-- /.tc-widget (Grid Widget) -->
	</div>
</section>
<?php endif; ?>