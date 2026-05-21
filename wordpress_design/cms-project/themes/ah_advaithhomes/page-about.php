<?php
/**
 * Template Name: About Page
 */
get_header();

$settings = ah_get_settings();
$stats    = ah_get_site_stats();
$signals  = ah_get_trust_signals();
?>

<!-- ── About Hero ────────────────────────────────────────────────────────── -->
<section class="page-hero page-hero--centered" aria-label="About us">
  <div class="container">
    <div class="page-hero__copy text-center" data-aos="fade-up" style="max-width:760px;margin-inline:auto">
      <span class="section__eyebrow">About Advaith Homes</span>
      <h1 class="page-hero__title">
        The UK's Buyer's Agent -<br><em>Working Exclusively for You</em>
      </h1>
      <p class="page-hero__desc">
        We exist to level the playing field. Sellers have agents negotiating for them - so should you.
        Advaith Homes is a buyer-only agency: we never list properties, never work for sellers, and
        never take referral fees from developers. Our only job is to help you buy smarter.
      </p>
    </div>
  </div>
</section>

<!-- ── Mission Strip ─────────────────────────────────────────────────────── -->
<?php if ( $stats ) : ?>
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

<!-- ── Our Story ─────────────────────────────────────────────────────────── -->
<section class="section section--pattern" aria-label="Our story">
  <div class="container">
    <div class="content-layout content-layout--2col">
      <div data-aos="fade-right">
        <span class="section__eyebrow">Our Story</span>
        <h2 class="section__title" style="font-size:2rem">
          Built for Buyers Who Deserve Better
        </h2>
        <div class="prose">
          <p>
            Advaith Homes was founded on a simple belief: buying a home is the biggest financial
            decision most people ever make, yet buyers are expected to navigate it alone - against
            estate agents who are legally and commercially incentivised to serve the seller.
          </p>
          <p>
            We started as a small team of former estate agents and property analysts who saw this
            imbalance every day. We switched sides. Now every negotiation, every recommendation,
            every phone call we make is in your interest - not the vendor's.
          </p>
          <p>
            Today we've helped over 500 buyers across England and Wales secure the right property
            at the right price, saving them an average of £14,200 per purchase.
          </p>
        </div>
      </div>
      <div data-aos="fade-left" data-delay="150">
        <div class="about-values">
          <div class="about-value-card">
            <div class="about-value-card__icon">🎯</div>
            <div class="about-value-card__title">Buyer-Only</div>
            <p class="about-value-card__desc">We never list properties or act for sellers. Ever. Our loyalty is entirely yours.</p>
          </div>
          <div class="about-value-card">
            <div class="about-value-card__icon">🔍</div>
            <div class="about-value-card__title">Full Market Access</div>
            <p class="about-value-card__desc">We see the whole market including off-market, pre-market, and agent-exclusive properties.</p>
          </div>
          <div class="about-value-card">
            <div class="about-value-card__icon">💷</div>
            <div class="about-value-card__title">Data-Led Negotiation</div>
            <p class="about-value-card__desc">Every offer is backed by comparable evidence so you never overpay.</p>
          </div>
          <div class="about-value-card">
            <div class="about-value-card__icon">🤝</div>
            <div class="about-value-card__title">End-to-End Support</div>
            <p class="about-value-card__desc">One point of contact from search to completion - managing solicitors, surveyors, and lenders for you.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── Team ──────────────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/team-section' ); ?>

<!-- ── Testimonials ──────────────────────────────────────────────────────── -->
<?php get_template_part( 'components/testimonials' ); ?>

<?php
get_template_part( 'components/cta-section', null, [] );

get_footer();
