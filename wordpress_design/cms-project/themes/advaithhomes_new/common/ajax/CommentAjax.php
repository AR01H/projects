<?php
/**
 * Comment AJAX Handlers
 *
 * Handles comment moderation, submission, and load-more pagination.
 *
 * @package Adn\Theme\Common\Ajax
 */
defined( 'ABSPATH' ) || exit;

/**
 * Inline comment moderation (admin only)
 */
function adn_moderate_comment_ajax() {
	check_ajax_referer( 'adn_moderate_comment', 'nonce' );

	if ( ! current_user_can( 'moderate_comments' ) ) {
		wp_send_json_error( array( 'message' => 'Permission denied' ), 403 );
	}

	$comment_id = isset( $_POST['comment_id'] ) ? (int) $_POST['comment_id'] : 0;
	$mod_action = isset( $_POST['mod_action'] ) ? sanitize_key( (string) $_POST['mod_action'] ) : '';

	if ( ! $comment_id || ! in_array( $mod_action, array( 'approve', 'unapprove', 'spam', 'trash' ), true ) ) {
		wp_send_json_error( array( 'message' => 'Invalid request' ), 400 );
	}

	if ( ! get_comment( $comment_id ) ) {
		wp_send_json_error( array( 'message' => 'Comment not found' ), 404 );
	}

	$ok = false;
	switch ( $mod_action ) {
		case 'approve':   $ok = wp_set_comment_status( $comment_id, 'approve' ); break;
		case 'unapprove': $ok = wp_set_comment_status( $comment_id, 'hold' );    break;
		case 'spam':      $ok = wp_spam_comment( $comment_id );                  break;
		case 'trash':     $ok = wp_trash_comment( $comment_id );                 break;
	}

	if ( $ok ) {
		wp_send_json_success( array( 'action' => $mod_action ) );
	} else {
		wp_send_json_error( array( 'message' => 'Action failed' ) );
	}
}

/**
 * AJAX comment submission (replaces wp-comments-post.php redirect)
 */
function adn_ajax_submit_comment() {
	if ( ! check_ajax_referer( 'adn_comment_nonce', 'adn_nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh and try again.', ADN_TEXT_DOMAIN ) ), 403 );
	}

	add_filter( 'pre_option_require_name_email', '__return_zero' );
	$comment = wp_handle_comment_submission( wp_unslash( $_POST ) );
	remove_filter( 'pre_option_require_name_email', '__return_zero' );

	if ( is_wp_error( $comment ) ) {
		wp_send_json_error( array( 'message' => $comment->get_error_message() ) );
	}

	$_args = array(
		'style'     => 'ol',
		'max_depth' => (int) get_option( 'thread_comments_depth', 5 ),
	);
	ob_start();
	adn_comment_callback( $comment, $_args, 1 );
	adn_comment_end_callback( $comment, $_args, 1 );
	$html = ob_get_clean();

	wp_send_json_success( array(
		'html'     => $html,
		'approved' => (bool) $comment->comment_approved,
		'message'  => $comment->comment_approved
			? __( 'Your comment has been posted.', ADN_TEXT_DOMAIN )
			: __( 'Your comment is awaiting moderation. Thank you.', ADN_TEXT_DOMAIN ),
	) );
}

/**
 * AJAX load-more comments pagination
 */
function adn_ajax_load_comments() {
	check_ajax_referer( 'adn_load_comments', 'nonce' );

	$post_id  = isset( $_POST['post_id'] ) ? (int) $_POST['post_id']       : 0;
	$page     = isset( $_POST['page'] )    ? max( 1, (int) $_POST['page'] ) : 1;
	$per_page = 10;

	$order = ( isset( $_POST['order'] ) && 'asc' === (string) $_POST['order'] ) ? 'ASC' : 'DESC';

	$allowed_statuses = array( 'approve' );
	if ( current_user_can( 'moderate_comments' ) ) {
		$allowed_statuses[] = 'hold';
	}
	$req_status = isset( $_POST['status'] ) ? sanitize_key( (string) $_POST['status'] ) : 'approve';
	$status     = in_array( $req_status, $allowed_statuses, true ) ? $req_status : 'approve';

	if ( ! $post_id || ! get_post( $post_id ) ) {
		wp_send_json_error( array( 'message' => 'Invalid post.' ), 400 );
	}

	$total       = (int) get_comments( array(
		'post_id' => $post_id,
		'status'  => $status,
		'count'   => true,
		'type'    => 'comment',
	) );
	$total_pages = $total > 0 ? (int) ceil( $total / $per_page ) : 1;
	$offset      = ( $page - 1 ) * $per_page;

	$comments = get_comments( array(
		'post_id' => $post_id,
		'status'  => $status,
		'number'  => $per_page,
		'offset'  => $offset,
		'orderby' => 'comment_date_gmt',
		'order'   => $order,
		'type'    => 'comment',
	) );

	if ( empty( $comments ) ) {
		wp_send_json_success( array( 'html' => '', 'total' => 0, 'page' => 1, 'total_pages' => 1 ) );
	}

	$GLOBALS['post'] = get_post( $post_id );
	setup_postdata( $GLOBALS['post'] );

	$_args = array(
		'style'     => 'ol',
		'max_depth' => (int) get_option( 'thread_comments_depth', 5 ),
	);

	ob_start();
	foreach ( $comments as $_c ) {
		adn_comment_callback( $_c, $_args, 1 );
		adn_comment_end_callback( $_c, $_args, 1 );
	}
	$html = ob_get_clean();
	wp_reset_postdata();

	wp_send_json_success( array(
		'html'        => $html,
		'total'       => $total,
		'page'        => $page,
		'total_pages' => $total_pages,
	) );
}
