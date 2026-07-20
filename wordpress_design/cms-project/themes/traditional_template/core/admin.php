<?php
/**
 * core/admin.php - Admin engine driven by config/admin.php.
 *
 * Mirrors the registry into the WP admin, four levels deep:
 *
 *   Theme                        <- 'menu'     add_menu_page
 *   +-- Admin Dashboard Tools    <- 'submenus' add_submenu_page (sidebar)
 *   |     [Dashboard] [Site Settings] [Admin Tools]   <- 'tabs'    pill bar
 *   |         [General] [Social Links]                <- 'subtabs' small pills
 *   +-- Contact Submissions
 *
 * Navigation, routing, view loading, option saving, tool running and field
 * rendering are all generic loops - adding a screen means adding array
 * entries + a small view file.
 *
 * View files live in /admin/tabs/ and are included through a realpath
 * whitelist, so a tampered ?tab / ?subtab can never load an arbitrary file.
 * Every option-group save and every tool run has capability + nonce
 * enforced HERE, before any callback executes.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wire everything - called from the bootstrap when is_admin().
 */
function nt_admin_boot() {
	add_action( 'admin_menu', 'nt_admin_register_menu' );
	add_action( 'admin_enqueue_scripts', 'nt_admin_enqueue_assets' );

	$admin = nt_config( 'admin' );

	// One save endpoint per option group - generic handler, zero per-group code.
	foreach ( (array) ( $admin['options'] ?? array() ) as $group => $def ) {
		add_action( 'admin_post_nt_save_' . $group, static function () use ( $group, $def ) {
			nt_admin_save_options( $group, $def );
		} );
	}

	// One endpoint per Admin Tool (config/admin.php 'tools' registry) - the
	// generic runner enforces capability + nonce before every callback.
	foreach ( (array) ( $admin['tools'] ?? array() ) as $key => $tool ) {
		add_action( 'admin_post_nt_tool_' . $key, static function () use ( $key, $tool ) {
			nt_admin_run_tool( $key, $tool );
		} );
	}
}

function nt_admin_menu_cfg() {
	$admin = nt_config( 'admin' );
	return wp_parse_args( (array) ( $admin['menu'] ?? array() ), array(
		'slug'       => 'nt-theme',
		'page_title' => 'Theme',
		'menu_title' => 'Theme',
		'capability' => 'manage_options',
		'icon'       => 'dashicons-admin-generic',
		'position'   => 59,
	) );
}

/**
 * The sidebar submenu registry.
 */
function nt_admin_submenus() {
	return (array) ( nt_config( 'admin' )['submenus'] ?? array() );
}

/**
 * The WP admin page slug serving a submenu (parent slug for the first one).
 */
function nt_admin_page_slug( $submenu ) {
	$menu     = nt_admin_menu_cfg();
	$submenus = nt_admin_submenus();
	if ( '' === $submenu || $submenu === (string) array_key_first( $submenus ) ) {
		return $menu['slug'];
	}
	return $menu['slug'] . '-' . $submenu;
}

/**
 * Sidebar registration:
 *   Theme -> one add_submenu_page per 'submenus' entry.
 */
function nt_admin_register_menu() {
	$menu = nt_admin_menu_cfg();

	add_menu_page(
		$menu['page_title'],
		$menu['menu_title'],
		$menu['capability'],
		$menu['slug'],
		'nt_admin_render_page',
		$menu['icon'],
		$menu['position']
	);

	foreach ( nt_admin_submenus() as $key => $def ) {
		$label = wp_strip_all_tags( (string) ( $def['label'] ?? $key ) );
		add_submenu_page(
			$menu['slug'],
			$label,
			$label,
			$menu['capability'],
			nt_admin_page_slug( (string) $key ),
			'nt_admin_render_page'
		);
	}
}

function nt_admin_enqueue_assets( $hook ) {
	if ( false === strpos( (string) $hook, nt_admin_menu_cfg()['slug'] ) ) {
		return;
	}
	wp_enqueue_media();
	$assets = nt_config( 'assets' );
	nt_enqueue_list( $assets['admin_css'] ?? array(), 'css', 'nt-admin' );
	nt_enqueue_list( $assets['admin_js'] ?? array(), 'js', 'nt-admin' );
}

// ---------------------------------------------------------------------------
// Current location (submenu / tab / subtab), always validated against the
// registry - bad input falls back to the first defined entry.
// ---------------------------------------------------------------------------

/**
 * Current sidebar submenu, derived from the WP page slug
 * (?page=nt-theme-contact_inbox -> 'contact_inbox').
 */
