<?php
/**
 * Template Name: Static HTML Page
 *
 * Serves raw HTML files from static/{slug}.html with full style isolation.
 * Append ?raw=1 to get the bare HTML — used internally as the iframe src.
 */
defined( 'ABSPATH' ) || exit;

$slug = get_post_field( 'post_name', get_the_ID() );
$file = get_template_directory() . '/static/' . sanitize_file_name( $slug ) . '.html';

// ── Raw mode ──────────────────────────────────────────────────────────────────
// Returns bare HTML with no WordPress wrapper.
// Used as the iframe src for style isolation, or for direct embedding elsewhere.
if ( isset( $_GET['raw'] ) && '1' === $_GET['raw'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( file_exists( $file ) ) {
		header( 'Content-Type: text/html; charset=UTF-8' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
		readfile( $file );
	} else {
		status_header( 404 );
		echo '<!DOCTYPE html><html><body><p>File not found.</p></body></html>';
	}
	exit;
}

// ── Normal mode — full theme layout with isolated iframe ──────────────────────
get_header();

$raw_url = add_query_arg( 'raw', '1', get_permalink() );
?>
<main class="ah-static-page-outer">
	<?php if ( file_exists( $file ) ) : ?>

		<iframe
			id="ah-static-frame"
			src="<?php echo esc_url( $raw_url ); ?>"
			sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-popups-to-escape-sandbox"
			scrolling="no"
			frameborder="0"
			style="width:100%;border:none;display:block;min-height:200px;"
			title="<?php echo esc_attr( get_the_title() ); ?>"
		></iframe>

		<script>
		(function () {
			var frame = document.getElementById( 'ah-static-frame' );
			function resize() {
				try {
					var h = frame.contentDocument.documentElement.scrollHeight;
					if ( h > 0 ) frame.style.height = h + 'px';
				} catch ( e ) {}
			}
			frame.addEventListener( 'load', resize );
			window.addEventListener( 'resize', function () {
				setTimeout( resize, 100 );
			} );
		})();
		</script>

	<?php else : ?>

		<div style="padding:40px 24px;text-align:center;color:#6b7280;">
			<p>No HTML file found at <code>static/<?php echo esc_html( $slug ); ?>.html</code>.</p>
			<?php if ( current_user_can( 'manage_options' ) ) : ?>
			<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-static-pages&edit=' . rawurlencode( $slug ) ) ); ?>" class="button">Create it in the admin</a></p>
			<?php endif; ?>
		</div>

	<?php endif; ?>
</main>

<?php
get_footer();
