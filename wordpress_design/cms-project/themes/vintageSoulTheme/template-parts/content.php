<?php
/**
 * template-parts/content.php - one post's card in a loop (home, archive,
 * search). Included via View::part('content') so home.php/archive.php/
 * search.php don't each repeat this markup - see docs/components.md
 * "template-parts vs components".
 */

use VintageSoul\Support\PostHelper;

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'card' ); ?> id="post-<?php the_ID(); ?>">
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="card__media" href="<?php the_permalink(); ?>">
			<?php the_post_thumbnail( 'vintagesoul-card' ); ?>
		</a>
	<?php endif; ?>
	<div class="card__body">
		<h2 class="card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>
		<p class="card__excerpt"><?php echo esc_html( PostHelper::excerpt( get_the_ID(), 24 ) ); ?></p>
	</div>
</article>
