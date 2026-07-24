<?php

namespace Adn\Theme\Helper;

defined( 'ABSPATH' ) || exit;

class ComponentRenderer {

	public static function render( string $name, array $context = array() ): void {
		$base = realpath( ADN_THEME_DIR . '/components' );
		$file = realpath( ADN_THEME_DIR . '/components/' . $name . '.php' );
		if ( ! $base || ! $file || 0 !== strpos( $file, $base ) || ! is_file( $file ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( '[ADN] Component not found: ' . $name );
			}
			return;
		}
		extract( $context, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract
		include $file;
	}

	public static function renderForm( $config ): void {
		self::render( 'form_builder/form_builder', array( 'form' => $config ) );
	}
}
