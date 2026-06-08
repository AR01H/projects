<?php
defined( 'ABSPATH' ) || exit;
$reviews = ah_get_reviews( 12 );
$stats   = ah_get_site_stats();

$rating_num  = '4.9';
$client_stat = '500+';
foreach ( $stats as $s ) {
	$s = is_object( $s ) ? (array) $s : $s;
	$n = $s['num'] ?? '';
	if ( strpos( $n, '★' ) !== false ) $rating_num  = rtrim( str_replace( '★', '', $n ) );
	if ( strpos( $n, '500' ) !== false ) $client_stat = $n;
}

return [
	'reviews'     => $reviews,
	'stats'       => $stats,
	'rating_num'  => $rating_num,
	'client_stat' => $client_stat,
];
