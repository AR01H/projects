<?php
/* ═══════════════════════════════════════════
   ADVAITH HOMES — COMING SOON PAGE
   Edit values here
   ═══════════════════════════════════════════ */

$site_name = defined('AH_BRAND_NAME')    ? AH_BRAND_NAME    : 'Advaith Homes';
$tagline   = defined('AH_BRAND_TAGLINE') ? AH_BRAND_TAGLINE : 'Your trusted partner for home buying in the UK';
$logo_url  = function_exists('get_template_directory_uri')
               ? get_template_directory_uri() . '/assets/images/logo.png'
               : '/assets/images/logo.png';
$phone     = defined('CONTACT_NUMBER') ? CONTACT_NUMBER : '+44 1234 567 890';
$email     = defined('CONTACT_EMAIL')  ? CONTACT_EMAIL  : 'hello@advaithhomes.co.uk';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($site_name); ?> – Coming Soon</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<style>
:root{
  --g200:#ffe68a;--g300:#ffd54f;--g400:#f7c62f;
  --g500:#eab308;--g600:#d89b00;--g700:#b7791f;
  --bg:#070601;--card:rgba(255,242,191,0.05);--card-h:rgba(255,242,191,0.09);
  --border:rgba(247,198,47,0.13);--border-h:rgba(247,198,47,0.3);
  --text:#fff9e6;--muted:rgba(255,242,191,0.46);
  --ff:'Playfair Display',Georgia,serif;--fb:'DM Sans',sans-serif;
  --r:16px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{background:var(--bg);color:var(--text);font-family:var(--fb);font-weight:300;min-height:100vh;overflow-x:hidden;-webkit-font-smoothing:antialiased}

/* ── BG SVG ── */
.bg-svg{position:fixed;inset:0;width:100%;height:100%;z-index:0;pointer-events:none}

/* ── KEYFRAMES ── */
@keyframes rise{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
@keyframes shimmer{0%,100%{opacity:1}50%{opacity:.68}}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-9px)}}
@keyframes drift{0%,100%{transform:translate(0,0)}50%{transform:translate(8px,-7px)}}
@keyframes drift2{0%,100%{transform:translate(0,0)}50%{transform:translate(-7px,9px)}}
@keyframes pulse{0%,100%{opacity:1;r:6}50%{opacity:.3;r:10}}
@keyframes scan{from{transform:translateY(-4px)}to{transform:translateY(100vh)}}
@keyframes spin{from{transform:rotate(0)}to{transform:rotate(360deg)}}
@keyframes breathe{0%,100%{box-shadow:0 0 0 0 rgba(234,179,8,0)}50%{box-shadow:0 0 20px 4px rgba(234,179,8,0.13)}}
@keyframes bar{from{transform:scaleY(0);transform-origin:bottom}to{transform:scaleY(1);transform-origin:bottom}}

/* ── PAGE ── */
.page{position:relative;z-index:2;max-width:820px;margin:0 auto;padding:2.5rem 1.2rem 5rem}

