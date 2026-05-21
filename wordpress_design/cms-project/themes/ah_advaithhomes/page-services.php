<?php
/**
 * Template Name: Services Page
 */
get_header();

$services = ah_get_services( 12 );
$service_points = ah_get_services_bullet_points(array_column($services,'id'));
$steps    = ah_get_process_steps();
$faqs     = ah_get_faqs( 8 );
?>

<?php get_template_part( 'components/page-header', null, [
	'eyebrow'    => 'What We Do',
	'title'      => 'Full-Service<br><em>Buyer Representation</em>',
	'title_em'   => 'Services',
	'desc'       => 'From your first search to completion day, we handle every step of the buying process - so you make the right decision at the right price, every time.',
	'breadcrumb' => [
		[ 'Home', home_url( '/' ) ],
		[ 'Services', '' ],
	],
] ); ?>

<!-- ── All Services ──────────────────────────────────────────────────────── -->
<?php if ( $services ) : ?>
<section class="section" aria-label="All services">
  <div class="container">
    <div class="section__header text-center">
      <span class="section__eyebrow">Our Services</span>
      <h2 class="section__title">Everything Covered Under One Roof</h2>
      <p class="section__desc" style="margin-inline:auto">
        We're a full-service buyer's agency - one fee, one team, one point of contact from search to keys.
      </p>
    </div>
    <div class="grid-1" style="display:grid;gap:20px;">
      <?php foreach ( $services as $i => $svc ) {
        $thumb_url = $svc->image_id ? wp_get_attachment_image_url( $svc->image_id, 'medium' ) : '';
      ?>
      <div class="service-card service-card--full" data-aos="fade-up" data-delay="<?php echo ( $i % 3 ) * 100; ?>">
        <div class="service-card__content">
            <div class="service-card__info">
            <div class="service-card__icon">
              <h2 class="service-card__title"><?php echo ($svc->icon ?? '✦'); ?> <?php echo ($svc->title); ?></h2>
            </div>
            <p class="service-card__body">- <?php echo ($svc->short_desc ?? ''); ?></p>
            <p class="service-card__body"><?php echo ($svc->full_desc ?? ''); ?></p>
          </div>
          <div class="service-card__image-wrap">
            <img
              class="service-card__image"
              src="<?php echo esc_url($thumb_url); ?>"
              alt="<?php echo esc_attr($svc->title); ?>"
            />
          </div>
        </div>

       <!-- Service points -->
        <?php if (!empty($service_points[$svc->id])) : ?>
          <ul class="service-card__points">
            <?php foreach ($service_points[$svc->id] as $point) : ?>
              <li><?php echo esc_html($point['point_text']); ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
      <?php } ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── Stats Strip ───────────────────────────────────────────────────────── -->
<?php $stats = ah_get_site_stats(); if ( $stats ) : ?>
<div class="section section--sm">
  <div class="container">
    <div class="stats-strip">
      <?php foreach ( $stats as $i => $stat ) :
        $stat = is_object($stat) ? (array) $stat : $stat;
      ?>
      <div class="stats-strip__item" data-aos="zoom-in" data-delay="<?php echo $i * 100; ?>">
        <div class="stats-strip__num"><?php echo esc_html( $stat['num'] ?? '' ); ?></div>
        <div class="stats-strip__label"><?php echo esc_html( $stat['label'] ?? '' ); ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ── How It Works ──────────────────────────────────────────────────────── -->
<?php if ( $steps ) : ?>
<section class="section section--pattern" aria-label="How we work">
  <div class="container">
    <div class="section__header text-center">
      <span class="section__eyebrow">The Process</span>
      <h2 class="section__title">How We Work With You</h2>
      <p class="section__desc" style="margin-inline:auto">
        A clear, structured process with you in control at every step - from brief to completion.
      </p>
    </div>
    <div class="process-grid">
      <?php foreach ( $steps as $i => $step ) :
        $step = is_object($step) ? (array) $step : $step;
      ?>
      <div class="process-card" data-aos="fade-up" data-delay="<?php echo ( $i % 3 ) * 80; ?>">
        <div class="process-card__num"><?php echo esc_html( $step['num'] ?? sprintf('%02d', $i + 1) ); ?></div>
        <div class="process-card__title"><?php echo esc_html( $step['title'] ); ?></div>
        <p class="process-card__desc"><?php echo esc_html( $step['desc'] ?? $step['description'] ?? '' ); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php
get_template_part( 'components/faq-section' );

get_template_part( 'components/testimonials' );

get_template_part( 'components/cta-section', null, [] );

get_footer();
