<?php
/**
 * Knowledge Hub audience cards ("I am Buying / Selling / New / Need Guidance").
 * Args: ['cards' => [ ['icon','variant','title','desc','cta','url'], ... ] ]
 */
defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/components/khub/khub-icons.php';

$cards = $args['cards'] ?? array();
if ( ! $cards ) {
	return;
}
?>
<section class="khub-audience" aria-label="Choose your path">
  <div class="container">
    <div class="khub-audience__grid">
      <?php foreach ( $cards as $c ) :
        $variant = ( ( $c['variant'] ?? 'navy' ) === 'gold' ) ? 'gold' : 'navy';
      ?>
        <a class="khub-aud-card" href="<?php echo esc_url( $c['url'] ?? '#' ); ?>">
          <span class="khub-aud-card__ico khub-aud-card__ico--<?php echo esc_attr( $variant ); ?>">
            <?php echo ah_khub_icon( $c['icon'] ?? 'home', 26 ); ?>
          </span>
          <h3 class="khub-aud-card__title"><?php echo esc_html( $c['title'] ?? '' ); ?></h3>
          <p class="khub-aud-card__desc"><?php echo esc_html( $c['desc'] ?? '' ); ?></p>
          <span class="khub-aud-card__cta">
            <?php echo esc_html( $c['cta'] ?? 'Learn more' ); ?> <?php echo ah_khub_icon( 'arrow', 15 ); ?>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