/* ── HEADER ── */
.top{display:flex;flex-direction:column;align-items:center;text-align:center;padding-bottom:2rem;border-bottom:1px solid var(--border);margin-bottom:2.5rem;animation:rise .65s ease both}
.logo-img{height:40vh;width:auto;max-width:280px;object-fit:contain;animation:shimmer 3.5s ease-in-out infinite}
.logo-fb{display:none;width:62px;height:62px;background:var(--g500);border-radius:14px;align-items:center;justify-content:center;font-family:var(--ff);font-size:1.8rem;font-weight:900;color:#3a1f00;animation:float 4s ease-in-out infinite}
.brand{font-family:var(--ff);font-size:1.65rem;font-weight:900;color:var(--g300);margin-top:.8rem;letter-spacing:.02em}
.tagline{font-size:12px;color:var(--muted);letter-spacing:.07em;font-style:italic;margin-top:.25rem}
.tag{margin-top:.75rem;display:inline-flex;align-items:center;gap:5px;background:rgba(234,179,8,0.09);border:1px solid rgba(234,179,8,0.22);border-radius:50px;padding:4px 13px;font-size:10px;font-weight:500;letter-spacing:.13em;text-transform:uppercase;color:var(--g400)}

/* ── CONTACT ── */
.lbl{font-size:10px;font-weight:500;letter-spacing:.2em;text-transform:uppercase;color:var(--g500);opacity:.82;margin-bottom:.4rem}
.h2{font-family:var(--ff);font-size:clamp(1.25rem,3vw,1.8rem);font-weight:700;color:var(--text);line-height:1.15;margin-bottom:1rem}
.h2 em{color:var(--g400);font-style:normal}

.pills{display:flex;flex-wrap:wrap;gap:.65rem;margin-bottom:2.5rem}
.pill{flex:1 1 160px;display:flex;align-items:center;gap:10px;background:var(--card);border:1px solid var(--border);border-radius:12px;padding:.85rem 1rem;text-decoration:none;color:var(--text);transition:background .2s,border-color .2s,transform .18s}
.pill:hover{background:var(--card-h);border-color:var(--border-h);transform:translateY(-2px)}
.pill-ico{width:34px;height:34px;background:rgba(234,179,8,0.11);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0}
.pill-l{font-size:9px;font-weight:500;letter-spacing:.13em;text-transform:uppercase;color:var(--g500);margin-bottom:2px}
.pill-v{font-size:13px;font-weight:500}

.rule{border:none;height:1px;background:linear-gradient(90deg,transparent,var(--border) 40%,var(--border) 60%,transparent);margin:0 0 2.5rem}

/* ── COMING SOON CARD ── */
.cs{border:1px solid var(--border);border-radius:22px;overflow:hidden;background:rgba(10,9,2,0.6);margin-bottom:2.8rem;animation:breathe 3.5s ease-in-out infinite}
.cs-scene{display:block;width:100%;height:220px}
@media(max-width:500px){.cs-scene{height:160px}}

.cs-body{padding:1.8rem 1.5rem 1.6rem;text-align:center}
@media(max-width:500px){.cs-body{padding:1.3rem 1.1rem 1.3rem}}
.cs-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(234,179,8,0.1);border:1px solid rgba(234,179,8,0.28);border-radius:50px;padding:4px 14px;font-size:10.5px;font-weight:500;letter-spacing:.16em;text-transform:uppercase;color:var(--g400);margin-bottom:1.2rem}
.cs-dot{width:6px;height:6px;border-radius:50%;background:var(--g400);animation:pulse 2s ease-in-out infinite;flex-shrink:0}
.cs-title{font-family:var(--ff);font-size:clamp(1.7rem,5vw,2.8rem);font-weight:900;line-height:1.08;margin-bottom:.7rem}
.cs-title em{color:var(--g400);font-style:italic}
.cs-desc{font-size:14px;color:var(--muted);line-height:1.9;max-width:480px;margin:0 auto}

.cs-foot{border-top:1px solid var(--border);padding:.8rem 1.5rem;display:flex;align-items:center;justify-content:center;gap:.5rem;font-size:12px;color:var(--muted);background:rgba(255,242,191,0.02)}
.cs-foot strong{color:var(--g500);font-weight:500}

/* ── GUIDES ── */
.guides-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:.8rem}
@media(max-width:520px){.guides-grid{grid-template-columns:1fr}}

.gcard{background:var(--card);border:1px solid var(--border);border-radius:var(--r);padding:1.3rem 1.2rem;transition:background .2s,border-color .2s,transform .2s;opacity:0;transform:translateY(14px);position:relative;overflow:hidden}
.gcard.vis{opacity:1;transform:none;transition:opacity .5s ease,transform .5s ease,background .2s,border-color .2s}
.gcard:hover{background:var(--card-h);border-color:var(--border-h);transform:translateY(-3px)}
.gcard::after{content:'';position:absolute;bottom:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--g600),transparent);opacity:0;transition:opacity .22s}
.gcard:hover::after{opacity:1}
.g-ico{font-size:1.7rem;margin-bottom:.7rem;display:block}
.g-cat{font-size:9.5px;font-weight:500;letter-spacing:.16em;text-transform:uppercase;color:var(--g600);margin-bottom:.38rem}
.g-title{font-family:var(--ff);font-size:.93rem;font-weight:700;color:var(--text);line-height:1.32;margin-bottom:.45rem}
.g-desc{font-size:12px;color:var(--muted);line-height:1.78;margin-bottom:.75rem}
.g-meta{font-size:10.5px;color:var(--g700)}

