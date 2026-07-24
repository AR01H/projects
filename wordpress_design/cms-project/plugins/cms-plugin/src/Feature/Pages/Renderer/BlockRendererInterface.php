<?php

namespace Ah\Cms\Feature\Pages\Renderer;

defined( 'ABSPATH' ) || exit;

/**
 * Block Renderer Interface — each block type implements this.
 */
interface BlockRendererInterface {

	/**
	 * Render a block of the given type with its data.
	 *
	 * @param string $type  Block type (e.g., 'hero', 'highlights', 'cta').
	 * @param array  $data  Block configuration data.
	 */
	public static function render( string $type, array $data ): void;
}
