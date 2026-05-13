<?php get_header(); ?>

<?php
// ═══════════════════════════════════════════════════════
//  PAGE DATA
// ═══════════════════════════════════════════════════════

$hero_pills = [
    [ 'icon' => '💒', 'name' => 'Weddings',           'desc' => 'Reception · Mehndi · Sangeet · Post-ceremony' ],
    [ 'icon' => '🏢', 'name' => 'Corporate Events',   'desc' => 'Wellness days · Conferences · Office parties'  ],
    [ 'icon' => '🎂', 'name' => 'Private Parties',    'desc' => 'Birthdays · Garden events · BBQs'              ],
    [ 'icon' => '🎪', 'name' => 'Festivals & Popups', 'desc' => 'Community fairs · Markets · Brand activations' ],
];

$event_cards = [
    [
        'class' => 'weddings',
        'icon'  => '💒',
        'title' => 'Weddings',
        'sub'   => 'The Most Special Day',
        'body'  => 'A live cane juice stall adds a beautiful, healthy, and culturally resonant element to any wedding. Your guests will remember it long after the big day.',
        'list'  => [ 'Reception welcome drinks', 'Mehndi & Sangeet nights', 'Post-ceremony refreshment', 'Custom flavour menu', 'Unlimited servings packages' ],
        'cta'   => 'Book for Your Wedding',
    ],
    [
        'class' => 'corporate',
        'icon'  => '🏢',
        'title' => 'Corporate Events',
        'sub'   => 'Premium Brand Experiences',
        'body'  => 'Stand out from the usual buffet. A live cane pressing stall creates genuine buzz at corporate gatherings and leaves a lasting impression on clients and employees.',
        'list'  => [ 'Office wellness & health days', 'Product launches & exhibitions', 'Client entertainment events', 'Team building days', 'Conference refreshment breaks' ],
        'cta'   => 'Book for Your Event',
    ],
    [
        'class' => 'parties',
        'icon'  => '🎂',
        'title' => 'Private Parties',
        'sub'   => 'Birthdays & Celebrations',
        'body'  => 'From intimate garden gatherings to large birthday bashes, our mobile stall brings live-pressed freshness to any private celebration.',
        'list'  => [ 'Birthday parties · All ages', 'Garden parties & BBQs', 'Family celebrations', 'Eid & Diwali gatherings', 'House warmings' ],
        'cta'   => 'Book for Your Party',
    ],
    [
        'class' => 'popups',
        'icon'  => '🛍️',
        'title' => 'Pop-up Activations',
        'sub'   => 'Brand & Retail',
        'body'  => 'Add a premium live-pressing experience to your brand activation. We work with retailers, brands, and agencies to create memorable consumer moments.',
        'list'  => [ 'Retail store activations', 'Brand launch events', 'Influencer gatherings', 'Sponsorship activations', 'Market & high street popups' ],
        'cta'   => 'Discuss a Partnership',
    ],
    [
        'class' => 'festivals',
        'icon'  => '🎪',
        'title' => 'Festivals',
        'sub'   => 'Community & Culture',
        'body'  => 'Our live cane stall is a festival favourite — drawing long queues, social media posts, and return visits. We are built for high-volume outdoor events.',
        'list'  => [ 'Music & culture festivals', 'Community fairs & fetes', 'Food & drink festivals', 'Sports & outdoor events', 'Street food markets' ],
        'cta'   => 'Book the Stall',
    ],
    [
        'class' => 'catering',
        'icon'  => '🍽️',
        'title' => 'Food & Drink Catering',
        'sub'   => 'Full Beverage Service',
        'body'  => 'We work with caterers, venues, and hospitality teams to provide premium sugarcane beverages as part of a wider event food and drink offering.',
        'list'  => [ 'Catering partnerships', 'Venue collaboration', 'Wedding caterer add-ons', 'Restaurant special events', 'Hotel & hospitality' ],
        'cta'   => 'Partner With Us',
    ],
];

