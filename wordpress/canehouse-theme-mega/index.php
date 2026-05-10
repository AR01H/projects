<?php get_header(); ?>

<?php
$pid = get_the_ID();

// ── HERO DATA ────────────────────────────────────────────────────────────────
$hero_tag   = ch_meta($pid, '_hero_tag',   '100% Natural · No Additives · Pressed Live');
$hero_t1    = ch_meta($pid, '_hero_title1','Pressed Fresh.');
$hero_t2    = ch_meta($pid, '_hero_title2','Served Cool.');
$hero_sub   = ch_meta($pid, '_hero_subtitle','The Cane House');
$hero_desc  = ch_meta($pid, '_hero_desc',  'Fresh sugarcane juice pressed live and blended with authentic cold-pressed fruit extracts & natural botanicals. Build your perfect juice — your way.');
$btn1_txt   = ch_meta($pid, '_hero_btn1_text','🥤 Build Your Juice');
$btn1_url   = ch_meta($pid, '_hero_btn1_url','#build');
$btn2_txt   = ch_meta($pid, '_hero_btn2_text','Hire for Events →');
$btn2_url   = ch_meta($pid, '_hero_btn2_url','#hire');

// ── ORDER STEPS ──────────────────────────────────────────────────────────────
$steps = get_post_meta($pid, '_order_steps', true);
if (empty($steps)) $steps = array(
    array('num'=>'1','emoji'=>'📏','title'=>'Select Size',   'desc'=>'Choose from Mini 250ml right up to Group Sharing 1.5L — perfect for every occasion'),
    array('num'=>'2','emoji'=>'🌾','title'=>'Select Cane',   'desc'=>'Yellow Cane (light golden) or Red Cane (rich amber, +£0.50)'),
    array('num'=>'3','emoji'=>'🥤','title'=>'Select Texture','desc'=>'Classic No Peel for a grassy taste, or Smooth With Peel for a cleaner finish (+£0.50)'),
    array('num'=>'4','emoji'=>'🍋','title'=>'Select Flavour','desc'=>'Pure Cane (free), Citrus Blends (+£0.50) or Tropical Blends (+£1.00)'),
    array('num'=>'5','emoji'=>'🎉','title'=>'Enjoy!',         'desc'=>'Served chilled, no ice unless requested — pure fresh natural goodness in every sip'),
);

// ── REVIEWS ──────────────────────────────────────────────────────────────────
$reviews = get_post_meta($pid, '_reviews', true);
if (empty($reviews)) $reviews = array(
    array('name'=>'Sarah Johnson','role'=>'Verified Customer','text'=>'The freshest cane juice I\'ve ever had in the UK. The ginger blend is absolutely life-changing! Highly recommend for events too.','avatar'=>'https://i.pravatar.cc/300?u=1'),
    array('name'=>'Mohammed Ali', 'role'=>'Verified Customer','text'=>'Reminds me of home! Pressed live right in front of you. No added sugar but so naturally sweet and refreshing. 10/10.','avatar'=>'https://i.pravatar.cc/300?u=2'),
    array('name'=>'Emma Wright',  'role'=>'Event Client',     'text'=>'We hired The Cane House for our wedding and it was the highlight! Guests loved the live pressing experience. So unique!','avatar'=>'https://i.pravatar.cc/300?u=3'),
);

// ── HIRE ─────────────────────────────────────────────────────────────────────
$hire_title = ch_meta($pid, '_hire_title', 'Bring Us to Your Event');
$hire_desc  = ch_meta($pid, '_hire_desc',  'Elevate your celebration with our premium live-pressed sugarcane juice experience.');
$hire_cards = get_post_meta($pid, '_hire_cards', true);
if (empty($hire_cards)) $hire_cards = array(
    array('icon'=>'💒','title'=>'Weddings',        'desc'=>'Add a traditional and healthy touch to your big day. We serve fresh juice live during your reception or party.','list'=>"Reception Drinks\nMehndi & Sangeet\nPost-Ceremony Refreshment"),
    array('icon'=>'🏢','title'=>'Corporate Events','desc'=>'Perfect for office parties, wellness days, and conferences. A healthy alternative to sugary sodas.','list'=>"Office Wellness Days\nProduct Launches\nExhibitions & Fairs"),
    array('icon'=>'🎉','title'=>'Private Parties', 'desc'=>'From birthdays to garden parties, we bring the vibe. Freshly pressed juice for guests of all ages.','list'=>"Birthday Parties\nCommunity Festivals\nGarden & BBQ Events"),
);

