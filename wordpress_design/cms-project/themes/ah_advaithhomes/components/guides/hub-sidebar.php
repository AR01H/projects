<?php
/**
 * Guides Hub sidebar (mockup #2): search · categories · popular topics.
 * Args: display_cats (category rows), popular_topics (WP_Term tags), active_cat.
 */
defined( 'ABSPATH' ) || exit;

$cats       = $args['display_cats']  ?? array();
$topics     = $args['popular_topics'] ?? array();
$active_cat = $args['active_cat']     ?? '';
$base       = get_permalink();
?>
<div class="ghub-side">

  <!-- Search -->
  <form class="ghub-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <svg class="ghub-search__ico" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input type="search" name="s" class="ghub-search__input" placeholder="Search guides…" aria-label="Search guides">
  </form>

  <!-- Categories -->
  <?php if ( $cats ) : ?>
  <div class="ghub-card">
    <h3 class="ghub-card__title">Categories</h3>
    <ul class="ghub-cats" role="list">
      <li>
        <a class="ghub-cat<?php echo $active_cat === '' ? ' is-active' : ''; ?>" href="<?php echo esc_url( $base ); ?>">
          <span>All Guides</span>
        </a>
      </li>
      <?php foreach ( $cats as $c ) :
        $c = is_object( $c ) ? (array) $c : $c;
        $slug  = $c['slug']  ?? '';
        $name  = $c['title'] ?? ( $c['name'] ?? '' );
        $count = (int) ( $c['count'] ?? 0 );
        if ( ! $slug ) continue;
      ?>
        <li>
          <a class="ghub-cat<?php echo $active_cat === $slug ? ' is-active' : ''; ?>"
             href="<?php echo esc_url( add_query_arg( 'category', $slug, $base ) ); ?>">
            <span><?php echo esc_html( $name ); ?></span>
            <?php if ( $count ) : ?><em class="ghub-cat__count"><?php echo $count; ?></em><?php endif; ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <!-- Popular topics -->
  <?php if ( $topics ) : ?>
  <div class="ghub-card">
    <h3 class="ghub-card__title">Popular Topics</h3>
    <div class="ghub-topics">
      <?php foreach ( $topics as $t ) : ?>
        <a class="ghub-topic" href="<?php echo esc_url( get_tag_link( $t ) ); ?>"><?php echo esc_html( $t->name ); ?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</div>
