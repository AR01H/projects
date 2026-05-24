<?php
// Fallback: serve static HTML pages that landed on page.php without _wp_page_template set.
$_ah_queried = get_queried_object();
$_ah_static  = ( $_ah_queried instanceof WP_Post )
	? get_post_meta( $_ah_queried->ID, '_ah_static_page', true )
	: '';
if ( $_ah_static ) {
	$static_dir = trailingslashit( get_template_directory() ) . 'static/';
	$real_dir   = realpath( $static_dir );
	$file       = $real_dir ? realpath( $real_dir . DIRECTORY_SEPARATOR . sanitize_file_name( $_ah_static ) . '.html' ) : false;
	if ( $file && strpos( $file, $real_dir ) === 0 && file_exists( $file ) ) {
		$html_raw = file_get_contents( $file );
		get_header();
		?>
<main style="margin:0;padding:0">
	<iframe id="ah-static-frame"
	        srcdoc="<?php echo htmlspecialchars( $html_raw, ENT_QUOTES, 'UTF-8' ); ?>"
	        style="width:100%;border:none;display:block;min-height:80vh;background:#fff"
	        title="<?php echo esc_attr( TXT_PHP_ECHO_ESC_ATTR_AH_QUERIED_POST_TITLE ); ?>"></iframe>
</main>
<script>
(function(){
	var f = document.getElementById('ah-static-frame');
	function r(){try{f.style.height=f.contentDocument.documentElement.scrollHeight+'px';}catch(e){}}
	f.addEventListener('load',r);
	window.addEventListener('resize',r);
})();
</script>
		<?php
		get_footer();
		exit;
	}
}
get_header();
?>

<main id="main-content">
  <div class="container section--sm">
    <?php ah_breadcrumb(); ?>
  </div>

  <div class="container section">
    <div class="content-layout">
      <article class="prose">
        <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
          <h1><?php the_title(); ?></h1>
          <?php the_content(); ?>
        <?php endwhile; ?>
      </article>

      <!-- Sidebar -->
      <aside class="sidebar">
        <div class="sidebar-card">
          <div class="sidebar-card__title">Need Expert Help?</div>
          <p style="font-size:.875rem;color:var(--text-secondary);margin-bottom:16px">
            Speak to one of our buyer's agents - free, no-obligation consultation.
          </p>
          <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn-primary btn-block">
            Book a Free Call →
          </a>
        </div>

        <div class="sidebar-card">
          <div class="sidebar-card__title">Quick Links</div>
          <div style="display:flex;flex-direction:column;gap:8px">
            <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="toc__item">📚 Buying Guides</a>
            <a href="<?php echo esc_url( home_url( '/services/' ) ); ?>" class="toc__item">✦ Our Services</a>
            <a href="<?php echo esc_url( home_url( '/guides/stamp-duty/' ) ); ?>" class="toc__item">📋 Stamp Duty Calculator</a>
            <a href="<?php echo esc_url( home_url( '/guides/mortgage-guide/' ) ); ?>" class="toc__item">🏦 Mortgage Guide</a>
            <a href="<?php echo esc_url( home_url( '/client-stories/' ) ); ?>" class="toc__item">⭐ Client Stories</a>
          </div>
        </div>
      </aside>
    </div>
  </div>
</main>


<?php get_template_part( 'components/scroll-to-top' ); ?>

<?php get_footer(); ?>
