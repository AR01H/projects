<?php get_header(); ?>
<?php
/**
 * index.php / front-page.php — Homepage
 * ALL data now reads from proper DB tables via ch_get_active()
 * Hero data still uses post_meta (edited on the Home page edit screen)
 * Everything else: Reviews, Steps, Flavours, Events, FAQs,
 *   Benefits, Showcase, Locations → from their own DB tables
 */
global $wpdb;
$pid = get_the_ID();

// ── HERO (post_meta — edit on Pages → Home) ───────────────────────────────────
$hero_tag = get_post_meta($pid, '_hero_tag', true) ?: '100% Natural · No Additives · Pressed Live';
$hero_t1 = get_post_meta($pid, '_hero_title1', true) ?: 'Pressed Fresh.';
$hero_t2 = get_post_meta($pid, '_hero_title2', true) ?: 'Served Cool.';
$hero_sub = get_post_meta($pid, '_hero_subtitle', true) ?: 'The Cane House';
$hero_desc = get_post_meta($pid, '_hero_desc', true) ?: 'Fresh sugarcane juice pressed live and blended with authentic cold-pressed fruit extracts & natural botanicals. Build your perfect juice — your way.';
$btn1_txt = get_post_meta($pid, '_hero_btn1_text', true) ?: '🥤 Build Your Juice';
$btn1_url = get_post_meta($pid, '_hero_btn1_url', true) ?: '#build';
$btn2_txt = get_post_meta($pid, '_hero_btn2_text', true) ?: 'Hire for Events →';
$btn2_url = get_post_meta($pid, '_hero_btn2_url', true) ?: '#hire';
$hero_img = get_post_meta($pid, '_hero_image', true) ?: get_template_directory_uri() . '/assets/images/thecanehouselogo.png';

// ── FROM DB TABLES ────────────────────────────────────────────────────────────
$steps = ch_get_active('order_steps', 'sort_order ASC');
$reviews = ch_get_active('reviews', 'sort_order ASC');
$flavours = ch_get_active('flavours', 'sort_order ASC');
$events = ch_get_active('events', 'sort_order ASC');
$faqs = ch_get_active('faqs', 'sort_order ASC');
$benefits = ch_get_active('benefits', 'sort_order ASC');
$slides = ch_get_active('showcase_slides', 'sort_order ASC');
$locations = ch_get_active('franchise_locs', 'sort_order ASC');

// ── GLOBAL SETTINGS (from Site Settings page) ─────────────────────────────────
$phone = ch_opt('phone', '+44 7887 699 208');
$website = ch_opt('website', 'www.thecanehouse.co.uk');
$wa = ch_opt('whatsapp', '447887699208');

// ── MARQUEE ───────────────────────────────────────────────────────────────────
$marquee_text = ch_opt('marquee', 'Pressed Fresh ✦ Served Cool ✦ No Added Sugar ✦ No Preservatives ✦ Pressed Live ✦ Natural Goodness ✦ Build Your Juice ✦ Events & Hire');
$marquee_items = array_map('trim', explode('✦', $marquee_text));

// ── HIRE section heading ──────────────────────────────────────────────────────
$hire_title = get_post_meta($pid, '_hire_title', true) ?: 'Bring Us to Your Event';
$hire_desc = get_post_meta($pid, '_hire_desc', true) ?: 'Elevate your celebration with our premium live-pressed sugarcane juice experience.';
?>

