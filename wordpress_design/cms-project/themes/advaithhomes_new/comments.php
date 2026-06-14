<?php
/**
 * comments.php — Custom comments template for advaithhomes_new.
 *
 * Called via comments_template() from single.php.
 * Callback functions MUST be defined before wp_list_comments() is called,
 * so they are placed at the top of this file before any output.
 */

defined( 'ABSPATH' ) || exit;

// ─── Comment render callbacks (defined first — called by wp_list_comments) ───

function adn_comment_callback( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	$tag         = ( 'div' === $args['style'] ) ? 'div' : 'li';
	$is_pingback = in_array( $comment->comment_type, array( 'pingback', 'trackback' ), true );
	?>
	<<?php echo esc_html( $tag ); ?> id="comment-<?php comment_ID(); ?>"
	    class="adn-comment<?php
	    	echo ( $is_pingback             ? ' adn-comment--ping'    : '' );
	    	echo ( $depth > 1               ? ' adn-comment--reply'   : '' );
	    	echo ( ! $comment->comment_approved ? ' adn-comment--pending' : '' );
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
							'max_depth'  => $args['max_depth'],
							'before'     => '<span class="adn-reply-link">',
							'after'      => '</span>',
						) ) );
						edit_comment_link( esc_html__( 'Edit', ADN_TEXT_DOMAIN ), '<span class="adn-edit-link">', '</span>' );
						?>

						<?php if ( current_user_can( 'moderate_comments' ) ) :
							$_cid   = (int) $comment->comment_ID;
							$_nonce = wp_create_nonce( 'adn_moderate_comment' );
							$_approved = (int) $comment->comment_approved; // 1=approved, 0=pending, spam='spam', trash='trash'
						?>
						<span class="adn-mod-actions"
						      data-comment="<?php echo esc_attr( (string) $_cid ); ?>"
						      data-nonce="<?php echo esc_attr( $_nonce ); ?>">
							<?php if ( 1 === $_approved ) : ?>
								<button type="button" class="adn-mod-btn adn-mod-unapprove" data-action="unapprove" title="<?php esc_attr_e( 'Unapprove', ADN_TEXT_DOMAIN ); ?>">⏸ <?php esc_html_e( 'Unapprove', ADN_TEXT_DOMAIN ); ?></button>
							<?php else : ?>
								<button type="button" class="adn-mod-btn adn-mod-approve" data-action="approve" title="<?php esc_attr_e( 'Approve', ADN_TEXT_DOMAIN ); ?>">✓ <?php esc_html_e( 'Approve', ADN_TEXT_DOMAIN ); ?></button>
							<?php endif; ?>
							<button type="button" class="adn-mod-btn adn-mod-spam" data-action="spam" title="<?php esc_attr_e( 'Mark as Spam', ADN_TEXT_DOMAIN ); ?>">⚑ <?php esc_html_e( 'Spam', ADN_TEXT_DOMAIN ); ?></button>
							<button type="button" class="adn-mod-btn adn-mod-trash" data-action="trash" title="<?php esc_attr_e( 'Trash', ADN_TEXT_DOMAIN ); ?>">🗑 <?php esc_html_e( 'Trash', ADN_TEXT_DOMAIN ); ?></button>
						</span>
						<?php endif; ?>

					</div>

				</div>
			</div>

		<?php endif; ?>
	<?php
	// Closing tag handled by adn_comment_end_callback.
}

function adn_comment_end_callback( $comment, $args, $depth ) {
	$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
	echo '</' . esc_html( $tag ) . ">\n";
}

// ─── Template output ─────────────────────────────────────────────────────────

if ( post_password_required() ) {
	echo '<p class="adn-comments-protected">'
		. esc_html__( 'This post is password protected. Enter the password to view comments.', ADN_TEXT_DOMAIN )
		. '</p>';
	return;
}

$comment_count = (int) get_comments_number();
?>

