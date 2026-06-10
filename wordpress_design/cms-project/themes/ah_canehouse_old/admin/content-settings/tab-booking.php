<?php defined( 'ABSPATH' ) || exit; ?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<?php wp_nonce_field( 'ch_content_settings_booking' ); ?>
	<input type="hidden" name="action" value="ch_content_settings_booking">

	<div class="ch-card">
		<h2>📅 Booking Wizard - Occasion Options</h2>
		<p class="ch-cs-desc">Occasion types shown in Step 3 of the booking wizard. Add, remove or drag to reorder.</p>

		<div class="ch-rep-header ch-rep-header--single">
			<span>Occasion Name</span>
			<span></span>
		</div>

		<div class="ch-repeater ch-repeater--single" id="ch-occasion-repeater">
			<?php foreach ( $occasions as $i => $occ ) : ?>
			<div class="ch-rep-row ch-rep-row--single">
				<input type="text" name="occasions[<?php echo $i; ?>]"
					value="<?php echo esc_attr( $occ ); ?>"
					placeholder="e.g. Birthday Party">
				<button type="button" class="ch-rep-remove" title="Remove">✕</button>
			</div>
			<?php endforeach; ?>
		</div>

		<button type="button" class="ch-rep-add button" data-target="ch-occasion-repeater" data-prefix="occasions" data-single="1">
			+ Add Occasion
		</button>
	</div>

	<?php submit_button( '💾 Save Occasions', 'primary', 'submit', false ); ?>
</form>
