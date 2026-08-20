/* =========================================================
   CANE RUSH — config.js
   All game CONTENT lives here as data: colors, environments,
   characters, obstacle/powerup metadata and the asset manifest.
   Add a new character/environment/achievement by editing the
   arrays below — no other file needs to change.
   ========================================================= */
'use strict';

/* ---------------- PALETTE ---------------- */
const COLORS = {
  lime:'#8BD246', limeDark:'#5FA82C', yellow:'#FFD23F', orange:'#FF8C3B',
  mango:'#FFB347', coconut:'#FFFDF7', aqua:'#3FD1D9', aquaDark:'#1FA3AC',
  cream:'#FFF7E9', cream2:'#FFEFD6', ink:'#2E2A1F', hotpink:'#FF6FA5',
  red:'#FF5A5F', purple:'#8B6FE0', brown:'#8B5A2B', watermelonRed:'#FF5D6C',
  watermelonGreen:'#3FA34D'
};

const LANES = [-1, 0, 1];
const JUDGE_Z = 0.90;      // depth at which collisions/pickups are evaluated
const PLAYER_Z = 0.90;
const HORIZON_Y_RATIO = 0.30;
const ENV_DISTANCE = 700; // meters per environment

/* ---------------- ENVIRONMENTS ---------------- */
const ENVIRONMENTS = [
  { name:'Sugarcane Farm', sky:['#BFF0C8','#EAFBD6'], ground:'#DCEFA0', road:'#C7E58A', accent:'#6FAF3C', deco:'farm' },
  { name:'Tropical Road', sky:['#BDEDF2','#E8FBF7'], ground:'#CDEFEF', road:'#AEE0E3', accent:'#1FA3AC', deco:'road' },
  { name:'Fruit Garden', sky:['#FFE3C4','#FFF6E3'], ground:'#FFE7B0', road:'#FFD98A', accent:'#FF8C3B', deco:'garden' },
  { name:'Juice Factory', sky:['#FFD7E6','#FFF0F5'], ground:'#FFD0DE', road:'#FFC0D2', accent:'#FF6FA5', deco:'factory' },
  { name:'Beach Road', sky:['#CDEFFF','#F0FBFF'], ground:'#FFF3D0', road:'#F5E3A8', accent:'#3FD1D9', deco:'beach' },
  { name:'Tropical Market', sky:['#F6E3FF','#FFF3FF'], ground:'#EAD8FF', road:'#DAC3F5', accent:'#8B6FE0', deco:'market' },
  { name:'Festival City', sky:['#FFE0B0','#FFF3DA'], ground:'#FFD79A', road:'#FFC773', accent:'#FF5A5F', deco:'festival' }
];

/* ---------------- CHARACTERS ---------------- */
const CHARACTERS = [
  { id:'cane', name:'Sugarcane', color:'#8BD246', dark:'#5FA82C', desc:'Energetic hero', cost:0 },
  { id:'pine', name:'Pineapple', color:'#FFD23F', dark:'#E8AC1A', desc:'Funny sidekick', cost:200 },
  { id:'mango', name:'Mango', color:'#FFB347', dark:'#E8641C', desc:'Confident charmer', cost:350 },
  { id:'coco', name:'Coconut', color:'#EADFC4', dark:'#8B5A2B', desc:'Strong & sturdy', cost:500 },
  { id:'lemon', name:'Lemon', color:'#FFF066', dark:'#E0C400', desc:'Super fast', cost:650 },
  { id:'melon', name:'Watermelon', color:'#FF5D6C', dark:'#3FA34D', desc:'Cool customer', cost:800 }
];

const CHAR_VISUALS = {
  cane:  { body:'#8BD246', dark:'#5FA82C', shape:'cane',   accent:'#FFD23F' },
  pine:  { body:'#FFD23F', dark:'#E8AC1A', shape:'pine',   accent:'#6FAF3C' },
  mango: { body:'#FFB347', dark:'#E8641C', shape:'mango',  accent:'#C0392B' },
  coco:  { body:'#EADFC4', dark:'#8B5A2B', shape:'coco',   accent:'#6B4423' },
  lemon: { body:'#FFF066', dark:'#E0C400', shape:'lemon',  accent:'#6FAF3C' },
  melon: { body:'#FF5D6C', dark:'#3FA34D', shape:'melon',  accent:'#3FA34D' }
};

/* ---------------- BOTTLE SKINS (collectible cane color, shop) ---------------- */
const BOTTLES = [
  { id:'original', name:'Original Cane', color:'#D9E86B', cost:0 },
  { id:'lemon', name:'Lemon Cane', color:'#FFF066', cost:150 },
  { id:'mango', name:'Mango Cane', color:'#FFB347', cost:150 },
  { id:'pineapple', name:'Pineapple Cane', color:'#FFD23F', cost:150 },
  { id:'mint', name:'Mint Cane', color:'#7CE0C0', cost:200 },
  { id:'ginger', name:'Ginger Cane', color:'#E8A14A', cost:200 },
  { id:'tropical', name:'Tropical Cane', color:'#FF8C3B', cost:300 }
];

