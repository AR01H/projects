<?php
namespace VintageSoul\Support;

defined( 'ABSPATH' ) || exit;

final class Formatter {

	public static function date( string $iso_date, string $format = 'F j, Y' ): string {
		$timestamp = strtotime( $iso_date );
		return $timestamp ? date_i18n( $format, $timestamp ) : '';
	}

	public static function star_rating( int $rating ): string {
		$rating = max( 0, min( 5, $rating ) );
		return str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating );
	}

	public static function compact_number( float $value ): string {
		if ( $value >= 1000000 ) {
			return rtrim( rtrim( number_format( $value / 1000000, 1 ), '0' ), '.' ) . 'M+';
		}
		if ( $value >= 1000 ) {
			return rtrim( rtrim( number_format( $value / 1000, 1 ), '0' ), '.' ) . 'K+';
		}
		return (string) (int) $value;
	}
}
