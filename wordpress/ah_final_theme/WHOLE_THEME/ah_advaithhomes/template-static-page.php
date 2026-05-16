<?php
/*
Template Name: Static HTML Page
*/
$slug       = get_post_field( 'post_name', get_queried_object_id() );
$static_dir = trailingslashit( get_template_directory() ) . 'static/';
$real_dir   = realpath( $static_dir );
$file       = $real_dir ? realpath( $real_dir . DIRECTORY_SEPARATOR . sanitize_file_name( $slug ) . '.html' ) : false;

if ( $file && strpos( $file, $real_dir ) === 0 && file_exists( $file ) ) {
	$html_raw = file_get_contents( $file );
} else {
	$html_raw = '<h2 style="font-family:sans-serif;padding:40px 24px;color:#1e293b">Page content not found.</h2>';
}

get_header();
?>
<main style="margin:0;padding:0">
	<iframe id="ah-static-frame"
	        srcdoc="<?php echo htmlspecialchars( $html_raw, ENT_QUOTES, 'UTF-8' ); ?>"
	        style="width:100%;border:none;display:block;min-height:80vh;background:#fff"
	        title="<?php echo esc_attr( get_the_title() ); ?>"></iframe>
</main>
<script>
(function(){
	var f = document.getElementById('ah-static-frame');
	function r(){try{f.style.height=f.contentDocument.documentElement.scrollHeight+'px';}catch(e){}}
	f.addEventListener('load',r);
	window.addEventListener('resize',r);
})();
</script>
<?php get_footer(); ?>
