<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Support\View;

$gallery_data = (array) ( JsonFileProvider::read( 'data/content/gallery.json' ) ?? array() );

$tag        = (string) ( $tag ?? ( $gallery_data['tag'] ?? 'Our Gallery' ) );
$title      = (string) ( $title ?? ( $gallery_data['title'] ?? 'LOOK BACK IN <em>Time</em>' ) );
$subtitle   = (string) ( $subtitle ?? ( $sub ?? ( $gallery_data['subtitle'] ?? 'A few of our favourite moments capturing the heritage, stall life, and smiles over the years.' ) ) );
$items      = ! empty( $items ) ? (array) $items : (array) ( $gallery_data['items'] ?? array() );
$categories = ! empty( $categories ) ? (array) $categories : (array) ( $gallery_data['categories'] ?? array() );

View::component( 'sections/look-back-in-time-section', array(
	'tag'        => $tag,
	'title'      => $title,
	'subtitle'   => $subtitle,
	'items'      => $items,
	'categories' => $categories,
) );
