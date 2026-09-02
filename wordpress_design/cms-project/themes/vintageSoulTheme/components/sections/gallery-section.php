<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Support\View;

$gallery_data = (array) ( JsonFileProvider::read( 'data/content/gallery.json' ) ?? array() );

$tag        = (string) ( $tag ?? ( $gallery_data['tag'] ?? '' ) );
$title      = (string) ( $title ?? ( $gallery_data['title'] ?? '' ) );
$subtitle   = (string) ( $subtitle ?? ( $sub ?? ( $gallery_data['subtitle'] ?? '' ) ) );
$items      = ! empty( $items ) ? (array) $items : (array) ( $gallery_data['items'] ?? array() );
$categories = ! empty( $categories ) ? (array) $categories : (array) ( $gallery_data['categories'] ?? array() );

View::component( 'sections/look-back-in-time-section', array(
	'tag'        => $tag,
	'title'      => $title,
	'subtitle'   => $subtitle,
	'items'      => $items,
	'categories' => $categories,
) );
