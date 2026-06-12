<?php
/**
 * Admin UI - settings page and admin-bar status node.
 *
 * The settings page is intentionally rendered as raw PHP/HTML
 * (no React, no REST API) for simplicity and zero JS dependencies
 * beyond a tiny AJAX toggle.
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
 * Class AdminUI
 */
final class AdminUI {

	/** @var Settings */
	private Settings $settings;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings Shared settings service.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	// ─── Menu ────────────────────────────────────────────────────────────────

	/**
	 * Registers the top-level admin menu page.
	 *
	 * @return void
	 */
	public function register_menu(): void {
		add_menu_page(
			__( 'Site Mode Manager', 'site-mode-manager' ),
			__( 'Site Mode',         'site-mode-manager' ),
			'manage_options',
			'site-mode-manager',
			[ $this, 'render_settings_page' ],
			'dashicons-admin-site-alt3',
			3
		);
	}

	// ─── Settings Page ───────────────────────────────────────────────────────

	/**
	 * Renders the settings page HTML.
	 *
	 * @return void
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'site-mode-manager' ) );
		}

		$active_mode = $this->settings->get_active_mode();
		$all_modes   = $this->settings->get_all_modes();

		$mode_icons = [
			Settings::MODE_NORMAL       => '🟢',
			Settings::MODE_COMING_SOON  => '🟡',
			Settings::MODE_MAINTENANCE  => '🔴',
			Settings::MODE_HOLIDAY      => '🎄',
			Settings::MODE_PRIVATE_BETA => '🔒',
			Settings::MODE_LANDING_PAGE => '📄',
		];

		$mode_descriptions = [
			Settings::MODE_NORMAL       => __( 'WordPress and your active theme render normally. All pages are public.', 'site-mode-manager' ),
			Settings::MODE_COMING_SOON  => __( 'Visitors see the Coming Soon page (HTTP 200). Admins can browse freely.', 'site-mode-manager' ),
			Settings::MODE_MAINTENANCE  => __( 'Visitors see the Maintenance page (HTTP 503 + Retry-After). Admins can browse freely.', 'site-mode-manager' ),
			Settings::MODE_HOLIDAY      => __( 'Reserved - add your own template and handler.', 'site-mode-manager' ),
			Settings::MODE_PRIVATE_BETA => __( 'Reserved - add your own template and handler.', 'site-mode-manager' ),
			Settings::MODE_LANDING_PAGE => __( 'Reserved - add your own template and handler.', 'site-mode-manager' ),
		];
		?>
		<div class="wrap smm-admin-wrap">

			<h1 class="smm-page-title">
				<span class="dashicons dashicons-admin-site-alt3"></span>
				<?php esc_html_e( 'Site Mode Manager', 'site-mode-manager' ); ?>
			</h1>

			<p class="smm-subtitle">
				<?php esc_html_e( 'Click a mode card to switch the site state instantly. No page reload needed.', 'site-mode-manager' ); ?>
			</p>

			<div id="smm-save-feedback" class="smm-feedback" aria-live="polite"></div>

			<div class="smm-mode-grid" role="radiogroup" aria-label="<?php esc_attr_e( 'Site Mode', 'site-mode-manager' ); ?>">
				<?php foreach ( $all_modes as $mode_key => $mode_label ) :
					$is_active = ( $mode_key === $active_mode );
					$icon      = $mode_icons[ $mode_key ] ?? '⚙️';
					$desc      = $mode_descriptions[ $mode_key ] ?? '';
				?>
				<button
					class="smm-mode-card <?php echo $is_active ? 'smm-mode-card--active' : ''; ?>"
					data-mode="<?php echo esc_attr( $mode_key ); ?>"
					role="radio"
					aria-checked="<?php echo $is_active ? 'true' : 'false'; ?>"
					type="button"
				>
					<span class="smm-mode-icon" aria-hidden="true"><?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<span class="smm-mode-label"><?php echo esc_html( $mode_label ); ?></span>
					<span class="smm-mode-desc"><?php echo esc_html( $desc ); ?></span>
					<?php if ( $is_active ) : ?>
						<span class="smm-active-badge"><?php esc_html_e( '✓ Active', 'site-mode-manager' ); ?></span>
					<?php endif; ?>
				</button>
				<?php endforeach; ?>
			</div>

			<hr class="smm-divider">

			<details class="smm-info-box">
				<summary><?php esc_html_e( 'Developer notes', 'site-mode-manager' ); ?></summary>
				<ul>
					<li><?php esc_html_e( 'Admins always bypass mode restrictions and see the live site.', 'site-mode-manager' ); ?></li>
					<li><?php esc_html_e( 'wp-admin and wp-login.php are never blocked.', 'site-mode-manager' ); ?></li>
					<li><?php esc_html_e( 'Override via PHP: define( \'SMM_MAINTENANCE_MODE\', true ) in wp-config.php (takes priority over database).', 'site-mode-manager' ); ?></li>
					<li><?php esc_html_e( 'Theme overrides: place templates/site-mode-manager/coming-soon.php in your theme.', 'site-mode-manager' ); ?></li>
					<li><?php esc_html_e( 'Holiday / Private Beta / Landing Page modes are reserved - extend the router and add templates as needed.', 'site-mode-manager' ); ?></li>
				</ul>
			</details>

			<hr class="smm-divider">

			<!-- Custom HTML Editors Section -->
			<div class="smm-custom-html-sections">

				<!-- Coming Soon HTML Editor -->
				<div class="smm-custom-html-section">
					<h3><?php esc_html_e( '🚀 Coming Soon - Custom HTML', 'site-mode-manager' ); ?></h3>
					<p class="smm-section-description">
						<?php esc_html_e( 'Replace the default coming soon template with custom HTML.', 'site-mode-manager' ); ?>
					</p>

					<?php
					$coming_soon_html = $this->settings->get_custom_coming_soon_html();
					$nonce_cs = wp_create_nonce( 'smm_save_coming_soon_html' );
					?>

					<div class="smm-custom-html-editor" style="border: 1px solid #ddd; border-radius: 4px; overflow: hidden;">
						<textarea
							class="smm-custom-html-textarea"
							data-type="coming_soon"
							rows="12"
							style="width: 100%; padding: 12px; font-family: 'Courier New', monospace; font-size: 13px; border: none; resize: vertical;"
							placeholder="<?php esc_attr_e( 'Paste your custom HTML for coming soon page...', 'site-mode-manager' ); ?>"
						><?php echo esc_textarea( $coming_soon_html ); ?></textarea>
					</div>

					<div style="margin-top: 12px;">
						<button
							type="button"
							class="smm-save-custom-html button button-primary"
							data-type="coming_soon"
							data-nonce="<?php echo esc_attr( $nonce_cs ); ?>"
						>
							<?php esc_html_e( 'Save Coming Soon HTML', 'site-mode-manager' ); ?>
						</button>
						<span class="smm-custom-html-feedback" data-type="coming_soon" style="margin-left: 10px;"></span>
					</div>
				</div>

				<!-- Maintenance HTML Editor -->
				<div class="smm-custom-html-section">
					<h3><?php esc_html_e( '🔧 Maintenance - Custom HTML', 'site-mode-manager' ); ?></h3>
					<p class="smm-section-description">
						<?php esc_html_e( 'Replace the default maintenance template with custom HTML.', 'site-mode-manager' ); ?>
					</p>

					<?php
					$maintenance_html = $this->settings->get_custom_maintenance_html();
					$nonce_maint = wp_create_nonce( 'smm_save_maintenance_html' );
					?>

					<div class="smm-custom-html-editor" style="border: 1px solid #ddd; border-radius: 4px; overflow: hidden;">
						<textarea
							class="smm-custom-html-textarea"
							data-type="maintenance"
							rows="12"
							style="width: 100%; padding: 12px; font-family: 'Courier New', monospace; font-size: 13px; border: none; resize: vertical;"
							placeholder="<?php esc_attr_e( 'Paste your custom HTML for maintenance page...', 'site-mode-manager' ); ?>"
						><?php echo esc_textarea( $maintenance_html ); ?></textarea>
					</div>

					<div style="margin-top: 12px;">
						<button
							type="button"
							class="smm-save-custom-html button button-primary"
							data-type="maintenance"
							data-nonce="<?php echo esc_attr( $nonce_maint ); ?>"
						>
							<?php esc_html_e( 'Save Maintenance HTML', 'site-mode-manager' ); ?>
						</button>
						<span class="smm-custom-html-feedback" data-type="maintenance" style="margin-left: 10px;"></span>
					</div>
				</div>

				<!-- Custom Page HTML Editor -->
				<div class="smm-custom-html-section">
					<h3><?php esc_html_e( '📄 Custom Page - Custom HTML', 'site-mode-manager' ); ?></h3>
					<p class="smm-section-description">
						<?php esc_html_e( 'Add custom HTML for the custom page (landing page) mode.', 'site-mode-manager' ); ?>
					</p>

					<?php
					$page_html = $this->settings->get_custom_page_html();
					$nonce_page = wp_create_nonce( 'smm_save_page_html' );
					?>

					<div class="smm-custom-html-editor" style="border: 1px solid #ddd; border-radius: 4px; overflow: hidden;">
						<textarea
							class="smm-custom-html-textarea"
							data-type="page"
							rows="12"
							style="width: 100%; padding: 12px; font-family: 'Courier New', monospace; font-size: 13px; border: none; resize: vertical;"
							placeholder="<?php esc_attr_e( 'Paste your custom HTML for custom page...', 'site-mode-manager' ); ?>"
						><?php echo esc_textarea( $page_html ); ?></textarea>
					</div>

					<div style="margin-top: 12px;">
						<button
							type="button"
							class="smm-save-custom-html button button-primary"
							data-type="page"
							data-nonce="<?php echo esc_attr( $nonce_page ); ?>"
						>
							<?php esc_html_e( 'Save Custom Page HTML', 'site-mode-manager' ); ?>
						</button>
						<span class="smm-custom-html-feedback" data-type="page" style="margin-left: 10px;"></span>
					</div>
				</div>

				<p style="margin-top: 20px; color: #666; font-size: 12px;">
					<strong><?php esc_html_e( 'Tip:', 'site-mode-manager' ); ?></strong>
					<?php esc_html_e( 'You can include complete HTML documents or just body content. Make sure your HTML is properly formatted.', 'site-mode-manager' ); ?>
				</p>
			</div>

		</div><!-- .smm-admin-wrap -->
		<?php
	}

	// ─── Admin Bar Node ──────────────────────────────────────────────────────

	/**
	 * Adds a quick-status node to the WordPress admin bar.
	 *
	 * @param \WP_Admin_Bar $admin_bar WP admin bar object.
	 * @return void
	 */
	public function admin_bar_node( \WP_Admin_Bar $admin_bar ): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$mode        = $this->settings->get_active_mode();
		$all_modes   = $this->settings->get_all_modes();
		$mode_label  = $all_modes[ $mode ] ?? $mode;

