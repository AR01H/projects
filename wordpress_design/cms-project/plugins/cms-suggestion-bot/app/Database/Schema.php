<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Database;

defined( 'ABSPATH' ) || exit;

/**
 * CREATE TABLE definitions for every cms_sug_bot_* table. Consumed only by
 * Installer\Installer via dbDelta() - nothing else should reference this
 * class directly.
 */
final class Schema {

	private function __construct() {}

	/**
	 * @return array<string, string> table name (unprefixed) => CREATE TABLE SQL.
	 */
	public static function definitions(): array {
		global $wpdb;
		$collate = $wpdb->get_charset_collate();

		return array(
			'cache'         => self::cache( $collate ),
			'chunks'        => self::chunks( $collate ),
			'hash'          => self::hash( $collate ),
			'reader'        => self::reader( $collate ),
			'knowledge'     => self::knowledge( $collate ),
			'conversations' => self::conversations( $collate ),
			'messages'      => self::messages( $collate ),
			'logs'          => self::logs( $collate ),
			'api_keys'      => self::apiKeys( $collate ),
			'settings'      => self::settings( $collate ),
			'statistics'    => self::statistics( $collate ),
			'jobs'          => self::jobs( $collate ),
			'queue'         => self::queue( $collate ),
			'embeddings'    => self::embeddings( $collate ),
			'versions'      => self::versions( $collate ),
		);
	}

