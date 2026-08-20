/* =========================================================
   CANE RUSH — character.js
   Draws the runner (player + menu mascot + character-select cards).
   One canvas-drawn rig covers every playable character; CHAR_VISUALS
   (config.js) picks the body shape/colors per character id.
   ========================================================= */
'use strict';

function limbTransform(ctx, pivotX, pivotY, angleDeg){
  ctx.save(); ctx.translate(pivotX,pivotY); ctx.rotate(angleDeg*Math.PI/180);
}

/* Draws a full runner character. state: run/jump/slide/hit/idle. phase: 0..1 cycling */
function drawRunner(ctx, charId, x, groundY, scale, state, phase, opts={}){
  const vis = CHAR_VISUALS[charId] || CHAR_VISUALS.cane;
  ctx.save();
  ctx.translate(x, groundY);
  ctx.scale(scale, scale);

  let squash=1, stretch=1, bodyLift=0, tilt=0;
  const swing = Math.sin(phase*Math.PI*2);
  const swing2 = Math.sin(phase*Math.PI*2 + Math.PI);

  if(state==='run'){
    bodyLift = Math.abs(Math.sin(phase*Math.PI*2))*6;
    squash = 1 - Math.abs(swing)*0.03; stretch = 1 + Math.abs(swing)*0.03;
  } else if(state==='jump'){
    squash = 1.08; stretch = 0.94; bodyLift = 10;
  } else if(state==='slide'){
    squash = 0.48; stretch = 1.32; bodyLift = -15; tilt = -17;
  } else if(state==='hit'){
    tilt = 18; squash=0.9; stretch=1.05;
  }

  ctx.rotate(tilt*Math.PI/180);
  ctx.translate(0, -bodyLift);

  const BW=44*stretch, BH=58*squash; // body box
  // sliding braces both arms back for balance, front arm swept further
  const armSwingAng = state==='run' ? swing*30 : (state==='jump'? -20 : (state==='slide'? -68:0));
  const armSwingAng2 = state==='run' ? swing2*30 : (state==='jump'? 30 : (state==='slide'? -40:0));
  // sliding tucks the back leg under the body and drives the other leg forward
  const legSwingAng = state==='run' ? swing*40 : (state==='jump'? -15 : (state==='slide'? 55:0));
  const legSwingAng2 = state==='run' ? swing2*40 : (state==='jump'? 20 : (state==='slide'? -58:0));

  // shadow handled by caller

  // --- legs ---
  ctx.fillStyle = vis.dark;
  drawLimb(ctx, -12, BH*0.42, 10, 26, legSwingAng, true);
  drawLimb(ctx, 12, BH*0.42, 10, 26, legSwingAng2, true);

  // --- back arm ---
  ctx.fillStyle = vis.dark;
  drawLimb(ctx, -BW*0.42, -BH*0.05, 8, 24, armSwingAng2+10, false);

  // --- body shape (depends on char) ---
  drawBodyShape(ctx, vis, BW, BH, state);

  // --- backpack for cane character ---
  if(charId==='cane' && !opts.hideBackpack){
    ctx.save();
    ctx.translate(-BW*0.55,-BH*0.15);
    ctx.rotate(-8*Math.PI/180);
    ctx.fillStyle='#5FA82C';
    roundRect(ctx,-8,-20,16,34,6); ctx.fill();
    ctx.strokeStyle='#3F7A1D'; ctx.lineWidth=2;
    for(let i=0;i<3;i++){ ctx.beginPath(); ctx.moveTo(-6,-16+i*10); ctx.lineTo(6,-16+i*10); ctx.stroke(); }
    ctx.restore();
  }

  // --- face ---
  drawFace(ctx, vis, BH, state);

  // --- front arm ---
  ctx.fillStyle = vis.body==='#FFF066' ? '#E0C400' : vis.dark;
  drawLimb(ctx, BW*0.42, -BH*0.05, 8, 24, armSwingAng, false);

  // --- front leg ---
  ctx.fillStyle = vis.dark;
  const frontLegAng = state==='slide' ? -76 : legSwingAng2*0.4;
  const frontLegX = 12 + (state==='slide' ? 30 : 0);
  drawLimb(ctx, frontLegX, BH*0.42, 10, 26, frontLegAng, true);

  ctx.restore();
}