function nt_admin_current_submenu() {
	$submenus = nt_admin_submenus();
	$slug     = nt_admin_menu_cfg()['slug'];
	$page     = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

	$prefix = $slug . '-';
	if ( 0 === strpos( $page, $prefix ) ) {
		$key = substr( $page, strlen( $prefix ) );
		if ( isset( $submenus[ $key ] ) ) {
			return $key;
		}
	}
	return (string) array_key_first( $submenus );
}

/**
 * Current pill tab (?tab=) within the current submenu.
 */
function nt_admin_current_tab() {
	$submenus = nt_admin_submenus();
	$tabs     = (array) ( $submenus[ nt_admin_current_submenu() ]['tabs'] ?? array() );
	if ( ! $tabs ) {
		return '';
	}
	$req = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
	if ( isset( $tabs[ $req ] ) ) {
		return $req;
	}
	return (string) array_key_first( $tabs );
}

/**
 * Current small pill subtab (?subtab=) within the current tab.
 */
function nt_admin_current_subtab() {
	$submenus = nt_admin_submenus();
	$tabs     = (array) ( $submenus[ nt_admin_current_submenu() ]['tabs'] ?? array() );
	$tab      = nt_admin_current_tab();
	$subs     = (array) ( $tabs[ $tab ]['subtabs'] ?? array() );
	if ( ! $subs ) {
		return '';
	}
	$req = isset( $_GET['subtab'] ) ? sanitize_key( wp_unslash( $_GET['subtab'] ) ) : '';
	if ( isset( $subs[ $req ] ) ) {
		return $req;
	}
	return (string) array_key_first( $subs );
}

/**
 * URL of a theme admin screen.
 *
 *   nt_admin_url()                                        -> first submenu
 *   nt_admin_url( 'contact_inbox' )                       -> a submenu
 *   nt_admin_url( 'dashboard_tools', 'admin_tools' )      -> a tab
 *   nt_admin_url( 'dashboard_tools', 'admin_tools', 'database' ) -> a subtab
 */
function nt_admin_url( $submenu = '', $tab = '', $subtab = '' ) {
	$args = array( 'page' => nt_admin_page_slug( (string) $submenu ) );
	if ( '' !== $tab ) {
		$args['tab'] = $tab;
	}
	if ( '' !== $subtab ) {
		$args['subtab'] = $subtab;
	}
	return add_query_arg( $args, admin_url( 'admin.php' ) );
}

// ---------------------------------------------------------------------------
// Page renderer.
// ---------------------------------------------------------------------------

/**
 * Renders every submenu screen: notices + pill tab bar + pill subtab bar +
 * whitelisted view include.
 */
