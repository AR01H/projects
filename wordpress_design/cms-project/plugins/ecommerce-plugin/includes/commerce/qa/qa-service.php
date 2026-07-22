<?php
namespace AHEcommerce\Commerce\QA;

use AHEcommerce\Commerce\Notifications\Email_Service;

/**
 * Product Q&A — customers ask questions, admin or other customers answer.
 */
class QA_Service {

	/**
	 * Submit a question.
	 *
	 * @return int|false Question ID or false.
	 */
	public static function ask( $product_id, $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_product_questions';

		$wpdb->insert( $table, array(
			'product_id'     => (int) $product_id,
			'questioner_name'  => sanitize_text_field( $data['name'] ),
			'questioner_email' => sanitize_email( $data['email'] ),
			'question'       => wp_kses_post( $data['question'] ),
			'status'         => 'pending',
			'created_at'     => current_time( 'mysql' ),
		) );

		$question_id = $wpdb->insert_id;

		// Notify admin of new question.
		if ( $question_id ) {
			$product_name = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT title FROM {$wpdb->prefix}ah_ecommerce_products WHERE id = %d",
					$product_id
				)
			);
			$to      = get_option( 'admin_email' );
			$subject = sprintf( 'New Question on: %s', $product_name );
			$body    = '<p><strong>' . esc_html( $data['name'] ) . '</strong> asked:</p>';
			$body   .= '<blockquote style="border-left:3px solid #ccc;padding-left:10px;">' . wp_kses_post( $data['question'] ) . '</blockquote>';
			$body   .= '<p><a href="' . esc_url( admin_url( 'admin.php?page=ah-products&action=edit&id=' . $product_id ) ) . '">Answer in Admin →</a></p>';
			wp_mail( $to, $subject, $body, array( 'Content-Type: text/html; charset=UTF-8' ) );
		}

		return $question_id;
	}

	/**
	 * Answer a question (admin or other customers).
	 *
	 * @return bool
	 */
	public static function answer( $question_id, $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_product_answers';

		$wpdb->insert( $table, array(
			'question_id'   => (int) $question_id,
			'answerer_name' => sanitize_text_field( $data['name'] ),
			'answerer_email' => sanitize_email( $data['email'] ?? '' ),
			'answer'        => wp_kses_post( $data['answer'] ),
			'is_admin'      => ! empty( $data['is_admin'] ) ? 1 : 0,
			'status'        => 'approved',
			'created_at'    => current_time( 'mysql' ),
		) );

		$answer_id = $wpdb->insert_id;

		if ( $answer_id ) {
			// Approve the original question too.
			$wpdb->update(
				$wpdb->prefix . 'ah_ecommerce_product_questions',
				array( 'status' => 'approved' ),
				array( 'id' => $question_id )
			);

			// Notify questioner of the answer.
			$question = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}ah_ecommerce_product_questions WHERE id = %d",
					$question_id
				)
			);
			if ( $question && $question->questioner_email ) {
				$subject = 'Your question has been answered!';
				$body    = '<p>Your question about <strong>Product #' . $question->product_id . '</strong> has been answered:</p>';
				$body   .= '<p><strong>Answer:</strong> ' . wp_kses_post( $data['answer'] ) . '</p>';
				wp_mail( $question->questioner_email, $subject, $body, array( 'Content-Type: text/html; charset=UTF-8' ) );
			}
		}

		return $answer_id > 0;
	}

	/**
	 * Get all questions for a product (with answers).
	 *
	 * @return array
	 */
	public static function get_questions( $product_id, $page = 1, $per_page = 20 ) {
		global $wpdb;
		$q_table = $wpdb->prefix . 'ah_ecommerce_product_questions';
		$a_table = $wpdb->prefix . 'ah_ecommerce_product_answers';
		$offset  = ( $page - 1 ) * $per_page;

		$questions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$q_table}
				WHERE product_id = %d AND status = 'approved'
				ORDER BY created_at DESC
				LIMIT %d OFFSET %d",
				$product_id,
				$per_page,
				$offset
			)
		);

		$total = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$q_table} WHERE product_id = %d AND status = 'approved'",
				$product_id
			)
		);

		// Attach answers to each question.
		foreach ( $questions as &$q ) {
			$q->answers = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$a_table} WHERE question_id = %d AND status = 'approved' ORDER BY created_at ASC",
					$q->id
				)
			);
		}

		return array(
			'items' => $questions,
			'meta'  => array(
				'current_page' => $page,
				'per_page'     => $per_page,
				'total_items'  => $total,
				'total_pages'  => ceil( $total / $per_page ),
			),
		);
	}

	/**
	 * Get pending questions (admin moderation).
	 */
	public static function get_pending( $page = 1, $per_page = 20 ) {
		global $wpdb;
		$q_table = $wpdb->prefix . 'ah_ecommerce_product_questions';
		$offset  = ( $page - 1 ) * $per_page;

		$items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT q.*, p.title AS product_name
				FROM {$q_table} q
				LEFT JOIN {$wpdb->prefix}ah_ecommerce_products p ON q.product_id = p.id
				WHERE q.status = 'pending'
				ORDER BY q.created_at DESC
				LIMIT %d OFFSET %d",
				$per_page,
				$offset
			)
		);

		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$q_table} WHERE status = 'pending'" );

		return array(
			'items' => $items,
			'meta'  => array(
				'current_page' => $page,
				'per_page'     => $per_page,
				'total_items'  => $total,
				'total_pages'  => ceil( $total / $per_page ),
			),
		);
	}

	/**
	 * Approve / reject / delete.
	 */
	public static function approve_question( $id ) {
		global $wpdb;
		return $wpdb->update( $wpdb->prefix . 'ah_ecommerce_product_questions', array( 'status' => 'approved' ), array( 'id' => $id ) );
	}

	public static function reject_question( $id ) {
		global $wpdb;
		return $wpdb->update( $wpdb->prefix . 'ah_ecommerce_product_questions', array( 'status' => 'rejected' ), array( 'id' => $id ) );
	}

	public static function delete_question( $id ) {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'ah_ecommerce_product_answers', array( 'question_id' => $id ) );
		return $wpdb->delete( $wpdb->prefix . 'ah_ecommerce_product_questions', array( 'id' => $id ) );
	}
}
