<?php
/**
 * Component: NIF Sidebar - Popular Now
 *
 * @var array $args {
 *   @type WP_Post[] $popular_posts  Top posts ordered by comment_count.
 * }
 */
defined( 'ABSPATH' ) || exit;

$popular_posts = $args['popular_posts'] ?? [];

if ( empty( $popular_posts ) ) {
	return;
}
?>
<div class="nif-sb-card" aria-label="<?php esc_attr_e( 'Popular Now', 'ah-theme' ); ?>">
  <div class="nif-sb-card__header">
    <span class="nif-section-label--primary"><?php esc_html_e( 'Popular Now', 'ah-theme' ); ?></span>
  </div>
  <ol class="nif-sb-popular">
    <?php foreach ( $popular_posts as $pp ) :
      $pp_cats = get_the_category( $pp->ID );
      $pp_cat  = $pp_cats[0] ?? null;
    ?>
    <li class="nif-sb-popular__item">
      <a href="<?php echo esc_url( get_permalink( $pp->ID ) ); ?>" class="nif-sb-popular__link">
        <?php if ( $pp_cat ) : ?>
          <span class="nif-sb-popular__cat" data-slug="<?php echo esc_attr( $pp_cat->slug ); ?>">
            <?php echo esc_html( $pp_cat->name ); ?>
          </span>
        <?php endif; ?>
        <span class="nif-sb-popular__title"><?php echo esc_html( get_the_title( $pp->ID ) ); ?></span>
      </a>
    </li>
    <?php endforeach; ?>
  </ol>
</div>