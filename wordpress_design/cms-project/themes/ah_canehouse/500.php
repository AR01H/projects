<?php
defined( 'ABSPATH' ) || exit;

/* Ensure a 500 HTTP status is sent */
status_header( 500 );

/* ── Debug gate ────────────────────────────────────────────────
 * Shows error info only when BOTH conditions are true:
 *   1. ?debug=true is in the URL
 *   2. WP_DEBUG is on OR the current user is an admin
 * Never leaks error details to anonymous visitors in production.
 * ─────────────────────────────────────────────────────────────*/
$show_debug = isset( $_GET['debug'] ) && $_GET['debug'] === 'true'
              && ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || current_user_can( 'manage_options' ) );

/* Collect error context ─────────────────────────────────────── */
$last_error = error_get_last();

/* Themes/plugins can pass a Throwable before including this file:
 *   $ch_500_exception = $e;
 *   include get_template_directory() . '/500.php'; exit; */
$exception = isset( $ch_500_exception ) && $ch_500_exception instanceof Throwable
             ? $ch_500_exception
             : null;

get_header();
?>

<main class="ch-main" id="main-content">
	<div class="ch-500" style="text-align:center;padding:8rem 2rem;">

		<div class="ch-500__icon" style="font-size:5rem;">⚠️</div>
		<h1 class="ch-500__title">500 - Something Went Wrong</h1>
		<p class="ch-500__desc">
			Our server hit an unexpected snag. Don't worry - it's not you.<br>
			Please try again in a moment.
		</p>
		<div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;margin-top:2rem">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-lime" style="display:inline-block;">
				🥤 Back to Home
			</a>
			<a href="javascript:location.reload()" class="btn-lime" style="display:inline-block;background:transparent;border:2px solid currentColor;">
				↻ Try Again
			</a>
		</div>

	</div>

<?php if ( $show_debug ) : ?>
	<!-- ── DEBUG PANEL ──────────────────────────────────────── -->
	<div style="max-width:900px;margin:0 auto 4rem;text-align:left;font-family:monospace;font-size:13px">

		<h2 style="font-size:15px;font-weight:700;padding:10px 16px;background:#1e1e2e;color:#cdd6f4;border-radius:6px 6px 0 0;margin:0">
			🐛 Debug Info
			<span style="float:right;font-size:11px;opacity:.6;font-weight:400">?debug=true</span>
		</h2>

		<div style="background:#13131f;color:#cdd6f4;padding:16px;border-radius:0 0 6px 6px;overflow-x:auto">

		<?php if ( $exception ) : ?>
			<!-- Exception from $ch_500_exception -->
			<div style="margin-bottom:14px">
				<span style="color:#f38ba8;font-weight:700"><?php echo esc_html( get_class( $exception ) ); ?></span>
				<span style="color:#fab387"> [<?php echo (int) $exception->getCode(); ?>]</span>
			</div>
			<div style="color:#a6e3a1;margin-bottom:14px;white-space:pre-wrap"><?php echo esc_html( $exception->getMessage() ); ?></div>
			<div style="color:#89b4fa;margin-bottom:14px">
				<strong style="color:#cba6f7">File:</strong>
				<?php echo esc_html( $exception->getFile() ); ?> <strong style="color:#cba6f7">line</strong> <?php echo (int) $exception->getLine(); ?>
			</div>

			<!-- Stack trace -->
			<details open>
				<summary style="cursor:pointer;color:#cba6f7;margin-bottom:8px;font-weight:700">Stack Trace</summary>
				<ol style="margin:0;padding-left:20px;color:#a6adc8;line-height:1.8">
				<?php foreach ( $exception->getTrace() as $i => $frame ) :
					$file  = $frame['file']     ?? '[internal]';
					$line  = $frame['line']     ?? '?';
					$class = isset( $frame['class'] ) ? esc_html( $frame['class'] . $frame['type'] ) : '';
					$fn    = esc_html( $frame['function'] ?? '' );
				?>
					<li>
						<span style="color:#89dceb"><?php echo $class . $fn; ?>()</span><br>
						<span style="color:#585b70;font-size:11px"><?php echo esc_html( $file ); ?>:<?php echo (int) $line; ?></span>
					</li>
				<?php endforeach; ?>
				</ol>
			</details>

		<?php elseif ( $last_error ) : ?>
			<!-- PHP last error -->
			<?php
			$type_map = [
				E_ERROR             => 'E_ERROR',
				E_WARNING           => 'E_WARNING',
				E_PARSE             => 'E_PARSE',
				E_NOTICE            => 'E_NOTICE',
				E_CORE_ERROR        => 'E_CORE_ERROR',
				E_CORE_WARNING      => 'E_CORE_WARNING',
				E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
				E_USER_ERROR        => 'E_USER_ERROR',
				E_USER_WARNING      => 'E_USER_WARNING',
				E_USER_NOTICE       => 'E_USER_NOTICE',
				E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
				E_DEPRECATED        => 'E_DEPRECATED',
			];
			$type_label = $type_map[ $last_error['type'] ] ?? 'ERROR(' . $last_error['type'] . ')';
			?>
			<div style="margin-bottom:10px">
				<span style="color:#f38ba8;font-weight:700"><?php echo esc_html( $type_label ); ?></span>
			</div>
			<div style="color:#a6e3a1;margin-bottom:10px;white-space:pre-wrap"><?php echo esc_html( $last_error['message'] ); ?></div>
			<div style="color:#89b4fa">
				<strong style="color:#cba6f7">File:</strong>
				<?php echo esc_html( $last_error['file'] ); ?>
				<strong style="color:#cba6f7">line</strong> <?php echo (int) $last_error['line']; ?>
			</div>

		<?php else : ?>
			<span style="color:#a6adc8">No PHP error recorded. The 500 was likely triggered intentionally (e.g. status_header(500)).</span>
		<?php endif; ?>

		<!-- Request context -->
		<hr style="border:0;border-top:1px solid #313244;margin:16px 0">
		<div style="color:#a6adc8;line-height:1.8">
			<div><strong style="color:#cba6f7">URL:</strong> <?php echo esc_html( ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' ) . '://' . ( $_SERVER['HTTP_HOST'] ?? '' ) . ( $_SERVER['REQUEST_URI'] ?? '' ) ); ?></div>
			<div><strong style="color:#cba6f7">Method:</strong> <?php echo esc_html( $_SERVER['REQUEST_METHOD'] ?? 'GET' ); ?></div>
			<div><strong style="color:#cba6f7">PHP:</strong> <?php echo esc_html( PHP_VERSION ); ?></div>
			<div><strong style="color:#cba6f7">WP:</strong> <?php echo esc_html( get_bloginfo( 'version' ) ); ?></div>
			<div><strong style="color:#cba6f7">Time:</strong> <?php echo esc_html( current_time( 'Y-m-d H:i:s' ) ); ?></div>
		</div>

		</div><!-- /debug panel inner -->
	</div>
<?php endif; /* show_debug */ ?>

</main>

<?php get_footer(); ?>
