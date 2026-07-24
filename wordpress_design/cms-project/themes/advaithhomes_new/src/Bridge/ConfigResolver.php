<?php

namespace Adn\Theme\Bridge;

defined( 'ABSPATH' ) || exit;

/**
 * Config Resolver — loads JSON config files with override support.
 *
 * Resolution order:
 * 1. Check overrides/{path}
 * 2. If exists → use override (or merge with base)
 * 3. If not → use base/{path}
 */
class ConfigResolver {

	private string $baseDir;
	private string $overrideDir;
	private PlaceholderResolver $resolver;

	public function __construct( string $baseDir, string $overrideDir, array $industry ) {
		$this->baseDir     = $baseDir;
		$this->overrideDir = $overrideDir;
		$this->resolver    = new PlaceholderResolver( $industry );
	}

	/**
	 * Load a JSON config file, with override support.
	 * Override file REPLACES base file entirely.
	 */
	public function load( string $path ): array {
		$overridePath = $this->overrideDir . '/' . $path;
		$basePath     = $this->baseDir . '/' . $path;

		if ( file_exists( $overridePath ) ) {
			return $this->readJson( $overridePath );
		}

		return $this->readJson( $basePath );
	}

	/**
	 * Load with deep merge — override values replace base,
	 * but missing keys fall back to base.
	 */
	public function loadMerged( string $path ): array {
		$base = $this->readJson( $this->baseDir . '/' . $path );
		$overridePath = $this->overrideDir . '/' . $path;

		if ( ! file_exists( $overridePath ) ) {
			return $base;
		}

		$override = $this->readJson( $overridePath );
		return array_replace_recursive( $base, $override );
	}

	/**
	 * Load with industry context — replace placeholders after loading.
	 */
	public function loadResolved( string $path ): array {
		$data = $this->loadMerged( $path );
		return $this->resolver->resolveAll( $data );
	}

	/**
	 * Read and decode a JSON file.
	 */
	private function readJson( string $path ): array {
		if ( ! file_exists( $path ) ) {
			return [];
		}
		$content = file_get_contents( $path );
		return json_decode( $content, true ) ?: [];
	}
}
