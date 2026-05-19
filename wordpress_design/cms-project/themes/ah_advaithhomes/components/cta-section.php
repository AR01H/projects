<?php
defined( 'ABSPATH' ) || exit;

$s         = $args ?? [];
$eyebrow   = $s['eyebrow']   ?? '';
$title     = $s['title']     ?? 'Ready to Start?<br><em>Let\'s Talk.</em>';
$desc      = $s['desc']      ?? 'Book a free consultation. No obligation, no pressure - just straight answers about how we can help you buy smarter.';
$cta_label = $s['cta_label'] ?? 'Book a Free Call';
$cta_url   = $s['cta_url']   ?? home_url( '/contact/' );
$sec_label = $s['sec_label'] ?? 'Browse Our Guides';
$sec_url   = $s['sec_url']   ?? home_url( '/guides/' );
?>
<section class="cta-section" aria-label="Call to action">
  <div class="container">
    <?php if ( $eyebrow ) : ?>
      <div class="cta-section__eyebrow"><?php echo esc_html( $eyebrow ); ?></div>
    <?php endif; ?>
    <h2 class="cta-section__title" data-aos="fade-up">
      <?php echo wp_kses( $title, [ 'br' => [], 'em' => [], 'span' => [ 'class' => [] ] ] ); ?>
    </h2>
    <?php if ( $desc ) : ?>
      <p class="cta-section__desc" data-aos="fade-up" data-delay="100">
        <?php echo esc_html( $desc ); ?>
      </p>
    <?php endif; ?>
    <div class="cta-section__actions" data-aos="fade-up" data-delay="200">
      <a href="<?php echo esc_url( $cta_url ); ?>" class="btn btn-gold">
        <?php echo esc_html( $cta_label ); ?> →
      </a>
      <?php if ( $sec_label ) : ?>
      <a href="<?php echo esc_url( $sec_url ); ?>" class="btn btn-white">
        <?php echo esc_html( $sec_label ); ?>
      </a>
      <?php endif; ?>
    </div>
  </div>
</section>
