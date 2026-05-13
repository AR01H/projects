<?php get_header(); ?>

<?php
// ═══════════════════════════════════════════════════════
//  PAGE DATA
// ═══════════════════════════════════════════════════════

$channels = [
    [ 'href' => 'tel:+447887699208',                    'target' => '',       'icon' => '📞', 'label' => 'Call Us',   'value' => '+44 7887 699 208',          'note' => 'Mon–Sun · 9am–8pm'                  ],
    [ 'href' => 'https://wa.me/447887699208',            'target' => '_blank', 'icon' => '💬', 'label' => 'WhatsApp', 'value' => 'Chat with Us',               'note' => 'Quick responses · Events & bookings' ],
    [ 'href' => 'mailto:hello@thecanehouse.co.uk',       'target' => '',       'icon' => '✉️', 'label' => 'Email',    'value' => 'hello@thecanehouse.co.uk',   'note' => 'We reply within 24 hours'           ],
    [ 'href' => 'https://www.thecanehouse.co.uk',        'target' => '_blank', 'icon' => '🌐', 'label' => 'Website',  'value' => 'thecanehouse.co.uk',         'note' => 'Full menu · Events · Franchise'     ],
];

$enquiry_options = [
    'General Enquiry',
    'Event / Stall Hire',
    'Franchise Opportunity',
    'Business Partnership',
    'Media / Press',
    'Customer Support',
    'Something Else',
];

$stores = [
    [
        'city'    => 'London',
        'name'    => 'London Central',
        'status'  => 'open',
        'address' => '📍 Central London Market, London EC1V 2NX',
        'hours'   => '⏰ Mon–Fri: 8am–8pm · Sat–Sun: 9am–9pm',
        'active'  => true,
        'map_url' => 'https://maps.google.com',
        'actions' => 'directions',
    ],
    [
        'city'    => 'Manchester',
        'name'    => 'Manchester Hub',
        'status'  => 'open',
        'address' => '📍 Northern Quarter, Manchester M4 1LA',
        'hours'   => '⏰ Mon–Sun: 9am–9pm',
        'active'  => false,
        'map_url' => 'https://maps.google.com',
        'actions' => 'directions',
    ],
    [
        'city'    => 'Birmingham',
        'name'    => 'Birmingham West',
        'status'  => 'open',
        'address' => '📍 Broad Street, Birmingham B1 2HF',
        'hours'   => '⏰ Mon–Sun: 10am–8pm',
        'active'  => false,
        'map_url' => 'https://maps.google.com',
        'actions' => 'directions',
    ],
    [
        'city'    => 'Leeds',
        'name'    => 'Leeds North',
        'status'  => 'coming',
        'address' => '📍 Kirkgate Market, Leeds LS1 3AW',
        'hours'   => '⏰ Opening Q1 2025',
        'active'  => false,
        'map_url' => '',
        'actions' => 'interest',
    ],
    [
        'city'    => 'Glasgow',
        'name'    => 'Glasgow Fresh',
        'status'  => 'coming',
        'address' => '📍 Buchanan Street, Glasgow G1 3HW',
        'hours'   => '⏰ Opening Q2 2025',
        'active'  => false,
        'map_url' => '',
        'actions' => 'interest',
    ],
];

$social_links = [
    [ 'icon' => '📸', 'label' => 'Instagram', 'href' => '#',                              'target' => ''       ],
    [ 'icon' => '📘', 'label' => 'Facebook',  'href' => '#',                              'target' => ''       ],
    [ 'icon' => '🎵', 'label' => 'TikTok',    'href' => '#',                              'target' => ''       ],
    [ 'icon' => '💬', 'label' => 'WhatsApp',  'href' => 'https://wa.me/447887699208',     'target' => '_blank' ],
    [ 'icon' => '▶️', 'label' => 'YouTube',   'href' => '#',                              'target' => ''       ],
];
?>


<!-- ═══════════ HERO ═══════════ -->
<section class="hero">
  <div class="hero-ring"></div>
  <div class="hero-ring2"></div>
  <div class="hero-inner">
    <div class="hero-tag">Get in Touch</div>
    <h1>We'd Love to<br><em>Hear From You</em></h1>
    <p>Whether you want to book us for an event, explore franchise opportunities, find your nearest location, or simply say hello — we are here and ready.</p>
  </div>
</section>


<!-- ═══════════ CHANNELS ═══════════ -->
<div class="channels-section">
  <div class="channels-grid">
    <?php foreach ( $channels as $ch ) : ?>
      <a href="<?php echo esc_url( $ch['href'] ); ?>"
         class="ch-card"
         <?php echo $ch['target'] ? 'target="' . esc_attr( $ch['target'] ) . '"' : ''; ?>>
        <div class="ch-icon"><?php echo $ch['icon']; ?></div>
        <div class="ch-label"><?php echo esc_html( $ch['label'] ); ?></div>
        <div class="ch-value"><?php echo esc_html( $ch['value'] ); ?></div>
        <div class="ch-note"><?php echo esc_html( $ch['note'] ); ?></div>
      </a>
    <?php endforeach; ?>
  </div>
</div>