// ── FAQ ───────────────────────────────────────────────────────────────────────
$faqs = get_post_meta($pid, '_faqs', true);
if (empty($faqs)) $faqs = array(
    array('q'=>'Do you add any sugar or preservatives?',  'a'=>'No, absolutely not. Our sugarcane juice is 100% natural, pressed live from the stalk. The sweetness comes entirely from the natural sugars in the cane itself.'),
    array('q'=>'How long does the juice stay fresh?',     'a'=>'Fresh sugarcane juice is best enjoyed immediately after pressing. However, if kept chilled, it can stay fresh for up to 24 hours.'),
    array('q'=>'What events can I hire you for?',         'a'=>'We cater for all types of events including weddings, birthdays, corporate gatherings, festivals, and community events across the UK.'),
    array('q'=>'Is your sugarcane juice sustainable?',    'a'=>'Yes! Sugarcane is a highly sustainable crop. Even our leftover fibre (bagasse) is biodegradable and can be used for composting or as biofuel.'),
);

// ── CONTACT ───────────────────────────────────────────────────────────────────
$phone     = ch_meta($pid, '_contact_phone',    '+44 7887 699 208');
$website   = ch_meta($pid, '_contact_website',  'www.thecanehouse.co.uk');
$events_c  = ch_meta($pid, '_contact_events',   'Available across the UK for events, weddings & community gatherings');
$franchise = ch_meta($pid, '_contact_franchise','Franchise enquiries warmly welcomed — reach out today');
$api_url   = ch_meta($pid, '_contact_api',      '');

// ── MARQUEE & FRANCHISE LOCATIONS ────────────────────────────────────────────
$marquee_text = get_option('canehouse_marquee', 'Pressed Fresh ✦ Served Cool ✦ No Added Sugar ✦ No Preservatives ✦ Pressed Live ✦ Natural Goodness ✦ Build Your Juice ✦ Events & Hire');
$marquee_items = array_map('trim', explode('✦', $marquee_text));
$locations_raw = get_option('canehouse_franchise_locations', "London Central\nManchester Hub\nBirmingham West\nLeeds North\nGlasgow Fresh\nCardiff Bay");
$locations = array_filter(array_map('trim', explode("\n", $locations_raw)));
?>

<!-- ═══════════════════════════════════════════════════════ HERO -->
<section id="hero" class="fade-up">
  <div class="hero-bubbles">
    <div class="bubble"></div><div class="bubble"></div><div class="bubble"></div>
    <div class="bubble"></div><div class="bubble"></div>
  </div>
  <div class="hero-deco d1">🌿</div>
  <div class="hero-deco d2">🌿</div>
  <div class="hero-inner">
    <div class="hero-left">
      <div class="hero-tag"><?php echo esc_html($hero_tag); ?></div>
      <h1 class="hero-title"><?php echo esc_html($hero_t1); ?><span class="accent"><?php echo esc_html($hero_t2); ?></span></h1>
      <div class="hero-subtitle"><?php echo esc_html($hero_sub); ?></div>
      <p class="hero-desc"><?php echo esc_html($hero_desc); ?></p>
      <div class="hero-btns">
        <a href="<?php echo esc_url($btn1_url); ?>" class="btn-lime"><?php echo esc_html($btn1_txt); ?></a>
        <a href="<?php echo esc_url($btn2_url); ?>" class="btn-outline"><?php echo esc_html($btn2_txt); ?></a>
      </div>
      <div class="hero-badges">
        <span class="badge-item fade-left" style="transition-delay:0s;">No Added Sugar</span>
        <span class="badge-item fade-left" style="transition-delay:0.10s;">No Preservatives</span>
        <span class="badge-item fade-left" style="transition-delay:0.20s;">Pressed Live</span>
        <span class="badge-item fade-left" style="transition-delay:0.30s;">Served Chilled</span>
      </div>
    </div>
    <div class="hero-right">
      <div class="hero-glow hero-cup-wrap">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/thecanehouselogo.png" alt="The CaneHouse" style="transform:rotate(15deg);" />
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
    <?php foreach(array_merge($marquee_items, $marquee_items) as $item): ?>
      <span class="marquee-item"><?php echo esc_html($item); ?></span><span class="marquee-sep">✦</span>
    <?php endforeach; ?>
  </div>