<!-- ═══════════════════════════════════════════════════════ HERO -->
<section id="hero" class="fade-up">
  <div class="hero-bubbles">
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
    <div class="bubble"></div>
  </div>
  <div class="hero-deco d1">🌿</div>
  <div class="hero-deco d2">🌿</div>
  <div class="hero-inner">
    <div class="hero-left">
      <div class="hero-tag"><?php echo esc_html($hero_tag); ?></div>
      <h1 class="hero-title"><?php echo esc_html($hero_t1); ?><span
          class="accent"><?php echo esc_html($hero_t2); ?></span></h1>
      <div class="hero-subtitle"><?php echo esc_html($hero_sub); ?></div>
      <p class="hero-desc"><?php echo esc_html($hero_desc); ?></p>
      <div class="hero-btns">
        <a href="<?php echo esc_url($btn1_url); ?>" class="btn-lime"><?php echo esc_html($btn1_txt); ?></a>
        <a href="<?php echo esc_url($btn2_url); ?>" class="btn-outline"><?php echo esc_html($btn2_txt); ?></a>
      </div>
      <div class="hero-badges">
        <span class="badge-item fade-left">No Added Sugar</span>
        <span class="badge-item fade-left">No Preservatives</span>
        <span class="badge-item fade-left">Pressed Live</span>
        <span class="badge-item fade-left">Served Chilled</span>
      </div>
    </div>
    <div class="hero-right">
      <div class="hero-glow hero-cup-wrap">
        <img src="<?php echo esc_url($hero_img); ?>" alt="<?php echo esc_attr($hero_sub); ?>"
          style="transform:rotate(15deg);" />
      </div>
      <div class="hero-cup-wrap">
        <div class="floating-leaf fl1">🍋</div>
        <div class="floating-leaf fl2">🍃</div>
        <div class="floating-leaf fl3">🌿</div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════ MARQUEE -->
<div class="marquee-wrap">
  <div class="marquee-track">
    <?php foreach (array_merge($marquee_items, $marquee_items) as $item): ?>
      <span class="marquee-item"><?php echo esc_html($item); ?></span><span class="marquee-sep">✦</span>
    <?php endforeach; ?>
  </div>
</div>

<!-- ═══════════════════════════════════════════════ HOW TO ORDER -->
<section id="how-to-order">
  <div class="how-header fade-up">
    <div class="section-tag">Simple &amp; Easy</div>
    <h2 class="section-title">How to <span class="accent">Order</span></h2>
    <p class="section-body">Build your perfect fresh cane juice in just <?php echo count($steps); ?> steps. Pressed
      live, just for you.</p>
  </div>
  <div class="steps-grid">
    <?php foreach ($steps as $i => $step):
      $is_last = ($i === count($steps) - 1); ?>
      <div class="step-card fade-up" <?php if ($is_last)
        echo 'style="border-color:var(--lime);"'; ?>>
        <div class="step-num" <?php if ($is_last)
          echo 'style="background:linear-gradient(135deg,#9bb800,#c8e830);color:var(--green-deep)"'; ?>>
          <?php echo esc_html($step->step_number ?: $i + 1); ?>
        </div>
        <?php if ($step->image_url): ?>
          <img src="<?php echo esc_url($step->image_url); ?>" alt="<?php echo esc_attr($step->title); ?>"
            style="width:60px;height:60px;object-fit:cover;border-radius:50%;margin:8px auto;">
        <?php else: ?>
          <div class="step-emoji <?php echo $is_last ? 'shakking-leaf' : ''; ?>"><?php echo esc_html($step->emoji); ?></div>
        <?php endif; ?>
        <div class="step-title"><?php echo esc_html($step->title); ?></div>
        <div class="step-desc"><?php echo esc_html($step->description); ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════ REVIEWS -->
