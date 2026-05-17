<?php
defined( 'ABSPATH' ) || exit;

$topic  = $args['topic'] ?? '';
$faqs   = ah_get_faqs( $topic );
$limit  = $args['limit'] ?? 6;
$faqs   = array_slice( $faqs, 0, $limit );

if ( empty( $faqs ) ) return;
?>
<section class="section" aria-label="Frequently asked questions">
  <div class="container container--md">
    <div class="section__header text-center">
      <span class="section__eyebrow">FAQ</span>
      <h2 class="section__title">Common Questions Answered</h2>
      <p class="section__desc" style="margin-inline:auto">
        Everything you need to know about working with a buyer's agent.
      </p>
    </div>

    <div>
      <?php foreach ( $faqs as $i => $faq ) : ?>
      <div class="faq" data-aos="fade-up" data-delay="<?php echo min( $i * 60, 300 ); ?>">
        <button class="faq__q" aria-expanded="false">
          <?php echo esc_html( $faq->question ); ?>
          <span class="faq__icon" aria-hidden="true">+</span>
        </button>
        <div class="faq__a" role="region">
          <div class="faq__a-inner">
            <?php echo wp_kses_post( $faq->answer ); ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center" style="margin-top:32px">
      <a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>" class="btn btn-outline">
        View all FAQs →
      </a>
    </div>
  </div>
</section>
