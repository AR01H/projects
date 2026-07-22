<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Bot;

use CmsSuggestionBot\Repositories\ChunkRepository;
use CmsSuggestionBot\Repositories\KnowledgeRepository;
use CmsSuggestionBot\Services\AiProviderRegistry;
use CmsSuggestionBot\Services\CommonQuestionsService;
use CmsSuggestionBot\Services\GreetingsService;
use CmsSuggestionBot\Services\RestrictedWordsService;
use CmsSuggestionBot\Services\SettingsService;

defined( 'ABSPATH' ) || exit;

/**
 * Turns one question into one answer - plus, when the match is a page/post,
 * a structured page/post suggestion (title+url) the front-end renders as a
 * clickable link, separately from the prose answer text.
 *
 * Also acts as a Post Suggester: when content matches, it returns multiple
 * related article suggestions (by category/tag overlap and content similarity)
 * so visitors discover more of the site's content even from a single query.
 *
 * Checked in order, fastest/cheapest first, so the common case never
 * touches a full-text query:
 *
 *   1. Restricted words   - reject in-memory, no DB hit at all.
 *   2. Greetings           - small talk, in-memory match (Services\GreetingsService).
 *   3. Common Questions    - object-cache-backed FAQ lookup (Services\CommonQuestionsService).
 *   4. Knowledge base      - FULLTEXT match on cms_sug_bot_knowledge.
 *   5. Cached content      - FULLTEXT match on cms_sug_bot_chunks (pages/posts/files)
 *                            + related post suggestions from category/tag/content similarity.
 *   6. Related fallback    - even when no direct answer, surface related posts via FULLTEXT.
 *   7. AI provider         - only if enabled + configured, given 4/5's matches as context.
 *   8. Fallback            - "I don't have an answer for that yet."
 *
 * Conversation memory: the current message always gets tried alone FIRST for
 * steps 4/5 (it's what the visitor is actually asking right now, so it must
 * win when it's enough on its own); only if that finds nothing does a second
 * pass fold in a short window of the visitor's own recent messages (see
 * buildContextualQuery()), so a follow-up like "where is it" still matches
 * the topic of the previous turn without needing an AI model to resolve the
 * pronoun - without older context ever outweighing what's being asked right now.
 */
final class AnswerResolver {

	private const HISTORY_WINDOW = 3;

	public function __construct(
		private readonly RestrictedWordsService $restrictedWords,
		private readonly GreetingsService $greetings,
		private readonly CommonQuestionsService $commonQuestions,
		private readonly KnowledgeRepository $knowledge,
		private readonly ChunkRepository $chunks,
		private readonly AiProviderRegistry $aiProviders,
		private readonly SettingsService $settings,
	) {}

	/**
	 * @param array<int, array{role:string, message:string}> $history Recent
	 *        messages for this conversation, oldest first, NOT including the
	 *        current $question (see Bot\BotEngine::ask()).
	 * @param int|null $post_id The current post/page ID when on a singular view.
	 * @return array{answer:string, source:string, matched_id:int|null, suggestion:array{title:string,url:string}|null, suggestions:array<int,array{title:string,url:string,excerpt:string}>|null}
	 */
	public function resolve( string $question, array $history = array(), ?int $post_id = null ): array {
		$question = trim( $question );

		if ( '' === $question ) {
			return $this->fallback();
		}

		if ( $this->restrictedWords->contains( $question ) ) {
			return array(
				'answer'      => __( "I can't help with that question.", 'cms-suggestion-bot' ),
				'source'      => 'restricted',
				'matched_id'  => null,
				'suggestion'  => null,
				'suggestions' => null,
			);
		}

		if ( $greeting = $this->greetings->match( $question ) ) {
			return $this->finalize( $greeting, 'greeting', null );
		}

		// Exact/near-exact common-question matches use the raw question only -
		// history would just add noise to what's meant to be a precise lookup.
		if ( $common = $this->commonQuestions->match( $question ) ) {
			return $this->finalize( (string) $common['answer'], 'common_question', (int) $common['id'] );
		}

		// Links-only mode: return only post/page links without verbose answers.
		if ( $this->settings->get( 'behaviour', 'links_only_mode', false ) ) {
			return $this->linksOnlyResponse( $question );
		}

		// If we're on a specific post/page, try that post's content first.
		if ( $post_id ) {
			if ( $result = $this->searchPostContext( $question, $post_id ) ) {
				return $result;
			}
			// No match in this post's content — fall through to search
			// the full knowledge base + cache so the visitor still gets
			// suggestions from other content on the site.
		}

		// Not on a post page - search everywhere
		if ( $result = $this->searchKnowledgeAndCache( $question ) ) {
			return $result;
		}

		// Nothing matched the current message alone - retry once with recent
		// conversation context blended in, for referential follow-ups.
		if ( ! empty( $history ) ) {
			$contextual = $this->buildContextualQuery( $question, $history );
			if ( $contextual !== $question && $result = $this->searchKnowledgeAndCache( $question, $contextual ) ) {
				return $result;
			}
		}

		// Even when the main answer misses, try to surface related posts so
		// the visitor still gets useful article suggestions.
		if ( $related = $this->findRelatedFallback( $question ) ) {
			return $related;
		}

		if ( $provider = $this->aiProviders->active() ) {
			return $this->finalize( $provider->answer( $question ), 'ai', null );
		}

		return $this->fallback();
	}

