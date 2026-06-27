<?php
defined( 'ABSPATH' ) || exit;

$engine_active = class_exists( 'AH_Rules_Engine' );
?>
<div class="card" style="max-width:none;">
	<h2><?php esc_html_e( 'Workflow Manager', ADN_TEXT_DOMAIN ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'The theme fires these automation triggers (see includes/rules_conditions.php). Attach email / WhatsApp / webhook actions to them in the CMS plugin - or install the ready-made sample rule below.', ADN_TEXT_DOMAIN ); ?>
	</p>

	<?php if ( ! $engine_active ) : ?>
		<div class="notice notice-warning inline">
			<p><?php esc_html_e( 'The CMS plugin (AH_Rules_Engine) is not active. Triggers still fire harmlessly, but no actions run until the plugin is activated.', ADN_TEXT_DOMAIN ); ?></p>
		</div>
	<?php endif; ?>

	<h3><?php esc_html_e( 'Theme triggers', ADN_TEXT_DOMAIN ); ?></h3>
	<table class="widefat striped" style="max-width:720px;">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Trigger', ADN_TEXT_DOMAIN ); ?></th>
				<th><?php esc_html_e( 'Fired by', ADN_TEXT_DOMAIN ); ?></th>
				<th><?php esc_html_e( 'Active rules', ADN_TEXT_DOMAIN ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		$triggers = class_exists( 'ADN_Rules' ) ? ADN_Rules::all() : array();
		foreach ( $triggers as $trigger => $label ) :
			$count = ADN_Theme_Admin::count_rules_for_trigger( $trigger );
			?>
			<tr>
				<td><code><?php echo esc_html( $trigger ); ?></code></td>
				<td><?php echo esc_html( $label ); ?></td>
				<td>
					<?php
					if ( $count < 0 ) {
						echo '&#8212; ' . esc_html__( 'plugin inactive', ADN_TEXT_DOMAIN );
					} elseif ( 0 === $count ) {
						echo '&#9888;&#65039; 0 - ' . esc_html__( 'nothing will happen', ADN_TEXT_DOMAIN );
					} else {
						echo '&#9989; ' . esc_html( (string) $count );
					}
					?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<h3 style="margin-top:1.5rem;"><?php esc_html_e( 'Sample rule', ADN_TEXT_DOMAIN ); ?></h3>
	<p class="description">
		<?php
		printf(
			/* translators: %s: sample rule name */
			esc_html__( 'Installs "%s": every contact form submission sends an HTML email to the site admin with the {name}, {email}, {phone}, {topic} and {message} tokens. Use it as a starting point and edit it in the CMS plugin.', ADN_TEXT_DOMAIN ),
			esc_html( ADN_Theme_Admin::sample_rule_name() )
		);
		?>
	</p>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="adn_install_contact_rule" />
		<?php wp_nonce_field( 'adn_install_contact_rule' ); ?>
		<p>
			<button type="submit" class="button button-primary" <?php disabled( ! $engine_active ); ?>>
				<?php esc_html_e( 'Install Sample Contact Rule', ADN_TEXT_DOMAIN ); ?>
			</button>
		</p>
	</form>
</div>
