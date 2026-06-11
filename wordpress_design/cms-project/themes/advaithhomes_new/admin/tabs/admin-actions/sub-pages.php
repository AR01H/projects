<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="card" style="max-width:none;">
	<h2><?php esc_html_e( 'Pages & Permalinks', ADN_TEXT_DOMAIN ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'After adding new page templates or slugs, run this to create any missing default pages and refresh permalinks, so the new URLs resolve immediately.', ADN_TEXT_DOMAIN ); ?>
	</p>

	<p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block; margin-right:.5rem;">
			<input type="hidden" name="action" value="adn_sync_pages" />
			<?php wp_nonce_field( 'adn_sync_pages' ); ?>
			<button type="submit" class="button button-primary">
				<?php esc_html_e( 'Sync Pages & Flush Permalinks', ADN_TEXT_DOMAIN ); ?>
			</button>
		</form>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;">
			<input type="hidden" name="action" value="adn_flush_rewrites" />
			<?php wp_nonce_field( 'adn_flush_rewrites' ); ?>
			<button type="submit" class="button">
				<?php esc_html_e( 'Flush Permalinks Only', ADN_TEXT_DOMAIN ); ?>
			</button>
		</form>
	</p>

	<h3 style="margin-top:1.5rem;"><?php esc_html_e( 'Default pages', ADN_TEXT_DOMAIN ); ?></h3>
	<table class="widefat striped" style="max-width:640px;">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Slug', ADN_TEXT_DOMAIN ); ?></th>
				<th><?php esc_html_e( 'Title', ADN_TEXT_DOMAIN ); ?></th>
				<th><?php esc_html_e( 'Template', ADN_TEXT_DOMAIN ); ?></th>
				<th><?php esc_html_e( 'Status', ADN_TEXT_DOMAIN ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		$definitions = function_exists( 'adn_get_page_definitions' ) ? adn_get_page_definitions() : array();
		foreach ( $definitions as $slug => $def ) :
			$page = get_page_by_path( $slug );
			?>
			<tr>
				<td><code><?php echo esc_html( $slug ); ?></code></td>
				<td><?php echo esc_html( $def['title'] ); ?></td>
				<td><code><?php echo esc_html( $def['template'] ); ?></code></td>
				<td>
					<?php
					echo $page
						? '&#9989; ' . esc_html__( 'exists', ADN_TEXT_DOMAIN )
						: '&#8212; ' . esc_html__( 'missing', ADN_TEXT_DOMAIN );
					?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