	/**
	 * Links-only mode: search for matching posts/pages and return just the links
	 * without verbose answer text. Returns the most relevant matches as suggestions.
	 */
	private function linksOnlyResponse( string $question ): array {
		$chunk_hits = $this->chunks->search( $question, 15 );

		if ( empty( $chunk_hits ) ) {
			return $this->fallback();
		}

		$suggestions = array();
		$seen_ids    = array();
		$top_cache_id = null;

		foreach ( $chunk_hits as $hit ) {
			$hit_id = (int) $hit['id'];
			if ( in_array( $hit_id, $seen_ids, true ) ) {
				continue;
			}
			$seen_ids[] = $hit_id;
			if ( null === $top_cache_id ) {
				$top_cache_id = $hit_id;
			}
			$suggestions[] = array(
				'title'   => (string) $hit['title'],
				'url'     => (string) $hit['url'],
				'excerpt' => wp_trim_words( (string) $hit['matched_chunk'], 20, '…' ),
				'type'    => (string) ( $hit['source_type'] ?? 'post' ),
			);
		}

		// Also pull category/tag-related posts from the top match
		if ( $top_cache_id ) {
			$related = $this->chunks->findRelated( $top_cache_id, 10 );
			foreach ( $related as $rel ) {
				$rel_id = (int) $rel['id'];
				if ( ! in_array( $rel_id, $seen_ids, true ) ) {
					$seen_ids[] = $rel_id;
					$suggestions[] = array(
						'title'   => (string) $rel['title'],
						'url'     => (string) $rel['url'],
						'excerpt' => wp_trim_words( (string) $rel['excerpt'], 20, '…' ),
						'type'    => (string) ( $rel['source_type'] ?? 'post' ),
					);
				}
			}
		}

		if ( empty( $suggestions ) ) {
			return $this->fallback();
		}

		// Sort by priority: posts first, then files, then pages
		$suggestions = $this->sortByPriority( $suggestions );

		$count = count( $suggestions );
		$answer = sprintf(
			/* translators: %d: number of matching posts/pages found */
			_n( 'Found %d relevant post:', 'Found %d relevant posts:', $count, 'cms-suggestion-bot' ),
			$count
		);

		$first = $suggestions[0];
		$result = $this->finalize( $answer, 'cache', null, array(
			'title' => $first['title'],
			'url'   => $first['url'],
		) );

		$result['suggestions'] = array_slice( $suggestions, 1 );

		return $result;
	}

	/**
	 * When on a specific post/page, search ONLY within that post's content.
	 * Returns relevant content from the post WITHOUT any suggestions or links.
	 */
	private function searchPostContext( string $question, int $post_id ): ?array {
		$post = get_post( $post_id );
		if ( ! $post || 'publish' !== $post->post_status ) {
			return null;
		}

		$content = wp_strip_all_tags( $post->post_content );
		$title = $post->post_title;

		// Check if the question relates to this post's title or content
		$question_lower = strtolower( $question );
		$title_lower = strtolower( $title );

		// Simple relevance check - does the question mention words from the title?
		$title_words = array_filter( explode( ' ', $title_lower ) );
		$question_words = array_filter( explode( ' ', $question_lower ) );
		$overlap = array_intersect( $title_words, $question_words );

		// Only match when the question actually relates to this post's title
		// (has word overlap). Short unrelated questions should fall through
		// to the full cache search so the visitor still gets suggestions.
		if ( ! empty( $overlap ) ) {
			// Try to find a relevant section based on the question
			$answer = $this->findRelevantSection( $content, $question );

			// Return answer ONLY - no suggestion link (user is already on this post)
			return $this->finalize( $answer, 'cache', $post_id, null );
		}

		return null;
	}

