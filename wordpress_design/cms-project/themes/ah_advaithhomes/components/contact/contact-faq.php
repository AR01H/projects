<?php
defined( 'ABSPATH' ) || exit;

$faqs = $args['faqs'] ?? [];
if ( ! $faqs ) return;
?>
<section class="section section--pattern" aria-label="<?php echo esc_attr( TXT_CONTACT_FAQ ); ?>">
  <div class="container container--md">
    <div class="section__header text-center">
      <span class="section__eyebrow">FAQ</span>
      <h2 class="section__title">Before You Call</h2>
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
    <div class="text-center" style="margin-top:24px">
      <a href="<?php echo esc_url( home_url( AH_LINK_FAQ ) ); ?>" class="btn btn-outline">View all FAQs →</a>
    </div>
  </div>
</section>
