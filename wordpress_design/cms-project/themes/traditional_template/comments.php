<?php
/**
 * comments.php - the discussion under an article.
 *
 * WordPress warns that a theme without this file is deprecated, and until now
 * this theme had none: comments_template() fell back to core's default markup,
 * which is unstyled here and printed a bare heading in the middle of the
 * vintage page.
 *
 * Kept deliberately quiet. A comment thread on a small site is a footnote to
 * the article, not a second article - so it sits in a narrow column, the
 * heading is the same small-caps kicker used everywhere else, and the form
 * only opens up when there is something to say. Every label comes from
 * admin/data/ui.json via NT_Ui::label(), like the rest of the theme.
 */

defined( 'ABSPATH' ) || exit;

// A password-protected post must not leak its discussion.
if ( post_password_required() ) {
	return;
}

$nt_count = (int) get_comments_number();
?>
<section class="app-comments" id="comments">
	<div class="app-comments__wrap">

		<?php if ( $nt_count > 0 ) : ?>
			<h2 class="app-comments__title">
				<?php NT_Icons::render( 'chat' ); ?>
				<?php
				printf(
					/* translators: %s: number of comments. */
					esc_html( _n( '%s response', '%s responses', $nt_count, NT_TEXT_DOMAIN ) ),
					esc_html( number_format_i18n( $nt_count ) )
				);
				?>
			</h2>

			<ol class="app-comments__list">
				<?php
				wp_list_comments( array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 46,
					'reply_text'  => esc_html__( 'Reply', NT_TEXT_DOMAIN ),
				) );
				?>
			</ol>

			<?php
			// Only printed when the thread is actually paginated.
			the_comments_pagination( array(
				'class'     => 'app-pagination',
				'prev_text' => esc_html( NT_Ui::label( 'previous' ) ),
				'next_text' => esc_html( NT_Ui::label( 'next' ) ),
			) );
			?>
		<?php endif; ?>

		<?php
		if ( comments_open() ) {

			comment_form( array(
				'class_form'         => 'app-form app-comments__form',
				'title_reply'        => esc_html__( 'Leave a note', NT_TEXT_DOMAIN ),
				'title_reply_to'     => esc_html__( 'Reply to %s', NT_TEXT_DOMAIN ),
				'cancel_reply_link'  => esc_html( NT_Ui::label( 'cancel' ) ),
				'label_submit'       => esc_html( NT_Ui::label( 'submit' ) ),
				'class_submit'       => 'app-btn app-form-submit',
				'title_reply_before' => '<h2 class="app-comments__title app-comments__title--form">',
				'title_reply_after'  => '</h2>',
				'comment_field'      => sprintf(
					'<p class="app-form-group app-form-row"><label class="app-form-label" for="comment">%1$s</label>'
					. '<textarea class="app-form-textarea" id="comment" name="comment" rows="5" required></textarea></p>',
					esc_html__( 'Your note', NT_TEXT_DOMAIN )
				),
			) );

		} elseif ( $nt_count > 0 ) {

			// Closed, but there is a thread worth showing - say why it is closed
			// rather than leaving a form-shaped hole.
			app_alert( array(
				'tone'    => 'note',
				'body'    => __( 'Comments are closed on this piece.', NT_TEXT_DOMAIN ),
				'compact' => true,
			) );
		}
		?>
	</div>
</section>
