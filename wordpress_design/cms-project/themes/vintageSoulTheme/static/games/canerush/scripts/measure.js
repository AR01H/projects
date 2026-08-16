/**
 * CANE RUSH — measure.js
 * Crops a diagnostic grid — one cell from each row/column position
 * so we can visually check coordinates before the full crop.
 * Run: node scripts/measure.js
 */
const sharp = require('sharp');
const path  = require('path');
const fs    = require('fs');

const ROOT  = path.resolve(__dirname, '..');
const ASSETS= path.join(ROOT,'assets','img');
const OUT   = path.join(ROOT,'assets','img','_diagnostic');
fs.mkdirSync(OUT, {recursive:true});

const MOV = path.join(ASSETS,'movements','ChatGPT Image Aug 16, 2026, 06_56_05 PM.png');
const EL2 = path.join(ASSETS,'elements', 'ChatGPT Image Aug 16, 2026, 06_58_27 PM.png');

async function crop(src, name, left, top, width, height) {
  const dest = path.join(OUT, name+'.png');
  try {
    await sharp(src)
      .extract({left:Math.max(0,Math.round(left)), top:Math.max(0,Math.round(top)),
                width:Math.round(width), height:Math.round(height)})
      .png().toFile(dest);
    console.log('✓', name, `(${left},${top},${width}x${height})`);
  } catch(e){ console.error('✗', name, e.message); }
}

async function main(){
  // ── MOVEMENTS 1402×1122 ─────────────────────────────────────────────────
  // We'll crop an annotated region to verify: draw a 6-col grid on row1
  // Full width 1402, 6 cols in row0 → each col ≈ 233px
  // But actual content is narrower — let's sample col 0..5 of each row
  const MW = 1402, MH = 1122;
  const COLS_R0 = 6;
  const cw0 = Math.floor(MW / COLS_R0); // 233

  // Row 0 (6 standing poses, top ≈ 20, h ≈ 200)
  for(let c=0;c<6;c++){
    await crop(MOV, `mov_r0_c${c}`, c*cw0, 20, cw0-2, 205);
  }

  // Row 1 left half: 4 run frames — each ~233px
  for(let c=0;c<4;c++){
    await crop(MOV, `mov_r1_run_c${c}`, c*cw0, 226, cw0-2, 205);
  }
  // Row 1 right half: 6 jump frames — remaining width = 1402-4*233=470, each=78px
  for(let c=0;c<6;c++){
    await crop(MOV, `mov_r1_jump_c${c}`, 932+c*78, 226, 76, 205);
  }

  // Row 2: left = 6 slide frames ~130px each; right = move left 4 frames ~115px each
  for(let c=0;c<6;c++){
    await crop(MOV, `mov_r2_slide_c${c}`, c*135, 440, 133, 148);
  }
  for(let c=0;c<4;c++){
    await crop(MOV, `mov_r2_left_c${c}`, 820+c*120, 440, 118, 148);
  }

  // Row 3: left 5 bank-left, right 5 bank-right
  for(let c=0;c<5;c++){
    await crop(MOV, `mov_r3_bleft_c${c}`, c*140, 590, 138, 155);
  }
  for(let c=0;c<5;c++){
    await crop(MOV, `mov_r3_bright_c${c}`, 704+c*140, 590, 138, 155);
  }

  // Row 4: 6 fall frames left, 5 handle right (we skip handle)
  for(let c=0;c<6;c++){
    await crop(MOV, `mov_r4_fall_c${c}`, c*175, 750, 173, 178);
  }

  // ── ELEMENTS-2 1536×1024 ─────────────────────────────────────────────────
  // Row 0 obstacles: 10 items, each ≈ 150px wide
  for(let c=0;c<10;c++){
    await crop(EL2, `el2_obs_r0_c${c}`, 20+c*150, 55, 148, 215);
  }
  // Row 1 obstacles: 10 items
  for(let c=0;c<10;c++){
    await crop(EL2, `el2_obs_r1_c${c}`, 20+c*150, 278, 148, 215);
  }
  // Row 2 collectables
  for(let c=0;c<10;c++){
    await crop(EL2, `el2_col_r2_c${c}`, 20+c*150, 516, 148, 165);
  }
  // Row 3 collectables/powerups
  for(let c=0;c<10;c++){
    await crop(EL2, `el2_pow_r3_c${c}`, 20+c*150, 698, 148, 165);
  }

  console.log(`\n✅ Diagnostic crops saved to: ${OUT}`);
}

main().catch(console.error);
