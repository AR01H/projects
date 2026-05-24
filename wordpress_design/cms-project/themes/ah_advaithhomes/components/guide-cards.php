<?php
defined( 'ABSPATH' ) || exit;

$categories = ah_get_guide_categories();
if ( empty( $categories ) ) return;
?>
<section class="section section--pattern" aria-label="<?php echo esc_attr( TXT_GUIDE_CATEGORIES ); ?>">
  <div class="container">
    <div class="section__header text-center">
      <span class="section__eyebrow">The Complete Resource</span>
      <h2 class="section__title">Everything You Need to Buy with Confidence</h2>
      <p class="section__desc" style="margin-inline:auto">
        Our guides cover every stage of the buying journey - from understanding mortgages to completion day.
      </p>
    </div>

    <div class="grid-4">
      <?php foreach ( $categories as $i => $cat ) :
        $cat = is_object($cat) ? (array) $cat : $cat;
      ?>
      <a href="<?php echo esc_url( home_url( '/guides/?category=' . urlencode( $cat['slug'] ) ) ); ?>"
         class="guide-card"
         data-aos="fade-up"
         data-delay="<?php echo $i * 100; ?>"
         style="text-decoration:none;color:inherit">
        <div class="guide-card__icon"><?php echo esc_html( $cat['icon'] ?? '📖' ); ?></div>
        <div class="guide-card__title"><?php echo esc_html( $cat['title'] ); ?></div>
        <div class="guide-card__desc"><?php echo esc_html( $cat['desc'] ?? '' ); ?></div>
        <div class="guide-card__count"><?php echo esc_html( $cat['count'] ?? '' ); ?> GUIDES →</div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