<section id="reviews">
  <div class="reviews-header fade-up">
    <div class="section-tag">Happy Customers</div>
    <h2 class="section-title">What Our <span class="accent">Fans Say</span></h2>
    <p class="section-body">Real reviews from our sugarcane lovers. Join the fresh juice community!</p>
  </div>
  <div class="reviews-container">
    <div class="reviews-track" id="reviews-track">
      <?php foreach ($reviews as $i => $r): ?>
        <div class="review-card fade-up <?php echo $i === 0 ? 'active' : ''; ?>">
          <div class="review-avatar">
            <?php if (!empty($r->image_url)): ?>
              <img src="<?php echo esc_url($r->image_url); ?>" alt="<?php echo esc_attr($r->name); ?>">
            <?php else: ?>
              <img
                src="https://ui-avatars.com/api/?name=<?php echo urlencode($r->name); ?>&background=2d5a1b&color=fff&size=80"
                alt="<?php echo esc_attr($r->name); ?>">
            <?php endif; ?>
          </div>
          <div class="review-content">
            <div class="review-rating"><?php echo str_repeat('⭐', intval($r->rating ?: 5)); ?></div>
            <p class="review-text">"<?php echo esc_html($r->review_text); ?>"</p>
            <div class="review-meta">
              <div class="review-name"><?php echo esc_html($r->name); ?></div>
              <div class="review-subtitle"><?php echo esc_html($r->role); ?></div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="reviews-nav-vertical">
      <div class="nav-dots" id="nav-dots">
        <?php for ($i = 0; $i < count($reviews); $i++): ?>
          <span class="dot <?php echo $i === 0 ? 'active' : ''; ?>"></span>
        <?php endfor; ?>
      </div>
      <div class="nav-arrows">
        <button class="v-btn" id="rev-prev">↑</button>
        <button class="v-btn" id="rev-next">↓</button>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════ BUILD YOUR JUICE -->
