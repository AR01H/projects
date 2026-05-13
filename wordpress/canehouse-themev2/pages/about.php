<?php get_header(); ?>

<style>

/* ── HERO ── */
.hero {
  position: relative;
  min-height: 90vh;
  display: flex;
  align-items: center;
  background: linear-gradient(135deg, var(--green-deep) 0%, #1a3a0a 50%, #2d5a1b 100%);
  overflow: hidden;
}
.hero-bg-pattern {
  position: absolute; inset: 0;
  background-image:
    radial-gradient(circle at 20% 50%, rgba(106,191,58,0.15) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(200,232,48,0.1) 0%, transparent 40%),
    radial-gradient(circle at 60% 80%, rgba(168,217,110,0.08) 0%, transparent 40%);
}
.hero-leaves {
  position: absolute; right: -60px; top: -40px;
  width: 55%; height: 110%;
  opacity: 0.07;
  background: url("data:image/svg+xml,%3Csvg viewBox='0 0 400 600' xmlns='http://www.w3.org/2000/svg'%3E%3Cellipse cx='200' cy='100' rx='30' ry='90' fill='%23a8d96e' transform='rotate(-20 200 100)'/%3E%3Cellipse cx='250' cy='200' rx='25' ry='80' fill='%23a8d96e' transform='rotate(15 250 200)'/%3E%3Cellipse cx='150' cy='300' rx='28' ry='85' fill='%23a8d96e' transform='rotate(-10 150 300)'/%3E%3Cellipse cx='300' cy='400' rx='22' ry='75' fill='%23a8d96e' transform='rotate(25 300 400)'/%3E%3Cellipse cx='180' cy='500' rx='30' ry='88' fill='%23a8d96e' transform='rotate(-5 180 500)'/%3E%3C/svg%3E") center/cover no-repeat;
}
.hero-content {
  position: relative; z-index: 2;
  max-width: 900px;
  padding: 100px 60px;
}
.hero-tag {
  display: inline-block;
  font-family: 'DM Sans', sans-serif;
  font-size: 11px; letter-spacing: 4px; text-transform: uppercase;
  color: var(--lime); font-weight: 500;
  border: 1px solid rgba(200,232,48,0.3);
  padding: 8px 20px; border-radius: 30px;
  margin-bottom: 32px;
  animation: fadeUp 0.8s ease both;
}
.hero h1 {
  font-family: 'Cormorant Garamond', serif;
  font-size: clamp(56px, 8vw, 100px);
  font-weight: 300;
  line-height: 1.0;
  color: var(--white);
  margin-bottom: 28px;
  animation: fadeUp 0.8s 0.15s ease both;
}
.hero h1 em {
  font-style: italic;
  color: var(--lime);
}
.hero-sub {
  font-size: 18px; line-height: 1.7; color: rgba(255,255,255,0.7);
  max-width: 520px;
  font-weight: 300;
  animation: fadeUp 0.8s 0.3s ease both;
}
.hero-scroll {
  position: absolute; bottom: 40px; left: 50%; transform: translateX(-50%);
  display: flex; flex-direction: column; align-items: center; gap: 8px;
  color: rgba(255,255,255,0.4); font-size: 11px; letter-spacing: 3px; text-transform: uppercase;
  animation: fadeUp 1s 0.6s ease both;
}
.hero-scroll span { width: 1px; height: 50px; background: linear-gradient(to bottom, rgba(200,232,48,0.6), transparent); }

/* ── STATS BAR ── */
.stats-bar {
  background: var(--lime);
  display: flex; justify-content: center; gap: 0;
  flex-wrap: wrap;
}
.stat-item {
  flex: 1; min-width: 160px;
  padding: 32px 24px;
  text-align: center;
  border-right: 1px solid rgba(45,90,27,0.15);
  transition: background 0.3s;
}
.stat-item:last-child { border-right: none; }
.stat-item:hover { background: rgba(45,90,27,0.08); }
.stat-num {
  font-family: 'Cormorant Garamond', serif;
  font-size: 44px; font-weight: 600;
  color: var(--green-deep); line-height: 1;
}
.stat-label {
  font-size: 11px; letter-spacing: 2px; text-transform: uppercase;
  color: var(--green-mid); margin-top: 6px;
}

/* ── SECTIONS ── */
.section { padding: 100px 40px; }
.section-inner { max-width: 1200px; margin: 0 auto; }

.tag-line {
  font-size: 11px; letter-spacing: 4px; text-transform: uppercase;
  color: var(--green-mid); font-weight: 500;
  display: flex; align-items: center; gap: 12px;
  margin-bottom: 20px;
}
.tag-line::before { content: ''; width: 30px; height: 1px; background: var(--green-mid); }

h2.section-title {
  font-family: 'Cormorant Garamond', serif;
  font-size: clamp(40px, 5vw, 64px);
  font-weight: 300; line-height: 1.1;
  color: var(--green-deep);
  margin-bottom: 24px;
}
h2.section-title em { font-style: italic; color: var(--green-mid); }

/* ── OUR JOURNEY ── */
.journey-section { background: var(--white); }
.journey-grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: 80px;
  align-items: center;
}
.journey-text p {
  font-size: 17px; line-height: 1.8; color: #3a5a28;
  margin-bottom: 20px; font-weight: 300;
}
.journey-image {
  position: relative;
}
.journey-image .img-frame {
  width: 100%; aspect-ratio: 4/5;
  background: linear-gradient(145deg, var(--green-pale), var(--green-bg));
  border-radius: 4px 80px 4px 4px;
  overflow: hidden;
  position: relative;
}
.journey-image .img-frame img {
  width: 100%; height: 100%; object-fit: cover;
  mix-blend-mode: multiply; opacity: 0.85;
}
.year-badge {
  position: absolute; bottom: -20px; left: -20px;
  background: var(--green-deep); color: var(--lime);
  padding: 20px 28px; border-radius: 4px;
  font-family: 'Cormorant Garamond', serif;
  font-size: 48px; font-weight: 600; line-height: 1;
}
.year-badge small { display: block; font-size: 12px; letter-spacing: 2px; color: rgba(200,232,48,0.6); font-family: 'DM Sans', sans-serif; }

