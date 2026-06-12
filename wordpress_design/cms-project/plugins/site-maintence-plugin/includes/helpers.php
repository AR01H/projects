<?php
/**
 * Global helper functions.
 *
 * These are purposely few and thin - most logic lives in the service classes.
 * Helpers exist only where a procedural call is more convenient for template use.
 *
 * @package SiteModeManager
 */

declare( strict_types=1 );

namespace SiteModeManager;

// Block direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the current active mode string.
 *
 * @return string One of the Settings::MODE_* constants.
 */
function smm_get_mode(): string {
	return smm()->settings->get_active_mode(); // phpcs:ignore - accessing via plugin instance is intentional.
}

/**
 * Checks whether the site is in the given mode.
 *
 * @param string $mode Mode constant (e.g. Settings::MODE_MAINTENANCE).
 * @return bool
 */
function smm_is_mode( string $mode ): bool {
	return smm_get_mode() === $mode;
}

/**
 * Outputs a partial template from templates/partials/.
 * Silently skips if the file does not exist.
 *
 * @param string       $partial  Filename relative to templates/partials/.
 * @param TemplateData $smm      Shared template data object.
 * @return void
 */
function smm_get_partial( string $partial, TemplateData $smm ): void {
	$path = $smm->partial_path( $partial );
	if ( file_exists( $path ) ) {
		include $path; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}
