<?php
namespace Ah\Cms\Feature\Posts\Controller;

defined( 'ABSPATH' ) || exit;

class PostAdminController {

	public static function register_metaboxes(): void {
		add_meta_box(
			'ah-cms-post-settings',
			'CMS Post Settings',
			array( self::class, 'render_metabox' ),
			'post',
			'side',
			'default'
		);
	}

	public static function render_metabox( \WP_Post $post ): void {
		wp_nonce_field( 'ah_cms_post_meta_save', 'ah_cms_post_meta_nonce' );
		$editor_mode  = get_post_meta( $post->ID, '_ah_editor_mode', true ) ?: 'gutenberg';
		$is_featured  = (bool) get_post_meta( $post->ID, '_ah_is_featured',  true );
		$is_popular   = (bool) get_post_meta( $post->ID, '_ah_is_popular',   true );
		$is_suggested = (bool) get_post_meta( $post->ID, '_ah_is_suggested', true );
		$form_edit_url = add_query_arg(
			array( 'page' => 'ah-posts', 'action' => 'edit-custom', 'id' => $post->ID ),
			admin_url( 'admin.php' )
		);
		?>
		<style>
		.ah-mb .ah-mb-row{margin-bottom:12px}
		.ah-mb label{display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px}
		.ah-mb .ah-mb-hint{font-size:11px;color:#888;margin-top:3px}
		.ah-mb .ah-mb-sep{border:0;border-top:1px solid #e0e0e0;margin:12px 0}
		.ah-mb .ah-tp-group{margin-bottom:8px}
		.ah-mb .ah-tp-head{font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#888;margin-bottom:4px}
		.ah-mb .ah-tp-opts{display:flex;flex-wrap:wrap;gap:4px}
		.ah-mb .ah-tp-chip{display:inline-flex;align-items:center;gap:4px;font-size:12px;padding:2px 7px;border:1px solid #cdd;border-radius:10px;cursor:pointer;background:#f9f9f9;user-select:none}
		.ah-mb .ah-tp-chip input{margin:0}
		.ah-mb .ah-tp-chip:has(input:checked){background:#e8f0fe;border-color:#4f7cf5;color:#1a49c4;font-weight:600}
		</style>
		<div class="ah-mb">
			<?php if ( $editor_mode === 'custom' ) : ?>
			<div class="ah-mb-row">
				<a href="<?php echo esc_url( $form_edit_url ); ?>" class="button button-secondary" style="width:100%;text-align:center;display:block;box-sizing:border-box;">
					&larr; Back to Form Editor
				</a>
			</div>
			<hr class="ah-mb-sep">
			<?php endif; ?>

			<div class="ah-mb-row">
				<label>
					<input type="checkbox" name="ah_is_featured" value="1" <?php checked( $is_featured ); ?>>
					<strong>Featured Post</strong>
				</label>
				<p class="ah-mb-hint">Show in featured sections across the site</p>
			</div>
			<div class="ah-mb-row">
				<label>
					<input type="checkbox" name="ah_is_popular" value="1" <?php checked( $is_popular ); ?>>
					<strong>Popular Post</strong>
				</label>
			</div>
			<div class="ah-mb-row">
				<label>
					<input type="checkbox" name="ah_is_suggested" value="1" <?php checked( $is_suggested ); ?>>
					<strong>Suggested Post</strong>
				</label>
			</div>

			<hr class="ah-mb-sep">
			<p style="font-size:12px;font-weight:600;margin:0 0 8px;">CMS Taxonomy Terms</p>
			<?php
			if ( class_exists( 'AH_Content_Taxonomy_Model' ) ) {
				$model  = new \AH_Content_Taxonomy_Model();
				$sel    = $model->get_term_ids( 'wp_post', $post->ID );
				$groups = $model->get_active_terms_grouped();
				if ( empty( $groups ) ) {
					echo '<p style="font-size:12px;color:#888;margin:0;">No taxonomy terms yet - add some in <a href="' . esc_url( admin_url( 'admin.php?page=ah-taxonomy' ) ) . '">Taxonomies</a>.</p>';
				} else {
					foreach ( $groups as $group ) {
						if ( empty( $group['items'] ) ) continue;
						?>
						<div class="ah-tp-group">
							<div class="ah-tp-head"><?php echo esc_html( $group['label'] ); ?></div>
							<div class="ah-tp-opts">
								<?php foreach ( $group['items'] as $term ) : ?>
									<label class="ah-tp-chip">
										<input type="checkbox" name="taxonomy_ids[]"
											value="<?php echo esc_attr( $term->id ); ?>"
											<?php checked( in_array( (int) $term->id, $sel, true ) ); ?>>
										<span><?php echo esc_html( $term->name ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</div>
						<?php
					}
				}
			}
			?>
		</div>
		<?php
	}

	public static function save_metabox( int $post_id ): void {
		if ( ! isset( $_POST['ah_cms_post_meta_nonce'] ) ) return;
		if ( ! wp_verify_nonce( $_POST['ah_cms_post_meta_nonce'], 'ah_cms_post_meta_save' ) ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

		update_post_meta( $post_id, '_ah_is_featured',  ! empty( $_POST['ah_is_featured'] )  ? '1' : '0' );
		update_post_meta( $post_id, '_ah_is_popular',   ! empty( $_POST['ah_is_popular'] )   ? '1' : '0' );
		update_post_meta( $post_id, '_ah_is_suggested', ! empty( $_POST['ah_is_suggested'] ) ? '1' : '0' );

		if ( class_exists( 'AH_Content_Taxonomy_Model' ) ) {
			$taxonomy_ids = array_map( 'intval', (array) ( $_POST['taxonomy_ids'] ?? array() ) );
			( new \AH_Content_Taxonomy_Model() )->sync_terms( 'wp_post', $post_id, $taxonomy_ids );
		}
	}
}
