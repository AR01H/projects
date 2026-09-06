<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\Plugins\ClientStoriesBridgeService;
use VintageSoul\Support\View;

$gallery_data = (array) ( JsonFileProvider::read( 'data/content/gallery.json' ) ?? array() );

// Primary source: CMS "Showcase Gallery" (admin.php?page=ah-client-stories).
// Its images have no title/caption/category, so category tabs only appear
// with the JSON fallback below. Falls back to the static JSON until that
// admin's header is made visible and gallery images are added.
$cms_header = ClientStoriesBridgeService::get_header();
$cms_items  = ClientStoriesBridgeService::get_gallery_items();
$using_cms  = ! empty( $cms_items );

$tag        = (string) ( $tag ?? ( $using_cms ? '' : ( $gallery_data['tag'] ?? '' ) ) );
$title      = (string) ( $title ?? ( $using_cms ? $cms_header['heading'] : ( $gallery_data['title'] ?? '' ) ) );
$subtitle   = (string) ( $subtitle ?? ( $sub ?? ( $using_cms ? $cms_header['description'] : ( $gallery_data['subtitle'] ?? '' ) ) ) );
$items      = ! empty( $items ) ? (array) $items : ( $using_cms ? $cms_items : (array) ( $gallery_data['items'] ?? array() ) );
$categories = ! empty( $categories ) ? (array) $categories : ( $using_cms ? array() : (array) ( $gallery_data['categories'] ?? array() ) );

View::component( 'sections/look-back-in-time-section', array(
	'tag'        => $tag,
	'title'      => $title,
	'subtitle'   => $subtitle,
	'items'      => $items,
	'categories' => $categories,
) );
