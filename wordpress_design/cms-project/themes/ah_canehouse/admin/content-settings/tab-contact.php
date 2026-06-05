<?php defined( 'ABSPATH' ) || exit; ?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<?php wp_nonce_field( 'ch_content_settings_contact' ); ?>
	<input type="hidden" name="action" value="ch_content_settings_contact">

	<div class="ch-card">
		<h2>📬 Contact Form — Enquiry Types</h2>
		<p class="ch-cs-desc">Options shown in the "I'm enquiring about" dropdown. Each item needs a <strong>Value</strong> (no spaces, lowercase) and a <strong>Label</strong> (what the visitor sees).</p>

		<div class="ch-rep-header">
			<span>Value <small>(internal key)</small></span>
			<span>Label <small>(shown to visitor)</small></span>
			<span></span>
		</div>

		<div class="ch-repeater" id="ch-enquiry-repeater">
			<?php foreach ( $enquiry_types as $i => $et ) : ?>
			<div class="ch-rep-row">
				<input type="text" name="enquiry_types[<?php echo $i; ?>][value]"
					value="<?php echo esc_attr( $et['value'] ); ?>"
					placeholder="e.g. event" class="ch-rep-val">
				<input type="text" name="enquiry_types[<?php echo $i; ?>][label]"
					value="<?php echo esc_attr( $et['label'] ); ?>"
					placeholder="e.g. Event / Stall Hire" class="ch-rep-lbl">
				<button type="button" class="ch-rep-remove" title="Remove">✕</button>
			</div>
			<?php endforeach; ?>
		</div>

		<button type="button" class="ch-rep-add button" data-target="ch-enquiry-repeater" data-prefix="enquiry_types">
			+ Add Enquiry Type
		</button>
	</div>

	<?php submit_button( '💾 Save Enquiry Types', 'primary', 'submit', false ); ?>
</form>
