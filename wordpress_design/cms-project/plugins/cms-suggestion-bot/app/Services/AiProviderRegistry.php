<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Services;

use CmsSuggestionBot\Contracts\AiProviderInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Registry of available AiProviderInterface implementations, keyed by
 * provider id (e.g. "openai", "claude", "gemini", "ollama"). Empty by
 * default - no provider ships with this plugin. Adding real AI support
 * later means writing one class per provider (implementing
 * AiProviderInterface) and calling register() on it in
 * Core\Plugin::registerServices(); nothing else changes, including this
 * class, Bot\AnswerResolver, or the Configuration admin page.
 */
final class AiProviderRegistry {

	/** @var array<string, AiProviderInterface> */
	private array $providers = array();

	public function __construct( private readonly SettingsService $settings ) {}

	public function register( AiProviderInterface $provider ): void {
		$this->providers[ $provider->id() ] = $provider;
	}

	public function isEnabled(): bool {
		return (bool) $this->settings->get( 'ai_approach', 'enabled', false );
	}

	/**
	 * The provider selected in Configuration -> AI Approach, or null when AI
	 * mode is off / no matching provider is registered / it isn't configured.
	 */
	public function active(): ?AiProviderInterface {
		if ( ! $this->isEnabled() ) {
			return null;
		}

		$active_id = (string) $this->settings->get( 'ai_approach', 'active_provider', '' );
		$provider  = $this->providers[ $active_id ] ?? null;

		return ( $provider && $provider->isConfigured() ) ? $provider : null;
	}

	/**
	 * @return array<int, string> Provider ids with a registered implementation.
	 */
	public function registeredIds(): array {
		return array_keys( $this->providers );
	}

	/**
	 * @return array<string, array<string, mixed>> The configured (label/api_key/model/...)
	 *         list from Configuration, regardless of whether a class is registered for it yet.
	 */
	public function configuredProviders(): array {
		$providers = $this->settings->get( 'ai_approach', 'providers', array() );

		return is_array( $providers ) ? $providers : array();
	}
}
