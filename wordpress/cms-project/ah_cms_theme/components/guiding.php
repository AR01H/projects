<?php
defined( 'ABSPATH' ) || exit;

$steps = [
	[ 'num' => 1, 'title' => __( 'Free Discovery Call', 'ah-theme' ),             'text' => __( 'We learn exactly what you need — your budget, timeline, and must-haves. Zero pressure, zero cost.', 'ah-theme' ) ],
	[ 'num' => 2, 'title' => __( 'Property Search & Research', 'ah-theme' ),      'text' => __( 'We scour on and off-market listings, shortlist genuine opportunities, and produce full research reports on each one.', 'ah-theme' ) ],
	[ 'num' => 3, 'title' => __( 'Viewings & Expert Assessment', 'ah-theme' ),     'text' => __( 'We accompany you (or view independently) and assess structural condition, neighbourhood, value, and red flags.', 'ah-theme' ) ],
	[ 'num' => 4, 'title' => __( 'Negotiation & Offer', 'ah-theme' ),             'text' => __( 'Using real market data, we negotiate hard on your behalf — achieving prices often 5–10% below asking.', 'ah-theme' ) ],
	[ 'num' => 5, 'title' => __( 'Legal & Completion Support', 'ah-theme' ),      'text' => __( 'We stay by your side through surveys, searches, solicitors, and completion — until you have the keys in hand.', 'ah-theme' ) ],
];
?>
<section class="section guiding">
  <div class="container">
    <div class="guiding__inner">
      <!-- Steps -->
      <div>
        <div class="eyebrow reveal"><?php esc_html_e( 'Our Process', 'ah-theme' ); ?></div>
        <h2 class="reveal reveal-delay-1">
          <?php esc_html_e( 'Guiding You Through Your Most Significant Home Purchase', 'ah-theme' ); ?>
        </h2>
        <div class="guiding__steps">
          <?php foreach ( $steps as $i => $step ) : ?>
            <div class="guiding__step reveal reveal-delay-<?php echo min( $i + 1, 4 ); ?>">
              <div class="guiding__step-num"><?php echo esc_html( $step['num'] ); ?></div>
              <div>
                <h4><?php echo esc_html( $step['title'] ); ?></h4>
                <p><?php echo esc_html( $step['text'] ); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <a href="<?php echo esc_url( home_url( '/guides/buyers-guide/' ) ); ?>"
           class="btn btn-primary reveal btn--arrow" style="margin-top:8px">
          <?php esc_html_e( 'Learn More', 'ah-theme' ); ?> →
        </a>
      </div>

      <!-- Visual card -->
      <div class="guiding__visual reveal reveal-delay-2">
        <div class="guiding__property-card">
          <div class="guiding__property-img">
            <img src="<?php echo esc_url( ah_unsplash( '1600596542815-ffad4c1539a9', 600, 400 ) ); ?>"
                 alt="<?php esc_attr_e( 'Example secured property', 'ah-theme' ); ?>"
                 loading="lazy">
          </div>
          <div style="display:flex;justify-content:space-between;align-items:flex-start">
            <div>
              <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:4px">📍 Richmond, London</div>
              <div class="prop-price">£485,000</div>
            </div>
            <div class="badge badge-success">✓ <?php esc_html_e( 'Secured', 'ah-theme' ); ?></div>
          </div>
          <div class="prop-meta">
            <span class="prop-tag">3 Bed</span>
            <span class="prop-tag">Garden</span>
            <span class="prop-tag">Near Schools</span>
          </div>
          <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);display:flex;justify-content:space-between">
            <div style="font-size:.8rem;color:var(--text-muted)"><?php esc_html_e( 'Listed at', 'ah-theme' ); ?></div>
            <div style="font-size:.8rem;font-weight:700;text-decoration:line-through;color:var(--text-muted)">£510,000</div>
          </div>
          <div style="display:flex;justify-content:space-between;margin-top:6px">
            <div style="font-size:.8rem;color:var(--text-muted)"><?php esc_html_e( 'We secured it at', 'ah-theme' ); ?></div>
            <div style="font-size:.9rem;font-weight:700;color:#16a34a">£485,000 ✓</div>
          </div>
        </div>
        <div class="guiding__saving">
          <div style="font-size:1.4rem">🎉</div>
          <div>
            <div class="saving-text"><?php esc_html_e( 'Buyer saved £25,000!', 'ah-theme' ); ?></div>
            <div style="font-size:.72rem;color:#15803d"><?php esc_html_e( 'Plus 4 months of searching time', 'ah-theme' ); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
