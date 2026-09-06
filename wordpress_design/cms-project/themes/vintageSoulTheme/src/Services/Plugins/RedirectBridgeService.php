<?php
namespace VintageSoul\Services\Plugins;

use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

/**
 * RedirectBridgeService — Bridges CMS Redirect Rules Manager (admin.php?page=ah-redirects)
 * with the theme request lifecycle and provides vintage-styled 410 and Exit Interstitial screens.
 */
class RedirectBridgeService {

	private static array $cache = array();

	/**
	 * Register theme hooks for redirect handling.
	 */
	public static function register_hooks(): void {
		add_action( 'template_redirect', array( static::class, 'handle_redirects' ), 1 );
	}

	/**
	 * Check incoming request path against active redirect rules.
	 */
	public static function handle_redirects(): void {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		if ( ! isset( $GLOBALS['wpdb'] ) ) {
			return;
		}

		$req_uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
		$path    = trim( (string) wp_parse_url( $req_uri, PHP_URL_PATH ), '/' );

		if ( '' === $path ) {
			return;
		}

		$rule = self::get_rule( $path );
		if ( ! $rule || empty( $rule->is_active ) ) {
			return;
		}

		// Increment hit count asynchronously / non-blocking
		self::record_hit( (int) $rule->id );

		$type   = (string) $rule->type;
		$target = esc_url_raw( (string) $rule->target_url );
		$label  = sanitize_text_field( (string) ( $rule->notes ?? '' ) );

		// 1. HTTP 410 Gone (Permanently Removed)
		if ( '410' === $type ) {
			self::render_410();
		}

		// 2. Exit Interstitial (Leaving Site Warning)
		if ( 'exit' === $type && '' !== $target ) {
			self::render_exit_interstitial( $target, $label );
		}

		// 3. HTTP 301 / 302 Standard Redirects
		if ( '' !== $target && in_array( $type, array( '301', '302' ), true ) ) {
			wp_redirect( $target, (int) $type ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
			exit;
		}
	}

	/**
	 * Get an active redirect rule matching a path or slug.
	 *
	 * @param string $path
	 * @return object|null
	 */
	public static function get_rule( string $path ): ?object {
		$clean_path = trim( strtolower( $path ), '/' );
		if ( '' === $clean_path ) {
			return null;
		}

		if ( array_key_exists( $clean_path, self::$cache ) ) {
			return self::$cache[ $clean_path ];
		}

		global $wpdb;
		$table = $wpdb->prefix . 'ah_redirect_rules';
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( ! $table_exists ) {
			self::$cache[ $clean_path ] = null;
			return null;
		}

		$row = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM `{$table}` WHERE (source_slug = %s OR source_slug = %s) AND is_active = 1 LIMIT 1",
			$clean_path,
			'/' . $clean_path
		) );

