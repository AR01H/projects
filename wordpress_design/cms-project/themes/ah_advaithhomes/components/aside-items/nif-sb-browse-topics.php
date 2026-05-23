<?php
/**
 * Component: NIF Sidebar — Browse by Topic
 *
 * @var array $args {
 *   @type WP_Term[] $cats        WP categories to list.
 *   @type string    $active_cat  Currently active category slug.
 *   @type string    $permalink   Base page permalink for filter links.
 * }
 */
defined( 'ABSPATH' ) || exit;

$cats       = $args['cats']       ?? [];
$active_cat = $args['active_cat'] ?? '';
$permalink  = $args['permalink']  ?? get_permalink();

if ( empty( $cats ) ) {
	return;
}
?>
<div class="nif-sb-card" aria-label="<?php esc_attr_e( 'Browse by Topic', 'ah-theme' ); ?>">
  <div class="nif-sb-card__header">
    <span class="nif-section-label--primary"><?php esc_html_e( 'Browse by Topic', 'ah-theme' ); ?></span>
  </div>
  <div class="nif-sb-topics">
    <a href="<?php echo esc_url( $permalink ); ?>"
       class="nif-sb-topic<?php echo ! $active_cat ? ' nif-sb-topic--active' : ''; ?>">
      <?php esc_html_e( 'All', 'ah-theme' ); ?>
    </a>
    <?php foreach ( $cats as $cat ) :
      $is_active = ( $active_cat === $cat->slug );
    ?>
    <a href="<?php echo esc_url( add_query_arg( 'category', $cat->slug, $permalink ) ); ?>"
       class="nif-sb-topic<?php echo $is_active ? ' nif-sb-topic--active' : ''; ?>"
       data-slug="<?php echo esc_attr( $cat->slug ); ?>">
      <?php echo esc_html( $cat->name ); ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>