<section class="adn-comments-section" id="comments">

	<?php /* ── Comment list ── */ ?>
	<?php if ( have_comments() ) : ?>

		<h2 class="adn-comments-heading">
			<?php
			if ( 1 === $comment_count ) {
				esc_html_e( '1 Comment', ADN_TEXT_DOMAIN );
			} else {
				printf( esc_html__( '%s Comments', ADN_TEXT_DOMAIN ), number_format_i18n( $comment_count ) );
			}
			?>
		</h2>

		<ol class="adn-comment-list">
			<?php
			wp_list_comments( array(
				'style'        => 'ol',
				'short_ping'   => true,
				'avatar_size'  => 48,
				'callback'     => 'adn_comment_callback',
				'end-callback' => 'adn_comment_end_callback',
			) );
			?>
		</ol>

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
			<nav class="adn-comment-pagination">
				<?php paginate_comments_links( array(
					'prev_text' => '&laquo; ' . esc_html__( 'Older Comments', ADN_TEXT_DOMAIN ),
					'next_text' => esc_html__( 'Newer Comments', ADN_TEXT_DOMAIN ) . ' &raquo;',
				) ); ?>
			</nav>
		<?php endif; ?>

	<?php elseif ( ! comments_open() ) : ?>

		<p class="adn-comments-closed">
			<?php esc_html_e( 'Comments are closed.', ADN_TEXT_DOMAIN ); ?>
		</p>

	<?php endif; ?>

	<?php /* ── Comment form ── */ ?>
	<?php
	$commenter = wp_get_current_commenter();
	$user      = wp_get_current_user();
	$user_id   = get_current_user_id();
	$req       = get_option( 'require_name_email' );
	$req_mark  = $req ? ' <span class="adn-required">*</span>' : '';

	$fields = array(
		'author' => '<div class="adn-form-row">'
			. '<label for="author">' . esc_html__( 'Name', ADN_TEXT_DOMAIN ) . $req_mark . '</label>'
			. '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" placeholder="' . esc_attr__( 'Your name', ADN_TEXT_DOMAIN ) . '" autocomplete="name"' . ( $req ? ' aria-required="true"' : '' ) . '>'
			. '</div>',
		'email'  => '<div class="adn-form-row">'
			. '<label for="email">' . esc_html__( 'Email', ADN_TEXT_DOMAIN ) . $req_mark . '</label>'
			. '<input id="email" name="email" type="email" value="' . esc_attr( $commenter['comment_author_email'] ) . '" placeholder="' . esc_attr__( 'your@email.com', ADN_TEXT_DOMAIN ) . '" autocomplete="email"' . ( $req ? ' aria-required="true"' : '' ) . '>'
			. '</div>',
		'url'    => '<div class="adn-form-row">'
			. '<label for="url">' . esc_html__( 'Website (optional)', ADN_TEXT_DOMAIN ) . '</label>'
			. '<input id="url" name="url" type="url" value="' . esc_attr( $commenter['comment_author_url'] ) . '" placeholder="' . esc_attr__( 'https://yoursite.com', ADN_TEXT_DOMAIN ) . '" autocomplete="url">'
			. '</div>',
	);

	comment_form( array(
		'title_reply'          => esc_html__( 'Leave a Comment', ADN_TEXT_DOMAIN ),
		'title_reply_to'       => esc_html__( 'Reply to %s', ADN_TEXT_DOMAIN ),
		'title_reply_before'   => '<h2 id="reply-title" class="adn-form-heading">',
		'title_reply_after'    => '</h2>',
		'cancel_reply_before'  => ' <span class="adn-cancel-reply">',
		'cancel_reply_after'   => '</span>',
		'cancel_reply_link'    => esc_html__( 'Cancel reply', ADN_TEXT_DOMAIN ),
		'label_submit'         => esc_html__( 'Post Comment', ADN_TEXT_DOMAIN ),
		'submit_button'        => '<button name="%1$s" type="submit" id="%2$s" class="%3$s btn btn-primary">%4$s</button>',
		'submit_field'         => '<div class="adn-form-submit">%1$s %2$s</div>',
		'comment_field'        => '<div class="adn-form-row adn-form-row--full">'
			. '<label for="comment">' . esc_html__( 'Comment', ADN_TEXT_DOMAIN ) . ' <span class="adn-required">*</span></label>'
			. '<textarea id="comment" name="comment" rows="5" placeholder="' . esc_attr__( 'Share your thoughts…', ADN_TEXT_DOMAIN ) . '" required></textarea>'
			. '</div>',
		'must_log_in'          => '<p class="adn-must-login">' . sprintf(
			esc_html__( 'You must be %s to post a comment.', ADN_TEXT_DOMAIN ),
			'<a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">' . esc_html__( 'logged in', ADN_TEXT_DOMAIN ) . '</a>'
		) . '</p>',
		'logged_in_as'         => $user_id ? '<p class="adn-logged-in">' . sprintf(
			esc_html__( 'Logged in as %1$s. %2$s', ADN_TEXT_DOMAIN ),
			'<strong>' . esc_html( $user->display_name ) . '</strong>',
			'<a href="' . esc_url( wp_logout_url( get_permalink() ) ) . '">' . esc_html__( 'Log out?', ADN_TEXT_DOMAIN ) . '</a>'
		) . '</p>' : '',
		'comment_notes_before' => '<p class="adn-comment-note">'
			. esc_html__( 'Your email address will not be published.', ADN_TEXT_DOMAIN )
			. ( $req ? ' ' . esc_html__( 'Required fields are marked', ADN_TEXT_DOMAIN ) . ' <span class="adn-required">*</span>' : '' )
			. '</p>',
		'comment_notes_after'  => '',
		'fields'               => $fields,
		'class_form'           => 'adn-comment-form',
		'class_container'      => 'adn-comment-form-wrap',
		'id_form'              => 'adn-comment-form',
	) );
	?>

