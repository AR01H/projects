<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Services;

use CmsSuggestionBot\Database\DB;
use CmsSuggestionBot\Database\Schema;
use CmsSuggestionBot\Repositories\KnowledgeRepository;
use CmsSuggestionBot\Logger\Logger;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Tools maintenance actions that don't belong to CacheService or
 * KnowledgeService specifically: DB Health Check, Repair, Optimize,
 * Export/Import Cache, Clear Knowledge.
 */
final class MaintenanceService {

	public function __construct(
		private readonly KnowledgeRepository $knowledge,
		private readonly Logger $logger,
	) {}

	public function clearKnowledge(): int {
		$count = $this->knowledge->truncate();
		$this->logger->info( Logger::CHANNEL_MANUAL, 'Cleared knowledge base.', array( 'count' => $count ) );

		return $count;
	}

	/**
	 * @return array<int, string> Missing table names (unprefixed), empty when healthy.
	 */
	public function checkHealth(): array {
		$missing = array();
		foreach ( array_keys( Schema::definitions() ) as $suffix ) {
			if ( ! DB::tableExists( DB::table( $suffix ) ) ) {
				$missing[] = $suffix;
			}
		}

		return $missing;
	}

	/**
	 * @return array<int, string> Table names that were repaired.
	 */
	public function repairTables(): array {
		$repaired = array();
		foreach ( array_keys( Schema::definitions() ) as $suffix ) {
			$table = DB::table( $suffix );
			if ( DB::tableExists( $table ) ) {
				DB::wpdb()->query( "REPAIR TABLE `{$table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$repaired[] = $suffix;
			}
		}
		$this->logger->info( Logger::CHANNEL_MANUAL, 'Repaired tables.', array( 'tables' => $repaired ) );

		return $repaired;
	}

	/**
	 * @return array<int, string> Table names that were optimized.
	 */
	public function optimizeTables(): array {
		$optimized = array();
		foreach ( array_keys( Schema::definitions() ) as $suffix ) {
			$table = DB::table( $suffix );
			if ( DB::tableExists( $table ) ) {
				DB::wpdb()->query( "OPTIMIZE TABLE `{$table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$optimized[] = $suffix;
			}
		}
		$this->logger->info( Logger::CHANNEL_MANUAL, 'Optimized tables.', array( 'tables' => $optimized ) );

		return $optimized;
	}

	/**
	 * @return string JSON export of the full cache table.
	 */
	public function exportCache(): string {
		$rows = DB::wpdb()->get_results( 'SELECT * FROM ' . DB::table( 'cache' ), ARRAY_A );

		return (string) wp_json_encode( array( 'exported_at' => current_time( 'mysql' ), 'entries' => $rows ) );
	}

	/**
	 * @return int Number of rows imported.
	 */
	public function importCache( string $json ): int {
		$decoded = json_decode( $json, true );
		$entries = is_array( $decoded ) && isset( $decoded['entries'] ) && is_array( $decoded['entries'] )
			? $decoded['entries']
			: array();

		$table = DB::table( 'cache' );
		$count = 0;
		foreach ( $entries as $entry ) {
			if ( ! is_array( $entry ) || empty( $entry['source_type'] ) || empty( $entry['source_id'] ) ) {
				continue;
			}
			unset( $entry['id'] );
			DB::wpdb()->replace( $table, $entry );
			++$count;
		}

		$this->logger->info( Logger::CHANNEL_MANUAL, 'Imported cache.', array( 'count' => $count ) );

		return $count;
	}
}
