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
<main id="main" class="main vst-404-hero">

  <!-- 1. Ambient Botanical & Effervescent Particle Atmosphere -->
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

      <!-- Floating Cane Leaves -->
      <g class="vst-drift-leaf l1" opacity="0.25">
        <path d="M180 160 C 220 120, 270 140, 290 170 C 250 190, 200 190, 180 160 Z" fill="#3a7d4a"/>
      </g>
      <g class="vst-drift-leaf l2" opacity="0.2">
        <path d="M1180 320 C 1220 280, 1260 300, 1280 330 C 1240 350, 1200 350, 1180 320 Z" fill="#3a7d4a"/>
      </g>
      <g class="vst-drift-leaf l3" opacity="0.22">
        <path d="M720 780 C 760 740, 800 760, 820 790 C 780 810, 740 810, 720 780 Z" fill="#3a7d4a"/>
      </g>

      <!-- Effervescent Golden Dust -->
      <circle class="vst-sparkle sp1" cx="320" cy="220" r="3" fill="#d49842"/>
      <circle class="vst-sparkle sp2" cx="1060" cy="180" r="2.5" fill="#f6d599"/>
      <circle class="vst-sparkle sp3" cx="540" cy="680" r="3" fill="#d49842"/>
      <circle class="vst-sparkle sp4" cx="880" cy="720" r="2" fill="#f6d599"/>
      <circle class="vst-sparkle sp5" cx="210" cy="560" r="2.5" fill="#f6d599"/>
      <circle class="vst-sparkle sp6" cx="1220" cy="540" r="3.5" fill="#d49842"/>
    </svg>
  </div>

  <div class="container vst-404-stage">

    <!-- MASTER ORNATE PARCHMENT CARD -->
    <div class="vst-404-card frame--ornate">
      
      <!-- Top Wax Seal Stamp -->
      <div class="vst-404-seal" aria-hidden="true">
        <div class="vst-404-seal__inner">
          <span class="vst-404-seal__cane">🌾</span>
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

        <div class="vst-404-ribbon-tag">
          <span class="vst-404-ribbon-line"></span>
          <span class="vst-404-ribbon-text">LOST IN THE CANE FIELDS</span>
          <span class="vst-404-ribbon-line"></span>
        </div>

        <h1 class="vst-404-title">
          THIS GLASS SEEMS TO BE <em>Empty</em>
        </h1>

        <p class="vst-404-lead">
          The page or vintage recipe you were searching for has been moved, pressed, or simply wandered off into the sugar plantation. 
          Don’t leave thirsty — explore our fresh pours below.
        </p>
      </div>

      <!-- INTERACTIVE SUGARCANE PRESS MINI-ACTION -->
      <div class="vst-404-interactive-box" id="vst-press-game">
        <div class="vst-404-interactive-box__content">
          <span class="vst-404-interactive-box__icon">🥤</span>
          <div class="vst-404-interactive-box__text">
            <strong>Thirsty while lost?</strong>
            <span>Press the button below to extract a complimentary discount perk!</span>
          </div>
        </div>
        <button type="button" class="btn btn--order-now vst-404-press-btn" id="vst-juice-squeeze-btn">
          ⚡ PRESS FRESH JUICE
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
            <button type="submit" class="vst-404-search-submit">FIND IT</button>
          </div>
        </form>
      </div>

      <!-- POPULAR DESTINATION CARDS -->
      <div class="vst-404-nav-grid">
        
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="vst-404-dest-card frame--rough-cut">
          <span class="vst-404-dest-card__icon">🏡</span>
          <h3 class="vst-404-dest-card__title">HOME PARLOUR</h3>
          <p class="vst-404-dest-card__desc">Return to the front doors and discover our craft story.</p>
          <span class="vst-404-dest-card__cta">Explore Home →</span>
        </a>

        <a href="<?php echo esc_url( RouteService::url( 'menu' ) ?: home_url( '/#order' ) ); ?>" class="vst-404-dest-card frame--rough-cut">
          <span class="vst-404-dest-card__icon">🍹</span>
          <h3 class="vst-404-dest-card__title">OUR DRINKS</h3>
          <p class="vst-404-dest-card__desc">Explore ginger-mint, lemon zest, and cold-pressed cane juice.</p>
          <span class="vst-404-dest-card__cta">View Menu →</span>
        </a>

        <a href="<?php echo esc_url( RouteService::url( 'events' ) ?: home_url( '/events' ) ); ?>" class="vst-404-dest-card frame--rough-cut">
          <span class="vst-404-dest-card__icon">🎪</span>
          <h3 class="vst-404-dest-card__title">LIVE EVENT BAR</h3>
          <p class="vst-404-dest-card__desc">Weddings, birthdays, galas & festivals across London.</p>
          <span class="vst-404-dest-card__cta">Book Live Bar →</span>
        </a>

        <a href="<?php echo esc_url( RouteService::url( 'franchise' ) ?: home_url( '/franchise' ) ); ?>" class="vst-404-dest-card frame--rough-cut">
          <span class="vst-404-dest-card__icon">💼</span>
          <h3 class="vst-404-dest-card__title">FRANCHISE</h3>
          <p class="vst-404-dest-card__desc">Turnkey kiosk partnerships & UK wholesale opportunities.</p>
          <span class="vst-404-dest-card__cta">Partner With Us →</span>
        </a>

        <a href="<?php echo esc_url( RouteService::url( 'history' ) ?: home_url( '/history' ) ); ?>" class="vst-404-dest-card frame--rough-cut">
          <span class="vst-404-dest-card__icon">📜</span>
          <h3 class="vst-404-dest-card__title">CANE HERITAGE</h3>
          <p class="vst-404-dest-card__desc">Ancient traditions, botanical varieties, and extraction history.</p>
          <span class="vst-404-dest-card__cta">Read Chronicle →</span>
        </a>

        <a href="<?php echo esc_url( RouteService::url( 'contact' ) ?: home_url( '/contact' ) ); ?>" class="vst-404-dest-card frame--rough-cut">
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
      squeezeBtn.innerHTML = '⚙️ PRESSING IN PROGRESS...';
      
      if (pressWheel) {
        pressWheel.style.animation = 'vst-press-spin-fast 0.6s linear infinite';
      }
      
      // Floating juice splash particles
      for (var i = 0; i < 16; i++) {
        createSplashParticle(squeezeBtn);
      }

      setTimeout(function() {
        if (pressWheel) {
          pressWheel.style.animation = 'vst-press-spin 12s linear infinite';
        }
        squeezeBtn.style.display = 'none';
        perkBox.style.display = 'block';
        perkBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }, 1200);
    });
  }

  function createSplashParticle(originElem) {
    var rect = originElem.getBoundingClientRect();
    var p = document.createElement('span');
    p.textContent = Math.random() > 0.5 ? '✨' : '💧';
    p.style.position = 'fixed';
    p.style.left = (rect.left + rect.width / 2) + 'px';
    p.style.top = (rect.top + rect.height / 2) + 'px';
    p.style.pointerEvents = 'none';
    p.style.zIndex = '999999';
    p.style.fontSize = (14 + Math.random() * 16) + 'px';
    p.style.transition = 'all 0.9s cubic-bezier(0.16, 1, 0.3, 1)';
    document.body.appendChild(p);

    var angle = Math.random() * Math.PI * 2;
    var dist = 60 + Math.random() * 120;
    var targetX = Math.cos(angle) * dist;
    var targetY = Math.sin(angle) * dist - 30;

    requestAnimationFrame(function() {
      requestAnimationFrame(function() {
        p.style.transform = 'translate(' + targetX + 'px, ' + targetY + 'px) scale(0.3)';
        p.style.opacity = '0';
      });
    });

    setTimeout(function() {
      if (p.parentNode) p.parentNode.removeChild(p);
    }, 1000);
  }
});
</script>

