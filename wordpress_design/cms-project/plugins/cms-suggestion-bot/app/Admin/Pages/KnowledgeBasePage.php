<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Admin\Pages;

use CmsSuggestionBot\Services\CommonQuestionsService;
use CmsSuggestionBot\Services\KnowledgeService;

defined( 'ABSPATH' ) || exit;

/**
 * Knowledge Base submenu - manual CRUD over cms_sug_bot_knowledge (question,
 * answer, category, keywords, priority). Editing here invalidates the
 * Common Questions cache so changes show up immediately.
 */
final class KnowledgeBasePage {

	public function __construct(
		private readonly KnowledgeService $knowledge,
		private readonly CommonQuestionsService $commonQuestions,
	) {}

	public function render(): void {
		if ( ! current_user_can( CSB_CAPABILITY ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'cms-suggestion-bot' ) );
		}

		$notice = $this->maybeSave();

		$edit_id = isset( $_GET['edit'] ) ? (int) $_GET['edit'] : 0;
		$editing = $edit_id ? $this->knowledge->find( $edit_id ) : null;
		$search  = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$status  = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';

		$entries = match ( true ) {
			'' !== $search      => $this->knowledge->search( $search ),
			'unanswered' === $status => $this->knowledge->unanswered( 50 ),
			default             => $this->knowledge->all( 50 ),
		};
		$unanswered_count = $this->knowledge->countUnanswered();

		include CSB_PLUGIN_DIR . '/templates/admin/knowledge-base.php';
	}

	private function maybeSave(): string {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			return '';
		}

		if ( isset( $_POST['csb_kb_save'] ) ) {
			check_admin_referer( 'csb_save_knowledge' );
			$id = (int) ( $_POST['id'] ?? 0 );

			if ( $id ) {
				$this->knowledge->update( $id, $_POST );
				$message = __( 'Entry updated.', 'cms-suggestion-bot' );
			} else {
				$this->knowledge->create( $_POST );
				$message = __( 'Entry created.', 'cms-suggestion-bot' );
			}

			$this->commonQuestions->invalidate();

			return $message;
		}

		if ( isset( $_POST['csb_kb_delete'] ) ) {
			check_admin_referer( 'csb_delete_knowledge' );
			$this->knowledge->delete( (int) $_POST['id'] );
			$this->commonQuestions->invalidate();

			return __( 'Entry deleted.', 'cms-suggestion-bot' );
		}

		return '';
	}
}
