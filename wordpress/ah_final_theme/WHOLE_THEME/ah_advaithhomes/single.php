<?php
defined( 'ABSPATH' ) || exit;

get_template_part( 'parts/header' );

while ( have_posts() ) :
  the_post();

  $post_id    = get_the_ID();
  $title      = get_the_title();
  $date       = get_the_date( 'j F Y' );
  $author     = get_the_author();
  $categories = get_the_category();
  $cat_name   = ! empty( $categories ) ? $categories[0]->name : '';
  $thumb      = get_the_post_thumbnail_url( $post_id, 'large' );
  $read_time  = ceil( str_word_count( strip_tags( get_the_content() ) ) / 200 );
?>
<main id="main-content">

  <!-- Article Hero -->
  <section class="article-hero">
    <div class="container" style="max-width:860px">
      <?php if ( $cat_name ) : ?>
        <div class="eyebrow reveal" style="color:var(--accent)"><?php echo esc_html( $cat_name ); ?></div>
      <?php endif; ?>
      <h1 class="reveal reveal-delay-1"><?php echo esc_html( $title ); ?></h1>
      <div class="article-meta reveal reveal-delay-2">
        <span><?php echo esc_html( $author ); ?></span>
        <span>•</span>
        <time><?php echo esc_html( $date ); ?></time>
        <span>•</span>
        <span>⏱ <?php printf( esc_html__( '%d min read', 'ah-theme' ), $read_time ); ?></span>
      </div>
    </div>
  </section>

  <!-- Featured Image -->
  <?php if ( $thumb ) : ?>
    <div class="article-hero-img reveal">
      <div class="container" style="max-width:860px">
        <img src="<?php echo esc_url( $thumb ); ?>"
             alt="<?php echo esc_attr( $title ); ?>"
             class="article-featured-img"
             loading="eager">
      </div>
    </div>
  <?php endif; ?>

  <!-- Article Body -->
  <article class="section article-body">
    <div class="container" style="max-width:760px">
      <div class="article-content reveal">
        <?php the_content(); ?>
      </div>

      <!-- Tags -->
      <?php
      $tags = get_the_tags();
      if ( $tags ) :
      ?>
        <div class="article-tags">
          <?php foreach ( $tags as $tag ) : ?>
            <a href="<?php echo esc_url( get_tag_link( $tag ) ); ?>" class="tag"><?php echo esc_html( $tag->name ); ?></a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Author Box -->
      <div class="author-box reveal">
        <div class="author-box__avatar">
          <?php echo get_avatar( get_the_author_meta( 'email' ), 64, '', '', [ 'class' => 'author-box__img' ] ); ?>
        </div>
        <div>
          <div class="author-box__name"><?php echo esc_html( $author ); ?></div>
          <p class="author-box__bio"><?php echo esc_html( get_the_author_meta( 'description' ) ?: __( 'Expert buyer agent at Advaith Homes.', 'ah-theme' ) ); ?></p>
        </div>
      </div>

      <!-- Post Navigation -->
      <nav class="post-nav" aria-label="<?php esc_attr_e( 'Article navigation', 'ah-theme' ); ?>">
        <?php
        $prev = get_previous_post();
        $next = get_next_post();
        if ( $prev ) :
        ?>
          <a href="<?php echo esc_url( get_permalink( $prev ) ); ?>" class="post-nav__link post-nav__link--prev">
            <span class="post-nav__dir">← <?php esc_html_e( 'Previous', 'ah-theme' ); ?></span>
            <span class="post-nav__title"><?php echo esc_html( get_the_title( $prev ) ); ?></span>
          </a>
        <?php endif; ?>
        <?php if ( $next ) : ?>
          <a href="<?php echo esc_url( get_permalink( $next ) ); ?>" class="post-nav__link post-nav__link--next">
            <span class="post-nav__dir"><?php esc_html_e( 'Next', 'ah-theme' ); ?> →</span>
            <span class="post-nav__title"><?php echo esc_html( get_the_title( $next ) ); ?></span>
          </a>
        <?php endif; ?>
      </nav>
    </div>
  </article>

</main>

<?php endwhile; ?>

<?php get_template_part( 'components/cta' ); ?>
<?php get_template_part( 'parts/footer' ); ?>
