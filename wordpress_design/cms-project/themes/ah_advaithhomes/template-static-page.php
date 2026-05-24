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

// URL parameter support: ?bare=1  |  ?no_header=1  |  ?no_footer=1  |  ?iframe=true
$bare      = ! empty( $_GET['bare'] );
$no_header = $bare || ! empty( $_GET['no_header'] );
$no_footer = $bare || ! empty( $_GET['no_footer'] );
$iframe    = isset( $_GET['iframe'] ) && $_GET['iframe'] === 'true';

if ( ! $no_header ) {
	get_header();
} else {
	?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
	<?php
}
?>
<main class="container">

	<?php if ( $iframe ) : ?>
	<iframe id="ah-static-frame"
			srcdoc="<?php echo htmlspecialchars( $html_raw, ENT_QUOTES, 'UTF-8' ); ?>"
			style="width:100%;border:none;display:block;min-height:80vh;background:#fff;margin-top:var(--nav-h)"
			title="<?php echo esc_attr( get_the_title() ); ?>">
	</iframe>
	<script>
	(function(){
		var f = document.getElementById('ah-static-frame');
		if ( ! f ) return;
		function r(){try{f.style.height=f.contentDocument.documentElement.scrollHeight+'px';}catch(e){}}
		f.addEventListener('load',r);
		window.addEventListener('resize',r);
	})();
	</script>
	<?php else : ?>
	<div><?php echo $html_raw; ?></div>
	<?php endif; ?>

</main>

<?php
if ( ! $no_footer ) {
	get_template_part( 'components/cta-section' );
	get_footer();
} else {
	wp_footer();
	?>
</body>
</html>
	<?php
}