<section id="build">
  <div class="build-header fade-up">
    <div class="section-tag">Full Menu</div>
    <h2 class="section-title">Build Your <span class="accent">Juice</span></h2>
    <p class="section-body">Mix and match size, cane type, texture and flavour. All prices in GBP.</p>
  </div>
  <div class="build-grid">
    <!-- SIZES — static pricing card -->
    <div class="option-card fade-left">
      <div class="option-header">
        <div class="option-num">1</div>
        <div>
          <div class="option-title">Size</div>
          <div class="option-sub">Pick your cup size</div>
        </div>
      </div>
      <div class="price-rows">
        <div class="price-row">
          <div class="row-left">
            <div class="row-icon">🥤</div>
            <div>
              <div class="row-name">Mini (250ml)</div>
              <div class="row-desc">Quick refresh, great for kids</div>
            </div>
          </div>
          <div class="row-price">£4.00</div>
        </div>
        <div class="price-row featured">
          <div class="row-left">
            <div class="row-icon">🥤</div>
            <div>
              <div class="row-name">Regular (350ml)</div>
              <div class="row-desc">Ideal single serving</div>
            </div>
          </div>
          <div class="row-right"><span class="row-badge">Popular</span>
            <div class="row-price">£5.50</div>
          </div>
        </div>
        <div class="price-row">
          <div class="row-left">
            <div class="row-icon">🧃</div>
            <div>
              <div class="row-name">Large (550ml)</div>
              <div class="row-desc">For a longer, refreshing drink</div>
            </div>
          </div>
          <div class="row-price">£7.00</div>
        </div>
        <div class="price-row">
          <div class="row-left">
            <div class="row-icon">🫙</div>
            <div>
              <div class="row-name">Sharing Jug (750ml)</div>
              <div class="row-desc">Great for two</div>
            </div>
          </div>
          <div class="row-price">£9.00</div>
        </div>
        <div class="price-row">
          <div class="row-left">
            <div class="row-icon">🍶</div>
            <div>
              <div class="row-name">Family (1L)</div>
              <div class="row-desc">Perfect for families</div>
            </div>
          </div>
          <div class="row-price">£14.50</div>
        </div>
        <div class="price-row featured">
          <div class="row-left">
            <div class="row-icon">🍾</div>
            <div>
              <div class="row-name">Group (1.5L)</div>
              <div class="row-desc">Ideal for group gatherings</div>
            </div>
          </div>
          <div class="row-right"><span class="row-badge">Best Value</span>
            <div class="row-price">£19.50</div>
          </div>
        </div>
      </div>
    </div>
    <!-- CANE + TEXTURE — static card -->
    <div class="option-card fade-right">
      <div class="option-header">
        <div class="option-num">2</div>
        <div>
          <div class="option-title">Cane Type</div>
          <div class="option-sub">Choose your cane</div>
        </div>
      </div>
      <div class="price-rows" style="margin-bottom:2rem;">
        <div class="price-row featured">
          <div class="row-left">
            <div class="row-icon">🌾</div>
            <div>
              <div class="row-name">Yellow Cane</div>
              <div class="row-desc">Light golden, fresh</div>
            </div>
          </div><span class="row-badge">Included</span>
        </div>
        <div class="price-row">
          <div class="row-left">
            <div class="row-icon">🎋</div>
            <div>
              <div class="row-name">Red Cane</div>
              <div class="row-desc">Rich amber, naturally sweeter</div>
            </div>
          </div>
          <div class="row-price">+£0.50</div>
        </div>
      </div>
      <div class="option-header">
        <div class="option-num">3</div>
        <div>
          <div class="option-title">Texture</div>
          <div class="option-sub">How it's pressed</div>
        </div>
      </div>
      <div class="price-rows">
        <div class="price-row featured">
          <div class="row-left">
            <div class="row-icon">🥢</div>
            <div>
              <div class="row-name">Classic (No Peel)</div>
              <div class="row-desc">Light grassy, traditional</div>
            </div>
          </div><span class="row-badge">Included</span>
        </div>
        <div class="price-row">
          <div class="row-left">
            <div class="row-icon">✨</div>
            <div>
              <div class="row-name">Smooth (With Peel)</div>
              <div class="row-desc">Cleaner, smoother finish</div>
            </div>
          </div>
          <div class="row-price">+£0.50</div>
        </div>
      </div>
    </div>
    <!-- FLAVOURS — FROM DB -->
    <div class="option-card fade-up" style="grid-column:1/-1;">
      <div class="option-header">
        <div class="option-num">4</div>
        <div>
          <div class="option-title">Flavour</div>
          <div class="option-sub">Pick your blend</div>
        </div>
      </div>
      <div class="flavour-grid">
        <?php foreach ($flavours as $f): ?>
          <div class="flavour-chip">
            <?php if (!empty($f->image_url)): ?>
              <img src="<?php echo esc_url($f->image_url); ?>" alt="<?php echo esc_attr($f->name); ?>"
                style="width:40px;height:40px;object-fit:cover;border-radius:50%;margin-bottom:6px;">
            <?php else: ?>
              <span class="chip-emoji shakking-leaf"><?php echo esc_html($f->emoji); ?></span>
            <?php endif; ?>
            <div class="chip-name"><?php echo esc_html($f->name); ?></div>
            <div class="chip-price <?php echo $f->price === 'Included' ? 'free' : ''; ?>">
              <?php echo esc_html($f->price); ?>  <?php if (!empty($f->flavour_type) && $f->flavour_type !== 'Base')
                     echo ' · ' . esc_html($f->flavour_type) . ' Blend'; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <p style="margin-top:1.2rem;font-size:0.72rem;color:var(--text-muted);font-style:italic;">* As different cane
        types are freshly pressed in a single machine, slight variation may occur. Contains natural sugars.</p>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════ BENEFITS -->
<section id="benefits">
  <div class="benefits-inner">
    <div class="fade-left">
      <div class="section-tag">Good for You</div>
      <h2 class="section-title">Why Sugarcane Juice is <span class="accent" style="color:var(--lime);">Loved
          Worldwide</span></h2>
      <p class="section-body">Fresh sugarcane juice is not just delicious — it's packed with natural benefits.</p>
      <div class="benefits-list">
        <?php foreach ($benefits as $b): ?>
          <div class="benefit-item">
            <?php if (!empty($b->image_url)): ?>
              <div class="benefit-icon"><img src="<?php echo esc_url($b->image_url); ?>" alt=""
                  style="width:36px;height:36px;object-fit:cover;border-radius:50%;"></div>
            <?php else: ?>
              <div class="benefit-icon"><?php echo esc_html($b->icon); ?></div>
            <?php endif; ?>
            <div>
              <div class="b-title"><?php echo esc_html($b->title); ?></div>
              <div class="b-desc"><?php echo esc_html($b->description); ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div style="display:flex;align-items:center;justify-content:center;" class="fade-right">
      <div class="promise-card">
        <span class="promise-icon">🌱</span>
        <div class="promise-title">Our Promise</div>
        <div class="promise-sub">Pressed Fresh. Served Cool.</div>
        <div class="promise-tags">
          <div class="promise-tag">No added sugar</div>
          <div class="promise-tag">No preservatives</div>
          <div class="promise-tag">Pure, natural refreshment</div>
          <div class="promise-tag">Pressed live at every order</div>
          <div class="promise-tag">Served chilled, always fresh</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════ STORY -->
