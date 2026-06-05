<?php defined( 'ABSPATH' ) || exit; ?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<?php wp_nonce_field( 'ch_content_settings_certs' ); ?>
	<input type="hidden" name="action" value="ch_content_settings_certs">

	<div class="ch-card">
		<h2>✅ Certification Badges</h2>
		<p class="ch-cs-desc">
			Each row is one card shown in the Certifications section.
			<strong>Icon</strong> = emoji, <strong>Title</strong> is required, <strong>Description</strong> and <strong>Badge label</strong> are optional.
		</p>

		<div class="ch-rep-header ch-rep-header--certs">
			<span>Icon</span><span>Title *</span><span>Description</span><span>Badge</span><span></span>
		</div>

		<div class="ch-repeater ch-repeater--certs" id="ch-certs-repeater">
			<?php foreach ( $certifications as $i => $cert ) :
				$cert = (array) $cert;
			?>
			<div class="ch-rep-row ch-rep-row--certs">
				<input type="text" name="certs[<?php echo $i; ?>][icon]"
					value="<?php echo esc_attr( $cert['icon'] ?? '✅' ); ?>"
					placeholder="✅" style="text-align:center;font-size:1.3rem;">
				<input type="text" name="certs[<?php echo $i; ?>][title]"
					value="<?php echo esc_attr( $cert['title'] ?? '' ); ?>"
					placeholder="e.g. Food Safety Registered">
				<input type="text" name="certs[<?php echo $i; ?>][desc]"
					value="<?php echo esc_attr( $cert['descr'] ?? $cert['desc'] ?? '' ); ?>"
					placeholder="Short description">
				<input type="text" name="certs[<?php echo $i; ?>][badge]"
					value="<?php echo esc_attr( $cert['badge'] ?? '' ); ?>"
					placeholder="e.g. NCASS Member">
				<button type="button" class="ch-rep-remove" title="Remove">✕</button>
			</div>
			<?php endforeach; ?>
		</div>

		<button type="button" class="ch-rep-add button"
			data-target="ch-certs-repeater"
			data-prefix="certs"
			data-columns="icon,title,desc,badge">
			+ Add Certification
		</button>
	</div>

	<?php submit_button( '💾 Save Certifications', 'primary', 'submit', false ); ?>
</form>
