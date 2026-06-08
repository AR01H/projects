<?php
defined( 'ABSPATH' ) || exit;

/**
 * AH_Page_Data
 *
 * Static page content served directly from real_data/ files — no DB roundtrip.
 * Use this for content that is rarely changed and has NO admin edit UI.
 *
 * Data lives in:
 *   real_data/json/section-headings.json  — page/section titles, tags, body copy
 *   real_data/json/brand.json             — brand name, tagline
 *   real_data/csv/process-steps.csv       — step-by-step process (num, title, desc)
 *   real_data/csv/site-stats.csv          — headline stats (num, label)
 *   real_data/csv/trust-signals.csv       — trust badges (icon, text)
 *
 * To add content: edit (or create) the matching file in real_data/, no code change needed.
 */
class AH_Page_Data {

	// ── Section Headings ──────────────────────────────────────────────────────

	/**
	 * All section headings from real_data/json/section-headings.json.
	 * Each entry is an assoc array with at least: tag, title, body.
	 *
	 * @return array<string, array{tag:string, title:string, body:string}>
	 */
	public static function all_section_headings(): array {
		static $cache = null;
		if ( $cache === null ) {
			$cache = AH_Real_Loader::kv_json( 'section-headings' ) ?: [];
		}
		return $cache;
	}

	/**
	 * Single section heading by key, e.g. 'services', 'reviews', 'faqs'.
	 * Returns the entry array, or [] if the key is absent.
	 */
	public static function section_heading( string $key ): array {
		$all = self::all_section_headings();
		return is_array( $all[ $key ] ?? null ) ? (array) $all[ $key ] : [];
	}

	// ── Brand ─────────────────────────────────────────────────────────────────

	/**
	 * Brand config from real_data/json/brand.json.
	 * Keys: brand_name, tagline, logo_url (optional).
	 */
	public static function brand(): array {
		static $cache = null;
		if ( $cache === null ) {
			$cache = AH_Real_Loader::kv_json( 'brand' ) ?: [];
		}
		return $cache;
	}

	// ── Static Site Content ───────────────────────────────────────────────────

	/**
	 * Process steps from real_data/csv/process-steps.csv.
	 * Columns: num, title, desc
	 * Falls back to [] when file is absent (use the WP option path instead).
	 */
	public static function process_steps(): array {
		return AH_Real_Loader::csv( 'process-steps' );
	}

	/**
	 * Site statistics from real_data/csv/site-stats.csv.
	 * Columns: num, label
	 */
	public static function site_stats(): array {
		return AH_Real_Loader::csv( 'site-stats' );
	}

	/**
	 * Trust signals from real_data/csv/trust-signals.csv.
	 * Columns: icon, text
	 */
	public static function trust_signals(): array {
		return AH_Real_Loader::csv( 'trust-signals' );
	}
}
