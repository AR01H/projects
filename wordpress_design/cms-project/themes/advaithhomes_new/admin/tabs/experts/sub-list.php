<?php
/**
 * admin/tabs/experts/sub-list.php - Expert / Team member list.
 *
 * Lists all DB-stored experts with photo, name, title, category, status, rating.
 * Provides Edit and Delete actions per row.
 */

defined( 'ABSPATH' ) || exit;

$rows    = class_exists( 'AH_Expert_DB' ) ? AH_Expert_DB::get_all() : array();
$new_url = ADN_Theme_Admin::tab_url( 'experts', 'new' );
?>

<div class="card" style="max-width:none;margin-bottom:24px;">
	<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
		<h2 style="margin:0;"><?php esc_html_e( 'Experts / Team Members', ADN_TEXT_DOMAIN ); ?></h2>
		<a href="<?php echo esc_url( $new_url ); ?>" class="button button-primary">
			<?php esc_html_e( '+ Add New Expert', ADN_TEXT_DOMAIN ); ?>
		</a>
	</div>
	<p class="description" style="margin-bottom:16px;">
		<?php esc_html_e( 'Manage expert profiles shown on the Ask an Expert page. Each expert gets a public profile page at /ask-expert/SLUG/.', ADN_TEXT_DOMAIN ); ?>
	</p>

	<?php if ( empty( $rows ) ) : ?>
		<p style="color:#6b7280;font-style:italic;">
			<?php esc_html_e( 'No experts yet. Use the "+ Add New Expert" button above to create your first profile.', ADN_TEXT_DOMAIN ); ?>
		</p>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped" style="margin-top:8px;">
			<thead>
				<tr>
					<th style="width:60px;"><?php esc_html_e( 'Photo', ADN_TEXT_DOMAIN ); ?></th>
					<th><?php esc_html_e( 'Name', ADN_TEXT_DOMAIN ); ?></th>
					<th><?php esc_html_e( 'Specialisation', ADN_TEXT_DOMAIN ); ?></th>
					<th><?php esc_html_e( 'Category', ADN_TEXT_DOMAIN ); ?></th>
					<th style="width:80px;"><?php esc_html_e( 'Status', ADN_TEXT_DOMAIN ); ?></th>
					<th style="width:80px;"><?php esc_html_e( 'Rating', ADN_TEXT_DOMAIN ); ?></th>
					<th style="width:160px;"><?php esc_html_e( 'Actions', ADN_TEXT_DOMAIN ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $r ) :
					$edit_url  = add_query_arg( 'edit_slug', $r['expert_slug'], $new_url );
					$photo_url = ( ! empty( $r['photo_id'] ) && (int) $r['photo_id'] > 0 )
						? wp_get_attachment_image_url( (int) $r['photo_id'], 'thumbnail' )
						: false;
					$rating    = ! empty( $r['rating'] ) ? number_format( (float) $r['rating'], 1 ) : '-';
				?>
				<tr>
					<td>
						<?php if ( $photo_url ) : ?>
							<img src="<?php echo esc_url( $photo_url ); ?>"
								style="width:48px;height:48px;object-fit:cover;border-radius:50%;display:block;" alt="">
						<?php else : ?>
							<div style="width:48px;height:48px;background:linear-gradient(135deg,#e8f0ec,#d4e6d8);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;">👤</div>
						<?php endif; ?>
					</td>
					<td>
						<strong><?php echo esc_html( $r['name'] ); ?></strong><br>
						<code style="font-size:11px;color:#9ca3af;"><?php echo esc_html( $r['expert_slug'] ); ?></code>
					</td>
					<td><?php echo esc_html( $r['title'] ); ?></td>
					<td><?php echo esc_html( $r['category'] ); ?></td>
					<td>
						<?php if ( 'active' === $r['status'] ) : ?>
							<span style="color:#16a34a;font-weight:600;"><?php esc_html_e( 'Active', ADN_TEXT_DOMAIN ); ?></span>
						<?php else : ?>
							<span style="color:#9ca3af;"><?php esc_html_e( 'Inactive', ADN_TEXT_DOMAIN ); ?></span>
						<?php endif; ?>
					</td>
					<td><?php echo esc_html( $rating ); ?></td>
					<td style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
						<a href="<?php echo esc_url( $edit_url ); ?>" class="button button-small">
							<?php esc_html_e( 'Edit', ADN_TEXT_DOMAIN ); ?>
						</a>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
							onsubmit="return confirm('<?php echo esc_js( __( 'Delete this expert? This cannot be undone.', ADN_TEXT_DOMAIN ) ); ?>');"
							style="margin:0;">
							<input type="hidden" name="action"      value="adn_delete_expert">
							<input type="hidden" name="expert_slug" value="<?php echo esc_attr( $r['expert_slug'] ); ?>">
							<?php wp_nonce_field( 'adn_delete_expert' ); ?>
							<button type="submit" class="button button-small" style="color:#b91c1c;border-color:#b91c1c;">
								<?php esc_html_e( 'Delete', ADN_TEXT_DOMAIN ); ?>
							</button>
						</form>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
