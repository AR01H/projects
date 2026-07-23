<?php
/**
 * config/files.php - Site code include map.
 *
 * Paths are relative to the theme root and loaded in order by the bootstrap.
 * core/ engines are NOT listed here - they are always loaded. This list is
 * for the site's own code: data providers, model helpers, shortcodes, etc.
 *
 *   'always' - loaded on every request (front + admin + ajax + rest).
 *   'admin'  - loaded only when is_admin().
 *   'front'  - loaded only when NOT is_admin().
 */

defined( 'ABSPATH' ) || exit;

return array(

	'always' => array(
		'admin/includes/terms.php',   // Term-level labels + JSON-backed term tree helpers.
		// ── Feature: Page Sections (OOP) ──
		// Each feature lives in its own src/<Feature>/ folder as a class; the
		// class is the "intermediate" layer between templates (UI) and JSON data.
		'src/Sections/class-section-renderer.php', // NT_Section_Renderer - renders page_sections.json.
		'includes/site-helpers.php',  // nt_section_visible() + nt_render_sections() thin wrappers.
		// 'includes/data-services.php',
		// 'includes/shortcodes.php',
	),

	'admin' => array(
		'admin/includes/admin-functions.php', // Site-specific wp-admin tweaks.
		'admin/includes/tools.php',           // Admin Tool callbacks (config/admin.php 'tools').
	),

	'front' => array(
		// 'includes/seo.php',
	),
);