<!-- ═══════════ FORM + STORES ═══════════ -->
<section class="content-section">
  <div class="inner">
    <div class="content-grid">

      <!-- CONTACT FORM -->
      <div class="form-side reveal">
        <div class="tag">Send a Message</div>
        <h2 class="form-title">Let's Start a<br><em>Conversation</em></h2>
        <div class="form-grid">
          <div class="form-group">
            <label>First Name</label>
            <input type="text" placeholder="Your first name">
          </div>
          <div class="form-group">
            <label>Last Name</label>
            <input type="text" placeholder="Your last name">
          </div>
        </div>
        <div style="height:16px"></div>
        <div class="form-grid">
          <div class="form-group">
            <label>Email Address</label>
            <input type="email" placeholder="your@email.com">
          </div>
          <div class="form-group">
            <label>Phone Number</label>
            <input type="tel" placeholder="+44 ...">
          </div>
        </div>
        <div style="height:16px"></div>
        <div class="form-grid form-full">
          <div class="form-group">
            <label>I'm Enquiring About</label>
            <select>
              <option value="">Select enquiry type...</option>
              <?php foreach ( $enquiry_options as $option ) : ?>
                <option><?php echo esc_html( $option ); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div style="height:16px"></div>
        <div class="form-grid form-full">
          <div class="form-group">
            <label>Your Message</label>
            <textarea placeholder="Tell us what you have in mind..."></textarea>
          </div>
        </div>
        <button class="form-submit" onclick="handleSubmit()">Send Message 🌿</button>
        <div class="success-msg" id="successMsg">
          ✅ Thank you! We've received your message and will get back to you within 24 hours. In the meantime, follow us on social for daily freshness updates!
        </div>
      </div>

      <!-- STORE LOCATOR -->
      <div class="stores-side reveal">
        <div class="tag">Store Locator</div>
        <h2 class="form-title">Find Your<br><em>Nearest Cane</em></h2>
        <div class="stores-list">
          <?php foreach ( $stores as $store ) : ?>
            <div class="store-card <?php echo $store['active'] ? 'active' : ''; ?>">
              <div class="store-top">
                <div>
                  <div class="store-city"><?php echo esc_html( $store['city'] ); ?></div>
                  <div class="store-name"><?php echo esc_html( $store['name'] ); ?></div>
                </div>
                <span class="store-status <?php echo esc_attr( $store['status'] ); ?>">
                  <?php echo $store['status'] === 'open' ? 'Open Now' : 'Coming Soon'; ?>
                </span>
              </div>
              <div class="store-details"><?php echo esc_html( $store['address'] ); ?></div>
              <div class="store-hours"><?php echo esc_html( $store['hours'] ); ?></div>
              <div class="store-actions">
                <?php if ( $store['actions'] === 'directions' ) : ?>
                  <a href="<?php echo esc_url( $store['map_url'] ); ?>" target="_blank" class="sa-btn primary">Get Directions</a>
                  <button class="sa-btn ghost">View on Map</button>
                <?php else : ?>
                  <button class="sa-btn ghost">Register Interest</button>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </div>
</section>


<!-- ═══════════ MAP ═══════════ -->
<section class="map-section">
  <div class="inner">
    <h2>Our <em>Locations</em></h2>
    <p>Find a Cane House near you — or bring us to your city through our franchise programme.</p>
    <div class="map-wrap reveal">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2482.959292060483!2d-0.12775!3d51.5074!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNTHCsDMwJzI2LjYiTiAwwrAwNyc0MC4xIlc!5e0!3m2!1sen!2suk!4v1700000000000!5m2!1sen!2suk"
        allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
        title="The Cane House Locations">
      </iframe>
    </div>
  </div>
</section>


<!-- ═══════════ SOCIAL ═══════════ -->
<section class="social-section">
  <h2>Follow the <em>Freshness</em></h2>
  <p>Daily pressing updates, new flavours, events, and community love.</p>
  <div class="social-links">
    <?php foreach ( $social_links as $soc ) : ?>
      <a href="<?php echo esc_url( $soc['href'] ); ?>"
         class="soc-btn"
         <?php echo $soc['target'] ? 'target="' . esc_attr( $soc['target'] ) . '"' : ''; ?>>
        <span class="soc-icon"><?php echo $soc['icon']; ?></span>
        <?php echo esc_html( $soc['label'] ); ?>
      </a>
    <?php endforeach; ?>
  </div>
</section>


<!-- ═══════════ FRANCHISE CTA ═══════════ -->
<div class="franchise-cta">
  <div class="franchise-cta-inner">
    <div>
      <h3>Interested in a <em>Franchise?</em></h3>
      <p>Bring The Cane House to your city. Full support, proven model, passionate community.</p>
    </div>
    <a href="franchise.php" class="franchise-cta-btn">Start Your Cane Journey →</a>
  </div>
</div>


<script>
function handleSubmit() {
  const msg = document.getElementById('successMsg');
  msg.style.display = 'block';
  setTimeout(() => { msg.style.display = 'none'; }, 6000);
}

document.querySelectorAll('.store-card').forEach(card => {
  card.addEventListener('click', () => {
    document.querySelectorAll('.store-card').forEach(c => c.classList.remove('active'));
    card.classList.add('active');
  });
});

const observer = new IntersectionObserver((entries) => {
  entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>

<?php get_footer(); ?>