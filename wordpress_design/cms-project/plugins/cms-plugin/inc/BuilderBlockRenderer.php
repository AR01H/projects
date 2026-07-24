<?php
/**
 * Block renderer functions for the Page Builder.
 * Backward-compatibility wrapper — delegates to Ah\Cms\Feature\Pages\Renderer\BlockRenderer.
 *
 * Functions defined here:
 *   ah_section_open( $d, $classes, $extra_style ) : string
 *   ah_render_builder_block( $type, $d )          : void
 */
defined( 'ABSPATH' ) || exit;

if ( function_exists( 'ah_render_builder_block' ) ) {
	return; // Already loaded (should not happen, but guard against double-include).
}

require_once __DIR__ . '/../src/Feature/Pages/Renderer/BlockRenderer.php';

// ── Section wrapper helper ────────────────────────────────────────────────────

function ah_section_open( array $d, string $classes = 'section', string $extra_style = '' ): string {
	return \Ah\Cms\Feature\Pages\Renderer\BlockRenderer::sectionOpen( $d, $classes, $extra_style );
}

// ── Main block renderer ───────────────────────────────────────────────────────

function ah_render_builder_block( string $type, array $d ): void {
	\Ah\Cms\Feature\Pages\Renderer\BlockRenderer::render( $type, $d );
}
