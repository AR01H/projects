<?php
/**
 * config/defaults.php - default values for every Configuration field.
 * Services\SettingsService merges saved settings on top of this array, so a
 * fresh install (or a setting added by a future update) always has a
 * sensible value instead of null/undefined.
 *
 * @return array<string, mixed>
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

return array(

	// ── General ──────────────────────────────────────────────────────────
	'general' => array(
		'enabled'           => true,
		'bot_name'          => 'CMS Suggestion Bot',
		// Shown as the larger intro/description text inside the open widget
		// (Front\ChatWidget), above the message list - not just a settings label.
		'bot_description'   => "Ask me about this site's content.",
		'show_description'  => true, // Whether to show the description in the widget
		'bot_logo_id'       => 0,
		'bot_icon'          => '💬',
		'theme_color'       => '#2271b1',
		'background_color'  => '#ffffff',
		'text_color'        => '#1d2327',
		'welcome_message'   => 'Hi! What are you looking for?',
		'goodbye_message'   => 'Thanks for stopping by!',
		'thinking_message'  => 'Thinking…',
		'typing_speed_ms'   => 18,
		'language'          => 'en',
		'timezone'          => 'UTC',
		// Small badge text under the bot name in the widget header. Empty =
		// auto ("AI Assistant" when Configuration -> AI Approach is enabled,
		// otherwise "Suggestion Bot") - set explicitly here to override that.
		'identity_label'    => '',
	),

	// ── Behaviour ────────────────────────────────────────────────────────
	'behaviour' => array(
		'tone'            => 'friendly', // friendly | professional | formal | casual
		'developer_mode'  => false,
		'verbose_mode'    => false,
		'links_only_mode' => false, // When true, bot returns only post/page links without verbose answers
		'show_related'    => true,  // Show related articles in suggestions
		'debug_mode'      => false,
		'safe_mode'       => true,
	),

	// ── Cache ────────────────────────────────────────────────────────────
	'cache' => array(
		'auto_generate'       => true,
		'generate_on_publish' => true,
		'generate_on_update'  => true,
		'generate_on_delete'  => true,
		'generate_daily'      => false,
		'generate_weekly'     => true,
		'generate_monthly'    => false,
		'max_cache_size_mb'   => 100,
		'compression'         => false,
		'chunk_words'         => 200,
	),

	// ── API ──────────────────────────────────────────────────────────────
	'api' => array(
		'enabled'         => false,
		'allowed_origins' => array(),
		'rate_limit'      => 60,
		'logging'         => true,
	),

	// ── Reader ───────────────────────────────────────────────────────────
	'reader' => array(
		'speed'            => 'normal', // slow | normal | fast
		'chunk_size'       => 200,
		'memory_limit_mb'  => 256,
		'batch_size'       => 50,
		'max_execution_sec' => 30,
		'sources'          => array( 'page', 'post', 'file' ),
	),

	// ── Common questions cache ──────────────────────────────────────────
	// Frequently-asked questions are answered straight from
	// cms_sug_bot_knowledge without touching the Reader/Cache pipeline again.
	'common_questions' => array(
		'enabled'      => true,
		'cache_ttl'    => DAY_IN_SECONDS,
		'max_entries'  => 200,
	),

	// ── Restricted words ────────────────────────────────────────────────
	// Terms in this list are stripped from indexed content and from bot
	// answers (see Helpers\Str::containsRestricted() / RestrictedWordsService).
	'restricted_words' => array(
		'enabled' => false,
		'words'   => array(),
		'mode'    => 'mask', // mask | block
	),

	// ── AI approach ──────────────────────────────────────────────────────
	// Off by default: the bot answers purely from cached content + the
	// knowledge base (Bot\AnswerResolver). Turning this on hands unmatched
	// questions to whichever provider is marked active below instead of the
	// built-in "not found" fallback. Each provider keeps its own credentials
	// so an admin can pre-configure several and switch "active_provider"
	// with no other change - see Contracts\AiProviderInterface and
	// Services\AiProviderRegistry, which is what actually reads this.
	'ai_approach' => array(
		'enabled'         => false,
		'active_provider' => '', // key into 'providers' below, e.g. 'openai'
		'providers'       => array(
			'openai' => array( 'label' => 'OpenAI (ChatGPT)', 'api_key' => '', 'model' => 'gpt-4o-mini' ),
			'claude' => array( 'label' => 'Anthropic Claude', 'api_key' => '', 'model' => 'claude-sonnet' ),
			'gemini' => array( 'label' => 'Google Gemini',    'api_key' => '', 'model' => 'gemini-1.5-flash' ),
			'ollama' => array( 'label' => 'Ollama (local)',   'api_key' => '', 'model' => 'llama3', 'endpoint' => 'http://localhost:11434' ),
		),
	),

	// ── Greetings ────────────────────────────────────────────────────────
	// Small talk ("hi", "how are you") is too short for FULLTEXT search
	// (below MySQL's minimum indexed word length) and isn't real site
	// content, so it's handled separately here rather than via the
	// Knowledge Base - see Services\GreetingsService, checked before any DB
	// query in Bot\AnswerResolver. Administrator can edit every response.
	// The phrase => response mapping itself lives in its own file
	// (config/greetings.php), not inlined here.
	'greetings' => array(
		'enabled' => true,
		'phrases' => require CSB_PLUGIN_DIR . '/config/greetings.php',
	),

	// ── Usage limits ─────────────────────────────────────────────────────
	// Caps how many questions one visitor can send the bot, independent of
	// the API rate_limit above (that one covers app/API callers; this one
	// covers the bot chat widget itself). Enforced in Bot\BotEngine.
	'usage_limits' => array(
		'enabled'                   => false,
		'max_messages_per_session'  => 20,
		'max_messages_per_day'      => 50,
		'limit_reached_message'     => 'You\'ve reached the question limit for now - please try again later.',
	),
);