/* Timeline */
.timeline { margin-top: 80px; position: relative; }
.timeline::before {
  content: ''; position: absolute; left: 50%; top: 0; bottom: 0;
  width: 1px; background: linear-gradient(to bottom, transparent, var(--green-light), transparent);
  transform: translateX(-50%);
}
.tl-item {
  display: grid; grid-template-columns: 1fr 60px 1fr;
  gap: 40px; align-items: center;
  margin-bottom: 60px;
}
.tl-item.right .tl-content { grid-column: 3; text-align: left; }
.tl-item.right .tl-empty { grid-column: 1; }
.tl-dot {
  grid-column: 2;
  width: 48px; height: 48px;
  background: var(--green-deep); border: 3px solid var(--lime);
  border-radius: 50%; display: flex; align-items: center; justify-content: center;
  font-size: 18px; position: relative; z-index: 1; justify-self: center;
}
.tl-content {
  background: var(--green-bg);
  padding: 28px 32px; border-radius: 4px;
  border-left: 3px solid var(--lime);
}
.tl-year {
  font-size: 11px; letter-spacing: 3px; text-transform: uppercase;
  color: var(--green-mid); font-weight: 500; margin-bottom: 6px;
}
.tl-content h3 {
  font-family: 'Cormorant Garamond', serif;
  font-size: 24px; color: var(--green-deep); margin-bottom: 8px;
}
.tl-content p { font-size: 14px; line-height: 1.7; color: var(--text-muted); }

/* ── FRESHNESS PHILOSOPHY ── */
.philosophy-section { background: var(--green-deep); }
.philosophy-section .tag-line { color: rgba(200,232,48,0.7); }
.philosophy-section .tag-line::before { background: rgba(200,232,48,0.5); }
.philosophy-section h2.section-title { color: var(--white); }
.philosophy-section h2.section-title em { color: var(--lime); }

.pillars-grid {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 2px;
  margin-top: 60px; background: rgba(255,255,255,0.05);
}
.pillar {
  padding: 56px 40px;
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(200,232,48,0.08);
  transition: background 0.4s, transform 0.3s;
  position: relative; overflow: hidden;
}
.pillar::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0;
  height: 2px; background: linear-gradient(to right, transparent, var(--lime), transparent);
  transform: scaleX(0); transition: transform 0.4s;
}
.pillar:hover { background: rgba(200,232,48,0.06); }
.pillar:hover::before { transform: scaleX(1); }
.pillar-icon { font-size: 36px; margin-bottom: 20px; }
.pillar h3 {
  font-family: 'Cormorant Garamond', serif;
  font-size: 26px; color: var(--white); margin-bottom: 12px;
}
.pillar p { font-size: 14px; line-height: 1.7; color: rgba(255,255,255,0.55); }
.pillar-num {
  position: absolute; top: 24px; right: 24px;
  font-family: 'Cormorant Garamond', serif;
  font-size: 72px; font-weight: 600;
  color: rgba(200,232,48,0.06); line-height: 1;
}

