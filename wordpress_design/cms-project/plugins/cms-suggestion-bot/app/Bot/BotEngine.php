<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Bot;

use CmsSuggestionBot\Repositories\ConversationRepository;
use CmsSuggestionBot\Repositories\KnowledgeRepository;
use CmsSuggestionBot\Repositories\MessageRepository;
use CmsSuggestionBot\Services\SettingsService;
use CmsSuggestionBot\Services\UsageLimitService;

defined( 'ABSPATH' ) || exit;

/**
 * Front door for a chat turn: enforces Configuration -> Usage Limits, pulls
 * recent conversation history so follow-up questions carry context, records
 * both sides of the exchange, and delegates the actual question -> answer
 * work to AnswerResolver. Ajax/API controllers call this - they never touch
 * AnswerResolver or the conversation repositories directly.
 */
final class BotEngine {

	private const HISTORY_LOOKBACK = 6;

	public function __construct(
		private readonly AnswerResolver $resolver,
		private readonly ConversationRepository $conversations,
		private readonly MessageRepository $messages,
		private readonly KnowledgeRepository $knowledge,
		private readonly UsageLimitService $usageLimits,
		private readonly SettingsService $settings,
	) {}

	public function isEnabled(): bool {
		return (bool) $this->settings->get( 'general', 'enabled', true );
	}

	/**
	 * @return array{answer:string, source:string, session_id:string, suggestion:array{title:string,url:string}|null, suggestions:array<int,array{title:string,url:string,excerpt:string}>|null}
	 */
	public function ask( string $question, string $session_id, string $visitor_key, ?int $user_id = null, ?int $post_id = null ): array {
		if ( ! $this->isEnabled() ) {
			return $this->response( '', 'disabled', $session_id );
		}

		if ( ! $this->usageLimits->isAllowed( $session_id, $visitor_key ) ) {
			return $this->response( $this->usageLimits->limitReachedMessage(), 'limit_reached', $session_id );
		}

		$conversation = $this->conversations->findOrCreate( $session_id, $user_id );

		// Pulled BEFORE the current question is recorded, so AnswerResolver
		// only ever sees messages that came strictly before it.
		$history = $this->messages->forConversation( (int) $conversation['id'], self::HISTORY_LOOKBACK );

		$this->messages->add( (int) $conversation['id'], 'user', $question );

		$result = $this->resolver->resolve( $question, $history, $post_id );

		if ( 'fallback' === $result['source'] ) {
			$this->knowledge->logUnanswered( $question );
		}

		$this->messages->add( (int) $conversation['id'], 'bot', $result['answer'], $result['source'] );
		$this->conversations->touch( (int) $conversation['id'] );
		$this->usageLimits->recordMessage( $session_id, $visitor_key );

		return $this->response( $result['answer'], $result['source'], $session_id, $result['suggestion'] ?? null, $result['suggestions'] ?? null );
	}

	public function welcomeMessage(): string {
		return (string) $this->settings->get( 'general', 'welcome_message', '' );
	}

	public function goodbyeMessage(): string {
		return (string) $this->settings->get( 'general', 'goodbye_message', '' );
	}

	/**
	 * @param array{title:string,url:string}|null $suggestion
	 * @param array<int,array{title:string,url:string,excerpt:string}>|null $suggestions
	 * @return array{answer:string, source:string, session_id:string, suggestion:array{title:string,url:string}|null, suggestions:array<int,array{title:string,url:string,excerpt:string}>|null}
	 */
	private function response( string $answer, string $source, string $session_id, ?array $suggestion = null, ?array $suggestions = null ): array {
		return array(
			'answer'      => $answer,
			'source'      => $source,
			'session_id'  => $session_id,
			'suggestion'  => $suggestion,
			'suggestions' => $suggestions,
		);
	}
}
