<?php
/**
 * VintageSoulTheme - Masterpiece Vintage 404 Experience
 *
 * Cinematic botanical & Victorian cold-press illustration, interactive
 * juice-press animation, vintage search bar, quick destination tiles,
 * and secret perk unlock.
 */

use VintageSoul\Services\RouteService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="main vst-404-hero paper-rough">

  <!-- 1. Ambient Botanical Atmosphere -->
  <div class="vst-404-ambient" aria-hidden="true">
    <svg class="vst-404-bg-svg" viewBox="0 0 1400 900" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
      <defs>
        <radialGradient id="vst-gold-glow" cx="50%" cy="40%" r="50%">
          <stop offset="0%" stop-color="#f6d599" stop-opacity="0.25"/>
          <stop offset="60%" stop-color="#d49842" stop-opacity="0.08"/>
          <stop offset="100%" stop-color="#2a1a0c" stop-opacity="0"/>
        </radialGradient>
        <linearGradient id="vst-cane-grad" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stop-color="#3d7d4c"/>
          <stop offset="50%" stop-color="#23522f"/>
          <stop offset="100%" stop-color="#0f2b16"/>
        </linearGradient>
      </defs>

      <rect width="100%" height="100%" fill="url(#vst-gold-glow)" />

      <!-- Left Giant Cane Cluster -->
      <g class="vst-sway-stalk vst-sway-stalk--1" opacity="0.22">
        <rect x="40" y="80" width="14" height="750" rx="7" fill="url(#vst-cane-grad)"/>
        <rect x="45" y="80" width="4" height="750" rx="2" fill="#84c593"/>
        <ellipse cx="47" cy="80" rx="16" ry="9" fill="#2d6438"/>
        <ellipse cx="47" cy="240" rx="13" ry="8" fill="#2d6438"/>
        <ellipse cx="47" cy="420" rx="13" ry="8" fill="#2d6438"/>
        <ellipse cx="47" cy="600" rx="13" ry="8" fill="#2d6438"/>
        <path d="M47 80 Q100 40 140 20" stroke="#4a8f5e" stroke-width="3" fill="none" stroke-linecap="round"/>
        <path d="M47 240 Q10 200 -20 180" stroke="#4a8f5e" stroke-width="3" fill="none" stroke-linecap="round"/>
        <path d="M47 420 Q110 390 150 360" stroke="#4a8f5e" stroke-width="3" fill="none" stroke-linecap="round"/>
      </g>

      <!-- Right Giant Cane Cluster -->
      <g class="vst-sway-stalk vst-sway-stalk--2" opacity="0.18">
        <rect x="1330" y="40" width="12" height="800" rx="6" fill="url(#vst-cane-grad)"/>
        <rect x="1334" y="40" width="4" height="800" rx="2" fill="#84c593"/>
        <ellipse cx="1336" cy="40" rx="14" ry="8" fill="#2d6438"/>
        <ellipse cx="1336" cy="210" rx="11" ry="7" fill="#2d6438"/>
        <ellipse cx="1336" cy="400" rx="11" ry="7" fill="#2d6438"/>
        <path d="M1336 40 Q1270 20 1230 0" stroke="#4a8f5e" stroke-width="3" fill="none" stroke-linecap="round"/>
        <path d="M1336 210 Q1380 180 1420 160" stroke="#4a8f5e" stroke-width="3" fill="none" stroke-linecap="round"/>
      </g>
    </svg>
  </div>

  <div class="container vst-404-stage">

    <!-- MASTER ORNATE PARCHMENT CARD WITH DECKLE ROUGH CUT -->
    <div class="vst-404-card">
      
      <!-- Top Wax Seal Stamp -->
      <div class="vst-404-seal" aria-hidden="true">
        <div class="vst-404-seal__inner">
          <span class="vst-404-seal__cane">🌿</span>
          <span class="vst-404-seal__code">404</span>
          <span class="vst-404-seal__tag">LOST HARVEST</span>
        </div>
      </div>

      <!-- KINETIC 404 COLD-PRESS ILLUSTRATION -->
      <div class="vst-404-headline-group">
        <div class="vst-404-digits" aria-label="Error 404: Page Not Found">
          <span class="vst-404-digit">4</span>
          
          <!-- Animated Cold-Press Juice Wheel as the 0 -->
          <div class="vst-404-press-stage" title="The Vintage Sugarcane Press">
            <svg class="vst-press-wheel" viewBox="0 0 110 110" width="110" height="110" xmlns="http://www.w3.org/2000/svg">
              <circle cx="55" cy="55" r="50" fill="#11381b" stroke="#caa06d" stroke-width="3"/>
              <circle cx="55" cy="55" r="42" fill="none" stroke="#8e622d" stroke-width="1.5" stroke-dasharray="6 3" class="vst-press-cog"/>
              
              <!-- Spokes -->
              <line x1="55" y1="13" x2="55" y2="97" stroke="#f6d599" stroke-width="2.5"/>
              <line x1="13" y1="55" x2="97" y2="55" stroke="#f6d599" stroke-width="2.5"/>
              <line x1="25" y1="25" x2="85" y2="85" stroke="#f6d599" stroke-width="2"/>
              <line x1="85" y1="25" x2="25" y2="85" stroke="#f6d599" stroke-width="2"/>
              
              <!-- Center Brass Nut -->
              <circle cx="55" cy="55" r="14" fill="#d49842" stroke="#f6d599" stroke-width="2"/>
              <circle cx="55" cy="55" r="6" fill="#2a1406"/>
            </svg>
            
            <!-- Dripping Juice Droplet -->
            <div class="vst-press-drop" aria-hidden="true">
              <span class="vst-drop-bubble">💧</span>
            </div>
          </div>

          <span class="vst-404-digit">4</span>
        </div>

        <div class="vintage-ribbon-tag">
          <span>LOST IN THE CANE FIELDS</span>
        </div>

        <h1 class="vst-404-title">
          THIS GLASS SEEMS TO BE <em>Empty</em>
        </h1>

        <p class="vst-404-lead">
          The page or vintage recipe you were searching for has been moved, pressed, or simply wandered off into the sugar plantation. 
          Don’t leave thirsty — explore our fresh pours below.
        </p>
      </div>

      <!-- INTERACTIVE SUGARCANE PRESS MINI-ACTION WITH ROUGH CUT -->
      <div class="vst-404-interactive-box" id="vst-press-game">
        <div class="vst-404-interactive-box__content">
          <span class="vst-404-interactive-box__icon">🥤</span>
          <div class="vst-404-interactive-box__text">
            <strong>THIRSTY WHILE LOST?</strong>
            <span>Press the button below to extract a complimentary discount perk!</span>
          </div>
        </div>
        <button type="button" class="btn btn--primary-vintage vst-404-press-btn" id="vst-juice-squeeze-btn">
          <span>⚡ PRESS FRESH JUICE</span>
        </button>
        <div class="vst-404-secret-perk" id="vst-perk-revealed" style="display:none;">
          <span class="vst-perk-badge">✨ VINTAGE PERK UNLOCKED</span>
          <p class="vst-perk-msg">Use code <strong class="vst-perk-code">FRESH404</strong> when requesting an event quote for <strong>10% off</strong> live bar catering!</p>
        </div>
      </div>

      <!-- VINTAGE ON-PAGE SEARCH BAR -->
      <div class="vst-404-search-wrap">
        <form role="search" method="get" class="vst-404-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
          <div class="vst-404-search-input-group">
            <span class="vst-404-search-icon" aria-hidden="true">🔍</span>
            <input type="search" class="vst-404-search-field" placeholder="Search for wedding catering, drinks menu, franchise, health..." value="<?php echo get_search_query(); ?>" name="s" required>
            <button type="submit" class="btn btn--primary-vintage vst-404-search-submit">
              <span>FIND IT</span>
            </button>
          </div>
        </form>
      </div>

      <!-- POPULAR DESTINATION CARDS -->
      <div class="vst-404-nav-grid">
        
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="vst-404-dest-card">
          <span class="vst-404-dest-card__icon">🏡</span>
          <h3 class="vst-404-dest-card__title">HOME PARLOUR</h3>
          <p class="vst-404-dest-card__desc">Return to the front doors and discover our craft story.</p>
          <span class="vst-404-dest-card__cta">Explore Parlour →</span>
        </a>

        <a href="<?php echo esc_url( RouteService::url( 'events' ) ?: home_url( '/events' ) ); ?>" class="vst-404-dest-card">
          <span class="vst-404-dest-card__icon">🎉</span>
          <h3 class="vst-404-dest-card__title">LIVE CATERING</h3>
          <p class="vst-404-dest-card__desc">Book our bespoke live sugarcane bar for your special celebrations.</p>
          <span class="vst-404-dest-card__cta">Book Event →</span>
        </a>

        <a href="<?php echo esc_url( RouteService::url( 'about' ) ?: home_url( '/about' ) ); ?>" class="vst-404-dest-card">
          <span class="vst-404-dest-card__icon">📖</span>
          <h3 class="vst-404-dest-card__title">OUR HERITAGE</h3>
          <p class="vst-404-dest-card__desc">Discover our family tradition and 100% natural extraction craft.</p>
          <span class="vst-404-dest-card__cta">Read Story →</span>
        </a>

        <a href="<?php echo esc_url( RouteService::url( 'franchise' ) ?: home_url( '/franchise' ) ); ?>" class="vst-404-dest-card">
          <span class="vst-404-dest-card__icon">🌱</span>
          <h3 class="vst-404-dest-card__title">FRANCHISE</h3>
          <p class="vst-404-dest-card__desc">Turnkey kiosk partnerships & UK wholesale opportunities.</p>
          <span class="vst-404-dest-card__cta">Partner With Us →</span>
        </a>

        <a href="<?php echo esc_url( RouteService::url( 'history' ) ?: home_url( '/history' ) ); ?>" class="vst-404-dest-card">
          <span class="vst-404-dest-card__icon">📜</span>
          <h3 class="vst-404-dest-card__title">CANE CHRONICLE</h3>
          <p class="vst-404-dest-card__desc">Ancient traditions, botanical varieties, and extraction history.</p>
          <span class="vst-404-dest-card__cta">Read Chronicle →</span>
        </a>

        <a href="<?php echo esc_url( RouteService::url( 'contact' ) ?: home_url( '/contact' ) ); ?>" class="vst-404-dest-card">
          <span class="vst-404-dest-card__icon">📞</span>
          <h3 class="vst-404-dest-card__title">CONCIERGE</h3>
          <p class="vst-404-dest-card__desc">Speak with our London team directly for bespoke requests.</p>
          <span class="vst-404-dest-card__cta">Get In Touch →</span>
        </a>

      </div>

      <!-- FOOTER VINTAGE PROVERB -->
      <div class="vst-404-footer-proverb">
        <span class="vst-proverb-ornament">❦</span>
        <blockquote class="vst-proverb-text">
          “In every field where sugarcane grows, sweetness is never truly lost — it only waits to be pressed.”
        </blockquote>
        <span class="vst-proverb-author">— The Cane House Heritage Journal</span>
      </div>

    </div>

  </div>

