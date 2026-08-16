<?php

defined( 'ABSPATH' ) || exit;

return array(
	'home'    => array(
		'path'       => '/',
		'alternates' => array(),
		'styles'     => array(),
		'scripts'    => array(),
	),
	'about'   => array(
		'path'       => '/about',
		'alternates' => array( 'story', 'company', 'our-team' ),
		'styles'     => array(),
		'scripts'    => array(),
	),
	'game'    => array(
		'path'       => '/game',
		'alternates' => array( 'play', 'timepass', 'shop', 'portfolio', 'gallery', 'services', 'blog' ),
		'styles'     => array(),
		'scripts'    => array(),
	),
	'contact' => array(
		'path'       => '/contact',
		'alternates' => array( 'connect', 'support', 'get-in-touch', 'enquiry' ),
		'styles'     => array(),
		'scripts'    => array(),
	),

	'history' => array(
		'path'       => '/history',
		'alternates' => array( 'all-about-cane', 'sugarcane', 'our-history' ),
		'styles'     => array(),
		'scripts'    => array(),
	),

	'franchise' => array(
		'path'       => '/franchise',
		'alternates' => array( 'partner', 'franchise-opportunities', 'become-a-partner' ),
		'styles'     => array(),
		'scripts'    => array(),
	),

	'elements' => array(
		'path'       => '/elements',
		'alternates' => array( 'components', 'style-guide', 'ui-kit', 'testing' ),
		'styles'     => array(),
		'scripts'    => array(),
	),
);
