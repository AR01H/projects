<?php

namespace Ah\Cms\Feature\Pages\Renderer;

defined( 'ABSPATH' ) || exit;

/**
 * Block Renderer Registry — maps block types to their renderer classes.
 * Replaces the giant switch statement in ah_render_builder_block().
 */
class BlockRendererRegistry {

	/** @var array<string, class-string<BlockRendererInterface>> */
	private static array $renderers = [];

	/**
	 * Register a block renderer for a type.
	 */
	public static function register( string $type, string $rendererClass ): void {
		self::$renderers[ $type ] = $rendererClass;
	}

	/**
	 * Render a block by type.
	 * Falls back to the legacy ah_render_builder_block() function if no renderer registered.
	 */
	public static function render( string $type, array $data ): void {
		if ( isset( self::$renderers[ $type ] ) ) {
			$renderer = self::$renderers[ $type ];
			$renderer::render( $type, $data );
			return;
		}

		// Fallback to legacy function
		if ( function_exists( 'ah_render_builder_block' ) ) {
			ah_render_builder_block( $type, $data );
		}
	}

	/**
	 * Get all registered block types.
	 */
	public static function getTypes(): array {
		return array_keys( self::$renderers );
	}

	/**
	 * Check if a renderer is registered for a type.
	 */
	public static function has( string $type ): bool {
		return isset( self::$renderers[ $type ] );
	}
}
