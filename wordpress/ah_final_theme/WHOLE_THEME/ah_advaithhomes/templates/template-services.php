<?php
/**
 * Template Name: Services
 */
defined( 'ABSPATH' ) || exit;

get_template_part( 'parts/header' );

$services = ah_get_services();
if ( empty( $services ) ) {
	$services = ah_static_services();
}

$settings = ah_get_settings();
$consult  = $settings['consultation_url'] ?? home_url( '/free-consultation/' );
?>
<main id="main-content">

  <!-- Page Hero -->
  <section class="page-hero">
    <div class="container">
      <div class="eyebrow reveal" style="color:var(--accent)"><?php esc_html_e( 'What We Do', 'ah-theme' ); ?></div>
      <h1 class="reveal reveal-delay-1"><?php esc_html_e( 'Our Services', 'ah-theme' ); ?></h1>
      <p class="reveal reveal-delay-2">
        <?php esc_html_e( 'Expert buying-agent services tailored to every type of property buyer — from first-time purchasers to seasoned investors.', 'ah-theme' ); ?>
      </p>
    </div>
  </section>

  <!-- Services Grid (all, not just 6) -->
  <section class="section">
    <div class="container">
      <div class="feature-grid">
        <?php
        $delays = [ 'reveal-delay-1', 'reveal-delay-2', 'reveal-delay-3' ];
        $imgs   = [
          ah_unsplash( '1560518883-ce09059eeffa' ),
          ah_unsplash( '1573497019940-1c28c88b4f3e' ),
          ah_unsplash( '1560520653-9e0e4c89eb11' ),
          ah_unsplash( '1450101499163-c8848c66ca85' ),
          ah_unsplash( '1589829545856-d10d557cf95f' ),
          ah_unsplash( '1600596542815-ffad4c1539a9' ),
        ];
        foreach ( $services as $i => $svc ) :
          $title  = ah_val( $svc, 'title' );
          $desc   = ah_val( $svc, 'description' );
          $img_id = ah_val( $svc, 'image_id', 0 );
          $img    = $img_id ? ah_media_url( $img_id ) : ( $imgs[ $i % count( $imgs ) ] ?? $imgs[0] );
          $delay  = $delays[ $i % 3 ];
        ?>
          <div class="feature-card reveal <?php echo esc_attr( $delay ); ?>">
            <div class="feature-card__img-wrap">
              <img src="<?php echo esc_url( $img ); ?>"
                   alt="<?php echo esc_attr( $title ); ?>"
                   class="feature-card__img"
                   loading="lazy">
            </div>
            <h3 class="feature-card__title"><?php echo esc_html( $title ); ?></h3>
            <p class="feature-card__desc"><?php echo esc_html( $desc ); ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php get_template_part( 'components/diff-table' ); ?>
  <?php get_template_part( 'components/dream-properties' ); ?>
  <?php get_template_part( 'components/faqs' ); ?>
  <?php get_template_part( 'components/cta' ); ?>

</main>
<?php get_template_part( 'parts/footer' ); ?>