	/**
	 * Find the most relevant section of content based on the question.
	 */
	private function findRelevantSection( string $content, string $question ): string {
		$question_words = array_filter( explode( ' ', strtolower( $question ) ) );

		// Split content into paragraphs
		$paragraphs = preg_split( '/\n\s*\n/', $content );

		// Score each paragraph by word overlap with the question
		$best_score = 0;
		$best_paragraph = '';

		foreach ( $paragraphs as $paragraph ) {
			$para_lower = strtolower( $paragraph );
			$score = 0;
			foreach ( $question_words as $word ) {
				if ( strlen( $word ) > 2 && strpos( $para_lower, $word ) !== false ) {
					$score++;
				}
			}
			if ( $score > $best_score ) {
				$best_score = $score;
				$best_paragraph = $paragraph;
			}
		}

		// If we found a relevant paragraph, return it; otherwise return the beginning
		if ( $best_score > 0 && ! empty( $best_paragraph ) ) {
			return wp_trim_words( $best_paragraph, 50, '…' );
		}

		// Return the first 50 words of the content
		return wp_trim_words( $content, 50, '…' );
	}

	/**
	 * Runs the knowledge-base + cached-content FULLTEXT lookups for one
	 * search string. $original_question is kept separate from $search_text
	 * so an AI provider (if enabled) is always handed what the visitor
	 * actually typed, never the history-padded version used for matching.
	 *
	 * @return array{answer:string, source:string, matched_id:int|null, suggestion:array{title:string,url:string}|null, suggestions:array<int,array{title:string,url:string,excerpt:string}>|null}|null
	 */
	private function searchKnowledgeAndCache( string $original_question, ?string $search_text = null ): ?array {
		$search_text ??= $original_question;

		$knowledge_hits = $this->knowledge->search( $search_text, 1 );
		if ( ! empty( $knowledge_hits ) ) {
			$hit = $knowledge_hits[0];
			return $this->finalize( (string) $hit['answer'], 'knowledge', (int) $hit['id'] );
		}

		$chunk_hits = $this->chunks->search( $search_text, 5 );
		if ( ! empty( $chunk_hits ) ) {
			$top    = $chunk_hits[0];
			$answer = sprintf(
				/* translators: 1: matched page/post title, 2: matching excerpt */
				__( '%1$s: %2$s', 'cms-suggestion-bot' ),
				(string) $top['title'],
				wp_trim_words( (string) $top['matched_chunk'], 40, '…' )
			);

			if ( $provider = $this->aiProviders->active() ) {
				$answer = $provider->answer( $original_question, $chunk_hits );
			}

			// Build the primary suggestion from the top match.
			$primary = array(
				'title' => (string) $top['title'],
				'url'   => (string) $top['url'],
			);

			// Collect additional related suggestions from the remaining chunk hits.
			$suggestions = array();
			$seen_ids    = array( (int) $top['id'] );

			// Deduplicate by cache_id - multiple chunks may come from the same post.
			foreach ( $chunk_hits as $hit ) {
				$hit_id = (int) $hit['id'];
				if ( in_array( $hit_id, $seen_ids, true ) ) {
					continue;
				}
				$seen_ids[] = $hit_id;
				$suggestions[] = array(
					'title'   => (string) $hit['title'],
					'url'     => (string) $hit['url'],
					'excerpt' => wp_trim_words( (string) $hit['matched_chunk'], 30, '…' ),
					'type'    => (string) ( $hit['source_type'] ?? 'post' ),
				);
			}

			// If the top match has a cache entry, also pull category/tag-related posts.
			if ( ! empty( $top['id'] ) ) {
				$related = $this->chunks->findRelated( (int) $top['id'], 10 );
				foreach ( $related as $rel ) {
					if ( ! in_array( (int) $rel['id'], $seen_ids, true ) ) {
						$seen_ids[] = (int) $rel['id'];
						$suggestions[] = array(
							'title'   => (string) $rel['title'],
							'url'     => (string) $rel['url'],
							'excerpt' => wp_trim_words( (string) $rel['excerpt'], 30, '…' ),
							'type'    => (string) ( $rel['source_type'] ?? 'post' ),
						);
					}
				}
			}

			// Sort suggestions by priority: posts first, then files, then pages
			$suggestions = $this->sortByPriority( $suggestions );

			$result = $this->finalize( $answer, 'cache', (int) $top['id'], $primary );

			// Only show suggestions if enabled in settings
			if ( $this->settings->get( 'behaviour', 'show_related', true ) ) {
				$result['suggestions'] = ! empty( $suggestions ) ? $suggestions : null;
			} else {
				$result['suggestions'] = null;
			}

			return $result;
		}

		return null;
	}

