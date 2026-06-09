<?php defined( 'ABSPATH' ) || exit; ?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<?php wp_nonce_field( 'ch_content_settings_hire_packages' ); ?>
	<input type="hidden" name="action" value="ch_content_settings_hire_packages">

	<div class="ch-card">
		<h2>🎪 Event Hire Packages</h2>
		<p class="ch-cs-desc">Each package shows an icon, title, description, and bullet list of inclusions on the Events page.</p>

		<?php $pkgs_list = (array) ( $pkgs ?? [] ); ?>
		<?php foreach ( $pkgs_list as $idx => $pkg ) :
			$pkg       = (array) $pkg;
			$pkg_items = implode( "\n", (array) ( $pkg['items'] ?? [] ) );
		?>
		<div style="background:#f9f9f9;border:1px solid #e8e8e8;border-radius:6px;padding:1rem;margin-bottom:.8rem;">
			<div class="ch-row">
				<label>Icon</label>
				<input type="text" name="hire_packages[<?php echo $idx; ?>][icon]"
					value="<?php echo esc_attr( $pkg['icon'] ?? '' ); ?>"
					style="width:70px;min-width:70px;flex:0 0 auto;">
				<label style="min-width:80px;">Title</label>
				<input type="text" name="hire_packages[<?php echo $idx; ?>][title]"
					value="<?php echo esc_attr( $pkg['title'] ?? '' ); ?>"
					placeholder="Package name">
			</div>
			<div class="ch-row">
				<label>Description</label>
				<textarea name="hire_packages[<?php echo $idx; ?>][desc]" rows="2"
					style="width:100%;flex:1;"><?php echo esc_textarea( $pkg['desc'] ?? '' ); ?></textarea>
			</div>
			<div class="ch-row">
				<label>List Items<br><small class="ch-cs-hint">(one per line)</small></label>
				<textarea name="hire_packages[<?php echo $idx; ?>][items]" rows="5"
					style="width:100%;flex:1;"><?php echo esc_textarea( $pkg_items ); ?></textarea>
			</div>
		</div>
		<?php endforeach; ?>

		<?php if ( empty( $pkgs_list ) ) : ?>
			<p class="ch-cs-desc">No packages found. Add packages via
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ch-theme-mock' ) ); ?>">Install Mock Data</a>
				or through the Events database table.
			</p>
		<?php endif; ?>
	</div>

	<?php submit_button( '💾 Save Hire Packages', 'primary', 'submit', false ); ?>
</form>
