/* =========================================================
   CANE RUSH — config.js
   All game CONTENT lives here as data. Add a new obstacle,
   power-up, character, environment or achievement by adding
   an entry below (or calling ObstacleManager.register /
   PowerUpManager.register at runtime) — no engine changes
   required.
   ========================================================= */
'use strict';

/* ---------------- CORE CONSTANTS ---------------- */
// Virtual/logical coordinate space. All gameplay math (physics,
// hitboxes, sizes) happens in this fixed-height space; the engine
// scales it to the real screen once per frame (see Game.resize()).
// This is what guarantees jump height / obstacle size / collision
// boxes are IDENTICAL in proportion on a 320px phone and a tablet.
const REF_HEIGHT = 800;

const LANES = [-1, 0, 1];
const JUDGE_Z = 0.90;      // depth at which collisions/pickups are evaluated
const PLAYER_Z = 0.90;
const HORIZON_Y_RATIO = 0.30;
const ENV_DISTANCE = 700;  // meters per environment

const DEBUG_KEY = 'F3';

/* ---------------- VINTAGE TRAVEL-POSTER PALETTE ----------------
   Flat, slightly muted retro tones — mirrors the SVG asset set in
   assets/svg/. Keeping this as the single source of truth means the
   canvas-drawn runner characters and the externally-loaded SVG
   obstacles/collectibles never clash. */
const COLORS = {
  lime:'#8FAE4E', limeDark:'#5F7A34', yellow:'#E8A33D', orange:'#C1502E',
  mango:'#D9A441', coconut:'#F3E5C8', aqua:'#2F7A72', aquaDark:'#1F5750',
  cream:'#F3E5C8', cream2:'#EAD9AE', ink:'#3B2A1E', hotpink:'#C77B71',
  red:'#C1502E', purple:'#2E4A5C', brown:'#5A3B22', watermelonRed:'#C77B71',
  watermelonGreen:'#7A8450', warn:'#D9A441', sand:'#DEC28B', navy:'#2E4A5C'
};

/* Path to every external SVG asset, keyed by the name code reaches for
   it with (AssetManager.get(name)). Add a new obstacle/collectible/
   power-up SVG here and it's available everywhere — no other file
   needs to change. */
const ASSET_MANIFEST = {
  'obstacle-fallenCane':      'obstacle-fallen-cane.svg',
  'obstacle-rollingCoconut':  'obstacle-rolling-coconut.svg',
  'obstacle-crate':           'obstacle-juice-crate.svg',
  'obstacle-puddle':          'obstacle-slippery-puddle.svg',
  'obstacle-coconutPile':     'obstacle-coconut-pile.svg',
  'obstacle-barrier':         'obstacle-wooden-barrier.svg',
  'obstacle-archway':         'obstacle-market-arch.svg',
  'obstacle-cart':            'obstacle-fruit-cart.svg',
  'obstacle-truck':           'obstacle-delivery-truck.svg',
  'obstacle-stall':           'obstacle-market-stall.svg',
  'obstacle-bottleStack':     'obstacle-bottle-stack.svg',

  'collectible-cane-normal':  'collectible-cane.svg',
  'collectible-cane-fresh':   'collectible-cane-fresh.svg',
  'collectible-cane-golden':  'collectible-cane-golden.svg',
  'collectible-coin':         'collectible-coin.svg',
  'collectible-mango':        'collectible-fruit-mango.svg',
  'collectible-pineapple':    'collectible-fruit-pineapple.svg',
  'collectible-lemon':        'collectible-fruit-lemon.svg',
  'collectible-coconut':      'collectible-fruit-coconut.svg',
  'collectible-watermelon':   'collectible-fruit-watermelon.svg',

  'powerup-magnet':           'powerup-cane-magnet.svg',
  'powerup-rush':             'powerup-sugar-rush.svg',
  'powerup-shield':           'powerup-juice-shield.svg',
  'powerup-golden':           'powerup-golden-cane.svg',
  'powerup-blast':            'powerup-fruit-blast.svg',
  'powerup-superjump':        'powerup-super-jump.svg',
  'powerup-fruitBoost':       'powerup-fruit-boost.svg',
  'powerup-secondWind':       'powerup-second-wind.svg',
  'powerup-slowSugar':        'powerup-slow-sugar.svg',
  'powerup-coinRain':         'powerup-coin-rain.svg',
};

