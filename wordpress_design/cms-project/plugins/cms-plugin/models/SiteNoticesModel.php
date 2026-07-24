<?php
defined( 'ABSPATH' ) || exit;

class AH_Site_Notices_Model extends AH_Model_Base {

	protected string $table_suffix = 'site_notices';

	public function get_active(): array {
		return $this->all( array(
			'where'    => "status = 'active'",
			'order_by' => 'sort_order',
			'order'    => 'ASC',
		) );
	}

	public function get_paginated_list( int $page = 1, string $search = '', string $status = '' ): array {
		global $wpdb;
		$where_clauses = array();
		$where_values  = array();

		if ( $search ) {
			$search_result = AH_DB_Helper::search_where( array( 'title', 'message' ), $search );
			$where_clauses[] = $search_result['where'];
			$where_values   = array_merge( $where_values, $search_result['where_in'] );
		}
		if ( $status ) {
			$where_clauses[] = 'status = %s';
			$where_values[]  = $status;
		}

		$where = ! empty( $where_clauses ) ? implode( ' AND ', $where_clauses ) : '';

		return $this->paginate( $page, array(
			'where'    => $where,
			'where_in' => $where_values,
			'order_by' => 'sort_order',
			'order'    => 'ASC',
		) );
	}

	public function save_notice( int $id, array $data ): int {
		$valid_triggers = array( 'immediate', 'exit-intent', 'delay', 'scroll' );
		$valid_freq     = array( 'session', 'daily', 'weekly', 'once_ever', 'always', 'custom' );

		$show_from  = sanitize_text_field( $data['show_from']  ?? '' );
		$show_until = sanitize_text_field( $data['show_until'] ?? '' );

		$clean = array(
			'title'          => sanitize_text_field( $data['title']        ?? '' ),
			'message'        => sanitize_textarea_field( $data['message']  ?? '' ),
			'image'          => esc_url_raw( $data['image']                ?? '' ) ?: null,
			'button_label'   => sanitize_text_field( $data['button_label'] ?? '' ) ?: null,
			'button_url'     => sanitize_text_field( $data['button_url']   ?? '' ) ?: null,
			'badge_text'     => sanitize_text_field( $data['badge_text']   ?? '' ) ?: null,
			'badge_color'    => sanitize_hex_color( $data['badge_color'] ?? '' ) ?: '#15803d',
			'position'       => ( $data['position'] ?? 'modal' ) === 'corner' ? 'corner' : 'modal',
			'trigger_type'   => in_array( $data['trigger_type'] ?? '', $valid_triggers, true ) ? $data['trigger_type'] : 'immediate',
			'trigger_delay'  => max( 0, (int) ( $data['trigger_delay']  ?? 0 ) ),
			'trigger_scroll' => min( 100, max( 0, (int) ( $data['trigger_scroll'] ?? 0 ) ) ),
			'scope'          => ( $data['scope'] ?? '' ) === 'slugs' ? 'slugs' : 'all',
			'slugs'          => sanitize_text_field( $data['slugs'] ?? '' ) ?: null,
			'frequency'             => in_array( $data['frequency'] ?? '', $valid_freq, true ) ? $data['frequency'] : 'daily',
			'frequency_custom_mins' => max( 1, (int) ( $data['frequency_custom_mins'] ?? 60 ) ),
			'device'         => in_array( $data['device'] ?? '', array( 'all', 'mobile', 'desktop' ), true ) ? $data['device'] : 'all',
			'show_from'      => ( $show_from  && strtotime( $show_from )  ) ? $show_from  : null,
			'show_until'     => ( $show_until && strtotime( $show_until ) ) ? $show_until : null,
			'auto_close'     => max( 0, (int) ( $data['auto_close'] ?? 0 ) ),
			'status'         => ( $data['status'] ?? 'active' ) === 'inactive' ? 'inactive' : 'active',
			'sort_order'     => (int) ( $data['sort_order'] ?? 0 ),
		);

		if ( $id > 0 ) {
			$this->update( $id, $clean );
			return $id;
		}
		return (int) $this->create( $clean );
	}
}