</section>

<?php if ( current_user_can( 'moderate_comments' ) ) : ?>
<script>
(function(){
'use strict';
var AJAX = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';

document.querySelectorAll('.adn-mod-actions').forEach(function(wrap){
    wrap.addEventListener('click', function(e){
        var btn = e.target.closest('.adn-mod-btn');
        if (!btn) return;
        var action  = btn.dataset.action;
        var cid     = wrap.dataset.comment;
        var nonce   = wrap.dataset.nonce;
        var comment = document.getElementById('comment-' + cid);
        btn.disabled = true;

        var fd = new FormData();
        fd.append('action',     'adn_moderate_comment');
        fd.append('nonce',      nonce);
        fd.append('comment_id', cid);
        fd.append('mod_action', action);

        fetch(AJAX, { method:'POST', body:fd })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (!d.success) { btn.disabled = false; return; }
                if (action === 'trash' || action === 'spam') {
                    // Fade and remove the comment card
                    if (comment) {
                        comment.style.transition = 'opacity .4s';
                        comment.style.opacity    = '0';
                        setTimeout(function(){ comment.remove(); }, 420);
                    }
                } else if (action === 'approve') {
                    if (comment) {
                        comment.classList.remove('adn-comment--pending');
                        comment.querySelector('.adn-comment-pending') && comment.querySelector('.adn-comment-pending').remove();
                        // Swap button to Unapprove
                        btn.dataset.action = 'unapprove';
                        btn.className = 'adn-mod-btn adn-mod-unapprove';
                        btn.textContent = '⏸ Unapprove';
                    }
                } else if (action === 'unapprove') {
                    if (comment) {
                        comment.classList.add('adn-comment--pending');
                        // Swap button to Approve
                        btn.dataset.action = 'approve';
                        btn.className = 'adn-mod-btn adn-mod-approve';
                        btn.textContent = '✓ Approve';
                    }
                }
                btn.disabled = false;
            })
            .catch(function(){ btn.disabled = false; });
    });
});
}());
</script>
<?php endif; ?>
