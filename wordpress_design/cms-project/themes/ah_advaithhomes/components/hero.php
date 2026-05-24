<?php
defined( 'ABSPATH' ) || exit;

$home      = ah_get_home_settings();
$s         = $args ?? [];
$headline  = $s['headline']  ?? $home['hero_headline']  ?? '';
$subline   = $s['subline']   ?? $home['hero_subline']   ?? '';
$cta_label = $s['cta_label'] ?? $home['hero_cta_label'] ?? 'Book a Free Consultation';
$cta_url   = $s['cta_url']   ?? home_url( $home['hero_cta_url'] ?? '/contact/' );
$stats     = [
	[ 'num' => $home['hero_stat_1'] ?? '', 'label' => $home['hero_stat_1_label'] ?? '' ],
	[ 'num' => $home['hero_stat_2'] ?? '', 'label' => $home['hero_stat_2_label'] ?? '' ],
	[ 'num' => $home['hero_stat_3'] ?? '', 'label' => $home['hero_stat_3_label'] ?? '' ],
	[ 'num' => $home['hero_stat_4'] ?? '', 'label' => $home['hero_stat_4_label'] ?? '' ],
];
$stats = array_filter( $stats, fn($s) => ! empty( $s['num'] ) );
?>
<section class="hero" aria-label="<?php echo esc_attr( TXT_HERO ); ?>">
  <div class="container">
    <div class="hero__inner">

      <!-- Copy -->
      <div class="hero__copy" data-aos="fade-right">
        <?php if ( $headline ) : ?>
          <h1 class="hero__title"><span><?php echo wp_kses_post( $headline ); ?></span></h1>
        <?php endif; ?>
        <?php if ( $subline ) : ?>
          <p class="hero__desc"><?php echo esc_html( $subline ); ?></p>
        <?php endif; ?>
        <div class="hero__actions">
          <a href="<?php echo esc_url( $cta_url ); ?>" class="btn btn-primary btn-md">
            <?php echo esc_html( $cta_label ); ?> →
          </a>
          <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="btn btn-outline btn-md">
            Browse Guides
          </a>
        </div>

      </div>
      
      <!-- Visual -->
      <div class="hero__visual" data-aos="fade-left" data-delay="200">
        <?php if ( $stats ) : ?>
        <div class="hero__stats">
          <?php foreach ( $stats as $i => $stat ) : ?>
          <div class="hero__stat" data-aos="zoom-in" data-delay="<?php echo $i * 100; ?>">
            <div class="hero__stat-num"><?php echo esc_html( $stat['num'] ); ?></div>
            <div class="hero__stat-label"><?php echo esc_html( $stat['label'] ); ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</section>
