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
<section class="hero" aria-label="Hero">
  <div class="container">
    <div class="hero__inner">

      <!-- Copy -->
      <div class="hero__copy" data-aos="fade-right">
        <div class="hero__eyebrow">
          <span>🇬🇧</span> UK Buyer's Agent
        </div>
        <?php if ( $headline ) : ?>
          <h1 class="hero__title"><?php echo wp_kses_post( $headline ); ?></h1>
        <?php endif; ?>
        <?php if ( $subline ) : ?>
          <p class="hero__desc"><?php echo esc_html( $subline ); ?></p>
        <?php endif; ?>
        <div class="hero__actions">
          <a href="<?php echo esc_url( $cta_url ); ?>" class="btn btn-primary btn-lg">
            <?php echo esc_html( $cta_label ); ?> →
          </a>
          <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="btn btn-ghost btn-lg">
            Browse Guides
          </a>
        </div>

        <?php if ( $stats ) : ?>
        <div class="hero__stats" style="margin-top:40px">
          <?php foreach ( $stats as $i => $stat ) : ?>
          <div class="hero__stat" data-aos="zoom-in" data-delay="<?php echo $i * 100; ?>">
            <div class="hero__stat-num"><?php echo esc_html( $stat['num'] ); ?></div>
            <div class="hero__stat-label"><?php echo esc_html( $stat['label'] ); ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Visual -->
      <div class="hero__visual" data-aos="fade-left" data-delay="200">
        <div class="hero__img-frame">
          <?php
          $img = get_template_directory() . '/assets/images/hero-home.jpg';
          if ( file_exists( $img ) ) :
          ?>
            <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/hero-home.jpg' ); ?>"
                 alt="Property buying with Advaith Homes" loading="eager">
          <?php else : ?>
            <div style="padding:60px;text-align:center;color:var(--text-muted)">
              <div style="font-size:5rem;margin-bottom:20px">🏠</div>
              <div style="font-family:var(--font-display);font-size:1.4rem;font-weight:600">Your Dream Home Awaits</div>
              <div style="margin-top:12px;font-size:.875rem">We'll find it for you</div>
            </div>
          <?php endif; ?>
        </div>
        <?php
          $badge_rating = rtrim( str_replace( '★', '', $home['hero_stat_4'] ?? '4.9' ) );
          $badge_count  = $home['hero_stat_3'] ?? '500+';
        ?>
        <div class="hero__badge float">
          <div style="font-weight:700;font-size:.95rem">★ <?php echo esc_html( $badge_rating ); ?>/5</div>
          <div style="font-size:.75rem;color:var(--text-muted);margin-top:2px">From <?php echo esc_html( $badge_count ); ?> buyers</div>
        </div>
      </div>

    </div>
  </div>
</section>
