<?php
defined( 'ABSPATH' ) || exit;

class AH_Pagination {

	/**
	 * Render WordPress-style pagination links for admin tables.
	 *
	 * @param array $meta  Output of AH_DB_Helper::paginate_meta()
	 * @param string $base_url  URL without 'paged' param
	 */
	public static function render( array $meta, string $base_url = '' ): string {
		if ( $meta['total_pages'] <= 1 ) return '';

		$current = (int) $meta['current_page'];
		$total   = (int) $meta['total_pages'];

		if ( ! $base_url ) {
			$base_url = remove_query_arg( 'paged' );
		}

		$html  = '<div class="ah-pagination tablenav-pages">';
		$html .= '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $meta['total'], 'ah-theme' ), number_format_i18n( $meta['total'] ) ) . '</span>';
		$html .= '<span class="pagination-links">';

		// First / Prev
		if ( $current > 1 ) {
			$html .= '<a class="first-page button" href="' . esc_url( add_query_arg( 'paged', 1, $base_url ) ) . '">&laquo;</a>';
			$html .= '<a class="prev-page button" href="' . esc_url( add_query_arg( 'paged', $current - 1, $base_url ) ) . '">&lsaquo;</a>';
		} else {
			$html .= '<span class="first-page button disabled">&laquo;</span>';
			$html .= '<span class="prev-page button disabled">&lsaquo;</span>';
		}

		$html .= '<span class="paging-input">' . $current . ' / <span class="total-pages">' . $total . '</span></span>';

		// Next / Last
		if ( $current < $total ) {
			$html .= '<a class="next-page button" href="' . esc_url( add_query_arg( 'paged', $current + 1, $base_url ) ) . '">&rsaquo;</a>';
			$html .= '<a class="last-page button" href="' . esc_url( add_query_arg( 'paged', $total, $base_url ) ) . '">&raquo;</a>';
		} else {
			$html .= '<span class="next-page button disabled">&rsaquo;</span>';
			$html .= '<span class="last-page button disabled">&raquo;</span>';
		}

		$html .= '</span></div>';
		return $html;
	}

	public static function current_page(): int {
		return max( 1, (int) ( $_GET['paged'] ?? 1 ) );
	}
}