		self::$cache[ $clean_path ] = $row ? (object) $row : null;
		return self::$cache[ $clean_path ];
	}

	/**
	 * Increment hit counter on a redirect rule.
	 *
	 * @param int $rule_id
	 */
	public static function record_hit( int $rule_id ): void {
		if ( $rule_id <= 0 || ! isset( $GLOBALS['wpdb'] ) ) {
			return;
		}
		global $wpdb;
		$table = $wpdb->prefix . 'ah_redirect_rules';
		$wpdb->query( $wpdb->prepare( "UPDATE `{$table}` SET hit_count = hit_count + 1 WHERE id = %d", $rule_id ) );
	}

	/**
	 * Render vintage botanical 410 Gone page.
	 */
	private static function render_410(): void {
		status_header( 410 );
		nocache_headers();
		$site_name = esc_html( get_bloginfo( 'name' ) ?: 'The Cane House' );
		$home_url  = esc_url( home_url( '/' ) );
		?>
		<!DOCTYPE html>
		<html lang="en">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title><?php echo esc_html__( 'Page Removed — 410 Gone', 'vintagesoul' ); ?> — <?php echo $site_name; ?></title>
			<style>
				*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
				body {
					font-family: 'EB Garamond', Georgia, serif;
					background: #040f07 radial-gradient(circle at 50% 30%, #0d2815 0%, #040f07 70%);
					color: #fbf2e6;
					display: flex;
					align-items: center;
					justify-content: center;
					min-height: 100vh;
					padding: 24px;
				}
				.card {
					background: #fbf2e6 linear-gradient(180deg, #fbf2e6 0%, #f4dcc0 100%);
					border: 2px solid #8e622d;
					border-radius: 8px;
					box-shadow: inset 0 0 0 1.5px #caa06d, 0 20px 50px rgba(0,0,0,0.6);
					padding: 48px 36px;
					max-width: 480px;
					width: 100%;
					text-align: center;
					color: #2a2015;
				}
				.crest { font-size: 40px; margin-bottom: 12px; }
				.badge {
					display: inline-block;
					font-family: 'Cinzel', serif;
					font-size: 11px;
					letter-spacing: 0.15em;
					color: #8e622d;
					border: 1px solid #caa06d;
					padding: 4px 12px;
					border-radius: 20px;
					margin-bottom: 16px;
					background: rgba(202,160,109,0.15);
				}
				h1 {
					font-family: 'Cinzel', Georgia, serif;
					font-size: 24px;
					letter-spacing: 0.05em;
					color: #0c6434;
					margin-bottom: 12px;
				}
				p { font-size: 16px; line-height: 1.6; color: #5a4632; margin-bottom: 24px; }
				.btn {
					display: inline-block;
					padding: 12px 28px;
					background: #0c6434 linear-gradient(180deg, #0c6434 0%, #084925 100%);
					color: #f6d599;
					border: 1.5px solid #caa06d;
					border-radius: 4px;
					text-decoration: none;
					font-family: 'Cinzel', serif;
					font-size: 12px;
					font-weight: 700;
					letter-spacing: 0.1em;
					transition: all 0.2s ease;
					box-shadow: 0 4px 14px rgba(12,100,52,0.35);
				}
				.btn:hover { background: #084925; color: #fff; transform: translateY(-1px); }
			</style>
		</head>
		<body>
			<div class="card">
				<div class="crest">🌿</div>
				<div class="badge">HTTP 410 • PERMANENTLY RETIRED</div>
				<h1>Chronicle Archived</h1>
				<p>This historical record or document has been permanently retired and is no longer available in the archives.</p>
				<a href="<?php echo $home_url; ?>" class="btn">← RETURN TO PARLOUR</a>
			</div>
		</body>
		</html>
		<?php
		exit;
	}

	/**
	 * Render vintage botanical Exit Interstitial page.
	 *
	 * @param string $target
	 * @param string $label
	 */
	private static function render_exit_interstitial( string $target, string $label ): void {
		nocache_headers();
		$site_name = esc_html( get_bloginfo( 'name' ) ?: 'The Cane House' );
		$t_esc     = esc_url( $target );
		$t_label   = esc_html( $label ?: $target );
		$home_url  = esc_url( home_url( '/' ) );
		?>
		<!DOCTYPE html>
		<html lang="en">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<meta http-equiv="refresh" content="4;url=<?php echo $t_esc; ?>">
			<title><?php echo esc_html__( 'Departing The Cane House', 'vintagesoul' ); ?> — <?php echo $site_name; ?></title>
			<style>
				*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
				body {
					font-family: 'EB Garamond', Georgia, serif;
					background: #040f07 radial-gradient(circle at 50% 30%, #0d2815 0%, #040f07 70%);
					color: #fbf2e6;
					display: flex;
					align-items: center;
					justify-content: center;
					min-height: 100vh;
					padding: 24px;
				}
				.card {
					background: #fbf2e6 linear-gradient(180deg, #fbf2e6 0%, #f4dcc0 100%);
					border: 2px solid #8e622d;
					border-radius: 8px;
					box-shadow: inset 0 0 0 1.5px #caa06d, 0 20px 50px rgba(0,0,0,0.6);
					padding: 44px 36px;
					max-width: 520px;
					width: 100%;
					text-align: center;
					color: #2a2015;
				}
				.icon { font-size: 38px; margin-bottom: 10px; }
				.badge {
					display: inline-block;
					font-family: 'Cinzel', serif;
					font-size: 11px;
					letter-spacing: 0.15em;
					color: #8e622d;
					border: 1px solid #caa06d;
					padding: 4px 12px;
					border-radius: 20px;
					margin-bottom: 14px;
					background: rgba(202,160,109,0.15);
				}
				h1 {
					font-family: 'Cinzel', Georgia, serif;
					font-size: 22px;
					letter-spacing: 0.05em;
					color: #0c6434;
					margin-bottom: 8px;
				}
				.sub { font-size: 15px; color: #5a4632; margin-bottom: 18px; }
				.dest {
					background: rgba(12,100,52,0.08);
					border: 1px dashed #caa06d;
					border-radius: 6px;
					padding: 10px 14px;
					font-size: 13px;
					color: #0c6434;
					word-break: break-all;
					margin-bottom: 18px;
					font-family: monospace;
				}
				.bar-wrap {
					height: 5px;
					background: #e1cbb3;
					border-radius: 3px;
					overflow: hidden;
					margin-bottom: 22px;
				}
				.bar {
					height: 100%;
					background: #0c6434 linear-gradient(90deg, #0c6434, #caa06d);
					border-radius: 3px;
					animation: fill 4s linear forwards;
				}
				@keyframes fill { from { width: 0; } to { width: 100%; } }
				.links { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
				.btn {
					display: inline-block;
					padding: 11px 22px;
					border-radius: 4px;
					text-decoration: none;
					font-family: 'Cinzel', serif;
					font-size: 12px;
					font-weight: 700;
					letter-spacing: 0.08em;
					transition: all 0.2s ease;
				}
				.btn-primary {
					background: #0c6434 linear-gradient(180deg, #0c6434 0%, #084925 100%);
					color: #f6d599;
					border: 1.5px solid #caa06d;
					box-shadow: 0 4px 14px rgba(12,100,52,0.35);
				}
				.btn-primary:hover { background: #084925; color: #fff; }
				.btn-secondary {
					background: transparent;
					color: #5a4632;
					border: 1.5px solid #caa06d;
				}
				.btn-secondary:hover { background: rgba(202,160,109,0.2); color: #2a2015; }
			</style>
		</head>
		<body>
			<div class="card">
				<div class="icon">🔗</div>
				<div class="badge">✦ EXTERNAL NAVIGATION ✦</div>
				<h1>Departing <?php echo $site_name; ?></h1>
				<p class="sub">You are being escorted to an external destination in a few moments.</p>
				<div class="dest"><?php echo $t_label; ?></div>
				<div class="bar-wrap"><div class="bar"></div></div>
				<div class="links">
					<a href="<?php echo $t_esc; ?>" class="btn btn-primary">CONTINUE NOW ➔</a>
					<a href="<?php echo $home_url; ?>" class="btn btn-secondary">STAY ON SITE</a>
				</div>
			</div>
		</body>
		</html>
		<?php
		exit;
	}
}
