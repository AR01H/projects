<?php
/**
 * templates/admin/logs.php
 *
 * @var array<int, array<string, mixed>> $entries
 * @var array<int, string>               $channels
 * @var string                           $channel
 * @var string                           $notice
 */

use CmsSuggestionBot\Admin\View;

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Logs', 'cms-suggestion-bot' ); ?></h1>

	<?php View::notice( $notice ); ?>

	<div class="csb-flex-between">
		<form method="get">
			<input type="hidden" name="page" value="<?php echo esc_attr( CSB_MENU_SLUG . '-logs' ); ?>">
			<select name="channel" onchange="this.form.submit()">
				<option value=""><?php esc_html_e( 'All Channels', 'cms-suggestion-bot' ); ?></option>
				<?php foreach ( $channels as $c ) : ?>
					<option value="<?php echo esc_attr( $c ); ?>" <?php selected( $channel, $c ); ?>><?php echo esc_html( ucfirst( $c ) ); ?></option>
				<?php endforeach; ?>
			</select>
		</form>

		<form method="post" onsubmit="return confirm('<?php echo esc_js( __( 'Purge old log entries?', 'cms-suggestion-bot' ) ); ?>');">
			<?php wp_nonce_field( 'csb_purge_logs' ); ?>
			<?php esc_html_e( 'Purge older than', 'cms-suggestion-bot' ); ?>
			<input type="number" name="days" value="30" class="csb-input-narrow"> <?php esc_html_e( 'days', 'cms-suggestion-bot' ); ?>
			<button type="submit" name="csb_purge_logs" value="1" class="button"><?php esc_html_e( 'Purge', 'cms-suggestion-bot' ); ?></button>
		</form>
	</div>

	<?php
	View::table(
		array( __( 'Time', 'cms-suggestion-bot' ), __( 'Channel', 'cms-suggestion-bot' ), __( 'Level', 'cms-suggestion-bot' ), __( 'Message', 'cms-suggestion-bot' ) ),
		array_map(
			static fn( array $row ) => array(
				esc_html( (string) $row['created_at'] ),
				esc_html( (string) $row['channel'] ),
				esc_html( (string) $row['level'] ),
				esc_html( (string) $row['message'] ),
			),
			$entries
		),
		__( 'No log entries yet.', 'cms-suggestion-bot' )
	);
	?>
</div>
