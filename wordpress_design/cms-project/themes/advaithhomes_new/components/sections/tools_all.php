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

// Split: hero (1st), side list (next 3), grid (rest)
$hero_item   = $all_tools[0];
$side_items  = array_slice( $all_tools, 1, 3 );
$grid_items  = array_slice( $all_tools, 4 );
?>
<section class="tc-section tc-all-section" id="allToolsSection">
	<div class="container">
		<?php /* ── Widget 1: Hero Featured Row ── */ ?>
		<div class="tc-widget">
			<?php /* ── Widget header: icon + title + count ── */ ?>
			<div class="tc-widget-header">
				<div class="tc-widget-title">
					<span class="tc-widget-icon">🧮</span>
					<h2><?php esc_html_e( 'Featured Calculators', ADN_TEXT_DOMAIN ); ?></h2>
				</div>
			</div>

			<?php /* ── Hero + Side list row (matches news_list_widget layout) ── */ ?>
			<div class="tc-all-hero-row">

				<?php /* BIG hero card — 1st calculator */ ?>
				<a
					href="<?php echo esc_url( adn_link( $hero_item['url'] ?? '' ) ); ?>"
					class="tc-hero-calc-card"
					data-category="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_key', (array)($hero_item['categories']??[]) ) ) ); ?>"
				>
					<?php
					$hero_thumb = isset( $hero_item['thumbnail'] ) && '' !== $hero_item['thumbnail'] ? (string) $hero_item['thumbnail'] : '';
					if ( empty( $hero_thumb ) ) {
						$hero_thumb = get_template_directory_uri() . THEME_DEFAULT_CALC_IMG . '?v=' . LOCAL_CACHE_VERSION;
					}
					?>
					<div class="tc-hero-calc-icon" style="padding:0; background:transparent;">
						<img src="<?php echo esc_url( $hero_thumb ); ?>" alt="" loading="lazy" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">
					</div>
					<div class="tc-hero-calc-body">
						<h3><?php echo esc_html( $hero_item['title'] ?? '' ); ?></h3>
						<?php if ( ! empty( $hero_item['desc'] ) ) : ?>
							<p><?php echo esc_html( wp_trim_words( $hero_item['desc'], 18, '…' ) ); ?></p>
						<?php endif; ?>
						<span class="tc-hero-calc-cta"><?php esc_html_e( 'Calculate Now', ADN_TEXT_DOMAIN ); ?> &rarr;</span>
					</div>
				</a>

				<?php /* Side list — next 3 calculators as mini_card rows */ ?>
				<div class="tc-all-side-list">
					<?php foreach ( $side_items as $side ) :
						$side_thumb = isset( $side['thumbnail'] ) && '' !== $side['thumbnail'] ? (string) $side['thumbnail'] : '';
						if ( empty( $side_thumb ) ) {
							$side_thumb = get_template_directory_uri() . THEME_DEFAULT_CALC_IMG . '?v=' . LOCAL_CACHE_VERSION;
						}
						adn_component( 'cards/mini_card', array( 'card' => array(
							'img_url' => $side_thumb,
							'title'   => $side['title'] ?? '',
							'tag'     => ! empty( $side['highlight'] ) ? $side['highlight'] : '',
							'url'     => $side['url']   ?? '',
						) ) );
					endforeach; ?>
				</div>

			</div>
		</div><!-- /.tc-widget (Hero Widget) -->

		<?php /* ── Widget 2: Browse Grid section ── */ ?>
		<div class="tc-widget tc-grid-widget" style="margin-top: 24px;">

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
							$url   = esc_url( adn_link( $item['url'] ?? '' ) );
							$icon  = $item['icon']  ?? '🧮';
							$title = $item['title'] ?? '';
							$desc  = $item['desc']  ?? '';
							$cats  = implode( ' ', array_map( 'sanitize_key', (array)($item['categories']??[]) ) );
						?>
							<a href="<?php echo $url; ?>" class="calc-list-item" data-category="<?php echo esc_attr( $cats ); ?>" data-index="<?php echo intval( $idx ); ?>">
								<div class="calc-list-item-left">
									<div class="calc-list-icon"><?php echo adn_icon( $icon ); ?></div>
									<div class="calc-list-text">
										<?php if ( $title ) : ?><h4><?php echo esc_html( $title ); ?></h4><?php endif; ?>
									</div>
								</div>
								<?php if ( $desc ) : ?>
									<div class="calc-list-desc"><p><?php echo esc_html( $desc ); ?></p></div>
								<?php endif; ?>
								<div class="calc-list-item-bottom">
									<span class="calc-list-arrow">&rarr;</span>
								</div>
							</a>
						<?php endforeach; ?>
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

<script>
(function () {
	document.addEventListener('DOMContentLoaded', function () {
		var pills     = document.querySelectorAll('.calc-pill');
		var items     = document.querySelectorAll('.calc-grid .calc-list-item');
		var heroRow   = document.querySelector('.tc-all-hero-row');
		var emptyMsg  = document.getElementById('calcEmptyState');

		function filterGrid(cat) {
			var visible = 0;

			// Show/hide hero row based on filter
			if (heroRow) {
				if (cat === 'all') {
					heroRow.style.display = '';
				} else {
					heroRow.style.display = 'none';
				}
			}

			items.forEach(function (item) {
				var cats = (item.getAttribute('data-category') || '').split(' ').map(function(s){ return s.trim(); });
				var idx = parseInt(item.getAttribute('data-index') || '0', 10);
				
				var matchesCat = (cat === 'all' || cats.indexOf(cat) !== -1);
				var isDuplicateInHero = (cat === 'all' && idx < 4); // First 4 are in hero block
				
				if (matchesCat && !isDuplicateInHero) {
					item.classList.remove('calc-filtered-out');
					visible++;
				} else {
					item.classList.add('calc-filtered-out');
				}
			});
			if (emptyMsg) { 
				// We consider the category 'empty' only if there's nothing in the grid AND no hero items are showing
				var heroVisible = (cat === 'all' && heroRow && heroRow.style.display !== 'none');
				emptyMsg.style.display = (visible === 0 && !heroVisible) ? 'flex' : 'none'; 
			}
		}

		pills.forEach(function (pill) {
			pill.addEventListener('click', function () {
				pills.forEach(function (p) { p.classList.remove('active'); p.setAttribute('aria-selected','false'); });
				pill.classList.add('active');
				pill.setAttribute('aria-selected', 'true');
				filterGrid(pill.getAttribute('data-filter'));
			});
		});
	});
})();
</script>
