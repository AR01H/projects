<?php
defined( 'ABSPATH' ) || exit;
$services = ah_get_services( 12 );
return [
	'services'       => $services,
	'service_points' => ah_get_services_bullet_points( array_column( $services, 'id' ) ),
	'steps'          => ah_get_process_steps(),
	'stats'          => ah_get_site_stats(),
];
