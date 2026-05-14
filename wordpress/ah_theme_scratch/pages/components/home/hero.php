<?php
$hero_badge = get_option('ah_hero_badge', "UK's #1 Dedicated Buyer's Agent — Not an Estate Agent");
$hero_title = get_option('ah_hero_title', 'We Buy Homes <em>For You</em>, Not the Seller');
$hero_sub = get_option('ah_hero_sub', 'Estate agents work for sellers. Advaith Homes works exclusively for <strong>you</strong> — the buyer. We research, negotiate, and guide you through every step, saving you an average of £18,000 and 6 months on your home purchase.');
?>
<section class="hero">
    <div class="container">
      <div class="hero__inner">
        <div class="hero__content">
          <div class="hero__badge reveal">
            <div class="hero__badge-dot">✓</div>
            <?php echo esc_html($hero_badge); ?>
          </div>
          <h1 class="hero__headline reveal reveal-delay-1">
            <?php echo wp_kses_post($hero_title); ?>
          </h1>
          <p class="hero__sub reveal reveal-delay-2">
            <?php echo wp_kses_post($hero_sub); ?>
          </p>
          <div class="hero__actions reveal reveal-delay-3">
            <a href="<?php echo esc_url(home_url('/free-consultation')); ?>" class="btn btn-primary btn-lg">Book Free Consultation →</a>
            <a href="<?php echo esc_url(home_url('/services')); ?>" class="btn btn-secondary btn-lg">Our Services</a>
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
              <strong>500+ Happy Buyers</strong>
              ⭐⭐⭐⭐⭐ &nbsp;4.9/5 average rating
            </div>
          </div>
        </div>

        <div class="hero__visual reveal reveal-delay-2">
          <div class="hero__img-wrap">
            <img src="<?php echo esc_url(mytheme_image('hero-home.png')); ?>" alt="Premium UK Home" class="hero__premium-img" />
            <div class="hero__premium-overlay"></div>
          </div>
          <div class="hero__card-float hero__card-float--stats">
            <div class="hero__card-icon hero__card-icon--violet">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="1" x2="12" y2="23"></line>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
              </svg>
            </div>
            <div>
              <div
                style="font-weight:600;color:var(--slate-800);font-size:0.85rem;text-transform:uppercase;letter-spacing:0.5px;">
                Avg. Saving</div>
              <div style="font-size:1.4rem;font-weight:800;color:var(--slate-900);font-family:var(--font-display)">
                £18,000+</div>
            </div>
          </div>
          <div class="hero__card-float hero__card-float--badge">
            <div class="hero__card-icon hero__card-icon--gold">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
              </svg>
            </div>
            <div>
              <div
                style="font-weight:600;color:var(--slate-800);font-size:0.85rem;text-transform:uppercase;letter-spacing:0.5px;">
                Time Saved</div>
              <div style="font-size:1.1rem;font-weight:700;color:var(--gold-600)">~6 Months</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