</main>

<script>
// Interactive Juice Squeezer Mini-Game on 404 Page
document.addEventListener('DOMContentLoaded', function() {
  var squeezeBtn = document.getElementById('vst-juice-squeeze-btn');
  var perkBox = document.getElementById('vst-perk-revealed');
  var pressWheel = document.querySelector('.vst-press-wheel');
  
  if (squeezeBtn && perkBox) {
    squeezeBtn.addEventListener('click', function() {
      squeezeBtn.disabled = true;
      var span = squeezeBtn.querySelector('span');
      if (span) span.innerHTML = '⚙️ PRESSING IN PROGRESS...';
      
      if (pressWheel) {
        pressWheel.style.animation = 'vst-press-spin-fast 0.6s linear infinite';
      }
      
      setTimeout(function() {
        if (pressWheel) {
          pressWheel.style.animation = 'vst-press-spin 14s linear infinite';
        }
        squeezeBtn.style.display = 'none';
        perkBox.style.display = 'block';
        perkBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }, 1100);
    });
  }
});
</script>

<style>
/* ══════════════════════════════════════════════════════════════════════════
   VINTAGESOUL - MASTERPIECE 404 PAGE STYLING
   ══════════════════════════════════════════════════════════════════════════ */
.vst-404-hero {
  position: relative !important;
  min-height: 94vh !important;
  padding: 80px 20px 70px !important;
  background-color: #edd5ad !important;
  background-image:
    url("assets/images/textures/paper/paper-fiber-a.svg"),
    linear-gradient(180deg, #f7e8cb 0%, #edd5ad 50%, #dfbe88 100%) !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  overflow: hidden !important;
  box-sizing: border-box !important;
}

.vst-404-ambient {
  position: absolute !important;
  inset: 0 !important;
  width: 100% !important;
  height: 100% !important;
  pointer-events: none !important;
  z-index: 0 !important;
}

.vst-404-bg-svg {
  width: 100% !important;
  height: 100% !important;
}

/* Stage & Card Container */
.vst-404-stage {
  position: relative !important;
  z-index: 2 !important;
  max-width: 960px !important;
  margin: 0 auto !important;
  padding-top: 30px !important;
}

/* Master Card with Authentic Vintage Rough Cut */
.vst-404-card {
  position: relative !important;
  background: linear-gradient(135deg, #fdf8ef 0%, #f7e6ce 60%, #eed5b3 100%) !important;
  border: 1.5px solid #8e622d !important;
  border-radius: 8px !important;
  padding: 56px 36px 40px !important;
  box-shadow: 
    inset 0 0 0 1.5px #caa06d,
    inset 0 1px 0 rgba(255, 255, 255, 0.8),
    0 16px 48px rgba(42, 26, 12, 0.28) !important;
  text-align: center !important;
  filter: url(#rough-button-cut-sm) !important;
  -webkit-filter: url(#rough-button-cut-sm) !important;
}

/* Top Wax Seal */
.vst-404-seal {
  position: absolute !important;
  top: -30px !important;
  left: 50% !important;
  transform: translateX(-50%) !important;
  width: 64px !important;
  height: 64px !important;
  border-radius: 50% !important;
  background: linear-gradient(135deg, #184b25 0%, #0d2f16 100%) !important;
  border: 2px solid #f6d599 !important;
  box-shadow: 
    inset 0 0 0 1.5px #8e622d,
    0 8px 20px rgba(0, 0, 0, 0.35) !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  z-index: 10 !important;
}

.vst-404-seal__inner {
  display: flex !important;
  flex-direction: column !important;
  align-items: center !important;
  justify-content: center !important;
  line-height: 1 !important;
  color: #f6d599 !important;
}

.vst-404-seal__cane { font-size: 14px !important; margin-bottom: 2px !important; }
.vst-404-seal__code { font-family: 'Cinzel', serif !important; font-size: 13px !important; font-weight: 800 !important; letter-spacing: 0.08em !important; }
.vst-404-seal__tag { font-family: 'Cinzel', serif !important; font-size: 6px !important; font-weight: 700 !important; letter-spacing: 0.14em !important; opacity: 0.85 !important; margin-top: 1px !important; }

/* Kinetic 404 Headline Group */
.vst-404-headline-group {
  display: flex !important;
  flex-direction: column !important;
  align-items: center !important;
  gap: 12px !important;
  margin-bottom: 28px !important;
}

.vst-404-digits {
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: clamp(12px, 3vw, 24px) !important;
  margin-bottom: 4px !important;
  line-height: 1 !important;
}

.vst-404-digit {
  font-family: 'Cinzel', 'Alfa Slab One', serif !important;
  font-size: clamp(76px, 12vw, 118px) !important;
  font-weight: 900 !important;
  color: #8e5f2b !important;
  text-shadow: 
    0 1px 0 #caa06d,
    0 2px 0 #8e5f2b,
    0 4px 10px rgba(42, 26, 12, 0.3) !important;
  letter-spacing: -0.02em !important;
}

.vst-404-press-stage {
  position: relative !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  width: clamp(74px, 11vw, 100px) !important;
  height: clamp(74px, 11vw, 100px) !important;
}

.vst-press-wheel {
  width: 100% !important;
  height: 100% !important;
  filter: drop-shadow(0 4px 12px rgba(0,0,0,0.35)) !important;
  animation: vst-press-spin 14s linear infinite !important;
}

@keyframes vst-press-spin {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}

@keyframes vst-press-spin-fast {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}

.vst-press-drop {
  position: absolute !important;
  bottom: -18px !important;
  left: 50% !important;
  transform: translateX(-50%) !important;
  font-size: 16px !important;
  animation: vst-drop-fall 2.2s cubic-bezier(0.4, 0, 1, 1) infinite !important;
}

@keyframes vst-drop-fall {
  0%   { opacity: 0; transform: translateX(-50%) translateY(-6px) scale(0.6); }
  40%  { opacity: 1; transform: translateX(-50%) translateY(0) scale(1.1); }
  80%  { opacity: 1; transform: translateX(-50%) translateY(14px) scale(0.9); }
  100% { opacity: 0; transform: translateX(-50%) translateY(22px) scale(0.4); }
}

.vst-404-title {
  font-family: 'Cinzel', 'Playfair Display', Georgia, serif !important;
  font-size: clamp(22px, 4.2vw, 32px) !important;
  font-weight: 800 !important;
  letter-spacing: 0.04em !important;
  color: #172b15 !important;
  margin: 4px 0 0 !important;
  text-transform: uppercase !important;
  line-height: 1.25 !important;
}

.vst-404-title em {
  color: #8e5f2b !important;
  font-style: italic !important;
  font-family: 'Dancing Script', 'Playfair Display', cursive, serif !important;
  text-transform: capitalize !important;
}

.vst-404-lead {
  font-family: var(--font-family-base, 'EB Garamond', Georgia, serif) !important;
  font-size: clamp(15px, 1.7vw, 17px) !important;
  line-height: 1.6 !important;
  color: #4a3017 !important;
  max-width: 620px !important;
  margin: 0 auto !important;
}

/* Interactive Mini-Box */
.vst-404-interactive-box {
  background: linear-gradient(135deg, #fdf8ee 0%, #f6e4cb 100%) !important;
  border: 1.5px solid #8e622d !important;
  border-radius: 6px !important;
  padding: 16px 20px !important;
  margin: 0 auto 26px !important;
  max-width: 580px !important;
  display: flex !important;
  flex-direction: column !important;
  align-items: center !important;
  gap: 12px !important;
  box-shadow: 
    inset 0 0 0 1px #caa06d,
    0 4px 14px rgba(42, 26, 12, 0.1) !important;
  filter: url(#rough-button-cut-sm) !important;
  -webkit-filter: url(#rough-button-cut-sm) !important;
}

.vst-404-interactive-box__content {
  display: flex !important;
  align-items: center !important;
  gap: 12px !important;
  text-align: left !important;
}

.vst-404-interactive-box__icon {
  font-size: 28px !important;
  flex-shrink: 0 !important;
}

.vst-404-interactive-box__text strong {
  display: block !important;
  font-family: 'Cinzel', serif !important;
  font-size: 13px !important;
  font-weight: 700 !important;
  color: #172b15 !important;
  letter-spacing: 0.06em !important;
  text-transform: uppercase !important;
}

.vst-404-interactive-box__text span {
  font-family: var(--font-family-base, 'EB Garamond', Georgia, serif) !important;
  font-size: 14px !important;
  color: #5c3c1a !important;
}

.vst-404-press-btn {
  font-size: 11px !important;
}

.vst-404-secret-perk {
  background: linear-gradient(180deg, #11381b 0%, #09200e 100%) !important;
  border: 1.5px solid #caa06d !important;
  box-shadow: inset 0 0 0 1px #8e622d, 0 8px 24px rgba(0,0,0,0.35) !important;
  border-radius: 6px !important;
  padding: 14px 18px !important;
  color: #fbf2e6 !important;
  width: 100% !important;
}

.vst-perk-badge {
  display: inline-block !important;
  font-family: 'Cinzel', serif !important;
  font-size: 11px !important;
  font-weight: 800 !important;
  letter-spacing: 0.12em !important;
  color: #f6d599 !important;
  margin-bottom: 4px !important;
}

.vst-perk-msg {
  font-family: var(--font-family-base, 'EB Garamond', serif) !important;
  font-size: 14.5px !important;
  line-height: 1.45 !important;
  color: #f3dfc8 !important;
  margin: 0 !important;
}

.vst-perk-code {
  display: inline-block !important;
  background: #2a1406 !important;
  color: #f6d599 !important;
  border: 1px solid #caa06d !important;
  padding: 2px 8px !important;
  border-radius: 4px !important;
  font-family: monospace !important;
  font-size: 13px !important;
}

/* Search Bar */
.vst-404-search-wrap {
  max-width: 540px !important;
  margin: 0 auto 32px !important;
  width: 100% !important;
}

.vst-404-search-form {
  width: 100% !important;
}

.vst-404-search-input-group {
  display: flex !important;
  align-items: center !important;
  background: #fdf8ef !important;
  border: 1.5px solid #8e622d !important;
  border-radius: 6px !important;
  box-shadow: inset 0 0 0 1px #caa06d, 0 4px 14px rgba(42, 26, 12, 0.12) !important;
  padding: 4px 6px 4px 12px !important;
}

.vst-404-search-icon {
  font-size: 15px !important;
  opacity: 0.7 !important;
  margin-right: 8px !important;
}

.vst-404-search-field {
  flex: 1 !important;
  border: none !important;
  background: transparent !important;
  font-family: var(--font-family-base, 'EB Garamond', Georgia, serif) !important;
  font-size: 15px !important;
  color: #172b15 !important;
  outline: none !important;
  padding: 6px 0 !important;
}

.vst-404-search-field::placeholder {
  color: #8e6d4c !important;
  font-style: italic !important;
  font-size: 13.5px !important;
}

.vst-404-search-submit {
  padding: 6px 16px !important;
  font-size: 10.5px !important;
}

/* Destination Navigation Grid */
.vst-404-nav-grid {
  display: grid !important;
  grid-template-columns: repeat(3, 1fr) !important;
  gap: 14px !important;
  margin-bottom: 30px !important;
}

@media (max-width: 768px) {
  .vst-404-nav-grid {
    grid-template-columns: 1fr !important;
    gap: 12px !important;
  }
}

.vst-404-dest-card {
  display: flex !important;
  flex-direction: column !important;
  align-items: center !important;
  text-align: center !important;
  padding: 16px 14px !important;
  background: linear-gradient(135deg, #fdf8ee 0%, #f6e4cb 100%) !important;
  border: 1.5px solid #8e622d !important;
  border-radius: 6px !important;
  text-decoration: none !important;
  box-shadow: inset 0 0 0 1px #caa06d, 0 3px 10px rgba(42, 26, 12, 0.08) !important;
  filter: url(#rough-button-cut-sm) !important;
  -webkit-filter: url(#rough-button-cut-sm) !important;
  transition: all 0.25s ease !important;
  box-sizing: border-box !important;
}

.vst-404-dest-card:hover {
  transform: translateY(-3px) !important;
  background: linear-gradient(135deg, #ffffff 0%, #faecd7 100%) !important;
  border-color: #caa06d !important;
  box-shadow: inset 0 0 0 1.5px #f6d599, 0 8px 20px rgba(142, 98, 45, 0.2) !important;
}

.vst-404-dest-card__icon {
  font-size: 24px !important;
  margin-bottom: 6px !important;
}

.vst-404-dest-card__title {
  font-family: 'Cinzel', serif !important;
  font-size: 12px !important;
  font-weight: 700 !important;
  letter-spacing: 0.08em !important;
  color: #172b15 !important;
  margin: 0 0 4px !important;
  text-transform: uppercase !important;
}

.vst-404-dest-card__desc {
  font-family: var(--font-family-base, 'EB Garamond', Georgia, serif) !important;
  font-size: 12.5px !important;
  line-height: 1.4 !important;
  color: #5c3c1a !important;
  margin: 0 0 8px !important;
  flex: 1 !important;
}

.vst-404-dest-card__cta {
  font-family: 'Cinzel', serif !important;
  font-size: 10px !important;
  font-weight: 700 !important;
  letter-spacing: 0.08em !important;
  color: #8e5f2b !important;
  text-transform: uppercase !important;
  margin-top: auto !important;
  transition: color 0.2s ease !important;
}

.vst-404-dest-card:hover .vst-404-dest-card__cta {
  color: #11381b !important;
}

/* Footer Proverb */
.vst-404-footer-proverb {
  border-top: 1px dashed rgba(142, 98, 45, 0.35) !important;
  padding-top: 20px !important;
  display: flex !important;
  flex-direction: column !important;
  align-items: center !important;
  gap: 4px !important;
}

.vst-proverb-ornament {
  font-size: 16px !important;
  color: #8e5f2b !important;
}

.vst-proverb-text {
  font-family: 'Dancing Script', cursive, serif !important;
  font-size: 18px !important;
  color: #3b220e !important;
  margin: 0 !important;
  line-height: 1.4 !important;
}

.vst-proverb-author {
  font-family: 'Cinzel', serif !important;
  font-size: 10px !important;
  font-weight: 700 !important;
  color: #8e5f2b !important;
  letter-spacing: 0.08em !important;
  text-transform: uppercase !important;
}
</style>

<?php get_footer(); ?>