/* Player physics — all values are in REF_HEIGHT virtual units so
   they scale identically across every screen size. */
const PLAYER_PHYSICS = {
  gravity: 2600,          // virtual px / s^2
  jumpVelocity: -800,     // initial upward velocity (negative = up)
  superJumpVelocity: -1150,
  fullHeight: 140,        // standing collision height
  fullWidth: 70,
  slideHeightFactor: 0.5, // collision height while sliding
  slideDuration: 0.55,
  laneLerpRate: 0.0025,   // exponential smoothing rate for lane interpolation (frame-rate independent)
};

/* ---------------- DIFFICULTY TIERS ---------------- */
const DIFFICULTY_TIERS = [
  { name:'Easy',    minDistance:0,    maxSpeedMult:1.0,  patternTier:0 },
  { name:'Normal',  minDistance:500,  maxSpeedMult:1.35, patternTier:1 },
  { name:'Fast',    minDistance:1500, maxSpeedMult:1.75, patternTier:2 },
  { name:'Extreme', minDistance:3000, maxSpeedMult:2.15, patternTier:2 },
];

/* ---------------- ENVIRONMENTS ---------------- */
const ENVIRONMENT_CONFIG = [
  { name:'Sugarcane Farm', sky:['#E8D9A8','#F3E5C8'], ground:'#DCC98F', road:'#C9AD6E', accent:'#7A8450', deco:'farm' },
  { name:'Tropical Road', sky:['#CFE0DA','#EAF2ED'], ground:'#C7D6C9', road:'#A9C0AC', accent:'#2F7A72', deco:'road' },
  { name:'Fruit Garden', sky:['#F0DAB8','#F8EEDA'], ground:'#E3C594','road':'#D6AE72', accent:'#C1502E', deco:'garden' },
  { name:'Juice Factory', sky:['#E7D1CD','#F5E9E5'], ground:'#DABEB6', road:'#CDA79C', accent:'#C77B71', deco:'factory' },
  { name:'Beach Road', sky:['#D8E4E2','#F0F5F3'], ground:'#E9D6A9', road:'#DCC287', accent:'#2F7A72', deco:'beach' },
  { name:'Tropical Market', sky:['#DCD3E0','#F1EBF2'], ground:'#D3C4A8', road:'#C2AE86', accent:'#2E4A5C', deco:'market' },
  { name:'Festival City', sky:['#EFD8AE','#F8ECD4'], ground:'#E0C088', road:'#D3A968', accent:'#C1502E', deco:'festival' }
];

/* ---------------- CHARACTERS ---------------- */
const CHARACTER_CONFIG = [
  { id:'cane', name:'Sugarcane', color:'#8FAE4E', dark:'#5F7A34', desc:'Energetic hero', cost:0 },
  { id:'pine', name:'Pineapple', color:'#E8A33D', dark:'#B67D26', desc:'Funny sidekick', cost:200 },
  { id:'mango', name:'Mango', color:'#D9A441', dark:'#A9761E', desc:'Confident charmer', cost:350 },
  { id:'coco', name:'Coconut', color:'#DEC28B', dark:'#5A3B22', desc:'Strong & sturdy', cost:500 },
  { id:'lemon', name:'Lemon', color:'#E8D078', dark:'#B7A03E', desc:'Super fast', cost:650 },
  { id:'melon', name:'Watermelon', color:'#C77B71', dark:'#7A8450', desc:'Cool customer', cost:800 }
];
const CHAR_VISUALS = {
  cane:  { body:'#8FD13F', dark:'#4C8A22', shape:'cane',   accent:'#2F5F1A' },
  pine:  { body:'#E8A33D', dark:'#B67D26', shape:'pine',   accent:'#7A8450' },
  mango: { body:'#D9A441', dark:'#A9761E', shape:'mango',  accent:'#C1502E' },
  coco:  { body:'#DEC28B', dark:'#5A3B22', shape:'coco',   accent:'#3B2A1E' },
  lemon: { body:'#E8D078', dark:'#B7A03E', shape:'lemon',  accent:'#7A8450' },
  melon: { body:'#C77B71', dark:'#7A8450', shape:'melon',  accent:'#7A8450' }
};

