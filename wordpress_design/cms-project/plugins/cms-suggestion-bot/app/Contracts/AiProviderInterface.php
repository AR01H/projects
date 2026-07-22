<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Contracts;

defined( 'ABSPATH' ) || exit;

/**
 * Future extension point for OpenAI / Claude / Gemini / Ollama / local LLM /
 * MCP providers (see the project's "Future AI Features" requirement). No
 * implementation ships yet - Bot\AnswerResolver works purely off cached
 * content and the knowledge base until an AI approach is enabled in
 * Configuration and a provider class is bound in Core\Plugin::registerServices().
 */
interface AiProviderInterface {

	/**
	 * Machine-readable identifier, e.g. "openai", "claude", "ollama".
	 */
	public function id(): string;

	/**
	 * Whether this provider is fully configured (API key present, endpoint
	 * reachable, etc.) and safe to route questions to.
	 */
	public function isConfigured(): bool;

	/**
	 * @param array<int, array<string, mixed>> $context Matched cache/knowledge
	 *        entries the non-AI resolver already found, given to the provider
	 *        as grounding context (a lightweight RAG hand-off).
	 * @return string The generated answer.
	 */
	public function answer( string $question, array $context = array() ): string;
}
