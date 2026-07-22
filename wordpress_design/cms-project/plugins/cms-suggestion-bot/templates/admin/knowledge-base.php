<?php
/**
 * templates/admin/knowledge-base.php
 *
 * @var array<int, array<string, mixed>> $entries
 * @var array<string, mixed>|null        $editing
 * @var string                           $search
 * @var string                           $status
 * @var int                              $unanswered_count
 * @var string                           $notice
 */

use CmsSuggestionBot\Admin\View;

defined( 'ABSPATH' ) || exit;

$base_url = admin_url( 'admin.php?page=' . CSB_MENU_SLUG . '-knowledge-base' );
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Knowledge Base', 'cms-suggestion-bot' ); ?></h1>

	<?php View::notice( $notice ); ?>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo esc_url( $base_url ); ?>" class="nav-tab<?php echo '' === $status ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'All', 'cms-suggestion-bot' ); ?></a>
		<a href="<?php echo esc_url( add_query_arg( 'status', 'unanswered', $base_url ) ); ?>" class="nav-tab<?php echo 'unanswered' === $status ? ' nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Unanswered', 'cms-suggestion-bot' ); ?>
			<?php if ( $unanswered_count > 0 ) : ?><span class="update-plugins count-<?php echo esc_attr( (string) $unanswered_count ); ?>"><span class="update-count"><?php echo esc_html( (string) $unanswered_count ); ?></span></span><?php endif; ?>
		</a>
	</h2>

	<div class="csb-grid-2col">
		<div>
			<form method="get" class="csb-mb-12">
				<input type="hidden" name="page" value="<?php echo esc_attr( CSB_MENU_SLUG . '-knowledge-base' ); ?>">
				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search questions…', 'cms-suggestion-bot' ); ?>">
				<button class="button"><?php esc_html_e( 'Search', 'cms-suggestion-bot' ); ?></button>
			</form>

			<?php
			View::table(
				array( __( 'Question', 'cms-suggestion-bot' ), __( 'Category', 'cms-suggestion-bot' ), __( 'Priority', 'cms-suggestion-bot' ), __( 'Used', 'cms-suggestion-bot' ), __( 'Actions', 'cms-suggestion-bot' ) ),
				array_map(
					static function ( array $entry ) {
						$edit_url   = add_query_arg( array( 'page' => CSB_MENU_SLUG . '-knowledge-base', 'edit' => $entry['id'] ) );
						$delete_url = wp_nonce_url( add_query_arg( array( 'page' => CSB_MENU_SLUG . '-knowledge-base' ) ), 'csb_delete_knowledge' );
						return array(
							esc_html( wp_trim_words( (string) $entry['question'], 12 ) ),
							esc_html( (string) $entry['category'] ),
							esc_html( (string) $entry['priority'] ),
							esc_html( (string) $entry['usage_count'] ),
							'<a href="' . esc_url( $edit_url ) . '" class="button button-small">' . esc_html__( 'Edit', 'cms-suggestion-bot' ) . '</a> '
							. '<form method="post" class="csb-form-inline" onsubmit="return confirm(\'' . esc_js( __( 'Delete this entry?', 'cms-suggestion-bot' ) ) . '\');">'
							. wp_nonce_field( 'csb_delete_knowledge', '_wpnonce', true, false )
							. '<input type="hidden" name="id" value="' . esc_attr( (string) $entry['id'] ) . '">'
							. '<button type="submit" name="csb_kb_delete" value="1" class="button button-small button-link-delete">' . esc_html__( 'Delete', 'cms-suggestion-bot' ) . '</button></form>',
						);
					},
					$entries
				)
			);
			?>
		</div>

		<div class="card">
			<h2 class="title"><?php echo $editing ? esc_html__( 'Edit Entry', 'cms-suggestion-bot' ) : esc_html__( 'Add Entry', 'cms-suggestion-bot' ); ?></h2>
			<form method="post">
				<?php wp_nonce_field( 'csb_save_knowledge' ); ?>
				<input type="hidden" name="id" value="<?php echo esc_attr( (string) ( $editing['id'] ?? 0 ) ); ?>">

				<p><label><?php esc_html_e( 'Question', 'cms-suggestion-bot' ); ?><br>
					<textarea name="question" class="large-text" rows="2" required><?php echo esc_textarea( (string) ( $editing['question'] ?? '' ) ); ?></textarea>
				</label></p>

				<p><label><?php esc_html_e( 'Answer', 'cms-suggestion-bot' ); ?><br>
					<textarea name="answer" class="large-text" rows="4" required><?php echo esc_textarea( (string) ( $editing['answer'] ?? '' ) ); ?></textarea>
				</label></p>

				<p><label><?php esc_html_e( 'Category', 'cms-suggestion-bot' ); ?><br>
					<input type="text" class="regular-text" name="category" value="<?php echo esc_attr( (string) ( $editing['category'] ?? '' ) ); ?>">
				</label></p>

				<p><label><?php esc_html_e( 'Keywords (comma-separated)', 'cms-suggestion-bot' ); ?><br>
					<input type="text" class="regular-text" name="keywords" value="<?php echo esc_attr( (string) ( $editing['keywords'] ?? '' ) ); ?>">
				</label></p>

				<p><label><?php esc_html_e( 'Priority', 'cms-suggestion-bot' ); ?><br>
					<input type="number" name="priority" value="<?php echo esc_attr( (string) ( $editing['priority'] ?? 0 ) ); ?>">
				</label></p>

				<p>
					<button type="submit" name="csb_kb_save" value="1" class="button button-primary"><?php esc_html_e( 'Save Entry', 'cms-suggestion-bot' ); ?></button>
					<?php if ( $editing ) : ?>
						<a href="<?php echo esc_url( add_query_arg( array( 'page' => CSB_MENU_SLUG . '-knowledge-base' ) ) ); ?>" class="button"><?php esc_html_e( 'Cancel', 'cms-suggestion-bot' ); ?></a>
					<?php endif; ?>
				</p>
			</form>
		</div>
	</div>
</div>