$provides = [
    [ 'icon' => '🏗️', 'title' => 'Full Setup',          'body' => 'We arrive early, set up completely, and pack away at the end. Zero effort required from your side.' ],
    [ 'icon' => '⚙️', 'title' => 'Press Equipment',     'body' => 'Commercial-grade stainless steel pressing machines, cleaning equipment, and all serving tools.' ],
    [ 'icon' => '🌾', 'title' => 'Fresh Cane Supply',   'body' => 'We source and transport fresh cane stalks to your event. Enough for your guest count, guaranteed.' ],
    [ 'icon' => '🌿', 'title' => 'Botanicals & Blends', 'body' => 'Ginger, mint, lemon, and tropical fruit blends are all prepared and brought fresh for your event.' ],
    [ 'icon' => '👕', 'title' => 'Branded Staff',       'body' => 'Uniformed, trained, and professional Cane House staff to run the stall throughout your event.' ],
    [ 'icon' => '♻️', 'title' => 'Eco Packaging',       'body' => 'Compostable cups, lids, and straws. No plastic. Your event stays clean and sustainable.' ],
    [ 'icon' => '🛡️', 'title' => 'Fully Insured',       'body' => 'Public liability insurance and food safety certification. We meet all event venue requirements.' ],
    [ 'icon' => '🎨', 'title' => 'Custom Branding',     'body' => 'For large events, we can apply custom event branding to cups, signage, and stall dressing.' ],
];

$pricing = [
    [
        'featured' => false,
        'tier'     => 'Starter',
        'name'     => 'Small Gathering',
        'price'    => 'POA',
        'sub'      => 'Up to 100 guests · 2–3 hours',
        'features' => [ '1 press machine', '1 Cane House staff member', 'Pure Cane + 2 blends', 'Eco cups & straws', 'Setup & pack down included' ],
    ],
    [
        'featured' => true,
        'tier'     => 'Premium',
        'name'     => 'Medium Event',
        'price'    => 'POA',
        'sub'      => '100–300 guests · Up to 6 hours',
        'features' => [ '2 press machines', '2 Cane House staff members', 'Full menu · All 6 blends', 'Yellow & Red cane options', 'Branded cups available', 'Event day support' ],
    ],
    [
        'featured' => false,
        'tier'     => 'Grand',
        'name'     => 'Large Event',
        'price'    => 'POA',
        'sub'      => '300+ guests · Full day',
        'features' => [ '3+ press machines', 'Full dedicated team', 'Complete bespoke menu', 'Custom event branding', 'Multi-station setup', 'Dedicated event manager' ],
    ],
];

$process_steps = [
    [ 'icon' => '📋', 'title' => 'Enquire', 'body' => 'Fill in our quote form or call us with your event details.' ],
    [ 'icon' => '💬', 'title' => 'Discuss', 'body' => 'We call you to discuss requirements, menu, and logistics.' ],
    [ 'icon' => '📝', 'title' => 'Quote',   'body' => 'Receive a personalised quote within 24 hours. No surprises.' ],
    [ 'icon' => '✅', 'title' => 'Confirm', 'body' => 'Approve your booking with a deposit to secure your date.' ],
    [ 'icon' => '🎉', 'title' => 'Enjoy',   'body' => 'We handle everything. You and your guests enjoy the experience.' ],
];
?>


<!-- ═══════════ HERO ═══════════ -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-left">
    <div class="hero-label">Events & Catering</div>
    <h1>We Come<br><em>To You</em></h1>
    <p class="hero-sub">Bring the live-pressed cane experience to your celebration. Weddings, corporate events, parties, festivals — we set up and press fresh all day.</p>
    <a href="#quote" class="hero-btn">Get a Custom Quote →</a>
  </div>
  <div class="hero-right">
    <?php foreach ( $hero_pills as $pill ) : ?>
      <div class="hero-event-pill">
        <div class="hep-icon"><?php echo $pill['icon']; ?></div>
        <div>
          <div class="hep-name"><?php echo esc_html( $pill['name'] ); ?></div>
          <div class="hep-desc"><?php echo esc_html( $pill['desc'] ); ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>


