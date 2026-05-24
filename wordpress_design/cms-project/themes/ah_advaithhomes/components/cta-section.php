<?php
defined( 'ABSPATH' ) || exit;

$s         = $args ?? [];
$eyebrow   = $s['eyebrow']   ?? CTA_SECONDARY_SECTION;
$title     = $s['title']     ?? CTA_PRIMARY_SECTION;
$desc      = $s['desc']      ?? CTA_DESCRIPTION;
$cta_label = $s['cta_label'] ?? CTA_BUTTON_TEXT;
$cta_url   = $s['cta_url']   ?? home_url( CTA_BUTTON_LINK );
$sec_label = $s['sec_label'] ?? CTA_BUTTON2_TEXT;
$sec_url   = $s['sec_url']   ?? home_url( CTA_BUTTON2_LINK );
?>
<section class="cta-section" aria-label="<?php echo esc_attr( TXT_CALL_TO_ACTION ); ?>">
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
