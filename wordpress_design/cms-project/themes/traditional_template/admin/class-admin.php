<?php
/**
 * admin/class-admin.php - Main admin menu registry for Traditional Template.
 * Each site section gets its own top-level submenu with contextual subtabs.
 */
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/class-admin-ui.php';
require_once __DIR__ . '/Handlers/BaseHandler.php';
require_once __DIR__ . '/Handlers/FaqHandler.php';
require_once __DIR__ . '/Handlers/ReviewHandler.php';
require_once __DIR__ . '/Handlers/TeamHandler.php';
require_once __DIR__ . '/Handlers/LocationHandler.php';
require_once __DIR__ . '/Handlers/HistoryHandler.php';
require_once __DIR__ . '/Handlers/DrinksHandler.php';
require_once __DIR__ . '/Handlers/EventsFeaturesHandler.php';
require_once __DIR__ . '/Handlers/NavHandler.php';
require_once __DIR__ . '/Handlers/SettingsHandler.php';
require_once __DIR__ . '/Handlers/GalleryHandler.php';
require_once __DIR__ . '/Handlers/ValuesHandler.php';
require_once __DIR__ . '/Handlers/CertificationsHandler.php';
require_once __DIR__ . '/Handlers/LogoStripHandler.php';
require_once __DIR__ . '/Handlers/TickerHandler.php';
require_once __DIR__ . '/Handlers/ProcessStepsHandler.php';
require_once __DIR__ . '/Handlers/DeliveryProductsHandler.php';
require_once __DIR__ . '/Handlers/NewsbarHandler.php';
require_once __DIR__ . '/Handlers/SpotlightsHandler.php';
require_once __DIR__ . '/Handlers/CustomCodeHandler.php';
require_once __DIR__ . '/Handlers/RedirectsHandler.php';
require_once __DIR__ . '/Handlers/AdminShortcutsHandler.php';
require_once __DIR__ . '/Handlers/VisitorStatsHandler.php';
require_once __DIR__ . '/Handlers/WorkflowsHandler.php';
require_once __DIR__ . '/Handlers/UtilitiesHandler.php';

require_once __DIR__ . '/Models/NewsbarModel.php';
require_once __DIR__ . '/Models/SpotlightsModel.php';
require_once __DIR__ . '/Models/CustomCodeModel.php';
require_once __DIR__ . '/Models/RedirectsModel.php';
require_once __DIR__ . '/Models/AdminShortcutsModel.php';
require_once __DIR__ . '/Models/VisitorStatsModel.php';
require_once __DIR__ . '/Models/WorkflowsModel.php';
require_once __DIR__ . '/Handlers/FormsHandler.php';

class App_Admin {

	const MENU_SLUG  = TT_Base_Handler::MENU_SLUG;
	const CAPABILITY = TT_Base_Handler::CAPABILITY;