/* ---------------- BOTTLE SKINS (collectible cane color) ---------------- */
const BOTTLE_CONFIG = [
  { id:'original', name:'Original Cane', color:'#C7C77A', cost:0 },
  { id:'lemon', name:'Lemon Cane', color:'#E8D078', cost:150 },
  { id:'mango', name:'Mango Cane', color:'#D9A441', cost:150 },
  { id:'pineapple', name:'Pineapple Cane', color:'#E8A33D', cost:150 },
  { id:'mint', name:'Mint Cane', color:'#7FAE9C', cost:200 },
  { id:'ginger', name:'Ginger Cane', color:'#C1502E', cost:200 },
  { id:'tropical', name:'Tropical Cane', color:'#C77B71', cost:300 }
];

/* ---------------- ACHIEVEMENTS ---------------- */
const ACHIEVEMENT_CONFIG = [
  { id:'first_run', name:'First Run', desc:'Complete your first run', icon:'🏁', check:s=>s.runs>=1 },
  { id:'runner_1000', name:'1,000m Runner', desc:'Reach 1,000m in a run', icon:'🏃', check:s=>s.bestDistance>=1000 },
  { id:'golden_cane', name:'Golden Cane', desc:'Collect a golden sugarcane', icon:'✨', check:s=>s.goldenCaneCollected>=1 },
  { id:'combo_10', name:'Combo x10', desc:'Reach a combo of x10', icon:'🔥', check:s=>s.bestCombo>=10 },
  { id:'100_canes', name:'100 Canes', desc:'Collect 100 canes total', icon:'🎋', check:s=>s.totalCane>=100 },
  { id:'fruit_collector', name:'Fruit Collector', desc:'Collect 50 fruits total', icon:'🍍', check:s=>s.totalFruit>=50 },
  { id:'master_5000', name:'5,000m Master', desc:'Reach 5,000m in a run', icon:'🚀', check:s=>s.bestDistance>=5000 },
  { id:'legend', name:'Cane Rush Legend', desc:'Score 25,000 in one run', icon:'👑', check:s=>s.bestScore>=25000 }
];

/* ---------------- OBSTACLE CONFIG ----------------
   action: 'jump' | 'slide' | 'lane' — tells the player (via shape,
   silhouette and the warning system) what to do, and drives the
   collision hitbox.
   collision box is expressed in REF_HEIGHT virtual units, bottom-up
   (0 = ground). 'jump' obstacles occupy the low band (must clear by
   jumping); 'slide' obstacles occupy an overhead band (must duck
   under by sliding); 'lane' obstacles occupy the full vertical band
   (unavoidable except by changing lane). */
const OBSTACLE_CONFIG = {
  fallenCane:  { action:'jump',  label:'Fallen Cane',   box:{bottom:0,  height:66 }, asset:'obstacle-fallenCane' },
  crate:       { action:'jump',  label:'Juice Crate',   box:{bottom:0,  height:70 }, asset:'obstacle-crate' },
  puddle:      { action:'jump',  label:'Slippery Puddle', box:{bottom:0, height:40 }, asset:'obstacle-puddle' },
  coconutPile: { action:'jump',  label:'Coconut Pile',  box:{bottom:0,  height:76 }, asset:'obstacle-coconutPile' },
  rollingCoconut: { action:'jump', label:'Rolling Coconut', box:{bottom:0, height:58 }, asset:'obstacle-rollingCoconut' }, // example of an easily-added obstacle
  barrier:     { action:'slide', label:'Wooden Barrier', box:{bottom:80, height:120}, asset:'obstacle-barrier' },
  archway:     { action:'slide', label:'Market Arch',   box:{bottom:80, height:120}, asset:'obstacle-archway' },
  cart:        { action:'lane',  label:'Fruit Cart',    box:{bottom:0,  height:210}, asset:'obstacle-cart' },
  truck:       { action:'lane',  label:'Delivery Truck', box:{bottom:0, height:210}, asset:'obstacle-truck' },
  stall:       { action:'lane',  label:'Market Stall',  box:{bottom:0,  height:210}, asset:'obstacle-stall' },
  bottleStack: { action:'lane',  label:'Bottle Stack',  box:{bottom:0,  height:210}, asset:'obstacle-bottleStack' },
};