footer{text-align:center;margin-top:2.5rem;font-size:11.5px;color:var(--muted)}
footer em{color:var(--g500);font-style:normal}
</style>
</head>
<body>

<!-- ═══ FULL PAGE SVG BACKGROUND ═══ -->
<svg class="bg-svg" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <defs>
    <radialGradient id="ga" cx="25%" cy="15%" r="55%">
      <stop offset="0%" stop-color="#b7791f" stop-opacity=".11"/>
      <stop offset="100%" stop-color="#070601" stop-opacity="0"/>
    </radialGradient>
    <radialGradient id="gb" cx="80%" cy="80%" r="50%">
      <stop offset="0%" stop-color="#eab308" stop-opacity=".07"/>
      <stop offset="100%" stop-color="#070601" stop-opacity="0"/>
    </radialGradient>
    <pattern id="pg" x="0" y="0" width="72" height="72" patternUnits="userSpaceOnUse">
      <path d="M72 0L0 0 0 72" fill="none" stroke="#f7c62f" stroke-width=".35" opacity=".09"/>
    </pattern>
    <pattern id="pd" x="0" y="0" width="36" height="36" patternUnits="userSpaceOnUse">
      <circle cx="1" cy="1" r=".9" fill="#f7c62f" opacity=".11"/>
    </pattern>
  </defs>

  <rect width="1440" height="900" fill="#070601"/>
  <rect width="1440" height="900" fill="url(#pg)"/>
  <rect width="1440" height="900" fill="url(#pd)"/>
  <rect width="1440" height="900" fill="url(#ga)"/>
  <rect width="1440" height="900" fill="url(#gb)"/>

  <!-- LARGE HOUSE — left -->
  <g opacity=".065" style="animation:drift 20s ease-in-out infinite">
    <polygon points="100,560 280,370 460,560" fill="none" stroke="#f7c62f" stroke-width="1.5"/>
    <rect x="138" y="560" width="240" height="170" fill="none" stroke="#f7c62f" stroke-width="1.3"/>
    <rect x="228" y="610" width="40" height="60" rx="3" fill="none" stroke="#f7c62f" stroke-width="1"/>
    <rect x="150" y="582" width="44" height="38" rx="2" fill="none" stroke="#f7c62f" stroke-width=".9"/>
    <rect x="322" y="582" width="44" height="38" rx="2" fill="none" stroke="#f7c62f" stroke-width=".9"/>
    <rect x="268" y="378" width="14" height="38" fill="none" stroke="#f7c62f" stroke-width=".9"/>
  </g>

  <!-- LARGE HOUSE — right -->
  <g opacity=".055" style="animation:drift2 24s ease-in-out infinite">
    <polygon points="980,530 1160,345 1340,530" fill="none" stroke="#eab308" stroke-width="1.3"/>
    <rect x="1018" y="530" width="228" height="160" fill="none" stroke="#eab308" stroke-width="1.1"/>
    <rect x="1106" y="580" width="38" height="55" rx="3" fill="none" stroke="#eab308" stroke-width="1"/>
    <rect x="1032" y="554" width="40" height="34" rx="2" fill="none" stroke="#eab308" stroke-width=".8"/>
    <rect x="1192" y="554" width="40" height="34" rx="2" fill="none" stroke="#eab308" stroke-width=".8"/>
  </g>

  <!-- SKYLINE bottom -->
  <g opacity=".055" transform="translate(0,640)">
    <rect x="30"   y="40"  width="50"  height="220" fill="none" stroke="#f7c62f" stroke-width=".7"/>
    <rect x="92"   y="80"  width="65"  height="180" fill="none" stroke="#f7c62f" stroke-width=".7"/>
    <rect x="168"  y="18"  width="42"  height="242" fill="none" stroke="#eab308" stroke-width=".7"/>
    <line x1="189" y1="0" x2="189" y2="18" stroke="#eab308" stroke-width=".7" opacity=".6"/>
    <rect x="221"  y="65"  width="58"  height="195" fill="none" stroke="#f7c62f" stroke-width=".7"/>
    <rect x="290"  y="100" width="44"  height="160" fill="none" stroke="#f7c62f" stroke-width=".7"/>
    <rect x="345"  y="28"  width="72"  height="232" fill="none" stroke="#eab308" stroke-width=".7"/>
    <rect x="428"  y="72"  width="50"  height="188" fill="none" stroke="#f7c62f" stroke-width=".7"/>
    <rect x="490"  y="22"  width="38"  height="238" fill="none" stroke="#eab308" stroke-width=".7"/>
    <line x1="509" y1="4"  x2="509" y2="22" stroke="#eab308" stroke-width=".7" opacity=".6"/>
    <rect x="540"  y="78"  width="60"  height="182" fill="none" stroke="#f7c62f" stroke-width=".7"/>
    <rect x="612"  y="104" width="46"  height="156" fill="none" stroke="#f7c62f" stroke-width=".7"/>
    <rect x="670"  y="44"  width="54"  height="216" fill="none" stroke="#eab308" stroke-width=".7"/>
    <rect x="736"  y="88"  width="64"  height="172" fill="none" stroke="#f7c62f" stroke-width=".7"/>
    <rect x="812"  y="34"  width="44"  height="226" fill="none" stroke="#eab308" stroke-width=".7"/>
    <rect x="868"  y="96"  width="55"  height="164" fill="none" stroke="#f7c62f" stroke-width=".7"/>
    <rect x="935"  y="68"  width="48"  height="192" fill="none" stroke="#f7c62f" stroke-width=".7"/>
    <rect x="996"  y="112" width="60"  height="148" fill="none" stroke="#eab308" stroke-width=".7"/>
    <rect x="1068" y="48"  width="52"  height="212" fill="none" stroke="#f7c62f" stroke-width=".7"/>
    <rect x="1132" y="82"  width="70"  height="178" fill="none" stroke="#eab308" stroke-width=".7"/>
    <rect x="1214" y="110" width="44"  height="150" fill="none" stroke="#f7c62f" stroke-width=".7"/>
    <rect x="1270" y="58"  width="56"  height="202" fill="none" stroke="#eab308" stroke-width=".7"/>
    <rect x="1338" y="88"  width="70"  height="172" fill="none" stroke="#f7c62f" stroke-width=".7"/>
  </g>

  <!-- ORBIT RING top -->
  <ellipse cx="720" cy="-60" rx="440" ry="230" fill="none" stroke="#f7c62f" stroke-width=".55" opacity=".055" style="animation:spin 70s linear infinite;transform-origin:720px -60px"/>

  <!-- CIRCLE accent top-right -->
  <circle cx="1340" cy="110" r="95"  fill="none" stroke="#eab308" stroke-width=".65" opacity=".06"/>
  <circle cx="1340" cy="110" r="62"  fill="none" stroke="#eab308" stroke-width=".4"  opacity=".04"/>

  <!-- DIAGONAL accents -->
  <line x1="0"    y1="0"   x2="260"  y2="900" stroke="#f7c62f" stroke-width=".4" opacity=".045"/>
  <line x1="1440" y1="0"   x2="1180" y2="900" stroke="#eab308" stroke-width=".4" opacity=".045"/>

  <!-- PULSING DOTS / map pins -->
  <circle cx="600" cy="190" r="4.5" fill="#f7c62f" opacity=".09" style="animation:pulse 4s ease-in-out infinite"/>
  <circle cx="850" cy="320" r="3.5" fill="#eab308" opacity=".07" style="animation:pulse 5s ease-in-out infinite 1s"/>
  <circle cx="380" cy="400" r="3"   fill="#f7c62f" opacity=".07" style="animation:pulse 6s ease-in-out infinite .4s"/>
  <circle cx="1080" cy="260" r="4"  fill="#eab308" opacity=".07" style="animation:pulse 4.5s ease-in-out infinite 2s"/>

  <!-- SCAN LINE -->
  <rect x="0" y="0" width="1440" height="1.5" fill="#f7c62f" opacity=".09" style="animation:scan 9s linear infinite"/>
