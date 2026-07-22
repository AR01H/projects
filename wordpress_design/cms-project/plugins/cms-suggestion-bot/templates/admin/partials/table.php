<?php
/**
 * templates/admin/partials/table.php - reusable striped admin table.
 * Used by every admin page that lists rows (Reader runs, Knowledge Base,
 * Logs, API keys, ...) so the table markup only exists once.
 *
 * @var array<int, string>             $headers
 * @var array<int, array<int, string>> $rows       Pre-escaped cell HTML.
 * @var string                         $empty_text
 */
defined( 'ABSPATH' ) || exit;
?>
<table class="widefat striped csb-table">
	<thead>
		<tr>
			<?php foreach ( $headers as $header ) : ?>
				<th><?php echo esc_html( $header ); ?></th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php if ( empty( $rows ) ) : ?>
			<tr><td colspan="<?php echo esc_attr( (string) count( $headers ) ); ?>"><?php echo esc_html( $empty_text ?: __( 'Nothing to show yet.', 'cms-suggestion-bot' ) ); ?></td></tr>
		<?php else : ?>
			<?php foreach ( $rows as $row ) : ?>
				<tr>
					<?php foreach ( $row as $cell ) : ?>
						<td><?php echo wp_kses_post( $cell ); ?></td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>