/* ---------------- ACHIEVEMENTS ---------------- */
const ACHIEVEMENTS = [
  { id:'first_run', name:'First Run', desc:'Complete your first run', icon:'🏁', check:s=>s.runs>=1 },
  { id:'runner_1000', name:'1,000m Runner', desc:'Reach 1,000m in a run', icon:'🏃', check:s=>s.bestDistance>=1000 },
  { id:'golden_cane', name:'Golden Cane', desc:'Collect a golden sugarcane', icon:'✨', check:s=>s.goldenCaneCollected>=1 },
  { id:'combo_10', name:'Combo x10', desc:'Reach a combo of x10', icon:'🔥', check:s=>s.bestCombo>=10 },
  { id:'100_canes', name:'100 Canes', desc:'Collect 100 canes total', icon:'🎋', check:s=>s.totalCane>=100 },
  { id:'fruit_collector', name:'Fruit Collector', desc:'Collect 50 fruits total', icon:'🍍', check:s=>s.totalFruit>=50 },
  { id:'master_5000', name:'5,000m Master', desc:'Reach 5,000m in a run', icon:'🚀', check:s=>s.bestDistance>=5000 },
  { id:'legend', name:'Cane Rush Legend', desc:'Score 25,000 in one run', icon:'👑', check:s=>s.bestScore>=25000 }
];

/* ---------------- OBSTACLES ----------------
   type -> {req:'jump'|'slide'|'lane', label} — metadata used for the
   spawn tables and (later) any accessibility/warning UI. */
const OBSTACLE_TYPES = {
  fallenCane:  { req:'jump', h:0.3, label:'fallen cane' },
  crate:       { req:'jump', h:0.42, label:'juice crate' },
  puddle:      { req:'jump', h:0.12, label:'puddle' },
  coconutPile: { req:'jump', h:0.4, label:'coconut pile' },
  barrier:     { req:'slide', h:1.0, label:'wooden barrier', overhead:true },
  archway:     { req:'slide', h:1.0, label:'market arch', overhead:true },
  cart:        { req:'lane', h:0.55, label:'fruit cart' },
  truck:       { req:'lane', h:0.85, label:'delivery truck' },
  stall:       { req:'lane', h:0.6, label:'market stall' },
  bottleStack: { req:'lane', h:0.6, label:'bottle stack' },
};

/* ---------------- POWER-UPS ---------------- */
const POWERUP_META = {
  magnet:   { icon:'🧲', color:'#8B6FE0', name:'Cane Magnet' },
  rush:     { icon:'⚡', color:'#FF8C3B', name:'Sugar Rush' },
  shield:   { icon:'🛡', color:'#3FD1D9', name:'Juice Shield' },
  golden:   { icon:'⭐', color:'#FFD23F', name:'Golden Cane' },
  blast:    { icon:'💥', color:'#FF5A5F', name:'Fruit Blast' },
  superjump:{ icon:'🚀', color:'#8BD246', name:'Super Jump' },
};

/* ---------------- ASSET MANIFEST ----------------
   Every externally-loaded SVG, keyed by the name AssetManager.get()
   reaches for. Collectibles and power-ups render this artwork once
   it's loaded; until then (or if a load fails) render-world.js draws
   the original vector fallback, so the game never shows a blank
   token. Add a new file here and it's available everywhere. */
const ASSET_MANIFEST = {
  'collectible-cane-normal': 'assets/collectibles/collectible-cane.svg',
  'collectible-cane-fresh':  'assets/collectibles/collectible-cane-fresh.svg',
  'collectible-cane-golden': 'assets/collectibles/collectible-cane-golden.svg',
  'collectible-coin':        'assets/collectibles/collectible-coin.svg',
  'collectible-mango':       'assets/collectibles/collectible-fruit-mango.svg',
  'collectible-pineapple':   'assets/collectibles/collectible-fruit-pineapple.svg',
  'collectible-lemon':       'assets/collectibles/collectible-fruit-lemon.svg',
  'collectible-coconut':     'assets/collectibles/collectible-fruit-coconut.svg',
  'collectible-watermelon':  'assets/collectibles/collectible-fruit-watermelon.svg',

  'powerup-magnet':    'assets/powerups/powerup-magnet.svg',
  'powerup-rush':      'assets/powerups/powerup-sugar-rush.svg',
  'powerup-shield':    'assets/powerups/powerup-juice-shield.svg',
  'powerup-golden':    'assets/powerups/powerup-golden-cane.svg',
  'powerup-blast':     'assets/powerups/powerup-fruit-blast.svg',
  'powerup-superjump': 'assets/powerups/powerup-super-jump.svg',
};
