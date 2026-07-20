<?php
/**
 * Admin Tools -> System Info. Read-only environment overview - handy for
 * support tickets and for checking a fresh install.
 */

defined( 'ABSPATH' ) || exit;

global $wpdb;

$nt_rows = array(
	__( 'Theme', NT_TEXT_DOMAIN )            => wp_get_theme()->get( 'Name' ) . ' ' . NT_THEME_VERSION,
	__( 'WordPress', NT_TEXT_DOMAIN )        => get_bloginfo( 'version' ),
	__( 'PHP', NT_TEXT_DOMAIN )              => PHP_VERSION,
	__( 'Database', NT_TEXT_DOMAIN )         => $wpdb->db_version(),
	__( 'Memory limit', NT_TEXT_DOMAIN )     => (string) ini_get( 'memory_limit' ),
	__( 'Max upload size', NT_TEXT_DOMAIN )  => size_format( wp_max_upload_size() ),
	__( 'WP_DEBUG', NT_TEXT_DOMAIN )         => ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'on' : 'off',
	__( 'Coming soon mode', NT_TEXT_DOMAIN ) => ( defined( 'NT_COMING_SOON' ) && NT_COMING_SOON ) ? 'ON (visitors redirected)' : 'off',
	__( 'Permalinks', NT_TEXT_DOMAIN )       => get_option( 'permalink_structure' ) ?: __( 'Plain (consider pretty permalinks)', NT_TEXT_DOMAIN ),
	__( 'Active plugins', NT_TEXT_DOMAIN )   => (string) count( (array) get_option( 'active_plugins', array() ) ),
	__( 'Registered pages', NT_TEXT_DOMAIN ) => (string) count( nt_config( 'pages' ) ),
	__( 'AJAX actions', NT_TEXT_DOMAIN )     => (string) count( nt_config( 'ajax' ) ),
	__( 'REST routes', NT_TEXT_DOMAIN )      => (string) count( (array) ( nt_config( 'rest' )['routes'] ?? array() ) ),
);
?>

<table class="widefat striped nt-admin-table" style="max-width:640px;">
	<tbody>
		<?php foreach ( $nt_rows as $nt_label => $nt_value ) : ?>
			<tr>
				<th scope="row" style="width:220px;"><?php echo esc_html( (string) $nt_label ); ?></th>
				<td><?php echo esc_html( (string) $nt_value ); ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
