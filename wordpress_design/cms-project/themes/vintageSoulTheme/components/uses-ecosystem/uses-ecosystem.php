<?php
/**
 * VintageSoulTheme - Reusable Filterable Uses Ecosystem Grid Component
 *
 * Renders a category-pill filter bar and a grid of use-case ecosystem cards
 * with icons, tags, impact badges, and fun facts.
 *
 * Props:
 *   categories (array)  - Ordered list of category names (first is "All")
 *   items      (array)  - Array of { title, desc, category, tag, impact, fact, icon, image } objects
 *   grid_id    (string) - Optional unique ID for the grid container (defaults to 'uses-ecosystem-grid')
 */

use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

$categories = isset( $categories ) ? (array) $categories : array();
$items      = isset( $items )      ? (array) $items      : array();
$grid_id    = isset( $grid_id )    ? trim( (string) $grid_id ) : 'uses-ecosystem-grid';

if ( empty( $items ) ) {
	return;
}
?>

<?php if ( ! empty( $categories ) ) : ?>
	<div class="uses-category-filter-bar" role="tablist" aria-label="Category Filter">
		<?php foreach ( $categories as $c_idx => $cat_name ) :
			$cat_slug  = 0 === $c_idx ? 'all' : strtolower( trim( preg_replace( '/[^a-z0-9]+/i', '-', $cat_name ), '-' ) );
			$is_active = 0 === $c_idx;
		?>
			<button class="uses-filter-pill<?php echo $is_active ? ' is-active' : ''; ?>"
					type="button"
					role="tab"
					aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
					data-filter-category="<?php echo esc_attr( $cat_slug ); ?>">
				<?php echo esc_html( $cat_name ); ?>
			</button>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<div class="uses-ecosystem-grid" id="<?php echo esc_attr( $grid_id ); ?>">
	<?php foreach ( $items as $u_item ) :
		$u_ttl     = (string) ( $u_item['title'] ?? '' );
		$u_dsc     = (string) ( $u_item['desc'] ?? '' );
		$u_cat     = (string) ( $u_item['category'] ?? '' );
		$u_catslug = strtolower( trim( preg_replace( '/[^a-z0-9]+/i', '-', $u_cat ), '-' ) );
		$u_tag     = (string) ( $u_item['tag'] ?? '' );
		$u_impact  = (string) ( $u_item['impact'] ?? '' );
		$u_fact    = (string) ( $u_item['fact'] ?? '' );
		$u_icon    = (string) ( $u_item['icon'] ?? 'leaf' );
		$u_img     = UrlHelper::resolve( (string) ( $u_item['image'] ?? '' ) );
	?>
		<article class="use-ecosystem-card frame--rough-cut" data-use-category="<?php echo esc_attr( $u_catslug ); ?>">
			<div class="use-ecosystem-card__top">
				<div class="use-ecosystem-card__icon-box">
					<?php echo IconHelper::render( $u_icon, '#f6d599', 24 ); // phpcs:ignore ?>
				</div>
				<div class="use-ecosystem-card__tags">
					<?php if ( '' !== $u_tag ) : ?>
						<span class="use-ecosystem-card__tag"><?php echo esc_html( $u_tag ); ?></span>
					<?php endif; ?>
					<?php if ( '' !== $u_impact ) : ?>
						<span class="use-ecosystem-card__impact-badge">🌱 <?php echo esc_html( $u_impact ); ?></span>
					<?php endif; ?>
				</div>
			</div>

			<h3 class="use-ecosystem-card__title"><?php echo esc_html( $u_ttl ); ?></h3>
			<p class="use-ecosystem-card__desc"><?php echo esc_html( $u_dsc ); ?></p>

			<?php if ( '' !== $u_fact ) : ?>
				<div class="use-ecosystem-card__fact">
					<span class="fact-leaf">✦</span>
					<span class="fact-text"><?php echo esc_html( $u_fact ); ?></span>
				</div>
			<?php endif; ?>
		</article>
	<?php endforeach; ?>
</div>

<!-- Interactive Category Filter Script -->
<script>
(function() {
	function initUsesFilter() {
		var filterButtons = document.querySelectorAll('[data-filter-category]');
		var cards = document.querySelectorAll('[data-use-category]');
		if (!filterButtons.length || !cards.length) return;

		filterButtons.forEach(function(btn) {
			btn.addEventListener('click', function() {
				var cat = this.getAttribute('data-filter-category');
				
				filterButtons.forEach(function(b) {
					b.classList.remove('is-active');
					b.setAttribute('aria-selected', 'false');
				});
				this.classList.add('is-active');
				this.setAttribute('aria-selected', 'true');

				cards.forEach(function(card) {
					var cardCat = card.getAttribute('data-use-category');
					if (cat === 'all' || cardCat === cat) {
						card.classList.remove('is-hidden');
						card.style.opacity = '0';
						card.style.transform = 'translateY(8px)';
						setTimeout(function() {
							card.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
							card.style.opacity = '1';
							card.style.transform = 'translateY(0)';
						}, 20);
					} else {
						card.classList.add('is-hidden');
					}
				});
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initUsesFilter);
	} else {
		initUsesFilter();
	}
})();
</script>