function nt_admin_render_page() {
	if ( ! current_user_can( nt_admin_menu_cfg()['capability'] ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', NT_TEXT_DOMAIN ) );
	}

	$submenus = nt_admin_submenus();
	$submenu  = nt_admin_current_submenu();
	$tabs     = (array) ( $submenus[ $submenu ]['tabs'] ?? array() );
	$tab      = nt_admin_current_tab();
	$subtab   = nt_admin_current_subtab();

	$submenu_label = wp_strip_all_tags( (string) ( $submenus[ $submenu ]['label'] ?? $submenu ) );

	echo '<div class="wrap nt-admin-wrap">';
	echo '<h1>' . esc_html( nt_admin_menu_cfg()['menu_title'] . ' — ' . $submenu_label ) . '</h1>';

	// Notices (set by the save/tools handlers via query args).
	if ( isset( $_GET['updated'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Saved.', NT_TEXT_DOMAIN ) . '</p></div>';
	}
	if ( isset( $_GET['nt_msg'] ) ) {
		echo '<div class="notice notice-info is-dismissible"><p>' . esc_html( sanitize_text_field( wp_unslash( $_GET['nt_msg'] ) ) ) . '</p></div>';
	}

	// Tab bar - pill buttons (icons + labels from the registry). Hidden when
	// the submenu has only one tab (e.g. Contact Submissions).
	if ( count( $tabs ) > 1 ) {
		echo '<div class="nt-tabbar">';
		foreach ( $tabs as $key => $def ) {
			echo nt_admin_pill_link( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the builder.
				nt_admin_url( $submenu, $key ),
				(string) ( $def['icon'] ?? '' ),
				wp_strip_all_tags( (string) ( $def['label'] ?? $key ) ),
				$key === $tab
			);
		}
		echo '</div>';
	}

	// Subtab bar - smaller pills, from the current tab's 'subtabs'.
	$subs = (array) ( $tabs[ $tab ]['subtabs'] ?? array() );
	if ( $subs ) {
		echo '<div class="nt-tabbar nt-tabbar-sub">';
		foreach ( $subs as $key => $def ) {
			echo nt_admin_pill_link( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the builder.
				nt_admin_url( $submenu, $tab, $key ),
				(string) ( $def['icon'] ?? '' ),
				wp_strip_all_tags( (string) ( $def['label'] ?? $key ) ),
				$key === $subtab
			);
		}
		echo '</div>';
	}

	// Resolve + include the view through the whitelist.
	$view = $subs ? ( $subs[ $subtab ]['view'] ?? '' ) : ( $tabs[ $tab ]['view'] ?? '' );
	$base = realpath( NT_THEME_DIR . '/admin/tabs' );
	$file = realpath( NT_THEME_DIR . '/admin/tabs/' . $view );
	if ( $base && $file && 0 === strpos( $file, $base ) && is_file( $file ) ) {
		include $file;
	} else {
		echo '<p>' . esc_html__( 'View file missing:', NT_TEXT_DOMAIN ) . ' <code>admin/tabs/' . esc_html( (string) $view ) . '</code></p>';
	}

	echo '</div>';
}

/**
 * One pill-style nav button (fully escaped here; callers echo the result).
 */
function nt_admin_pill_link( $url, $icon, $label, $active ) {
	$class = 'nt-tabbtn' . ( $active ? ' is-active' : '' );
	$html  = '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '"' . ( $active ? ' aria-current="page"' : '' ) . '>';
	if ( '' !== (string) $icon ) {
		$html .= '<span class="nt-tabbtn-icon" aria-hidden="true">' . esc_html( (string) $icon ) . '</span>';
	}
	$html .= '<span class="nt-tabbtn-label">' . esc_html( (string) $label ) . '</span></a>';
	return $html;
}

/**
 * The three hidden location fields every admin form posts, so handlers can
 * redirect back to the exact screen the user was on.
 */
function nt_admin_location_fields() {
	echo '<input type="hidden" name="nt_submenu" value="' . esc_attr( nt_admin_current_submenu() ) . '">';
	echo '<input type="hidden" name="nt_tab" value="' . esc_attr( nt_admin_current_tab() ) . '">';
	echo '<input type="hidden" name="nt_subtab" value="' . esc_attr( nt_admin_current_subtab() ) . '">';
}

/**
 * Read the posted location fields back as an nt_admin_url() argument list.
 */
function nt_admin_posted_location() {
	return array(
		isset( $_POST['nt_submenu'] ) ? sanitize_key( wp_unslash( $_POST['nt_submenu'] ) ) : '',
		isset( $_POST['nt_tab'] ) ? sanitize_key( wp_unslash( $_POST['nt_tab'] ) ) : '',
		isset( $_POST['nt_subtab'] ) ? sanitize_key( wp_unslash( $_POST['nt_subtab'] ) ) : '',
	);
}

// ---------------------------------------------------------------------------
// Generic option-group save engine.
// ---------------------------------------------------------------------------

/**
 * Sanitize one value by its declared type (config/admin.php 'fields').
 */
function nt_admin_sanitize( $type, $raw ) {
	switch ( $type ) {
		case 'textarea': return sanitize_textarea_field( (string) $raw );
		case 'email':    return sanitize_email( (string) $raw );
		case 'url':      return esc_url_raw( (string) $raw );
		case 'int':      return absint( $raw );
		case 'bool':     return empty( $raw ) ? 0 : 1;
		case 'html':     return wp_kses_post( (string) $raw );
		case 'key':      return sanitize_key( (string) $raw );
		case 'text':
		default:         return sanitize_text_field( (string) $raw );
	}
}

/**
 * Shared admin-post handler for every option group.
 */
function nt_admin_save_options( $group, $def ) {
	if ( ! current_user_can( nt_admin_menu_cfg()['capability'] ) ) {
		wp_die( esc_html__( 'Permission denied.', NT_TEXT_DOMAIN ) );
	}
	check_admin_referer( 'nt_save_' . $group );

	$option = (string) ( $def['option'] ?? 'nt_' . sanitize_key( $group ) );
	$fields = (array) ( $def['fields'] ?? array() );

	$clean = array();
	foreach ( $fields as $key => $type ) {
		$raw           = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
		$clean[ $key ] = nt_admin_sanitize( $type, $raw );
	}
	update_option( $option, $clean );

	list( $submenu, $tab, $subtab ) = nt_admin_posted_location();
	wp_safe_redirect( add_query_arg( 'updated', '1', nt_admin_url( $submenu, $tab, $subtab ) ) );
	exit;
}

// ---------------------------------------------------------------------------
// View helpers - make settings views 3 lines: open + fields loop + close.
// ---------------------------------------------------------------------------

/**
 * Open a settings form bound to an option group. Prints the admin-post
 * action, nonce and current location hidden fields.
 */
function nt_admin_form_open( $group ) {
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="' . esc_attr( 'nt_save_' . $group ) . '">';
	nt_admin_location_fields();
	wp_nonce_field( 'nt_save_' . $group );
	echo '<table class="form-table" role="presentation"><tbody>';
}

function nt_admin_form_close( $button_label = '' ) {
	echo '</tbody></table>';
	submit_button( '' !== $button_label ? $button_label : __( 'Save Changes', NT_TEXT_DOMAIN ) );
	echo '</form>';
}

/**
 * Render form rows from a labels array - types come from config/admin.php,
 * so a view never re-declares them.
 *
 *   nt_admin_fields( 'general', array(
 *       'tagline' => array( 'label' => 'Tagline', 'help' => 'Shown in the header.' ),
 *       'email'   => array( 'label' => 'Public Email' ),
 *   ) );
 */
function nt_admin_fields( $group, $rows ) {
	$admin  = nt_config( 'admin' );
	$types  = (array) ( $admin['options'][ $group ]['fields'] ?? array() );
	$values = nt_option( $group );

	foreach ( (array) $rows as $key => $row ) {
		$type  = (string) ( $types[ $key ] ?? 'text' );
		$label = (string) ( $row['label'] ?? ucwords( str_replace( '_', ' ', $key ) ) );
		$help  = (string) ( $row['help'] ?? '' );
		$value = $values[ $key ] ?? '';
		$id    = 'nt-' . $group . '-' . $key;

		echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label></th><td>';

		switch ( $type ) {
			case 'textarea':
			case 'html':
				echo '<textarea class="large-text" rows="4" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '">' . esc_textarea( (string) $value ) . '</textarea>';
				break;
			case 'bool':
				echo '<label><input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="1" ' . checked( ! empty( $value ), true, false ) . '> ' . esc_html( $help ? $help : $label ) . '</label>';
				$help = ''; // Already shown next to the checkbox.
				break;
			case 'int':
				echo '<input type="number" class="small-text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( (string) $value ) . '">';
				break;
			default:
				$input_type = in_array( $type, array( 'email', 'url' ), true ) ? $type : 'text';
				echo '<input type="' . esc_attr( $input_type ) . '" class="regular-text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( (string) $value ) . '">';
		}

		if ( '' !== $help ) {
			echo '<p class="description">' . esc_html( $help ) . '</p>';
		}
		echo '</td></tr>';
	}
}

// ---------------------------------------------------------------------------
// Generic Admin Tools engine (config/admin.php 'tools' registry).
// ---------------------------------------------------------------------------

/**
 * Shared runner for every registered tool: capability + nonce first, then
 * the callback. The callback returns a status message (or exits itself for
 * download-type tools like the settings export).
 */
function nt_admin_run_tool( $key, $tool ) {
	if ( ! current_user_can( nt_admin_menu_cfg()['capability'] ) ) {
		wp_die( esc_html__( 'Permission denied.', NT_TEXT_DOMAIN ) );
	}
	check_admin_referer( 'nt_tool_' . $key );

	if ( ! empty( $tool['file'] ) ) {
		nt_require_theme_file( $tool['file'] );
	}
	$callback = $tool['callback'] ?? '';
	$message  = is_callable( $callback )
		? (string) call_user_func( $callback )
		: 'Tool handler missing: ' . $key;

	list( $submenu, $tab, $subtab ) = nt_admin_posted_location();
	wp_safe_redirect( add_query_arg( 'nt_msg', rawurlencode( $message ), nt_admin_url( $submenu, $tab, $subtab ) ) );
	exit;
}

/**
 * Render the tool cards of one group as nonce'd one-click forms.
 * Used by the admin-tools/sub-*.php views:
 *
 *   nt_admin_tools_render( 'maintenance' );
 *
 * Tools with group '_hidden' are never rendered here - they have their own
 * custom form in a view (e.g. the settings import upload form).
 */
function nt_admin_tools_render( $group = '' ) {
	$tools = (array) ( nt_config( 'admin' )['tools'] ?? array() );

	echo '<div class="nt-admin-tools">';
	foreach ( $tools as $key => $tool ) {
		$tool_group = (string) ( $tool['group'] ?? '' );
		if ( '_hidden' === $tool_group || ( '' !== $group && $group !== $tool_group ) ) {
			continue;
		}
		echo '<div class="nt-admin-card nt-admin-tool">';
		echo '<h3>' . esc_html( (string) ( $tool['title'] ?? $key ) ) . '</h3>';
		echo '<p>' . esc_html( (string) ( $tool['desc'] ?? '' ) ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( 'nt_tool_' . $key ) . '">';
		nt_admin_location_fields();
		wp_nonce_field( 'nt_tool_' . $key );
		submit_button( (string) ( $tool['button'] ?? __( 'Run', NT_TEXT_DOMAIN ) ), 'secondary', 'submit', false );
		echo '</form></div>';
	}
	echo '</div>';
}
