<?php
/**
 * Calculators Hub main column (mockup #5): Popular grid + filterable "All" list.
 * Args: popular (array), calculators (array), categories, active, base_url.
 */
defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/components/khub/khub-icons.php';

$popular     = $args['popular']     ?? array();
$calculators = $args['calculators'] ?? array();
$categories  = $args['categories']  ?? array();
$active      = $args['active']      ?? 'all';
$base        = $args['base_url']    ?? get_permalink();

/* Filter the "All" list by the active category. */
$list = ( 'all' === $active )
	? $calculators
	: array_values( array_filter( $calculators, static fn( $c ) => ( $c['category'] ?? '' ) === $active ) );

$pop_card = static function ( array $c ): void { ?>
	<a class="calc-card" href="<?php echo esc_url( $c['url'] ?? '#' ); ?>">
		<span class="calc-card__ico"><?php echo ah_khub_icon( $c['icon'] ?? 'calc', 24 ); ?></span>
		<span class="calc-card__title"><?php echo esc_html( $c['title'] ?? '' ); ?></span>
		<span class="calc-card__desc"><?php echo esc_html( $c['desc'] ?? '' ); ?></span>
		<span class="calc-card__cta">Calculate Now <?php echo ah_khub_icon( 'arrow', 14 ); ?></span>
	</a>
<?php };

$row = static function ( array $c ): void { ?>
	<a class="calc-row" href="<?php echo esc_url( $c['url'] ?? '#' ); ?>">
		<span class="calc-row__ico"><?php echo ah_khub_icon( $c['icon'] ?? 'calc', 20 ); ?></span>
		<span class="calc-row__info">
			<strong class="calc-row__title"><?php echo esc_html( $c['title'] ?? '' ); ?></strong>
			<small class="calc-row__desc"><?php echo esc_html( $c['desc'] ?? '' ); ?></small>
		</span>
		<span class="calc-row__chev"><?php echo ah_khub_icon( 'arrow', 16 ); ?></span>
	</a>
<?php };
?>
<div class="ghub-main">

  <!-- Popular -->
  <?php if ( $popular && 'all' === $active ) : ?>
  <section class="calc-block">
    <h2 class="ghub-allhead__title">Popular Calculators</h2>
    <div class="calc-grid">
      <?php foreach ( $popular as $c ) { $pop_card( $c ); } ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- All / filtered -->
  <section class="calc-block">
    <div class="ghub-allhead">
      <h2 class="ghub-allhead__title">All Calculators <span class="ghub-allhead__count">(<?php echo count( $list ); ?>)</span></h2>
    </div>

    <?php get_template_part( 'components/parent-term-tabs', null, [
      'terms'     => array_map( static fn( $c ) => (object) [ 'slug' => $c['slug'] ?? '', 'name' => $c['label'] ?? '' ], array_filter( $categories, static fn( $c ) => ( $c['slug'] ?? '' ) !== 'all' ) ),
      'active'    => 'all' === $active ? '' : $active,
      'base_url'  => $base,
      'param'     => 'category',
      'all_label' => 'All',
    ] ); ?>

    <?php if ( $list ) : ?>
      <div class="calc-list">
        <?php foreach ( $list as $c ) { $row( $c ); } ?>
      </div>
    <?php else : ?>
      <p class="ghub-empty">No calculators in this category yet.</p>
    <?php endif; ?>
  </section>

</div>
