<?php
defined( 'ABSPATH' ) || exit;

class AH_Contact_Model extends AH_Model_Base {

	protected string $table_suffix = 'contact_form_submissions';

	public function get_paginated( int $page = 1, string $status = '', string $search = '', string $type = '' ): array {
		$where    = array();
		$where_in = array();
		if ( $status ) { $where[] = 'status = %s'; $where_in[] = $status; }
		if ( $type )   { $where[] = 'enquiry_type = %s'; $where_in[] = $type; }
		if ( $search ) {
			$s        = AH_DB_Helper::search_where( array( 'full_name', 'email', 'subject', 'message', 'short_quote' ), $search );
			$where[]  = $s['where'];
			$where_in = array_merge( $where_in, $s['where_in'] );
		}
		$args = array( 'order_by' => 'submitted_at', 'order' => 'DESC' );
		if ( $where ) { $args['where'] = implode( ' AND ', $where ); $args['where_in'] = $where_in; }
		return $this->paginate( $page, $args );
	}

	public function mark_read( int $id ): bool {
		return AH_DB_Helper::update( $this->table(), array( 'is_read' => 1 ), $id );
	}

	public function mark_status( int $id, string $status ): bool {
		return AH_DB_Helper::update( $this->table(), array( 'status' => $status ), $id );
	}

	public function mark_email_sent( int $id ): bool {
		return AH_DB_Helper::update( $this->table(), array(
			'email_sent'    => 1,
			'email_sent_at' => current_time( 'mysql' ),
		), $id );
	}

	public function add_note( int $id, string $note ): bool {
		$sub = $this->find( $id );
		if ( ! $sub ) return false;
		$timestamp    = wp_date( 'M j, Y g:i a' );
		$current_user = wp_get_current_user()->display_name ?: 'Admin';
		$new_entry    = "[{$timestamp} — {$current_user}] " . sanitize_textarea_field( $note );
		$existing     = $sub->admin_notes ?? '';
		$combined     = $new_entry . ( $existing ? "\n\n" . $existing : '' );
		return AH_DB_Helper::update( $this->table(), array( 'admin_notes' => $combined ), $id );
	}

	public function unread_count(): int {
		return $this->count( "is_read = 0 AND status = 'new'" );
	}

	public function get_page_config( int $page_id ): ?object {
		return AH_DB_Helper::get_by( AH_DB_Helper::table( 'contact_page_config' ), 'page_id', $page_id );
	}

	public function save_page_config( int $page_id, array $data ): void {
		$t    = AH_DB_Helper::table( 'contact_page_config' );
		$data = array_merge( $data, array( 'page_id' => $page_id, 'updated_by' => get_current_user_id() ?: null ) );
		$row  = $this->get_page_config( $page_id );
		$row ? AH_DB_Helper::update( $t, $data, (int) $row->id ) : AH_DB_Helper::insert( $t, $data );
	}

	public function submit( array $data ): int|false {
		$allowed_types = array( 'general', 'complaint', 'sales', 'support', 'media', 'other' );
		$enquiry_type  = in_array( $data['enquiry_type'] ?? '', $allowed_types, true ) ? $data['enquiry_type'] : 'general';

		$row = array(
			'full_name'       => sanitize_text_field( $data['full_name'] ),
			'email'           => sanitize_email( $data['email'] ),
			'phone'           => sanitize_text_field( $data['phone'] ?? '' ),
			'subject'         => sanitize_text_field( $data['subject'] ?? '' ),
			'message'         => sanitize_textarea_field( $data['message'] ),
			'enquiry_type'    => $enquiry_type,
			'short_quote'     => sanitize_text_field( $data['short_quote'] ?? '' ),
			'attachment_path' => esc_url_raw( $data['attachment_path'] ?? '' ),
			'attachment_name' => sanitize_file_name( $data['attachment_name'] ?? '' ),
			'email_sent'      => ! empty( $data['email_sent'] ) ? 1 : 0,
			'email_sent_at'   => ! empty( $data['email_sent'] ) ? current_time( 'mysql' ) : null,
			'page_url'        => esc_url_raw( $data['page_url'] ?? '' ),
			'user_agent'      => sanitize_text_field( $data['user_agent'] ?? '' ),
			'ip_address'      => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
		);

		return AH_DB_Helper::insert( $this->table(), $row );
	}
}
