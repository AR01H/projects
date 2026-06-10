<?php defined( 'ABSPATH' ) || exit; ?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<?php wp_nonce_field( 'ch_content_settings_business' ); ?>
	<input type="hidden" name="action" value="ch_content_settings_business">

	<div class="ch-card">
		<h2>🕒 Opening Hours &amp; Response Time</h2>
		<p class="ch-cs-desc">These appear on the Contact page and in form messages.</p>

		<div class="ch-row">
			<label>Opening Hours</label>
			<input type="text" name="business_hours"
				value="<?php echo esc_attr( $s['business_hours'] ?? 'Mon-Sat · 9am-9pm' ); ?>"
				placeholder="e.g. Mon-Sat · 9am-9pm">
			<span class="ch-cs-hint">Shown in contact info strip</span>
		</div>
		<div class="ch-row">
			<label>Response Time</label>
			<input type="text" name="response_time"
				value="<?php echo esc_attr( $s['response_time'] ?? 'within 24 hours' ); ?>"
				placeholder="e.g. within 24 hours">
			<span class="ch-cs-hint">Used in "we reply X" messages</span>
		</div>
		<div class="ch-row">
			<label>Coverage Area</label>
			<input type="text" name="address"
				value="<?php echo esc_attr( $s['address'] ?? 'Available across the UK' ); ?>"
				placeholder="e.g. Available across the UK">
		</div>
		<div class="ch-row">
			<label>Events &amp; Hire Info Text</label>
			<input type="text" name="events_info_text"
				value="<?php echo esc_attr( $s['events_info_text'] ?? 'Available across the UK for events, weddings & community gatherings' ); ?>"
				placeholder="Short line shown in contact section">
		</div>
		<div class="ch-row">
			<label>Franchise Info Text</label>
			<input type="text" name="franchise_info_text"
				value="<?php echo esc_attr( $s['franchise_info_text'] ?? 'Franchise enquiries warmly welcomed - reach out today' ); ?>"
				placeholder="Short line shown in contact section">
		</div>
	</div>

	<?php submit_button( '💾 Save Business Details', 'primary', 'submit', false ); ?>
</form>
