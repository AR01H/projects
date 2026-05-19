<?php
/**
 * Template Name: Page Builder
 *
 * Renders static/{slug}.html directly into the theme wrapper.
 * Extracts <style> blocks and <body> content from the file so
 * both the file's own styles and the theme styles apply correctly.
 */
defined( 'ABSPATH' ) || exit;

$slug       = get_post_field( 'post_name', get_queried_object_id() );
$static_dir = realpath( get_template_directory() . '/static' );
$file       = $static_dir ? realpath( $static_dir . DIRECTORY_SEPARATOR . sanitize_file_name( $slug ) . '.html' ) : false;

$inline_styles = '';
$body_html     = '';

if ( $file && strpos( $file, $static_dir ) === 0 && file_exists( $file ) ) {
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$raw = file_get_contents( $file );

	// Extract all <style> blocks
	preg_match_all( '/<style[^>]*>(.*?)<\/style>/is', $raw, $style_matches );
	$inline_styles = implode( "\n", $style_matches[1] ?? [] );

	// Extract <body> content; fall back to full HTML if no body tag
	if ( preg_match( '/<body[^>]*>(.*?)<\/body>/is', $raw, $body_match ) ) {
		$body_html = $body_match[1];
	} else {
		// No full document structure — use as-is
		$body_html = $raw;
	}
}

get_header();

get_template_part( 'components/page-header', null, [
	'title'      => get_the_title(),
	'breadcrumb' => [
		[ 'Home',          home_url( '/' ) ],
		[ get_the_title(), '' ],
	],
] );
?>

<main id="main-content">
  <?php if ( $inline_styles ) : ?>
  <style id="pb-page-styles"><?php echo $inline_styles; /* trusted admin file */ ?></style>
  <?php endif; ?>

  <div class="container section">
    <?php if ( $body_html ) : ?>
      <div class="pb-content">
        <?php echo $body_html; /* trusted admin file — path-traversal guarded above */ ?>
      </div>
    <?php else : ?>
      <div class="text-center section--sm">
        <div style="font-size:3rem;margin-bottom:16px">📄</div>
        <h2 style="font-family:var(--font-display);font-size:1.5rem;margin-bottom:12px">Content not found</h2>
        <p style="color:var(--text-secondary)">
          No file found at <code>static/<?php echo esc_html( $slug ); ?>.html</code>.
        </p>
        <?php if ( current_user_can( 'manage_options' ) ) : ?>
          <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-static-pages&edit=' . rawurlencode( $slug ) ) ); ?>"
             class="btn btn-outline" style="margin-top:16px">Create it in admin →</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php get_template_part( 'components/cta-section' ); ?>
<?php get_footer(); ?>