	public static function init(): void {
		add_action( 'admin_menu',            array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_init',            array( __CLASS__, 'maybe_redirect' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );

		// --- POST action hooks ---
		add_action( 'admin_post_tt_save_faq',           array( 'TT_Faq_Handler',             'handle_save' ) );
		add_action( 'admin_post_tt_delete_faq',         array( 'TT_Faq_Handler',             'handle_delete' ) );
		add_action( 'admin_post_tt_save_review',        array( 'TT_Review_Handler',          'handle_save' ) );
		add_action( 'admin_post_tt_delete_review',      array( 'TT_Review_Handler',          'handle_delete' ) );
		add_action( 'admin_post_tt_save_team',          array( 'TT_Team_Handler',            'handle_save' ) );
		add_action( 'admin_post_tt_delete_team',        array( 'TT_Team_Handler',            'handle_delete' ) );
		add_action( 'admin_post_tt_save_location',      array( 'TT_Location_Handler',        'handle_save' ) );
		add_action( 'admin_post_tt_delete_location',    array( 'TT_Location_Handler',        'handle_delete' ) );
		add_action( 'admin_post_tt_save_history',       array( 'TT_History_Handler',         'handle_save' ) );
		add_action( 'admin_post_tt_delete_history',     array( 'TT_History_Handler',         'handle_delete' ) );
		add_action( 'admin_post_tt_save_drink',         array( 'TT_Drinks_Handler',          'handle_save' ) );
		add_action( 'admin_post_tt_delete_drink',       array( 'TT_Drinks_Handler',          'handle_delete' ) );
		add_action( 'admin_post_tt_save_event_feature', array( 'TT_Events_Features_Handler', 'handle_save' ) );
		add_action( 'admin_post_tt_delete_event_feature', array( 'TT_Events_Features_Handler', 'handle_delete' ) );
		add_action( 'admin_post_tt_save_nav',              array( 'TT_Nav_Handler',                 'handle_save' ) );
		add_action( 'admin_post_tt_delete_nav',            array( 'TT_Nav_Handler',                 'handle_delete' ) );
		add_action( 'admin_post_tt_save_settings',         array( 'TT_Settings_Handler',            'handle_save' ) );
		add_action( 'admin_post_tt_save_gallery_image',    array( 'TT_Gallery_Handler',             'handle_save' ) );
		add_action( 'admin_post_tt_delete_gallery_image',  array( 'TT_Gallery_Handler',             'handle_delete' ) );
		add_action( 'admin_post_tt_save_value',            array( 'TT_Values_Handler',              'handle_save' ) );
		add_action( 'admin_post_tt_delete_value',          array( 'TT_Values_Handler',              'handle_delete' ) );
		add_action( 'admin_post_tt_save_certification',    array( 'TT_Certifications_Handler',      'handle_save' ) );
		add_action( 'admin_post_tt_delete_certification',  array( 'TT_Certifications_Handler',      'handle_delete' ) );
		add_action( 'admin_post_tt_save_logo_strip',       array( 'TT_Logo_Strip_Handler',          'handle_save' ) );
		add_action( 'admin_post_tt_delete_logo_strip',     array( 'TT_Logo_Strip_Handler',          'handle_delete' ) );
		add_action( 'admin_post_tt_save_ticker_item',      array( 'TT_Ticker_Handler',              'handle_save' ) );
		add_action( 'admin_post_tt_delete_ticker_item',    array( 'TT_Ticker_Handler',              'handle_delete' ) );
		add_action( 'admin_post_tt_save_process_step',     array( 'TT_Process_Steps_Handler',       'handle_save' ) );
		add_action( 'admin_post_tt_delete_process_step',   array( 'TT_Process_Steps_Handler',       'handle_delete' ) );
		add_action( 'admin_post_tt_save_delivery_product', array( 'TT_Delivery_Products_Handler',   'handle_save' ) );
		add_action( 'admin_post_tt_delete_delivery_product', array( 'TT_Delivery_Products_Handler', 'handle_delete' ) );
		add_action( 'admin_post_tt_save_form', array( 'TT_Forms_Handler', 'handle_save_form' ) );
		add_action( 'admin_post_tt_save_form_field', array( 'TT_Forms_Handler', 'handle_save_field' ) );
		add_action( 'admin_post_tt_delete_form_field', array( 'TT_Forms_Handler', 'handle_delete_field' ) );
		add_action( 'admin_post_tt_update_newsbar', array( 'TT_Newsbar_Handler', 'handle_request' ) );
		add_action( 'admin_post_tt_update_spotlights', array( 'TT_Spotlights_Handler', 'handle_request' ) );
		add_action( 'admin_post_tt_update_custom_code', array( 'TT_Custom_Code_Handler', 'handle_request' ) );
		add_action( 'admin_post_tt_update_redirects', array( 'TT_Redirects_Handler', 'handle_request' ) );
		add_action( 'admin_post_tt_update_shortcuts', array( 'TT_Admin_Shortcuts_Handler', 'handle_request' ) );
		add_action( 'admin_post_tt_update_visitor_stats', array( 'TT_Visitor_Stats_Handler', 'handle_request' ) );
		add_action( 'admin_post_tt_update_workflows', array( 'TT_Workflows_Handler', 'handle_request' ) );
		add_action( 'admin_post_tt_run_utility', array( 'TT_Utilities_Handler', 'handle_request' ) );
	}

	
	private static function add_forms_menu(): void {
		add_menu_page( 'Forms', self::fa('fa-wpforms', 'Forms'), self::CAPABILITY, 'tt-forms', function(){ self::render_tabs('tt-forms', [
			'list' => 'All Forms',
		], 'forms'); }, 'dashicons-feedback', 57 );
	}

	// -------------------------------------------------------------------------
	// Icon helper
	// -------------------------------------------------------------------------
	private static function fa( string $icon, string $label ): string {
		return '<i class="fa-solid ' . esc_attr( $icon ) . '" style="margin-right:6px;opacity:.75;"></i>' . esc_html( $label );
	}

	// -------------------------------------------------------------------------
	// MENU DEFINITION
	// Each top-level key becomes a WordPress submenu page.
	// Subtabs are rendered as nav-tab-wrapper tabs inside the page.
	// -------------------------------------------------------------------------
	public static function menus(): array {
		return array(

			// 1. Dashboard
			'dashboard' => array(
				'label' => self::fa( 'fa-gauge-high', 'Dashboard' ),
				'view'  => 'TabDashboard.php',
			),

			// 2. Home Page
			'home' => array(
				'label'   => self::fa( 'fa-house', 'Home Page' ),
				'subtabs' => array(
					'hero'       => array( 'label' => '🏠 Hero Banner',   'view' => 'home/SubHero.php' ),
					'drinks'     => array( 'label' => '🥤 Juices / Drinks', 'view' => 'home/SubDrinks.php' ),
					'story'      => array( 'label' => '📖 Our Story',       'view' => 'home/SubStory.php' ),
					'home_media' => array( 'label' => '🎞️ Media / Carousel', 'view' => 'home/SubHomeMedia.php' ),
				),
			),

			// 3. Events & Catering
			'events' => array(
				'label'   => self::fa( 'fa-calendar-star', 'Events' ),
				'subtabs' => array(
					'events_settings' => array( 'label' => '⚙️ Settings',        'view' => 'events/SubSettings.php' ),
					'event_features'  => array( 'label' => '✅ Feature Bullets',  'view' => 'events/SubFeatures.php' ),
					'events_faqs'     => array( 'label' => '❓ FAQs',             'view' => 'events/SubFaqs.php' ),
					'events_reviews'  => array( 'label' => '⭐ Reviews',          'view' => 'events/SubReviews.php' ),
				),
			),

			// 4. Franchise
			'franchise' => array(
				'label'   => self::fa( 'fa-store', 'Franchise' ),
				'subtabs' => array(
					'franchise_settings' => array( 'label' => '⚙️ Settings',    'view' => 'franchise/SubSettings.php' ),
					'franchise_faqs'     => array( 'label' => '❓ FAQs',         'view' => 'franchise/SubFaqs.php' ),
					'franchise_reviews'  => array( 'label' => '⭐ Reviews',      'view' => 'franchise/SubReviews.php' ),
					'hire_packages'      => array( 'label' => '📦 Hire Packages', 'view' => 'franchise/SubHirePackages.php' ),
					'pricing'            => array( 'label' => '💰 Pricing Tiers', 'view' => 'franchise/SubPricing.php' ),
				),
			),

			// 5. Products & Order
			'products' => array(
				'label'   => self::fa( 'fa-box', 'Products' ),
				'subtabs' => array(
					'flavours'         => array( 'label' => '🫙 Flavours',           'view' => 'products/SubFlavours.php' ),
					'delivery'         => array( 'label' => '🚚 Delivery Products', 'view' => 'products/SubDelivery.php' ),
					'order_faqs'       => array( 'label' => '❓ Order FAQs',          'view' => 'products/SubFaqs.php' ),
					'order_reviews'    => array( 'label' => '⭐ Order Reviews',       'view' => 'products/SubReviews.php' ),
					'process_steps'    => array( 'label' => '🔢 Process Steps',      'view' => 'products/SubProcessSteps.php' ),
				),
			),

			// 6. Global Content
			'global_content' => array(
				'label'   => self::fa( 'fa-layer-group', 'Global Content' ),
				'subtabs' => array(
					'global_faqs'     => array( 'label' => '❓ All FAQs',     'view' => 'global/SubFaqs.php' ),
					'global_reviews'  => array( 'label' => '⭐ All Reviews',   'view' => 'global/SubReviews.php' ),
					'team'            => array( 'label' => '👥 Team',          'view' => 'global/SubTeam.php' ),
					'locations'       => array( 'label' => '📍 Locations',     'view' => 'global/SubLocations.php' ),
					'history'         => array( 'label' => '🏛️ History',        'view' => 'global/SubHistory.php' ),
					'gallery'         => array( 'label' => '🖼️ Gallery',        'view' => 'global/SubGallery.php' ),
					'values'          => array( 'label' => '💎 Our Values',    'view' => 'global/SubValues.php' ),
					'certifications'  => array( 'label' => '🏅 Certifications', 'view' => 'global/SubCertifications.php' ),
					'logo_strip'      => array( 'label' => '🏷️ Logo Strip',     'view' => 'global/SubLogoStrip.php' ),
					'ticker'          => array( 'label' => '📢 Ticker',         'view' => 'global/SubTicker.php' ),
					'stats'           => array( 'label' => '📊 Stats',          'view' => 'global/SubStats.php' ),
					'opening_hours'   => array( 'label' => '🕐 Opening Hours',  'view' => 'global/SubOpeningHours.php' ),
				),
			),

			// 7. Site Settings
			'site_settings' => array(
				'label'   => self::fa( 'fa-gear', 'Site Settings' ),
				'subtabs' => array(
					'brand'    => array( 'label' => '🏷️ Brand & Logo',  'view' => 'site/SubBrand.php' ),
					'contact'  => array( 'label' => '📞 Contact Info',   'view' => 'site/SubContact.php' ),
					'cookies'  => array( 'label' => '🍪 Cookies',        'view' => 'site/SubCookies.php' ),
					'legal'    => array( 'label' => '⚖️ Legal Pages',    'view' => 'site/SubLegal.php' ),
					'social'   => array( 'label' => '👍 Social Media',   'view' => 'site/SubSocial.php' ),
				),
			),

			// 8. Advanced CMS (Plugin Features)
			'advanced' => array(
				'label'   => self::fa( 'fa-bolt', 'Advanced CMS' ),
				'subtabs' => array(
					'newsbar'    => array( 'label' => '📰 Newsbar',       'view' => 'advanced/SubNewsbar.php' ),
					'spotlights' => array( 'label' => '✨ Spotlights',    'view' => 'advanced/SubSpotlights.php' ),
					'redirects'  => array( 'label' => '🔀 Redirects',     'view' => 'advanced/SubRedirects.php' ),
					'customcode' => array( 'label' => '💻 Custom Code',   'view' => 'advanced/SubCustomCode.php' ),
					'shortcuts'  => array( 'label' => '⚡ Shortcuts',     'view' => 'advanced/SubShortcuts.php' ),
					'stats'      => array( 'label' => '📈 Visitor Stats', 'view' => 'advanced/SubVisitorStats.php' ),
					'workflows'  => array( 'label' => '📋 Workflows',     'view' => 'advanced/SubWorkflows.php' ),
				),
			),

		);
	}

	// -------------------------------------------------------------------------
	// Register menus
	// -------------------------------------------------------------------------
	public static function register_menu(): void {
		// Main menu item
		add_menu_page(
			'TT Admin', 'TT Admin', self::CAPABILITY, self::MENU_SLUG,
			array( __CLASS__, 'render_page' ), 'dashicons-admin-site-alt2', 3
		);

		$menus = self::menus();
		$first = array_key_first( $menus );

		foreach ( $menus as $key => $def ) {
			$slug = ( $key === $first ) ? self::MENU_SLUG : self::MENU_SLUG . '-' . $key;
			add_submenu_page(
				self::MENU_SLUG,
				wp_strip_all_tags( $def['label'] ),
				$def['label'],
				self::CAPABILITY,
				$slug,
				array( __CLASS__, 'render_page' )
			);
		}
	}

	// -------------------------------------------------------------------------
	// Enqueue assets
	// -------------------------------------------------------------------------
	public static function enqueue_assets( string $hook ): void {
		if ( false === strpos( $hook, self::MENU_SLUG ) ) { return; }
		wp_enqueue_media();
		wp_enqueue_style( 'tt-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css', array(), '6.5.2' );
		wp_enqueue_style( 'tt-admin', get_template_directory_uri() . '/admin/assets/admin.css', array(), '1.1' );
	}

	// -------------------------------------------------------------------------
	// Redirect to first subtab if none specified
	// -------------------------------------------------------------------------
	public static function maybe_redirect(): void {
		if ( ! is_admin() || ! current_user_can( self::CAPABILITY ) ) { return; }
		$page = sanitize_key( wp_unslash( $_GET['page'] ?? '' ) );
		if ( '' === $page ) { return; }

		$menus   = self::menus();
		$matched = '';
		foreach ( $menus as $k => $d ) {
			$slug = ( $k === array_key_first( $menus ) ) ? self::MENU_SLUG : self::MENU_SLUG . '-' . $k;
			if ( $slug === $page ) { $matched = $k; break; }
		}
		if ( '' === $matched || empty( $menus[ $matched ]['subtabs'] ) ) { return; }

		$req_sub   = sanitize_key( wp_unslash( $_GET['subtab'] ?? '' ) );
		if ( '' !== $req_sub && isset( $menus[ $matched ]['subtabs'][ $req_sub ] ) ) { return; }

		$first_sub = (string) array_key_first( $menus[ $matched ]['subtabs'] );
		if ( '' !== $first_sub ) {
			$slug = ( $matched === array_key_first( $menus ) ) ? self::MENU_SLUG : self::MENU_SLUG . '-' . $matched;
			wp_safe_redirect( add_query_arg( array( 'page' => $slug, 'subtab' => $first_sub ), admin_url( 'admin.php' ) ) );
			exit;
		}
	}

	// -------------------------------------------------------------------------
	// Render page
	// -------------------------------------------------------------------------
	public static function render_page(): void {
		$menus  = self::menus();
		$active = array_key_first( $menus );
		$page   = sanitize_key( wp_unslash( $_GET['page'] ?? '' ) );

		foreach ( $menus as $k => $d ) {
			$slug = ( $k === array_key_first( $menus ) ) ? self::MENU_SLUG : self::MENU_SLUG . '-' . $k;
			if ( $slug === $page ) { $active = $k; break; }
		}

		$active_sub = sanitize_key( wp_unslash( $_GET['subtab'] ?? '' ) );
		if ( empty( $active_sub ) && ! empty( $menus[ $active ]['subtabs'] ) ) {
			$active_sub = array_key_first( $menus[ $active ]['subtabs'] );
		}

		$view = ! empty( $menus[ $active ]['subtabs'] )
			? ( $menus[ $active ]['subtabs'][ $active_sub ]['view'] ?? '' )
			: ( $menus[ $active ]['view'] ?? '' );

		echo '<div class="wrap tt-admin-wrap">';

		// Notices
		if ( ! empty( $_GET['tt_err'] ) ) {
			$msg = sanitize_text_field( wp_unslash( $_GET['tt_msg'] ?? 'An error occurred.' ) );
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $msg ) . '</p></div>';
		} elseif ( ! empty( $_GET['tt_done'] ) ) {
			$msg = sanitize_text_field( wp_unslash( $_GET['tt_msg'] ?? 'Saved successfully.' ) );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $msg ) . '</p></div>';
		}

