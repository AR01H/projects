/**
 * CANE RUSH — crop_assets.js  (v4 — pixel-calibrated)
 *
 * Pixel coordinates confirmed through iterative visual inspection.
 *
 * MOVEMENTS sheet  1402 × 1122:
 *   R0  y=28  h=209  — 6 standing poses (BACK/LBACK/RBACK/LEFT/RIGHT/FRONT), col-w=233
 *   R1  y=282 h=224  — RUN 1-4 (cols 0-3, w=231) + JUMP 6-frames (x=932, w=78)
 *   R2  y=570 h=145  — SLIDE 6 (cols 0-5, w=133) + MOVE-LEFT 4 (x=820, w=118)
 *   R3  y=755 h=155  — MOVE-TO-LEFT-EDGE 5 (cols 0-4, w=140) + MOVE-TO-RIGHT-EDGE 5 (x=704)
 *   R4  y=945 h=140  — FALL 6 (cols 0-5, w=175)
 *
 * ELEMENTS-2 sheet  1536 × 1024:
 *   Obs R0  y=55  h=210  10 obstacle items, col-w=148, gap=5, start-x=18
 *   Obs R1  y=278 h=210  10 obstacle items, same grid
 *   Col R2  y=516 h=160  10 collectables (coins, juice, gems), same grid
 *   Pow R3  y=698 h=155  10 powerup/collectible items, same grid
 *
 * ELEMENTS-3 sheet  1536 × 1024:
 *   Juice  y=55  h=180  10 juice bottles (powerups), col-w=148, gap=5, start-x=18
 *
 * Run: node scripts/crop_assets.js
 */

const sharp  = require('sharp');
const path   = require('path');
const fs     = require('fs');

const ROOT   = path.resolve(__dirname, '..');
const ASSETS = path.join(ROOT, 'assets', 'img');

const SRC = {
  MOV: path.join(ASSETS, 'movements', 'ChatGPT Image Aug 16, 2026, 06_56_05 PM.png'),
  EL2: path.join(ASSETS, 'elements',  'ChatGPT Image Aug 16, 2026, 06_58_27 PM.png'),
  EL3: path.join(ASSETS, 'elements',  'ChatGPT Image Aug 16, 2026, 07_01_45 PM.png'),
};

const DIR = {
  MOV: path.join(ASSETS, 'movements'),
  OBS: path.join(ASSETS, 'obstacles'),
  COL: path.join(ASSETS, 'collectibles'),
  POW: path.join(ASSETS, 'powerups'),
};

function ensureDirs() {
  Object.values(DIR).forEach(d => fs.mkdirSync(d, { recursive: true }));
}

