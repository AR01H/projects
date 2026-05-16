<?php
defined( 'ABSPATH' ) || exit;

$services = ah_get_services();
if ( empty( $services ) ) {
	$services = ah_static_services();
}

$imgs = [
	ah_unsplash('1560518883-ce09059eeffa'),
	ah_unsplash('1573497019940-1c28c88b4f3e'),
	ah_unsplash('1560520653-9e0e4c89eb11'),
	ah_unsplash('1450101499163-c8848c66ca85'),
	ah_unsplash('1589829545856-d10d557cf95f'),
	ah_unsplash('1560520653-9e0e4c89eb11'),
];
?>
<section class="feature-section">
  <div class="container">
    <div style="text-align:center;margin-bottom:60px">
      <div class="eyebrow reveal" style="color:var(--accent);justify-content:center">
        <?php esc_html_e( 'Our Expertise', 'ah-theme' ); ?>
      </div>
      <h2 class="reveal reveal-delay-1" style="font-size:clamp(1.8rem,4vw,2.8rem)">
        <?php esc_html_e( 'Find Your Dream Property', 'ah-theme' ); ?>
      </h2>
    </div>

    <div class="feature-grid">
      <?php
      $delays = [ 'reveal-delay-1', 'reveal-delay-2', 'reveal-delay-3' ];
      foreach ( array_slice( $services, 0, 6 ) as $i => $svc ) :
        $title = is_object($svc) ? ($svc->title ?? '') : ($svc['title'] ?? '');
        $desc  = is_object($svc) ? ($svc->description ?? '') : ($svc['description'] ?? '');
        $img_id= is_object($svc) ? ($svc->image_id ?? 0) : ($svc['image_id'] ?? 0);
        $img   = $img_id ? ah_media_url($img_id) : ( $imgs[$i] ?? $imgs[0] );
        $delay = $delays[$i % 3];
      ?>
        <div class="feature-card reveal <?php echo esc_attr($delay); ?>">
          <div class="feature-card__img-wrap">
            <img src="<?php echo esc_url($img); ?>"
                 alt="<?php echo esc_attr($title); ?>"
                 class="feature-card__img"
                 loading="lazy">
          </div>
          <h3 class="feature-card__title"><?php echo esc_html($title); ?></h3>
          <p class="feature-card__desc"><?php echo esc_html($desc); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