</div>

<!-- ═══════════════════════════════════════════════ HOW TO ORDER -->
<section id="how-to-order">
  <div class="how-header fade-up">
    <div class="section-tag">Simple &amp; Easy</div>
    <h2 class="section-title">How to <span class="accent">Order</span></h2>
    <p class="section-body">Build your perfect fresh cane juice in just 5 steps. Pressed live, just for you.</p>
  </div>
  <div class="steps-grid">
    <?php foreach($steps as $i => $step): $is_last = ($i === count($steps)-1); ?>
    <div class="step-card fade-up" <?php if($is_last) echo 'style="border-color:var(--lime);"'; ?>>
      <div class="step-num" <?php if($is_last) echo 'style="background:linear-gradient(135deg,#9bb800,#c8e830);color:var(--green-deep)"'; ?>><?php echo esc_html($step['num'] ?? $i+1); ?></div>
      <div class="step-emoji <?php echo $is_last ? 'shakking-leaf' : ''; ?>"><?php echo esc_html($step['emoji']); ?></div>
      <div class="step-title"><?php echo esc_html($step['title']); ?></div>
      <div class="step-desc"><?php echo esc_html($step['desc']); ?></div>
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
      <?php foreach($reviews as $i => $r): ?>
      <div class="review-card fade-up <?php echo $i===0 ? 'active' : ''; ?>">
        <div class="review-avatar"><img src="<?php echo esc_url($r['avatar']); ?>" alt="<?php echo esc_attr($r['name']); ?>"></div>
        <div class="review-content">
          <div class="review-rating">⭐⭐⭐⭐⭐</div>
          <p class="review-text">"<?php echo esc_html($r['text']); ?>"</p>
          <div class="review-meta">
            <div class="review-name"><?php echo esc_html($r['name']); ?></div>
            <div class="review-subtitle"><?php echo esc_html($r['role']); ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="reviews-nav-vertical">
      <div class="nav-dots" id="nav-dots">
        <?php for($i=0;$i<count($reviews);$i++): ?>
        <span class="dot <?php echo $i===0?'active':''; ?>"></span>
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
    <!-- SIZES -->
    <div class="option-card fade-left">
      <div class="option-header">
        <div class="option-num">1</div>
        <div><div class="option-title">Size</div><div class="option-sub">Pick your cup size</div></div>
      </div>
      <div class="price-rows">
        <div class="price-row"><div class="row-left"><div class="row-icon">🥤</div><div><div class="row-name">Mini (250ml)</div><div class="row-desc">Quick refresh, great for kids or first-timers</div></div></div><div class="row-price">£4.00</div></div>
        <div class="price-row featured"><div class="row-left"><div class="row-icon">🥤</div><div><div class="row-name">Regular (350ml)</div><div class="row-desc">Ideal single serving — balanced &amp; refreshing</div></div></div><div class="row-right"><span class="row-badge">Popular</span><div class="row-price">£5.50</div></div></div>
        <div class="price-row"><div class="row-left"><div class="row-icon">🧃</div><div><div class="row-name">Large (550ml)</div><div class="row-desc">For a longer, more refreshing drink</div></div></div><div class="row-price">£7.00</div></div>
        <div class="price-row"><div class="row-left"><div class="row-icon">🫙</div><div><div class="row-name">Sharing Jug (750ml)</div><div class="row-desc">Great for two — perfect for sharing</div></div></div><div class="row-price">£9.00</div></div>
        <div class="price-row"><div class="row-left"><div class="row-icon">🍶</div><div><div class="row-name">Family Sharing (1L)</div><div class="row-desc">Perfect for families</div></div></div><div class="row-price">£14.50</div></div>
        <div class="price-row featured"><div class="row-left"><div class="row-icon">🍾</div><div><div class="row-name">Group Sharing (1.5L)</div><div class="row-desc">Ideal for group gatherings</div></div></div><div class="row-right"><span class="row-badge">Best Value</span><div class="row-price">£19.50</div></div></div>
      </div>
    </div>
    <!-- CANE + TEXTURE -->
    <div class="option-card fade-right">
      <div class="option-header"><div class="option-num">2</div><div><div class="option-title">Cane Type</div><div class="option-sub">Choose your cane</div></div></div>
      <div class="price-rows" style="margin-bottom:2rem;">
        <div class="price-row featured"><div class="row-left"><div class="row-icon">🌾</div><div><div class="row-name">Yellow Cane</div><div class="row-desc">Light golden, fresh and refreshing</div></div></div><span class="row-badge">Included</span></div>
        <div class="price-row"><div class="row-left"><div class="row-icon">🎋</div><div><div class="row-name">Red Cane</div><div class="row-desc">Naturally sweeter, rich golden-amber tone</div></div></div><div class="row-price">+£0.50</div></div>
      </div>
      <div class="option-header" style="margin-top:0.5rem;"><div class="option-num">3</div><div><div class="option-title">Texture</div><div class="option-sub">How it's pressed</div></div></div>
      <div class="price-rows">
        <div class="price-row featured"><div class="row-left"><div class="row-icon">🥢</div><div><div class="row-name">Classic</div><div class="row-desc">No Peel — light grassy, traditional taste</div></div></div><span class="row-badge">Included</span></div>
        <div class="price-row"><div class="row-left"><div class="row-icon">✨</div><div><div class="row-name">Smooth</div><div class="row-desc">With Peel — cleaner, smoother finish</div></div></div><div class="row-price">+£0.50</div></div>
      </div>
    </div>
    <!-- FLAVOURS -->
    <div class="option-card fade-up" style="grid-column:1/-1;">
      <div class="option-header"><div class="option-num">4</div><div><div class="option-title">Flavour</div><div class="option-sub">Pick your blend</div></div></div>
      <div class="flavour-grid">
        <div class="flavour-chip"><span class="chip-emoji shakking-leaf">🌿</span><div class="chip-name">Pure Cane</div><div class="chip-price free">Included — Clean &amp; natural</div></div>
        <div class="flavour-chip"><span class="chip-emoji shakking-leaf">🍋</span><div class="chip-name">Lemon</div><div class="chip-price">+£0.50 · Citrus Blend</div></div>
        <div class="flavour-chip"><span class="chip-emoji shakking-leaf">🫚</span><div class="chip-name">Ginger</div><div class="chip-price">+£0.50 · Citrus Blend</div></div>
        <div class="flavour-chip"><span class="chip-emoji shakking-leaf">🌀</span><div class="chip-name">Lemon &amp; Ginger</div><div class="chip-price">+£0.50 · Citrus Blend</div></div>
        <div class="flavour-chip"><span class="chip-emoji shakking-leaf">🌱</span><div class="chip-name">Mint</div><div class="chip-price">+£0.50 · Citrus Blend</div></div>
        <div class="flavour-chip"><span class="chip-emoji shakking-leaf">🍍</span><div class="chip-name">Pineapple</div><div class="chip-price">+£1.00 · Tropical Blend</div></div>
        <div class="flavour-chip"><span class="chip-emoji shakking-leaf">🍉</span><div class="chip-name">Watermelon</div><div class="chip-price">+£1.00 · Tropical Blend</div></div>
        <div class="flavour-chip"><span class="chip-emoji shakking-leaf">🍓</span><div class="chip-name">Strawberry</div><div class="chip-price">+£1.00 · Tropical Blend</div></div>
      </div>
      <p style="margin-top:1.2rem;font-size:0.72rem;color:var(--text-muted);font-style:italic;">* As different cane types are freshly pressed in a single machine, slight variation in colour and taste may occur. Contains natural sugars — please consume responsibly.</p>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════ BENEFITS -->