async function crop(src, dest, left, top, width, height) {
  try {
    await sharp(src)
      .extract({
        left:   Math.max(0, Math.round(left)),
        top:    Math.max(0, Math.round(top)),
        width:  Math.round(width),
        height: Math.round(height),
      })
      .png()
      .toFile(dest);
    console.log('  ✓', path.basename(dest));
  } catch(e) {
    console.error('  ✗', path.basename(dest), e.message);
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// MOVEMENTS  (1402 × 1122)
// ─────────────────────────────────────────────────────────────────────────────
async function cropMovements() {
  console.log('\n── Movements ──');
  const M = SRC.MOV;
  const O = DIR.MOV;

  // ── Row 0 — Standing poses (col-w=233)
  // Cols: 0=BACK, 1=LEFT-BACK, 2=RIGHT-BACK, 3=LEFT, 4=RIGHT, 5=FRONT
  const R0 = { t: 28, h: 209, cw: 231 };
  const r0x = c => c * 233;

  // Use col3=LEFT as left-strafe, col4=RIGHT as right-strafe, col5=FRONT as idle
  await crop(M, path.join(O,'idle1.png'),  r0x(5), R0.t, R0.cw, R0.h);   // FRONT
  await crop(M, path.join(O,'idle2.png'),  r0x(4), R0.t, R0.cw, R0.h);   // RIGHT

  // ── Row 1 — RUN LOOP (cols 0-3, cw=231) + JUMP (6 frames, x=932, cw=78)
  const R1 = { t: 282, h: 224 };

  // Run frames 1-4
  await crop(M, path.join(O,'run1.png'), 0,   R1.t, 231, R1.h);
  await crop(M, path.join(O,'run2.png'), 233, R1.t, 231, R1.h);
  await crop(M, path.join(O,'run3.png'), 466, R1.t, 231, R1.h);
  await crop(M, path.join(O,'run4.png'), 699, R1.t, 231, R1.h);
  // run5-8: reuse row0 poses (back/side angles)
  await crop(M, path.join(O,'run5.png'), r0x(0), R0.t, R0.cw, R0.h);  // BACK
  await crop(M, path.join(O,'run6.png'), r0x(1), R0.t, R0.cw, R0.h);  // LEFT BACK
  await crop(M, path.join(O,'run7.png'), r0x(2), R0.t, R0.cw, R0.h);  // RIGHT BACK
  await crop(M, path.join(O,'run8.png'), r0x(3), R0.t, R0.cw, R0.h);  // LEFT

  // Jump frames 1-4 (PREPARE, TAKEOFF, AIR, DESCEND)
  const jx = c => 932 + c * 78;
  await crop(M, path.join(O,'jump1.png'), jx(0), R1.t, 77, R1.h);
  await crop(M, path.join(O,'jump2.png'), jx(1), R1.t, 77, R1.h);
  await crop(M, path.join(O,'jump3.png'), jx(2), R1.t, 77, R1.h);
  await crop(M, path.join(O,'jump4.png'), jx(3), R1.t, 77, R1.h);

  // Superjump: use AIR + LAND + RECOVER frames
  await crop(M, path.join(O,'superjump1.png'), jx(2), R1.t, 77, R1.h);  // AIR
  await crop(M, path.join(O,'superjump2.png'), jx(3), R1.t, 77, R1.h);  // DESCEND
  await crop(M, path.join(O,'superjump3.png'), jx(4), R1.t, 77, R1.h);  // LAND

  // ── Row 2 — SLIDE (cols 0-5, cw=133) + MOVE-LEFT (x=820, cw=118)
  const R2 = { t: 570, h: 145 };
  const sx = c => c * 135;

  await crop(M, path.join(O,'slide1.png'), sx(0), R2.t, 133, R2.h);
  await crop(M, path.join(O,'slide2.png'), sx(1), R2.t, 133, R2.h);
  await crop(M, path.join(O,'slide3.png'), sx(2), R2.t, 133, R2.h);
  await crop(M, path.join(O,'slide4.png'), sx(3), R2.t, 133, R2.h);

  // Move-left 4 frames
  const lx = c => 820 + c * 120;
  await crop(M, path.join(O,'left1.png'), lx(0), R2.t, 118, R2.h);
  await crop(M, path.join(O,'left2.png'), lx(1), R2.t, 118, R2.h);
  await crop(M, path.join(O,'left3.png'), lx(2), R2.t, 118, R2.h);
  await crop(M, path.join(O,'left4.png'), lx(3), R2.t, 118, R2.h);

  // ── Row 3 — BANK LEFT (cols 0-4) + BANK RIGHT (cols 5-9), cw=140
  const R3 = { t: 755, h: 155 };
  const bx = c => c * 140;

  await crop(M, path.join(O,'right1.png'), bx(0), R3.t, 138, R3.h);
  await crop(M, path.join(O,'right2.png'), bx(1), R3.t, 138, R3.h);
  await crop(M, path.join(O,'right3.png'), bx(2), R3.t, 138, R3.h);
  await crop(M, path.join(O,'right4.png'), bx(3), R3.t, 138, R3.h);

  // ── Row 4 — FALL DOWN (cols 0-5, cw=175)
  const R4 = { t: 945, h: 140 };
  const fx = c => c * 175;

  await crop(M, path.join(O,'fall1.png'), fx(0), R4.t, 174, R4.h);
  await crop(M, path.join(O,'fall2.png'), fx(1), R4.t, 174, R4.h);
  await crop(M, path.join(O,'fall3.png'), fx(2), R4.t, 174, R4.h);
  await crop(M, path.join(O,'fall4.png'), fx(3), R4.t, 174, R4.h);
}

// ─────────────────────────────────────────────────────────────────────────────
// ELEMENTS-2  (1536 × 1024)
// Grid: col-w=148, gap≈5, start-x=18
// ─────────────────────────────────────────────────────────────────────────────
async function cropElements2() {
  console.log('\n── Obstacles + Collectibles (Elements-2) ──');
  const E = SRC.EL2;
  const ex = c => 18 + c * 153;  // 148+5 gap

  // ── Row 0 obstacles (y=55, h=210)
  const oR0 = { t: 55, h: 210 };
  await crop(E, path.join(DIR.OBS,'obstacle-fallen-cane.png'),       ex(0), oR0.t, 148, oR0.h);  // SUGARCANE BARRIER
  await crop(E, path.join(DIR.OBS,'obstacle-fruit-cart.png'),         ex(1), oR0.t, 148, oR0.h);  // CANE CART BLOCKER
  await crop(E, path.join(DIR.OBS,'obstacle-juice-crate.png'),        ex(2), oR0.t, 148, oR0.h);  // SUGARCANE CRATE
  await crop(E, path.join(DIR.OBS,'obstacle-coconut-pile.png'),       ex(3), oR0.t, 148, oR0.h);  // ROCK PILE
  await crop(E, path.join(DIR.OBS,'obstacle-cane-pile.png'),          ex(4), oR0.t, 148, oR0.h);  // BROKEN CANE PILE
  await crop(E, path.join(DIR.OBS,'obstacle-slippery-puddle.png'),    ex(5), oR0.t, 148, oR0.h);  // MUD PUDDLE
  await crop(E, path.join(DIR.OBS,'obstacle-wooden-barrier.png'),     ex(6), oR0.t, 148, oR0.h);  // STONE BLOCK
  await crop(E, path.join(DIR.OBS,'obstacle-market-arch.png'),        ex(7), oR0.t, 148, oR0.h);  // LOW CANE BARRICADE
  await crop(E, path.join(DIR.OBS,'obstacle-hanging-cane.png'),       ex(8), oR0.t, 148, oR0.h);  // HANGING CANE
  await crop(E, path.join(DIR.OBS,'obstacle-falling-cane-trap.png'),  ex(9), oR0.t, 141, oR0.h);  // FALLING CANE TRAP

  // ── Row 1 obstacles (y=278, h=210)
  const oR1 = { t: 278, h: 210 };
  await crop(E, path.join(DIR.OBS,'obstacle-spikes.png'),          ex(0), oR1.t, 148, oR1.h);  // SPIKES TRAP
  await crop(E, path.join(DIR.OBS,'obstacle-rolling-coconut.png'), ex(1), oR1.t, 148, oR1.h);  // ROLLING CANE LOG
  await crop(E, path.join(DIR.OBS,'obstacle-lava-crack.png'),      ex(2), oR1.t, 148, oR1.h);  // LAVA CRACK
  await crop(E, path.join(DIR.OBS,'obstacle-cane-blade.png'),      ex(3), oR1.t, 148, oR1.h);  // CANE SWING BLADE
  await crop(E, path.join(DIR.OBS,'obstacle-narrow-path.png'),     ex(4), oR1.t, 148, oR1.h);  // NARROW PATH
  await crop(E, path.join(DIR.OBS,'obstacle-bridge.png'),          ex(5), oR1.t, 148, oR1.h);  // COLLAPSING BRIDGE
  await crop(E, path.join(DIR.OBS,'obstacle-thorny-bush.png'),     ex(6), oR1.t, 148, oR1.h);  // THORNY CANE BUSH
  await crop(E, path.join(DIR.OBS,'obstacle-stone-pillar.png'),    ex(7), oR1.t, 148, oR1.h);  // STONE PILLAR
  await crop(E, path.join(DIR.OBS,'obstacle-falling-rocks.png'),   ex(8), oR1.t, 148, oR1.h);  // FALLING ROCKS
  await crop(E, path.join(DIR.OBS,'obstacle-cane-wall.png'),       ex(9), oR1.t, 141, oR1.h);  // CANE WALL

  // ── Row 2 collectables (y=516, h=160)
  // Cols: 0=small coin, 1=med coin, 2=large coin, 3=spinning coin, 4=star coin,
  //       5=juice bottle, 6=green gem, 7=blue gem, 8=red gem, 9=yellow gem
  const cR2 = { t: 516, h: 160 };
  await crop(E, path.join(DIR.COL,'collectible-coin.png'),            ex(0), cR2.t, 148, cR2.h);
  await crop(E, path.join(DIR.COL,'collectible-cane-golden.png'),     ex(4), cR2.t, 148, cR2.h);  // star coin
  await crop(E, path.join(DIR.COL,'collectible-fruit-coconut.png'),   ex(5), cR2.t, 148, cR2.h);  // juice bottle
  await crop(E, path.join(DIR.COL,'collectible-fruit-lemon.png'),     ex(6), cR2.t, 148, cR2.h);  // green gem
  await crop(E, path.join(DIR.COL,'collectible-fruit-watermelon.png'),ex(7), cR2.t, 148, cR2.h);  // blue gem
  await crop(E, path.join(DIR.COL,'collectible-fruit-pineapple.png'), ex(8), cR2.t, 148, cR2.h);  // red gem
  await crop(E, path.join(DIR.COL,'collectible-fruit-mango.png'),     ex(9), cR2.t, 141, cR2.h);  // yellow gem

  // ── Row 3 powerups/collectibles (y=698, h=155)
  // Cols: 0=shield, 1=speed, 2=2x, 3=magnet, 4=energy, 5=extra-life,
  //       6=cane-token, 7=cane-leaf, 8=juice-bonus, 9=daily-bonus
  const pR3 = { t: 698, h: 155 };
  await crop(E, path.join(DIR.POW,'powerup-juice-shield.png'),  ex(0), pR3.t, 148, pR3.h);
  await crop(E, path.join(DIR.POW,'powerup-sugar-rush.png'),    ex(1), pR3.t, 148, pR3.h);
  await crop(E, path.join(DIR.POW,'powerup-golden-cane.png'),   ex(2), pR3.t, 148, pR3.h);
  await crop(E, path.join(DIR.POW,'powerup-cane-magnet.png'),   ex(3), pR3.t, 148, pR3.h);
  await crop(E, path.join(DIR.POW,'powerup-fruit-boost.png'),   ex(4), pR3.t, 148, pR3.h);
  await crop(E, path.join(DIR.POW,'powerup-second-wind.png'),   ex(5), pR3.t, 148, pR3.h);
  await crop(E, path.join(DIR.COL,'collectible-cane.png'),       ex(6), pR3.t, 148, pR3.h);
  await crop(E, path.join(DIR.COL,'collectible-cane-fresh.png'), ex(7), pR3.t, 148, pR3.h);
  await crop(E, path.join(DIR.POW,'powerup-coin-rain.png'),      ex(8), pR3.t, 148, pR3.h);
  await crop(E, path.join(DIR.POW,'powerup-slow-sugar.png'),     ex(9), pR3.t, 141, pR3.h);
}

// ─────────────────────────────────────────────────────────────────────────────
// ELEMENTS-3  (1536 × 1024) — Juice bottles row for super-jump powerup
// ─────────────────────────────────────────────────────────────────────────────
async function cropElements3() {
  console.log('\n── Juice Powerups (Elements-3) ──');
  const E  = SRC.EL3;
  const ex = c => 18 + c * 153;
  const jR = { t: 55, h: 180 };

  // Cols: 0=Normal, 1=Energy(lightning), 2=Golden, 3=Double(2x), 4=Shield,
  //       5=Magnet, 6=ExtraLife, 7=Speed, 8=Mega, 9=Mystery
  await crop(E, path.join(DIR.POW,'powerup-super-jump.png'), ex(8), jR.t, 148, jR.h);  // Mega juice = super jump
}

// ─────────────────────────────────────────────────────────────────────────────
// MAIN
// ─────────────────────────────────────────────────────────────────────────────
async function main() {
  console.log('\n🎯 CaneRush Asset Cropper v4 (pixel-calibrated)\n');
  ensureDirs();
  await cropMovements();
  await cropElements2();
  await cropElements3();
  console.log('\n✅ All done!\n');
}

main().catch(console.error);
