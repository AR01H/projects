<?php
$process_title = get_option('ah_process_title', 'Guiding You Through Your Most Significant Home Purchase');
?>
<section class="section guiding">
    <div class="container">
      <div class="guiding__inner">
        <div>
          <div class="eyebrow reveal">Our Process</div>
          <h2 class="reveal reveal-delay-1"><?php echo esc_html($process_title); ?></h2>
          <div class="guiding__steps">
            <div class="guiding__step reveal reveal-delay-1">
              <div class="guiding__step-num">1</div>
              <div>
                <h4>Free Discovery Call</h4>
                <p>We learn exactly what you need, your budget, timeline, and must-haves. Zero pressure, zero cost.</p>
              </div>
            </div>
            <div class="guiding__step reveal reveal-delay-2">
              <div class="guiding__step-num">2</div>
              <div>
                <h4>Property Search & Research</h4>
                <p>We scour on and off-market listings, shortlist genuine opportunities, and produce full research
                  reports on each one.</p>
              </div>
            </div>
            <div class="guiding__step reveal reveal-delay-3">
              <div class="guiding__step-num">3</div>
              <div>
                <h4>Viewings & Expert Assessment</h4>
                <p>We accompany you (or view independently) and assess structural condition, neighbourhood, value, and
                  red flags.</p>
              </div>
            </div>
            <div class="guiding__step reveal reveal-delay-4">
              <div class="guiding__step-num">4</div>
              <div>
                <h4>Negotiation & Offer</h4>
                <p>Using real market data, we negotiate hard on your behalf — achieving prices often 5–10% below asking.
                </p>
              </div>
            </div>
            <div class="guiding__step reveal">
              <div class="guiding__step-num">5</div>
              <div>
                <h4>Legal & Completion Support</h4>
                <p>We stay by your side through surveys, searches, solicitors, and completion — until you have the keys
                  in hand.</p>
              </div>
            </div>
          </div>
          <a href="<?php echo esc_url(home_url('/process/guidance')); ?>" class="btn btn-primary reveal" style="margin-top:8px">Learn More
            →</a>
        </div>
        <div class="guiding__visual reveal reveal-delay-2">
          <div class="guiding__property-card">
            <div class="guiding__property-img">🏘️</div>
            <div style="display:flex;justify-content:space-between;align-items:flex-start">
              <div>
                <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:4px">📍 Richmond, London</div>
                <div class="prop-price">£485,000</div>
              </div>
              <div class="badge">✓ Secured</div>
            </div>
            <div class="prop-meta">
              <span class="prop-tag">3 Bed</span>
              <span class="prop-tag">Garden</span>
              <span class="prop-tag">Near Schools</span>
            </div>
            <div
              style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);display:flex;justify-content:space-between">
              <div style="font-size:.8rem;color:var(--text-muted)">Listed at</div>
              <div style="font-size:.8rem;font-weight:700;text-decoration:line-through;color:var(--text-muted)">£510,000
              </div>
            </div>
            <div style="display:flex;justify-content:space-between;margin-top:6px">
              <div style="font-size:.8rem;color:var(--text-muted)">We secured it at</div>
              <div style="font-size:.9rem;font-weight:700;color:#16a34a">£485,000 ✓</div>
            </div>
          </div>
          <div class="guiding__saving">
            <div style="font-size:1.4rem">🎉</div>
            <div>
              <div class="saving-text">Buyer saved £25,000!</div>
              <div style="font-size:.72rem;color:#15803d">Plus 4 months of searching time</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
