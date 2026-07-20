<?php
/**
 * config/admin.php - Theme admin: menu > submenus > tabs > subtabs + options + tools.
 *
 * core/admin.php mirrors this array into the WP sidebar and the page UI:
 *
 *   Theme                        <- 'menu' (top-level sidebar menu)
 *   +-- Admin Dashboard Tools    <- each 'submenus' entry = sidebar SUBMENU
 *   |     [Dashboard] [Site Settings] [Admin Tools]   <- its 'tabs' = pill bar
 *   |         [General] [Social]                      <- a tab's 'subtabs' = small pills
 *   +-- Contact Submissions      <- another sidebar submenu
 *
 * Views live in /admin/tabs/ (realpath-guarded whitelist).
 *
 *   - 'options' -> generic SAVE engine. Each group gets its own
 *                  admin_post_nt_save_{group} endpoint with nonce +
 *                  capability enforced and every field sanitized by TYPE.
 *   - 'tools'   -> one-click maintenance actions (see block below).
 *
 * ADD A SETTINGS SCREEN (3 steps, no PHP handlers to write):
 *   1. Add a subtab under 'tabs' pointing at a view file.
 *   2. Add an option group under 'options' listing field => type.
 *   3. Create the view: a form posting action=nt_save_{group}
 *      (see admin/tabs/settings/sub-general.php for the pattern).
 *   Read values anywhere with nt_option( 'group', 'field', 'default' ).
 *
 * Field types -> sanitizer:
 *   text -> sanitize_text_field   textarea -> sanitize_textarea_field
 *   email -> sanitize_email       url      -> esc_url_raw
 *   int  -> absint                bool     -> (0|1)
 *   html -> wp_kses_post          key      -> sanitize_key
 */

defined( 'ABSPATH' ) || exit;