<section id="story">
  <div class="story-inner">
    <div class="story-visual fade-left">
      <div class="story-main-card">
        <svg width="180" height="200" viewBox="0 0 180 200" xmlns="http://www.w3.org/2000/svg">
          <rect x="50" y="20" width="18" height="160" rx="6" fill="rgba(200,232,48,0.3)" />
          <rect x="78" y="10" width="18" height="170" rx="6" fill="rgba(200,232,48,0.4)" />
          <rect x="106" y="25" width="18" height="155" rx="6" fill="rgba(200,232,48,0.3)" />
          <path d="M68 20 Q90 0 110 15" stroke="rgba(200,232,48,0.6)" stroke-width="4" fill="none"
            stroke-linecap="round" />
          <text x="90" y="195" text-anchor="middle" font-family="Nunito,sans-serif" font-weight="900" font-size="10"
            fill="rgba(255,255,255,0.4)" letter-spacing="2">THE CANE HOUSE</text>
        </svg>
      </div>
      <div class="story-secondary-card">
        <p class="story-quote">"Sugarcane — one of nature's most generous gifts. Pure energy, pressed fresh."</p>
      </div>
      <div class="story-year-badge"><span>2,000+<br />Years<br />of Cane</span></div>
    </div>
    <div class="fade-right">
      <div class="section-tag">The Story of Sugarcane</div>
      <h2 class="section-title">Beyond the <span class="accent">Juice</span></h2>
      <p class="section-body" style="margin-top:1rem;">Sugarcane has been cherished for over 2,000 years, originating in
        South and Southeast Asia. Fresh sugarcane juice is naturally sweet, refreshing, and enjoyed across tropical
        cultures.</p>
      <p class="section-body" style="margin-top:1rem;font-size:0.9rem;">Even the leftover fibre is biodegradable —
        making sugarcane a truly sustainable crop.</p>
      <div class="story-facts">
        <div class="story-fact">
          <div class="fact-icon">🍬</div>
          <div class="fact-title">Sugar &amp; Jaggery</div>
          <div class="fact-desc">Traditional sweeteners</div>
        </div>
        <div class="story-fact">
          <div class="fact-icon">🫙</div>
          <div class="fact-title">Molasses</div>
          <div class="fact-desc">Rich mineral content</div>
        </div>
        <div class="story-fact">
          <div class="fact-icon">⛽</div>
          <div class="fact-title">Ethanol</div>
          <div class="fact-desc">Clean-burning biofuel</div>
        </div>
        <div class="story-fact">
          <div class="fact-icon">🌱</div>
          <div class="fact-title">Eco Fibre</div>
          <div class="fact-desc">100% biodegradable</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════ HIRE -->
