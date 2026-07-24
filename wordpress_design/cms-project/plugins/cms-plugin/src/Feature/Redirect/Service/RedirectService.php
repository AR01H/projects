<?php

namespace Ah\Cms\Feature\Redirect\Service;

defined( 'ABSPATH' ) || exit;

/**
 * Redirect service — handles URL redirect rules on the frontend.
 * Extracted from ah-cms.php template_redirect handler.
 */
class RedirectService {

	/**
	 * Check and enforce redirect rules on frontend.
	 * Priority 1 = fires before WordPress's own redirect_canonical.
	 */
	public static function checkRedirects(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_redirect_rules';

		// Bail if table doesn't exist yet (pre-upgrade).
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( ! $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) ) {
			return;
		}

		$path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?? '', '/' );
		if ( '' === $path ) {
			return;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rule = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM `{$table}` WHERE source_slug = %s AND is_active = 1 LIMIT 1",
				$path
			)
		);

		if ( ! $rule ) {
			return;
		}

		// Increment hit counter (non-blocking; ignore errors).
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( $wpdb->prepare( "UPDATE `{$table}` SET hit_count = hit_count + 1 WHERE id = %d", (int) $rule->id ) );

		$type   = (string) $rule->type;
		$target = esc_url_raw( (string) $rule->target_url );
		$label  = sanitize_text_field( (string) $rule->notes );

		if ( '410' === $type ) {
			self::render410();
		}

		if ( 'exit' === $type && $target ) {
			self::renderExit( $target, $label );
		}

		if ( $target && in_array( $type, [ '301', '302' ], true ) ) {
			wp_redirect( $target, (int) $type );
			exit;
		}
	}

	/**
	 * Render a 410 Gone page.
	 */
	private static function render410(): void {
		status_header( 410 );
		nocache_headers();
		$site = esc_html( get_bloginfo( 'name' ) );
		echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Page Gone - {$site}</title>" // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			. "<style>*{box-sizing:border-box}body{margin:0;font-family:system-ui,sans-serif;background:#f8fafc;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px}.card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:48px 40px;max-width:440px;text-align:center}.icon{font-size:48px;margin-bottom:16px}.title{font-size:22px;font-weight:700;color:#111827;margin:0 0 10px}.msg{color:#6b7280;font-size:15px;margin:0 0 24px}.back{display:inline-block;padding:10px 24px;background:#1d4ed8;color:#fff;border-radius:8px;text-decoration:none;font-weight:500;font-size:14px}</style>" // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			. "</head><body><div class='card'><div class='icon'>🗑️</div><h1 class='title'>Page Removed</h1><p class='msg'>This page no longer exists and has been permanently removed.</p><a href='" . esc_url( home_url( '/' ) ) . "' class='back'>← Back to Home</a></div></body></html>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Render an exit interstitial page.
	 */
	private static function renderExit( string $target, string $label ): void {
		nocache_headers();
		$site     = esc_html( get_bloginfo( 'name' ) );
		$t_esc    = esc_url( $target );
		$t_label  = esc_html( $label ?: $target );
		$home_url = esc_url( home_url( '/' ) );
		echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Leaving {$site}</title>" // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			. "<meta http-equiv='refresh' content='4;url={$t_esc}'>" // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			. "<style>*{box-sizing:border-box}body{margin:0;font-family:system-ui,sans-serif;background:#f8fafc;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px}.card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:48px 40px;max-width:480px;text-align:center}.icon{font-size:48px;margin-bottom:16px}.title{font-size:20px;font-weight:700;color:#111827;margin:0 0 8px}.site{font-size:13px;color:#6b7280;margin:0 0 16px}.dest{background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:12px 16px;font-size:13px;color:#0369a1;word-break:break-all;margin-bottom:20px}.bar-wrap{height:4px;background:#e5e7eb;border-radius:2px;overflow:hidden;margin-bottom:20px}.bar{height:100%;background:#1d4ed8;border-radius:2px;animation:fill 4s linear forwards}@keyframes fill{from{width:0}to{width:100%}}.links{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}.btn{display:inline-block;padding:9px 20px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:500}.btn-primary{background:#1d4ed8;color:#fff}.btn-secondary{background:#f3f4f6;color:#374151;border:1px solid #d1d5db}</style>" // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			. "</head><body><div class='card'><div class='icon'>🔗</div><h1 class='title'>You are leaving {$site}</h1><p class='site'>You will be redirected to an external site in a moment.</p><div class='dest'>{$t_label}</div><div class='bar-wrap'><div class='bar'></div></div><div class='links'><a href='{$t_esc}' class='btn btn-primary'>Continue →</a><a href='{$home_url}' class='btn btn-secondary'>Stay on {$site}</a></div></div></body></html>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}
}
