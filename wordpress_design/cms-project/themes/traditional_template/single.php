<?php
/**
 * Single blog post template.
 *
 * Uses the same cinematic "poster" page_header as the News listing - the
 * post's own featured image becomes the backdrop (falls back to a vintage
 * default photo when a post has none), with title/date/author overlaid.
 */
defined( 'ABSPATH' ) || exit;

get_header();

$nt_hdr = nt_data( 'page_headers' )['blog_post'] ?? array();

while ( have_posts() ) :
	the_post();

	$nt_poster_img = get_the_post_thumbnail_url( get_the_ID(), 'large' );
	if ( ! $nt_poster_img ) {
		$nt_poster_img = $nt_hdr['fallback_image'] ?? '';
	}

	nt_component( 'parts/page_header', array(
		'tag'   => $nt_hdr['tag']  ?? '',
		'icon'  => $nt_hdr['icon'] ?? '',
		'title' => get_the_title(),
		'meta'  => sprintf(
			/* translators: 1: post date, 2: author name */
			__( '%1$s · By %2$s', NT_TEXT_DOMAIN ),
			get_the_date(),
			get_the_author()
		),
		'image' => $nt_poster_img,
	) );
	?>

	<article <?php post_class( 'nt-entry nt-single' ); ?>>
		<div class="container nt-entry__wrap">
			<div class="nt-entry-content"><?php the_content(); ?></div>
		</div>
	</article>

	<?php
	if ( comments_open() || get_comments_number() ) {
		echo '<div class="container">';
		comments_template();
		echo '</div>';
	}

endwhile;

get_footer();