</svg>

<!-- ═══ PAGE ═══ -->
<div class="page">

  <!-- HEADER -->
  <header class="top">
    <img class="logo-img"
         src="<?php echo htmlspecialchars($logo_url); ?>"
         alt="<?php echo htmlspecialchars($site_name); ?>"
         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
    <div class="logo-fb">A</div>
    <div class="brand"><?php echo htmlspecialchars($site_name); ?></div>
    <div class="tagline"><?php echo htmlspecialchars($tagline); ?></div>
    <div class="tag">✦ Helping People Find Their Home in the UK</div>
  </header>

  <!-- CONTACT -->
  <section style="animation:rise .7s ease .08s both">
    <p class="lbl">Contact Us</p>
    <div class="h2">Get in touch &amp; <em>speak to our team</em></div>
    <div class="pills">
      <a href="tel:<?php echo preg_replace('/\s+/','',$phone); ?>" class="pill">
        <div class="pill-ico">📞</div>
        <div>
          <div class="pill-l">Call Us</div>
          <div class="pill-v"><?php echo htmlspecialchars($phone); ?></div>
        </div>
      </a>
      <a href="mailto:<?php echo htmlspecialchars($email); ?>" class="pill">
        <div class="pill-ico">✉️</div>
        <div>
          <div class="pill-l">Email Us</div>
          <div class="pill-v"><?php echo htmlspecialchars($email); ?></div>
        </div>
      </a>
    </div>
  </section>

  <hr class="rule">

  <!-- COMING SOON CARD -->
  <section style="animation:rise .75s ease .15s both">
    <div class="cs">

      <!-- ANIMATED SCENE SVG -->
      <svg class="cs-scene" viewBox="0 0 820 220" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <defs>
          <radialGradient id="csg" cx="50%" cy="0%" r="80%">
            <stop offset="0%" stop-color="#f7c62f" stop-opacity=".1"/>
            <stop offset="100%" stop-color="#080601" stop-opacity="0"/>
          </radialGradient>
        </defs>
        <rect width="820" height="220" fill="url(#csg)"/>
        <line x1="0" y1="188" x2="820" y2="188" stroke="#f7c62f" stroke-width=".6" opacity=".15"/>

        <!-- MAIN HOUSE -->
        <g style="animation:drift 13s ease-in-out infinite">
          <polygon points="285,108 410,42 535,108" fill="#f7c62f" fill-opacity=".035" stroke="#f7c62f" stroke-width="1.3" stroke-opacity=".5"/>
          <rect x="305" y="108" width="210" height="80" fill="none" stroke="#f7c62f" stroke-width="1.1" stroke-opacity=".45"/>
          <!-- door -->
          <rect x="393" y="144" width="38" height="44" rx="3" fill="none" stroke="#f7c62f" stroke-width=".9" stroke-opacity=".48"/>
          <circle cx="427" cy="168" r="2" fill="#f7c62f" opacity=".45"/>
          <!-- windows -->
          <rect x="320" y="124" width="32" height="28" rx="2" fill="#f7c62f" fill-opacity=".06" stroke="#f7c62f" stroke-width=".8" stroke-opacity=".38" style="animation:shimmer 2.8s ease-in-out infinite"/>
          <rect x="468" y="124" width="32" height="28" rx="2" fill="#f7c62f" fill-opacity=".04" stroke="#f7c62f" stroke-width=".8" stroke-opacity=".38" style="animation:shimmer 2.8s ease-in-out infinite .9s"/>
          <!-- chimney -->
          <rect x="458" y="50" width="14" height="28" fill="none" stroke="#f7c62f" stroke-width=".8" stroke-opacity=".32"/>
        </g>

        <!-- SMALL HOUSE LEFT -->
        <g opacity=".35" style="animation:drift2 16s ease-in-out infinite">
          <polygon points="55,170 120,120 185,170" fill="none" stroke="#eab308" stroke-width="1"/>
          <rect x="70" y="170" width="110" height="18" fill="none" stroke="#eab308" stroke-width=".9"/>
          <rect x="100" y="152" width="20" height="18" rx="1" fill="none" stroke="#eab308" stroke-width=".7"/>
          <rect x="77"  y="154" width="14" height="12" rx="1" fill="none" stroke="#eab308" stroke-width=".6"/>
          <rect x="149" y="154" width="14" height="12" rx="1" fill="none" stroke="#eab308" stroke-width=".6"/>
        </g>

        <!-- SMALL HOUSE RIGHT -->
        <g opacity=".3" style="animation:drift 18s ease-in-out infinite .5s">
          <polygon points="635,170 698,122 762,170" fill="none" stroke="#eab308" stroke-width="1"/>
          <rect x="650" y="170" width="105" height="18" fill="none" stroke="#eab308" stroke-width=".9"/>
          <rect x="679" y="152" width="20" height="18" rx="1" fill="none" stroke="#eab308" stroke-width=".7"/>
          <rect x="657" y="154" width="14" height="12" rx="1" fill="none" stroke="#eab308" stroke-width=".6"/>
          <rect x="727" y="154" width="14" height="12" rx="1" fill="none" stroke="#eab308" stroke-width=".6"/>
        </g>

        <!-- MAP PIN -->
        <g style="animation:float 3.8s ease-in-out infinite" opacity=".5">
          <circle cx="736" cy="50" r="17" fill="none" stroke="#f7c62f" stroke-width="1.1"/>
          <circle cx="736" cy="46" r="6"  fill="#f7c62f" opacity=".55"/>
          <line x1="736" y1="67" x2="736" y2="78" stroke="#f7c62f" stroke-width="1.2"/>
        </g>

        <!-- POUND CIRCLE -->
        <g style="animation:float 4.4s ease-in-out infinite .7s" opacity=".38">
          <circle cx="84" cy="60" r="22" fill="none" stroke="#eab308" stroke-width=".9"/>
          <text x="84" y="68" text-anchor="middle" font-size="20" fill="#eab308" font-family="Georgia,serif" font-weight="bold">£</text>
        </g>

        <!-- BAR CHART -->
        <g transform="translate(690,110)" opacity=".35">
          <rect x="0"  y="46" width="13" height="32" rx="2" fill="none" stroke="#f7c62f" stroke-width=".8" style="animation:bar 2s ease both .2s"/>
          <rect x="18" y="26" width="13" height="52" rx="2" fill="none" stroke="#f7c62f" stroke-width=".8" style="animation:bar 2s ease both .35s"/>
          <rect x="36" y="8"  width="13" height="70" rx="2" fill="none" stroke="#f7c62f" stroke-width=".8" style="animation:bar 2s ease both .5s"/>
          <rect x="54" y="32" width="13" height="46" rx="2" fill="none" stroke="#eab308" stroke-width=".8" style="animation:bar 2s ease both .65s"/>
          <line x1="-3" y1="78" x2="74" y2="78" stroke="#f7c62f" stroke-width=".6" opacity=".45"/>
        </g>

        <rect x="0" y="0" width="820" height="1.2" fill="#f7c62f" opacity=".1" style="animation:scan 7s linear infinite"/>
      </svg>

      <!-- BODY -->
      <div class="cs-body">
        <div class="cs-badge"><span class="cs-dot"></span> Coming Soon</div>
        <h2 class="cs-title">Your UK Home Buying<br><em>Platform is on its Way</em></h2>
        <p class="cs-desc">Search properties, compare mortgages, explore areas, and manage your entire buying journey — all in one place. We're almost ready. For now, we're here to guide you every step of the way.</p>
      </div>
    </div>
  </section>


</div>

<script>
const io = new IntersectionObserver(e => {
  e.forEach(x => { if(x.isIntersecting) x.target.classList.add('vis'); });
}, { threshold: 0.07 });
document.querySelectorAll('.gcard').forEach((el, i) => {
  el.style.transitionDelay = (i * 0.06) + 's';
  io.observe(el);
});
</script>
</body>
</html>