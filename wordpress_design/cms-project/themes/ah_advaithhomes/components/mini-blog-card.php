<?php
/**
 * Component: Mini Blog Card
 * Image fills the card background; content overlays at the bottom.
 *
 * @var array $args {
 *   @type WP_Post $post  The post object.
 *   @type int     $idx   Zero-based loop index.
 * }
 */

$post        = $args['post'] ?? null;
$idx         = $args['idx']  ?? 0;

if ( ! $post instanceof WP_Post ) {
    return;
}

$is_featured = $idx === 0 || get_post_meta( $post->ID, '_ah_featured', true );
$cats        = get_the_category( $post->ID );
$cat_name    = ! empty( $cats ) ? $cats[0]->name : '';
$permalink   = esc_url( get_permalink( $post ) );
?>

<article class="mini-blog-post-cart<?php echo $is_featured ? ' mini-blog-post-cart--featured' : ''; ?>"
         data-aos="fade-up"
         data-delay="<?php echo esc_attr( $idx * 100 ); ?>">

  <?php if ( has_post_thumbnail( $post ) ) : ?>
    <div class="mini-blog-post-cart__img-wrap">
      <a href="<?php echo $permalink; ?>" tabindex="-1" aria-hidden="true">
        <?php echo get_the_post_thumbnail( $post, 'ah-card' ); ?>
      </a>
    </div>
  <?php endif; ?>

  <div class="mini-blog-post-cart__overlay"></div>

  <div class="mini-blog-post-cart__body">

    <?php if ( $cat_name ) : ?>
      <span class="mini-blog-post-cart__cat"><?php echo esc_html( $cat_name ); ?></span>
    <?php endif; ?>

    <h3 class="mini-blog-post-cart__title">
      <a href="<?php echo $permalink; ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a>
    </h3>

    <p class="mini-blog-post-cart__excerpt">
      <?php echo esc_html( wp_trim_words( get_the_excerpt( $post ), 20, '…' ) ); ?>
    </p>

    <a href="<?php echo $permalink; ?>" class="btn btn-sm btn-ghost">Read →</a>

  </div>
</article>