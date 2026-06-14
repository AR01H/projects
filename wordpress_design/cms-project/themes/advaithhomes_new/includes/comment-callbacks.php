<?php
/**
 * includes/comment-callbacks.php
 *
 * Comment render callbacks shared between comments.php (template) and the
 * AJAX handlers (adn_submit_comment, adn_load_comments). Defined here so
 * they are available during AJAX requests without including the full template.
 */

defined( 'ABSPATH' ) || exit;

if ( function_exists( 'adn_comment_callback' ) ) {
	return;
}

function adn_comment_callback( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	$tag         = ( isset( $args['style'] ) && 'div' === $args['style'] ) ? 'div' : 'li';
	$is_pingback = in_array( $comment->comment_type, array( 'pingback', 'trackback' ), true );
	?>
	<<?php echo esc_html( $tag ); ?> id="comment-<?php comment_ID(); ?>"
	    class="adn-comment<?php
	    	echo ( $is_pingback                  ? ' adn-comment--ping'    : '' );
	    	echo ( $depth > 1                    ? ' adn-comment--reply'   : '' );
	    	echo ( ! $comment->comment_approved  ? ' adn-comment--pending' : '' );
	    ?>">

		<?php if ( $is_pingback ) : ?>

			<div class="adn-comment-ping">
				<?php esc_html_e( 'Pingback:', ADN_TEXT_DOMAIN ); ?>
				<a href="<?php comment_author_url(); ?>"><?php comment_author(); ?></a>
			</div>

		<?php else : ?>

			<div class="adn-comment-body">

				<div class="adn-comment-avatar">
					<?php echo get_avatar( $comment, 48, '', '', array( 'class' => 'adn-avatar' ) ); ?>
				</div>

				<div class="adn-comment-content">

					<div class="adn-comment-meta">
						<span class="adn-comment-author"><?php comment_author(); ?></span>
						<time class="adn-comment-date" datetime="<?php comment_date( 'Y-m-d' ); ?>">
							<?php comment_date( 'M j, Y' ); ?>
						</time>
						<?php if ( ! $comment->comment_approved ) : ?>
							<span class="adn-comment-pending">
								<?php esc_html_e( 'Awaiting moderation', ADN_TEXT_DOMAIN ); ?>
							</span>
						<?php endif; ?>
					</div>

					<div class="adn-comment-text">
						<?php comment_text(); ?>
					</div>

					<div class="adn-comment-actions">
						<?php
						comment_reply_link( array_merge( $args, array(
							'reply_text' => esc_html__( 'Reply', ADN_TEXT_DOMAIN ),
							'depth'      => $depth,
							'max_depth'  => isset( $args['max_depth'] ) ? $args['max_depth'] : 5,
							'before'     => '<span class="adn-reply-link">',
							'after'      => '</span>',
						) ) );
						edit_comment_link( esc_html__( 'Edit', ADN_TEXT_DOMAIN ), '<span class="adn-edit-link">', '</span>' );
						?>

						<?php if ( current_user_can( 'moderate_comments' ) ) :
							$_cid      = (int) $comment->comment_ID;
							$_nonce    = wp_create_nonce( 'adn_moderate_comment' );
							$_approved = (int) $comment->comment_approved;
						?>
						<span class="adn-mod-actions"
						      data-comment="<?php echo esc_attr( (string) $_cid ); ?>"
						      data-nonce="<?php echo esc_attr( $_nonce ); ?>">
							<?php if ( 1 === $_approved ) : ?>
								<button type="button" class="adn-mod-btn adn-mod-unapprove" data-action="unapprove">⏸ <?php esc_html_e( 'Unapprove', ADN_TEXT_DOMAIN ); ?></button>
							<?php else : ?>
								<button type="button" class="adn-mod-btn adn-mod-approve" data-action="approve">✓ <?php esc_html_e( 'Approve', ADN_TEXT_DOMAIN ); ?></button>
							<?php endif; ?>
							<button type="button" class="adn-mod-btn adn-mod-spam" data-action="spam">⚑ <?php esc_html_e( 'Spam', ADN_TEXT_DOMAIN ); ?></button>
							<button type="button" class="adn-mod-btn adn-mod-trash" data-action="trash">🗑 <?php esc_html_e( 'Trash', ADN_TEXT_DOMAIN ); ?></button>
						</span>
						<?php endif; ?>

					</div>

				</div>
			</div>

		<?php endif; ?>
	<?php
}

function adn_comment_end_callback( $comment, $args, $depth ) {
	$tag = ( isset( $args['style'] ) && 'div' === $args['style'] ) ? 'div' : 'li';
	echo '</' . esc_html( $tag ) . ">\n";
}
