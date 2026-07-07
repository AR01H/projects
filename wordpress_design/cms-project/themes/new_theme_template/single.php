<?php
/**
 * Single blog post template.
 */
defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="nt-container nt-section">

	<?php
	while ( have_posts() ) {
		the_post();
		?>
		<article <?php post_class( 'nt-entry nt-single' ); ?>>

			<header class="nt-entry-header">
				<h1 class="nt-entry-title"><?php the_title(); ?></h1>
				<p class="nt-entry-meta">
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
					<span class="nt-entry-author"><?php the_author(); ?></span>
				</p>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="nt-entry-thumb"><?php the_post_thumbnail( 'large' ); ?></figure>
			<?php endif; ?>

			<div class="nt-entry-content"><?php the_content(); ?></div>

		</article>
		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
	}
	?>

</div>
<?php
get_footer();
