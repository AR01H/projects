<?php
/**
 * Component: NIF Category Filter Bar
 * Reusable topic-filter navigation strip.
 * Uses CMS parent terms (colored tabs) when ≥ 2 are active, otherwise falls back to WP categories.
 *
 * @var array $args {
 *   @type object[]  $parent_terms        CMS parent term objects (id, name, slug, color) — preferred
 *   @type string    $active_parent_term  Active parent term slug
 *   @type WP_Term[] $cats                WP categories (fallback)
 *   @type string    $active_cat          Active WP category slug (fallback)
 *   @type string    $permalink           Base URL for filter links
 * }
 */
defined( 'ABSPATH' ) || exit;

$parent_terms       = $args['parent_terms']       ?? [];
$active_parent_term = $args['active_parent_term'] ?? '';
$cats               = $args['cats']               ?? [];
$active_cat         = $args['active_cat']         ?? '';
$permalink          = $args['permalink']           ?? get_permalink();

$use_parent_terms = count( $parent_terms ) >= 2;

if ( ! $use_parent_terms && empty( $cats ) ) return;

$all_active = $use_parent_terms ? ! $active_parent_term : ! $active_cat;
?>
<div class="nif-filter-wrap">
  <div class="container">
    <div class="nif-filter-inner">
      <span class="nif-filter-label"><?php esc_html_e( 'Topics', 'ah-theme' ); ?></span>
      <nav class="nif-filter-bar<?php echo $use_parent_terms ? ' nif-filter-bar--pt' : ''; ?>" role="tablist" aria-label="<?php esc_attr_e( 'Filter by topic', 'ah-theme' ); ?>">

      <a href="<?php echo esc_url( $permalink ); ?>"
         class="nif-filter-tab<?php echo $all_active ? ' nif-filter-tab--active' : ''; ?>"
         role="tab" aria-selected="<?php echo $all_active ? 'true' : 'false'; ?>">
        <?php esc_html_e( 'All', 'ah-theme' ); ?>
      </a>

      <?php if ( $use_parent_terms ) : ?>

        <?php foreach ( $parent_terms as $pt ) :
          $is_active = ( $active_parent_term === $pt->slug );
          $color     = ! empty( $pt->color ) ? $pt->color : '#1a1a2e';
          $label     = ( ! empty( $pt->icon_emoji ) ? $pt->icon_emoji . ' ' : '' ) . $pt->name;
        ?>
        <a href="<?php echo esc_url( add_query_arg( 'parent_term', $pt->slug, $permalink ) ); ?>"
           class="nif-filter-tab nif-filter-tab--colored<?php echo $is_active ? ' nif-filter-tab--colored-active' : ''; ?>"
           style="--pt-color: <?php echo esc_attr( $color ); ?>"
           role="tab" aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
          <?php echo esc_html( $label ); ?>
        </a>
        <?php endforeach; ?>

      <?php else : ?>

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

      <?php endif; ?>

      </nav>
    </div><!-- /.nif-filter-inner -->
  </div>
</div>
