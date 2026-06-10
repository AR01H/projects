<?php
/**
 * Calculators Hub sidebar (mockup #5): Browse by Category + "Need Help?" card.
 * Args: categories, counts, active, base_url, help (title/desc/cta).
 */
defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/components/khub/khub-icons.php';

$cats   = $args['categories'] ?? array();
$counts = $args['counts']     ?? array();
$active = $args['active']      ?? 'all';
$base   = $args['base_url']    ?? get_permalink();
$help   = $args['help']        ?? array();
?>
<div class="ghub-side">

  <div class="ghub-card">
    <h3 class="ghub-card__title">Browse by Category</h3>
    <ul class="ghub-cats" role="list">
      <?php foreach ( $cats as $c ) :
        $slug  = $c['slug']  ?? '';
        $label = $c['label'] ?? '';
        if ( '' === $slug ) continue;
        $count = (int) ( $counts[ $slug ] ?? 0 );
        $url   = 'all' === $slug ? remove_query_arg( 'category', $base ) : add_query_arg( 'category', $slug, $base );
      ?>
        <li>
          <a class="ghub-cat<?php echo $active === $slug ? ' is-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>">
            <span><?php echo esc_html( $label ); ?></span>
            <?php if ( $count ) : ?><em class="ghub-cat__count"><?php echo $count; ?></em><?php endif; ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <?php if ( ! empty( $help['title'] ) ) : ?>
  <div class="ghub-card calc-help">
    <span class="calc-help__ico"><?php echo ah_khub_icon( 'guide', 22 ); ?></span>
    <h3 class="ghub-card__title"><?php echo esc_html( $help['title'] ); ?></h3>
    <?php if ( ! empty( $help['desc'] ) ) : ?><p class="ghub-card__sub"><?php echo esc_html( $help['desc'] ); ?></p><?php endif; ?>
    <?php if ( ! empty( $help['cta']['label'] ) ) : ?>
      <a class="btn btn-primary" href="<?php echo esc_url( $help['cta']['url'] ?? '#' ); ?>"><?php echo esc_html( $help['cta']['label'] ); ?> <?php echo ah_khub_icon( 'arrow', 14 ); ?></a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

</div>