		// Page title
		$allowed_tags = array( 'i' => array( 'class' => array(), 'style' => array() ) );
		echo '<h1>' . wp_kses( $menus[ $active ]['label'], $allowed_tags ) . '</h1>';

		// Subtabs nav
		if ( ! empty( $menus[ $active ]['subtabs'] ) ) {
			echo '<h2 class="nav-tab-wrapper" style="margin-bottom:20px;">';
			foreach ( $menus[ $active ]['subtabs'] as $skey => $sdef ) {
				$slug = ( $active === array_key_first( $menus ) ) ? self::MENU_SLUG : self::MENU_SLUG . '-' . $active;
				$url  = add_query_arg( array( 'page' => $slug, 'subtab' => $skey ), admin_url( 'admin.php' ) );
				$cls  = ( $skey === $active_sub ) ? 'nav-tab-active' : '';
				echo '<a href="' . esc_url( $url ) . '" class="nav-tab ' . $cls . '">'
					. wp_kses( $sdef['label'], $allowed_tags )
					. '</a>';
			}
			echo '</h2>';
		}

		// View body
		echo '<div class="tt-tab-body">';
		$path = __DIR__ . '/tabs/' . $view;
		if ( file_exists( $path ) ) {
			require $path;
		} else {
			echo '<div class="notice notice-warning"><p>View not found: <code>' . esc_html( $view ) . '</code></p></div>';
		}
		echo '</div></div>';
	}
}
