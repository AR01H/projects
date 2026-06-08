<?php
defined( 'ABSPATH' ) || exit;
$grouped     = $args['grouped']     ?? [];
$topic_icons = $args['topic_icons'] ?? [];
if ( ! $grouped ) return;
?>
<section class="section" aria-label="<?php echo esc_attr( TXT_ALL_FAQS ); ?>">
  <div class="container container--md">
    <?php foreach ( $grouped as $topic => $faqs ) :
      $icon = $topic_icons[ strtolower( $topic ) ] ?? '❓';
    ?>
    <div style="margin-bottom:48px" data-aos="fade-up">
      <h2 style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;margin-bottom:20px;display:flex;align-items:center;gap:10px">
        <span><?php echo esc_html( $icon ); ?></span>
        <?php echo esc_html( ucfirst( $topic ) ); ?>
      </h2>
      <?php foreach ( $faqs as $i => $faq ) : ?>
      <div class="faq" data-aos="fade-up" data-delay="<?php echo min( $i * 40, 240 ); ?>">
        <button class="faq__q" aria-expanded="false">
          <?php echo esc_html( $faq->question ); ?>
          <span class="faq__icon" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 4 6 8 10 4"/></svg></span>
        </button>
        <div class="faq__a" role="region">
          <div class="faq__a-inner"><?php echo wp_kses_post( $faq->answer ); ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
  </div>
</section>
