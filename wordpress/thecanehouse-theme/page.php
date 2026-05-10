<?php get_header(); ?>

<main style="padding-top:100px;min-height:60vh;">
  <div style="max-width:900px;margin:0 auto;padding:2rem;">
    <?php if(have_posts()): while(have_posts()): the_post(); ?>
      <h1><?php the_title(); ?></h1>
      <div><?php the_content(); ?></div>
    <?php endwhile; endif; ?>
  </div>
</main>

<?php get_footer(); ?>
