<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Support\View;

$tag        = (string) ( $tag ?? 'Our Gallery' );
$title      = (string) ( $title ?? 'A LOOK BACK IN TIME' );
$subtitle   = (string) ( $subtitle ?? ( $sub ?? 'A few of our favourite moments capturing the heritage, stall life, and smiles over the years.' ) );
$items      = (array) ( $items ?? array() );
$categories = (array) ( $categories ?? array() );

View::component( 'sections/look-back-in-time-section', array(
	'tag'        => $tag,
	'title'      => $title,
	'subtitle'   => $subtitle,
	'items'      => $items,
	'categories' => $categories,
) );