/* ── SUGARCANE EXPERIENCE ── */
.experience-section { background: var(--green-bg); }
.experience-intro {
  max-width: 680px; margin-bottom: 70px;
}
.experience-intro p { font-size: 18px; line-height: 1.8; color: #3a5a28; font-weight: 300; }

.exp-cards {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;
}
.exp-card {
  background: var(--white);
  border-radius: 4px;
  overflow: hidden;
  position: relative;
  transition: transform 0.35s, box-shadow 0.35s;
  box-shadow: 0 4px 20px var(--shadow);
}
.exp-card:hover { transform: translateY(-8px); box-shadow: 0 16px 40px var(--shadow); }
.exp-card-img {
  height: 200px;
  background: linear-gradient(145deg, var(--green-pale), var(--green-light));
  display: flex; align-items: center; justify-content: center;
  font-size: 56px;
  position: relative; overflow: hidden;
}
.exp-card-img::after {
  content: ''; position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(45,90,27,0.6), transparent);
}
.exp-card-body { padding: 24px; }
.exp-card-body h3 {
  font-family: 'Cormorant Garamond', serif;
  font-size: 22px; color: var(--green-deep); margin-bottom: 8px;
}
.exp-card-body p { font-size: 13px; line-height: 1.65; color: var(--text-muted); }

/* ── VISION & MISSION ── */
.vision-section { background: var(--white); }
.vision-split {
  display: grid; grid-template-columns: 1fr 1fr; gap: 3px;
  margin-top: 60px;
}
.vs-box {
  padding: 64px 56px;
  position: relative; overflow: hidden;
}
.vs-box.vision-box { background: var(--green-deep); }
.vs-box.mission-box { background: var(--lime); }
.vs-box .vs-label {
  font-size: 10px; letter-spacing: 5px; text-transform: uppercase;
  margin-bottom: 20px;
}
.vision-box .vs-label { color: rgba(200,232,48,0.6); }
.mission-box .vs-label { color: var(--green-mid); }
.vs-box h3 {
  font-family: 'Cormorant Garamond', serif;
  font-size: clamp(32px, 3vw, 48px); font-weight: 300; line-height: 1.2;
  margin-bottom: 24px;
}
.vision-box h3 { color: var(--white); }
.mission-box h3 { color: var(--green-deep); }
.vs-box p { font-size: 16px; line-height: 1.75; }
.vision-box p { color: rgba(255,255,255,0.65); }
.mission-box p { color: var(--green-deep); opacity: 0.8; }
.vs-deco {
  position: absolute; bottom: -20px; right: -20px;
  font-family: 'Cormorant Garamond', serif;
  font-size: 140px; line-height: 1; font-weight: 600; opacity: 0.05;
}
.vision-box .vs-deco { color: var(--lime); }
.mission-box .vs-deco { color: var(--green-deep); }

/* ── FOUNDER ── */
.founder-section {
  background: linear-gradient(160deg, var(--green-bg) 60%, var(--green-pale));
  position: relative; overflow: hidden;
}
.founder-grid {
  display: grid; grid-template-columns: 380px 1fr; gap: 80px; align-items: center;
}
.founder-photo {
  position: relative;
}
.founder-photo .photo-wrap {
  width: 100%; aspect-ratio: 3/4;
  background: linear-gradient(145deg, var(--green-light), var(--green-mid));
  border-radius: 4px 100px 4px 4px;
  display: flex; align-items: center; justify-content: center;
  font-size: 80px; color: rgba(255,255,255,0.4);
  overflow: hidden;
}
.founder-photo .quote-badge {
  position: absolute; bottom: 30px; right: -30px;
  background: var(--green-deep); color: var(--white);
  padding: 20px 24px; max-width: 240px;
  border-radius: 4px;
  font-family: 'Cormorant Garamond', serif;
  font-size: 18px; font-style: italic; line-height: 1.4;
}
.founder-photo .quote-badge::before { content: '"'; font-size: 40px; color: var(--lime); line-height: 1; display: block; }
.founder-text h3 {
  font-family: 'Cormorant Garamond', serif;
  font-size: 48px; font-weight: 300; color: var(--green-deep);
  margin-bottom: 6px;
}
.founder-role {
  font-size: 11px; letter-spacing: 3px; text-transform: uppercase;
  color: var(--green-mid); margin-bottom: 28px;
}
.founder-text p {
  font-size: 16px; line-height: 1.8; color: #3a5a28;
  margin-bottom: 18px; font-weight: 300;
}
.founder-values {
  display: flex; gap: 12px; flex-wrap: wrap; margin-top: 32px;
}
.fv-chip {
  padding: 8px 18px; border: 1px solid var(--green-light);
  border-radius: 30px; font-size: 12px; color: var(--green-deep);
  letter-spacing: 1px;
}

/* ── PREMIUM INGREDIENTS ── */
.ingredients-section { background: var(--green-deep); }
.ingredients-section .tag-line { color: rgba(200,232,48,0.7); }
.ingredients-section .tag-line::before { background: rgba(200,232,48,0.4); }
.ingredients-section h2.section-title { color: var(--white); }

