<?php

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<div id="comments" class="comments-area vintage-comments frame--rough-cut">
	<?php if ( have_comments() ) : ?>
		<h3 class="comments-title">
			✦ <?php comments_number( esc_html__( 'BE THE FIRST TO COMMENT', 'vintagesoul' ), esc_html__( '1 CHRONICLE RESPONSE', 'vintagesoul' ), esc_html__( '% CHRONICLE RESPONSES', 'vintagesoul' ) ); ?> ✦
		</h3>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 48,
				)
			);
			?>
		</ol>

		<?php
		the_comments_pagination(
			array(
				'prev_text' => '← ' . esc_html__( 'Older Thoughts', 'vintagesoul' ),
				'next_text' => esc_html__( 'Newer Thoughts', 'vintagesoul' ) . ' →',
			)
		);
		?>
	<?php endif; ?>

	<?php if ( comments_open() ) : ?>
		<div class="comment-form-wrap">
			<?php
			comment_form(
				array(
					'title_reply'          => esc_html__( 'LEAVE A THOUGHT', 'vintagesoul' ),
					'title_reply_to'       => esc_html__( 'REPLY TO %s', 'vintagesoul' ),
					'cancel_reply_link'    => esc_html__( 'Cancel', 'vintagesoul' ),
					'label_submit'         => esc_html__( 'POST COMMENT ✦', 'vintagesoul' ),
					'class_submit'         => 'btn btn--primary-vintage comment-submit-btn',
					'comment_notes_before' => '',
					'comment_notes_after'  => '',
				)
			);
			?>
		</div>
	<?php elseif ( get_comments_number() ) : ?>
		<p class="comments-closed"><?php esc_html_e( 'Comments are closed.', 'vintagesoul' ); ?></p>
	<?php endif; ?>
</div>
