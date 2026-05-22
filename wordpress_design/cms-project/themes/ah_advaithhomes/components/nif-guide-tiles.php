<?php
/**
 * Component: NIF Guide Tiles
 * "Latest Guides" — 3-column dark colored tiles from WP posts.
 *
 * @var array $args {
 *   @type WP_Post[] $posts    Up to 6 WP_Post objects.
 *   @type string    $eyebrow  Section label. Default 'Guides & Resources'.
 *   @type string    $see_all  URL for "See all" header link. Default /guides/.
 * }
 */
defined( 'ABSPATH' ) || exit;

$posts   = $args['posts']   ?? [];
$eyebrow = $args['eyebrow'] ?? __( 'Guides & Resources', 'ah-theme' );
$see_all = $args['see_all'] ?? home_url( '/guides/' );

if ( empty( $posts ) ) return;

if ( ! function_exists( 'nif_tile_cat_class' ) ) {
	function nif_tile_cat_class( string $slug ): string {
		static $map = [
			'buying'  => 'cat-buying',
			'first'   => 'cat-first',
			'finance' => 'cat-finance',
			'legal'   => 'cat-legal',
			'invest'  => 'cat-invest',
			'tips'    => 'cat-tips',
			'client'  => 'cat-buying',
		];
		foreach ( $map as $k => $cls ) {
			if ( str_contains( $slug, $k ) ) return $cls;
		}
		return 'cat-default';
	}
}
?>
<section class="nif-portal-section" aria-label="<?php echo esc_attr( $eyebrow ); ?>">

  <div class="nif-portal-section-row">
    <span class="nif-section-label--primary"><?php echo esc_html( $eyebrow ); ?></span>
    <a href="<?php echo esc_url( $see_all ); ?>" class="nif-more-link">
      <?php esc_html_e( 'See all', 'ah-theme' ); ?> <span aria-hidden="true">→</span>
    </a>
  </div>

  <div class="nif-guide-grid">
    <?php foreach ( $posts as $i => $p ) :
      $d        = nif_get_post_data( $p );
      $cat_cls  = $d['cat'] ? nif_tile_cat_class( $d['cat']->slug ) : 'cat-default';
      $bg_style = $d['thumb_url'] ? 'style="--nif-tile-img:url(' . esc_url( $d['thumb_url'] ) . ')"' : '';
    ?>
    <article class="nif-guide-tile nif-guide-tile--<?php echo esc_attr( $cat_cls ); ?>"
             <?php echo $bg_style; ?>
             data-aos="fade-up" data-aos-delay="<?php echo esc_attr( ( $i % 3 ) * 70 ); ?>">

      <div class="nif-guide-tile__overlay" aria-hidden="true"></div>

      <div class="nif-guide-tile__body">
        <?php if ( $d['cat'] ) : ?>
          <span class="nif-tile-badge" data-slug="<?php echo esc_attr( $d['cat']->slug ); ?>">
            <?php echo esc_html( $d['cat']->name ); ?>
          </span>
        <?php endif; ?>

        <h3 class="nif-guide-tile__title">
          <a href="<?php echo esc_url( $d['permalink'] ); ?>">
            <?php echo esc_html( get_the_title( $p->ID ) ); ?>
          </a>
        </h3>

        <p class="nif-guide-tile__excerpt"><?php echo esc_html( $d['excerpt'] ); ?></p>

        <?php if ( $d['read_time'] ) : ?>
        <div class="nif-guide-tile__meta">
          <span class="nif-meta-pill nif-meta-pill--light">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <?php echo esc_html( $d['read_time'] ); ?>
          </span>
        </div>
        <?php endif; ?>
      </div>

    </article>
    <?php endforeach; ?>
  </div>

</section>