		$status_icons = [
			Settings::MODE_NORMAL      => '🟢',
			Settings::MODE_COMING_SOON => '🟡',
			Settings::MODE_MAINTENANCE => '🔴',
		];
		$icon = $status_icons[ $mode ] ?? '⚙️';

		$admin_bar->add_node( [
			'id'    => 'smm-status',
			'title' => $icon . ' ' . esc_html( $mode_label ),
			'href'  => admin_url( 'admin.php?page=site-mode-manager' ),
			'meta'  => [
				'title' => __( 'Site Mode Manager - click to change mode', 'site-mode-manager' ),
			],
		] );
	}

	// ─── AJAX Handlers ───────────────────────────────────────────────────────

	/**
	 * Handles the wp_ajax_smm_toggle_mode AJAX request.
	 *
	 * @return void
	 */
	public function handle_ajax_toggle(): void {
		// Verify nonce.
		check_ajax_referer( 'smm_toggle_mode', 'nonce' );

		// Verify capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Insufficient permissions.', 'site-mode-manager' ) ], 403 );
		}

		$new_mode = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : '';
		if ( empty( $new_mode ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid mode.', 'site-mode-manager' ) ], 400 );
		}

		$saved = $this->settings->set_active_mode( $new_mode );
		if ( ! $saved ) {
			wp_send_json_error( [ 'message' => __( 'Could not save mode.', 'site-mode-manager' ) ], 500 );
		}

		$all_modes = $this->settings->get_all_modes();
		wp_send_json_success( [
			'mode'  => $new_mode,
			'label' => $all_modes[ $new_mode ] ?? $new_mode,
		] );
	}

	/**
	 * Handles the wp_ajax_smm_save_coming_soon_html AJAX request.
	 *
	 * @return void
	 */
	public function handle_save_coming_soon_html(): void {
		// Verify nonce.
		check_ajax_referer( 'smm_save_coming_soon_html', 'nonce' );

		// Verify capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Insufficient permissions.', 'site-mode-manager' ) ], 403 );
		}

		$custom_html = isset( $_POST['html'] ) ? wp_unslash( $_POST['html'] ) : '';
		if ( ! is_string( $custom_html ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid HTML content.', 'site-mode-manager' ) ], 400 );
		}

		$saved = $this->settings->set_custom_coming_soon_html( $custom_html );
		if ( ! $saved ) {
			wp_send_json_error( [ 'message' => __( 'Could not save custom HTML.', 'site-mode-manager' ) ], 500 );
		}

		wp_send_json_success( [ 'message' => __( 'Coming Soon HTML saved successfully.', 'site-mode-manager' ) ] );
	}

	/**
	 * Handles the wp_ajax_smm_save_maintenance_html AJAX request.
	 *
	 * @return void
	 */
	public function handle_save_maintenance_html(): void {
		// Verify nonce.
		check_ajax_referer( 'smm_save_maintenance_html', 'nonce' );

		// Verify capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Insufficient permissions.', 'site-mode-manager' ) ], 403 );
		}

		$custom_html = isset( $_POST['html'] ) ? wp_unslash( $_POST['html'] ) : '';
		if ( ! is_string( $custom_html ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid HTML content.', 'site-mode-manager' ) ], 400 );
		}

		$saved = $this->settings->set_custom_maintenance_html( $custom_html );
		if ( ! $saved ) {
			wp_send_json_error( [ 'message' => __( 'Could not save custom HTML.', 'site-mode-manager' ) ], 500 );
		}

		wp_send_json_success( [ 'message' => __( 'Maintenance HTML saved successfully.', 'site-mode-manager' ) ] );
	}

	/**
	 * Handles the wp_ajax_smm_save_page_html AJAX request.
	 *
	 * @return void
	 */
	public function handle_save_page_html(): void {
		// Verify nonce.
		check_ajax_referer( 'smm_save_page_html', 'nonce' );

		// Verify capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Insufficient permissions.', 'site-mode-manager' ) ], 403 );
		}

		$custom_html = isset( $_POST['html'] ) ? wp_unslash( $_POST['html'] ) : '';
		if ( ! is_string( $custom_html ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid HTML content.', 'site-mode-manager' ) ], 400 );
		}

		$saved = $this->settings->set_custom_page_html( $custom_html );
		if ( ! $saved ) {
			wp_send_json_error( [ 'message' => __( 'Could not save custom HTML.', 'site-mode-manager' ) ], 500 );
		}

		wp_send_json_success( [ 'message' => __( 'Custom Page HTML saved successfully.', 'site-mode-manager' ) ] );
	}
}
