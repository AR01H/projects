<?php
defined( 'ABSPATH' ) || exit;
$parent_terms = $args['parent_terms'] ?? [];
$hero_img     = get_template_directory_uri() . '/assets/images/backgrounds/family_background.png';
?>
<section class="nhp-banner nhp-banner--hero">
  <div class="nhp-banner__media" style="background-image:url('<?php echo esc_url( $hero_img ); ?>')" aria-hidden="true"></div>

  <div class="nhp-banner__body">
    <span class="nhp-banner__eyebrow">UK Property Resource</span>
    <h1 class="nhp-banner__title nhp-banner__title--hero">
      Your Complete Guide to <em>Buying Property</em> in the UK
    </h1>
    <p class="nhp-banner__sub">
      Independent, expert-written guides on rules, regulations, finance, and legal requirements - everything a buyer needs, explained clearly.
    </p>

    <div class="nhp-banner__chips">
      <?php foreach ( array_slice( $parent_terms, 0, 6 ) as $pt ) :
        $pt = is_object( $pt ) ? $pt : (object) $pt;
      ?>
      <a href="<?php echo esc_url( home_url( '/guides/?parent_term=' . urlencode( $pt->slug ?? '' ) ) ); ?>"
         class="nhp-banner__chip">
        <?php if ( ! empty( $pt->icon_emoji ) ) echo esc_html( $pt->icon_emoji ) . ' '; ?>
        <?php echo esc_html( $pt->name ?? '' ); ?>
      </a>
      <?php endforeach; ?>
      <a href="<?php echo esc_url( home_url( '/allnews/' ) ); ?>" class="nhp-banner__chip">📰 Latest News</a>
      <a href="<?php echo esc_url( home_url( '/multiinfo/' ) ); ?>" class="nhp-banner__chip">🛠️ All Services</a>
      <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="nhp-banner__chip nhp-banner__chip--accent">All Topics →</a>
    </div>

    <div class="nhp-banner__ctas">
      <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="nhp-banner__btn nhp-banner__btn--primary">
        <span>Browse Guides</span>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
      <a href="<?php echo esc_url( home_url( '/multiinfo/' ) ); ?>" class="nhp-banner__btn nhp-banner__btn--ghost">Explore Info Hub</a>
    </div>
  </div>

  <svg class="nhp-banner__rooftops" viewBox="0 0 1200 90" preserveAspectRatio="none" aria-hidden="true">
    <path d="M0,90 L0,60 L70,60 L70,44 L104,44 L104,32 L138,32 L138,60 L210,60 L255,28 L300,60 L390,60 L390,48 L450,48 L450,36 L486,36 L486,60 L560,60 L606,22 L652,60 L740,60 L740,46 L800,46 L800,60 L880,60 L926,30 L972,60 L1060,60 L1060,50 L1116,50 L1116,40 L1150,40 L1150,60 L1200,60 L1200,90 Z"/>
    <rect x="252" y="14" width="6" height="16"/>
    <rect x="603" y="8"  width="6" height="16"/>
    <rect x="923" y="16" width="6" height="16"/>
  </svg>
</section>