.ing-list {
  display: grid; grid-template-columns: repeat(2, 1fr); gap: 1px;
  margin-top: 60px; background: rgba(255,255,255,0.05);
}
.ing-item {
  display: flex; gap: 28px; align-items: flex-start;
  padding: 40px 44px;
  background: rgba(255,255,255,0.02);
  border: 1px solid rgba(200,232,48,0.07);
  transition: background 0.3s;
}
.ing-item:hover { background: rgba(200,232,48,0.04); }
.ing-icon {
  width: 56px; height: 56px; flex-shrink: 0;
  background: rgba(200,232,48,0.1);
  border-radius: 50%; display: flex; align-items: center; justify-content: center;
  font-size: 24px;
}
.ing-text h3 {
  font-family: 'Cormorant Garamond', serif;
  font-size: 24px; color: var(--white); margin-bottom: 8px;
}
.ing-text p { font-size: 14px; line-height: 1.65; color: rgba(255,255,255,0.5); }

/* ── HYGIENE STANDARDS ── */
.hygiene-section { background: var(--green-bg); }
.hygiene-intro { max-width: 620px; margin-bottom: 60px; }
.hygiene-intro p { font-size: 17px; line-height: 1.8; color: #3a5a28; font-weight: 300; }

.hygiene-grid {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;
}
.hy-card {
  padding: 40px 32px;
  background: var(--white);
  border-radius: 4px;
  border-bottom: 3px solid var(--lime);
  box-shadow: 0 2px 16px var(--shadow);
  transition: transform 0.3s;
}
.hy-card:hover { transform: translateY(-4px); }
.hy-card .hy-icon { font-size: 36px; margin-bottom: 18px; }
.hy-card h3 {
  font-family: 'Cormorant Garamond', serif;
  font-size: 24px; color: var(--green-deep); margin-bottom: 10px;
}
.hy-card p { font-size: 14px; line-height: 1.65; color: var(--text-muted); }
.hy-badge {
  display: inline-block; margin-top: 14px;
  font-size: 10px; letter-spacing: 2px; text-transform: uppercase;
  color: var(--green-mid); border: 1px solid var(--green-light);
  padding: 4px 12px; border-radius: 20px;
}

/* ── SUSTAINABILITY ── */
.sustain-section { background: var(--white); }
.sustain-hero {
  background: linear-gradient(145deg, var(--green-deep), #1a3a0a);
  border-radius: 4px;
  padding: 80px 80px;
  display: grid; grid-template-columns: 1fr 1fr; gap: 80px;
  align-items: center;
  margin-bottom: 60px; position: relative; overflow: hidden;
}
.sustain-hero::before {
  content: '🌿'; position: absolute; right: 60px; top: 40px;
  font-size: 200px; opacity: 0.04;
}
.sustain-hero-text h3 {
  font-family: 'Cormorant Garamond', serif;
  font-size: 48px; font-weight: 300; color: var(--white);
  margin-bottom: 16px;
}
.sustain-hero-text p { font-size: 16px; line-height: 1.8; color: rgba(255,255,255,0.6); }
.sustain-stats {
  display: grid; grid-template-columns: 1fr 1fr; gap: 24px;
}
.ss-item {
  padding: 28px; background: rgba(200,232,48,0.08);
  border: 1px solid rgba(200,232,48,0.15); border-radius: 4px;
  text-align: center;
}
.ss-item .ss-num {
  font-family: 'Cormorant Garamond', serif;
  font-size: 40px; font-weight: 600; color: var(--lime);
}
.ss-item .ss-lbl { font-size: 12px; color: rgba(255,255,255,0.5); margin-top: 4px; letter-spacing: 1px; }

.sustain-cards {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;
}
.sc-card {
  padding: 36px 28px;
  border: 1px solid var(--green-pale);
  border-radius: 4px;
  position: relative; overflow: hidden;
  transition: border-color 0.3s, box-shadow 0.3s;
}
.sc-card:hover { border-color: var(--lime); box-shadow: 0 8px 30px var(--shadow); }
.sc-card .sc-icon { font-size: 32px; margin-bottom: 16px; }
.sc-card h3 {
  font-family: 'Cormorant Garamond', serif;
  font-size: 22px; color: var(--green-deep); margin-bottom: 10px;
}
.sc-card p { font-size: 13px; line-height: 1.65; color: var(--text-muted); }

/* ── ANIMATIONS ── */
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}
.reveal {
  opacity: 0; transform: translateY(40px);
  transition: opacity 0.7s ease, transform 0.7s ease;
}
.reveal.visible { opacity: 1; transform: translateY(0); }

/* ── RESPONSIVE ── */
@media (max-width: 900px) {
  .journey-grid, .founder-grid, .vision-split { grid-template-columns: 1fr; }
  .hero-content { padding: 80px 28px; }
  .pillars-grid, .exp-cards, .sustain-cards, .sustain-stats { grid-template-columns: 1fr 1fr; }
  .hygiene-grid { grid-template-columns: 1fr; }
  .ing-list { grid-template-columns: 1fr; }
  .timeline::before { display: none; }
  .tl-item { grid-template-columns: 1fr; gap: 16px; }
  .tl-dot { display: none; }
  .tl-item.right .tl-content { grid-column: 1; }
  .sustain-hero { grid-template-columns: 1fr; padding: 48px 32px; }
  .section { padding: 60px 24px; }
  .year-badge { bottom: -16px; left: -8px; font-size: 36px; }
  .founder-photo .quote-badge { right: 0; }
  .vs-box { padding: 40px 32px; }
}
@media (max-width: 600px) {
  .pillars-grid, .exp-cards, .sustain-cards, .sustain-stats { grid-template-columns: 1fr; }
  .stats-bar { flex-direction: column; }
}
</style>

<?php
// ═══════════════════════════════════════════════════════
//  PAGE DATA — edit content here, markup stays untouched
// ═══════════════════════════════════════════════════════

$stats = [
    [ 'num' => '2K+',  'label' => 'Years of Cane History' ],
    [ 'num' => '100%', 'label' => 'Natural & Pure'        ],
    [ 'num' => '0',    'label' => 'Artificial Additives'  ],
    [ 'num' => '∞',    'label' => 'Freshness Promise'     ],
];

$timeline = [
    [
        'year'  => '2020',
        'title' => 'The First Press',
        'body'  => 'Our founder presses the very first glass at a local market stall. The response is overwhelming — people taste the difference immediately.',
        'emoji' => '🌱',
        'side'  => 'left',
    ],
    [
        'year'  => '2021',
        'title' => 'First Permanent Stall',
        'body'  => 'Growing demand leads to our first fixed location. Word of mouth spreads fast — pure cane beats every synthetic alternative on the market.',
        'emoji' => '🏪',
        'side'  => 'right',
    ],
    [
        'year'  => '2022',
        'title' => 'Event Hire Launches',
        'body'  => 'Weddings, festivals, corporate events — The Cane House becomes the premium live-pressing experience that elevates every celebration.',
        'emoji' => '🎉',
        'side'  => 'left',
    ],
    [
        'year'  => '2024',
        'title' => 'Franchise Programme Opens',
        'body'  => 'The fresh juice revolution expands. We invite passionate entrepreneurs to bring authentic cane culture to their cities.',
        'emoji' => '🤝',
        'side'  => 'right',
    ],
];

$pillars = [
    [ 'num' => '01', 'icon' => '🌾', 'title' => 'Pressed Live, Always',       'body' => 'Every glass is pressed at the moment of order. We do not pre-make, pre-bottle, or preserve. Your juice begins the instant you ask for it.' ],
    [ 'num' => '02', 'icon' => '🚫', 'title' => 'Zero Artificial Anything',   'body' => 'No syrups, no concentrates, no artificial sweeteners, no preservatives. The sweetness you taste comes entirely from the cane itself.' ],
    [ 'num' => '03', 'icon' => '🌡️', 'title' => 'Served at Peak Freshness',  'body' => 'Temperature matters. We chill naturally — not with excessive ice — so the full spectrum of cane flavour reaches your palate intact.' ],
    [ 'num' => '04', 'icon' => '🌿', 'title' => 'Real Botanicals Only',       'body' => 'Our add-ins — mint, ginger, lemon, tropical fruits — are whole, real ingredients. Never extracts, never flavouring agents.' ],
    [ 'num' => '05', 'icon' => '🧽', 'title' => 'Hygiene as a Ritual',        'body' => 'Our machines are cleaned between every pressing session. Cleanliness is not a standard — it is a philosophy woven into our culture.' ],
    [ 'num' => '06', 'icon' => '🌍', 'title' => 'Consciously Sourced',        'body' => 'We work only with trusted growers who share our values. Cane that is grown with care produces juice that you can taste the difference in.' ],
];

$experience_cards = [
    [ 'icon' => '🌾', 'title' => 'Farm Selection', 'body' => 'Yellow and Red varieties, hand-selected for sugar content and freshness. Only the finest stalks reach our machines.' ],
    [ 'icon' => '⚙️', 'title' => 'Live Pressing',  'body' => 'Pressed right before your eyes. The theatre of extraction is as much a part of the experience as the juice itself.' ],
    [ 'icon' => '🧊', 'title' => 'Chilled to Perfection', 'body' => 'Served at the exact temperature that maximises refreshment without diluting the cane\'s natural character.' ],
    [ 'icon' => '🥂', 'title' => 'Premium Serving', 'body' => 'Presented with care in premium glassware. Because extraordinary ingredients deserve extraordinary presentation.' ],
];

$ingredients = [
    [ 'icon' => '🌾', 'title' => 'Yellow Sugarcane',     'body' => 'Light golden, delicately sweet. Our flagship cane variety — fresh, clean, and universally loved. The purest expression of cane juice.' ],
    [ 'icon' => '🎋', 'title' => 'Red Sugarcane',        'body' => 'Naturally richer and deeper in colour, with a complex amber sweetness. For those who want a more intense cane experience.' ],
    [ 'icon' => '🍋', 'title' => 'Fresh Citrus',         'body' => 'Real lemon juice — never concentrate — adds a bright, zesty counterpoint that elevates and energises every glass.' ],
    [ 'icon' => '🫚', 'title' => 'Whole Ginger Root',    'body' => 'Pressed or grated fresh. Warming, digestive, and deeply flavourful. A classic pairing trusted across tropical cultures for generations.' ],
    [ 'icon' => '🌿', 'title' => 'Fresh Mint Leaves',    'body' => 'Cooling and aromatic. Hand-picked mint adds a clean, herbal dimension that transforms cane juice into the ultimate summer refresher.' ],
    [ 'icon' => '🍍', 'title' => 'Tropical Fruit Blends','body' => 'Cold-pressed pineapple, watermelon, and strawberry extracts — real fruit, not syrups — for our tropical fusion range.' ],
];

$hygiene_cards = [
    [ 'icon' => '🧼', 'title' => 'Press-by-Press Cleaning',  'body' => 'Our machines are cleaned and sanitised between every pressing session — not at the end of the day, but throughout the day, every time.',  'badge' => 'Zero Cross-Contamination' ],
    [ 'icon' => '🥶', 'title' => 'Cold-Chain Integrity',      'body' => 'Cane stalks are stored at controlled temperatures from delivery to press. Cold storage preserves natural sugars and prevents fermentation.',   'badge' => 'Temperature Controlled'  ],
    [ 'icon' => '🧤', 'title' => 'Food-Safe Handling',        'body' => 'All staff follow rigorous food hygiene protocols. Gloves, aprons, and food-grade equipment are mandatory at every serving point.',            'badge' => 'Certified Safe'           ],
    [ 'icon' => '🌾', 'title' => 'Cane Inspection Protocol',  'body' => 'Every batch of cane is visually inspected before pressing. Any stalk that doesn\'t meet our quality standard is rejected immediately.',          'badge' => 'Quality Checked'          ],
    [ 'icon' => '🫙', 'title' => 'No Reuse, No Shortcuts',    'body' => 'Serving cups, straws, and all single-use items are never reused. We take no shortcuts when it comes to the customer experience.',               'badge' => 'Single-Use Guarantee'     ],
    [ 'icon' => '✅', 'title' => 'Fully Insured & Certified', 'body' => 'The Cane House operates with full public liability insurance and food safety certification across all locations and events.',                    'badge' => 'Fully Compliant'          ],
];

$sustain_stats = [
    [ 'num' => '0%',   'label' => 'Plastic Straws'        ],
    [ 'num' => '100%', 'label' => 'Biodegradable Cups'    ],
    [ 'num' => 'Local','label' => 'Farmer Partnerships'   ],
    [ 'num' => 'Zero', 'label' => 'Waste Philosophy'      ],
];

$sustain_cards = [
    [ 'icon' => '♻️', 'title' => 'Eco Packaging',      'body' => 'All cups, lids, and carriers are compostable and plant-derived. We actively avoid single-use plastics at every customer touchpoint.' ],
    [ 'icon' => '🌱', 'title' => 'Farm Partnerships',  'body' => 'We source from growers who practice sustainable agriculture — no harmful pesticides, responsible water usage, and fair labour practices.' ],
    [ 'icon' => '🏘️', 'title' => 'Supporting Local',  'body' => 'Our franchise model prioritises local employment and community investment. When we grow, the communities around us grow too.' ],
    [ 'icon' => '🌾', 'title' => 'Zero Waste Pressing','body' => 'Bagasse from our pressing is composted or diverted to biofuel partnerships. We aim to have a net-zero waste footprint at all locations.' ],
];

$founder_values = [
    'Purity First',
    'Pressed Live',
    'Community Roots',
    'No Compromise',
    'Natural Always',
];
?>


<!-- ═══════════════════════════════════════ HERO ═══════ -->
<section class="hero">
  <div class="hero-bg-pattern"></div>
  <div class="hero-leaves"></div>
  <div class="hero-content">
    <div class="hero-tag">Our Story</div>
    <h1>The <em>Soul</em><br>Behind the Cane</h1>
    <p class="hero-sub">Born from a love of pure refreshment and a belief that nature's finest ingredients deserve the finest care. This is The Cane House.</p>
  </div>
  <div class="hero-scroll">
    scroll
    <span></span>
  </div>
</section>


<!-- ═══════════════════════════════════════ STATS ═══════ -->
<div class="stats-bar">
  <?php foreach ( $stats as $stat ) : ?>
    <div class="stat-item">
      <div class="stat-num"><?php echo esc_html( $stat['num'] ); ?></div>
      <div class="stat-label"><?php echo esc_html( $stat['label'] ); ?></div>
    </div>
  <?php endforeach; ?>
</div>


<!-- ═══════════════════════════════════════ OUR JOURNEY ═══════ -->
<section class="section journey-section">
  <div class="section-inner">
    <div class="journey-grid">
      <div class="journey-text">
        <div class="tag-line">Our Journey</div>
        <h2 class="section-title">From <em>Field</em><br>to Your Glass</h2>
        <p>It started with a simple question: why can't everyone enjoy the same pure, live-pressed sugarcane juice that has energised tropical communities for centuries? The Cane House was our answer.</p>
        <p>We set out to bring the authentic experience of freshly crushed cane juice to modern palates — without compromise, without shortcuts, and without ever sacrificing the natural integrity that makes sugarcane so extraordinary.</p>
        <p>Every glass is a journey from sun-drenched fields to your hands, pressed live, served cool, and crafted with the reverence this ancient crop deserves.</p>
      </div>
      <div class="journey-image reveal">
        <div class="img-frame">
          <img src="https://images.unsplash.com/photo-1546173159-315724a31696?auto=format&fit=crop&q=80&w=600" alt="Sugarcane field">
        </div>
        <div class="year-badge">
          2020
          <small>Founded</small>
        </div>
      </div>
    </div>

    <!-- Timeline -->
    <div class="timeline reveal">
      <?php foreach ( $timeline as $item ) : ?>
        <div class="tl-item <?php echo ( $item['side'] === 'right' ) ? 'right' : ''; ?>">
          <?php if ( $item['side'] === 'right' ) : ?>
            <div></div>
          <?php endif; ?>

          <?php if ( $item['side'] === 'left' ) : ?>
            <div class="tl-content">
              <div class="tl-year"><?php echo esc_html( $item['year'] ); ?></div>
              <h3><?php echo esc_html( $item['title'] ); ?></h3>
              <p><?php echo esc_html( $item['body'] ); ?></p>
            </div>
          <?php endif; ?>

          <div class="tl-dot"><?php echo $item['emoji']; ?></div>

          <?php if ( $item['side'] === 'right' ) : ?>
            <div class="tl-content">
              <div class="tl-year"><?php echo esc_html( $item['year'] ); ?></div>
              <h3><?php echo esc_html( $item['title'] ); ?></h3>
              <p><?php echo esc_html( $item['body'] ); ?></p>
            </div>
          <?php endif; ?>

          <?php if ( $item['side'] === 'left' ) : ?>
            <div></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════ FRESHNESS PHILOSOPHY ═══════ -->
<section class="section philosophy-section">
  <div class="section-inner">
    <div class="tag-line">Freshness Philosophy</div>
    <h2 class="section-title">The Art of <em>Pure</em></h2>

    <div class="pillars-grid">
      <?php foreach ( $pillars as $pillar ) : ?>
        <div class="pillar reveal">
          <div class="pillar-num"><?php echo esc_html( $pillar['num'] ); ?></div>
          <div class="pillar-icon"><?php echo $pillar['icon']; ?></div>
          <h3><?php echo esc_html( $pillar['title'] ); ?></h3>
          <p><?php echo esc_html( $pillar['body'] ); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════ SUGARCANE EXPERIENCE ═══════ -->
<section class="section experience-section">
  <div class="section-inner">
    <div class="tag-line">The Sugarcane Experience</div>
    <h2 class="section-title">More Than a <em>Drink</em></h2>
    <div class="experience-intro">
      <p>Sugarcane juice is a sensory journey — the scent of fresh cane, the visual theatre of live pressing, the first cool sip of something entirely, purely natural. We've crafted every touchpoint to honour that experience.</p>
    </div>

    <div class="exp-cards">
      <?php foreach ( $experience_cards as $card ) : ?>
        <div class="exp-card reveal">
          <div class="exp-card-img"><?php echo $card['icon']; ?></div>
          <div class="exp-card-body">
            <h3><?php echo esc_html( $card['title'] ); ?></h3>
            <p><?php echo esc_html( $card['body'] ); ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════ VISION & MISSION ═══════ -->
<section class="section vision-section">
  <div class="section-inner">
    <div class="tag-line">Vision & Mission</div>
    <h2 class="section-title">What We <em>Stand</em> For</h2>

    <div class="vision-split reveal">
      <div class="vs-box vision-box">
        <div class="vs-label">Our Vision</div>
        <h3>To redefine what refreshment means in the modern world</h3>
        <p>We envision a world where the default choice for refreshment is natural, pure, and pressed fresh — where people reach for real over artificial, and where sugarcane takes its rightful place as the world's finest beverage crop.</p>
        <div class="vs-deco">V</div>
      </div>
      <div class="vs-box mission-box">
        <div class="vs-label">Our Mission</div>
        <h3>Press fresher, serve better, grow together</h3>
        <p>Every glass we press is a commitment to freshness, honesty, and quality. Our mission is to make live-pressed sugarcane juice accessible, exciting, and extraordinary — one city, one franchise, one sip at a time.</p>
        <div class="vs-deco">M</div>
      </div>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════ FOUNDER ═══════ -->
<section class="section founder-section">
  <div class="section-inner">
    <div class="founder-grid">
      <div class="founder-photo reveal">
        <div class="photo-wrap">🧑‍🌾</div>
        <div class="quote-badge">
          Nature gave us sugarcane. We just press it right.
        </div>
      </div>
      <div class="founder-text">
        <div class="tag-line">Founder Story</div>
        <h3>The Founder's Letter</h3>
        <div class="founder-role">Founder & Head of Freshness</div>
        <p>Growing up, fresh cane juice was never just a drink — it was a ritual. The sound of the press, the golden colour, the instant energy it gave you on a summer afternoon. When I moved to the UK, I realised how much I missed it. Not just the taste, but that feeling.</p>
        <p>That's why I started The Cane House. Not to build a business, but to recreate a feeling — and share it. I wanted everyone to experience what I grew up knowing: that when nature does the work, nothing artificial comes close.</p>
        <p>We are not a juice brand. We are custodians of something ancient, made fresh every day.</p>
        <div class="founder-values">
          <?php foreach ( $founder_values as $value ) : ?>
            <span class="fv-chip"><?php echo esc_html( $value ); ?></span>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════ PREMIUM INGREDIENTS ═══════ -->
<section class="section ingredients-section">
  <div class="section-inner">
    <div class="tag-line">Premium Ingredients</div>
    <h2 class="section-title">What Goes <em>Inside</em></h2>
    <div class="ing-list reveal">
      <?php foreach ( $ingredients as $ing ) : ?>
        <div class="ing-item">
          <div class="ing-icon"><?php echo $ing['icon']; ?></div>
          <div class="ing-text">
            <h3><?php echo esc_html( $ing['title'] ); ?></h3>
            <p><?php echo esc_html( $ing['body'] ); ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════ HYGIENE STANDARDS ═══════ -->
<section class="section hygiene-section">
  <div class="section-inner">
    <div class="tag-line">Hygiene Standards</div>
    <h2 class="section-title">Freshness You Can <em>Trust</em></h2>
    <div class="hygiene-intro">
      <p>At The Cane House, hygiene isn't an afterthought or a compliance box. It is woven into every step of our process — from sourcing to serving — because purity of ingredient demands purity of practice.</p>
    </div>

    <div class="hygiene-grid">
      <?php foreach ( $hygiene_cards as $card ) : ?>
        <div class="hy-card reveal">
          <div class="hy-icon"><?php echo $card['icon']; ?></div>
          <h3><?php echo esc_html( $card['title'] ); ?></h3>
          <p><?php echo esc_html( $card['body'] ); ?></p>
          <span class="hy-badge"><?php echo esc_html( $card['badge'] ); ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════ SUSTAINABILITY ═══════ -->
<section class="section sustain-section">
  <div class="section-inner">
    <div class="tag-line">Sustainability</div>
    <h2 class="section-title">Good for You.<br><em>Good for the Earth.</em></h2>

    <div class="sustain-hero reveal">
      <div class="sustain-hero-text">
        <h3>Sugarcane is Nature's Most Generous Crop</h3>
        <p>Every part of the cane plant gives back. The juice refreshes and nourishes. The fibre (bagasse) is 100% biodegradable. Even the by-products become biofuel and organic fertiliser. When we press a glass of cane juice, almost nothing is wasted.</p>
      </div>
      <div class="sustain-stats">
        <?php foreach ( $sustain_stats as $ss ) : ?>
          <div class="ss-item">
            <div class="ss-num"><?php echo esc_html( $ss['num'] ); ?></div>
            <div class="ss-lbl"><?php echo esc_html( $ss['label'] ); ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="sustain-cards">
      <?php foreach ( $sustain_cards as $card ) : ?>
        <div class="sc-card reveal">
          <div class="sc-icon"><?php echo $card['icon']; ?></div>
          <h3><?php echo esc_html( $card['title'] ); ?></h3>
          <p><?php echo esc_html( $card['body'] ); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<script>
// Reveal on scroll
const observer = new IntersectionObserver((entries) => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); } });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>

<?php get_footer(); ?>