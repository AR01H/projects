<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Admin;

use CmsSuggestionBot\Admin\Pages\AdminToolsPage;
use CmsSuggestionBot\Admin\Pages\ApiPage;
use CmsSuggestionBot\Admin\Pages\ConfigurationPage;
use CmsSuggestionBot\Admin\Pages\DashboardPage;
use CmsSuggestionBot\Admin\Pages\HelpPage;
use CmsSuggestionBot\Admin\Pages\KnowledgeBasePage;
use CmsSuggestionBot\Admin\Pages\LogsPage;
use CmsSuggestionBot\Admin\Pages\ReaderPage;
use CmsSuggestionBot\Admin\Pages\SettingsPage;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the top-level "CMS Suggestion Bot" admin menu and its nine
 * submenus. Each submenu delegates rendering to one Admin\Pages\* class
 * (single responsibility: this class only wires slugs to page objects).
 */
final class Menu {

	public function __construct(
		private readonly DashboardPage $dashboard,
		private readonly AdminToolsPage $adminTools,
		private readonly ConfigurationPage $configuration,
		private readonly ReaderPage $reader,
		private readonly KnowledgeBasePage $knowledgeBase,
		private readonly LogsPage $logs,
		private readonly ApiPage $api,
		private readonly SettingsPage $settings,
		private readonly HelpPage $help,
	) {}

	public function hooks(): void {
		add_action( 'admin_menu', array( $this, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAssets' ) );
	}

	public function enqueueAssets( string $hook ): void {
		// Only load on our plugin pages
		if ( strpos( $hook, CSB_MENU_SLUG ) === false ) {
			return;
		}

		wp_enqueue_style( 'csb-admin', CSB_PLUGIN_URL . '/assets/css/admin.css', array(), CSB_VERSION );
	}

	public function register(): void {
		add_menu_page(
			__( 'CMS Suggestion Bot', 'cms-suggestion-bot' ),
			__( 'CMS Sug BOT', 'cms-suggestion-bot' ),
			CSB_CAPABILITY,
			CSB_MENU_SLUG,
			array( $this->dashboard, 'render' ),
			'dashicons-format-chat',
			58
		);

		$submenus = array(
			'dashboard'      => array( __( 'Dashboard', 'cms-suggestion-bot' ), $this->dashboard ),
			'admin-tools'    => array( __( 'Admin Tools', 'cms-suggestion-bot' ), $this->adminTools ),
			'configuration'  => array( __( 'Configuration', 'cms-suggestion-bot' ), $this->configuration ),
			'reader'         => array( __( 'Reader', 'cms-suggestion-bot' ), $this->reader ),
			'knowledge-base' => array( __( 'Knowledge Base', 'cms-suggestion-bot' ), $this->knowledgeBase ),
			'logs'           => array( __( 'Logs', 'cms-suggestion-bot' ), $this->logs ),
			'api'            => array( __( 'API', 'cms-suggestion-bot' ), $this->api ),
			'settings'       => array( __( 'Settings', 'cms-suggestion-bot' ), $this->settings ),
			'help'           => array( __( 'Help', 'cms-suggestion-bot' ), $this->help ),
		);

		foreach ( $submenus as $slug => [ $label, $page ] ) {
			add_submenu_page(
				CSB_MENU_SLUG,
				$label,
				$label,
				CSB_CAPABILITY,
				'dashboard' === $slug ? CSB_MENU_SLUG : CSB_MENU_SLUG . '-' . $slug,
				array( $page, 'render' )
			);
		}
	}
}