<!-- ═══════════ EVENT TYPES ═══════════ -->
<section class="section events-section">
  <div class="inner">
    <div class="tag">What We Cater For</div>
    <h2 class="title">Every <em>Celebration</em><br>Deserves Freshness</h2>
    <div class="events-grid">
      <?php foreach ( $event_cards as $card ) : ?>
        <div class="ev-card <?php echo esc_attr( $card['class'] ); ?> reveal">
          <div class="ev-header">
            <div class="ev-deco"><?php echo $card['icon']; ?></div>
            <div class="ev-icon"><?php echo $card['icon']; ?></div>
            <div class="ev-card-title"><?php echo esc_html( $card['title'] ); ?></div>
            <div class="ev-card-sub"><?php echo esc_html( $card['sub'] ); ?></div>
          </div>
          <div class="ev-body">
            <p><?php echo esc_html( $card['body'] ); ?></p>
            <ul class="ev-list">
              <?php foreach ( $card['list'] as $item ) : ?>
                <li><?php echo esc_html( $item ); ?></li>
              <?php endforeach; ?>
            </ul>
            <a href="#quote" class="ev-cta"><?php echo esc_html( $card['cta'] ); ?></a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ═══════════ WHAT WE PROVIDE ═══════════ -->
<section class="section provides-section">
  <div class="inner">
    <div class="tag">What's Included</div>
    <h2 class="title">We Bring <em>Everything</em></h2>
    <div class="provides-grid">
      <?php foreach ( $provides as $item ) : ?>
        <div class="pv-card reveal">
          <div class="pv-icon"><?php echo $item['icon']; ?></div>
          <h3><?php echo esc_html( $item['title'] ); ?></h3>
          <p><?php echo esc_html( $item['body'] ); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ═══════════ PRICING ═══════════ -->
<section class="section pricing-section">
  <div class="inner">
    <div class="tag">Hire Packages</div>
    <h2 class="title">Simple, <em>Transparent</em> Pricing</h2>
    <p>Three hire tiers to match your event. All include setup, fresh cane, staff, and eco packaging. Custom quotes available for all sizes.</p>
    <div class="pricing-grid">
      <?php foreach ( $pricing as $plan ) : ?>
        <div class="price-card <?php echo $plan['featured'] ? 'featured' : ''; ?> reveal">
          <div class="pc-tier"><?php echo esc_html( $plan['tier'] ); ?></div>
          <div class="pc-name"><?php echo esc_html( $plan['name'] ); ?></div>
          <div class="pc-price"><?php echo esc_html( $plan['price'] ); ?> <small>/ event</small></div>
          <div class="pc-sub"><?php echo esc_html( $plan['sub'] ); ?></div>
          <ul class="pc-features">
            <?php foreach ( $plan['features'] as $feature ) : ?>
              <li><?php echo esc_html( $feature ); ?></li>
            <?php endforeach; ?>
          </ul>
          <a href="#quote" class="pc-btn">Get a Quote</a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ═══════════ BOOKING PROCESS ═══════════ -->
<section class="section process-section">
  <div class="inner">
    <div class="tag">Booking Process</div>
    <h2 class="title">Booked in <em>5 Simple Steps</em></h2>
    <div class="process-row reveal">
      <?php foreach ( $process_steps as $step ) : ?>
        <div class="pr-step">
          <div class="pr-circle"><?php echo $step['icon']; ?></div>
          <h3><?php echo esc_html( $step['title'] ); ?></h3>
          <p><?php echo esc_html( $step['body'] ); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ═══════════ CTA ═══════════ -->
<div class="cta-section" id="quote">
  <div class="inner">
    <h2>Ready to Book<br><em>The Cane House?</em></h2>
    <p>Get in touch today for a custom quote. We're available across the UK for events of all sizes — from intimate gatherings to large festivals.</p>
    <div class="cta-btns">
      <a href="tel:+447887699208" class="cta-btn-main">📞 Call +44 7887 699 208</a>
      <a href="contact.php" class="cta-btn-ghost">Send an Enquiry →</a>
    </div>
  </div>
</div>


<script>
const observer = new IntersectionObserver((entries) => {
  entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>

<?php get_footer(); ?>