<section id="benefits">
  <div class="benefits-inner">
    <div class="fade-left">
      <div class="section-tag">Good for You</div>
      <h2 class="section-title">Why Sugarcane Juice is <span class="accent" style="color:var(--lime);">Loved Worldwide</span></h2>
      <p class="section-body">Fresh sugarcane juice is not just delicious — it's packed with natural benefits. Enjoyed across tropical cultures for over 2,000 years.</p>
      <div class="benefits-list">
        <div class="benefit-item"><div class="benefit-icon">⚡</div><div><div class="b-title">Natural Energy Booster</div><div class="b-desc">Provides instant energy with natural sugars — no additives or artificial ingredients.</div></div></div>
        <div class="benefit-item"><div class="benefit-icon">💧</div><div><div class="b-title">Hydrating &amp; Cooling</div><div class="b-desc">Perfect for warm days, helping to refresh and rehydrate the body naturally.</div></div></div>
        <div class="benefit-item"><div class="benefit-icon">🌿</div><div><div class="b-title">Rich in Natural Nutrients</div><div class="b-desc">Contains antioxidants, minerals, and electrolytes your body loves.</div></div></div>
        <div class="benefit-item"><div class="benefit-icon">🫁</div><div><div class="b-title">Supports Digestion</div><div class="b-desc">Traditionally enjoyed with lemon and ginger to aid digestion.</div></div></div>
        <div class="benefit-item"><div class="benefit-icon">🛡️</div><div><div class="b-title">Boosts Immunity &amp; Refreshing</div><div class="b-desc">Natural compounds support overall wellness. Unlike fizzy drinks — clean, fresh and light.</div></div></div>
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
        <div style="margin-top:1.8rem;padding-top:1.5rem;border-top:1px solid rgba(200,232,48,0.15);">
          <p style="font-size:0.75rem;color:rgba(255,255,255,0.45);font-style:italic;line-height:1.6;">Sugarcane has been cherished for over 2,000 years. Even the leftover fibre is biodegradable — a truly sustainable crop.</p>
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
        <svg width="180" height="200" viewBox="0 0 180 200" xmlns="http://www.w3.org/2000/svg" style="position:relative;z-index:1">
          <rect x="50" y="20" width="18" height="160" rx="6" fill="rgba(200,232,48,0.3)"/>
          <rect x="78" y="10" width="18" height="170" rx="6" fill="rgba(200,232,48,0.4)"/>
          <rect x="106" y="25" width="18" height="155" rx="6" fill="rgba(200,232,48,0.3)"/>
          <path d="M68 20 Q90 0 110 15" stroke="rgba(200,232,48,0.6)" stroke-width="4" fill="none" stroke-linecap="round"/>
          <path d="M96 10 Q118 -5 135 12" stroke="rgba(200,232,48,0.5)" stroke-width="4" fill="none" stroke-linecap="round"/>
          <text x="90" y="195" text-anchor="middle" font-family="Nunito,sans-serif" font-weight="900" font-size="10" fill="rgba(255,255,255,0.4)" letter-spacing="2">THE CANE HOUSE</text>
        </svg>
      </div>
      <div class="story-secondary-card"><p class="story-quote">"Sugarcane — one of nature's most generous gifts. Pure energy, pressed fresh."</p></div>
      <div class="story-year-badge"><span>2,000+<br/>Years<br/>of Cane</span></div>
    </div>
    <div class="fade-right">
      <div class="section-tag">The Story of Sugarcane</div>
      <h2 class="section-title">Beyond the <span class="accent">Juice</span></h2>
      <p class="section-body" style="margin-top:1rem;">Sugarcane has been cherished for over 2,000 years, originating in South and Southeast Asia and now grown across the world. Fresh sugarcane juice is naturally sweet, refreshing, and enjoyed across tropical cultures.</p>
      <p class="section-body" style="margin-top:1rem;font-size:0.9rem;">Beyond juice, sugarcane offers a range of valuable products. At The Cane House, even the leftover fibre is biodegradable — making sugarcane a truly sustainable crop.</p>
      <div class="story-facts">
        <div class="story-fact"><div class="fact-icon">🍬</div><div class="fact-title">Sugar &amp; Jaggery</div><div class="fact-desc">Traditional sweeteners from sugarcane</div></div>
        <div class="story-fact"><div class="fact-icon">🫙</div><div class="fact-title">Molasses</div><div class="fact-desc">Rich syrup with deep mineral content</div></div>
        <div class="story-fact"><div class="fact-icon">⛽</div><div class="fact-title">Ethanol</div><div class="fact-desc">Clean-burning biofuel from fermentation</div></div>
        <div class="story-fact"><div class="fact-icon">🌱</div><div class="fact-title">Eco Fibre</div><div class="fact-desc">Biodegradable — fully sustainable crop</div></div>
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
      <?php foreach($hire_cards as $card): ?>
      <div class="hire-card fade-up">
        <div class="h-card-icon"><?php echo esc_html($card['icon']); ?></div>
        <h3><?php echo esc_html($card['title']); ?></h3>
        <p><?php echo esc_html($card['desc']); ?></p>
        <ul class="h-card-list">
          <?php foreach(explode("\n", $card['list']) as $item): if(trim($item)): ?>
          <li><?php echo esc_html(trim($item)); ?></li>
          <?php endif; endforeach; ?>
        </ul>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="hire-features-bar fade-up">
      <div class="h-feature"><span class="hf-icon">🌿</span><div class="hf-text">Pressed Live On-Site</div></div>
      <div class="h-feature"><span class="hf-icon">❄️</span><div class="hf-text">Naturally Chilled</div></div>
      <div class="h-feature"><span class="hf-icon">🥤</span><div class="hf-text">Unlimited Servings Options</div></div>
      <div class="h-feature"><span class="hf-icon">🛡️</span><div class="hf-text">Fully Insured &amp; Certified</div></div>
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
    <p class="section-body">Be part of the fresh juice revolution. Bring the live pressed cane experience to your city.</p>
    <div class="juice-showcase">
      <div class="showcase-container" id="showcase-track">
        <div class="showcase-card active" data-index="0"><img src="https://images.unsplash.com/photo-1546173159-315724a31696?auto=format&fit=crop&q=80&w=600" alt="Pure Cane"><div class="showcase-info"><h3>Pure Yellow Cane</h3><p>Fresh &amp; Naturally Sweet</p></div></div>
        <div class="showcase-card next" data-index="1"><img src="https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&q=80&w=600" alt="Lemon Blend"><div class="showcase-info"><h3>Zesty Lemon</h3><p>Citrus Refreshment</p></div></div>
        <div class="showcase-card" data-index="2"><img src="https://images.unsplash.com/photo-1556881286-fc6915169721?auto=format&fit=crop&q=80&w=600" alt="Ginger Twist"><div class="showcase-info"><h3>Spicy Ginger</h3><p>Warming &amp; Healthy</p></div></div>
        <div class="showcase-card prev" data-index="3"><img src="https://images.unsplash.com/photo-1595981267035-7b04ca84a82d?auto=format&fit=crop&q=80&w=600" alt="Cool Mint"><div class="showcase-info"><h3>Cooling Mint</h3><p>Ultimate Freshness</p></div></div>
      </div>
      <div class="showcase-controls">
        <button class="s-btn" id="showcase-prev">←</button>
        <button class="s-btn" id="showcase-next">→</button>
      </div>
    </div>
    <div class="franchise-marquee">
      <div class="franchise-track">
        <?php foreach(array_merge($locations, $locations) as $loc): ?>
        <div class="f-item"><span class="f-icon">📍</span><span class="f-name"><?php echo esc_html($loc); ?></span></div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="franchise-contact">
      <a href="tel:<?php echo esc_attr(str_replace(' ', '', $phone)); ?>" class="contact-pill">📞 <?php echo esc_html($phone); ?></a>
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
    <?php foreach($faqs as $i => $faq): ?>
    <div class="faq-item <?php echo $i===0?'active':''; ?>">
      <button class="faq-question"><?php echo esc_html($faq['q']); ?><div class="faq-icon">+</div></button>
      <div class="faq-answer"><p><?php echo esc_html($faq['a']); ?></p></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- MAP -->
