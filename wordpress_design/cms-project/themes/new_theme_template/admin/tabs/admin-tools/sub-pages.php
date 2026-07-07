<?php
/**
 * Admin Tools -> Pages. Sync tool (registry group 'pages') plus a live
 * status table of every entry in config/pages.php.
 */

defined( 'ABSPATH' ) || exit;

nt_admin_tools_render( 'pages' );
?>

<h2><?php esc_html_e( 'Page Registry Status', NT_TEXT_DOMAIN ); ?></h2>
<table class="widefat striped nt-admin-table">
	<thead>
		<tr>
			<th><?php esc_html_e( 'Slug', NT_TEXT_DOMAIN ); ?></th>
			<th><?php esc_html_e( 'Template', NT_TEXT_DOMAIN ); ?></th>
			<th><?php esc_html_e( 'Template file', NT_TEXT_DOMAIN ); ?></th>
			<th><?php esc_html_e( 'WP Page', NT_TEXT_DOMAIN ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( nt_config( 'pages' ) as $nt_slug => $nt_def ) : ?>
			<?php
			$nt_template = (string) ( $nt_def['template'] ?? '' );
			$nt_file_ok  = '' !== $nt_template && is_file( NT_THEME_DIR . '/' . $nt_template );
			$nt_page_ok  = get_page_by_path( (string) $nt_slug ) instanceof WP_Post;
			?>
			<tr>
				<td><code>/<?php echo esc_html( (string) $nt_slug ); ?>/</code></td>
				<td><code><?php echo esc_html( $nt_template ); ?></code></td>
				<td><?php echo $nt_file_ok ? esc_html__( 'OK', NT_TEXT_DOMAIN ) : '<strong>' . esc_html__( 'MISSING', NT_TEXT_DOMAIN ) . '</strong>'; ?></td>
				<td><?php echo $nt_page_ok ? esc_html__( 'created', NT_TEXT_DOMAIN ) : esc_html__( 'virtual (serves anyway)', NT_TEXT_DOMAIN ); ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