<section id="hire">
  <div class="hire-container">
    <div class="hire-header fade-up">
      <div class="section-tag">Live Juice Stall Hire</div>
      <h2 class="section-title">Bring Us to <span class="accent">Your Event</span></h2>
      <p class="section-body"><?php echo esc_html($hire_desc); ?></p>
    </div>
    <div class="hire-grid">
      <?php foreach ($events as $ev): ?>
        <div class="hire-card fade-up">
          <?php if (!empty($ev->image_url)): ?>
            <img src="<?php echo esc_url($ev->image_url); ?>" alt="<?php echo esc_attr($ev->title); ?>"
              style="width:100%;height:160px;object-fit:cover;border-radius:12px;margin-bottom:12px;">
          <?php else: ?>
            <div class="h-card-icon"><?php echo esc_html($ev->icon); ?></div>
          <?php endif; ?>
          <h3><?php echo esc_html($ev->title); ?></h3>
          <p><?php echo esc_html($ev->description); ?></p>
          <?php if (!empty($ev->list_items)): ?>
            <ul class="h-card-list">
              <?php foreach (explode("\n", $ev->list_items) as $item):
                if (trim($item)): ?>
                  <li><?php echo esc_html(trim($item)); ?></li>
                <?php endif; endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="hire-features-bar fade-up">
      <div class="h-feature"><span class="hf-icon">🌿</span>
        <div class="hf-text">Pressed Live On-Site</div>
      </div>
      <div class="h-feature"><span class="hf-icon">❄️</span>
        <div class="hf-text">Naturally Chilled</div>
      </div>
      <div class="h-feature"><span class="hf-icon">🥤</span>
        <div class="hf-text">Unlimited Servings</div>
      </div>
      <div class="h-feature"><span class="hf-icon">🛡️</span>
        <div class="hf-text">Fully Insured</div>
      </div>
    </div>
    <div class="hire-cta fade-up">
      <a href="#contact" class="btn-lime">Get a Custom Quote →</a>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════ FRANCHISE -->
<section id="franchise">
  <div class="franchise-inner fade-up">
    <div class="section-tag" style="justify-content:center;color:var(--green-deep);">Grow With Us</div>
    <h2 class="section-title">Franchise <span class="accent" style="color:var(--green-mid);">Opportunities</span></h2>
    <p class="section-body">Be part of the fresh juice revolution. Bring the live pressed cane experience to your city.
    </p>
    <?php if (!empty($slides)): ?>
      <div class="juice-showcase">
        <div class="showcase-container" id="showcase-track">
          <?php foreach ($slides as $si => $sl): ?>
            <div class="showcase-card <?php echo $si === 0 ? 'active' : ($si === 1 ? 'next' : ($si === count($slides) - 1 ? 'prev' : '')); ?>"
              data-index="<?php echo $si; ?>">
              <?php if (!empty($sl->image_url)): ?>
                <img src="<?php echo esc_url($sl->image_url); ?>" alt="<?php echo esc_attr($sl->title); ?>">
              <?php endif; ?>
              <div class="showcase-info">
                <h3><?php echo esc_html($sl->title); ?></h3>
                <p><?php echo esc_html($sl->subtitle); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="showcase-controls">
          <button class="s-btn" id="showcase-prev">←</button>
          <button class="s-btn" id="showcase-next">→</button>
        </div>
      </div>
    <?php endif; ?>
    <?php if (!empty($locations)): ?>
      <div class="franchise-marquee">
        <div class="franchise-track">
          <?php foreach (array_merge((array) $locations, (array) $locations) as $loc): ?>
            <div class="f-item">
              <?php if (!empty($loc->image_url)): ?>
                <img src="<?php echo esc_url($loc->image_url); ?>" alt=""
                  style="width:20px;height:20px;border-radius:50%;object-fit:cover;">
              <?php else: ?>
                <span class="f-icon">📍</span>
              <?php endif; ?>
              <span class="f-name"><?php echo esc_html($loc->name); ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
    <div class="franchise-contact">
      <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $phone)); ?>" class="contact-pill">📞
        <?php echo esc_html($phone); ?></a>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════ FAQ -->
