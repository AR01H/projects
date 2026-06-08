<?php
defined( 'ABSPATH' ) || exit;

$services       = $args['services']       ?? [];
$service_points = $args['service_points'] ?? [];

if ( ! $services ) return;
?>
<section class="section" aria-label="<?php echo esc_attr( TXT_ALL_SERVICES ); ?>">
  <div class="container">
    <div class="section__header text-center">
      <span class="section__eyebrow">Our Services</span>
      <h2 class="section__title">Everything Covered Under One Roof</h2>
      <p class="section__desc" style="margin-inline:auto">
        We're a full-service buyer's agency - one fee, one team, one point of contact from search to keys.
      </p>
    </div>
    <div class="grid-1" style="display:grid;gap:28px;">
      <?php foreach ( $services as $i => $svc ) :
        $thumb_url = $svc->image_id ? wp_get_attachment_image_url( $svc->image_id, 'medium' ) : '';
      ?>
      <div class="service-card service-card--full" data-aos="fade-up" data-delay="<?php echo ( $i % 3 ) * 100; ?>">
        <div class="service-card__content">
          <div class="service-card__info">
            <div class="service-card__icon-badge"><?php echo esc_html( $svc->icon ?? '✦' ); ?></div>
            <h2 class="service-card__title"><?php echo esc_html( $svc->title ); ?></h2>
            <?php if ( ! empty( $svc->short_desc ) ) : ?>
              <p class="service-card__tagline"><?php echo wp_kses_post( $svc->short_desc ); ?></p>
            <?php endif; ?>
            <?php if ( ! empty( $svc->full_desc ) ) : ?>
              <div class="service-card__body"><?php echo wp_kses_post( $svc->full_desc ); ?></div>
            <?php endif; ?>
          </div>
          <div class="service-card__image-wrap">
            <img class="service-card__image"
                 src="<?php echo esc_url( $thumb_url ); ?>"
                 alt="<?php echo esc_attr( $svc->title ); ?>">
          </div>
        </div>
        <?php if ( ! empty( $service_points[ $svc->id ] ) ) : ?>
          <ul class="service-card__points">
            <?php foreach ( $service_points[ $svc->id ] as $point ) : ?>
              <li><?php echo esc_html( $point['point_text'] ); ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
