<?php
/**
 * Areas - search + popular areas card grid.
 * Args: title, sub, items => [ { name, region, url }, ... ]
 */
defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/components/khub/khub-icons.php';

$title = $args['title'] ?? 'Popular Areas';
$sub   = $args['sub']   ?? '';
$items = $args['items'] ?? array();
?>
<section class="areas-popular">
  <div class="container">

    <form class="areas-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
      <span class="areas-search__ico"><?php echo ah_khub_icon( 'search', 18 ); ?></span>
      <input type="search" name="s" class="areas-search__input" placeholder="Search for an area or postcode…" aria-label="Search areas">
      <button type="submit" class="btn btn-primary areas-search__btn">Search</button>
    </form>

    <?php if ( $title ) : ?>
    <div class="areas-popular__head">
      <h2 class="areas-popular__title"><?php echo esc_html( $title ); ?></h2>
      <?php if ( $sub ) : ?><p class="areas-popular__sub"><?php echo esc_html( $sub ); ?></p><?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ( $items ) : ?>
    <div class="areas-grid">
      <?php foreach ( $items as $a ) : ?>
        <a class="area-card" href="<?php echo esc_url( $a['url'] ?? '#' ); ?>">
          <span class="area-card__pin"><?php echo ah_khub_icon( 'home', 20 ); ?></span>
          <span class="area-card__body">
            <strong class="area-card__name"><?php echo esc_html( $a['name'] ?? '' ); ?></strong>
            <?php if ( ! empty( $a['region'] ) ) : ?><small class="area-card__region"><?php echo esc_html( $a['region'] ); ?></small><?php endif; ?>
          </span>
          <span class="area-card__cta">View Guide <?php echo ah_khub_icon( 'arrow', 14 ); ?></span>
        </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</section>