<iframe src="https://www.google.com/maps/embed?pb=!1m23!1m12!1m3!1d120560.61893157221!2d73.17017714511401!3d19.21618509484755!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!4m8!3e6!4m0!4m5!1s0x3be792574416f8f3%3A0x7663c40ae0d632a6!2sshanti+sagar+resort+map!3m2!1d19.2161984!2d73.2402176!5e0!3m2!1sen!2sin!4v1499686069577" width="100%" height="350" frameborder="0" style="border:0" allowfullscreen></iframe>

<!-- ════════════════════════════════════════════════════ CONTACT -->
<section id="contact">
  <div class="fade-left">
    <div class="section-tag">Say Hello</div>
    <h2 class="section-title">Get in <span class="accent">Touch</span></h2>
    <p class="section-body" style="margin-top:0.8rem;margin-bottom:2rem;">Questions about our juices, booking us for your event, or interested in franchise opportunities? We'd love to hear from you.</p>
    <div class="contact-detail"><div class="cd-icon">📞</div><div><div class="cd-label">Call Us</div><div class="cd-val"><?php echo esc_html($phone); ?></div></div></div>
    <div class="contact-detail"><div class="cd-icon">🌐</div><div><div class="cd-label">Website</div><div class="cd-val"><?php echo esc_html($website); ?></div></div></div>
    <div class="contact-detail"><div class="cd-icon">🎪</div><div><div class="cd-label">Events &amp; Hire</div><div class="cd-val"><?php echo esc_html($events_c); ?></div></div></div>
    <div class="contact-detail"><div class="cd-icon">🤝</div><div><div class="cd-label">Franchise</div><div class="cd-val"><?php echo esc_html($franchise); ?></div></div></div>
  </div>
  <div class="contact-form fade-right">
    <div class="form-title">Send Us a Message 🌿</div>
    <div class="form-group"><label class="form-label">Full Name <span style="color:#ef4444">*</span></label><input type="text" id="name" class="form-input" placeholder="Your name" required/></div>
    <div class="form-group"><label class="form-label">Mobile Number</label><input type="tel" id="mobile" class="form-input" placeholder="+44 ..."/></div>
    <div class="form-group"><label class="form-label">I'm enquiring about</label>
      <select id="enquiry-type" class="form-select">
        <option value="">Select enquiry type...</option>
        <option>General Enquiry</option>
        <option>Event / Stall Hire</option>
        <option>Franchise Opportunity</option>
        <option>Something Else</option>
      </select>
    </div>
    <div class="form-group"><label class="form-label">Email Address <span style="color:#ef4444">*</span></label><input type="email" id="email" class="form-input" placeholder="you@email.com" required/></div>
    <div class="form-group"><label class="form-label">Message <span style="color:#ef4444">*</span></label><textarea id="query" class="form-textarea" placeholder="Tell us more — event date, location, expected guests..." required></textarea></div>
    <button class="form-submit" id="submitBtn" type="button"><span id="btnText">Send Message 🥤</span></button>
    <div id="formStatus" class="form-status" style="display:none;margin-top:12px;padding:10px 14px;border-radius:8px;font-size:0.9rem;"></div>
  </div>
</section>

<?php
// Pass API URL to JS
if($api_url):
?>
<script>var CANEHOUSE_API_URL = '<?php echo esc_js($api_url); ?>';</script>
<?php endif; ?>

<?php get_footer(); ?>