	/**
	 * When the main search finds nothing, attempt to surface related posts
	 * by doing a broader FULLTEXT search on the question itself - gives the
	 * visitor useful article links even when there's no direct answer.
	 *
	 * @return array{answer:string, source:string, matched_id:int|null, suggestion:array{title:string,url:string}|null, suggestions:array<int,array{title:string,url:string,excerpt:string}>|null}|null
	 */
	private function findRelatedFallback( string $question ): ?array {
		$chunk_hits = $this->chunks->search( $question, 5 );
		if ( empty( $chunk_hits ) ) {
			return null;
		}

		$suggestions = array();
		$seen_ids    = array();

		foreach ( $chunk_hits as $hit ) {
			$hit_id = (int) $hit['id'];
			if ( in_array( $hit_id, $seen_ids, true ) ) {
				continue;
			}
			$seen_ids[] = $hit_id;
			$suggestions[] = array(
				'title'   => (string) $hit['title'],
				'url'     => (string) $hit['url'],
				'excerpt' => wp_trim_words( (string) $hit['matched_chunk'], 30, '…' ),
				'type'    => (string) ( $hit['source_type'] ?? 'post' ),
			);
		}

		if ( empty( $suggestions ) ) {
			return null;
		}

		// Sort by priority: posts first, then files, then pages
		$suggestions = $this->sortByPriority( $suggestions );

		$answer = sprintf(
			/* translators: %s: the user's question */
			__( "I don't have a direct answer, but here are some related articles for \"%s\":", 'cms-suggestion-bot' ),
			$question
		);

		$first = $suggestions[0];
		$result = $this->finalize( $answer, 'cache_related', null, array(
			'title' => $first['title'],
			'url'   => $first['url'],
		) );

		// Only show suggestions if enabled in settings
		if ( $this->settings->get( 'behaviour', 'show_related', true ) ) {
			$result['suggestions'] = array_slice( $suggestions, 1 );
		} else {
			$result['suggestions'] = null;
		}

		return $result;
	}

	/**
	 * Folds the visitor's own last few messages into the current question -
	 * only used as a second-pass fallback (see resolve()) once the current
	 * message alone has already failed to match anything on its own.
	 *
	 * @param array<int, array{role:string, message:string}> $history
	 */
	private function buildContextualQuery( string $question, array $history ): string {
		$prior_user_messages = array_values( array_filter(
			array_map(
				static fn( array $m ) => 'user' === ( $m['role'] ?? '' ) ? trim( (string) ( $m['message'] ?? '' ) ) : '',
				$history
			),
			static fn( string $m ) => '' !== $m
		) );

		$window   = array_slice( $prior_user_messages, -self::HISTORY_WINDOW );
		$window[] = $question;

		return implode( ' ', $window );
	}

	/**
	 * @param array{title:string,url:string}|null $suggestion
	 * @return array{answer:string, source:string, matched_id:int|null, suggestion:array{title:string,url:string}|null, suggestions:array<int,array{title:string,url:string,excerpt:string}>|null}
	 */
	private function finalize( string $answer, string $source, ?int $matched_id, ?array $suggestion = null ): array {
		return array(
			'answer'     => $this->restrictedWords->apply( $answer ),
			'source'     => $source,
			'matched_id' => $matched_id,
			'suggestion' => $suggestion,
			'suggestions' => null,
		);
	}

	/**
	 * Sort suggestions by priority: posts first, then files (external resources), then pages.
	 */
	private function sortByPriority( array $suggestions ): array {
		$priority = array(
			'post' => 0,
			'file' => 1,
			'page' => 2,
		);

		usort( $suggestions, static function ( array $a, array $b ) use ( $priority ): int {
			$typeA = $priority[ $a['type'] ?? 'post' ] ?? 1;
			$typeB = $priority[ $b['type'] ?? 'post' ] ?? 1;
			return $typeA <=> $typeB;
		} );

		return $suggestions;
	}

	/**
	 * @return array{answer:string, source:string, matched_id:int|null, suggestion:array{title:string,url:string}|null, suggestions:array<int,array{title:string,url:string,excerpt:string}>|null}
	 */
	private function fallback(): array {
		return array(
			'answer'     => __( "I don't have an answer for that yet.", 'cms-suggestion-bot' ),
			'source'     => 'fallback',
			'matched_id' => null,
			'suggestion' => null,
			'suggestions' => null,
		);
	}
}