function drawLimb(ctx, px, py, w, len, angleDeg, isLeg){
  ctx.save();
  ctx.translate(px,py);
  ctx.rotate(angleDeg*Math.PI/180);
  roundRect(ctx, -w/2, 0, w, len, w/2);
  ctx.fill();
  // hand/foot dot
  ctx.beginPath(); ctx.ellipse(0,len, w*0.65, w*0.5,0,0,Math.PI*2); ctx.fill();
  ctx.restore();
}

function drawBodyShape(ctx, vis, BW, BH, state){
  ctx.save();
  const grad = ctx.createLinearGradient(0,-BH*0.7,0,BH*0.55);
  grad.addColorStop(0, lighten(vis.body,18));
  grad.addColorStop(1, vis.body);
  ctx.fillStyle = grad;

  if(vis.shape==='cane'){
    roundRect(ctx, -BW*0.42, -BH*0.72, BW*0.84, BH*1.22, BW*0.4); ctx.fill();
    // vertical highlight strip for a rounded, glossier stalk
    ctx.save(); ctx.globalAlpha=0.28; ctx.fillStyle='#FFFFFF';
    roundRect(ctx, -BW*0.26, -BH*0.66, BW*0.15, BH*1.06, BW*0.08); ctx.fill();
    ctx.restore();
    ctx.strokeStyle='rgba(0,0,0,0.12)'; ctx.lineWidth=3;
    for(let i=1;i<4;i++){ const yy=-BH*0.72+ (BH*1.22)*(i/4); ctx.beginPath(); ctx.moveTo(-BW*0.4,yy); ctx.lineTo(BW*0.4,yy); ctx.stroke(); }
    // leaf spray top — one tall center blade + two angled side blades
    ctx.fillStyle=vis.accent;
    ctx.beginPath(); ctx.moveTo(0,-BH*0.72); ctx.quadraticCurveTo(-3,-BH*1.08,0,-BH*1.32); ctx.quadraticCurveTo(4,-BH*1.08,0,-BH*0.72); ctx.fill();
    ctx.beginPath(); ctx.moveTo(0,-BH*0.72); ctx.quadraticCurveTo(-15,-BH*1.0,-17,-BH*1.16); ctx.quadraticCurveTo(-4,-BH*0.94,0,-BH*0.72); ctx.fill();
    ctx.beginPath(); ctx.moveTo(0,-BH*0.72); ctx.quadraticCurveTo(16,-BH*0.98,19,-BH*1.13); ctx.quadraticCurveTo(5,-BH*0.93,0,-BH*0.72); ctx.fill();
    // leaf center veins
    ctx.strokeStyle='rgba(0,0,0,0.16)'; ctx.lineWidth=1.5;
    ctx.beginPath(); ctx.moveTo(0,-BH*0.74); ctx.lineTo(0,-BH*1.26); ctx.stroke();
  } else if(vis.shape==='pine'){
    ctx.beginPath(); ctx.ellipse(0,-BH*0.05,BW*0.46,BH*0.58,0,0,Math.PI*2); ctx.fill();
    // crosshatch pineapple texture
    ctx.strokeStyle='rgba(0,0,0,0.15)'; ctx.lineWidth=2;
    for(let i=-2;i<=2;i++){ ctx.beginPath(); ctx.moveTo(-BW*0.4,-BH*0.4+i*10); ctx.lineTo(BW*0.4,-BH*0.05+i*10); ctx.stroke(); }
    ctx.fillStyle=vis.accent;
    for(let i=-1;i<=1;i++){
      ctx.save(); ctx.translate(i*10,-BH*0.62); ctx.rotate(i*0.3);
      ctx.beginPath(); ctx.moveTo(0,0); ctx.quadraticCurveTo(-6,-24,0,-34); ctx.quadraticCurveTo(6,-24,0,0); ctx.fill();
      ctx.restore();
    }
  } else if(vis.shape==='mango'){
    ctx.beginPath(); ctx.ellipse(0,0,BW*0.48,BH*0.6,0.15,0,Math.PI*2); ctx.fill();
    ctx.fillStyle=vis.accent; ctx.globalAlpha=0.5;
    ctx.beginPath(); ctx.ellipse(-BW*0.1,-BH*0.25,BW*0.22,BH*0.22,0.3,0,Math.PI*2); ctx.fill();
    ctx.globalAlpha=1;
    ctx.fillStyle='#6FAF3C';
    roundRect(ctx,-4,-BH*0.68,8,10,3); ctx.fill();
  } else if(vis.shape==='coco'){
    ctx.beginPath(); ctx.arc(0,0,BW*0.5,0,Math.PI*2); ctx.fill();
    ctx.fillStyle='rgba(255,255,255,0.4)';
    ctx.beginPath(); ctx.arc(-BW*0.15,-BH*0.18,BW*0.28,0,Math.PI*2); ctx.fill();
    ctx.fillStyle=vis.dark;
    for(let i=0;i<3;i++){ ctx.beginPath(); ctx.arc(-4+i*8,-BH*0.58,3,0,Math.PI*2); ctx.fill(); }
  } else if(vis.shape==='lemon'){
    ctx.beginPath(); ctx.ellipse(0,0,BW*0.42,BH*0.62,0,0,Math.PI*2); ctx.fill();
    ctx.fillStyle=vis.dark;
    ctx.beginPath(); ctx.ellipse(0,-BH*0.6,5,7,0,0,Math.PI*2); ctx.fill();
    ctx.beginPath(); ctx.ellipse(0,BH*0.6,5,7,0,0,Math.PI*2); ctx.fill();
  } else if(vis.shape==='melon'){
    ctx.beginPath(); ctx.ellipse(0,0,BW*0.5,BH*0.6,0,0,Math.PI*2); ctx.fill();
    ctx.strokeStyle=vis.dark; ctx.lineWidth=5; ctx.lineCap='round';
    for(let i=-2;i<=2;i++){ ctx.beginPath(); ctx.moveTo(i*9,-BH*0.55); ctx.quadraticCurveTo(i*13,0,i*9,BH*0.55); ctx.stroke(); }
  }
  ctx.restore();
}