return array(

	'menu' => array(
		'slug'       => 'nt-theme',
		'page_title' => 'Theme Control Center',
		'menu_title' => 'Theme',
		'capability' => 'manage_options',
		'icon'       => 'dashicons-admin-customizer',
		'position'   => 59,
	),

	'submenus' => array(

		// Sidebar submenu 1: everything for running the theme.
		'dashboard_tools' => array(
			'label' => 'Admin Dashboard Tools',
			'tabs'  => array(

				'dashboard' => array(
					'label' => 'Dashboard',
					'icon'  => '📊',
					'view'  => 'tab-dashboard.php',
				),

				'settings' => array(
					'label'   => 'Site Settings',
					'icon'    => '⚙️',
					'subtabs' => array(
						'general' => array(
							'label' => 'General',
							'icon'  => '🏠',
							'view'  => 'settings/sub-general.php',
						),
						'social' => array(
							'label' => 'Social Links',
							'icon'  => '🔗',
							'view'  => 'settings/sub-social.php',
						),
					),
				),

				'admin_tools' => array(
					'label'   => 'Admin Tools',
					'icon'    => '🛠️',
					'subtabs' => array(
						'maintenance' => array(
							'label' => 'Cache & Rewrites',
							'icon'  => '🧹',
							'view'  => 'admin-tools/sub-maintenance.php',
						),
						'pages' => array(
							'label' => 'Pages',
							'icon'  => '📄',
							'view'  => 'admin-tools/sub-pages.php',
						),
						'database' => array(
							'label' => 'Database',
							'icon'  => '🗄️',
							'view'  => 'admin-tools/sub-database.php',
						),
						'import-export' => array(
							'label' => 'Import / Export',
							'icon'  => '📦',
							'view'  => 'admin-tools/sub-import-export.php',
						),
						'system' => array(
							'label' => 'System Info',
							'icon'  => 'ℹ️',
							'view'  => 'admin-tools/sub-system.php',
						),
					),
				),
			),
		),

		// Sidebar submenu 2: contact form inbox (nt_submissions table -
		// see config/database.php + handlers/ajax/contact.php).
		'contact_inbox' => array(
			'label' => 'Contact Submissions',
			'tabs'  => array(
				'inbox' => array(
					'label' => 'Inbox',
					'icon'  => '📥',
					'view'  => 'tab-contact-inbox.php',
				),
			),
		),
	),

	// -----------------------------------------------------------------------
	// Admin TOOLS registry - one-click maintenance actions, common to every
	// site built on this template. core/admin.php loops this array:
	//   - each key gets its own admin_post_nt_tool_{key} endpoint,
	//   - capability + nonce are checked BEFORE the callback runs,
	//   - the callback returns a status message shown back on the tab.
	// Render a group of tools on any admin view with:
	//   nt_admin_tools_render( 'maintenance' );
	//
	// ADD A TOOL (2 steps): add an entry here + write the callback in
	// admin/includes/tools.php. No handler/nonce/form code needed.
	// -----------------------------------------------------------------------
	'tools' => array(

		'clear_cache' => array(
			'title'    => 'Clear Object Cache',
			'desc'     => 'Flush the WordPress object cache (wp_cache_flush).',
			'button'   => 'Clear Cache',
			'callback' => 'nt_tool_clear_object_cache',
			'group'    => 'maintenance',
		),

		'clear_transients' => array(
			'title'    => 'Clear Transients',
			'desc'     => 'Delete all transients from the options table (cached API calls, expired leftovers).',
			'button'   => 'Clear Transients',
			'callback' => 'nt_tool_clear_transients',
			'group'    => 'maintenance',
		),

		'flush_rewrites' => array(
			'title'    => 'Flush Rewrite Rules',
			'desc'     => 'Rebuild permalinks. Run after changing page slugs or REST routes.',
			'button'   => 'Flush',
			'callback' => 'nt_tool_flush_rewrites',
			'group'    => 'maintenance',
		),

		'sync_pages' => array(
			'title'    => 'Sync Pages',
			'desc'     => 'Create WP page rows for every entry in config/pages.php (existing pages are skipped) and set the front page.',
			'button'   => 'Sync Now',
			'callback' => 'nt_tool_sync_pages',
			'group'    => 'pages',
		),

		'export_settings' => array(
			'title'    => 'Export Settings',
			'desc'     => 'Download all theme option groups as a JSON file.',
			'button'   => 'Download JSON',
			'callback' => 'nt_tool_export_settings',
			'group'    => 'import-export',
		),

		'install_tables' => array(
			'title'    => 'Install All Tables',
			'desc'     => 'Check every table in config/database.php and create any that are missing (dbDelta - safe to re-run).',
			'button'   => 'Install / Repair All',
			'callback' => 'nt_tool_install_tables',
			'group'    => 'database',
		),

		// Import has its own upload form in sub-import-export.php; it still
		// runs through the same generic endpoint + nonce as every tool.
		'import_settings' => array(
			'title'    => 'Import Settings',
			'desc'     => 'Restore theme option groups from an exported JSON file.',
			'button'   => 'Import',
			'callback' => 'nt_tool_import_settings',
			'group'    => '_hidden',
		),

		// Per-table install button, rendered per row by sub-database.php.
		'install_table' => array(
			'title'    => 'Install Table',
			'callback' => 'nt_tool_install_table',
			'group'    => '_hidden',
		),

		// Contact Submissions row actions (forms rendered by tab-contact-inbox.php).
		'submission_status' => array(
			'title'    => 'Update Submission Status',
			'callback' => 'nt_tool_submission_status',
			'group'    => '_hidden',
		),
		'submission_delete' => array(
			'title'    => 'Delete Submission',
			'callback' => 'nt_tool_submission_delete',
			'group'    => '_hidden',
		),
	),

	// Option groups saved by the generic engine. option = wp_options row name.
	'options' => array(

		'general' => array(
			'option' => 'nt_general',
			'fields' => array(
				'tagline'      => 'text',
				'phone'        => 'text',
				'email'        => 'email',
				'address'      => 'textarea',
				'footer_note'  => 'html',
			),
		),

		'social' => array(
			'option' => 'nt_social',
			'fields' => array(
				'facebook'  => 'url',
				'instagram' => 'url',
				'youtube'   => 'url',
				'linkedin'  => 'url',
				'whatsapp'  => 'text',
			),
		),
	),
);
