<?php

namespace Adn\Theme\Helper;

defined( 'ABSPATH' ) || exit;

class PageHelper {

	public static function open( array $ctx ): void {
		$chrome = isset( $ctx['chrome'] ) ? $ctx['chrome'] : array();

		// Render the main header/navigation component
		ComponentRenderer::render( 'parts/main_header', array( 'chrome' => $chrome ) );
	}

	public static function close( array $ctx ): void {
		$chrome = isset( $ctx['chrome'] ) ? $ctx['chrome'] : array();
		$footer = isset( $chrome['footer'] ) ? $chrome['footer'] : array();

		// Render the main footer component
		ComponentRenderer::render( 'parts/main_footer', array( 'footer' => $footer ) );
	}
}
