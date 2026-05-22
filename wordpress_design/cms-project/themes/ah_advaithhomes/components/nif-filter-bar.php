<?php
/**
 * Component: NIF Category Filter Bar
 * Reusable topic-filter navigation strip.
 *
 * @var array $args {
 *   @type WP_Term[] $cats       From get_categories()
 *   @type string    $active_cat Active category slug
 *   @type string    $permalink  Base URL for filter links
 * }
 */
defined( 'ABSPATH' ) || exit;

$cats       = $args['cats']       ?? [];
$active_cat = $args['active_cat'] ?? '';
$permalink  = $args['permalink']  ?? get_permalink();

if ( empty( $cats ) ) return;
?>
<div class="nif-filter-wrap">
  <div class="container">
    <nav class="nif-filter-bar" role="tablist" aria-label="<?php esc_attr_e( 'Filter by topic', 'ah-theme' ); ?>">

      <a href="<?php echo esc_url( $permalink ); ?>"
         class="nif-filter-tab<?php echo ! $active_cat ? ' nif-filter-tab--active' : ''; ?>"
         role="tab" aria-selected="<?php echo ! $active_cat ? 'true' : 'false'; ?>">
        <?php esc_html_e( 'All Topics', 'ah-theme' ); ?>
      </a>

      <?php foreach ( $cats as $cat ) :
        $is_active = ( $active_cat === $cat->slug );
      ?>
      <a href="<?php echo esc_url( add_query_arg( 'category', $cat->slug, $permalink ) ); ?>"
         class="nif-filter-tab<?php echo $is_active ? ' nif-filter-tab--active' : ''; ?>"
         role="tab" aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
        <?php echo esc_html( $cat->name ); ?>
        <span class="nif-filter-tab__count"><?php echo esc_html( $cat->count ); ?></span>
      </a>
      <?php endforeach; ?>

    </nav>
  </div>
</div>
