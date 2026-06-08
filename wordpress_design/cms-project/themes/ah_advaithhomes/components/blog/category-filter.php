<?php
defined( 'ABSPATH' ) || exit;
$wp_cats    = $args['wp_cats']    ?? [];
$active_cat = $args['active_cat'] ?? '';
if ( ! $wp_cats ) return;
?>
<div style="border-bottom:1px solid var(--border);background:var(--bg-alt)">
  <div class="container" style="padding-top:16px;padding-bottom:16px">
    <div class="filter-tabs" role="tablist" aria-label="<?php echo esc_attr( TXT_BLOG_CATEGORIES ); ?>">
      <a href="<?php echo esc_url( get_permalink() ); ?>"
         class="filter-tab<?php if ( ! $active_cat ) echo esc_html( TXT_FILTER_TAB_ACTIVE ); ?>"
         role="tab" aria-selected="<?php echo ! $active_cat ? 'true' : 'false'; ?>">
        <?php echo esc_html( AH_LABEL_ALL_POSTS ); ?>
      </a>
      <?php foreach ( $wp_cats as $cat ) :
        $is_active = ( $active_cat === $cat->slug );
      ?>
      <a href="<?php echo esc_url( add_query_arg( 'category', $cat->slug, get_permalink() ) ); ?>"
         class="filter-tab<?php if ( $is_active ) echo esc_html( TXT_FILTER_TAB_ACTIVE ); ?>"
         role="tab" aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
        <?php echo esc_html( $cat->name ); ?>
        <span style="font-size:.75rem;opacity:.7;margin-left:4px">(<?php echo esc_html( $cat->count ); ?>)</span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
