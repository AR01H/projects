<?php
/**
 * Sidebar: Browse by Topic
 * - ≥2 parent terms → theme-native card list with colored dots + accordion children
 * - ≤1 parent term  → original "Browse by Topic" chip list (WP categories)
 */
defined( 'ABSPATH' ) || exit;

$cats               = $args['cats']               ?? [];
$active_cat         = $args['active_cat']         ?? '';
$permalink          = $args['permalink']           ?? get_permalink();
$parent_terms       = $args['parent_terms']       ?? [];
$active_parent_term = $args['active_parent_term'] ?? '';
$active_pt_obj      = $args['active_pt_obj']      ?? null;
$pt_child_cats      = $args['pt_child_cats']      ?? [];

$use_parent_terms = count( $parent_terms ) >= 2;

if ( ! $use_parent_terms && empty( $cats ) ) return;

?>
<div class="nif-sb-card" aria-label="<?php esc_attr_e( 'Browse by Topic', 'ah-theme' ); ?>">
  <div class="nif-sb-card__header">
    <span class="nif-section-label--primary"><?php esc_html_e( 'Browse by Topic', 'ah-theme' ); ?></span>
  </div>

  <?php if ( $use_parent_terms ) : ?>

  <div class="nif-sb-pt-list">

    <?php
    // "All Topics" row — active when no parent term selected
    $all_active  = ! $active_parent_term;
    $all_row_cls = 'nif-sb-pt-row nif-sb-pt-row--all' . ( $all_active ? ' nif-sb-pt-row--all-active' : '' );
    ?>
    <a href="<?php echo esc_url( $permalink ); ?>" class="<?php echo esc_attr( $all_row_cls ); ?>">
      <span class="nif-sb-pt-dot" style="background: var(--border)"></span>
      <span class="nif-sb-pt-name"><?php esc_html_e( 'All Topics', 'ah-theme' ); ?></span>
      <?php if ( $all_active ) : ?>
        <span class="nif-sb-pt-arrow">▾</span>
      <?php else : ?>
        <span class="nif-sb-pt-arrow">›</span>
      <?php endif; ?>
    </a>

    <?php foreach ( $parent_terms as $pt ) :
      $color     = ! empty( $pt->color ) ? $pt->color : '#1e293b';
      $label     = ( ! empty( $pt->icon_emoji ) ? $pt->icon_emoji . ' ' : '' ) . $pt->name;
      $is_active = ( $active_parent_term === $pt->slug );
      // Clicking an already-active parent term collapses back to All Topics
      $btn_href  = $is_active ? $permalink : add_query_arg( 'parent_term', $pt->slug, $permalink );
    ?>
    <div class="nif-sb-pt-item<?php echo $is_active ? ' nif-sb-pt-item--open' : ''; ?>">

      <a href="<?php echo esc_url( $btn_href ); ?>"
         class="nif-sb-pt-row"
         style="--ptc: <?php echo esc_attr( $color ); ?>">
        <span class="nif-sb-pt-dot" style="background: <?php echo esc_attr( $color ); ?>"></span>
        <span class="nif-sb-pt-name"><?php echo esc_html( $label ); ?></span>
        <span class="nif-sb-pt-arrow"><?php echo $is_active ? '▾' : '›'; ?></span>
      </a>

      <?php if ( $is_active ) : ?>
      <div class="nif-sb-pt-children" style="--ptc: <?php echo esc_attr( $color ); ?>">
        <?php if ( ! empty( $pt_child_cats ) ) : ?>
          <?php foreach ( $pt_child_cats as $cat ) :
            $is_cat_active = ( $active_cat === $cat->slug );
            $cat_href      = add_query_arg( [ 'parent_term' => $active_parent_term, 'category' => $cat->slug ], $permalink );
          ?>
          <a href="<?php echo esc_url( $cat_href ); ?>"
             class="nif-sb-pt-child<?php echo $is_cat_active ? ' nif-sb-pt-child--active' : ''; ?>">
            <?php echo esc_html( $cat->name ); ?>
          </a>
          <?php endforeach; ?>
        <?php else : ?>
          <span class="nif-sb-pt-child--empty"><?php esc_html_e( 'No sub-topics yet', 'ah-theme' ); ?></span>
        <?php endif; ?>
      </div>
      <?php endif; ?>

    </div>
    <?php endforeach; ?>

  </div>

  <?php else : // ── WP categories fallback ──────────────────────────────────── ?>

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

  <?php endif; ?>

</div>