<section id="faq">
  <div class="faq-header fade-up">
    <div class="section-tag">Questions?</div>
    <h2 class="section-title">Common <span class="accent">Queries</span></h2>
    <p class="section-body">Everything you need to know about our fresh sugarcane juice and services.</p>
  </div>
  <div class="faq-grid fade-up">
    <?php foreach ($faqs as $fi => $faq): ?>
      <div class="faq-item <?php echo $fi === 0 ? 'active' : ''; ?>">
        <button class="faq-question">
          <?php echo esc_html($faq->question); ?>
          <div class="faq-icon">+</div>
        </button>
        <div class="faq-answer">
          <p><?php echo esc_html($faq->answer); ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- MAP -->
<?php $map = ch_opt('maps_embed', '');
if ($map): ?>
  <iframe src="<?php echo esc_url($map); ?>" width="100%" height="350" frameborder="0" style="border:0" allowfullscreen
    loading="lazy"></iframe>
<?php endif; ?>

<!-- ════════════════════════════════════════════════════ CONTACT -->
<section id="contact">
  <div class="fade-left">
    <h2 class="section-title">Get in <span class="accent">Touch</span></h2>
    <p class="section-body" style="margin-top:0.8rem;margin-bottom:2rem;">Questions about our juices, booking us for
      your event, or interested in franchise opportunities?</p>
    <div class="contact-detail">
      <div class="cd-icon">📞</div>
      <div>
        <div class="cd-label">Call Us</div>
        <div class="cd-val"><?php echo esc_html($phone); ?></div>
      </div>
    </div>
    <div class="contact-detail">
      <div class="cd-icon">🌐</div>
      <div>
        <div class="cd-label">Website</div>
        <div class="cd-val"><?php echo esc_html($website); ?></div>
      </div>
    </div>
    <div class="contact-detail">
      <div class="cd-icon">💬</div>
      <div>
        <div class="cd-label">WhatsApp</div>
        <div class="cd-val"><a href="https://wa.me/<?php echo esc_attr($wa); ?>" style="color:inherit;">Message us on
            WhatsApp</a></div>
      </div>
    </div>
    <?php
    // Extra custom contact fields from post meta
    $extras = get_post_meta($pid, '_contact_extra_fields', true);
    if (is_array($extras))
      foreach ($extras as $ef):
        if (empty($ef['label']))
          continue; ?>
        <div class="contact-detail">
          <div class="cd-icon"><?php echo esc_html($ef['icon'] ?? '📌'); ?></div>
          <div>
            <div class="cd-label"><?php echo esc_html($ef['label']); ?></div>
            <div class="cd-val"><?php echo esc_html($ef['value']); ?></div>
          </div>
        </div>
      <?php endforeach; ?>
  </div>
  <div class="contact-form fade-right">
    <div class="form-title">Send Us a Message 🌿</div>
    <div class="form-group"><label class="form-label">Full Name <span style="color:#ef4444">*</span></label><input
        type="text" id="name" class="form-input" placeholder="Your name" required /></div>
    <div class="form-group"><label class="form-label">Mobile Number</label><input type="tel" id="mobile"
        class="form-input" placeholder="+44 ..." /></div>
    <div class="form-group"><label class="form-label">I'm enquiring about</label>
      <select id="enquiry-type" class="form-select">
        <option value="">Select enquiry type...</option>
        <option>General Enquiry</option>
        <option>Event / Stall Hire</option>
        <option>Franchise Opportunity</option>
        <option>Something Else</option>
      </select>
    </div>
    <div class="form-group"><label class="form-label">Email Address <span style="color:#ef4444">*</span></label><input
        type="email" id="email" class="form-input" placeholder="you@email.com" required /></div>
    <div class="form-group"><label class="form-label">Message <span style="color:#ef4444">*</span></label><textarea
        id="query" class="form-textarea" placeholder="Tell us more — event date, location, expected guests..."
        required></textarea></div>
    <button class="form-submit" id="submitBtn" type="button"><span id="btnText">Send Message 🥤</span></button>
    <div id="formStatus" class="form-status"
      style="display:none;margin-top:12px;padding:10px 14px;border-radius:8px;font-size:0.9rem;"></div>
  </div>
</section>

<?php get_footer(); ?>