<?php
defined( 'ABSPATH' ) || exit;

class AH_Pagination {

	/**
	 * Render pagination links for admin tables.
	 *
	 * @param array $meta  Output of AH_DB_Helper::paginate_meta()
	 * @param string $base_url  URL without 'paged' param
	 */
	public static function render( array $meta, string $base_url = '' ): string {
		$total = (int) $meta['total'];
		$total_pages = (int) $meta['total_pages'];
		$current = (int) $meta['current_page'];
		$per_page = (int) ( $meta['per_page'] ?? 20 );

		if ( $total_pages <= 1 ) {
			if ( $total > 0 ) {
				return '<div class="ah-pagination"><span class="ah-pag-info">Showing ' . number_format_i18n( $total ) . ' item' . ( $total !== 1 ? 's' : '' ) . '</span></div>';
			}
			return '';
		}

		if ( ! $base_url ) {
			$base_url = remove_query_arg( 'paged' );
		}

		$start = ( $current - 1 ) * $per_page + 1;
		$end   = min( $current * $per_page, $total );

		$html = '<div class="ah-pagination">';
		$html .= '<span class="ah-pag-info">Showing ' . number_format_i18n( $start ) . '–' . number_format_i18n( $end ) . ' of ' . number_format_i18n( $total ) . ' items</span>';
		$html .= '<span class="ah-pag-links">';

		// First
		if ( $current > 1 ) {
			$html .= '<a href="' . esc_url( add_query_arg( 'paged', 1, $base_url ) ) . '" class="ah-pag-btn" title="First page">&laquo;</a>';
		} else {
			$html .= '<span class="ah-pag-btn disabled">&laquo;</span>';
		}

		// Previous
		if ( $current > 1 ) {
			$html .= '<a href="' . esc_url( add_query_arg( 'paged', $current - 1, $base_url ) ) . '" class="ah-pag-btn" title="Previous page">&lsaquo;</a>';
		} else {
			$html .= '<span class="ah-pag-btn disabled">&lsaquo;</span>';
		}

		// Page numbers
		$range = self::page_range( $current, $total_pages );
		foreach ( $range as $p ) {
			if ( $p === '...' ) {
				$html .= '<span class="ah-pag-dots">…</span>';
			} elseif ( $p === $current ) {
				$html .= '<span class="ah-pag-btn active">' . $p . '</span>';
			} else {
				$html .= '<a href="' . esc_url( add_query_arg( 'paged', $p, $base_url ) ) . '" class="ah-pag-btn">' . $p . '</a>';
			}
		}

		// Next
		if ( $current < $total_pages ) {
			$html .= '<a href="' . esc_url( add_query_arg( 'paged', $current + 1, $base_url ) ) . '" class="ah-pag-btn" title="Next page">&rsaquo;</a>';
		} else {
			$html .= '<span class="ah-pag-btn disabled">&rsaquo;</span>';
		}

		// Last
		if ( $current < $total_pages ) {
			$html .= '<a href="' . esc_url( add_query_arg( 'paged', $total_pages, $base_url ) ) . '" class="ah-pag-btn" title="Last page">&raquo;</a>';
		} else {
			$html .= '<span class="ah-pag-btn disabled">&raquo;</span>';
		}

		$html .= '</span></div>';
		return $html;
	}

	/**
	 * Generate page number range with ellipsis.
	 */
	private static function page_range( int $current, int $total ): array {
		if ( $total <= 7 ) {
			return range( 1, $total );
		}

		$range = array();
		$range[] = 1;

		if ( $current > 3 ) {
			$range[] = '...';
		}

		$start = max( 2, $current - 1 );
		$end   = min( $total - 1, $current + 1 );

		for ( $i = $start; $i <= $end; $i++ ) {
			$range[] = $i;
		}

		if ( $current < $total - 2 ) {
			$range[] = '...';
		}

		$range[] = $total;
		return $range;
	}

	public static function current_page(): int {
		return max( 1, (int) ( $_GET['paged'] ?? 1 ) );
	}
}
