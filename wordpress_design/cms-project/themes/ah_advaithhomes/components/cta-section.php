<?php
defined( 'ABSPATH' ) || exit;

$s          = $args ?? [];
$title      = $s['title']      ?? 'Ready to Buy Smarter?';
$desc       = $s['desc']       ?? "Stop scrolling Rightmove alone. Let us find the right property, negotiate the best price, and manage the entire process — so you can just show up on completion day.";
$cta_label  = $s['cta_label']  ?? 'Book a Free Consultation';
$cta_url    = $s['cta_url']    ?? home_url( '/contact/' );
$sec_label  = $s['sec_label']  ?? 'Browse Guides First';
$sec_url    = $s['sec_url']    ?? home_url( '/guides/' );
?>
<section class="cta-section" aria-label="Call to action">
  <div class="container">
    <div class="cta-section__title" data-aos="fade-up">
      <?php echo wp_kses_post( $title ); ?>
    </div>
    <p class="cta-section__desc" data-aos="fade-up" data-delay="100">
      <?php echo esc_html( $desc ); ?>
    </p>
    <div class="cta-section__actions" data-aos="fade-up" data-delay="200">
      <a href="<?php echo esc_url( $cta_url ); ?>" class="btn btn-gold btn-sm">
        <?php echo esc_html( $cta_label ); ?> →
      </a>
      <a href="<?php echo esc_url( $sec_url ); ?>" class="btn btn-white btn-sm">
        <?php echo esc_html( $sec_label ); ?>
      </a>
    </div>
  </div>
</section>
