<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Front;

use CmsSuggestionBot\Services\AiProviderRegistry;
use CmsSuggestionBot\Services\SettingsService;

defined( 'ABSPATH' ) || exit;

/**
 * Renders the floating chat widget on the front end and hands its config
 * (texts, colors, identity label) to assets/js/chat-widget.js, which drives
 * all the actual interaction against Ajax\BotAjaxController.
 */
final class ChatWidget {

	public function __construct(
		private readonly SettingsService $settings,
		private readonly AiProviderRegistry $aiProviders,
	) {}

	public function hooks(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_footer', array( $this, 'render' ) );
	}

	public function enqueue(): void {
		if ( ! $this->isEnabled() ) {
			return;
		}

		wp_enqueue_style( 'csb-chat-widget', CSB_PLUGIN_URL . '/assets/css/chat-widget.css', array(), CSB_VERSION );
		wp_enqueue_script( 'csb-chat-widget', CSB_PLUGIN_URL . '/assets/js/chat-widget.js', array(), CSB_VERSION, true );

		$general = $this->settings->group( 'general' );
		$post_context = $this->getPostContext();

		wp_localize_script( 'csb-chat-widget', 'csbChat', array(
			'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
			'nonce'           => wp_create_nonce( 'csb_chat' ),
			'botName'         => $general['bot_name'],
			'botDescription'  => $general['bot_description'],
			'showDescription' => (bool) ( $general['show_description'] ?? true ),
			'botIcon'         => $general['bot_icon'],
			'identityLabel'   => $this->identityLabel( $general['identity_label'] ),
			'themeColor'      => $general['theme_color'],
			'backgroundColor' => $general['background_color'],
			'textColor'       => $general['text_color'],
			'welcomeMessage'  => $general['welcome_message'],
			'goodbyeMessage'  => $general['goodbye_message'],
			'thinkingMessage' => $general['thinking_message'],
			'typingSpeedMs'   => (int) $general['typing_speed_ms'],
			'postContext'     => $post_context,
		) );
	}

	/**
	 * Detect if we're on a single post/page and return context info.
	 */
	private function getPostContext(): ?array {
		if ( ! is_singular() ) {
			return null;
		}

		$post = get_post();
		if ( ! $post || 'publish' !== $post->post_status ) {
			return null;
		}

		return array(
			'id'    => (int) $post->ID,
			'title' => get_the_title( $post ),
			'url'   => get_permalink( $post ),
			'type'  => get_post_type( $post ),
		);
	}

	public function render(): void {
		if ( ! $this->isEnabled() ) {
			return;
		}

		include CSB_PLUGIN_DIR . '/templates/chat-widget.php';
	}

	private function isEnabled(): bool {
		return (bool) $this->settings->get( 'general', 'enabled', true );
	}

	private function identityLabel( string $configured ): string {
		if ( '' !== $configured ) {
			return $configured;
		}

		return $this->aiProviders->isEnabled() && $this->aiProviders->active()
			? __( 'AI Assistant', 'cms-suggestion-bot' )
			: __( 'Suggestion Bot', 'cms-suggestion-bot' );
	}
}