<style>
/* ══════════════════════════════════════════════════════════════════════════
   VINTAGESOUL - MASTERPIECE 404 PAGE STYLING
   ══════════════════════════════════════════════════════════════════════════ */
.vst-404-hero {
  position: relative;
  min-height: 94vh;
  padding: clamp(90px, 11vw, 140px) 20px clamp(70px, 8vw, 100px);
  background: 
    radial-gradient(ellipse at 50% 10%, rgba(246, 213, 153, 0.25) 0%, transparent 60%),
    linear-gradient(165deg, #fdf8f0 0%, #f4e4cf 35%, #ebcfad 75%, #e1c094 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  box-sizing: border-box;
}

/* ─── Ambient SVG Layer ─── */
.vst-404-ambient {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 0;
}

.vst-404-bg-svg {
  width: 100%;
  height: 100%;
}

@keyframes vst-sway {
  0%, 100% { transform: rotate(0deg); transform-origin: bottom center; }
  33%       { transform: rotate(1.8deg); transform-origin: bottom center; }
  66%       { transform: rotate(-1.4deg); transform-origin: bottom center; }
}

@keyframes vst-drift {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  50%       { transform: translateY(-16px) rotate(6deg); }
}

@keyframes vst-spark {
  0%, 100% { transform: scale(1) translateY(0); opacity: 0.25; }
  50%       { transform: scale(1.6) translateY(-14px); opacity: 0.65; }
}

.vst-sway-stalk--1 { animation: vst-sway 8s ease-in-out infinite; }
.vst-sway-stalk--2 { animation: vst-sway 10s ease-in-out infinite reverse; }
.l1 { animation: vst-drift 6s ease-in-out infinite; }
.l2 { animation: vst-drift 7.5s ease-in-out infinite 1s; }
.l3 { animation: vst-drift 8.5s ease-in-out infinite 2s; }
.sp1 { animation: vst-spark 4s ease-in-out infinite; }
.sp2 { animation: vst-spark 5.5s ease-in-out infinite 0.8s; }
.sp3 { animation: vst-spark 4.2s ease-in-out infinite 1.5s; }
.sp4 { animation: vst-spark 6s ease-in-out infinite 2s; }
.sp5 { animation: vst-spark 5s ease-in-out infinite 1.2s; }
.sp6 { animation: vst-spark 4.8s ease-in-out infinite 0.3s; }

/* ─── Stage & Card Container ─── */
.vst-404-stage {
  position: relative;
  z-index: 2;
  max-width: 960px;
  margin: 0 auto;
  padding-top: 20px;
}

.vst-404-card {
  position: relative;
  margin-top: 40px;
  background: 
    url("assets/images/textures/paper/parchment-subtle.png"),
    linear-gradient(175deg, #fdf6ec 0%, #faecd7 50%, #f3dec2 100%) !important;
  border: 2px solid #8e622d !important;
  border-radius: 12px;
  padding: 64px 38px 48px;
  box-shadow: 
    inset 0 0 0 2px #caa06d,
    inset 0 0 30px rgba(142, 98, 45, 0.12),
    0 24px 60px rgba(42, 26, 12, 0.22) !important;
  text-align: center;
}

/* ─── Top Wax Seal ─── */
.vst-404-seal {
  position: absolute;
  top: -34px;
  left: 50%;
  transform: translateX(-50%);
  width: 68px;
  height: 68px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1f572c 0%, #0e3017 100%);
  border: 2px solid #f6d599;
  box-shadow: 
    inset 0 0 0 1.5px #8e622d,
    0 8px 20px rgba(0, 0, 0, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
}

.vst-404-seal__inner {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  line-height: 1;
  color: #f6d599;
}

.vst-404-seal__cane { font-size: 14px; margin-bottom: 2px; }
.vst-404-seal__code { font-family: 'Cinzel', serif; font-size: 13px; font-weight: 800; letter-spacing: 0.08em; }
.vst-404-seal__tag { font-family: 'Cinzel', serif; font-size: 6px; font-weight: 700; letter-spacing: 0.14em; opacity: 0.85; margin-top: 1px; }

/* ─── Kinetic 404 Headline Group ─── */
.vst-404-headline-group {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding-top: 12px;
  margin-bottom: 32px;
}

.vst-404-digits {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: clamp(12px, 3vw, 22px);
  margin-bottom: 6px;
  line-height: 1;
}

.vst-404-digit {
  font-family: 'Cinzel', 'Playfair Display', Georgia, serif;
  font-size: clamp(80px, 14vw, 130px);
  font-weight: 900;
  color: transparent;
  background: linear-gradient(180deg, #d49842 0%, #8e622d 55%, #42270f 100%);
  -webkit-background-clip: text;
  background-clip: text;
  letter-spacing: -0.04em;
  filter: drop-shadow(0 6px 14px rgba(142, 98, 45, 0.35));
}

.vst-404-press-stage {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: clamp(80px, 13vw, 110px);
  height: clamp(80px, 13vw, 110px);
}

.vst-press-wheel {
  width: 100%;
  height: 100%;
  filter: drop-shadow(0 6px 14px rgba(0,0,0,0.35));
  animation: vst-press-spin 14s linear infinite;
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
  position: absolute;
  bottom: -18px;
  left: 50%;
  transform: translateX(-50%);
  font-size: 16px;
  animation: vst-drop-fall 2.2s cubic-bezier(0.4, 0, 1, 1) infinite;
}

@keyframes vst-drop-fall {
  0%   { opacity: 0; transform: translateX(-50%) translateY(-6px) scale(0.6); }
  40%  { opacity: 1; transform: translateX(-50%) translateY(0) scale(1.1); }
  80%  { opacity: 1; transform: translateX(-50%) translateY(14px) scale(0.9); }
  100% { opacity: 0; transform: translateX(-50%) translateY(22px) scale(0.4); }
}

/* Ribbon Tag */
.vst-404-ribbon-tag {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  margin-top: 4px;
}

.vst-404-ribbon-line {
  display: inline-block;
  width: 40px;
  height: 1px;
  background: linear-gradient(90deg, transparent, #8e622d);
}
.vst-404-ribbon-line:last-child {
  background: linear-gradient(90deg, #8e622d, transparent);
}

.vst-404-ribbon-text {
  font-family: 'Cinzel', serif;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.22em;
  color: #8e5f2b;
  text-transform: uppercase;
}

.vst-404-title {
  font-family: 'Cinzel', 'Playfair Display', Georgia, serif;
  font-size: clamp(22px, 4.5vw, 34px);
  font-weight: 800;
  letter-spacing: 0.05em;
  color: #172b15;
  margin: 4px 0 0;
  text-transform: uppercase;
  line-height: 1.2;
}

.vst-404-title em {
  color: #8e5f2b;
  font-style: italic;
  font-family: 'Playfair Display', Georgia, serif;
}

.vst-404-lead {
  font-family: 'EB Garamond', Georgia, serif;
  font-size: clamp(15.5px, 1.8vw, 18px);
  line-height: 1.65;
  color: #533618;
  max-width: 640px;
  margin: 0 auto;
}

/* ─── Interactive Squeeze Mini-Box ─── */
.vst-404-interactive-box {
  background: linear-gradient(135deg, #f7ebd7 0%, #edd8bc 100%);
  border: 1.5px dashed #8e622d;
  border-radius: 8px;
  padding: 16px 22px;
  margin: 0 auto 28px;
  max-width: 620px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  box-shadow: inset 0 0 12px rgba(142, 98, 45, 0.1);
}

.vst-404-interactive-box__content {
  display: flex;
  align-items: center;
  gap: 12px;
  text-align: left;
}

.vst-404-interactive-box__icon {
  font-size: 32px;
  flex-shrink: 0;
}

.vst-404-interactive-box__text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.vst-404-interactive-box__text strong {
  font-family: 'Cinzel', serif;
  font-size: 13px;
  color: #172b15;
  letter-spacing: 0.04em;
}

.vst-404-interactive-box__text span {
  font-family: 'EB Garamond', Georgia, serif;
  font-size: 14px;
  color: #5c3c1a;
}

.vst-404-press-btn {
  padding: 10px 24px !important;
  font-size: 12px !important;
  letter-spacing: 0.1em !important;
  cursor: pointer;
}

.vst-404-secret-perk {
  background: linear-gradient(180deg, #11381b 0%, #09200e 100%);
  border: 1.5px solid #caa06d;
  box-shadow: inset 0 0 0 1px #8e622d, 0 8px 24px rgba(0,0,0,0.35);
  border-radius: 6px;
  padding: 16px 20px;
  color: #fbf2e6;
  width: 100%;
  animation: vst-fade-in 0.4s ease forwards;
}

@keyframes vst-fade-in {
  from { opacity: 0; transform: translateY(8px); }
  to   { opacity: 1; transform: translateY(0); }
}

.vst-perk-badge {
  display: inline-block;
  font-family: 'Cinzel', serif;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.12em;
  color: #f6d599;
  margin-bottom: 6px;
}

.vst-perk-msg {
  font-family: 'EB Garamond', Georgia, serif;
  font-size: 15px;
  line-height: 1.5;
  color: #f3dfc8;
  margin: 0;
}

.vst-perk-code {
  display: inline-block;
  background: #2a1406;
  color: #f6d599;
  border: 1px solid #caa06d;
  padding: 2px 10px;
  border-radius: 4px;
  font-family: monospace;
  font-size: 14px;
  letter-spacing: 0.08em;
}

/* ─── Search Bar ─── */
.vst-404-search-wrap {
  max-width: 580px;
  margin: 0 auto 36px;
  width: 100%;
}

.vst-404-search-form {
  width: 100%;
}

.vst-404-search-input-group {
  display: flex;
  align-items: center;
  background: #ffffff;
  border: 2px solid #8e622d;
  border-radius: 6px;
  box-shadow: inset 0 0 0 1px #caa06d, 0 4px 16px rgba(42, 26, 12, 0.12);
  overflow: hidden;
  padding: 4px 6px 4px 14px;
  transition: border-color 0.25s ease, box-shadow 0.25s ease;
}

.vst-404-search-input-group:focus-within {
  border-color: #d49842;
  box-shadow: inset 0 0 0 1px #d49842, 0 6px 20px rgba(212, 152, 66, 0.25);
}

.vst-404-search-icon {
  font-size: 16px;
  opacity: 0.6;
  margin-right: 10px;
}

.vst-404-search-field {
  flex: 1;
  border: none;
  background: transparent;
  font-family: 'EB Garamond', Georgia, serif;
  font-size: 16px;
  color: #172b15;
  outline: none;
  padding: 8px 0;
}

.vst-404-search-field::placeholder {
  color: #8e6d4c;
  font-style: italic;
  font-size: 14px;
}

.vst-404-search-submit {
  background: linear-gradient(180deg, #184b25 0%, #0d2f16 100%);
  color: #f6d599;
  border: 1.5px solid #8e622d;
  border-radius: 4px;
  padding: 8px 18px;
  font-family: 'Cinzel', serif;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: inset 0 0 0 1px #caa06d;
}

.vst-404-search-submit:hover {
  background: linear-gradient(180deg, #1f5d2f 0%, #123d1e 100%);
  border-color: #f6d599;
  color: #ffffff;
  transform: translateY(-1px);
}

/* ─── Destination Navigation Grid ─── */
.vst-404-nav-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 36px;
}

.vst-404-dest-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 20px 16px;
  background: linear-gradient(135deg, #fdf8ee 0%, #f6e6ce 100%);
  border: 1.5px solid #8e622d;
  border-radius: 6px;
  text-decoration: none;
  box-shadow: inset 0 0 0 1px #caa06d, 0 4px 14px rgba(42, 26, 12, 0.1);
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  box-sizing: border-box;
}

.vst-404-dest-card:hover {
  transform: translateY(-4px);
  background: linear-gradient(135deg, #ffffff 0%, #fbf1e2 100%);
  border-color: #d49842;
  box-shadow: inset 0 0 0 1.5px #d49842, 0 10px 24px rgba(142, 98, 45, 0.22);
}

.vst-404-dest-card__icon {
  font-size: 28px;
  margin-bottom: 8px;
  filter: drop-shadow(0 2px 4px rgba(42, 26, 12, 0.15));
}

.vst-404-dest-card__title {
  font-family: 'Cinzel', serif;
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.08em;
  color: #172b15;
  margin: 0 0 6px;
  text-transform: uppercase;
}

.vst-404-dest-card__desc {
  font-family: 'EB Garamond', Georgia, serif;
  font-size: 13px;
  line-height: 1.45;
  color: #5c3c1a;
  margin: 0 0 10px;
  flex: 1;
}

.vst-404-dest-card__cta {
  font-family: 'Cinzel', serif;
  font-size: 10.5px;
  font-weight: 700;
  letter-spacing: 0.08em;
  color: #8e5f2b;
  text-transform: uppercase;
  margin-top: auto;
  transition: color 0.2s ease;
}

.vst-404-dest-card:hover .vst-404-dest-card__cta {
  color: #11381b;
}

/* ─── Footer Proverb ─── */
.vst-404-footer-proverb {
  border-top: 1px solid #8e622d;
  padding-top: 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}

.vst-proverb-ornament {
  font-size: 18px;
  color: #8e5f2b;
}

.vst-proverb-text {
  font-family: 'Dancing Script', cursive, serif;
  font-size: clamp(16px, 2.2vw, 20px);
  color: #5c3c1a;
  font-style: italic;
  margin: 0;
  max-width: 580px;
}

.vst-proverb-author {
  font-family: 'Cinzel', serif;
  font-size: 9.5px;
  letter-spacing: 0.14em;
  color: #8e5f2b;
  text-transform: uppercase;
}

/* ─── Responsive Adjustments ─── */
@media (max-width: 860px) {
  .vst-404-nav-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 560px) {
  .vst-404-card {
    padding: 48px 18px 32px;
  }
  .vst-404-nav-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  .vst-404-interactive-box__content {
    flex-direction: column;
    text-align: center;
  }
  .vst-404-search-input-group {
    flex-direction: column;
    gap: 8px;
    padding: 10px;
  }
  .vst-404-search-submit {
    width: 100%;
  }
}
</style>

<?php get_footer(); ?>