/* ---------------- COLLECTIBLES ---------------- */
const COLLECTIBLE_SCORE = {
  cane:{ normal:10, fresh:25, golden:100 },
  coin:5,
  fruit:15
};

/* ---------------- POWER-UP CONFIG ----------------
   Each power-up is fully self-describing: icon, color, name,
   duration and activate/deactivate hooks that mutate a shared
   `powerups` timer bag on the player/game state. This is the same
   shape PowerUpManager.register() accepts at runtime. */
const POWERUP_CONFIG = {
  magnet:   { name:'Cane Magnet',  icon:'🧲', color:'#2E4A5C', duration:8, asset:'powerup-magnet',
              activate(state){ state.powerups.magnet = 8; } },
  rush:     { name:'Sugar Rush',   icon:'⚡', color:'#C1502E', duration:6, asset:'powerup-rush',
              activate(state){ state.powerups.rush = 6; } },
  shield:   { name:'Juice Shield', icon:'🛡', color:'#2F7A72', duration:1, asset:'powerup-shield',
              activate(state){ state.powerups.shield = 1; } },
  golden:   { name:'Golden Cane',  icon:'⭐', color:'#D9A441', duration:10, asset:'powerup-golden',
              activate(state){ state.powerups.golden = 10; } },
  blast:    { name:'Fruit Blast',  icon:'💥', color:'#C77B71', duration:1.4, asset:'powerup-blast',
              activate(state){ state.powerups.blast = 1.4; } },
  superjump:{ name:'Super Jump',   icon:'🚀', color:'#7A8450', duration:8, asset:'powerup-superjump',
              activate(state){ state.powerups.superjump = 8; } },
  fruitBoost:{ name:'Fruit Boost', icon:'🍹', color:'#C77B71', duration:8, asset:'powerup-fruitBoost',  // example of an easily-added power-up
              activate(state){ state.powerups.fruitBoost = 8; } },
  secondWind:{ name:'Second Wind', icon:'💚', color:'#8FAE4E', duration:999, asset:'powerup-secondWind', persistent:true,
              activate(state){ state.powerups.secondWind = 1; } }, // a stored charge, not a countdown — consumed on crash
  slowSugar:{ name:'Slow Sugar',   icon:'⏳', color:'#DEC28B', duration:5, asset:'powerup-slowSugar',
              activate(state){ state.powerups.slowSugar = 5; } }, // stretches reaction time / obstacle approach speed
  coinRain: { name:'Coin Rain',    icon:'🪙', color:'#E8A33D', duration:0.1, asset:'powerup-coinRain',
              activate(state){
                state.powerups.coinRain = 0.1;
                for(let i=0;i<7;i++){ state.collectibleManager.list.push({kind:'coin', lane:pick(LANES), z: i*0.025, id:Math.random()}); }
              } },
};

/* ---------------- SAFE OBSTACLE PATTERNS ----------------
   Every pattern is guaranteed solvable: never all three lanes
   blocked, and no lane requires two conflicting actions. Patterns
   are grouped by tier so the DifficultyManager can gradually
   unlock more complex combinations instead of throwing everything
   at the player at once. Empty string = lane is clear (may spawn a
   collectible). */
const SAFE_PATTERNS = {
  0: [ // tier 0 — easy, single obstacle, plenty of clear lanes
    ['jump','',''], ['','jump',''], ['','','jump'],
    ['slide','',''], ['','','slide'],
    ['lane','',''], ['','','lane'],
    ['','',''], ['','',''],
  ],
  1: [ // tier 1 — two simultaneous obstacles, one guaranteed clear lane
    ['lane','','lane'], ['jump','','slide'], ['slide','','jump'],
    ['jump','lane',''], ['','lane','jump'],
    ['','slide',''],
  ],
  2: [ // tier 2 — denser, still always >=1 safe lane
    ['lane','jump',''], ['','jump','lane'],
    ['slide','lane',''], ['','lane','slide'],
    ['jump','slide',''], ['','slide','jump'],
  ]
};
