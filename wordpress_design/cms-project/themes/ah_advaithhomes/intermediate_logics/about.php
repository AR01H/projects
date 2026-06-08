<?php
defined( 'ABSPATH' ) || exit;
return [
	'stats'   => ah_get_site_stats(),
	'signals' => ah_get_trust_signals(),
];
