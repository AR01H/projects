<?php
/**
 * Dashboard tab - the architecture at a glance: what each registry contains
 * and where to edit it. Included by nt_admin_render_page().
 */

defined( 'ABSPATH' ) || exit;

$nt_pages     = nt_config( 'pages' );
$nt_ajax      = nt_config( 'ajax' );
$nt_rest      = nt_config( 'rest' );
$nt_redirects = nt_config( 'redirects' );
$nt_routes    = nt_config( 'routes' );

$nt_cards = array(
	array(
		'title' => __( 'Pages', NT_TEXT_DOMAIN ),
		'count' => count( $nt_pages ),
		'file'  => 'config/pages.php',
		'desc'  => __( 'Slug, template, CSS/JS per entry. Auto-created on Sync.', NT_TEXT_DOMAIN ),
	),
	array(
		'title' => __( 'AJAX Actions', NT_TEXT_DOMAIN ),
		'count' => count( $nt_ajax ),
		'file'  => 'config/ajax.php',
		'desc'  => __( 'Nonce + capability enforced by the dispatcher.', NT_TEXT_DOMAIN ),
	),
	array(
		'title' => __( 'REST Routes', NT_TEXT_DOMAIN ),
		'count' => count( (array) ( $nt_rest['routes'] ?? array() ) ),
		'file'  => 'config/rest.php',
		'desc'  => sprintf( __( 'Namespace: %s', NT_TEXT_DOMAIN ), (string) ( $nt_rest['namespace'] ?? 'nt/v1' ) ),
	),
	array(
		'title' => __( 'Redirects', NT_TEXT_DOMAIN ),
		'count' => count( $nt_redirects ),
		'file'  => 'config/redirects.php',
		'desc'  => __( 'Path-to-destination rules, run before rendering.', NT_TEXT_DOMAIN ),
	),
	array(
		'title' => __( 'Dynamic Routes', NT_TEXT_DOMAIN ),
		'count' => count( $nt_routes ),
		'file'  => 'config/routes.php',
		'desc'  => __( 'DB-driven URL matchers (term pages etc.).', NT_TEXT_DOMAIN ),
	),
);
?>

<div class="nt-admin-cards">
	<?php foreach ( $nt_cards as $nt_card ) : ?>
		<div class="nt-admin-card">
			<span class="nt-admin-card-count"><?php echo esc_html( (string) $nt_card['count'] ); ?></span>
			<h3><?php echo esc_html( $nt_card['title'] ); ?></h3>
			<p><?php echo esc_html( $nt_card['desc'] ); ?></p>
			<code><?php echo esc_html( $nt_card['file'] ); ?></code>
		</div>
	<?php endforeach; ?>
</div>

<h2><?php esc_html_e( 'Registered Pages', NT_TEXT_DOMAIN ); ?></h2>
<table class="widefat striped nt-admin-table">
	<thead>
		<tr>
			<th><?php esc_html_e( 'Slug', NT_TEXT_DOMAIN ); ?></th>
			<th><?php esc_html_e( 'Title', NT_TEXT_DOMAIN ); ?></th>
			<th><?php esc_html_e( 'Template', NT_TEXT_DOMAIN ); ?></th>
			<th><?php esc_html_e( 'WP Page', NT_TEXT_DOMAIN ); ?></th>
			<th><?php esc_html_e( 'View', NT_TEXT_DOMAIN ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $nt_pages as $nt_slug => $nt_def ) : ?>
			<?php
			$nt_exists   = get_page_by_path( (string) $nt_slug ) instanceof WP_Post;
			$nt_page_url = ! empty( $nt_def['front'] ) ? home_url( '/' ) : home_url( '/' . $nt_slug . '/' );
			?>
			<tr>
				<td><code>/<?php echo esc_html( (string) $nt_slug ); ?>/</code><?php echo ! empty( $nt_def['front'] ) ? ' <span class="nt-badge">' . esc_html__( 'front', NT_TEXT_DOMAIN ) . '</span>' : ''; ?></td>
				<td><?php echo esc_html( (string) ( $nt_def['title'] ?? '' ) ); ?></td>
				<td><code><?php echo esc_html( (string) ( $nt_def['template'] ?? '' ) ); ?></code></td>
				<td><?php echo $nt_exists ? esc_html__( 'created', NT_TEXT_DOMAIN ) : esc_html__( 'virtual (serves anyway)', NT_TEXT_DOMAIN ); ?></td>
				<td><a href="<?php echo esc_url( $nt_page_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Open', NT_TEXT_DOMAIN ); ?> &rarr;</a></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
