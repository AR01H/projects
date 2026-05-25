<?php
/**
 * Sidebar for the MultiInfo portal template.
 * Reuses existing aside components; replaces "Browse by Topic" with
 * a context-aware navigation card.
 *
 * @var array $args {
 *   @type object|null $active_pt      Active parent term object, or null.
 *   @type string      $active_slug    Active parent term slug (empty = root).
 *   @type string      $active_cat     Active sub-category slug.
 *   @type WP_Term[]   $pt_child_cats  Sub-categories of active parent term.
 *   @type object[]    $parent_terms   All parent terms (for root navigation card).
 *   @type WP_Post[]   $popular_posts  Scoped popular posts.
 *   @type array       $site_stats     Market pulse data.
 *   @type array       $news_bar_items Flash updates data.
 *   @type string      $base_url       Canonical URL for this page.
 * }
 */
defined( 'ABSPATH' ) || exit;

$active_pt     = $args['active_pt']     ?? null;
$active_slug   = $args['active_slug']   ?? '';
$active_cat    = $args['active_cat']    ?? '';
$pt_child_cats = $args['pt_child_cats'] ?? [];
$parent_terms  = $args['parent_terms']  ?? [];
$popular_posts = $args['popular_posts'] ?? [];
$site_stats    = $args['site_stats']    ?? [];
$news_bar_items = $args['news_bar_items'] ?? [];
$base_url      = $args['base_url']      ?? home_url( '/multiinfo/' );
?>

<!-- ── Flash Updates ──────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/aside-items/nif-sb', 'flash-updates', [
	'news_bar_items' => $news_bar_items,
] ); ?>

<!-- ── Topics navigation card ────────────────────────────────────────────── -->
<?php if ( $active_pt ) : ?>
<!-- On a parent-term page: show subcategories of that term -->
<div class="nif-sb-card" aria-label="<?php echo esc_attr( TXT_SUBCATEGORIES ); ?>">
  <div class="nif-sb-card__header">
    <span class="nif-section-label--primary">
      <?php echo esc_html( sprintf( TXT_IN_S, $active_pt->name ) ); ?>
    </span>
  </div>

  <?php $pt_color = ! empty( $active_pt->color ) ? $active_pt->color : 'var(--accent)'; ?>

  <a href="<?php echo esc_url( $base_url ); ?>"
     class="nif-sb-pt-row nif-sb-pt-row--all<?php echo ! $active_cat ? ' nif-sb-pt-row--all-active' : ''; ?>">
    <span class="nif-sb-pt-dot" style="background:<?php echo esc_attr( $pt_color ); ?>"></span>
    <span class="nif-sb-pt-name"><?php echo esc_html( TXT_ALL ); ?></span>
    <span class="nif-sb-pt-arrow"><?php echo ! $active_cat ? '▾' : '›'; ?></span>
  </a>

  <?php if ( ! empty( $pt_child_cats ) ) : ?>
  <div class="nif-sb-pt-children nif-sb-pt-children--always" style="--ptc:<?php echo esc_attr( $pt_color ); ?>">
    <?php foreach ( $pt_child_cats as $fc ) :
      $is_active = ( $active_cat === $fc->slug );
    ?>
    <a href="<?php echo esc_url( add_query_arg( 'cat', $fc->slug, $base_url ) ); ?>"
       class="nif-sb-pt-child<?php echo $is_active ? ' nif-sb-pt-child--active' : ''; ?>">
      <?php echo esc_html( $fc->name ); ?>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Link to the standalone topic page e.g. /buying-term/ -->
  <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;border-top:1px solid var(--border);">
    <a href="<?php echo esc_url( home_url( '/multiinfo/' ) ); ?>"
       style="display:inline-flex;align-items:center;gap:4px;font-size:.78rem;color:var(--text-secondary);text-decoration:none;">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
      <?php echo esc_html( TXT_ALL_TOPICS ); ?>
    </a>
    <a href="<?php echo esc_url( home_url( '/' . $active_slug . '/' ) ); ?>"
       style="display:inline-flex;align-items:center;gap:4px;font-size:.78rem;font-weight:600;color:var(--accent);text-decoration:none;">
      <?php echo esc_html( TXT_FULL_PAGE ); ?>
      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
    </a>
  </div>
</div>

<?php else : ?>
<!-- On the root /multiinfo/ page: show all parent terms as a card list -->
<?php if ( ! empty( $parent_terms ) ) : ?>
<div class="nif-sb-card" aria-label="<?php echo esc_attr( TXT_PHP_ECHO_ESC_ATTR_TXT_BROWSE_BY_TOPIC ); ?>">
  <div class="nif-sb-card__header">
    <span class="nif-section-label--primary"><?php echo esc_html( TXT_EXPLORE_TOPICS ); ?></span>
  </div>
  <div class="nif-sb-pt-list">
    <?php foreach ( $parent_terms as $pt ) :
      $color = ! empty( $pt->color ) ? $pt->color : '#1e293b';
      $label = ( ! empty( $pt->icon_emoji ) ? $pt->icon_emoji . ' ' : '' ) . $pt->name;
    ?>
    <a href="<?php echo esc_url( home_url( '/multiinfo/' . $pt->slug . '/' ) ); ?>"
       class="nif-sb-pt-row"
       style="--ptc:<?php echo esc_attr( $color ); ?>">
      <span class="nif-sb-pt-dot" style="background:<?php echo esc_attr( $color ); ?>"></span>
      <span class="nif-sb-pt-name"><?php echo esc_html( $label ); ?></span>
      <span class="nif-sb-pt-arrow">›</span>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- ── Market Pulse ───────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/aside-items/nif-sb', 'market-pulse', [
	'site_stats' => $site_stats,
] ); ?>

<!-- ── Popular Now (scoped to active parent term when set) ───────────────── -->
<?php get_template_part( 'components/aside-items/nif-sb', 'popular-now', [
	'popular_posts' => $popular_posts,
] ); ?>

<!-- ── Weekly Briefing ───────────────────────────────────────────────────── -->
<?php get_template_part( 'components/aside-items/nif-sb', 'weekly-briefing' ); ?>