	// One row per readable WordPress object (page, post, product, ...).
	private static function cache( string $collate ): string {
		$t = DB::table( 'cache' );
		return "CREATE TABLE {$t} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			source_type VARCHAR(50) NOT NULL,
			source_id BIGINT UNSIGNED NOT NULL,
			title TEXT NULL,
			slug VARCHAR(200) NULL,
			url VARCHAR(500) NULL,
			excerpt TEXT NULL,
			content_hash VARCHAR(64) NULL,
			word_count INT UNSIGNED NOT NULL DEFAULT 0,
			meta LONGTEXT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'active',
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY source (source_type, source_id),
			KEY status (status),
			FULLTEXT KEY search_idx (title, excerpt)
		) {$collate};";
	}

	// Word-count-bounded slices of a cached entry's content, individually hashed.
	// FULLTEXT on content is Bot\AnswerResolver's primary lookup - a single
	// indexed MATCH AGAINST query instead of scanning/string-matching rows in
	// PHP, so answers stay fast as the cache grows (see project's speed requirement).
	private static function chunks( string $collate ): string {
		$t = DB::table( 'chunks' );
		return "CREATE TABLE {$t} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			cache_id BIGINT UNSIGNED NOT NULL,
			chunk_index INT UNSIGNED NOT NULL DEFAULT 0,
			content LONGTEXT NULL,
			hash VARCHAR(64) NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY cache_id (cache_id),
			FULLTEXT KEY content_idx (content)
		) {$collate};";
	}

	// Fast hash/version lookup for incremental regeneration, kept separate from
	// the full cache row so a hash comparison never has to load meta/content.
	private static function hash( string $collate ): string {
		$t = DB::table( 'hash' );
		return "CREATE TABLE {$t} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			source_type VARCHAR(50) NOT NULL,
			source_id BIGINT UNSIGNED NOT NULL,
			content_hash VARCHAR(64) NOT NULL,
			checksum VARCHAR(64) NULL,
			checked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY source (source_type, source_id)
		) {$collate};";
	}

	// One row per Reader scan run (manual or cron-triggered).
	private static function reader( string $collate ): string {
		$t = DB::table( 'reader' );
		return "CREATE TABLE {$t} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			reader_type VARCHAR(50) NOT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'pending',
			total INT UNSIGNED NOT NULL DEFAULT 0,
			processed INT UNSIGNED NOT NULL DEFAULT 0,
			started_at DATETIME NULL,
			finished_at DATETIME NULL,
			message TEXT NULL,
			PRIMARY KEY  (id),
			KEY reader_type (reader_type)
		) {$collate};";
	}

	// Manually curated + auto-learned question/answer pairs. status = 'published'
	// for real answers, 'unanswered' for questions Bot\AnswerResolver couldn't
	// match (auto-logged by Bot\BotEngine) - the Knowledge Base admin page
	// lists those separately so an admin can fill in an answer.
	private static function knowledge( string $collate ): string {
		$t = DB::table( 'knowledge' );
		return "CREATE TABLE {$t} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			question TEXT NOT NULL,
			answer LONGTEXT NOT NULL,
			category VARCHAR(100) NULL,
			keywords TEXT NULL,
			priority INT NOT NULL DEFAULT 0,
			source VARCHAR(50) NOT NULL DEFAULT 'manual',
			status VARCHAR(20) NOT NULL DEFAULT 'published',
			usage_count INT UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY category (category),
			KEY status (status),
			FULLTEXT KEY qa_idx (question, keywords)
		) {$collate};";
	}

	// A chat session (one visitor's back-and-forth) - see Bot\BotEngine context memory.
	private static function conversations( string $collate ): string {
		$t = DB::table( 'conversations' );
		return "CREATE TABLE {$t} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			session_id VARCHAR(64) NOT NULL,
			user_id BIGINT UNSIGNED NULL,
			started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			meta LONGTEXT NULL,
			PRIMARY KEY  (id),
			KEY session_id (session_id)
		) {$collate};";
	}

	private static function messages( string $collate ): string {
		$t = DB::table( 'messages' );
		return "CREATE TABLE {$t} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			conversation_id BIGINT UNSIGNED NOT NULL,
			role VARCHAR(20) NOT NULL DEFAULT 'user',
			message LONGTEXT NOT NULL,
			matched_source VARCHAR(50) NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY conversation_id (conversation_id)
		) {$collate};";
	}

	// Reader / Cache / API / error / cron / manual-action log lines (Logger\Logger).
	private static function logs( string $collate ): string {
		$t = DB::table( 'logs' );
		return "CREATE TABLE {$t} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			channel VARCHAR(50) NOT NULL,
			level VARCHAR(20) NOT NULL DEFAULT 'info',
			message TEXT NOT NULL,
			context LONGTEXT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY channel (channel),
			KEY created_at (created_at)
		) {$collate};";
	}

	private static function apiKeys( string $collate ): string {
		$t = DB::table( 'api_keys' );
		return "CREATE TABLE {$t} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			label VARCHAR(100) NULL,
			api_key VARCHAR(64) NOT NULL,
			allowed_origins TEXT NULL,
			rate_limit INT UNSIGNED NOT NULL DEFAULT 60,
			is_active TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
			last_used_at DATETIME NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY api_key (api_key)
		) {$collate};";
	}

	// Per-row settings store (Repositories\SettingsRepository) - deliberately
	// its own table rather than a single wp_options blob, so individual
	// settings can be read/written/logged without decoding the whole set.
	private static function settings( string $collate ): string {
		$t = DB::table( 'settings' );
		return "CREATE TABLE {$t} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			setting_key VARCHAR(150) NOT NULL,
			setting_value LONGTEXT NULL,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY setting_key (setting_key)
		) {$collate};";
	}

	private static function statistics( string $collate ): string {
		$t = DB::table( 'statistics' );
		return "CREATE TABLE {$t} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			stat_key VARCHAR(100) NOT NULL,
			stat_value LONGTEXT NULL,
			recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY stat_key (stat_key)
		) {$collate};";
	}

	// One row per admin-triggered or cron-triggered job (Generate Cache, Rebuild, ...).
	private static function jobs( string $collate ): string {
		$t = DB::table( 'jobs' );
		return "CREATE TABLE {$t} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			job_type VARCHAR(50) NOT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'pending',
			payload LONGTEXT NULL,
			result LONGTEXT NULL,
			started_at DATETIME NULL,
			finished_at DATETIME NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY job_type (job_type),
			KEY status (status)
		) {$collate};";
	}

	// Individual units of work belonging to a job (e.g. one row per source
	// record still needing cache regeneration) - lets large sites process in
	// bounded batches instead of one long-running request.
	private static function queue( string $collate ): string {
		$t = DB::table( 'queue' );
		return "CREATE TABLE {$t} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			job_id BIGINT UNSIGNED NULL,
			task_type VARCHAR(50) NOT NULL,
			payload LONGTEXT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'pending',
			attempts INT UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY status (status),
			KEY job_id (job_id)
		) {$collate};";
	}

	// Reserved for future semantic-search / RAG providers (see Contracts\AiProviderInterface) - unused until an AI approach is enabled.
	private static function embeddings( string $collate ): string {
		$t = DB::table( 'embeddings' );
		return "CREATE TABLE {$t} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			cache_id BIGINT UNSIGNED NULL,
			chunk_id BIGINT UNSIGNED NULL,
			provider VARCHAR(50) NULL,
			vector LONGTEXT NULL,
			dimensions INT UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY cache_id (cache_id),
			KEY chunk_id (chunk_id)
		) {$collate};";
	}

	// Schema/db-version migration audit trail (Installer\Installer).
	private static function versions( string $collate ): string {
		$t = DB::table( 'versions' );
		return "CREATE TABLE {$t} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			version VARCHAR(20) NOT NULL,
			applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id)
		) {$collate};";
	}
}
