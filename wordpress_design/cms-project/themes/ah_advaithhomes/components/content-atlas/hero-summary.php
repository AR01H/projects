<?php
defined( 'ABSPATH' ) || exit;
$summary_cards = $args['summary_cards'] ?? [];
?>
<section class="page-hero atlas-hero" aria-label="<?php echo esc_attr( TXT_CONTENT_OVERVIEW ); ?>">
  <div class="container">
    <div class="page-hero__copy" style="max-width:820px" data-aos="fade-up">
      <span class="section__eyebrow">Content Atlas</span>
      <h1 class="page-hero__title">Everything Managed in Admin,<br><em>Readable on One Page</em></h1>
      <p class="page-hero__desc">This page brings together the important content controlled from the CMS admin: posts, services, static pages, downloads, forms, trust signals, and the core site settings that shape the live site.</p>
    </div>
  </div>
</section>
<section class="section section--sm" aria-label="<?php echo esc_attr( TXT_CONTENT_SUMMARY ); ?>">
  <div class="container">
    <div class="atlas-summary">
      <?php foreach ( $summary_cards as $card ) : ?>
      <div class="atlas-summary-card" data-aos="fade-up">
        <strong><?php echo esc_html( (string) $card['value'] ); ?></strong>
        <div><?php echo esc_html( $card['label'] ); ?></div>
        <div class="atlas-muted"><?php echo esc_html( $card['note'] ); ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
