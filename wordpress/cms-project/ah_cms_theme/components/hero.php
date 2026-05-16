<?php
defined( 'ABSPATH' ) || exit;

$home_data = ah_get_home_data();
$settings  = ah_get_settings();
$consult   = $settings['consultation_url'] ?? home_url( '/free-consultation/' );

// Fallback content
$badge    = ah_raw( $home_data['hero_badge']    ?? [], 'value', "UK's #1 Dedicated Buyer's Agent — Not an Estate Agent" );
$headline = ah_raw( $home_data['hero_headline'] ?? [], 'value', "We Buy Homes <em>For You</em>,<br>Not the Seller" );
$subtext  = ah_raw( $home_data['hero_subtext']  ?? [], 'value', "Estate agents work for sellers. Advaith Homes works exclusively for <strong>you</strong> — the buyer. We research, negotiate, and guide you through every step, saving you an average of £18,000 and 6 months on your home purchase." );

$hero_img_id = ah_raw( $home_data['hero_image'] ?? [], 'value', 0 );
$hero_img    = $hero_img_id ? ah_media_url( (int) $hero_img_id ) : ah_hero_img();
?>
<section class="hero">
  <div class="container">
    <div class="hero__inner">
      <!-- Content -->
      <div class="hero__content">
        <div class="hero__badge reveal">
          <div class="hero__badge-dot">✓</div>
          <?php echo esc_html( $badge ); ?>
        </div>
        <h1 class="hero__headline reveal reveal-delay-1">
          <?php echo wp_kses( $headline, [ 'em' => [], 'br' => [], 'strong' => [] ] ); ?>
        </h1>
        <p class="hero__sub reveal reveal-delay-2">
          <?php echo wp_kses( $subtext, [ 'strong' => [], 'em' => [], 'a' => [ 'href' => [] ] ] ); ?>
        </p>
        <div class="hero__actions reveal reveal-delay-3">
          <a href="<?php echo esc_url( $consult ); ?>" class="btn btn-primary btn-lg btn--arrow">
            <?php esc_html_e( 'Book Free Consultation', 'ah-theme' ); ?>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </a>
          <a href="<?php echo esc_url( home_url( '/services/' ) ); ?>" class="btn btn-secondary btn-lg">
            <?php esc_html_e( 'Our Services', 'ah-theme' ); ?>
          </a>
        </div>
        <div class="hero__trust reveal reveal-delay-4">
          <div class="hero__trust-avatars">
            <div class="hero__trust-avatar">SR</div>
            <div class="hero__trust-avatar">PK</div>
            <div class="hero__trust-avatar">AM</div>
            <div class="hero__trust-avatar">JL</div>
            <div class="hero__trust-avatar">+</div>
          </div>
          <div class="hero__trust-text">
            <strong><?php esc_html_e( '500+ Happy Buyers', 'ah-theme' ); ?></strong>
            ⭐⭐⭐⭐⭐ &nbsp;<?php esc_html_e( '4.9/5 average rating', 'ah-theme' ); ?>
          </div>
        </div>
      </div>

      <!-- Visual -->
      <div class="hero__visual reveal reveal-delay-2">
        <div class="hero__img-wrap">
          <img src="<?php echo esc_url( $hero_img ); ?>"
               alt="<?php esc_attr_e( 'Premium UK Home', 'ah-theme' ); ?>"
               class="hero__premium-img"
               loading="eager"
               fetchpriority="high">
          <div class="hero__premium-overlay"></div>
        </div>
        <!-- Floating stats cards -->
        <div class="hero__card-float hero__card-float--stats">
          <div class="hero__card-icon hero__card-icon--violet">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="1" x2="12" y2="23"></line>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
          </div>
          <div>
            <div style="font-weight:600;color:var(--slate-800);font-size:.85rem;text-transform:uppercase;letter-spacing:.5px">
              <?php esc_html_e( 'Avg. Saving', 'ah-theme' ); ?>
            </div>
            <div style="font-size:1.4rem;font-weight:800;color:var(--slate-900);font-family:var(--font-display)">£18,000+</div>
          </div>
        </div>
        <div class="hero__card-float hero__card-float--badge">
          <div class="hero__card-icon hero__card-icon--gold">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
          </div>
          <div>
            <div style="font-weight:600;color:var(--slate-800);font-size:.85rem;text-transform:uppercase;letter-spacing:.5px">
              <?php esc_html_e( 'Time Saved', 'ah-theme' ); ?>
            </div>
            <div style="font-size:1.1rem;font-weight:700;color:var(--gold-600)">~6 <?php esc_html_e( 'Months', 'ah-theme' ); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
