<?php
defined( 'ABSPATH' ) || exit;
return [
	'steps'      => ah_get_process_steps(),
	'blog_posts' => get_posts( [ 'numberposts' => 4, 'post_status' => 'publish' ] ),
];
