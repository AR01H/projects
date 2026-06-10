<?php defined( 'ABSPATH' ) || exit; ?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<?php wp_nonce_field( 'ch_content_settings_franchise_locations' ); ?>
	<input type="hidden" name="action" value="ch_content_settings_franchise_locations">

	<div class="ch-card">
		<h2>📍 Franchise Locations</h2>
		<p class="ch-cs-desc">Locations shown in the scrolling franchise marquee on the Franchise page.</p>

		<div class="ch-rep-header" style="grid-template-columns:60px 1fr 36px;">
			<span>Icon</span><span>Location Name</span><span></span>
		</div>
		<div class="ch-repeater" id="ch-locs-repeater">
			<?php foreach ( (array) ( $locs ?? [] ) as $idx => $loc ) :
				$loc = (array) $loc;
			?>
			<div class="ch-rep-row" style="grid-template-columns:60px 1fr 36px;">
				<input type="text" name="franchise_locations[<?php echo $idx; ?>][icon]"
					value="<?php echo esc_attr( $loc['icon'] ?? '📍' ); ?>"
					style="text-align:center;font-size:1.2rem;">
				<input type="text" name="franchise_locations[<?php echo $idx; ?>][name]"
					value="<?php echo esc_attr( $loc['name'] ?? '' ); ?>"
					placeholder="City - Area">
				<button type="button" class="ch-rep-remove" title="Remove">✕</button>
			</div>
			<?php endforeach; ?>
		</div>
		<button type="button" id="ch-add-loc-cs" class="button ch-rep-add" style="margin-top:.4rem;">
			+ Add Location
		</button>
	</div>

	<?php submit_button( '💾 Save Franchise Locations', 'primary', 'submit', false ); ?>
</form>

<script>
/* Add-row for franchise locations - sets icon default "📍" */
document.getElementById('ch-add-loc-cs').addEventListener('click', function () {
	var rep = document.getElementById('ch-locs-repeater');
	var idx = rep.querySelectorAll('.ch-rep-row').length;
	var row = document.createElement('div');
	row.className = 'ch-rep-row';
	row.style.gridTemplateColumns = '60px 1fr 36px';
	row.innerHTML =
		'<input type="text" name="franchise_locations[' + idx + '][icon]" value="📍" style="text-align:center;font-size:1.2rem;">' +
		'<input type="text" name="franchise_locations[' + idx + '][name]" placeholder="City - Area">' +
		'<button type="button" class="ch-rep-remove" title="Remove">✕</button>';
	rep.appendChild(row);
	row.querySelectorAll('input')[1].focus();
});
</script>