function drawFace(ctx, vis, BH, state){
  const eyeY = -BH*0.12, eyeGap = 12;
  const blink = state==='hit';
  ctx.save();
  // eyes white
  ctx.fillStyle='#FFFFFF';
  [-1,1].forEach(s=>{
    ctx.beginPath(); ctx.ellipse(s*eyeGap, eyeY, 9, blink?2:11, 0,0,Math.PI*2); ctx.fill();
  });
  if(!blink){
    ctx.fillStyle='#2E2A1F';
    [-1,1].forEach(s=>{
      ctx.beginPath(); ctx.arc(s*eyeGap + s*2, eyeY+2, 5,0,Math.PI*2); ctx.fill();
      ctx.fillStyle='#fff';
      ctx.beginPath(); ctx.arc(s*eyeGap + s*2-1.5, eyeY, 1.6,0,Math.PI*2); ctx.fill();
      ctx.fillStyle='#2E2A1F';
    });
  } else {
    ctx.strokeStyle='#2E2A1F'; ctx.lineWidth=2;
    [-1,1].forEach(s=>{ ctx.beginPath(); ctx.moveTo(s*eyeGap-6,eyeY); ctx.lineTo(s*eyeGap+6,eyeY); ctx.stroke(); });
  }
  // cheeks
  ctx.fillStyle='rgba(255,120,120,0.35)';
  [-1,1].forEach(s=>{ ctx.beginPath(); ctx.ellipse(s*20, eyeY+12, 6,4,0,0,Math.PI*2); ctx.fill(); });
  // mouth
  ctx.strokeStyle='#2E2A1F'; ctx.lineWidth=2.5; ctx.lineCap='round';
  ctx.beginPath();
  if(state==='hit'){ ctx.arc(0,eyeY+20,7,Math.PI*1.15,Math.PI*1.85); }
  else { ctx.arc(0,eyeY+14,7,0.15*Math.PI,0.85*Math.PI); }
  ctx.stroke();
  ctx.restore();
}
