<?php
/**
 * Template Name: Services Page
 */
get_header();

$services = ah_get_services( 12 );
$steps    = ah_get_process_steps();
$faqs     = ah_get_faqs( '', 8 );
?>

<!-- ── Page Hero ─────────────────────────────────────────────────────────── -->
<section class="page-hero" aria-label="Services">
  <div class="container">
    <div class="page-hero__inner">
      <div class="page-hero__copy" data-aos="fade-up">
        <span class="section__eyebrow">What We Do</span>
        <h1 class="page-hero__title">Full-Service<br><em>Buyer Representation</em></h1>
        <p class="page-hero__desc">
          From your first search to completion day, we handle every step of the buying
          process — so you make the right decision at the right price, every time.
        </p>
        <div class="hero__actions">
          <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn-primary btn-lg">
            Book a Free Call →
          </a>
          <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="btn btn-outline btn-lg">
            Browse Guides
          </a>
        </div>
      </div>
      <div class="page-hero__trust" data-aos="fade-left" data-delay="200">
        <?php foreach ( ah_get_trust_signals() as $sig ) :
          $sig = is_object($sig) ? (array) $sig : $sig;
        ?>
        <div class="page-hero__trust-item">
          <span><?php echo esc_html( $sig['icon'] ?? '' ); ?></span>
          <span><?php echo esc_html( $sig['text'] ?? '' ); ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ── All Services ──────────────────────────────────────────────────────── -->
<?php if ( $services ) : ?>
<section class="section" aria-label="All services">
  <div class="container">
    <div class="section__header text-center">
      <span class="section__eyebrow">Our Services</span>
      <h2 class="section__title">Everything Covered Under One Roof</h2>
      <p class="section__desc" style="margin-inline:auto">
        We're a full-service buyer's agency — one fee, one team, one point of contact from search to keys.
      </p>
    </div>
    <div class="grid-3">
      <?php foreach ( $services as $i => $svc ) : ?>
      <div class="service-card service-card--full" data-aos="fade-up" data-delay="<?php echo ( $i % 3 ) * 100; ?>">
        <div class="service-card__icon"><?php echo esc_html( $svc->icon ?? '✦' ); ?></div>
        <h2 class="service-card__title"><?php echo esc_html( $svc->title ); ?></h2>
        <p class="service-card__body"><?php echo esc_html( $svc->summary ?? $svc->description ?? '' ); ?></p>
        <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn-sm btn-ghost" style="margin-top:auto">
          Ask about this →
        </a>
      </div>
      <?php endforeach; ?>
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
<section class="section section--alt" aria-label="How we work">
  <div class="container">
    <div class="section__header text-center">
      <span class="section__eyebrow">The Process</span>
      <h2 class="section__title">How We Work With You</h2>
      <p class="section__desc" style="margin-inline:auto">
        A clear, structured process with you in control at every step — from brief to completion.
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

<!-- ── Services FAQ ──────────────────────────────────────────────────────── -->
<?php if ( $faqs ) : ?>
<section class="section" aria-label="Services FAQ">
  <div class="container container--md">
    <div class="section__header text-center">
      <span class="section__eyebrow">FAQ</span>
      <h2 class="section__title">Questions About Our Services</h2>
    </div>
    <div>
      <?php foreach ( $faqs as $i => $faq ) : ?>
      <div class="faq" data-aos="fade-up" data-delay="<?php echo min( $i * 50, 300 ); ?>">
        <button class="faq__q" aria-expanded="false">
          <?php echo esc_html( $faq->question ); ?>
          <span class="faq__icon" aria-hidden="true">+</span>
        </button>
        <div class="faq__a" role="region">
          <div class="faq__a-inner"><?php echo wp_kses_post( $faq->answer ); ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php
get_template_part( 'components/testimonials' );

get_template_part( 'components/cta-section', null, [
  'title'     => 'Ready to Start Your<br><em>Property Search?</em>',
  'desc'      => 'Book a free, no-obligation consultation. We\'ll explain exactly how we work and whether we\'re the right fit for your search.',
  'cta_label' => 'Book a Free Call →',
  'cta_url'   => home_url( '/contact/' ),
  'sec_label' => 'Read Our Guides First',
  'sec_url'   => home_url( '/guides/' ),
] );

get_footer();
