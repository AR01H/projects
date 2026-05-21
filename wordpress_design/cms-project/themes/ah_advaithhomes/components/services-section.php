<?php
defined( 'ABSPATH' ) || exit;

$services = ah_get_services();
?>

<section class="section what-we-do" aria-label="Services">
  <div class="container">
    <div class="section__header text-center">
      <span class="section__eyebrow">What We Do</span>
      <h2 class="section__title">Full-Service Buyer Representation</h2>
      <p class="section__desc" style="margin-inline:auto">
        From your first search to completion day - we handle every step so you can focus on the move, not the process.
      </p>
    </div>

    <div class="grid-3">
      <?php foreach ( $services as $i => $svc ) : ?>
      <div class="service-card" data-aos="fade-up" data-delay="<?php echo ( $i % 3 ) * 100; ?>">
        <div class="service-card__icon">
         
          <div class="service-card__title service-card__title-mini">  <?php echo esc_html( $svc->icon ?? '✦' ); ?> <?php echo esc_html( $svc->title ); ?></div>
        </div>
        
        <p class="service-card__body"><?php echo esc_html( $svc->short_desc ?? $svc->short_desc ?? '' ); ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center" style="margin-top:40px">
      <a href="<?php echo esc_url( home_url( '/services/' ) ); ?>" class="btn btn-outline">
        See all services →
      </a>
    </div>
  </div>
</section>
