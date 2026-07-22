<?php
/**
 * templates/admin/api.php
 *
 * @var bool                             $api_enabled
 * @var array<int, array<string, mixed>> $keys
 * @var string|null                      $new_key
 * @var string                           $notice
 */

use CmsSuggestionBot\Admin\View;

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap">
	<h1><?php esc_html_e( 'API', 'cms-suggestion-bot' ); ?></h1>

	<?php View::notice( $notice ); ?>

	<p>
		<?php esc_html_e( 'Status:', 'cms-suggestion-bot' ); ?>
		<?php echo $api_enabled ? '🟢 ' . esc_html__( 'Enabled', 'cms-suggestion-bot' ) : '⚪ ' . esc_html__( 'Disabled', 'cms-suggestion-bot' ); ?>
		&mdash;
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . CSB_MENU_SLUG . '-configuration&tab=api' ) ); ?>"><?php esc_html_e( 'Change in Configuration', 'cms-suggestion-bot' ); ?></a>
	</p>

	<?php if ( $new_key ) : ?>
		<div class="notice notice-warning"><p>
			<strong><?php esc_html_e( 'New key:', 'cms-suggestion-bot' ); ?></strong>
			<code><?php echo esc_html( $new_key ); ?></code>
			<?php esc_html_e( '(copy it now - it will not be shown again)', 'cms-suggestion-bot' ); ?>
		</p></div>
	<?php endif; ?>

	<h2><?php esc_html_e( 'Issue a New Key', 'cms-suggestion-bot' ); ?></h2>
	<form method="post">
		<?php wp_nonce_field( 'csb_api_keys' ); ?>
		<input type="text" name="label" placeholder="<?php esc_attr_e( 'Label, e.g. \"Mobile App\"', 'cms-suggestion-bot' ); ?>">
		<button type="submit" name="csb_api_issue" value="1" class="button button-primary"><?php esc_html_e( 'Issue Key', 'cms-suggestion-bot' ); ?></button>
	</form>

	<h2 class="csb-section-heading--sm"><?php esc_html_e( 'Existing Keys', 'cms-suggestion-bot' ); ?></h2>
	<?php
	View::table(
		array( __( 'Label', 'cms-suggestion-bot' ), __( 'Key', 'cms-suggestion-bot' ), __( 'Status', 'cms-suggestion-bot' ), __( 'Rate Limit', 'cms-suggestion-bot' ), __( 'Last Used', 'cms-suggestion-bot' ), __( 'Actions', 'cms-suggestion-bot' ) ),
		array_map(
			static function ( array $k ) {
				$masked = substr( (string) $k['api_key'], 0, 6 ) . str_repeat( '•', 10 );
				$active = (int) $k['is_active'] === 1;
				return array(
					esc_html( (string) $k['label'] ),
					'<code>' . esc_html( $masked ) . '</code>',
					$active ? '🟢 ' . esc_html__( 'Active', 'cms-suggestion-bot' ) : '⚪ ' . esc_html__( 'Revoked', 'cms-suggestion-bot' ),
					esc_html( (string) $k['rate_limit'] ) . '/min',
					esc_html( (string) ( $k['last_used_at'] ?? '—' ) ),
					'<form method="post" class="csb-form-inline">'
					. wp_nonce_field( 'csb_api_keys', '_wpnonce', true, false )
					. '<input type="hidden" name="id" value="' . esc_attr( (string) $k['id'] ) . '">'
					. ( $active ? '<button type="submit" name="csb_api_revoke" value="1" class="button button-small">' . esc_html__( 'Revoke', 'cms-suggestion-bot' ) . '</button> ' : '' )
					. '<button type="submit" name="csb_api_delete" value="1" class="button button-small button-link-delete" onclick="return confirm(\'' . esc_js( __( 'Delete this key?', 'cms-suggestion-bot' ) ) . '\');">' . esc_html__( 'Delete', 'cms-suggestion-bot' ) . '</button></form>',
				);
			},
			$keys
		),
		__( 'No API keys yet.', 'cms-suggestion-bot' )
	);
	?>
</div>
