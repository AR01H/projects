<?php get_header(); ?>

<main>
    <h1><?php the_archive_title(); ?></h1>

    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <div class="post-item">
            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            <p><?php the_excerpt(); ?></p>
        </div>
    <?php endwhile; endif; ?>

    <div class="pagination">
        <?php posts_nav_link(); ?>
    </div>
</main>

<?php get_footer(); ?>