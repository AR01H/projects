<?php
/**
 * components/sections/expert_cats_strip.php
 * Props: $categories[] { key, label, icon?, active? }
 */
defined( 'ABSPATH' ) || exit;

$_cats = ( isset( $categories ) && is_array( $categories ) ) ? $categories : array();
if ( empty( $_cats ) ) return;
?>
<div class="expert-cats-strip">
	<div class="container">
		<div class="expert-cats-inner" id="expertCatsContainer" role="tablist" aria-label="<?php esc_attr_e( 'Filter experts by category', ADN_TEXT_DOMAIN ); ?>">
			<?php 
			$_index = 0;
			$_limit = 17; // Show 7 categories + "All Experts" by default on desktop
			foreach ( $_cats as $_c ) :
				$_ck     = esc_attr( ( isset( $_c['key'] )   ? (string) $_c['key']   : 'all' ) );
				$_cl     = esc_html( isset( $_c['label'] ) ? (string) $_c['label'] : '' );
				$_active = ! empty( $_c['active'] );
				$_hide   = ($_index > $_limit) ? ' expert-cat-tab--collapsed' : '';
			?>
				<button
					type="button"
					class="expert-cat-tab<?php echo $_active ? ' active' : ''; ?><?php echo $_hide; ?>"
					data-cat="<?php echo $_ck; ?>"
					role="tab"
					aria-selected="<?php echo $_active ? 'true' : 'false'; ?>"
				>
					<span class="ect-label"><?php echo $_cl; ?></span>
				</button>
			<?php 
				$_index++;
			endforeach; 
			?>

			<?php if ( count( $_cats ) > ($_limit + 1) ) : ?>
				<button type="button" class="expert-cat-tab-toggle" id="expertCatsToggle" aria-expanded="false">
					<span>+ More</span>
				</button>
			<?php endif; ?>
		</div>
		<div class="expert-cats-fade" id="expertCatsFade" aria-hidden="true"></div>
	</div>
</div>

<script>
(function(){
	document.addEventListener('DOMContentLoaded', function() {
		var toggle = document.getElementById('expertCatsToggle');
		var container = document.getElementById('expertCatsContainer');
		var fade = document.getElementById('expertCatsFade');

		if (toggle && container) {
			toggle.addEventListener('click', function() {
				var isExpanded = container.classList.toggle('is-expanded');
				toggle.setAttribute('aria-expanded', isExpanded);
				toggle.querySelector('span').textContent = isExpanded ? '- Show Less' : '+ More';
			});
		}

		if (container && fade) {
			var updateFade = function() {
				var hasOverflow = container.scrollWidth > container.clientWidth + 2;
				var atEnd = container.scrollLeft + container.clientWidth >= container.scrollWidth - 2;
				fade.classList.toggle('is-visible', hasOverflow && !atEnd);
			};
			container.addEventListener('scroll', updateFade, { passive: true });
			window.addEventListener('resize', updateFade);
			updateFade();
		}
	});
})();
</script>
